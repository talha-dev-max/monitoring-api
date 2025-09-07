<?php

namespace Tests\Feature;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MonitoringSuppliersCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_monitor_suppliers_command_with_no_suppliers(): void
    {
        $this->artisan('monitor:suppliers')
            ->expectsOutput('No active suppliers found.')
            ->assertExitCode(1);
    }

    public function test_monitor_specific_supplier(): void
    {
        Http::fake([
            'https://httpbin.org/status/200' => Http::response('OK', 200)
        ]);

        Supplier::create([
            'name' => 'test_supplier',
            'base_url' => 'https://httpbin.org',
            'health_endpoint' => '/status/200',
            'status' => 'active',
            'timeout_seconds' => 30,
            'retry_count' => 3,
            'headers' => [],
            'is_backup' => false,
            'priority' => 1
        ]);

        $this->artisan('monitor:suppliers --supplier=test_supplier')
            ->expectsOutput('🚀 Starting supplier API monitoring...')
            ->expectsOutput('Monitoring supplier: test_supplier')
            ->assertExitCode(0);
    }

    public function test_monitor_nonexistent_supplier(): void
    {
        $this->artisan('monitor:suppliers --supplier=nonexistent')
            ->expectsOutput('🚀 Starting supplier API monitoring...')
            ->expectsOutput('Monitoring supplier: nonexistent')
            ->assertExitCode(0);
    }

    public function test_monitor_all_suppliers(): void
    {
        Http::fake([
            'https://httpbin.org/status/200' => Http::response('OK', 200),
            'https://httpbin.org/status/500' => Http::response('Server Error', 500)
        ]);

        Supplier::create([
            'name' => 'healthy_supplier',
            'base_url' => 'https://httpbin.org',
            'health_endpoint' => '/status/200',
            'status' => 'active',
            'timeout_seconds' => 30,
            'retry_count' => 3,
            'headers' => [],
            'is_backup' => false,
            'priority' => 1
        ]);

        Supplier::create([
            'name' => 'unhealthy_supplier',
            'base_url' => 'https://httpbin.org',
            'health_endpoint' => '/status/500',
            'status' => 'active',
            'timeout_seconds' => 30,
            'retry_count' => 3,
            'headers' => [],
            'is_backup' => false,
            'priority' => 1
        ]);

        $this->artisan('monitor:suppliers')
            ->expectsOutput('🚀 Starting supplier API monitoring...')
            ->expectsOutput('Found 2 active suppliers to monitor')
            ->expectsOutput('📊 Monitoring Summary:')
            ->assertExitCode(1); // Should fail due to unhealthy supplier
    }

    public function test_monitor_inactive_suppliers_are_skipped(): void
    {
        Supplier::create([
            'name' => 'inactive_supplier',
            'base_url' => 'https://httpbin.org',
            'health_endpoint' => '/status/200',
            'status' => 'inactive',
            'timeout_seconds' => 30,
            'retry_count' => 3,
            'headers' => [],
            'is_backup' => false,
            'priority' => 1
        ]);

        $this->artisan('monitor:suppliers')
            ->expectsOutput('No active suppliers found.')
            ->assertExitCode(1);
    }
}
