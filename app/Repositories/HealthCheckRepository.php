<?php

namespace App\Repositories;

use App\Models\ApiHealthCheck;
use Illuminate\Database\Eloquent\Collection;

class HealthCheckRepository implements HealthCheckRepositoryInterface
{
    public function create(array $data): ApiHealthCheck
    {
        return ApiHealthCheck::create($data);
    }

    public function findBySupplier(int $supplierId, int $limit = 100): Collection
    {
        return ApiHealthCheck::where('supplier_id', $supplierId)
            ->orderBy('checked_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function findRecentBySupplier(int $supplierId, int $hours = 24): Collection
    {
        return ApiHealthCheck::where('supplier_id', $supplierId)
            ->recentChecks($hours)
            ->orderBy('checked_at', 'desc')
            ->get();
    }

    public function findHealthyChecks(int $supplierId, int $hours = 24): Collection
    {
        return ApiHealthCheck::where('supplier_id', $supplierId)
            ->recentChecks($hours)
            ->healthy()
            ->get();
    }

    public function findUnhealthyChecks(int $supplierId, int $hours = 24): Collection
    {
        return ApiHealthCheck::where('supplier_id', $supplierId)
            ->recentChecks($hours)
            ->unhealthy()
            ->get();
    }

    public function getAverageResponseTime(int $supplierId, int $hours = 24): float
    {
        return (float) ApiHealthCheck::where('supplier_id', $supplierId)
            ->recentChecks($hours)
            ->avg('response_time_ms') ?? 0.0;
    }

    public function getErrorRate(int $supplierId, int $hours = 24): float
    {
        $totalChecks = ApiHealthCheck::where('supplier_id', $supplierId)
            ->recentChecks($hours)
            ->count();

        if ($totalChecks === 0) {
            return 0.0;
        }

        $unhealthyChecks = ApiHealthCheck::where('supplier_id', $supplierId)
            ->recentChecks($hours)
            ->unhealthy()
            ->count();

        return ($unhealthyChecks / $totalChecks) * 100;
    }

    public function cleanup(int $daysToKeep = 30): int
    {
        return ApiHealthCheck::where('checked_at', '<', now()->subDays($daysToKeep))
            ->delete();
    }
}