<?php

namespace App\Providers;

use App\Models\Supplier;
use App\Repositories\SupplierRepository;
use App\Repositories\SupplierRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SupplierRepositoryInterface::class, function ($app) {
            return new SupplierRepository(new Supplier());
        });
    }

    public function boot(): void
    {
        //
    }
}