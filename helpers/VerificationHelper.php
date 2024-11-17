<?php

class VerificationHelper {

    /**
     * Verify Paystack signature from incoming requests.
     * 
     * @param string $paystackSecret The Paystack secret key.
     * @return bool Returns true if the signature is valid, false otherwise.
     */
    public static function verifyPaystackSignature($paystackSecret) {
        // Ensure the request is POST and contains the Paystack signature header
        if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST' || !isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'])) {
            return false;
        }

        // Retrieve the raw request body
        $input = file_get_contents('php://input');

        // Generate the hash using the secret key and compare it with the Paystack signature
        $hash = hash_hmac('sha512', $input, $paystackSecret);
        
        return hash_equals($hash, $_SERVER['HTTP_X_PAYSTACK_SIGNATURE']);
    }

    /**
     * Verify PayDirect request based on IP address validation.
     * 
     * @param array $allowedIps List of allowed IPs for the PayDirect vendor.
     * @return bool Returns true if the IP address is valid, false otherwise.
     */
    public static function verifyPayDirectIp($allowedIps) {
        // Get the client IP address
        $clientIp = $_SERVER['REMOTE_ADDR'];

        // Check if the client IP is in the allowed IP list
        return in_array($clientIp, $allowedIps);
    }
}
