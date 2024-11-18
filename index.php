<?php
require_once 'middleware/ApiAuditLogger.php';
require_once 'helpers/auth_helper.php'; // For JWT decoding

$logger = new ApiAuditLogger();

// Set headers for CORS and JSON responses
setHeaders();

// Handle preflight requests (OPTIONS method)
handlePreflightRequests();

// Extract request details
$requestPayload = file_get_contents('php://input');
$endpoint = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($endpoint, PHP_URL_PATH);
$uriSegments = explode('/', trim($uri, '/'));
$route = '/' . end($uriSegments); // Current route
$userId = null;
$userType = null;

// Authenticate the user
try {
    $authData = authenticate(); // Decodes the JWT and returns user data
    $userId = $authData['user_id'] ?? null;
    $userType = $authData['user_type'] ?? null;
} catch (Exception $e) {
    sendErrorResponse(401, 'Unauthorized: ' . $e->getMessage());
    exit;
}

// Route handling and logging
try {
    // Output buffering to capture route response
    ob_start();

    // Include all route files
    require_once 'routes/payment.php';
    require_once 'routes/web.php';
    require_once 'routes/invoice.php';
    require_once 'routes/payee.php';
    require_once 'routes/enumerator.php';
    require_once 'routes/mda.php';

    // Capture response
    $responsePayload = ob_get_clean();
    $statusCode = http_response_code();

    // Log the request and response
    $logger->logRequest($endpoint, $method, $requestPayload, $responsePayload, $statusCode, $userId, $userType);

    // Send the response
    echo $responsePayload;
} catch (Exception $e) {
    $responsePayload = json_encode(["status" => "error", "message" => $e->getMessage()]);
    $statusCode = 500;
    $logger->logRequest($endpoint, $method, $requestPayload, $responsePayload, $statusCode, $userId, $userType);
    http_response_code($statusCode);
    echo $responsePayload;
}

// Helper functions

function setHeaders() {
    global $allowedOrigins;
    $allowedOrigins = ['http://localhost', 'localhost'];

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: $origin");
    }

    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Content-Type: application/json");
}

function handlePreflightRequests() {
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Credentials: true");
        exit(0);
    }
}

function sendErrorResponse($statusCode, $message) {
    http_response_code($statusCode);
    echo json_encode(["status" => "error", "message" => $message]);
}
