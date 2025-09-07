<?php

namespace Tests\Unit;

use App\Models\Supplier;
use App\Repositories\SupplierRepositoryInterface;
use App\Services\SupplierService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class SupplierServiceTest extends TestCase
{
    protected $supplierRepository;
    protected $supplierService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->supplierRepository = Mockery::mock(SupplierRepositoryInterface::class);
        $this->supplierService = new SupplierService($this->supplierRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_all_suppliers(): void
    {
        $suppliers = new Collection([
            new Supplier(['id' => 1, 'name' => 'Supplier 1']),
            new Supplier(['id' => 2, 'name' => 'Supplier 2'])
        ]);

        $this->supplierRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn($suppliers);

        $result = $this->supplierService->getAllSuppliers();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    public function test_get_paginated_suppliers(): void
    {
        $paginatedSuppliers = Mockery::mock(LengthAwarePaginator::class);

        $this->supplierRepository
            ->shouldReceive('paginate')
            ->once()
            ->with(10)
            ->andReturn($paginatedSuppliers);

        $result = $this->supplierService->getPaginatedSuppliers(10);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    public function test_get_supplier_by_id(): void
    {
        $supplier = new Supplier(['id' => 1, 'name' => 'Test Supplier']);
        $supplier->id = 1; // Explicitly set the ID since it's not fillable

        $this->supplierRepository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($supplier);

        $result = $this->supplierService->getSupplierById(1);

        $this->assertInstanceOf(Supplier::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('Test Supplier', $result->name);
    }

    public function test_get_supplier_by_id_returns_null_when_not_found(): void
    {
        $this->supplierRepository
            ->shouldReceive('findById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $result = $this->supplierService->getSupplierById(999);

        $this->assertNull($result);
    }

    public function test_get_supplier_by_name(): void
    {
        $supplier = new Supplier(['id' => 1, 'name' => 'Test Supplier']);

        $this->supplierRepository
            ->shouldReceive('findByName')
            ->once()
            ->with('Test Supplier')
            ->andReturn($supplier);

        $result = $this->supplierService->getSupplierByName('Test Supplier');

        $this->assertInstanceOf(Supplier::class, $result);
        $this->assertEquals('Test Supplier', $result->name);
    }

    public function test_get_active_suppliers(): void
    {
        $activeSuppliers = new Collection([
            new Supplier(['id' => 1, 'name' => 'Active Supplier 1', 'status' => 'active']),
            new Supplier(['id' => 2, 'name' => 'Active Supplier 2', 'status' => 'active'])
        ]);

        $this->supplierRepository
            ->shouldReceive('findActive')
            ->once()
            ->andReturn($activeSuppliers);

        $result = $this->supplierService->getActiveSuppliers();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    public function test_get_backup_suppliers(): void
    {
        $backupSuppliers = new Collection([
            new Supplier(['id' => 1, 'name' => 'Backup Supplier 1', 'is_backup' => true]),
            new Supplier(['id' => 2, 'name' => 'Backup Supplier 2', 'is_backup' => true])
        ]);

        $this->supplierRepository
            ->shouldReceive('findBackupSuppliers')
            ->once()
            ->andReturn($backupSuppliers);

        $result = $this->supplierService->getBackupSuppliers();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    public function test_create_supplier(): void
    {
        $supplierData = [
            'name' => 'New Supplier',
            'base_url' => 'https://api.example.com',
            'health_endpoint' => '/health',
            'status' => 'active',
            'timeout_seconds' => 30,
            'retry_count' => 3,
            'is_backup' => false
        ];

        $createdSupplier = new Supplier(array_merge($supplierData, ['id' => 1]));
        $createdSupplier->id = 1;

        $this->supplierRepository
            ->shouldReceive('create')
            ->once()
            ->with($supplierData)
            ->andReturn($createdSupplier);

        $result = $this->supplierService->createSupplier($supplierData);

        $this->assertInstanceOf(Supplier::class, $result);
        $this->assertEquals('New Supplier', $result->name);
    }


    public function test_update_supplier(): void
    {
        $supplierData = [
            'name' => 'Updated Supplier',
            'base_url' => 'https://updated-api.example.com',
            'timeout_seconds' => 45
        ];

        $existingSupplier = new Supplier(['id' => 1, 'name' => 'Original Supplier']);
        $existingSupplier->id = 1;
        $updatedSupplier = new Supplier(['id' => 1, 'name' => 'Updated Supplier']);
        $updatedSupplier->id = 1;

        $this->supplierRepository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($existingSupplier);

        $this->supplierRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, $supplierData)
            ->andReturn($updatedSupplier);

        $result = $this->supplierService->updateSupplier(1, $supplierData);

        $this->assertInstanceOf(Supplier::class, $result);
        $this->assertEquals('Updated Supplier', $result->name);
    }

    public function test_update_supplier_returns_null_when_not_found(): void
    {
        $supplierData = ['name' => 'Updated Name'];

        $this->supplierRepository
            ->shouldReceive('findById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $result = $this->supplierService->updateSupplier(999, $supplierData);

        $this->assertNull($result);
    }

    public function test_delete_supplier(): void
    {
        $existingSupplier = new Supplier(['id' => 1, 'name' => 'Supplier to Delete']);
        $existingSupplier->id = 1;

        $this->supplierRepository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($existingSupplier);

        $this->supplierRepository
            ->shouldReceive('delete')
            ->once()
            ->with(1)
            ->andReturn(true);

        $result = $this->supplierService->deleteSupplier(1);

        $this->assertTrue($result);
    }

    public function test_delete_supplier_returns_false_when_not_found(): void
    {
        $this->supplierRepository
            ->shouldReceive('findById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $result = $this->supplierService->deleteSupplier(999);

        $this->assertFalse($result);
    }

    public function test_toggle_supplier_status(): void
    {
        $activeSupplier = new Supplier(['id' => 1, 'name' => 'Test Supplier', 'status' => 'active']);
        $activeSupplier->id = 1;
        $inactiveSupplier = new Supplier(['id' => 1, 'name' => 'Test Supplier', 'status' => 'inactive']);
        $inactiveSupplier->id = 1;

        $this->supplierRepository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($activeSupplier);

        $this->supplierRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, ['status' => 'inactive'])
            ->andReturn($inactiveSupplier);

        $result = $this->supplierService->toggleSupplierStatus(1);

        $this->assertInstanceOf(Supplier::class, $result);
        $this->assertEquals('inactive', $result->status);
    }

    public function test_toggle_supplier_status_returns_null_when_not_found(): void
    {
        $this->supplierRepository
            ->shouldReceive('findById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $result = $this->supplierService->toggleSupplierStatus(999);

        $this->assertNull($result);
    }
}