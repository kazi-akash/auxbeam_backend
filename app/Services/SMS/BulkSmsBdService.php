<?php

namespace App\Services\SMS;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BulkSmsBdService implements SmsServiceInterface
{
    protected $apiKey;
    protected $senderId;
    protected $apiUrl = 'https://api.bulksmsbd.com/api/v1';

    public function __construct(array $credentials)
    {
        $this->apiKey = $credentials['api_key'] ?? '';
        $this->senderId = $credentials['sender_id'] ?? '';
    }

    /**
     * Send SMS message.
     */
    public function send(string $phone, string $message): array
    {
        try {
            $phone = $this->formatPhone($phone);

            if (!$this->validatePhone($phone)) {
                return [
                    'success' => false,
                    'message_id' => null,
                    'response' => null,
                    'error' => 'Invalid phone number format',
                ];
            }

            $response = Http::post($this->apiUrl . '/send', [
                'api_key' => $this->apiKey,
                'senderid' => $this->senderId,
                'number' => $phone,
                'message' => $message,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['status']) && $data['status'] === 'success') {
                return [
                    'success' => true,
                    'message_id' => $data['message_id'] ?? null,
                    'response' => $data,
                    'error' => null,
                ];
            }

            return [
                'success' => false,
                'message_id' => null,
                'response' => $data,
                'error' => $data['message'] ?? 'Failed to send SMS',
            ];
        } catch (\Exception $e) {
            Log::error('BulkSMSBD Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message_id' => null,
                'response' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get SMS delivery status.
     */
    public function getStatus(string $messageId): array
    {
        try {
            $response = Http::get($this->apiUrl . '/status', [
                'api_key' => $this->apiKey,
                'message_id' => $messageId,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['status'])) {
                return [
                    'status' => $data['status'],
                    'delivered_at' => $data['delivered_at'] ?? null,
                ];
            }

            return [
                'status' => 'unknown',
                'delivered_at' => null,
            ];
        } catch (\Exception $e) {
            Log::error('BulkSMSBD Status Error: ' . $e->getMessage());

            return [
                'status' => 'unknown',
                'delivered_at' => null,
            ];
        }
    }

    /**
     * Get account balance.
     */
    public function getBalance(): array
    {
        try {
            $response = Http::get($this->apiUrl . '/balance', [
                'api_key' => $this->apiKey,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['balance'])) {
                return [
                    'balance' => (float) $data['balance'],
                    'currency' => 'BDT',
                ];
            }

            return [
                'balance' => 0,
                'currency' => 'BDT',
            ];
        } catch (\Exception $e) {
            Log::error('BulkSMSBD Balance Error: ' . $e->getMessage());

            return [
                'balance' => 0,
                'currency' => 'BDT',
            ];
        }
    }

    /**
     * Validate phone number format (Bangladesh).
     */
    public function validatePhone(string $phone): bool
    {
        // Bangladesh phone format: 880XXXXXXXXXX or 01XXXXXXXXX
        return preg_match('/^(880|0)?1[3-9]\d{8}$/', $phone);
    }

    /**
     * Format phone number for BulkSMSBD (880XXXXXXXXXX).
     */
    public function formatPhone(string $phone): string
    {
        // Remove spaces, dashes, and plus signs
        $phone = preg_replace('/[\s\-\+]/', '', $phone);

        // If starts with 0, replace with 880
        if (str_starts_with($phone, '0')) {
            $phone = '880' . substr($phone, 1);
        }

        // If doesn't start with 880, add it
        if (!str_starts_with($phone, '880')) {
            $phone = '880' . $phone;
        }

        return $phone;
    }
}
