<?php

namespace Tests\Feature;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SupplierApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_can_get_all_suppliers(): void
    {
        Supplier::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/suppliers');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'base_url',
                            'health_endpoint',
                            'status',
                            'timeout_seconds',
                            'retry_count',
                            'headers',
                            'is_backup',
                            'priority',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'message'
                ])
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Suppliers retrieved successfully'
                ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_can_get_paginated_suppliers(): void
    {
        Supplier::factory()->count(20)->create();

        $response = $this->getJson('/api/v1/suppliers?paginate=true&per_page=5');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'data',
                        'current_page',
                        'per_page',
                        'total'
                    ],
                    'message'
                ]);

        $this->assertEquals(5, count($response->json('data.data')));
    }

    public function test_can_create_supplier(): void
    {
        $supplierData = [
            'name' => 'Test Supplier',
            'base_url' => 'https://api.example.com',
            'health_endpoint' => '/health',
            'status' => 'active',
            'timeout_seconds' => 30,
            'retry_count' => 3,
            'headers' => ['Authorization' => 'Bearer token'],
            'is_backup' => false,
            'priority' => 1
        ];

        $response = $this->postJson('/api/v1/suppliers', $supplierData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'id',
                        'name',
                        'base_url',
                        'health_endpoint',
                        'status',
                        'timeout_seconds',
                        'retry_count',
                        'headers',
                        'is_backup',
                        'priority',
                        'created_at',
                        'updated_at'
                    ],
                    'message'
                ])
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Supplier created successfully',
                    'data' => $supplierData
                ]);

        $this->assertDatabaseHas('suppliers', [
            'name' => 'Test Supplier',
            'base_url' => 'https://api.example.com'
        ]);
    }

    public function test_cannot_create_supplier_with_invalid_data(): void
    {
        $invalidData = [
            'name' => '', // Required field
            'base_url' => 'not-a-url', // Invalid URL
            'timeout_seconds' => -1, // Invalid timeout
        ];

        $response = $this->postJson('/api/v1/suppliers', $invalidData);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'errors'
                ])
                ->assertJson([
                    'status' => 'error',
                    'message' => 'Validation failed'
                ]);
    }

    public function test_cannot_create_supplier_with_duplicate_name(): void
    {
        $existingSupplier = Supplier::factory()->create(['name' => 'Unique Supplier']);

        $supplierData = [
            'name' => 'Unique Supplier', // Duplicate name
            'base_url' => 'https://api.example.com',
            'health_endpoint' => '/health',
            'status' => 'active',
            'timeout_seconds' => 30,
            'retry_count' => 3
        ];

        $response = $this->postJson('/api/v1/suppliers', $supplierData);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'errors'
                ]);
    }

    public function test_can_get_single_supplier(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->getJson("/api/v1/suppliers/{$supplier->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'id',
                        'name',
                        'base_url',
                        'health_endpoint',
                        'status',
                        'timeout_seconds',
                        'retry_count',
                        'headers',
                        'is_backup',
                        'priority',
                        'created_at',
                        'updated_at'
                    ],
                    'message'
                ])
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Supplier retrieved successfully',
                    'data' => [
                        'id' => $supplier->id,
                        'name' => $supplier->name
                    ]
                ]);
    }

    public function test_returns_404_when_supplier_not_found(): void
    {
        $response = $this->getJson('/api/v1/suppliers/999');

        $response->assertStatus(404)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'Supplier not found'
                ]);
    }

    public function test_can_update_supplier(): void
    {
        $supplier = Supplier::factory()->create();

        $updateData = [
            'name' => 'Updated Supplier Name',
            'base_url' => 'https://updated-api.example.com',
            'timeout_seconds' => 45
        ];

        $response = $this->putJson("/api/v1/suppliers/{$supplier->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data',
                    'message'
                ])
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Supplier updated successfully',
                    'data' => [
                        'id' => $supplier->id,
                        'name' => 'Updated Supplier Name',
                        'base_url' => 'https://updated-api.example.com',
                        'timeout_seconds' => 45
                    ]
                ]);

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Updated Supplier Name',
            'base_url' => 'https://updated-api.example.com'
        ]);
    }

    public function test_cannot_update_nonexistent_supplier(): void
    {
        $updateData = ['name' => 'Updated Name'];

        $response = $this->putJson('/api/v1/suppliers/999', $updateData);

        $response->assertStatus(404)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'Supplier not found'
                ]);
    }

    public function test_can_delete_supplier(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->deleteJson("/api/v1/suppliers/{$supplier->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Supplier deleted successfully'
                ]);

        $this->assertDatabaseMissing('suppliers', [
            'id' => $supplier->id
        ]);
    }

    public function test_cannot_delete_nonexistent_supplier(): void
    {
        $response = $this->deleteJson('/api/v1/suppliers/999');

        $response->assertStatus(404)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'Supplier not found'
                ]);
    }

    public function test_can_get_active_suppliers(): void
    {
        Supplier::factory()->count(2)->create(['status' => 'active']);
        Supplier::factory()->count(2)->create(['status' => 'inactive']);

        $response = $this->getJson('/api/v1/suppliers/active');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data',
                    'message'
                ])
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Active suppliers retrieved successfully'
                ]);

        $this->assertCount(2, $response->json('data'));

        foreach ($response->json('data') as $supplier) {
            $this->assertEquals('active', $supplier['status']);
        }
    }

    public function test_can_get_backup_suppliers(): void
    {
        Supplier::factory()->count(2)->create([
            'status' => 'active',
            'is_backup' => true,
            'priority' => 1
        ]);
        Supplier::factory()->count(2)->create([
            'status' => 'active',
            'is_backup' => false
        ]);

        $response = $this->getJson('/api/v1/suppliers/backup');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data',
                    'message'
                ])
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Backup suppliers retrieved successfully'
                ]);

        $this->assertCount(2, $response->json('data'));

        foreach ($response->json('data') as $supplier) {
            $this->assertEquals(true, $supplier['is_backup']);
            $this->assertEquals('active', $supplier['status']);
        }
    }

    public function test_can_toggle_supplier_status(): void
    {
        $supplier = Supplier::factory()->create(['status' => 'active']);

        $response = $this->patchJson("/api/v1/suppliers/{$supplier->id}/toggle-status");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data',
                    'message'
                ])
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Supplier status toggled successfully',
                    'data' => [
                        'id' => $supplier->id,
                        'status' => 'inactive'
                    ]
                ]);

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'status' => 'inactive'
        ]);
    }

    public function test_cannot_toggle_status_of_nonexistent_supplier(): void
    {
        $response = $this->patchJson('/api/v1/suppliers/999/toggle-status');

        $response->assertStatus(404)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'Supplier not found'
                ]);
    }
}
