<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiHealthCheck extends Model
{
    protected $fillable = [
        'supplier_id',
        'response_time_ms',
        'status_code',
        'status',
        'error_message',
        'response_headers',
        'checked_at'
    ];

    protected $casts = [
        'response_headers' => 'array',
        'checked_at' => 'datetime'
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function isHealthy(): bool
    {
        return $this->status === 'healthy';
    }

    public function isTimeout(): bool
    {
        return $this->status === 'timeout';
    }

    public function scopeHealthy($query)
    {
        return $query->where('status', 'healthy');
    }

    public function scopeUnhealthy($query)
    {
        return $query->where('status', '!=', 'healthy');
    }

    public function scopeRecentChecks($query, int $hours = 24)
    {
        return $query->where('checked_at', '>=', now()->subHours($hours));
    }
}
