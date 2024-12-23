<?php
require_once 'config/database.php';
require_once 'helpers/validation_helper.php';  // Use the MDA duplicate check

class MdaController
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Create a new MDA and its contact information.
     */
    public function createMda($data)
    {
        // Validate required fields for MDA
        if (!isset($data['fullname'], $data['mda_code'], $data['email'], $data['phone'], $data['industry'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields: fullname, mda_code, email, phone, industry']);
            http_response_code(400); // Bad request
            return;
        }

        // Check if the MDA fullname or MDA code already exists in the 'mda' table
        if (isDuplicateMda($this->conn, $data['fullname'], $data['mda_code'])) {
            echo json_encode(['status' => 'error', 'message' => 'MDA with this name or MDA code already exists']);
            http_response_code(409); // Conflict
            return;
        }

        // Start transaction
        $this->conn->begin_transaction();

        try {
            // Insert MDA into 'mda' table
            $query = "INSERT INTO mda (fullname, mda_code, email, phone, industry, allow_payment, status, time_in) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                'ssssssi',
                $data['fullname'],
                $data['mda_code'],
                $data['email'],
                $data['phone'],
                $data['industry'],
                $data['allow_payment'], // Default to allow payment (1 = true)
                $data['status']       // Default status to active (1 = active)
            );

            if (!$stmt->execute()) {
                throw new Exception('Error creating MDA: ' . $stmt->error);
            }

            // Get the MDA ID for the newly created MDA
            $mda_id = $stmt->insert_id;

            // Insert MDA contact information if provided
            if (isset($data['contact_info']) && is_array($data['contact_info'])) {
                $this->createMdaContactInfo($mda_id, $data['contact_info']);
            }

            // Commit transaction
            $this->conn->commit();

            // Return success response
            echo json_encode([
                'status' => 'success',
                'message' => 'MDA created successfully',
                'mda_id' => $mda_id
            ]);

        } catch (Exception $e) {
            // Rollback transaction in case of an error
            $this->conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        } finally {
            $stmt->close();
        }
    }

    /**
     * Helper function to insert MDA contact information into 'mda_contact_info' table.
     */
    private function createMdaContactInfo($mda_id, $contact_info)
    {
        // Ensure contact information fields are present
        if (!isset($contact_info['state'], $contact_info['geolocation'], $contact_info['lga'], $contact_info['address'])) {
            throw new Exception('Missing required contact info fields: state, geolocation, lga, address');
        }

        $query = "INSERT INTO mda_contact_info (mda_id, state, geolocation, lga, address) 
                  VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'issss',
            $mda_id,
            $contact_info['state'],
            $contact_info['geolocation'],
            $contact_info['lga'],
            $contact_info['address']
        );

        if (!$stmt->execute()) {
            throw new Exception('Error creating MDA contact information: ' . $stmt->error);
        }

        $stmt->close();
    }

    /**
     * Update MDA information and optional contact information.
     */
    public function updateMda($data)
    {
        // Validate required fields
        if (!isset($data['mda_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required field: mda_id']);
            http_response_code(400); // Bad request
            return;
        }

        $mda_id = $data['mda_id'];

        // Check if the MDA exists
        $query = "SELECT id FROM mda WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $mda_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'MDA not found']);
            http_response_code(404); // Not found
            $stmt->close();
            return;
        }

        $stmt->close();

        // Optionally, check for duplicates (if updating fullname or mda_code)
        if (isset($data['fullname']) || isset($data['mda_code'])) {
            $query = "SELECT id FROM mda WHERE (fullname = ? OR mda_code = ?) AND id != ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $fullname = $data['fullname'] ?? '';
            $mda_code = $data['mda_code'] ?? '';
            $stmt->bind_param('ssi', $fullname, $mda_code, $mda_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                echo json_encode(['status' => 'error', 'message' => 'MDA with this name or MDA code already exists']);
                http_response_code(409); // Conflict
                $stmt->close();
                return;
            }

            $stmt->close();
        }

        // Update the MDA details
        $query = "UPDATE mda SET fullname = COALESCE(?, fullname), mda_code = COALESCE(?, mda_code), email = COALESCE(?, email), phone = COALESCE(?, phone), industry = COALESCE(?, industry), allow_payment = COALESCE(?, allow_payment), status = COALESCE(?, status) WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'ssssssii',
            $data['fullname'],
            $data['mda_code'],
            $data['email'],
            $data['phone'],
            $data['industry'],
            $data['allow_payment'],
            $data['status'],
            $mda_id
        );

        if (!$stmt->execute()) {
            echo json_encode(['status' => 'error', 'message' => 'Error updating MDA: ' . $stmt->error]);
            http_response_code(500); // Internal Server Error
            return;
        }

        $stmt->close();

        // Update MDA contact information if provided
        if (isset($data['contact_info']) && is_array($data['contact_info'])) {
            $this->updateMdaContactInfo($mda_id, $data['contact_info']);
        }

        // Return success response
        echo json_encode([
            'status' => 'success',
            'message' => 'MDA updated successfully'
        ]);
    }

    /**
     * Update MDA contact information.
     */
    private function updateMdaContactInfo($mda_id, $contact_info)
    {
        $query = "UPDATE mda_contact_info SET state = COALESCE(?, state), geolocation = COALESCE(?, geolocation), lga = COALESCE(?, lga), address = COALESCE(?, address) WHERE mda_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'ssssi',
            $contact_info['state'],
            $contact_info['geolocation'],
            $contact_info['lga'],
            $contact_info['address'],
            $mda_id
        );

        if (!$stmt->execute()) {
            throw new Exception('Error updating MDA contact information: ' . $stmt->error);
        }

        $stmt->close();
    }

    /**
     * Fetch all MDAs with pagination.
     */
    public function getAllMdas($page, $limit)
    {
        // Set default page and limit if not provided
        $page = isset($page) ? (int) $page : 1;
        $limit = isset($limit) ? (int) $limit : 10;

        // Calculate the offset
        $offset = ($page - 1) * $limit;

        // Fetch the total number of MDAs
        $count_query = "SELECT COUNT(*) as total FROM mda";
        $result = $this->conn->query($count_query);
        $total_mdas = $result->fetch_assoc()['total'];

        // Fetch the paginated MDAs
        $query = "SELECT * FROM mda LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $mdas = $result->fetch_all(MYSQLI_ASSOC);

        // Calculate total pages
        $total_pages = ceil($total_mdas / $limit);

        // Return response
        echo json_encode([
            'status' => 'success',
            'data' => [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_mdas' => $total_mdas,
                'mdas' => $mdas
            ]
        ]);

        $stmt->close();
    }

    /**
     * Fetch MDA by various filters (id, fullname, mda_code, email, allow_payment, status).
     */
    public function getMdaByFilters($queryParams)
    {
        // Base query to fetch MDA details and count revenue heads
        $query = "
            SELECT
                m.*,
                mdac.state,
                mdac.geolocation,
                mdac.lga,
                mdac.address,
                COUNT(rh.id) AS total_revenue_heads
            FROM
                mda m
            LEFT JOIN mda_contact_info mdac ON m.id = mdac.mda_id
            LEFT JOIN revenue_heads rh ON m.id = rh.mda_id
            WHERE 1=1
            ";

        $params = [];
        $types = "";

        // Apply filters based on query parameters
        if (!empty($queryParams['id'])) {
            $query .= " AND m.id = ?";
            $params[] = $queryParams['id'];
            $types .= "i";
        }

        if (!empty($queryParams['fullname'])) {
            $query .= " AND m.fullname LIKE ?";
            $params[] = '%' . $queryParams['fullname'] . '%';
            $types .= "s";
        }

        if (!empty($queryParams['mda_code'])) {
            $query .= " AND m.mda_code = ?";
            $params[] = $queryParams['mda_code'];
            $types .= "s";
        }

        if (!empty($queryParams['allow_payment'])) {
            $query .= " AND m.allow_payment = ?";
            $params[] = $queryParams['allow_payment'];
            $types .= "i";
        }

        if (!empty($queryParams['status'])) {
            $query .= " AND m.status = ?";
            $params[] = $queryParams['status'];
            $types .= "i";
        }

        if (!empty($queryParams['email'])) {
            $query .= " AND m.email LIKE ?";
            $params[] = '%' . $queryParams['email'] . '%';
            $types .= "s";
        }

        // Add GROUP BY
        $query .= " GROUP BY m.id, mdac.state, mdac.geolocation, mdac.lga, mdac.address";


        // Add pagination if provided
        $page = isset($queryParams['page']) ? (int) $queryParams['page'] : 1;
        $limit = isset($queryParams['limit']) ? (int) $queryParams['limit'] : 10;
        $offset = ($page - 1) * $limit;

        $query .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        // Prepare and execute the query
        $stmt = $this->conn->prepare($query);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch MDAs and calculate total remittance for each
        $mdas = [];
        while ($row = $result->fetch_assoc()) {
            $mdaId = $row['id'];

            // Calculate total remittance for the MDA
            $remittanceQuery = "SELECT revenue_head, payment_status FROM invoices WHERE payment_status = 'paid'";
            $remittanceResult = $this->conn->query($remittanceQuery);

            $totalRemittance = 0;

            while ($invoice = $remittanceResult->fetch_assoc()) {
                $revenueHeads = json_decode($invoice['revenue_head'], true);

                foreach ($revenueHeads as $revenueHead) {
                    // Check if the revenue head belongs to this MDA
                    $revenueHeadQuery = "SELECT mda_id FROM revenue_heads WHERE id = ?";
                    $stmtRevenueHead = $this->conn->prepare($revenueHeadQuery);
                    $stmtRevenueHead->bind_param('i', $revenueHead['revenue_head_id']);
                    $stmtRevenueHead->execute();
                    $revenueHeadResult = $stmtRevenueHead->get_result();
                    $revenueHeadData = $revenueHeadResult->fetch_assoc();

                    if ($revenueHeadData['mda_id'] == $mdaId) {
                        $totalRemittance += $revenueHead['amount'];
                    }

                    $stmtRevenueHead->close();
                }
            }

            // Add total remittance to the MDA details
            $row['total_remittance'] = $totalRemittance;

            $mdas[] = $row;
        }

        // Return structured response
        echo json_encode([
            "status" => "success",
            "data" => $mdas
        ]);
    }


    public function deleteMda($mda_id)
    {
        // Ensure the MDA exists
        $check_query = "SELECT id FROM mda WHERE id = ? AND account_status = 'activate'";
        $stmt = $this->conn->prepare($check_query);
        $stmt->bind_param('i', $mda_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            echo json_encode(['status' => 'error', 'message' => 'MDA not found']);
            http_response_code(404); // Not Found
            $stmt->close();
            return;
        }

        // Start transaction to ensure safe deletion
        $this->conn->begin_transaction();

        try {
            // Deactivate associated revenue heads
            $deactivate_revenue_query = "UPDATE revenue_heads SET account_status = 'deactivate' WHERE mda_id = ?";
            $stmt = $this->conn->prepare($deactivate_revenue_query);
            $stmt->bind_param('i', $mda_id);
            if (!$stmt->execute()) {
                throw new Exception('Error deactivating revenue heads: ' . $stmt->error);
            }

            // Deactivate associated MDA contact information
            $deactivate_contact_info_query = "UPDATE mda_contact_info SET account_status = 'deactivate' WHERE mda_id = ?";
            $stmt = $this->conn->prepare($deactivate_contact_info_query);
            $stmt->bind_param('i', $mda_id);
            if (!$stmt->execute()) {
                throw new Exception('Error deactivating MDA contact info: ' . $stmt->error);
            }

            // Finally, deactivate the MDA
            $deactivate_mda_query = "UPDATE mda SET account_status = 'deactivate' WHERE id = ?";
            $stmt = $this->conn->prepare($deactivate_mda_query);
            $stmt->bind_param('i', $mda_id);
            if (!$stmt->execute()) {
                throw new Exception('Error deactivating MDA: ' . $stmt->error);
            }

            // Commit transaction
            $this->conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'MDA deactivated successfully']);
            $stmt->close();
        } catch (Exception $e) {
            // Rollback transaction in case of an error
            $this->conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

        // {
        //     "mda_id": 123
        // }

    }

    public function getMdaUsers($queryParams)
    {
        // Base query to fetch MDA users
        $query = "
            SELECT 
                mu.id,
                mu.mda_id,
                mu.name,
                mu.email,
                mu.phone,
                mu.created_at,
                mu.img,
                mu.office_name,
                m.fullname AS mda_name
            FROM mda_users mu
            LEFT JOIN mda m ON mu.mda_id = m.id
            WHERE 1=1
        ";

        $params = [];
        $types = "";

        // Apply filters
        if (!empty($queryParams['mda_id'])) {
            $query .= " AND mu.mda_id = ?";
            $params[] = $queryParams['mda_id'];
            $types .= "i";
        }

        if (!empty($queryParams['name'])) {
            $query .= " AND mu.name LIKE ?";
            $params[] = '%' . $queryParams['name'] . '%';
            $types .= "s";
        }

        if (!empty($queryParams['email'])) {
            $query .= " AND mu.email LIKE ?";
            $params[] = '%' . $queryParams['email'] . '%';
            $types .= "s";
        }

        if (!empty($queryParams['phone_number'])) {
            $query .= " AND mu.phone_number LIKE ?";
            $params[] = '%' . $queryParams['phone'] . '%';
            $types .= "s";
        }

        if (!empty($queryParams['office_name'])) {
            $query .= " AND mu.office_name LIKE ?";
            $params[] = '%' . $queryParams['office_name'] . '%';
            $types .= "s";
        }

        // Add pagination
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;

        $query .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        // Prepare and execute the query
        $stmt = $this->conn->prepare($query);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch results
        $mdaUsers = [];
        while ($row = $result->fetch_assoc()) {
            $mdaUsers[] = $row;
        }

        // Get total count for pagination
        $totalQuery = "SELECT COUNT(*) as total FROM mda_users WHERE 1=1";
        if (!empty($queryParams['mda_id'])) {
            $totalQuery .= " AND mda_id = " . (int) $queryParams['mda_id'];
        }
        if (!empty($queryParams['name'])) {
            $totalQuery .= " AND name LIKE '%" . $this->conn->real_escape_string($queryParams['name']) . "%'";
        }
        if (!empty($queryParams['email'])) {
            $totalQuery .= " AND email LIKE '%" . $this->conn->real_escape_string($queryParams['email']) . "%'";
        }
        if (!empty($queryParams['phone'])) {
            $totalQuery .= " AND phone LIKE '%" . $this->conn->real_escape_string($queryParams['phone_number']) . "%'";
        }
        if (!empty($queryParams['office_name'])) {
            $totalQuery .= " AND office_name LIKE '%" . $this->conn->real_escape_string($queryParams['office_name']) . "%'";
        }

        $totalResult = $this->conn->query($totalQuery);
        $total = $totalResult->fetch_assoc()['total'];
        $totalPages = ceil($total / $limit);

        // Return structured response
        echo json_encode([
            "status" => "success",
            "data" => $mdaUsers,
            "pagination" => [
                "current_page" => $page,
                "per_page" => $limit,
                "total_pages" => $totalPages,
                "total_records" => $total
            ]
        ]);
    }

    public function getInvoicesByMda($queryParams) {
        // Ensure MDA ID is provided
        if (empty($queryParams['mda_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'MDA ID is required']);
            http_response_code(400);
            return;
        }
    
        // Fetch all revenue head IDs and names for the specified MDA
        $revenueHeadQuery = "SELECT id, item_name FROM revenue_heads WHERE mda_id = ?";
        $stmt = $this->conn->prepare($revenueHeadQuery);
        $stmt->bind_param('i', $queryParams['mda_id']);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $revenueHeadMap = [];
        while ($row = $result->fetch_assoc()) {
            $revenueHeadMap[$row['id']] = $row['item_name'];
        }
        $stmt->close();
    
        // If no revenue heads found, return an empty result
        if (empty($revenueHeadMap)) {
            echo json_encode(['status' => 'success', 'data' => [], 'pagination' => ['total_records' => 0]]);
            return;
        }
    
        // Base invoice query
        $invoiceQuery = "SELECT * FROM invoices WHERE 1=1";
        $params = [];
        $types = "";
    
        // Add optional filters
        if (!empty($queryParams['status'])) {
            $invoiceQuery .= " AND payment_status = ?";
            $params[] = $queryParams['status'];
            $types .= "s";
        }
    
        // Filter by revenue_head_id
        if (!empty($queryParams['revenue_head_id'])) {
            $invoiceQuery .= " AND JSON_CONTAINS(revenue_head, ?)";
            $params[] = json_encode([['revenue_head_id' => (int)$queryParams['revenue_head_id']]]);
            $types .= "s";
        }
    
        // Filter by date range
        if (!empty($queryParams['start_date']) && !empty($queryParams['end_date'])) {
            $invoiceQuery .= " AND date_created BETWEEN ? AND ?";
            $params[] = $queryParams['start_date'];
            $params[] = $queryParams['end_date'];
            $types .= "ss";
        }
    
        // Prepare and execute the query
        $stmt = $this->conn->prepare($invoiceQuery);
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
    
        $invoices = [];
        while ($row = $result->fetch_assoc()) {
            $revenueHeads = json_decode($row['revenue_head'], true);
            $associatedRevenueHeads = [];
            $includeInvoice = false;
    
            foreach ($revenueHeads as $revenueHead) {
                if (array_key_exists($revenueHead['revenue_head_id'], $revenueHeadMap)) {
                    $includeInvoice = true;
                    $associatedRevenueHeads[] = [
                        'revenue_head_id' => $revenueHead['revenue_head_id'],
                        'item_name' => $revenueHeadMap[$revenueHead['revenue_head_id']],
                        'amount' => $revenueHead['amount']
                    ];
                }
            }
    
            if ($includeInvoice) {
                $row['associated_revenue_heads'] = $associatedRevenueHeads;
                $invoices[] = $row;
            }
        }
        $stmt->close();
    
        // Pagination
        $page = isset($queryParams['page']) ? (int)$queryParams['page'] : 1;
        $limit = isset($queryParams['limit']) ? (int)$queryParams['limit'] : 10;
        $offset = ($page - 1) * $limit;
    
        $paginatedInvoices = array_slice($invoices, $offset, $limit);
        $totalRecords = count($invoices);
        $totalPages = ceil($totalRecords / $limit);
    
        // Return the result
        echo json_encode([
            "status" => "success",
            "data" => $paginatedInvoices,
            "pagination" => [
                "current_page" => $page,
                "per_page" => $limit,
                "total_pages" => $totalPages,
                "total_records" => $totalRecords
            ]
        ]);
    }
    

    
    
    public function getInvoicesWithPaymentInfoByMda($queryParams)
    {
        if (empty($queryParams['mda_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'MDA ID is required']);
            http_response_code(400);
            return;
        }

        $mda_id = (int) $queryParams['mda_id'];

        // Fetch all revenue heads for the given MDA
        $revenueHeadQuery = "SELECT id, item_name FROM revenue_heads WHERE mda_id = ?";
        $stmt = $this->conn->prepare($revenueHeadQuery);
        $stmt->bind_param('i', $mda_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $revenueHeadMap = [];
        while ($row = $result->fetch_assoc()) {
            $revenueHeadMap[$row['id']] = $row['item_name'];
        }
        $stmt->close();

        // If no revenue heads are found, return empty data
        if (empty($revenueHeadMap)) {
            echo json_encode([
                "status" => "success",
                "data" => [],
                "pagination" => ['total_records' => 0]
            ]);
            return;
        }

        // Fetch all invoices
        $invoiceQuery = "
            SELECT 
                inv.*,
                pc.payment_channel,
                pc.payment_method,
                pc.payment_bank,
                pc.payment_reference_number,
                pc.receipt_number,
                pc.amount_paid AS payment_amount,
                pc.date_payment_created
            FROM invoices inv
            LEFT JOIN payment_collection pc ON inv.invoice_number = pc.invoice_number
        ";
        $stmt = $this->conn->prepare($invoiceQuery);
        $stmt->execute();
        $result = $stmt->get_result();

        $invoices = [];
        while ($row = $result->fetch_assoc()) {
            // Decode the revenue_head JSON
            $revenueHeads = json_decode($row['revenue_head'], true);
            $associatedRevenueHeads = [];
            $includeInvoice = false;

            // Filter revenue heads based on MDA
            foreach ($revenueHeads as $revenueHead) {
                if (isset($revenueHeadMap[$revenueHead['revenue_head_id']])) {
                    $includeInvoice = true;
                    $associatedRevenueHeads[] = [
                        'revenue_head_id' => $revenueHead['revenue_head_id'],
                        'item_name' => $revenueHeadMap[$revenueHead['revenue_head_id']],
                        'amount' => $revenueHead['amount']
                    ];
                }
            }

            if ($includeInvoice) {
                $row['associated_revenue_heads'] = $associatedRevenueHeads;

                // Fetch taxpayer info
                $userInfo = $this->getTaxpayerInfo($row['tax_number']);
                $row['user_info'] = $userInfo;

                $invoices[] = $row;
            }
        }
        $stmt->close();

        // Pagination
        $page = isset($queryParams['page']) ? (int) $queryParams['page'] : 1;
        $limit = isset($queryParams['limit']) ? (int) $queryParams['limit'] : 10;
        $offset = ($page - 1) * $limit;

        $pagedInvoices = array_slice($invoices, $offset, $limit);
        $totalRecords = count($invoices);
        $totalPages = ceil($totalRecords / $limit);

        // Return structured response
        echo json_encode([
            "status" => "success",
            "data" => $pagedInvoices,
            "pagination" => [
                "current_page" => $page,
                "per_page" => $limit,
                "total_pages" => $totalPages,
                "total_records" => $totalRecords
            ]
        ]);
    }

    // Helper function to fetch taxpayer info
    private function getTaxpayerInfo($taxNumber)
    {
        // Check taxpayer table
        $query = "SELECT first_name, surname, email, phone FROM taxpayer WHERE tax_number = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $taxNumber);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $taxpayer = $result->fetch_assoc();
            $stmt->close();
            return $taxpayer;
        }

        // Check enumerator_tax_payers table
        $query = "SELECT first_name, last_name AS surname, email, phone FROM enumerator_tax_payers WHERE tax_number = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $taxNumber);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $enumeratorTaxpayer = $result->fetch_assoc();
            $stmt->close();
            return $enumeratorTaxpayer;
        }

        $stmt->close();
        return null;
    }



    public function getRevenueHeadSummary()
    {
        // SQL query to fetch counts
        $query = "
            SELECT 
                COUNT(*) AS total_revenue_heads,
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS active_revenue_heads,
                SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS inactive_revenue_heads
            FROM revenue_heads
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $summary = $result->fetch_assoc();

        // Response structure
        echo json_encode([
            "status" => "success",
            "data" => [
                "total_revenue_heads" => (int) $summary['total_revenue_heads'],
                "active_revenue_heads" => (int) $summary['active_revenue_heads'],
                "inactive_revenue_heads" => (int) $summary['inactive_revenue_heads'],
            ]
        ]);
    }

    public function getRevenueHeadSummaryByMda($mda_id)
    {
        // SQL query to fetch counts for a specific MDA
        $query = "
            SELECT 
                COUNT(*) AS total_revenue_heads,
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS active_revenue_heads,
                SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS inactive_revenue_heads
            FROM revenue_heads
            WHERE mda_id = ?
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $mda_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $summary = $result->fetch_assoc();

        // Response structure
        echo json_encode([
            "status" => "success",
            "data" => [
                "mda_id" => $mda_id,
                "total_revenue_heads" => (int) $summary['total_revenue_heads'],
                "active_revenue_heads" => (int) $summary['active_revenue_heads'],
                "inactive_revenue_heads" => (int) $summary['inactive_revenue_heads'],
            ]
        ]);
    }












}