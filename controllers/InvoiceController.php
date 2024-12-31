<?php
require_once 'config/database.php';

class InvoiceController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Create a new invoice for the given taxpayer and revenue items.
     */
    public function createInvoice($data) {
        // Validate required fields
        if (!isset($data['tax_number'], $data['invoice_type'], $data['revenue_heads'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields: tax_number, invoice_type, revenue_heads']);
            http_response_code(400); // Bad request
            return;
        }

        // Ensure invoice_type is valid
        if (!in_array($data['invoice_type'], ['direct', 'demand notice'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid invoice_type. Must be "direct" or "demand notice"']);
            http_response_code(400); // Bad request
            return;
        }

        // Validate revenue heads (ensure it's an array)
        if (!is_array($data['revenue_heads']) || empty($data['revenue_heads'])) {
            echo json_encode(['status' => 'error', 'message' => 'Revenue heads must be a non-empty array']);
            http_response_code(400); // Bad request
            return;
        }

        // Generate unique invoice number
        $invoice_number = $this->generateUniqueInvoiceNumber();

        // Prepare values for insertion
        $tax_number = $data['tax_number'];
        $invoice_type = $data['invoice_type'];
        $tax_office = isset($data['tax_office']) ? $data['tax_office'] : null;
        $lga = isset($data['lga']) ? $data['lga'] : null;
        $description = isset($data['description']) ? $data['description'] : null;

        // Calculate total amount and due date based on revenue heads
        $total_amount = 0;
        $due_dates = [];

        foreach ($data['revenue_heads'] as $revenue) {
            // Example structure: ['revenue_head_id' => 1, 'amount' => 5000.00]
            if (!isset($revenue['revenue_head_id'], $revenue['amount'])) {
                echo json_encode(['status' => 'error', 'message' => 'Each revenue head must include revenue_head_id and amount']);
                http_response_code(400); // Bad request
                return;
            }

            // Fetch revenue head information (including frequency)
            $revenue_head_info = $this->getRevenueHeadInfo($revenue['revenue_head_id']);
            if (!$revenue_head_info) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid revenue head: ' . $revenue['revenue_head_id']]);
                http_response_code(400); // Bad request
                return;
            }

            // Calculate due date based on frequency
            $due_date = $this->calculateDueDate($revenue_head_info['frequency']);
            $due_dates[] = $due_date;

            // Accumulate total amount
            $total_amount += $revenue['amount'];
        }

        // Use the earliest due date among all revenue heads
        $due_date = min($due_dates);

        // Insert invoice into the database
        $revenue_heads_json = json_encode($data['revenue_heads']); // Store revenue heads as a JSON array

        $query = "INSERT INTO invoices (tax_number, invoice_type, tax_office, lga, revenue_head, invoice_number, due_date, payment_status, amount_paid, description, date_created)
                  VALUES (?, ?, ?, ?, ?, ?, ?, 'unpaid', ?, ?, NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'sssssssss',
            $tax_number,
            $invoice_type,
            $tax_office,
            $lga,
            $revenue_heads_json,
            $invoice_number,
            $due_date,
            $total_amount,   // Store total amount in 'amount_paid'
            $description
        );

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Invoice created successfully', 'invoice_number' => $invoice_number]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create invoice: ' . $stmt->error]);
            http_response_code(500); // Internal Server Error
        }

        $stmt->close();
    }

    public function createDemandNotice($data) {
        // Validate required fields
        if (!isset($data['tax_number'], $data['revenue_heads'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields: tax_number, revenue_heads']);
            http_response_code(400); // Bad request
            return;
        }
    
        // Validate revenue heads (ensure it's an array)
        if (!is_array($data['revenue_heads']) || empty($data['revenue_heads'])) {
            echo json_encode(['status' => 'error', 'message' => 'Revenue heads must be a non-empty array']);
            http_response_code(400); // Bad request
            return;
        }
    
        // Generate unique invoice number
        $invoice_number = $this->generateUniqueDemandNoticeInvoiceNumber();
    
        // Prepare values for insertion
        $tax_number = $data['tax_number'];
        $invoice_type = 'demand notice';
        $tax_office = isset($data['tax_office']) ? $data['tax_office'] : null;
        $lga = isset($data['lga']) ? $data['lga'] : null;
        $file_number = isset($data['file_number']) ? $data['file_number'] : null;
        $description = isset($data['description']) ? $data['description'] : null;
    
        // Collect due dates based on revenue heads
        $due_dates = [];
    
        foreach ($data['revenue_heads'] as $revenue) {
            // Example structure: ['revenue_head_id' => 1, 'previous_year_date' => '2023', 'current_year_date' => '2024']
            if (!isset($revenue['revenue_head_id'], $revenue['previous_year_date'], $revenue['current_year_date'])) {
                echo json_encode(['status' => 'error', 'message' => 'Each revenue head must include revenue_head_id, previous_year_date, and current_year_date']);
                http_response_code(400); // Bad request
                return;
            }
    
            // Fetch revenue head information (including frequency)
            $revenue_head_info = $this->getRevenueHeadInfo($revenue['revenue_head_id']);
            if (!$revenue_head_info) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid revenue head: ' . $revenue['revenue_head_id']]);
                http_response_code(400); // Bad request
                return;
            }
    
            // Calculate due date based on frequency
            $due_date = $this->calculateDueDate($revenue_head_info['frequency']);
            $due_dates[] = $due_date;
        }
    
        // Use the earliest due date among all revenue heads
        $due_date = min($due_dates);
    
        // Insert invoice into the database
        $revenue_heads_json = json_encode($data['revenue_heads']); // Store revenue heads as a JSON array
    
        $query = "INSERT INTO demand_notices (file_number, tax_number, invoice_type, tax_office, lga, revenue_head, invoice_number, due_date, payment_status, description, date_created)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'unpaid', ?, NOW())";
    
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'sssssssss',
            $file_number,
            $tax_number,
            $invoice_type,
            $tax_office,
            $lga,
            $revenue_heads_json,
            $invoice_number,
            $due_date,
            $description
        );
    
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Demand notice created successfully', 'invoice_number' => $invoice_number]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create demand notice: ' . $stmt->error]);
            http_response_code(500); // Internal Server Error
        }
    
        $stmt->close();
    }
    

    /**
     * Generate a unique invoice number.
     */
    protected function generateUniqueInvoiceNumber() {
        $invoice_number = 'INV-' . strtoupper(uniqid());

        // Ensure no duplicate invoice numbers
        $query = "SELECT id FROM invoices WHERE invoice_number = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $invoice_number);
        $stmt->execute();
        $stmt->store_result();

        // If duplicate found, recursively generate a new one
        if ($stmt->num_rows > 0) {
            $stmt->close();
            return $this->generateUniqueInvoiceNumber();
        }

        $stmt->close();
        return $invoice_number;
    }

    protected function generateUniqueDemandNoticeInvoiceNumber() {
        $invoice_number = 'CDN-' . strtoupper(uniqid());

        // Ensure no duplicate invoice numbers
        $query = "SELECT id FROM demand_notices WHERE invoice_number = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $invoice_number);
        $stmt->execute();
        $stmt->store_result();

        // If duplicate found, recursively generate a new one
        if ($stmt->num_rows > 0) {
            $stmt->close();
            return $this->generateUniqueDemandNoticeInvoiceNumber();
        }

        $stmt->close();
        return $invoice_number;
    }
    /**
     * Fetch the revenue head information by its ID.
     */
    private function getRevenueHeadInfo($revenue_head_id) {
        $query = "SELECT * FROM revenue_heads WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $revenue_head_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $revenue_head = $result->fetch_assoc();
        $stmt->close();

        return $revenue_head;
    }

    /**
     * Calculate due date based on frequency.
     */
    private function calculateDueDate($frequency) {
        $current_date = new DateTime();

        switch (strtolower($frequency)) {
            case 'yearly':
                $current_date->modify('+1 year');
                break;
            case 'monthly':
                $current_date->modify('+1 month');
                break;
            case 'quarterly':
                $current_date->modify('+3 months');
                break;
            case 'weekly':
                $current_date->modify('+1 week');
                break;
            default:
                $current_date->modify('+1 month'); // Default to 1 month if frequency is not recognized
        }

        return $current_date->format('Y-m-d');
    }

    /**
     * Fetch all invoices with pagination and filters, along with revenue head and MDA names.
     */
    public function getInvoices($filters, $page, $limit) {
        // Set default page and limit if not provided
        $page = isset($page) ? (int)$page : 1;
        $limit = isset($limit) ? (int)$limit : 10;
        $offset = ($page - 1) * $limit;
    
        // Base query for invoices without aggregating revenue head details
        $query = "
            SELECT 
                i.*, 
                COALESCE(tp.first_name, etp.first_name, su.name) AS tax_first_name,
                COALESCE(tp.surname, etp.last_name, NULL) AS tax_last_name,
                COALESCE(tp.email, etp.email, su.email) AS tax_email,
                COALESCE(tp.phone, etp.phone, su.phone) AS tax_phone,
                su.industry AS special_user_industry,
                su.staff_quota AS special_user_staff_quota,
                su.official_TIN AS special_user_official_tin,
                su.state AS special_user_state,
                su.lga AS special_user_lga,
                su.address AS special_user_address,
                su.category AS special_user_category
            FROM invoices i
            LEFT JOIN taxpayer tp ON tp.tax_number = i.tax_number
            LEFT JOIN enumerator_tax_payers etp ON etp.tax_number = i.tax_number
            LEFT JOIN special_users_ su ON su.payer_id = i.tax_number
            WHERE 1=1
        ";
        
        $params = [];
        $types = '';
    
        // Apply filters
        if (isset($filters['invoice_number'])) {
            $query .= " AND i.invoice_number = ?";
            $params[] = $filters['invoice_number'];
            $types .= 's';
        }
    
        if (isset($filters['tax_number'])) {
            $query .= " AND i.tax_number = ?";
            $params[] = $filters['tax_number'];
            $types .= 's';
        }
    
        if (isset($filters['invoice_type'])) {
            $query .= " AND i.invoice_type = ?";
            $params[] = $filters['invoice_type'];
            $types .= 's';
        }
    
        if (isset($filters['payment_status'])) {
            $query .= " AND i.payment_status = ?";
            $params[] = $filters['payment_status'];
            $types .= 's';
        }
    
        if (isset($filters['due_date_start']) && isset($filters['due_date_end'])) {
            $query .= " AND i.due_date BETWEEN ? AND ?";
            $params[] = $filters['due_date_start'];
            $params[] = $filters['due_date_end'];
            $types .= 'ss';
        }
    
        if (isset($filters['date_created_start']) && isset($filters['date_created_end'])) {
            $query .= " AND i.date_created BETWEEN ? AND ?";
            $params[] = $filters['date_created_start'];
            $params[] = $filters['date_created_end'];
            $types .= 'ss';
        }
    
        //  
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
    
        // Prepare and execute the query
        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params); // Bind parameters dynamically
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $invoices = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    
        // Process each invoice to add revenue head details
        foreach ($invoices as &$invoice) {
            $revenueHeads = json_decode($invoice['revenue_head'], true);
            $detailedRevenueHeads = [];
    
            // For each revenue head in the JSON array, fetch additional details
            foreach ($revenueHeads as $revenueHead) {
                $revenueHeadId = $revenueHead['revenue_head_id'];
                $amount = $revenueHead['amount'];
    
                // Fetch revenue head details and MDA name
                $revenueHeadDetails = $this->getRevenueHeadDetails($revenueHeadId);
                if ($revenueHeadDetails) {
                    $detailedRevenueHeads[] = [
                        'revenue_head_id' => $revenueHeadId,
                        'item_name' => $revenueHeadDetails['item_name'],
                        'amount' => $amount,
                        'mda_name' => $revenueHeadDetails['mda_name']
                    ];
                }
            }
    
            // Add detailed revenue heads to the invoice
            $invoice['revenue_heads'] = $detailedRevenueHeads;
        }
    
        // Fetch total number of invoices for pagination
        $count_query = "SELECT COUNT(*) as total FROM invoices WHERE 1=1";
        $stmt_count = $this->conn->prepare($count_query);
        $stmt_count->execute();
        $count_result = $stmt_count->get_result();
        $total_invoices = $count_result->fetch_assoc()['total'];
        $total_pages = ceil($total_invoices / $limit);
        $stmt_count->close();
    
        // Return the paginated and filtered invoices
        echo json_encode([
            'status' => 'success',
            'data' => [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_invoices' => $total_invoices,
                'invoices' => $invoices
            ]
        ]);
    }

    public function getDemandNoticeInvoices($filters, $page, $limit)
    {
        // Set default page and limit if not provided
        $page = isset($page) ? (int)$page : 1;
        $limit = isset($limit) ? (int)$limit : 10;
        $offset = ($page - 1) * $limit;

        // Base query for demand notice invoices
        $query = "
            SELECT 
                dn.*, 
                COALESCE(tp.first_name, etp.first_name, su.name) AS tax_first_name,
                COALESCE(tp.surname, etp.last_name, NULL) AS tax_last_name,
                COALESCE(tp.email, etp.email, su.email) AS tax_email,
                COALESCE(tp.phone, etp.phone, su.phone) AS tax_phone,
                COALESCE(tb.business_type, etp.business_type, NULL) AS tax_business_type,
                COALESCE(tp.address, etp.address, su.address, NULL) AS address,
                su.industry AS special_user_industry,
                su.staff_quota AS special_user_staff_quota,
                su.official_TIN AS special_user_official_tin,
                su.state AS special_user_state,
                su.lga AS special_user_lga,
                su.address AS special_user_address,
                su.category AS special_user_category
            FROM demand_notices dn
            LEFT JOIN taxpayer tp ON tp.tax_number = dn.tax_number
            LEFT JOIN taxpayer_business tb ON tb.taxpayer_id = tp.id
            LEFT JOIN enumerator_tax_payers etp ON etp.tax_number = dn.tax_number
            LEFT JOIN special_users_ su ON su.payer_id = dn.tax_number
            WHERE 1 = 1
        ";

        $params = [];
        $types = '';

        // Apply filters
        if (isset($filters['invoice_number'])) {
            $query .= " AND dn.invoice_number = ?";
            $params[] = $filters['invoice_number'];
            $types .= 's';
        }

        if (isset($filters['tax_number'])) {
            $query .= " AND dn.tax_number = ?";
            $params[] = $filters['tax_number'];
            $types .= 's';
        }

        if (isset($filters['payment_status'])) {
            $query .= " AND dn.payment_status = ?";
            $params[] = $filters['payment_status'];
            $types .= 's';
        }

        if (isset($filters['due_date_start']) && isset($filters['due_date_end'])) {
            $query .= " AND dn.due_date BETWEEN ? AND ?";
            $params[] = $filters['due_date_start'];
            $params[] = $filters['due_date_end'];
            $types .= 'ss';
        }

        if (isset($filters['date_created_start']) && isset($filters['date_created_end'])) {
            $query .= " AND dn.date_created BETWEEN ? AND ?";
            $params[] = $filters['date_created_start'];
            $params[] = $filters['date_created_end'];
            $types .= 'ss';
        }

        // Pagination
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        // Prepare and execute the query
        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $demandNotices = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Process each demand notice to add revenue head details
        $filteredDemandNotices = [];
        foreach ($demandNotices as &$demandNotice) {
            $revenueHeads = json_decode($demandNotice['revenue_head'], true);
            $detailedRevenueHeads = [];
            $includeDemandNotice = false;

            // For each revenue head in the JSON array, fetch additional details
            foreach ($revenueHeads as $revenueHead) {
                $revenueHeadId = $revenueHead['revenue_head_id'];

                // Filter by item_code or mda_code if provided
                $revenueHeadDetails = $this->getRevenueHeadDetails($revenueHeadId);

                if ($revenueHeadDetails) {
                    $includeDemandNotice = true;

                    $detailedRevenueHeads[] = [
                        'revenue_head_id' => $revenueHeadId,
                        'item_name' => $revenueHeadDetails['item_name'],
                        'previous_year_date' => $revenueHead['previous_year_date'] ?? null,
                        'previous_year_amount' => $revenueHead['previous_year_amount'] ?? null,
                        'current_year_date' => $revenueHead['current_year_date'] ?? null,
                        'current_year_amount' => $revenueHead['current_year_amount'] ?? null,
                        'mda_name' => $revenueHeadDetails['mda_name'],
                        'mda_code' => $revenueHeadDetails['mda_code'],
                        'item_code' => $revenueHeadDetails['item_code']
                    ];
                }
            }

            // Add detailed revenue heads to the demand notice
            if ($includeDemandNotice) {
                $demandNotice['revenue_heads'] = $detailedRevenueHeads;
                $filteredDemandNotices[] = $demandNotice;
            }
        }

        // Pagination
        $totalRecords = count($filteredDemandNotices);
        $paginatedDemandNotices = array_slice($filteredDemandNotices, $offset, $limit);
        $totalPages = ceil($totalRecords / $limit);

        // Return the paginated and filtered demand notices
        echo json_encode([
            'status' => 'success',
            'data' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_demand_notices' => $totalRecords,
                'demand_notices' => $paginatedDemandNotices
            ]
        ]);
    }

    
    
    private function getRevenueHeadDetails($revenueHeadId) {
        $query = "
            SELECT rh.item_name, rh.item_code, m.mda_code, m.fullname AS mda_name 
            FROM revenue_heads rh 
            LEFT JOIN mda m ON rh.mda_id = m.id 
            WHERE rh.id = ?
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $revenueHeadId);
        $stmt->execute();
        $result = $stmt->get_result();
        $details = $result->fetch_assoc();
        $stmt->close();
    
        return $details;
    }

    public function getInvoiceSummary($mda_id = null) {
        // Initialize totals
        $totalInvoices = 0;
        $totalAmountInvoiced = 0;
        $totalAmountPaid = 0;
    
        // Fetch revenue head IDs for the specified MDA (if mda_id is provided)
        $revenueHeadIds = [];
        if ($mda_id) {
            $revenueHeadQuery = "SELECT id FROM revenue_heads WHERE mda_id = ?";
            $stmt = $this->conn->prepare($revenueHeadQuery);
            $stmt->bind_param('i', $mda_id);
            $stmt->execute();
            $result = $stmt->get_result();
    
            while ($row = $result->fetch_assoc()) {
                $revenueHeadIds[] = $row['id'];
            }
            $stmt->close();
    
            // If no revenue heads are found for the MDA, return early with zero totals
            if (empty($revenueHeadIds)) {
                echo json_encode([
                    "status" => "success",
                    "data" => [
                        "total_invoices" => 0,
                        "total_amount_invoiced" => 0,
                        "total_amount_paid" => 0,
                    ]
                ]);
                return;
            }
        }
    
        // Fetch all invoices
        $invoiceQuery = "SELECT revenue_head, payment_status, amount_paid FROM invoices";
        $stmt = $this->conn->prepare($invoiceQuery);
        $stmt->execute();
        $result = $stmt->get_result();
    
        // Loop through invoices and calculate totals
        while ($row = $result->fetch_assoc()) {
            // Decode the revenue_head JSON
            $revenueHeads = json_decode($row['revenue_head'], true);
            $includeInvoice = false;
            $invoiceInvoicedAmount = 0;
            $invoicePaidAmount = 0;
    
            // Loop through revenue heads in the JSON
            foreach ($revenueHeads as $revenueHead) {
                // Check if the revenue head belongs to the specified MDA
                if (!$mda_id || in_array($revenueHead['revenue_head_id'], $revenueHeadIds)) {
                    $includeInvoice = true;
                    $invoiceInvoicedAmount += (float)$revenueHead['amount']; // Add to total amount invoiced
                }
            }
    
            // Count the invoice and add amounts if it matches the MDA filter
            if ($includeInvoice) {
                $totalInvoices++;
                $totalAmountInvoiced += $invoiceInvoicedAmount;
    
                if ($row['payment_status'] === 'paid') {
                    $totalAmountPaid += $invoiceInvoicedAmount; // Use only the relevant revenue head amounts
                }
            }
        }
        $stmt->close();
    
        // Return structured response
        echo json_encode([
            "status" => "success",
            "data" => [
                "total_invoices" => $totalInvoices,
                "total_amount_invoiced" => $totalAmountInvoiced,
                "total_amount_paid" => $totalAmountPaid,
            ]
        ]);
    }

    public function getInvoiceStatsByTaxNumber($taxNumber){
        // Check if tax_number is provided
        if (empty($taxNumber)) {
            echo json_encode(['status' => 'error', 'message' => 'Tax number is required']);
            http_response_code(400);
            return;
        }

        // Initialize statistics
        $totalInvoices = 0;
        $totalPaidInvoices = 0;
        $totalDueInvoices = 0;
        $totalUnpaidInvoices = 0; // New field
        $totalAmountPaid = 0.0;
        $totalAmountDue = 0.0;
        $totalAmountUnpaid = 0.0; // New field

        // Query to fetch invoices for the given tax number
        $query = "
            SELECT 
                id, 
                payment_status, 
                amount_paid, 
                revenue_head, 
                due_date 
            FROM invoices 
            WHERE tax_number = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $taxNumber);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $totalInvoices++;

            // Decode revenue_head JSON to calculate amounts
            $revenueHeads = json_decode($row['revenue_head'], true);
            $invoiceAmount = 0.0;

            foreach ($revenueHeads as $revenueHead) {
                $invoiceAmount += (float)$revenueHead['amount'];
            }

            // Check payment status and categorize
            if ($row['payment_status'] === 'paid') {
                $totalPaidInvoices++;
                $totalAmountPaid += (float)$row['amount_paid'];
            } elseif ($row['payment_status'] === 'unpaid') {
                $totalUnpaidInvoices++;
                $totalAmountUnpaid += $invoiceAmount;
            }

            // Check if due
            if (strtotime($row['due_date']) < time() && $row['payment_status'] !== 'paid') {
                $totalDueInvoices++;
                $totalAmountDue += $invoiceAmount;
            }
        }

        $stmt->close();

        // Return the statistics
        echo json_encode([
            "status" => "success",
            "data" => [
                "total_invoices" => $totalInvoices,
                "total_paid_invoices" => $totalPaidInvoices,
                "total_due_invoices" => $totalDueInvoices,
                "total_unpaid_invoices" => $totalUnpaidInvoices, // New field
                "total_amount_paid" => $totalAmountPaid,
                "total_amount_due" => $totalAmountDue,
                "total_amount_unpaid" => $totalAmountUnpaid // New field
            ]
        ]);
    }

    public function getSpecialUserStats($payerId, $filters){
        // Validate input
        if (empty($payerId)) {
            echo json_encode(['status' => 'error', 'message' => 'Payer ID is required']);
            http_response_code(400);
            return;
        }

        // Default response
        $response = [
            'total_monthly_remittance' => 0.0,
            'total_annual_remittance' => 0.0,
            'total_annual_estimate' => 0.0,
            'total_monthly_estimate' => 0.0
        ];

        // Handle date filters
        $currentMonth = date('Y-m');
        $currentYear = date('Y');
        $monthFilter = isset($filters['month']) ? $filters['month'] : $currentMonth;
        $yearFilter = isset($filters['year']) ? $filters['year'] : $currentYear;

        // Total Monthly Remittance
        $monthlyQuery = "
            SELECT SUM(pc.amount_paid) AS total_remittance
            FROM payment_collection pc
            JOIN invoices i ON pc.invoice_number = i.invoice_number
            WHERE i.tax_number = ?
            AND DATE_FORMAT(pc.date_payment_created, '%Y-%m') = ?";
        $stmt = $this->conn->prepare($monthlyQuery);
        $stmt->bind_param('ss', $payerId, $monthFilter);
        $stmt->execute();
        $stmt->bind_result($monthlyRemittance);
        $stmt->fetch();
        $response['total_monthly_remittance'] = $monthlyRemittance ?: 0.0;
        $stmt->close();

        // Total Annual Remittance
        $annualQuery = "
            SELECT SUM(pc.amount_paid) AS total_remittance
            FROM payment_collection pc
            JOIN invoices i ON pc.invoice_number = i.invoice_number
            WHERE i.tax_number = ?
            AND DATE_FORMAT(pc.date_payment_created, '%Y') = ?";
        $stmt = $this->conn->prepare($annualQuery);
        $stmt->bind_param('ss', $payerId, $yearFilter);
        $stmt->execute();
        $stmt->bind_result($annualRemittance);
        $stmt->fetch();
        $response['total_annual_remittance'] = $annualRemittance ?: 0.0;
        $stmt->close();

        // Total Monthly Estimate
        $monthlyEstimateQuery = "
            SELECT SUM(esb.monthly_tax_payable) AS total_monthly_estimate
            FROM employee_salary_and_benefits esb
            JOIN special_user_employees sue ON esb.employee_id = sue.id
            WHERE sue.associated_special_user_id = ?";
        $stmt = $this->conn->prepare($monthlyEstimateQuery);
        $stmt->bind_param('i', $payerId);
        $stmt->execute();
        $stmt->bind_result($monthlyEstimate);
        $stmt->fetch();
        $response['total_monthly_estimate'] = $monthlyEstimate ?: 0.0;
        $stmt->close();

        // Total Annual Estimate
        $response['total_annual_estimate'] = $response['total_monthly_estimate'] * 12;

        // Return the response
        echo json_encode(['status' => 'success', 'data' => $response]);
    }

    public function getDemandNoticeMetrics($filters)
    {
        // Base query to fetch demand notices
        $query = "SELECT * FROM demand_notices WHERE 1 = 1";

        $params = [];
        $types = '';

        // Apply filters
        if (isset($filters['tax_number'])) {
            $query .= " AND tax_number = ?";
            $params[] = $filters['tax_number'];
            $types .= 's';
        }

        if (isset($filters['mda_code'])) {
            $query .= " AND EXISTS (
                SELECT 1 
                FROM revenue_heads rh 
                WHERE rh.mda_id = (SELECT id FROM mda WHERE mda_code = ?) 
                AND JSON_CONTAINS(demand_notices.revenue_head, JSON_OBJECT('revenue_head_id', rh.id))
            )";
            $params[] = $filters['mda_code'];
            $types .= 's';
        }

        if (isset($filters['item_code'])) {
            $query .= " AND EXISTS (
                SELECT 1 
                FROM revenue_heads rh 
                WHERE rh.item_code = ? 
                AND JSON_CONTAINS(demand_notices.revenue_head, JSON_OBJECT('revenue_head_id', rh.id))
            )";
            $params[] = $filters['item_code'];
            $types .= 's';
        }

        if (isset($filters['date_created_start']) && isset($filters['date_created_end'])) {
            $query .= " AND date_created BETWEEN ? AND ?";
            $params[] = $filters['date_created_start'];
            $params[] = $filters['date_created_end'];
            $types .= 'ss';
        }

        // Prepare and execute query
        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $demandNotices = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Initialize counters
        $totalDemandNotices = 0;
        $totalAmountGenerated = 0;
        $totalAmountPaid = 0;
        $totalAmountUnpaid = 0;

        // Process each demand notice
        foreach ($demandNotices as $demandNotice) {
            $totalDemandNotices++;

            // Decode revenue head JSON to PHP array
            $revenueHeads = json_decode($demandNotice['revenue_head'], true);

            // Sum up amounts for generated, paid, and unpaid notices
            foreach ($revenueHeads as $revenueHead) {
                $currentYearAmount = $revenueHead['current_year_amount'] ?? 0;

                $totalAmountGenerated += $currentYearAmount;

                if ($demandNotice['payment_status'] === 'paid') {
                    $totalAmountPaid += $currentYearAmount;
                } elseif ($demandNotice['payment_status'] === 'unpaid') {
                    $totalAmountUnpaid += $currentYearAmount;
                }
            }
        }

        // Return the metrics
        echo json_encode([
            'status' => 'success',
            'data' => [
                'total_demand_notices' => $totalDemandNotices,
                'total_amount_generated' => $totalAmountGenerated,
                'total_amount_paid' => $totalAmountPaid,
                'total_amount_unpaid' => $totalAmountUnpaid
            ]
        ]);
    }






    
    
}

    

    
    
    

