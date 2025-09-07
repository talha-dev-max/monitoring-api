<?php

namespace App\Repositories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;

class SupplierRepository extends BaseRepository implements SupplierRepositoryInterface
{
    public function __construct(Supplier $model)
    {
        parent::__construct($model);
    }

    public function findById(int $id): ?Supplier
    {
        return parent::findById($id);
    }

    public function findByName(string $name): ?Supplier
    {
        return $this->findByField('name', $name);
    }

    public function findActive(): Collection
    {
        return $this->findByFields(['status' => 'active']);
    }

    public function findBackupSuppliers(): Collection
    {
        return $this->getModel()
            ->where('is_backup', true)
            ->where('status', 'active')
            ->orderBy('priority')
            ->get();
    }

    public function create(array $data): Supplier
    {
        return parent::create($data);
    }

    public function update(int $id, array $data): ?Supplier
    {
        return parent::update($id, $data);
    }
}