<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    public function findAll(): Collection;
    
    public function findById(int $id): ?Model;
    
    public function create(array $data): Model;
    
    public function update(int $id, array $data): ?Model;
    
    public function delete(int $id): bool;
    
    public function paginate(int $perPage = 15, array $columns = ['*']);
    
    public function findByField(string $field, $value): ?Model;
    
    public function findByFields(array $criteria): Collection;
}