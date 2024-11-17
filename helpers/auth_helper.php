
<?php
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key; // For decoding the JWT with the key

$secret_key = 'your_secret_key_plateau_35c731567705ad451533eb8516558ca0dad1e3e56d095c50289aabbff516a7f7_your_secret_key_plateau';  // Use the same secret key used to sign the JWT

function authenticate() {
    global $secret_key;

    // Fetch the Authorization header
    $headers = apache_request_headers();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;

    if ($authHeader) {
        // Extract the JWT from the Bearer token
        list($jwt) = sscanf($authHeader, 'Bearer %s');

        if ($jwt) {
            try {
                // Decode the JWT
                $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

                // Return decoded token
                return (array) $decoded;
            } catch (Exception $e) {
                // Handle invalid/expired token
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Invalid or expired token']);
                exit;
            }
        }
    }

    // If token is not provided or incorrect, deny access
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Token not provided']);
    exit;
}

