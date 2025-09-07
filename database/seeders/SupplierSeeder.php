<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Repositories\SupplierRepositoryInterface;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function __construct(
        private SupplierRepositoryInterface $supplierRepository
    ) {}

    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'stripe_payment',
                'base_url' => 'https://api.stripe.com',
                'health_endpoint' => '/v1/account',
                'status' => 'active',
                'timeout_seconds' => 30,
                'retry_count' => 3,
                'headers' => [
                    'Authorization' => 'Bearer sk_test_...',
                    'Content-Type' => 'application/json'
                ],
                'is_backup' => false,
                'priority' => 1
            ],
            [
                'name' => 'paypal_payment',
                'base_url' => 'https://api.sandbox.paypal.com',
                'health_endpoint' => '/v1/oauth2/token',
                'status' => 'active',
                'timeout_seconds' => 25,
                'retry_count' => 3,
                'headers' => [
                    'Accept' => 'application/json',
                    'Accept-Language' => 'en_US'
                ],
                'is_backup' => true,
                'priority' => 2
            ],
            [
                'name' => 'fedex_shipping',
                'base_url' => 'https://apis-sandbox.fedex.com',
                'health_endpoint' => '/ship/v1/shipments',
                'status' => 'active',
                'timeout_seconds' => 35,
                'retry_count' => 2,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-locale' => 'en_US'
                ],
                'is_backup' => false,
                'priority' => 1
            ],
            [
                'name' => 'ups_shipping',
                'base_url' => 'https://wwwcie.ups.com',
                'health_endpoint' => '/api/shipments/v1/ship',
                'status' => 'active',
                'timeout_seconds' => 30,
                'retry_count' => 3,
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'is_backup' => true,
                'priority' => 2
            ],
            [
                'name' => 'inventory_service',
                'base_url' => 'https://api.inventory-system.com',
                'health_endpoint' => '/health',
                'status' => 'active',
                'timeout_seconds' => 20,
                'retry_count' => 3,
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'is_backup' => false,
                'priority' => 1
            ],
            [
                'name' => 'product_catalog',
                'base_url' => 'https://catalog-api.example.com',
                'health_endpoint' => '/api/v2/health',
                'status' => 'active',
                'timeout_seconds' => 25,
                'retry_count' => 2,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'API-Version' => '2.0'
                ],
                'is_backup' => false,
                'priority' => 1
            ],
            [
                'name' => 'notification_service',
                'base_url' => 'https://notifications.example.com',
                'health_endpoint' => '/status',
                'status' => 'active',
                'timeout_seconds' => 15,
                'retry_count' => 4,
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'is_backup' => false,
                'priority' => 1
            ],
            [
                'name' => 'analytics_service',
                'base_url' => 'https://analytics-api.example.com',
                'health_endpoint' => '/v1/health',
                'status' => 'maintenance',
                'timeout_seconds' => 40,
                'retry_count' => 2,
                'headers' => [
                    'Authorization' => 'Bearer token123',
                    'Content-Type' => 'application/json'
                ],
                'is_backup' => false,
                'priority' => 3
            ],
            [
                'name' => 'backup_payment',
                'base_url' => 'https://backup-payment.example.com',
                'health_endpoint' => '/ping',
                'status' => 'active',
                'timeout_seconds' => 20,
                'retry_count' => 5,
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'is_backup' => true,
                'priority' => 3
            ],
            [
                'name' => 'external_api',
                'base_url' => 'https://httpbin.org',
                'health_endpoint' => '/status/200',
                'status' => 'active',
                'timeout_seconds' => 10,
                'retry_count' => 2,
                'headers' => [],
                'is_backup' => false,
                'priority' => 1
            ]
        ];

        $this->command->info('🚀 Starting supplier seeding with create/update operations...');

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($suppliers as $supplierData) {
            $existingSupplier = $this->supplierRepository->findByName($supplierData['name']);
            
            if ($existingSupplier) {
                // Update existing supplier
                $this->updateSupplier($existingSupplier, $supplierData);
                $updatedCount++;
                $this->command->line("🔄 Updated: {$supplierData['name']}");
            } else {
                // Create new supplier
                $this->createSupplier($supplierData);
                $createdCount++;
                $this->command->line("✅ Created: {$supplierData['name']}");
            }
        }

        $this->command->info("📊 Seeding completed: {$createdCount} created, {$updatedCount} updated");

        // Demonstrate additional create/update operations
        $this->demonstrateAdditionalOperations();
    }

    private function createSupplier(array $supplierData): Supplier
    {
        return $this->supplierRepository->create($supplierData);
    }

    private function updateSupplier(Supplier $supplier, array $supplierData): ?Supplier
    {
        // Only update if there are actual changes
        $hasChanges = false;
        $updates = [];

        foreach ($supplierData as $key => $value) {
            if ($supplier->getAttribute($key) !== $value) {
                $updates[$key] = $value;
                $hasChanges = true;
            }
        }

        if ($hasChanges) {
            return $this->supplierRepository->update($supplier->id, $updates);
        }

        return $supplier;
    }

    private function demonstrateAdditionalOperations(): void
    {
        $this->command->info('🎯 Demonstrating additional repository operations...');

        // Create a new test supplier
        $testSupplier = $this->createSupplier([
            'name' => 'test_api_service',
            'base_url' => 'https://jsonplaceholder.typicode.com',
            'health_endpoint' => '/posts/1',
            'status' => 'active',
            'timeout_seconds' => 15,
            'retry_count' => 2,
            'headers' => ['Accept' => 'application/json'],
            'is_backup' => false,
            'priority' => 1
        ]);
        $this->command->line("✅ Created test supplier: {$testSupplier->name}");

        // Update the test supplier
        $updatedSupplier = $this->supplierRepository->update($testSupplier->id, [
            'timeout_seconds' => 30,
            'status' => 'maintenance',
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'Monitoring-System/1.0'
            ]
        ]);
        
        if ($updatedSupplier) {
            $this->command->line("🔄 Updated test supplier timeout: {$updatedSupplier->timeout_seconds}s");
        }

        // Demonstrate repository queries
        $activeSuppliers = $this->supplierRepository->findActive();
        $this->command->line("📊 Active suppliers count: {$activeSuppliers->count()}");

        $backupSuppliers = $this->supplierRepository->findBackupSuppliers();
        $this->command->line("🔄 Backup suppliers count: {$backupSuppliers->count()}");

        // Create another supplier for demonstration
        $this->createSupplier([
            'name' => 'weather_api',
            'base_url' => 'https://api.openweathermap.org',
            'health_endpoint' => '/data/2.5/weather?q=London&appid=demo',
            'status' => 'inactive',
            'timeout_seconds' => 20,
            'retry_count' => 3,
            'headers' => [],
            'is_backup' => false,
            'priority' => 2
        ]);
        $this->command->line("✅ Created weather API supplier");

        $this->command->info('🎉 Repository operations demonstration completed!');
    }
}
