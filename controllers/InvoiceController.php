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
    
    private function getRevenueHeadDetails($revenueHeadId) {
        $query = "
            SELECT rh.item_name, m.fullname AS mda_name 
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
    

    
    
    
}
