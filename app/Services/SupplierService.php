<?php

namespace App\Services;

use App\Models\Supplier;
use App\Repositories\SupplierRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SupplierService
{
    protected SupplierRepositoryInterface $supplierRepository;

    public function __construct(SupplierRepositoryInterface $supplierRepository)
    {
        $this->supplierRepository = $supplierRepository;
    }

    public function getAllSuppliers(): Collection
    {
        return $this->supplierRepository->findAll();
    }

    public function getPaginatedSuppliers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->supplierRepository->paginate($perPage);
    }

    public function getSupplierById(int $id): ?Supplier
    {
        return $this->supplierRepository->findById($id);
    }

    public function getSupplierByName(string $name): ?Supplier
    {
        return $this->supplierRepository->findByName($name);
    }

    public function getActiveSuppliers(): Collection
    {
        return $this->supplierRepository->findActive();
    }

    public function getBackupSuppliers(): Collection
    {
        return $this->supplierRepository->findBackupSuppliers();
    }

    public function createSupplier(array $data): Supplier
    {
        return $this->supplierRepository->create($data);
    }

    public function updateSupplier(int $id, array $data): ?Supplier
    {
        $supplier = $this->getSupplierById($id);
        
        if (!$supplier) {
            return null;
        }
        
        return $this->supplierRepository->update($id, $data);
    }

    public function deleteSupplier(int $id): bool
    {
        $supplier = $this->getSupplierById($id);
        
        if (!$supplier) {
            return false;
        }

        return $this->supplierRepository->delete($id);
    }

    public function toggleSupplierStatus(int $id): ?Supplier
    {
        $supplier = $this->getSupplierById($id);
        
        if (!$supplier) {
            return null;
        }

        $newStatus = $supplier->status === 'active' ? 'inactive' : 'active';
        
        return $this->supplierRepository->update($id, ['status' => $newStatus]);
    }

}