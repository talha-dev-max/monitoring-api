<?php

namespace App\Notifications;

use App\Models\Supplier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupplierApiFailure extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Supplier $supplier,
        private array $failures,
        private string $severity
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $failureCount = count($this->failures);
        $subject = "🚨 {$this->severity} Alert: {$this->supplier->name} API Failure";
        
        $message = (new MailMessage)
            ->error()
            ->subject($subject)
            ->greeting("API Monitoring Alert")
            ->line("**Supplier**: {$this->supplier->name}")
            ->line("**Severity**: " . ucfirst($this->severity))
            ->line("**Failure Count**: {$failureCount}")
            ->line("**Base URL**: {$this->supplier->base_url}");

        if (!empty($this->failures)) {
            $message->line("**Recent Failures:**");
            foreach (array_slice($this->failures, 0, 5) as $failure) {
                $time = $failure['timestamp'] ?? 'Unknown time';
                $error = $failure['error'] ?? 'Unknown error';
                $message->line("- {$time}: {$error}");
            }
        }

        $message->action('View Monitoring Dashboard', url('/monitoring'))
            ->line('Please investigate this issue immediately.');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'supplier_id' => $this->supplier->id,
            'supplier_name' => $this->supplier->name,
            'severity' => $this->severity,
            'failure_count' => count($this->failures),
            'failures' => $this->failures
        ];
    }
}
