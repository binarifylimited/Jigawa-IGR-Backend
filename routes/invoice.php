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

if ($request_method === 'POST' && $uri === '/create-demand-notice') {
    $data = json_decode(file_get_contents("php://input"), true);
    $invoiceController->createDemandNotice($data);
    exit;
}


// Route: Fetch invoices with pagination and filters (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $uri === '/get-invoices') {
    // $decoded_token = authenticate();  // Authenticate the request
    // Call the register method in RegistrationController

    // Optionally check if the authenticated user has the role to create an admin
    // if ($decoded_token['role'] !== 'super_admin') {
    //     echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only super admins can register new users']);
    //     http_response_code(403); // Forbidden
    //     exit;
    // }
    $filters = [
        'invoice_number' => isset($_GET['invoice_number']) ? $_GET['invoice_number'] : null,
        'mda_id' => isset($_GET['mda_id']) ? $_GET['mda_id'] : null,
        'revenue_head_id' => isset($_GET['revenue_head_id']) ? $_GET['revenue_head_id'] : null,
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

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $uri === '/get-demand-notice-invoices') {
    // Get filters from the query parameters
    $filters = [
        'invoice_number' => isset($_GET['invoice_number']) ? $_GET['invoice_number'] : null,
        'tax_number' => isset($_GET['tax_number']) ? $_GET['tax_number'] : null,
        'payment_status' => isset($_GET['payment_status']) ? $_GET['payment_status'] : null,
        'due_date_start' => isset($_GET['due_date_start']) ? $_GET['due_date_start'] : null,
        'due_date_end' => isset($_GET['due_date_end']) ? $_GET['due_date_end'] : null,
        'date_created_start' => isset($_GET['date_created_start']) ? $_GET['date_created_start'] : null,
        'date_created_end' => isset($_GET['date_created_end']) ? $_GET['date_created_end'] : null,
        'mda_code' => isset($_GET['mda_code']) ? $_GET['mda_code'] : null,
        'item_code' => isset($_GET['item_code']) ? $_GET['item_code'] : null,
    ];

    // Pagination parameters
    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? $_GET['limit'] : 10;

    // Call the getDemandNoticeInvoices function in InvoiceController
    $invoiceController->getDemandNoticeInvoices(array_filter($filters), $page, $limit);
    exit;
}


if ($request_method == 'GET' && $uri == '/invoices-summary') {
    $mda_id = isset($_GET['mda_id']) ? (int)$_GET['mda_id'] : null; // Optional MDA filter
    $invoiceController->getInvoiceSummary($mda_id);
    exit;
}

if ($request_method === 'GET' && $uri === '/get-taxpayer-invoice-stats') {
    $taxNumber = isset($_GET['tax_number']) ? $_GET['tax_number'] : null;
    $invoiceController->getInvoiceStatsByTaxNumber($taxNumber);
    exit;
}

if ($request_method === 'GET' && $uri === '/get-special-user-stats') {
    $payerId = isset($_GET['payer_id']) ? $_GET['payer_id'] : null;
    $filters = [
        'month' => isset($_GET['month']) ? $_GET['month'] : null,
        'year' => isset($_GET['year']) ? $_GET['year'] : null
    ];
    $invoiceController->getSpecialUserStats($payerId, $filters);
    exit;
}

if ($request_method === 'GET' && $uri === '/get-demand-notice-metrics') {
    // Retrieve filters from query parameters
    $filters = [
        'tax_number' => isset($_GET['tax_number']) ? $_GET['tax_number'] : null,
        'mda_code' => isset($_GET['mda_code']) ? $_GET['mda_code'] : null,
        'item_code' => isset($_GET['item_code']) ? $_GET['item_code'] : null,
        'date_created_start' => isset($_GET['date_created_start']) ? $_GET['date_created_start'] : null,
        'date_created_end' => isset($_GET['date_created_end']) ? $_GET['date_created_end'] : null,
    ];

    // Call the getDemandNoticeMetrics function with the provided filters
    $invoiceController->getDemandNoticeMetrics(array_filter($filters));
    exit;
}


// You can add more invoice-related routes here (for example, fetching invoice details, updating an invoice, etc.)
