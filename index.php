<?php
require_once 'middleware/ApiAuditLogger.php';
require_once 'helpers/auth_helper.php'; // For JWT decoding

// Proceed with your main application logic (e.g., routing)
require_once 'routes/payment.php';
require_once 'routes/web.php';
require_once 'routes/invoice.php';
require_once 'routes/payee.php';
require_once 'routes/enumerator.php';
require_once 'routes/mda.php';   // MDA-related routes (create MDA, etc.)

$logger = new ApiAuditLogger();
// Allow only specific domains (e.g., http://example.com) to access the API
$allowedOrigins = ['http://localhost', 'localhost'];

// Get the Origin of the request
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
// Check if the Origin is in the allowed list
// if (!in_array($origin, $allowedOrigins)) {

//     exit('Invalid Origin');
// }else{
//     header("Access-Control-Allow-Origin: $origin");
// }

// Always allow these headers (ensure these headers match what you expect to receive)
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");
// Check the request method and the endpoint (based on URL)
$request_method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);
$uri = '/'.end( $uri );
// Handle preflight requests (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Allowed methods
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    
    // Allow credentials (if necessary, for authentication or cookies)
    header("Access-Control-Allow-Credentials: true");

    // Exit for OPTIONS requests
    exit(0);
}


try {
    // Capture the request details
    $endpoint = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];
    $requestPayload = file_get_contents('php://input');

    // Authenticate the user and extract their ID and type
    $userId = null;
    $userType = null;
    $authData = authenticate(); // Call the authenticate function
    if ($authData) {
        $userId = $authData['user_id'] ?? null;
        $userType = $authData['user_type'] ?? null;
    }

    // Route handling logic
    ob_start(); // Start output buffering to capture response
    require_once './routes/web.php'; // Load your routes
    $responsePayload = ob_get_clean(); // Get the response output
    $statusCode = http_response_code();

    // Log the request and response
    $logger->logRequest($endpoint, $method, $requestPayload, $responsePayload, $statusCode, $userId, $userType);

    // Send the captured response
    echo $responsePayload;
} catch (Exception $e) {
    // Handle exceptions and log them as well
    $responsePayload = json_encode(["status" => "error", "message" => $e->getMessage()]);
    $statusCode = 500;
    $logger->logRequest($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], $requestPayload, $responsePayload, $statusCode, $userId, $userType);
    http_response_code($statusCode);
    echo $responsePayload;
}
// Proceed with your main application logic (e.g., routing)
require_once 'routes/payment.php';
require_once 'routes/web.php';
require_once 'routes/invoice.php';
require_once 'routes/payee.php';
require_once 'routes/enumerator.php';
require_once 'routes/mda.php';   // MDA-related routes (create MDA, etc.)
