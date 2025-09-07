<?php

namespace App\Repositories;

use App\Models\ApiHealthCheck;
use Illuminate\Database\Eloquent\Collection;

interface HealthCheckRepositoryInterface
{
    public function create(array $data): ApiHealthCheck;
    
    public function findBySupplier(int $supplierId, int $limit = 100): Collection;
    
    public function findRecentBySupplier(int $supplierId, int $hours = 24): Collection;
    
    public function findHealthyChecks(int $supplierId, int $hours = 24): Collection;
    
    public function findUnhealthyChecks(int $supplierId, int $hours = 24): Collection;
    
    public function getAverageResponseTime(int $supplierId, int $hours = 24): float;
    
    public function getErrorRate(int $supplierId, int $hours = 24): float;
    
    public function cleanup(int $daysToKeep = 30): int;
}