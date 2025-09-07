<?php

namespace App\Console\Commands;

use App\Services\Monitoring\ApiMonitoringService;
use App\Repositories\SupplierRepositoryInterface;
use Illuminate\Console\Command;

class MonitorSupplierApis extends Command
{
    protected $signature = 'monitor:suppliers 
                            {--supplier= : Monitor specific supplier by name}
                            {--timeout=300 : Timeout for the monitoring process}
                            {--alert : Send alerts for failures}';

    protected $description = 'Monitor supplier API health and performance';

    public function __construct(
        private ApiMonitoringService $monitoringService,
        private SupplierRepositoryInterface $supplierRepository
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🚀 Starting supplier API monitoring...');
        
        $specificSupplier = $this->option('supplier');
        $shouldAlert = $this->option('alert');
        
        if ($specificSupplier) {
            return $this->monitorSpecificSupplier($specificSupplier, $shouldAlert);
        }

        return $this->monitorAllSuppliers($shouldAlert);
    }

    private function monitorSpecificSupplier(string $supplierName, bool $shouldAlert): int
    {
        $this->info("Monitoring supplier: {$supplierName}");

        $healthStatus = $this->monitoringService->monitorApiHealth($supplierName);
        
        $this->displayHealthStatus($supplierName, $healthStatus);

        if (!$healthStatus->isHealthy() && $shouldAlert) {
            $this->handleFailure($supplierName, $healthStatus);
        }

        return Command::SUCCESS;
    }

    private function monitorAllSuppliers(bool $shouldAlert): int
    {
        $suppliers = $this->supplierRepository->findActive();
        
        if ($suppliers->isEmpty()) {
            $this->warn('No active suppliers found.');
            return Command::FAILURE;
        }

        $this->info("Found {$suppliers->count()} active suppliers to monitor");

        $healthyCount = 0;
        $unhealthyCount = 0;
        $failures = [];

        $progressBar = $this->output->createProgressBar($suppliers->count());
        $progressBar->start();

        foreach ($suppliers as $supplier) {
            $healthStatus = $this->monitoringService->monitorApiHealth($supplier->name);
            
            if ($healthStatus->isHealthy()) {
                $healthyCount++;
            } else {
                $unhealthyCount++;
                $failures[] = [
                    'supplier' => $supplier->name,
                    'status' => $healthStatus->status,
                    'error' => $healthStatus->errorMessage,
                    'timestamp' => now()->toDateTimeString()
                ];
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->displaySummary($healthyCount, $unhealthyCount);

        if (!empty($failures)) {
            $this->displayFailures($failures);
            
            if ($shouldAlert) {
                $this->sendAlertsForFailures($failures);
            }
        }

        return $unhealthyCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function displayHealthStatus(string $supplier, $healthStatus): void
    {
        $status = $healthStatus->isHealthy() ? '✅ Healthy' : '❌ Unhealthy';
        $responseTime = $healthStatus->responseTimeMs . 'ms';
        
        $this->table(
            ['Supplier', 'Status', 'Response Time', 'Status Code'],
            [[$supplier, $status, $responseTime, $healthStatus->statusCode]]
        );

        if ($healthStatus->errorMessage) {
            $this->error("Error: {$healthStatus->errorMessage}");
        }
    }

    private function displaySummary(int $healthy, int $unhealthy): void
    {
        $this->info('📊 Monitoring Summary:');
        $this->line("✅ Healthy suppliers: {$healthy}");
        $this->line("❌ Unhealthy suppliers: {$unhealthy}");
        $this->line("📈 Success rate: " . round(($healthy / ($healthy + $unhealthy)) * 100, 2) . '%');
    }

    private function displayFailures(array $failures): void
    {
        $this->error('🚨 Failed Suppliers:');
        
        $tableData = array_map(function ($failure) {
            return [
                $failure['supplier'],
                $failure['status'],
                $failure['error'] ?? 'N/A',
                $failure['timestamp']
            ];
        }, $failures);

        $this->table(
            ['Supplier', 'Status', 'Error', 'Timestamp'],
            $tableData
        );
    }

    private function handleFailure(string $supplier, $healthStatus): void
    {
        $failures = [[
            'supplier' => $supplier,
            'status' => $healthStatus->status,
            'error' => $healthStatus->errorMessage,
            'timestamp' => now()->toDateTimeString()
        ]];

        $this->monitoringService->alertOnFailures($supplier, $failures);
        $this->warn("Alert sent for failed supplier: {$supplier}");
    }

    private function sendAlertsForFailures(array $failures): void
    {
        $groupedFailures = collect($failures)->groupBy('supplier');
        
        foreach ($groupedFailures as $supplier => $supplierFailures) {
            $this->monitoringService->alertOnFailures($supplier, $supplierFailures->toArray());
        }
        
        $this->warn("Alerts sent for {$groupedFailures->count()} failed suppliers");
    }
}
