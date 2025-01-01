<?php
// Include the AuthController
require_once 'controllers/AuthController.php';
require_once 'controllers/PresumptiveTaxController.php';
// Include the auth_helper where authenticate() is defined
require_once 'helpers/auth_helper.php';

// Initialize the AuthController
$authController = new AuthController();
$taxController = new TaxController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $uri === '/calculate-presumptive-tax') {
    // Decode the incoming JSON payload
    $inputData = json_decode(file_get_contents('php://input'), true);

    // Call the function to calculate presumptive tax
    $taxController->calculatePresumptiveTax($inputData);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $uri === '/get-presumptive-taxes') {
    $filters = [
        'business_type' => $_GET['business_type'] ?? null,
        'frequency' => $_GET['frequency'] ?? null,
    ];

    $filters = array_filter($filters); // Remove null values
    $taxController->getAllPresumptiveTaxes($filters);
    exit;
}

