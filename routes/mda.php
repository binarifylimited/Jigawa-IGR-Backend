<?php
require_once 'controllers/AuthController.php';
require_once 'controllers/MdaController.php';
require_once 'controllers/RevenueHeadController.php';

// Include the auth_helper where authenticate() is defined
require_once 'helpers/auth_helper.php';



$mdaController = new MdaController();
$revenueHeadController = new RevenueHeadController();

// Route: Create MDA (POST)
if ($request_method == 'POST' && $uri == '/create-mda') {
    // $decoded_token = authenticate();  // Authenticate the request
    // // Call the register method in RegistrationController

    // // Optionally check if the authenticated user has the role to create an admin
    // if ($decoded_token['role'] !== 'super_admin') {
    //     echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only super admins can register new users']);
    //     http_response_code(403); // Forbidden
    //     exit;
    // }
    $input = json_decode(file_get_contents('php://input'), true);
    $mdaController->createMda($input);
    exit;
}


// Route: Create Revenue Head for a specific MDA (POST)
if ($request_method == 'POST' && $uri == '/create-revenue-head') {
    // $decoded_token = authenticate();  // Authenticate the request
    // // Call the register method in RegistrationController

    // // Optionally check if the authenticated user has the role to create an admin
    // if ($decoded_token['role'] !== 'super_admin') {
    //     echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only super admins can register new users']);
    //     http_response_code(403); // Forbidden
    //     exit;
    // }
    $input = json_decode(file_get_contents('php://input'), true);
    $revenueHeadController->createRevenueHead($input);
    exit;
}

// Route: Create Multiple Revenue Heads (POST)
if ($request_method == 'POST' && $uri == '/create-multiple-revenue-heads') {
    // $decoded_token = authenticate();  // Authenticate the request
    // // Call the register method in RegistrationController

    // // Optionally check if the authenticated user has the role to create an admin
    // if ($decoded_token['role'] !== 'super_admin') {
    //     echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only super admins can register new users']);
    //     http_response_code(403); // Forbidden
    //     exit;
    // }
    $input = json_decode(file_get_contents('php://input'), true);
    $revenueHeadController->createMultipleRevenueHeads($input);
    exit;
}


// Route: Update MDA information (POST)
if ($request_method == 'POST' && $uri == '/update-mda') {
    // $decoded_token = authenticate();  // Authenticate the request
    // // Call the register method in RegistrationController

    // // Optionally check if the authenticated user has the role to create an admin
    // if ($decoded_token['role'] !== 'super_admin') {
    //     echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only super admins can register new users']);
    //     http_response_code(403); // Forbidden
    //     exit;
    // }
    $input = json_decode(file_get_contents('php://input'), true);
    $mdaController->updateMda($input);
    exit;
}
// You can add more MDA-related routes here (e.g., update MDA, delete MDA)

// Route: Update Revenue Head information (POST)
if ($request_method == 'POST' && $uri == '/update-revenue-head') {
    // $decoded_token = authenticate();  // Authenticate the request
    // // Call the register method in RegistrationController

    // // Optionally check if the authenticated user has the role to create an admin
    // if ($decoded_token['role'] !== 'super_admin') {
    //     echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only super admins can register new users']);
    //     http_response_code(403); // Forbidden
    //     exit;
    // }
    $input = json_decode(file_get_contents('php://input'), true);
    $revenueHeadController->updateRevenueHead($input);
    exit;
}

// Route: Fetch All MDAs with pagination (GET)
if ($request_method == 'GET' && $uri == '/get-mdas') {
    // $decoded_token = authenticate();  // Authenticate the request
    // // Call the register method in RegistrationController

    // // Optionally check if the authenticated user has the role to create an admin
    // if ($decoded_token['role'] !== 'super_admin') {
    //     echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only super admins can register new users']);
    //     http_response_code(403); // Forbidden
    //     exit;
    // }
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $mdaController->getAllMdas($page, $limit);
    exit;
}

// Route: Fetch MDA by filters (GET)
if ($request_method == 'GET' && $uri == '/get-mda') {
    // $decoded_token = authenticate();  // Authenticate the request
    // // Call the register method in RegistrationController

    // // Optionally check if the authenticated user has the role to create an admin
    // if ($decoded_token['role'] !== 'super_admin') {
    //     echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only super admins can register new users']);
    //     http_response_code(403); // Forbidden
    //     exit;
    // }
    $filters = [
        'id' => isset($_GET['id']) ? (int)$_GET['id'] : null,
        'fullname' => isset($_GET['fullname']) ? $_GET['fullname'] : null,
        'mda_code' => isset($_GET['mda_code']) ? $_GET['mda_code'] : null,
        'email' => isset($_GET['email']) ? $_GET['email'] : null,
        'allow_payment' => isset($_GET['allow_payment']) ? (int)$_GET['allow_payment'] : null,
        'status' => isset($_GET['status']) ? (int)$_GET['status'] : null,
    ];

    $mdaController->getMdaByFilters(array_filter($filters)); // Filter out null values
    exit;
}

// Route: Fetch Revenue Head by filters (GET)
if ($request_method == 'GET' && $uri == '/get-revenue-head') {
    // $decoded_token = authenticate();  // Authenticate the request
    // Call the register method in RegistrationController

    // Optionally check if the authenticated user has the role to create an admin
    // if ($decoded_token['role'] !== 'super_admin') {
    //     echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only super admins can register new users']);
    //     http_response_code(403); // Forbidden
    //     exit;
    // }
    $filters = [
        'id' => isset($_GET['id']) ? (int)$_GET['id'] : null,
        'item_code' => isset($_GET['item_code']) ? $_GET['item_code'] : null,
        'item_name' => isset($_GET['item_name']) ? $_GET['item_name'] : null,
        'category' => isset($_GET['category']) ? $_GET['category'] : null,
        'status' => isset($_GET['status']) ? (int)$_GET['status'] : null,
        'mda_id' => isset($_GET['mda_id']) ? (int)$_GET['mda_id'] : null,
    ];

    $revenueHeadController->getRevenueHeadByFilters(array_filter($filters)); // Filter out null values
    exit;
}


// Route: Approve Revenue Head Status  (POST)
if ($request_method == 'PUT' && $uri == '/approve-revenue-head') {
    // $decoded_token = authenticate();  // Authenticate the request
    // // Call the register method in RegistrationController

    // // Optionally check if the authenticated user has the role to create an admin
    // if ($decoded_token['role'] !== 'super_admin') {
    //     echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only super admins can register new users']);
    //     http_response_code(403); // Forbidden
    //     exit;
    // }
    $input = json_decode(file_get_contents('php://input'), true);
    $revenueHeadController->approveRevenueHead($input);
    exit;
}


// Route: Delete MDA
if ($request_method == 'DELETE' && $uri == '/delete-mda') {
    // You may want to authenticate the request, here’s an example
    // $decoded_token = authenticate();

    // Optionally check if the authenticated user has the role to delete
    // if ($decoded_token['role'] !== 'super_admin') {
    //     echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only super admins can delete MDAs']);
    //     http_response_code(403); // Forbidden
    //     exit;
    // }

    // Get the input data from the request body (the ID of the MDA to delete)
    $input = json_decode(file_get_contents('php://input'), true);

    // Check if mda_id is provided in the input
    if (isset($input['mda_id'])) {
        $mda_id = $input['mda_id']; // Extract mda_id
        $mdaController->deleteMda($mda_id); // Call the delete method
    } else {
        echo json_encode(['status' => 'error', 'message' => 'MDA ID is required']);
        http_response_code(400); // Bad Request
    }
    exit;
}

// Route: Get Users under MDAs and Specific MDAs
if ($request_method == 'GET' && $uri == '/get-mda-users') {
    $filters = [
        'mda_id' => isset($_GET['mda_id']) ? (int)$_GET['mda_id'] : null,
        'name' => isset($_GET['name']) ? $_GET['name'] : null,
        'email' => isset($_GET['email']) ? $_GET['email'] : null,
        'phone_number' => isset($_GET['phone']) ? $_GET['phone'] : null,
        'office_name' => isset($_GET['office_name']) ? $_GET['office_name'] : null
    ];

    $mdaController->getMdaUsers(array_filter($filters)); // Filter out null values
    exit;
}




// If no matching route is found
http_response_code(404);
echo json_encode(['status' => 'error:'.$uri, 'message' => 'Endpoint not found']);
exit;
