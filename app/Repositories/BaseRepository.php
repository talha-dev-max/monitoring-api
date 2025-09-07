<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function findAll(): Collection
    {
        return $this->model->all();
    }

    public function findById(int $id): ?Model
    {
        return $this->model->find($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): ?Model
    {
        $record = $this->findById($id);
        
        if (!$record) {
            return null;
        }

        $record->update($data);
        
        return $record->fresh();
    }

    public function delete(int $id): bool
    {
        $record = $this->findById($id);
        
        if (!$record) {
            return false;
        }

        return $record->delete();
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->paginate($perPage, $columns);
    }

    public function findByField(string $field, $value): ?Model
    {
        return $this->model->where($field, $value)->first();
    }

    public function findByFields(array $criteria): Collection
    {
        $query = $this->model->newQuery();
        
        foreach ($criteria as $field => $value) {
            $query->where($field, $value);
        }
        
        return $query->get();
    }

    protected function getModel(): Model
    {
        return $this->model;
    }
}