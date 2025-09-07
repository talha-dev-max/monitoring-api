<?php

namespace Tests\Unit;

use App\DTOs\HealthStatus;
use App\DTOs\PerformanceMetrics;
use App\DTOs\UptimeReport;
use App\Models\Supplier;
use App\Services\Monitoring\ApiMonitoringService;
use App\Repositories\SupplierRepositoryInterface;
use App\Repositories\HealthCheckRepositoryInterface;
use App\Repositories\IncidentRepositoryInterface;
use Tests\TestCase;
use Mockery;

class ApiMonitoringServiceTest extends TestCase
{
    private $supplierRepository;
    private $healthCheckRepository;
    private $incidentRepository;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->supplierRepository = Mockery::mock(SupplierRepositoryInterface::class);
        $this->healthCheckRepository = Mockery::mock(HealthCheckRepositoryInterface::class);
        $this->incidentRepository = Mockery::mock(IncidentRepositoryInterface::class);
        
        $this->service = new ApiMonitoringService(
            $this->supplierRepository,
            $this->healthCheckRepository,
            $this->incidentRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_monitor_api_health_returns_error_when_supplier_not_found(): void
    {
        $this->supplierRepository
            ->shouldReceive('findByName')
            ->with('nonexistent_supplier')
            ->once()
            ->andReturn(null);

        $result = $this->service->monitorApiHealth('nonexistent_supplier');

        $this->assertInstanceOf(HealthStatus::class, $result);
        $this->assertEquals('error', $result->status);
        $this->assertEquals('Supplier not found', $result->errorMessage);
    }

    public function test_monitor_api_health_skips_inactive_supplier(): void
    {
        $supplier = new Supplier([
            'name' => 'inactive_supplier',
            'status' => 'inactive'
        ]);

        $this->supplierRepository
            ->shouldReceive('findByName')
            ->with('inactive_supplier')
            ->once()
            ->andReturn($supplier);

        $result = $this->service->monitorApiHealth('inactive_supplier');

        $this->assertInstanceOf(HealthStatus::class, $result);
        $this->assertEquals('error', $result->status);
        $this->assertEquals('Supplier is not active', $result->errorMessage);
    }

    public function test_track_response_times_returns_empty_metrics_for_nonexistent_supplier(): void
    {
        $this->supplierRepository
            ->shouldReceive('findByName')
            ->with('nonexistent')
            ->once()
            ->andReturn(null);

        $result = $this->service->trackResponseTimes('nonexistent');

        $this->assertInstanceOf(PerformanceMetrics::class, $result);
        $this->assertEquals('nonexistent', $result->supplier);
        $this->assertEquals(0.0, $result->avgResponseTimeMs);
        $this->assertEquals(0, $result->totalRequests);
    }

    public function test_track_response_times_calculates_metrics_correctly(): void
    {
        $supplier = new Supplier(['name' => 'test_supplier']);
        $supplier->id = 1; // Set ID directly since it's not fillable
        
        $this->supplierRepository
            ->shouldReceive('findByName')
            ->with('test_supplier')
            ->once()
            ->andReturn($supplier);

        $healthChecks = new \Illuminate\Database\Eloquent\Collection([
            (object) ['response_time_ms' => 100, 'status' => 'healthy', 'checked_at' => now()],
            (object) ['response_time_ms' => 200, 'status' => 'healthy', 'checked_at' => now()],
            (object) ['response_time_ms' => 300, 'status' => 'unhealthy', 'checked_at' => now()],
        ]);

        $this->healthCheckRepository
            ->shouldReceive('findRecentBySupplier')
            ->with(1, 24)
            ->once()
            ->andReturn($healthChecks);

        $result = $this->service->trackResponseTimes('test_supplier');

        $this->assertInstanceOf(PerformanceMetrics::class, $result);
        $this->assertEquals('test_supplier', $result->supplier);
        $this->assertEquals(200.0, $result->avgResponseTimeMs);
        $this->assertEquals(3, $result->totalRequests);
        $this->assertEquals(2, $result->successfulRequests);
        $this->assertEquals(1, $result->failedRequests);
    }

    public function test_alert_on_failures_does_nothing_for_empty_failures(): void
    {
        $supplier = new Supplier(['name' => 'test_supplier']);
        $supplier->id = 1; // Set ID directly since it's not fillable
        
        $this->supplierRepository
            ->shouldReceive('findByName')
            ->with('test_supplier')
            ->once()
            ->andReturn($supplier);

        $this->service->alertOnFailures('test_supplier', []);
        
        $this->assertTrue(true);
    }

    public function test_health_status_dto_methods(): void
    {
        $healthyStatus = HealthStatus::healthy('test', 100, 200);
        $this->assertTrue($healthyStatus->isHealthy());
        $this->assertEquals('healthy', $healthyStatus->status);

        $unhealthyStatus = HealthStatus::unhealthy('test', 500, 500, 'Server error');
        $this->assertFalse($unhealthyStatus->isHealthy());
        $this->assertEquals('unhealthy', $unhealthyStatus->status);

        $timeoutStatus = HealthStatus::timeout('test', 30000);
        $this->assertFalse($timeoutStatus->isHealthy());
        $this->assertEquals('timeout', $timeoutStatus->status);

        $errorStatus = HealthStatus::error('test', 'Connection failed');
        $this->assertFalse($errorStatus->isHealthy());
        $this->assertEquals('error', $errorStatus->status);
    }

    public function test_performance_metrics_from_empty_health_checks(): void
    {
        $metrics = PerformanceMetrics::fromHealthChecks('test_supplier', []);
        
        $this->assertEquals('test_supplier', $metrics->supplier);
        $this->assertEquals(0.0, $metrics->avgResponseTimeMs);
        $this->assertEquals(0, $metrics->totalRequests);
        $this->assertEquals(0.0, $metrics->errorRate);
        $this->assertEquals(0.0, $metrics->availability);
    }

    public function test_uptime_report_sla_status(): void
    {
        $report = UptimeReport::fromIncidents('test', [], 43200, now()->subDays(30), now());
        
        $this->assertEquals('excellent', $report->slaStatus);
        $this->assertTrue($report->isSlaMet(99.9));
        $this->assertTrue($report->isSlaMet(99.0));
    }
}
