<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'base_url',
        'health_endpoint',
        'status',
        'timeout_seconds',
        'retry_count',
        'headers',
        'is_backup',
        'priority'
    ];

    protected $casts = [
        'headers' => 'array',
        'is_backup' => 'boolean'
    ];

    public function healthChecks(): HasMany
    {
        return $this->hasMany(ApiHealthCheck::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(ApiIncident::class);
    }

    public function recentHealthChecks(): HasMany
    {
        return $this->healthChecks()
            ->orderBy('checked_at', 'desc')
            ->limit(100);
    }

    public function getFullHealthUrlAttribute(): string
    {
        return rtrim($this->base_url, '/') . '/' . ltrim($this->health_endpoint, '/');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
