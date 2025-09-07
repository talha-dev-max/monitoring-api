<?php

namespace App\Providers;

use App\Repositories\SupplierRepository;
use App\Repositories\SupplierRepositoryInterface;
use App\Repositories\HealthCheckRepository;
use App\Repositories\HealthCheckRepositoryInterface;
use App\Repositories\IncidentRepository;
use App\Repositories\IncidentRepositoryInterface;
use App\Services\Monitoring\ApiMonitoringService;
use Illuminate\Support\ServiceProvider;

class MonitoringServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SupplierRepositoryInterface::class, SupplierRepository::class);
        $this->app->bind(HealthCheckRepositoryInterface::class, HealthCheckRepository::class);
        $this->app->bind(IncidentRepositoryInterface::class, IncidentRepository::class);
        
        $this->app->singleton(ApiMonitoringService::class, function ($app) {
            return new ApiMonitoringService(
                $app->make(SupplierRepositoryInterface::class),
                $app->make(HealthCheckRepositoryInterface::class),
                $app->make(IncidentRepositoryInterface::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
