<?php

class CredoPaymentHandler {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Extract payment data from Credo's payload.
     */
    public function extractPaymentData($payload) {
        // Use null coalescing operators to check if the fields exist or set defaults
        $customer = $payload['data']['customer'] ?? null;
        $payload_event = strtolower($payload['event']);
        return [
            'invoice_number' => $customer['lastName'] ?? null,  // Invoice number stored in customer->lastName
            'payment_channel' => 'Credo', // Hardcoded for Credo
            'payment_bank' => $payload['data']['paymentMethodType'] ?? null, // Bank name
            'payment_method' => $payload['data']['paymentMethod'] ?? null, // Payment method (e.g., Bank Transfer)
            'payment_reference_number' => $payload['data']['transRef'] ?? null, // Credo transaction reference
            'amount_paid' => $payload['data']['transAmount'] ?? 0, // Transaction amount
            'receipt_number' => $customer['lastName'] ?? null,  // Receipt number stored in customer->lastName
            'date_payment_created' => date('Y-m-d H:i:s', $payload['data']['transactionDate'] / 1000) // Convert timestamp to human-readable date
        ];
    }
}
