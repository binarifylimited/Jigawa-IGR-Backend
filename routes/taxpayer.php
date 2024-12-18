<?php
require_once 'controllers/TaxpayerController.php';

$taxpayerController = new TaxpayerController();

// Route: Check taxpayer verification status
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $uri === '/check-taxpayer-verification') {
    $queryParams = $_GET;
    $response = $taxpayerController->checkVerificationStatus($queryParams);

    // Set response header and output the response in JSON format
    header('Content-Type: application/json');
    echo $response;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $uri === '/verify-taxpayer') {
    // Parse JSON body
    $input = json_decode(file_get_contents('php://input'), true);
    $response = $taxpayerController->verifyTaxpayer($input);

    // Set response header and output the response in JSON format
    header('Content-Type: application/json');
    echo $response;
    exit;
}

// Route: Regenerate verification code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $uri === '/regenerate-verification-code') {
    // Parse JSON body
    $input = json_decode(file_get_contents('php://input'), true);
    $response = $taxpayerController->regenerateVerificationCode($input);

    // Set response header and output the response in JSON format
    header('Content-Type: application/json');
    echo $response;
    exit;
}

// Route: Get all taxpayers with filters
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $uri === '/get-taxpayers') {
    $queryParams = $_GET;
    $response = $taxpayerController->getAllTaxpayers($queryParams);

    // Set response header and output the response in JSON format
    header('Content-Type: application/json');
    echo $response;
    exit;
}

// Route: Get total registered taxpayers
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $uri === '/get-taxpayer-statistics') {
    $response = $taxpayerController->getTaxpayerStatistics();

    // Set response header and output the response in JSON format
    header('Content-Type: application/json');
    echo $response;
    exit;
}

// Route: Update TIN status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $uri === '/update-tin-status') {
    $input = json_decode(file_get_contents('php://input'), true);
    $response = $taxpayerController->updateTinStatus($input);

    // Set response header and output the response in JSON format
    header('Content-Type: application/json');
    echo $response;
    exit;
}
