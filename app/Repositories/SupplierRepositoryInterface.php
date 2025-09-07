<?php

namespace App\Repositories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;

interface SupplierRepositoryInterface extends BaseRepositoryInterface
{
    public function findById(int $id): ?Supplier;
    
    public function findByName(string $name): ?Supplier;
    
    public function findActive(): Collection;
    
    public function findBackupSuppliers(): Collection;
    
    public function create(array $data): Supplier;
    
    public function update(int $id, array $data): ?Supplier;
}