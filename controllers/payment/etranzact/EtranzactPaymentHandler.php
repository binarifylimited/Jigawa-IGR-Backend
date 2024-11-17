<?php

class EtranzactPaymentHandler {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Extract payment data from Etranzact's payload.
     */
    public function extractPaymentData($payload) {
        return [
            'mda_id' => $payload['agency_id'],
            'revenue_head' => $payload['revenue_head'],
            'user_id' => $payload['user_id'],
            'invoice_number' => $payload['invoice_number'],
            'payment_channel' => 'Etranzact',
            'payment_method' => $payload['payment_mode'],
            'payment_bank' => $payload['bank_name'],
            'payment_gateway' => 'Etranzact',
            'payment_reference_number' => $payload['transaction_ref'],
            'receipt_number' => $payload['receipt_number'],
            'amount_paid' => $payload['amount_paid'],
            'date_payment_created' => $payload['payment_date']
        ];
    }
}
