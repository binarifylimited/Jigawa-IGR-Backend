<?php
// Include the AuthController
require_once 'controllers/AuthController.php';
// Include the auth_helper where authenticate() is defined
require_once 'helpers/auth_helper.php';
require_once 'controllers/AdminController.php';

// Initialize the AuthController
$authController = new AuthController();
$adminController = new AdminController();

if ($request_method == 'GET' && $uri == '/get-total-amount-paid') {
    $filters = [
        'month' => isset($_GET['month']) ? (int)$_GET['month'] : null,
        'year' => isset($_GET['year']) ? (int)$_GET['year'] : null,
    ];

    $adminController->getTotalAmountPaid(array_filter($filters)); // Filter out null values
    exit;
}

if ($request_method == 'GET' && $uri == '/get-total-amount-paid-yearly') {
    $filters = [
        'year' => isset($_GET['year']) ? (int)$_GET['year'] : null,
    ];

    $adminController->getTotalAmountPaidYearly(array_filter($filters)); // Filter out null values
    exit;
}

if ($request_method == 'GET' && $uri == '/get-expected-monthly-revenue') {
    $filters = [
        'month' => isset($_GET['month']) ? (int)$_GET['month'] : null,
        'year' => isset($_GET['year']) ? (int)$_GET['year'] : null,
    ];

    $adminController->getExpectedMonthlyRevenue(array_filter($filters)); // Filter out null values
    exit;
}

if ($request_method == 'GET' && $uri == '/get-total-special-users') {
    $adminController->getTotalSpecialUsers();
    exit;
}

if ($request_method == 'GET' && $uri == '/get-total-employees') {
    $adminController->getTotalEmployees();
    exit;
}

if ($request_method == 'GET' && $uri == '/get-total-annual-estimate') {
    $filters = [
        'year' => isset($_GET['year']) ? (int)$_GET['year'] : null,
    ];

    $adminController->getTotalAnnualEstimate(array_filter($filters)); // Filter out null values
    exit;
}

if ($request_method == 'GET' && $uri == '/get-total-annual-remittance') {
    $filters = [
        'year' => isset($_GET['year']) ? (int)$_GET['year'] : null,
    ];

    $adminController->getTotalAnnualRemittance(array_filter($filters)); // Filter out null values
    exit;
}

if ($request_method == 'GET' && $uri == '/get-monthly-estimate') {
    $filters = [
        'month' => isset($_GET['month']) ? (int)$_GET['month'] : null,
        'year' => isset($_GET['year']) ? (int)$_GET['year'] : null,
    ];

    $adminController->getMonthlyEstimate(array_filter($filters)); // Filter out null values
    exit;
}








