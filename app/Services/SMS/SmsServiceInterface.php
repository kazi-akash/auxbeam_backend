<?php

namespace App\Services\SMS;

interface SmsServiceInterface
{
    /**
     * Send SMS message.
     *
     * @param string $phone
     * @param string $message
     * @return array ['success' => bool, 'message_id' => string|null, 'response' => mixed, 'error' => string|null]
     */
    public function send(string $phone, string $message): array;

    /**
     * Get SMS delivery status.
     *
     * @param string $messageId
     * @return array ['status' => string, 'delivered_at' => string|null]
     */
    public function getStatus(string $messageId): array;

    /**
     * Get account balance.
     *
     * @return array ['balance' => float, 'currency' => string]
     */
    public function getBalance(): array;

    /**
     * Validate phone number format.
     *
     * @param string $phone
     * @return bool
     */
    public function validatePhone(string $phone): bool;

    /**
     * Format phone number for provider.
     *
     * @param string $phone
     * @return string
     */
    public function formatPhone(string $phone): string;
}
