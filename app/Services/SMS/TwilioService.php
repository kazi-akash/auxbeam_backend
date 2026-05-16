<?php

namespace App\Services\SMS;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwilioService implements SmsServiceInterface
{
    protected $accountSid;
    protected $authToken;
    protected $fromNumber;
    protected $apiUrl;

    public function __construct(array $credentials)
    {
        $this->accountSid = $credentials['account_sid'] ?? '';
        $this->authToken = $credentials['auth_token'] ?? '';
        $this->fromNumber = $credentials['from_number'] ?? '';
        $this->apiUrl = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}";
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

            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->asForm()
                ->post($this->apiUrl . '/Messages.json', [
                    'From' => $this->fromNumber,
                    'To' => $phone,
                    'Body' => $message,
                ]);

            $data = $response->json();

            if ($response->successful() && isset($data['sid'])) {
                return [
                    'success' => true,
                    'message_id' => $data['sid'],
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
            Log::error('Twilio Error: ' . $e->getMessage());

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
            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->get($this->apiUrl . "/Messages/{$messageId}.json");

            $data = $response->json();

            if ($response->successful() && isset($data['status'])) {
                return [
                    'status' => $data['status'],
                    'delivered_at' => $data['date_sent'] ?? null,
                ];
            }

            return [
                'status' => 'unknown',
                'delivered_at' => null,
            ];
        } catch (\Exception $e) {
            Log::error('Twilio Status Error: ' . $e->getMessage());

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
            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->get($this->apiUrl . '/Balance.json');

            $data = $response->json();

            if ($response->successful() && isset($data['balance'])) {
                return [
                    'balance' => (float) $data['balance'],
                    'currency' => $data['currency'] ?? 'USD',
                ];
            }

            return [
                'balance' => 0,
                'currency' => 'USD',
            ];
        } catch (\Exception $e) {
            Log::error('Twilio Balance Error: ' . $e->getMessage());

            return [
                'balance' => 0,
                'currency' => 'USD',
            ];
        }
    }

    /**
     * Validate phone number format (E.164).
     */
    public function validatePhone(string $phone): bool
    {
        // E.164 format: +[country code][number]
        return preg_match('/^\+[1-9]\d{1,14}$/', $phone);
    }

    /**
     * Format phone number for Twilio (E.164 format).
     */
    public function formatPhone(string $phone): string
    {
        // Remove spaces, dashes
        $phone = preg_replace('/[\s\-]/', '', $phone);

        // If doesn't start with +, add +880 for Bangladesh
        if (!str_starts_with($phone, '+')) {
            if (str_starts_with($phone, '0')) {
                $phone = '+880' . substr($phone, 1);
            } elseif (str_starts_with($phone, '880')) {
                $phone = '+' . $phone;
            } else {
                $phone = '+880' . $phone;
            }
        }

        return $phone;
    }
}
