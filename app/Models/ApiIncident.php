<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiIncident extends Model
{
    protected $fillable = [
        'supplier_id',
        'type',
        'severity',
        'description',
        'started_at',
        'resolved_at',
        'duration_minutes',
        'metadata'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'resolved_at' => 'datetime',
        'metadata' => 'array'
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function isResolved(): bool
    {
        return !is_null($this->resolved_at);
    }

    public function resolve(): self
    {
        $this->resolved_at = now();
        $this->duration_minutes = $this->started_at->diffInMinutes($this->resolved_at);
        $this->save();
        
        return $this;
    }

    public function scopeActive($query)
    {
        return $query->whereNull('resolved_at');
    }

    public function scopeResolved($query)
    {
        return $query->whereNotNull('resolved_at');
    }

    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
