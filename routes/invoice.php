<?php
require_once 'controllers/AuthController.php';
require_once 'controllers/InvoiceController.php';

// Include the auth_helper where authenticate() is defined
require_once 'helpers/auth_helper.php';
// Create an instance of InvoiceController
$invoiceController = new InvoiceController();


// Route: Create a new invoice (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $uri === '/create-invoice') {
    // Decode the input JSON

    $input = json_decode(file_get_contents('php://input'), true);

    // Call the createInvoice method in InvoiceController
    $invoiceController->createInvoice($input);
    exit;
}

// Route: Fetch invoices with pagination and filters (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $uri === '/get-invoices') {
    $decoded_token = authenticate();  // Authenticate the request
    // Call the register method in RegistrationController

    // Optionally check if the authenticated user has the role to create an admin
    if ($decoded_token['role'] !== 'super_admin') {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only super admins can register new users']);
        http_response_code(403); // Forbidden
        exit;
    }
    $filters = [
        'invoice_number' => isset($_GET['invoice_number']) ? $_GET['invoice_number'] : null,
        'tax_number' => isset($_GET['tax_number']) ? $_GET['tax_number'] : null,
        'invoice_type' => isset($_GET['invoice_type']) ? $_GET['invoice_type'] : null,
        'payment_status' => isset($_GET['payment_status']) ? $_GET['payment_status'] : null,
        'due_date_start' => isset($_GET['due_date_start']) ? $_GET['due_date_start'] : null,
        'due_date_end' => isset($_GET['due_date_end']) ? $_GET['due_date_end'] : null,
        'date_created_start' => isset($_GET['date_created_start']) ? $_GET['date_created_start'] : null,
        'date_created_end' => isset($_GET['date_created_end']) ? $_GET['date_created_end'] : null
    ];

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

    $invoiceController->getInvoices(array_filter($filters), $page, $limit);
    exit;
}

// You can add more invoice-related routes here (for example, fetching invoice details, updating an invoice, etc.)
