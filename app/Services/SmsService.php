<?php

namespace App\Services;

use App\Models\SmsConfiguration;
use App\Models\SmsLog;
use App\Models\SmsTemplate;
use App\Models\User;
use App\Models\Order;
use App\Services\SMS\BulkSmsBdService;
use App\Services\SMS\TwilioService;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Get SMS provider instance.
     */
    protected function getProvider(?string $providerName = null)
    {
        $config = $providerName 
            ? SmsConfiguration::getByProvider($providerName)
            : SmsConfiguration::getDefault();

        if (!$config || !$config->is_active) {
            throw new \Exception('No active SMS provider configured');
        }

        return match($config->provider) {
            'bulksmsbd' => new BulkSmsBdService($config->credentials),
            'twilio' => new TwilioService($config->credentials),
            default => throw new \Exception('Unsupported SMS provider: ' . $config->provider),
        };
    }

    /**
     * Send SMS using template.
     */
    public function sendFromTemplate(
        string $templateSlug,
        string $phone,
        array $variables,
        ?User $user = null,
        ?Order $order = null,
        ?string $provider = null
    ): bool {
        $template = SmsTemplate::where('slug', $templateSlug)->active()->first();

        if (!$template) {
            Log::error("SMS Template not found: {$templateSlug}");
            return false;
        }

        $message = $template->render($variables);

        return $this->send($phone, $message, $user, $order, $provider);
    }

    /**
     * Send SMS message.
     */
    public function send(
        string $phone,
        string $message,
        ?User $user = null,
        ?Order $order = null,
        ?string $providerName = null
    ): bool {
        try {
            $provider = $this->getProvider($providerName);
            $config = $providerName 
                ? SmsConfiguration::getByProvider($providerName)
                : SmsConfiguration::getDefault();

            // Create log entry
            $log = SmsLog::create([
                'user_id' => $user?->id,
                'order_id' => $order?->id,
                'phone' => $phone,
                'message' => $message,
                'provider' => $config->provider,
                'status' => 'pending',
            ]);

            // Send SMS
            $result = $provider->send($phone, $message);

            // Update log
            $log->update([
                'status' => $result['success'] ? 'sent' : 'failed',
                'message_id' => $result['message_id'],
                'provider_response' => json_encode($result['response']),
                'error_message' => $result['error'],
                'sent_at' => $result['success'] ? now() : null,
            ]);

            return $result['success'];
        } catch (\Exception $e) {
            Log::error('SMS Send Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send order confirmation SMS.
     */
    public function sendOrderConfirmation(Order $order): bool
    {
        $phone = $order->customer_phone ?? $order->user?->phone;

        if (!$phone) {
            return false;
        }

        return $this->sendFromTemplate(
            'order-confirmation',
            $phone,
            [
                'customer_name' => $order->customer_name ?? $order->user?->first_name,
                'order_number' => $order->order_number,
                'total_amount' => number_format($order->total_amount, 2),
                'order_date' => $order->created_at->format('d M Y'),
            ],
            $order->user,
            $order
        );
    }

    /**
     * Send order status update SMS.
     */
    public function sendOrderStatusUpdate(Order $order, string $newStatus): bool
    {
        $phone = $order->customer_phone ?? $order->user?->phone;

        if (!$phone) {
            return false;
        }

        $statusMessages = [
            'confirmed' => 'confirmed',
            'processing' => 'being processed',
            'shipped' => 'shipped',
            'delivered' => 'delivered',
            'complete' => 'completed',
            'cancelled' => 'cancelled',
        ];

        $statusText = $statusMessages[$newStatus] ?? $newStatus;

        return $this->sendFromTemplate(
            'order-status-update',
            $phone,
            [
                'customer_name' => $order->customer_name ?? $order->user?->first_name,
                'order_number' => $order->order_number,
                'status' => $statusText,
                'tracking_number' => $order->tracking_number ?? 'N/A',
            ],
            $order->user,
            $order
        );
    }

    /**
     * Send OTP SMS.
     */
    public function sendOtp(string $phone, string $otp, ?User $user = null): bool
    {
        return $this->sendFromTemplate(
            'otp-verification',
            $phone,
            [
                'otp' => $otp,
                'validity' => '10 minutes',
            ],
            $user
        );
    }

    /**
     * Send delivery notification SMS.
     */
    public function sendDeliveryNotification(Order $order): bool
    {
        $phone = $order->customer_phone ?? $order->user?->phone;

        if (!$phone) {
            return false;
        }

        return $this->sendFromTemplate(
            'delivery-notification',
            $phone,
            [
                'customer_name' => $order->customer_name ?? $order->user?->first_name,
                'order_number' => $order->order_number,
                'tracking_number' => $order->tracking_number ?? 'N/A',
            ],
            $order->user,
            $order
        );
    }

    /**
     * Get SMS statistics.
     */
    public function getStatistics(array $filters = []): array
    {
        $query = SmsLog::query();

        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        if (isset($filters['provider'])) {
            $query->where('provider', $filters['provider']);
        }

        $total = $query->count();
        $sent = (clone $query)->sent()->count();
        $failed = (clone $query)->failed()->count();
        $pending = (clone $query)->pending()->count();
        $totalCost = (clone $query)->sum('cost');

        return [
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'pending' => $pending,
            'success_rate' => $total > 0 ? round(($sent / $total) * 100, 2) : 0,
            'total_cost' => $totalCost,
        ];
    }

    /**
     * Retry failed SMS.
     */
    public function retryFailed(int $logId): bool
    {
        $log = SmsLog::findOrFail($logId);

        if ($log->status !== 'failed') {
            return false;
        }

        if ($log->retry_count >= 3) {
            return false;
        }

        $log->increment('retry_count');

        return $this->send(
            $log->phone,
            $log->message,
            $log->user,
            $log->order,
            $log->provider
        );
    }
}
