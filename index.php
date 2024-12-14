<?php

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
// echo $uri;
// Handle preflight requests (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Allowed methods
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    
    // Allow credentials (if necessary, for authentication or cookies)
    header("Access-Control-Allow-Credentials: true");

    // Exit for OPTIONS requests
    exit(0);
}

// Proceed with your main application logic (e.g., routing)
require_once 'routes/payment.php';
require_once 'routes/web.php';
require_once 'routes/invoice.php';
require_once 'routes/payee.php';
require_once 'routes/enumerator.php';
require_once 'routes/mda.php';   // MDA-related routes (create MDA, etc.)
