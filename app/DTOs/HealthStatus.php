<?php

namespace App\DTOs;

readonly class HealthStatus
{
    public function __construct(
        public string $supplier,
        public string $status,
        public int $responseTimeMs,
        public int $statusCode,
        public ?\DateTime $timestamp = null,
        public ?string $errorMessage = null,
        public ?array $responseHeaders = null
    ) {}

    public static function healthy(string $supplier, int $responseTimeMs, int $statusCode = 200): self
    {
        return new self(
            supplier: $supplier,
            status: 'healthy',
            responseTimeMs: $responseTimeMs,
            statusCode: $statusCode,
            timestamp: new \DateTime()
        );
    }

    public static function unhealthy(string $supplier, int $responseTimeMs, int $statusCode, string $errorMessage): self
    {
        return new self(
            supplier: $supplier,
            status: 'unhealthy',
            responseTimeMs: $responseTimeMs,
            statusCode: $statusCode,
            timestamp: new \DateTime(),
            errorMessage: $errorMessage
        );
    }

    public static function timeout(string $supplier, int $responseTimeMs, string $errorMessage = 'Request timeout'): self
    {
        return new self(
            supplier: $supplier,
            status: 'timeout',
            responseTimeMs: $responseTimeMs,
            statusCode: 0,
            timestamp: new \DateTime(),
            errorMessage: $errorMessage
        );
    }

    public static function error(string $supplier, string $errorMessage): self
    {
        return new self(
            supplier: $supplier,
            status: 'error',
            responseTimeMs: 0,
            statusCode: 0,
            timestamp: new \DateTime(),
            errorMessage: $errorMessage
        );
    }

    public function isHealthy(): bool
    {
        return $this->status === 'healthy';
    }

    public function toArray(): array
    {
        return [
            'supplier' => $this->supplier,
            'status' => $this->status,
            'response_time_ms' => $this->responseTimeMs,
            'status_code' => $this->statusCode,
            'timestamp' => $this->timestamp?->format('Y-m-d H:i:s'),
            'error_message' => $this->errorMessage,
            'response_headers' => $this->responseHeaders
        ];
    }
}