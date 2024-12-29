<?php
require_once 'config/database.php';

class RevenueHeadController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Create a new revenue head (or multiple revenue heads if category is an array) for a specific MDA.
     */
    public function createRevenueHead($data) {
        // Validate required fields for revenue head
        if (!isset($data['mda_id'], $data['item_code'], $data['item_name'], $data['category'], $data['amount'], $data['frequency'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields: mda_id, item_code, item_name, category, amount, frequency']);
            http_response_code(400); // Bad request
            return;
        }

        // Check if category is an array
        $categories = is_array($data['category']) ? $data['category'] : [$data['category']];

        // Validate each category
        $valid_categories = ['individual', 'corporate', 'state', 'federal'];
        foreach ($categories as $category) {
            if (!in_array(strtolower($category), $valid_categories)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid category. Must be one of: individual, corporate, state, federal']);
                http_response_code(400); // Bad request
                return;
            }
        }

        // Check if the item_code or item_name already exists
        $query = "SELECT id FROM revenue_heads WHERE item_code = ? OR item_name = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ss', $data['item_code'], $data['item_name']);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Revenue head with this item code or item name already exists']);
            http_response_code(409); // Conflict
            $stmt->close();
            return;
        }

        // Insert a revenue head for each category
        $mda_id = $data['mda_id'];
        $item_code = $data['item_code'];
        $item_name = $data['item_name'];
        $amount = $data['amount'];
        $status = $data['status'] ?? 1;  // Default status to active
        $frequency = $data['frequency'];

        $created_ids = [];

        foreach ($categories as $category) {
            // Insert revenue head into 'revenue_heads' table
            $query = "INSERT INTO revenue_heads (mda_id, item_code, item_name, category, amount, status, frequency, time_in) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                'isssdss',
                $mda_id,
                $item_code,
                $item_name,
                $category,
                $amount,
                $status,
                $frequency
            );

            if (!$stmt->execute()) {
                echo json_encode(['status' => 'error', 'message' => 'Error creating revenue head for category: ' . $category . '. Error: ' . $stmt->error]);
                http_response_code(500); // Internal Server Error
                return;
            }

            // Collect the inserted revenue head IDs
            $created_ids[] = $stmt->insert_id;
        }

        // Return success response with created revenue head IDs
        echo json_encode([
            'status' => 'success',
            'message' => 'Revenue heads created successfully',
            'created_revenue_head_ids' => $created_ids
        ]);

        $stmt->close();
    }
    /**
     * Create multiple revenue heads for different MDAs or categories.
     */
    public function createMultipleRevenueHeads($data) {
        if (!isset($data['revenue_heads']) || !is_array($data['revenue_heads'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing or invalid revenue_heads array']);
            http_response_code(400); // Bad request
            return;
        }

        $created_ids = [];
        $errors = [];

        // Loop through each revenue head in the array
        foreach ($data['revenue_heads'] as $revenue_head) {
            // Validate required fields for each revenue head
            if (!isset($revenue_head['mda_id'], $revenue_head['item_code'], $revenue_head['item_name'], $revenue_head['category'], $revenue_head['amount'], $revenue_head['frequency'])) {
                $errors[] = ['message' => 'Missing required fields', 'revenue_head' => $revenue_head];
                continue; // Skip this entry
            }

            // Check if category is an array
            $categories = is_array($revenue_head['category']) ? $revenue_head['category'] : [$revenue_head['category']];

            // Validate each category
            $valid_categories = ['individual', 'corporate', 'state', 'federal'];
            foreach ($categories as $category) {
                if (!in_array(strtolower($category), $valid_categories)) {
                    $errors[] = ['message' => 'Invalid category: ' . $category, 'revenue_head' => $revenue_head];
                    continue 2; // Skip to the next revenue head if category is invalid
                }
            }

            // Check if the item_code or item_name already exists
            $query = "SELECT id FROM revenue_heads WHERE item_code = ? OR item_name = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('ss', $revenue_head['item_code'], $revenue_head['item_name']);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $errors[] = ['message' => 'Revenue head with this item code or item name already exists', 'revenue_head' => $revenue_head];
                $stmt->close();
                continue; // Skip this entry
            }

            $stmt->close();

            // Insert a revenue head for each category
            $mda_id = $revenue_head['mda_id'];
            $item_code = $revenue_head['item_code'];
            $item_name = $revenue_head['item_name'];
            $amount = $revenue_head['amount'];
            $status = $revenue_head['status'] ?? 1;  // Default status to active
            $frequency = $revenue_head['frequency'];

            foreach ($categories as $category) {
                // Insert revenue head into 'revenue_heads' table
                $query = "INSERT INTO revenue_heads (mda_id, item_code, item_name, category, amount, status, frequency, time_in) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

                $stmt = $this->conn->prepare($query);
                $stmt->bind_param(
                    'isssdss',
                    $mda_id,
                    $item_code,
                    $item_name,
                    $category,
                    $amount,
                    $status,
                    $frequency
                );

                if (!$stmt->execute()) {
                    $errors[] = ['message' => 'Error creating revenue head for category: ' . $category . '. Error: ' . $stmt->error, 'revenue_head' => $revenue_head];
                    continue; // Skip this entry
                }

                // Collect the inserted revenue head IDs
                $created_ids[] = $stmt->insert_id;
            }

            $stmt->close();
        }

        // Return success response with created revenue head IDs and any errors
        if (!empty($created_ids)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Revenue heads created successfully',
                'created_revenue_head_ids' => $created_ids,
                'errors' => $errors
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to create any revenue heads',
                'errors' => $errors
            ]);
        }
    }

    /**
     * Update a revenue head's information.
     */
    public function updateRevenueHead($data) {
        // Validate required field: revenue_head_id
        if (!isset($data['revenue_head_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required field: revenue_head_id']);
            http_response_code(400); // Bad request
            return;
        }

        $revenue_head_id = $data['revenue_head_id'];

        // Check if the revenue head exists
        $query = "SELECT id FROM revenue_heads WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $revenue_head_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Revenue head not found']);
            http_response_code(404); // Not found
            $stmt->close();
            return;
        }

        $stmt->close();

        // Optionally, check for duplicates (if updating item_code or item_name)
        if (isset($data['item_code']) || isset($data['item_name'])) {
            $query = "SELECT id FROM revenue_heads WHERE (item_code = ? OR item_name = ?) AND id != ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $item_code = $data['item_code'] ?? '';
            $item_name = $data['item_name'] ?? '';
            $stmt->bind_param('ssi', $item_code, $item_name, $revenue_head_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Revenue head with this item code or item name already exists']);
                http_response_code(409); // Conflict
                $stmt->close();
                return;
            }

            $stmt->close();
        }

        // Update the revenue head details
        $query = "UPDATE revenue_heads SET item_code = COALESCE(?, item_code), item_name = COALESCE(?, item_name), category = COALESCE(?, category), amount = COALESCE(?, amount), status = COALESCE(?, status), frequency = COALESCE(?, frequency) WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'sssdssi',
            $data['item_code'],
            $data['item_name'],
            $data['category'],
            $data['amount'],
            $data['status'],
            $data['frequency'],
            $revenue_head_id
        );

        if (!$stmt->execute()) {
            echo json_encode(['status' => 'error', 'message' => 'Error updating revenue head: ' . $stmt->error]);
            http_response_code(500); // Internal Server Error
            return;
        }

        $stmt->close();

        // Return success response
        echo json_encode([
            'status' => 'success',
            'message' => 'Revenue head updated successfully'
        ]);
    }

    /**
     * Fetch Revenue Head by various filters (id, item_code, item_name, category, status, mda_id).
     */
    // public function getRevenueHeadByFilters($filters) {
    //     // Base query
    //     $query = "SELECT * FROM revenue_heads WHERE 1 = 1"; // 1 = 1 is a dummy condition to simplify appending other conditions
    //     $params = [];
    //     $types = '';

    //     // Add conditions dynamically
    //     if (isset($filters['id'])) {
    //         $query .= " AND id = ?";
    //         $params[] = $filters['id'];
    //         $types .= 'i';
    //     }

    //     if (isset($filters['item_code'])) {
    //         $query .= " AND item_code = ?";
    //         $params[] = $filters['item_code'];
    //         $types .= 's';
    //     }

    //     if (isset($filters['item_name'])) {
    //         // Using LIKE for partial matching on item_name
    //         $query .= " AND item_name LIKE ?";
    //         $params[] = '%' . $filters['item_name'] . '%';
    //         $types .= 's';
    //     }

    //     if (isset($filters['category'])) {
    //         $query .= " AND category = ?";
    //         $params[] = $filters['category'];
    //         $types .= 's';
    //     }

    //     if (isset($filters['status'])) {
    //         $query .= " AND status = ?";
    //         $params[] = $filters['status'];
    //         $types .= 'i';
    //     }

    //     if (isset($filters['mda_id'])) {
    //         $query .= " AND mda_id = ?";
    //         $params[] = $filters['mda_id'];
    //         $types .= 'i';
    //     }

    //     // Prepare and bind the query
    //     $stmt = $this->conn->prepare($query);

    //     if (!empty($params)) {
    //         $stmt->bind_param($types, ...$params); // Spread operator for dynamic params
    //     }

    //     $stmt->execute();
    //     $result = $stmt->get_result();
    //     $revenue_heads = $result->fetch_all(MYSQLI_ASSOC);

    //     if (count($revenue_heads) > 0) {
    //         // Return matching revenue head(s)
    //         echo json_encode(['status' => 'success', 'data' => $revenue_heads]);
    //     } else {
    //         echo json_encode(['status' => 'error', 'message' => 'No matching revenue head found']);
    //         http_response_code(404); // Not found
    //     }

    //     $stmt->close();
    // }

    // public function getRevenueHeadByFilters($filters) {
    //     // Base query with JOIN to include MDA name
    //     $query = "
    //         SELECT 
    //             rh.*, 
    //             m.fullname AS mda_name 
    //         FROM 
    //             revenue_heads rh
    //         LEFT JOIN 
    //             mda m ON rh.mda_id = m.id
    //         WHERE 1 = 1"; // 1 = 1 is a dummy condition to simplify appending other conditions
        
    //     $params = [];
    //     $types = '';
    
    //     // Add conditions dynamically
    //     if (isset($filters['id'])) {
    //         $query .= " AND rh.id = ?";
    //         $params[] = $filters['id'];
    //         $types .= 'i';
    //     }
    
    //     if (isset($filters['item_code'])) {
    //         $query .= " AND rh.item_code = ?";
    //         $params[] = $filters['item_code'];
    //         $types .= 's';
    //     }
    
    //     if (isset($filters['item_name'])) {
    //         // Using LIKE for partial matching on item_name
    //         $query .= " AND rh.item_name LIKE ?";
    //         $params[] = '%' . $filters['item_name'] . '%';
    //         $types .= 's';
    //     }
    
    //     if (isset($filters['category'])) {
    //         $query .= " AND rh.category = ?";
    //         $params[] = $filters['category'];
    //         $types .= 's';
    //     }
    
    //     if (isset($filters['status'])) {
    //         $query .= " AND rh.status = ?";
    //         $params[] = $filters['status'];
    //         $types .= 'i';
    //     }
    
    //     if (isset($filters['mda_id'])) {
    //         $query .= " AND rh.mda_id = ?";
    //         $params[] = $filters['mda_id'];
    //         $types .= 'i';
    //     }
    
    //     // Prepare and bind the query
    //     $stmt = $this->conn->prepare($query);
    
    //     if (!empty($params)) {
    //         $stmt->bind_param($types, ...$params); // Spread operator for dynamic params
    //     }
    
    //     $stmt->execute();
    //     $result = $stmt->get_result();
    //     $revenue_heads = $result->fetch_all(MYSQLI_ASSOC);
    
    //     if (count($revenue_heads) > 0) {
    //         // Return matching revenue head(s) with MDA name
    //         echo json_encode(['status' => 'success', 'data' => $revenue_heads]);
    //     } else {
    //         echo json_encode(['status' => 'error', 'message' => 'No matching revenue head found']);
    //         http_response_code(404); // Not found
    //     }
    
    //     $stmt->close();
    // }

    public function getRevenueHeadByFilters($filters) {
        // Base query with JOIN to include MDA name
        $query = "
            SELECT 
                rh.*, 
                m.fullname AS mda_name 
            FROM 
                revenue_heads rh
            LEFT JOIN 
                mda m ON rh.mda_id = m.id
            WHERE 1 = 1"; // 1 = 1 is a dummy condition to simplify appending other conditions
        
        $params = [];
        $types = '';
    
        // Add conditions dynamically
        if (isset($filters['id'])) {
            $query .= " AND rh.id = ?";
            $params[] = $filters['id'];
            $types .= 'i';
        }
    
        if (isset($filters['item_code'])) {
            $query .= " AND rh.item_code = ?";
            $params[] = $filters['item_code'];
            $types .= 's';
        }
    
        if (isset($filters['item_name'])) {
            // Using LIKE for partial matching on item_name
            $query .= " AND rh.item_name LIKE ?";
            $params[] = '%' . $filters['item_name'] . '%';
            $types .= 's';
        }
    
        if (isset($filters['category'])) {
            $query .= " AND rh.category = ?";
            $params[] = $filters['category'];
            $types .= 's';
        }
    
        if (isset($filters['status'])) {
            $query .= " AND rh.status = ?";
            $params[] = $filters['status'];
            $types .= 'i';
        }
    
        if (isset($filters['mda_id'])) {
            $query .= " AND rh.mda_id = ?";
            $params[] = $filters['mda_id'];
            $types .= 'i';
        }
    
        // Prepare and bind the query
        $stmt = $this->conn->prepare($query);
    
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params); // Spread operator for dynamic params
        }
    
        $stmt->execute();
        $result = $stmt->get_result();
        $revenue_heads = $result->fetch_all(MYSQLI_ASSOC);
    
        // Fetch all paid invoices
        $invoiceQuery = "SELECT revenue_head FROM invoices WHERE payment_status = 'paid'";
        $stmtInvoice = $this->conn->prepare($invoiceQuery);
        $stmtInvoice->execute();
        $invoiceResult = $stmtInvoice->get_result();
        $invoices = $invoiceResult->fetch_all(MYSQLI_ASSOC);
        $stmtInvoice->close();
    
        // Decode and process invoice data
        foreach ($revenue_heads as &$revenueHead) {
            $totalRevenue = 0;
            $revenueHeadId = $revenueHead['id'];
    
            foreach ($invoices as $invoice) {
                $invoiceRevenueHeads = json_decode($invoice['revenue_head'], true);
    
                foreach ($invoiceRevenueHeads as $item) {
                    if ((int)$item['revenue_head_id'] === $revenueHeadId) {
                        $totalRevenue += (float)$item['amount'];
                    }
                }
            }
    
            // Add total revenue to the revenue head data
            $revenueHead['total_revenue_generated'] = $totalRevenue;
        }
    
        if (count($revenue_heads) > 0) {
            // Return matching revenue head(s) with total revenue
            echo json_encode(['status' => 'success', 'data' => $revenue_heads]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No matching revenue head found']);
            http_response_code(404); // Not found
        }
    
        $stmt->close();
    }
    
    

    public function approveRevenueHead($data) {
        // Validate required fields
        if (!isset($data['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required field: id']);
            http_response_code(400); // Bad request
            return;
        }
    
        $id = $data['id'];
        // $status = 'active'; // Default to approved status


           // Check if the revenue head exists
        $query = "SELECT id, status FROM revenue_heads WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Revenue head not found']);
            http_response_code(404); // Not Found
            $stmt->close();
            return;
        }

        $revenue_head = $result->fetch_assoc();

        // Check if the revenue head is already active
        if ($revenue_head['status'] === 'active') {
            echo json_encode(['status' => 'error', 'message' => 'Revenue head is already active']);
            http_response_code(400); // Bad Request
            $stmt->close();
            return;
        }
        
            // Update status of the revenue head 
        $update_query = "UPDATE revenue_heads SET status = 'active' WHERE id = ?";
        $stmt = $this->conn->prepare($update_query);
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Revenue head approved successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to approve revenue head. Error: ' . $stmt->error]);
            http_response_code(500); // Internal Server Error
        }
        
            $stmt->close();

            // PUT {{BASE_URL}}/approve-revenue-head/1
            // {
            //     "id": 123
            // }

    }



}
