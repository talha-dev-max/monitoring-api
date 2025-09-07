<?php

namespace App\DTOs;

readonly class PerformanceMetrics
{
    public function __construct(
        public string $supplier,
        public float $avgResponseTimeMs,
        public int $minResponseTimeMs,
        public int $maxResponseTimeMs,
        public float $percentile95Ms,
        public float $percentile99Ms,
        public float $errorRate,
        public int $totalRequests,
        public int $successfulRequests,
        public int $failedRequests,
        public float $availability,
        public array $trends = []
    ) {}

    public static function fromHealthChecks(string $supplier, array $healthChecks): self
    {
        if (empty($healthChecks)) {
            return new self(
                supplier: $supplier,
                avgResponseTimeMs: 0.0,
                minResponseTimeMs: 0,
                maxResponseTimeMs: 0,
                percentile95Ms: 0.0,
                percentile99Ms: 0.0,
                errorRate: 0.0,
                totalRequests: 0,
                successfulRequests: 0,
                failedRequests: 0,
                availability: 0.0
            );
        }

        $responseTimes = array_map(fn($check) => $check['response_time_ms'], $healthChecks);
        $successfulCount = count(array_filter($healthChecks, fn($check) => $check['status'] === 'healthy'));
        $totalCount = count($healthChecks);

        sort($responseTimes);

        return new self(
            supplier: $supplier,
            avgResponseTimeMs: array_sum($responseTimes) / count($responseTimes),
            minResponseTimeMs: min($responseTimes),
            maxResponseTimeMs: max($responseTimes),
            percentile95Ms: self::percentile($responseTimes, 95),
            percentile99Ms: self::percentile($responseTimes, 99),
            errorRate: (($totalCount - $successfulCount) / $totalCount) * 100,
            totalRequests: $totalCount,
            successfulRequests: $successfulCount,
            failedRequests: $totalCount - $successfulCount,
            availability: ($successfulCount / $totalCount) * 100
        );
    }

    private static function percentile(array $values, float $percentile): float
    {
        if (empty($values)) {
            return 0.0;
        }

        $index = ($percentile / 100) * (count($values) - 1);
        $lower = floor($index);
        $upper = ceil($index);

        if ($lower === $upper) {
            return $values[$lower];
        }

        return $values[$lower] + ($index - $lower) * ($values[$upper] - $values[$lower]);
    }

    public function toArray(): array
    {
        return [
            'supplier' => $this->supplier,
            'avg_response_time_ms' => round($this->avgResponseTimeMs, 2),
            'min_response_time_ms' => $this->minResponseTimeMs,
            'max_response_time_ms' => $this->maxResponseTimeMs,
            'percentile_95_ms' => round($this->percentile95Ms, 2),
            'percentile_99_ms' => round($this->percentile99Ms, 2),
            'error_rate_percent' => round($this->errorRate, 2),
            'total_requests' => $this->totalRequests,
            'successful_requests' => $this->successfulRequests,
            'failed_requests' => $this->failedRequests,
            'availability_percent' => round($this->availability, 2),
            'trends' => $this->trends
        ];
    }
}