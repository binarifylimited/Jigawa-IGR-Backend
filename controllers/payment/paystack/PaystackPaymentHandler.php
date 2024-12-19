<?php

class PaystackPaymentHandler {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Extract payment data from Paystack's payload.
     */
    public function extractPaymentData($payload) {
        // Step 1: Decode the JSON payload (this should be done if not already decoded at an earlier stage)
        $payload = is_string($payload) ? json_decode($payload, true) : $payload;

        // Step 2: Use null coalescing operators to check if the fields exist or set defaults
        $metadata = $payload['data']['metadata'] ?? null;
        $customFields = $metadata['custom_fields'][0] ?? null;
        $dateTime = new DateTime($payload['data']['paid_at']);
        $mysqlDate = $dateTime->format('Y-m-d H:i:s');
        return [
            'invoice_number' => $customFields['value'] ?? null,  // Check if custom_fields[0] exists
            'payment_channel' => 'PayStack', // Hardcoded for PayStack
            'payment_bank' => $payload['data']['authorization']['bank'] ?? null, // Check if bank exists
            'payment_method' => $payload['data']['authorization']['card_type'] ?? null, // Check if card_type exists
            'payment_reference_number' => $payload['data']['reference'] ?? null, // Check if reference exists
            'receipt_number' => $customFields['value'] ?? null, // Same as invoice_number if missing
            'amount_paid' => isset($payload['data']['amount']) ? $payload['data']['amount'] / 100 : 0, // Convert from kobo to Naira
            'date_payment_created' => $mysqlDate ?? null // Check if paid_at exists
        ];
    }
}
