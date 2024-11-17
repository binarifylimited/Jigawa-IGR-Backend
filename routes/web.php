<?php
// Include the AuthController
require_once 'controllers/AuthController.php';
require_once 'controllers/RegistrationController.php';
require_once 'controllers/MdaController.php';
require_once 'controllers/EnumeratorController.php';



// Include the auth_helper where authenticate() is defined
require_once 'helpers/auth_helper.php';

// Initialize the AuthController
$authController = new AuthController();
$registrationController = new RegistrationController();
$mdaController = new MdaController();
$enumeratorController = new EnumeratorController();

// Route: Login (POST)
if ($request_method == 'POST' && $uri == '/login') {
    // Get the JSON data from the request body
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate if email and password are provided
    if (!isset($input['email']) || !isset($input['password'])) {
        echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
        http_response_code(400); // Bad request
        exit;
    }

    // Call the login method in AuthController
    $authController->login($input['email'], $input['password']);
    exit;
}

// Route: Register Admin User (POST)
if ($request_method == 'POST' && $uri == '/register-admin') {
    // Authenticate the request first using the JWT token
    $decoded_token = authenticate(); // This function will decode and verify the JWT token

    // Optionally check if the authenticated user has the role to create an admin
    if ($decoded_token['role'] !== 'super_admin') {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only super admins can register new users']);
        http_response_code(403); // Forbidden
        exit;
    }

    // Get the JSON data from the request body
    $input = json_decode(file_get_contents('php://input'), true);

    // Call the register method in RegistrationController
    $registrationController->registerAdminUser($input);
    exit;
}

// Route: Register MDA User (POST)
if ($request_method == 'POST' && $uri == '/register-mda') {
    $decoded_token = authenticate();  // Authenticate the request
    // Call the register method in RegistrationController

    // Optionally check if the authenticated user has the role to create an admin
    if ($decoded_token['role'] !== 'super_admin') {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only super admins can register new users']);
        http_response_code(403); // Forbidden
        exit;
    }
    $input = json_decode(file_get_contents('php://input'), true);
    $registrationController->registerMDAUser($input);
    exit;
}

// Route: Register Enumerator User (POST)
if ($request_method == 'POST' && $uri == '/register-enumerator') {
    $decoded_token = authenticate();  // Authenticate the request
    // Call the register method in RegistrationController

    // Optionally check if the authenticated user has the role to create an admin
    if ($decoded_token['role'] !== 'super_admin') {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only super admins can register new users']);
        http_response_code(403); // Forbidden
        exit;
    }
    $input = json_decode(file_get_contents('php://input'), true);
    $registrationController->registerEnumeratorUser($input);
    exit;
}

// Route: Register Special User (POST)
if ($request_method == 'POST' && $uri == '/register-special-user') {
    $decoded_token = authenticate();  // Authenticate the request
    // Call the register method in RegistrationController

    // Optionally check if the authenticated user has the role to create an admin
    if ($decoded_token['role'] !== 'super_admin') {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only super admins can register new users']);
        http_response_code(403); // Forbidden
        exit;
    }
    $input = json_decode(file_get_contents('php://input'), true);
    $registrationController->registerSpecialUser($input);
    exit;
}

// Route: Register Employee with Salary and Benefits (POST)
if ($request_method == 'POST' && $uri == '/register-employee-with-salary') {
    $decoded_token = authenticate();  // Authenticate the request

    // Optionally check if the authenticated user has the role to create an admin
    if ($decoded_token['role'] !== 'super_admin') {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only super admins can register new users']);
        http_response_code(403); // Forbidden
        exit;
    }
    $input = json_decode(file_get_contents('php://input'), true);
    $registrationController->registerEmployeeWithSalary($input);
    exit;
}

// Route: Register Taxpayer (POST)
if ($request_method == 'POST' && $uri == '/register-taxpayer') {
    $decoded_token = authenticate();  // Authenticate the request
    $input = json_decode(file_get_contents('php://input'), true);
    $registrationController->registerTaxpayer($input);
    exit;
}

// Route: Register Enumerator Tax Payer (POST)
if ($request_method == 'POST' && $uri == '/register-enumerator-taxpayer') {
    $input = json_decode(file_get_contents('php://input'), true);
    $registrationController->registerEnumeratorTaxPayer($input);
    exit;
}

// Route: Profile (GET, protected)
if ($request_method == 'GET' && $uri == '/profile') {
    // Authenticate the request first using the authenticate() function from auth_helper.php
    $decoded = authenticate(); // This function will decode and verify the JWT token

    // If authentication is successful, proceed with fetching profile details
    echo json_encode([
        'status' => 'success',
        'message' => 'Profile fetched successfully',
        'user' => $decoded // You can include relevant user details from the decoded token
    ]);
    exit;
}




