<?php

namespace App\Services\Monitoring;

use App\DTOs\HealthStatus;
use App\DTOs\PerformanceMetrics;
use App\DTOs\UptimeReport;
use App\Repositories\SupplierRepositoryInterface;
use App\Repositories\HealthCheckRepositoryInterface;
use App\Repositories\IncidentRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SupplierApiFailure;

class ApiMonitoringService
{
    public function __construct(
        private SupplierRepositoryInterface $supplierRepository,
        private HealthCheckRepositoryInterface $healthCheckRepository,
        private IncidentRepositoryInterface $incidentRepository
    ) {}

    public function monitorApiHealth(string $supplier): HealthStatus
    {
        $supplierModel = $this->supplierRepository->findByName($supplier);
        
        if (!$supplierModel) {
            Log::warning("Supplier '{$supplier}' not found");
            return HealthStatus::error($supplier, "Supplier not found");
        }

        if (!$supplierModel->isActive()) {
            Log::info("Supplier '{$supplier}' is not active, skipping health check");
            return HealthStatus::error($supplier, "Supplier is not active");
        }

        $startTime = microtime(true);
        $healthStatus = $this->performHealthCheck($supplierModel);
        $endTime = microtime(true);
        
        $responseTime = (int) (($endTime - $startTime) * 1000);

        $this->healthCheckRepository->create([
            'supplier_id' => $supplierModel->id,
            'response_time_ms' => $healthStatus->responseTimeMs ?: $responseTime,
            'status_code' => $healthStatus->statusCode,
            'status' => $healthStatus->status,
            'error_message' => $healthStatus->errorMessage,
            'response_headers' => $healthStatus->responseHeaders,
            'checked_at' => now()
        ]);

        Log::info("Health check completed for supplier '{$supplier}'", [
            'status' => $healthStatus->status,
            'response_time_ms' => $healthStatus->responseTimeMs,
            'status_code' => $healthStatus->statusCode
        ]);

        return $healthStatus;
    }

    public function trackResponseTimes(string $supplier): PerformanceMetrics
    {
        $supplierModel = $this->supplierRepository->findByName($supplier);
        
        if (!$supplierModel) {
            Log::warning("Supplier '{$supplier}' not found for performance tracking");
            return PerformanceMetrics::fromHealthChecks($supplier, []);
        }

        $healthChecks = $this->healthCheckRepository->findRecentBySupplier($supplierModel->id, 24);
        
        $healthCheckData = $healthChecks->map(function ($check) {
            return [
                'response_time_ms' => $check->response_time_ms,
                'status' => $check->status,
                'checked_at' => $check->checked_at
            ];
        })->toArray();

        $metrics = PerformanceMetrics::fromHealthChecks($supplier, $healthCheckData);

        Log::info("Performance metrics calculated for supplier '{$supplier}'", [
            'avg_response_time' => $metrics->avgResponseTimeMs,
            'error_rate' => $metrics->errorRate,
            'availability' => $metrics->availability
        ]);

        return $metrics;
    }

    public function alertOnFailures(string $supplier, array $failures): void
    {
        $supplierModel = $this->supplierRepository->findByName($supplier);
        
        if (!$supplierModel) {
            Log::warning("Supplier '{$supplier}' not found for alerting");
            return;
        }

        if (empty($failures)) {
            return;
        }

        $severity = $this->calculateSeverity($failures);
        $incidentType = $this->determineIncidentType($failures);
        
        $activeIncidents = $this->incidentRepository->findActiveIncidents($supplierModel->id);
        $existingIncident = $activeIncidents->where('type', $incidentType)->first();

        if (!$existingIncident) {
            $incident = $this->incidentRepository->create([
                'supplier_id' => $supplierModel->id,
                'type' => $incidentType,
                'severity' => $severity,
                'description' => $this->buildIncidentDescription($supplier, $failures),
                'started_at' => now(),
                'metadata' => [
                    'failures' => $failures,
                    'failure_count' => count($failures)
                ]
            ]);

            Log::error("New incident created for supplier '{$supplier}'", [
                'incident_id' => $incident->id,
                'type' => $incidentType,
                'severity' => $severity,
                'failure_count' => count($failures)
            ]);

            $this->sendAlert($supplierModel, $failures, $severity);
        } else {
            Log::info("Existing incident found for supplier '{$supplier}', updating metadata");
            $existingIncident->update([
                'metadata' => array_merge(
                    $existingIncident->metadata ?? [],
                    ['latest_failures' => $failures, 'updated_at' => now()]
                )
            ]);
        }
    }

    public function generateUptimeReport(string $supplier): UptimeReport
    {
        $supplierModel = $this->supplierRepository->findByName($supplier);
        
        if (!$supplierModel) {
            Log::warning("Supplier '{$supplier}' not found for uptime report");
            return UptimeReport::fromIncidents($supplier, [], 0);
        }

        $periodStart = now()->subDays(30);
        $periodEnd = now();
        $totalPeriodMinutes = $periodStart->diffInMinutes($periodEnd);

        $incidents = $this->incidentRepository->findByDateRange($periodStart, $periodEnd)
            ->where('supplier_id', $supplierModel->id)
            ->map(function ($incident) {
                return [
                    'id' => $incident->id,
                    'type' => $incident->type,
                    'severity' => $incident->severity,
                    'started_at' => $incident->started_at,
                    'resolved_at' => $incident->resolved_at,
                    'duration_minutes' => $incident->duration_minutes ?? 0,
                    'description' => $incident->description
                ];
            })
            ->toArray();

        $report = UptimeReport::fromIncidents(
            $supplier,
            $incidents,
            $totalPeriodMinutes,
            $periodStart,
            $periodEnd
        );

        Log::info("Uptime report generated for supplier '{$supplier}'", [
            'uptime_percentage' => $report->uptimePercentage,
            'incident_count' => $report->incidentCount,
            'total_downtime_minutes' => $report->totalDowntimeMinutes
        ]);

        return $report;
    }

    private function performHealthCheck($supplier): HealthStatus
    {
        try {
            $response = Http::timeout($supplier->timeout_seconds)
                ->withHeaders($supplier->headers ?? [])
                ->get($supplier->full_health_url);

            $responseTime = 0; // HTTP facade doesn't expose timing directly
            $statusCode = $response->status();
            $headers = $response->headers();

            if ($response->successful()) {
                return HealthStatus::healthy(
                    $supplier->name,
                    $responseTime,
                    $statusCode
                );
            }

            return HealthStatus::unhealthy(
                $supplier->name,
                $responseTime,
                $statusCode,
                "HTTP {$statusCode}: " . $response->body()
            );

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return HealthStatus::timeout(
                $supplier->name,
                $supplier->timeout_seconds * 1000,
                "Connection timeout: " . $e->getMessage()
            );
        } catch (\Exception $e) {
            return HealthStatus::error(
                $supplier->name,
                "Request failed: " . $e->getMessage()
            );
        }
    }

    private function calculateSeverity(array $failures): string
    {
        $failureCount = count($failures);
        
        return match (true) {
            $failureCount >= 5 => 'critical',
            $failureCount >= 3 => 'high',
            $failureCount >= 2 => 'medium',
            default => 'low'
        };
    }

    private function determineIncidentType(array $failures): string
    {
        $timeouts = collect($failures)->where('type', 'timeout')->count();
        $errors = collect($failures)->where('type', 'error')->count();
        
        if ($timeouts > $errors) {
            return 'timeout';
        }
        
        if ($errors > 0) {
            return 'error_rate';
        }
        
        return 'downtime';
    }

    private function buildIncidentDescription(string $supplier, array $failures): string
    {
        $failureCount = count($failures);
        $types = collect($failures)->pluck('type')->unique()->implode(', ');
        
        return "Supplier '{$supplier}' experiencing {$failureCount} consecutive failures. Types: {$types}";
    }

    private function sendAlert($supplier, array $failures, string $severity): void
    {
        try {
            Notification::route('mail', config('monitoring.alert_email', 'admin@example.com'))
                ->notify(new SupplierApiFailure($supplier, $failures, $severity));
                
            Log::info("Alert notification sent for supplier '{$supplier->name}'");
        } catch (\Exception $e) {
            Log::error("Failed to send alert notification for supplier '{$supplier->name}': " . $e->getMessage());
        }
    }
}