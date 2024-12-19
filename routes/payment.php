<?php
// Include the AuthController
// require_once '../controllers/AuthController.php';
// Include the auth_helper where authenticate() is defined
// require_once '../helpers/auth_helper.php';
// require_once __DIR__ . '/controllers/payment/PaymentController.php';
// require_once __DIR__ . '/helpers/VerificationHelper.php';

require_once 'controllers/PaymentController.php';
require_once 'helpers/VerificationHelper.php';


// Initialize PaymentController
$paymentController = new PaymentController();

// Route: Process Paystack Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $uri === '/process-paystack-payment') {
    $paystackSecret = 'sk_test_8235860c9dd930e6370aac4a6e66e38b8150bff8'; // Replace with your actual secret key

    // Verify Paystack Signature
    if (VerificationHelper::verifyPaystackSignature($paystackSecret)) {
        http_response_code(403); // Forbidden
        echo json_encode(['status' => 'error', 'message' => 'Invalid Paystack signature']);
        exit;
    }
    
    // If the signature is valid, proceed to handle the request
    $input = json_decode(file_get_contents('php://input'), true);
    $paymentController->processPaystackPayment($input);
    exit;
}

// Route: Process PayDirect Payment (IP validation example)
// Route: Process PayDirect Payment
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && $uri === '/process-paydirect-payment') {
//     $xmlPayload = file_get_contents('php://input');
//     $response = $paymentController->processPayDirectPayment($xmlPayload);
//     echo $response;
//     exit;
// }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $uri === '/process-paydirect-payment') {
    // Example IPs, replace with actual allowed PayDirect IPs
    // $allowedIps = ['203.0.113.1', '198.51.100.22'];
    $allowedIps = [];
    // Verify PayDirect IP address
    // if (!VerificationHelper::verifyPayDirectIp($allowedIps)) {
    //     http_response_code(403); // Forbidden
    //     echo json_encode(['status' => 'error', 'message' => 'Unauthorized IP address'.$_SERVER['REMOTE_ADDR']]);
    //     exit;
    // }

    $xmlPayload = file_get_contents('php://input');
    $paymentController->processPayDirectPayment($xmlPayload);
    exit;

    // // If the IP is valid, proceed to handle the request
    // $input = json_decode(file_get_contents('php://input'), true);
    // $paymentController->processPaydirectPayment($input['payload']);
    // exit;
}

// Route: Process Credo Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $uri === '/process-credo-payment') {
    // No signature validation needed for Credo in this example
    $input = json_decode(file_get_contents('php://input'), true);
    $paymentController->processCredoPayment($input);
    exit;
}

// Route: Get payment collection with optional filters and pagination
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $uri === '/get-payment') {
    // Capture query parameters from the request
    $queryParams = $_GET;

    // Fetch payment collection with filters and pagination
    $response = $paymentController->getPaymentCollection($queryParams);
    
    // Output the response in JSON format
    header('Content-Type: application/json');
    echo $response;
    exit;
}
