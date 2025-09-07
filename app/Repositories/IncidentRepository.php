<?php

namespace App\Repositories;

use App\Models\ApiIncident;
use Illuminate\Database\Eloquent\Collection;

class IncidentRepository implements IncidentRepositoryInterface
{
    public function create(array $data): ApiIncident
    {
        return ApiIncident::create($data);
    }

    public function findById(int $id): ?ApiIncident
    {
        return ApiIncident::find($id);
    }

    public function findBySupplier(int $supplierId): Collection
    {
        return ApiIncident::where('supplier_id', $supplierId)
            ->orderBy('started_at', 'desc')
            ->get();
    }

    public function findActiveIncidents(int $supplierId): Collection
    {
        return ApiIncident::where('supplier_id', $supplierId)
            ->active()
            ->orderBy('started_at', 'desc')
            ->get();
    }

    public function findResolvedIncidents(int $supplierId): Collection
    {
        return ApiIncident::where('supplier_id', $supplierId)
            ->resolved()
            ->orderBy('resolved_at', 'desc')
            ->get();
    }

    public function findBySeverity(string $severity): Collection
    {
        return ApiIncident::bySeverity($severity)
            ->orderBy('started_at', 'desc')
            ->get();
    }

    public function findByType(string $type): Collection
    {
        return ApiIncident::byType($type)
            ->orderBy('started_at', 'desc')
            ->get();
    }

    public function findByDateRange(\DateTime $start, \DateTime $end): Collection
    {
        return ApiIncident::whereBetween('started_at', [$start, $end])
            ->orderBy('started_at', 'desc')
            ->get();
    }

    public function resolveIncident(int $id): ?ApiIncident
    {
        $incident = $this->findById($id);
        
        if (!$incident || $incident->isResolved()) {
            return null;
        }

        return $incident->resolve();
    }

    public function cleanup(int $daysToKeep = 90): int
    {
        return ApiIncident::where('started_at', '<', now()->subDays($daysToKeep))
            ->delete();
    }
}