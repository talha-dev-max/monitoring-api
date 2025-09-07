<?php

namespace App\Repositories;

use App\Models\ApiIncident;
use Illuminate\Database\Eloquent\Collection;

interface IncidentRepositoryInterface
{
    public function create(array $data): ApiIncident;
    
    public function findById(int $id): ?ApiIncident;
    
    public function findBySupplier(int $supplierId): Collection;
    
    public function findActiveIncidents(int $supplierId): Collection;
    
    public function findResolvedIncidents(int $supplierId): Collection;
    
    public function findBySeverity(string $severity): Collection;
    
    public function findByType(string $type): Collection;
    
    public function findByDateRange(\DateTime $start, \DateTime $end): Collection;
    
    public function resolveIncident(int $id): ?ApiIncident;
    
    public function cleanup(int $daysToKeep = 90): int;
}