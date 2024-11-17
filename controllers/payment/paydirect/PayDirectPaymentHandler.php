<?php

class PayDirectPaymentHandler {
    private $conn;
    private $xmlToJsonUrl = "https://xml2json-six.vercel.app/convert";

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Convert XML to JSON and extract payment data from PayDirect.
     */
    public function extractPaymentData($xmlPayload) {
        // Convert XML to JSON using external service
        $jsonPayload = $this->convertXmlToJson($xmlPayload);
        if (!$jsonPayload) {
            return [
                'status' => 1,
                'statusMessage' => 'Rejected By System'
            ];
        }

        $dataArray = json_decode($jsonPayload, true);
        if (!isset($dataArray["PaymentNotificationRequest"]["Payments"]["Payment"])) {
            return [
                'status' => 1,
                'statusMessage' => 'Rejected By System'
            ];
        }

        $payment = $dataArray["PaymentNotificationRequest"]["Payments"]["Payment"];
        
        // Check required fields
        if (empty($payment["CustReference"]) || empty($payment["PaymentLogId"]) || empty($payment["Amount"]) || empty($payment["ReceiptNo"])) {
            return [
                'status' => 1,
                'statusMessage' => 'Rejected By System'
            ];
        }

        // Check if IsReversal is set to anything other than 'False'
        if ($payment['IsReversal'] !== 'False') {
            return [
                'status' => 1,
                'statusMessage' => 'Payment Reversal'
            ];
        }

        $date = $payment["PaymentDate"]; // Original date format (m/d/Y H:i:s)
        $datetime = DateTime::createFromFormat('m/d/Y H:i:s', $date);
        $mysqlDateTime = $datetime->format('Y-m-d H:i:s'); // Convert to MySQL DATETIME format

        // Return structured data for payment processing
        return [
            'invoice_number' => $payment["CustReference"],
            'payment_channel' => 'InterSwitch',
            'payment_bank' => $payment["BankName"],
            'payment_method' => $payment["PaymentMethod"],
            'payment_reference_number' => $payment["PaymentLogId"],
            'receipt_number' => $payment["ReceiptNo"] . "-" . $payment["PaymentLogId"],
            'amount_paid' => $payment["Amount"],
            'date_payment_created' => $mysqlDateTime,
            'status' => 0,  // Assuming a valid state initially
            'statusMessage' => 'Success'
        ];
    }

    /**
     * Convert XML to JSON using the external service.
     */
    private function convertXmlToJson($xmlPayload) {
        $headers = [
            "Content-Type: text/xml"
        ];

        $ch = curl_init($this->xmlToJsonUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlPayload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response ? $response : null;
    }
}
