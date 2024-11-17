<?php
// Include the AuthController
require_once 'controllers/AuthController.php';
// Include the auth_helper where authenticate() is defined
require_once 'helpers/auth_helper.php';
require_once 'controllers/PayeeController.php';

// Initialize the AuthController
$authController = new AuthController();
$specialUserController = new SpecialUserController();

// Route: Get all special users with filters, employee count, total monthly tax, annual tax, and total payments
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $uri === '/get-special-users') {
    // Capture query parameters and pass them to the controller
    $queryParams = $_GET;

    // Call the method in the controller with the query parameters
    $response = $specialUserController->getAllSpecialUsers($queryParams);

    // Set response header and output the response in JSON format
    header('Content-Type: application/json');
    echo $response;
    exit;
} 

// Route: Get all employees under a specific special user with optional pagination
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $uri === '/get-special-user-employees') {
    // Capture query parameters and pass them to the controller
    $queryParams = $_GET;

    // Call the method in the controller with the query parameters
    $response = $specialUserController->getEmployeesBySpecialUser($queryParams);

    // Set response header and output the response in JSON format
    header('Content-Type: application/json');
    echo $response;
    exit;
}
