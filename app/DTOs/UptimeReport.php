<?php

namespace App\DTOs;

readonly class UptimeReport
{
    public function __construct(
        public string $supplier,
        public float $uptimePercentage,
        public int $totalDowntimeMinutes,
        public int $incidentCount,
        public float $mttr, // Mean Time To Recovery in minutes
        public float $mtbf, // Mean Time Between Failures in hours
        public array $incidents = [],
        public array $dailyUptime = [],
        public string $slaStatus = 'unknown',
        public ?\DateTime $reportPeriodStart = null,
        public ?\DateTime $reportPeriodEnd = null
    ) {}

    public static function fromIncidents(
        string $supplier,
        array $incidents,
        int $totalPeriodMinutes,
        ?\DateTime $periodStart = null,
        ?\DateTime $periodEnd = null
    ): self {
        $incidentCount = count($incidents);
        $totalDowntimeMinutes = array_sum(array_column($incidents, 'duration_minutes'));
        $uptimeMinutes = $totalPeriodMinutes - $totalDowntimeMinutes;
        $uptimePercentage = ($uptimeMinutes / $totalPeriodMinutes) * 100;

        $mttr = $incidentCount > 0 
            ? $totalDowntimeMinutes / $incidentCount 
            : 0;

        $mtbf = $incidentCount > 1 
            ? ($totalPeriodMinutes / 60) / ($incidentCount - 1)
            : 0;

        $slaStatus = self::calculateSlaStatus($uptimePercentage);

        return new self(
            supplier: $supplier,
            uptimePercentage: $uptimePercentage,
            totalDowntimeMinutes: $totalDowntimeMinutes,
            incidentCount: $incidentCount,
            mttr: $mttr,
            mtbf: $mtbf,
            incidents: $incidents,
            slaStatus: $slaStatus,
            reportPeriodStart: $periodStart,
            reportPeriodEnd: $periodEnd
        );
    }

    private static function calculateSlaStatus(float $uptimePercentage): string
    {
        return match (true) {
            $uptimePercentage >= 99.95 => 'excellent',
            $uptimePercentage >= 99.9 => 'good',
            $uptimePercentage >= 99.0 => 'acceptable',
            $uptimePercentage >= 95.0 => 'poor',
            default => 'critical'
        };
    }

    public function isSlaMet(float $slaThreshold = 99.9): bool
    {
        return $this->uptimePercentage >= $slaThreshold;
    }

    public function getDowntimeFormatted(): string
    {
        $hours = intval($this->totalDowntimeMinutes / 60);
        $minutes = $this->totalDowntimeMinutes % 60;
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        
        return "{$minutes}m";
    }

    public function toArray(): array
    {
        return [
            'supplier' => $this->supplier,
            'uptime_percentage' => round($this->uptimePercentage, 3),
            'total_downtime_minutes' => $this->totalDowntimeMinutes,
            'downtime_formatted' => $this->getDowntimeFormatted(),
            'incident_count' => $this->incidentCount,
            'mttr_minutes' => round($this->mttr, 2),
            'mtbf_hours' => round($this->mtbf, 2),
            'sla_status' => $this->slaStatus,
            'sla_met_99_9' => $this->isSlaMet(99.9),
            'sla_met_99_0' => $this->isSlaMet(99.0),
            'incidents' => $this->incidents,
            'daily_uptime' => $this->dailyUptime,
            'report_period_start' => $this->reportPeriodStart?->format('Y-m-d H:i:s'),
            'report_period_end' => $this->reportPeriodEnd?->format('Y-m-d H:i:s')
        ];
    }
}