<?php
require_once 'config/database.php';
require_once 'controllers/EmailController.php';

$emailController = new EmailController();
class TaxpayerController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Method to check verification status of a taxpayer
    public function checkVerificationStatus($queryParams) {
        // Ensure at least one identifier is provided
        if (empty($queryParams['tax_number']) && empty($queryParams['phone']) && empty($queryParams['email'])) {
            return json_encode(["status" => "error", "message" => "Provide tax_number, phone, or email to check verification status"]);
        }

        // Base query
        $query = "
            SELECT t.tax_number, t.first_name, t.surname, ts.verification_status, ts.tin_status
            FROM taxpayer t
            INNER JOIN taxpayer_security ts ON t.id = ts.taxpayer_id
            WHERE 1=1
        ";
        $params = [];
        $types = "";

        // Add conditions based on input
        if (!empty($queryParams['tax_number'])) {
            $query .= " AND t.tax_number = ?";
            $params[] = $queryParams['tax_number'];
            $types .= "s";
        }
        if (!empty($queryParams['phone'])) {
            $query .= " AND t.phone = ?";
            $params[] = $queryParams['phone'];
            $types .= "s";
        }
        if (!empty($queryParams['email'])) {
            $query .= " AND t.email = ?";
            $params[] = $queryParams['email'];
            $types .= "s";
        }

        // Execute query
        $stmt = $this->conn->prepare($query);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if taxpayer exists
        if ($result->num_rows === 0) {
            return json_encode(["status" => "error", "message" => "Taxpayer not found"]);
        }

        // Fetch taxpayer data
        $taxpayer = $result->fetch_assoc();

        // Return JSON response
        return json_encode([
            "status" => "success",
            "data" => [
                "tax_number" => $taxpayer['tax_number'],
                "first_name" => $taxpayer['first_name'],
                "surname" => $taxpayer['surname'],
                "verification_status" => $taxpayer['verification_status'], // "verified", "pending", etc.
                "tin_status" => $taxpayer['tin_status'] // "verified", "unverified", etc.
            ]
        ]);
    }

    // Verify taxpayer account using a verification code
    public function verifyTaxpayer($input) {
        // Validate input
        if (empty($input['tax_number']) && empty($input['phone']) && empty($input['email'])) {
            return json_encode(["status" => "error", "message" => "Provide tax_number, phone, or email"]);
        }
        if (empty($input['verification_code'])) {
            return json_encode(["status" => "error", "message" => "Verification code is required"]);
        }

        // Determine the identifier (tax_number, phone, or email)
        $taxIdentifier = !empty($input['tax_number']) ? $input['tax_number'] : (!empty($input['phone']) ? $input['phone'] : $input['email']);
        $verificationCode = $input['verification_code'];

        // Base query to find the taxpayer
        $query = "
            SELECT ts.verification_code, ts.verification_status, t.tax_number, t.phone, t.email
            FROM taxpayer t
            INNER JOIN taxpayer_security ts ON t.id = ts.taxpayer_id
            WHERE (t.tax_number = ? OR t.phone = ? OR t.email = ?)
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sss", $taxIdentifier, $taxIdentifier, $taxIdentifier);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if taxpayer exists
        if ($result->num_rows === 0) {
            return json_encode(["status" => "error", "message" => "Taxpayer not found"]);
        }

        // Fetch taxpayer data
        $taxpayer = $result->fetch_assoc();

        // Validate verification code
        if ($taxpayer['verification_code'] !== $verificationCode) {
            return json_encode(["status" => "error", "message" => "Invalid verification code"]);
        }

        // Check if already verified
        if ($taxpayer['verification_status'] === 'verified') {
            return json_encode(["status" => "error", "message" => "Account already verified"]);
        }

        // Update verification status
        $updateQuery = "UPDATE taxpayer_security SET verification_status = 'verified' WHERE verification_code = ?";
        $updateStmt = $this->conn->prepare($updateQuery);
        $updateStmt->bind_param("s", $verificationCode);
        $updateStmt->execute();

        if ($updateStmt->affected_rows > 0) {
            return json_encode(["status" => "success", "message" => "Account successfully verified"]);
        } else {
            return json_encode(["status" => "error", "message" => "Failed to verify account"]);
        }
    }

    public function regenerateVerificationCode($input) {
        // Validate input
        if (empty($input['tax_number']) && empty($input['phone']) && empty($input['email'])) {
            return json_encode(["status" => "error", "message" => "Provide tax_number, phone, or email"]);
        }

        // Determine the identifier (tax_number, phone, or email)
        $taxIdentifier = !empty($input['tax_number']) ? $input['tax_number'] : (!empty($input['phone']) ? $input['phone'] : $input['email']);

        // Check if taxpayer exists
        $query = "
            SELECT ts.verification_status, t.id AS taxpayer_id, t.email, t.first_name, t.surname
            FROM taxpayer t
            INNER JOIN taxpayer_security ts ON t.id = ts.taxpayer_id
            WHERE t.tax_number = ? OR t.phone = ? OR t.email = ?
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sss", $taxIdentifier, $taxIdentifier, $taxIdentifier);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return json_encode(["status" => "error", "message" => "Taxpayer not found"]);
        }

        $taxpayer = $result->fetch_assoc();
        // Check if the account is already verified
        if ($taxpayer['verification_status'] === 'verified') {
            return json_encode(["status" => "error", "message" => "Account is already verified"]);
        }

        // Generate a new verification code
        $newVerificationCode = rand(100000, 999999);

        // Update the verification code in the database
        $updateQuery = "UPDATE taxpayer_security SET verification_code = ? WHERE taxpayer_id = ?";
        $updateStmt = $this->conn->prepare($updateQuery);
        $updateStmt->bind_param("si", $newVerificationCode, $taxpayer['taxpayer_id']);
        $updateStmt->execute();


        if ($updateStmt->affected_rows > 0) {
            global $emailController;
            $emailController->userVerificationEmail($taxpayer['email'], $taxpayer['first_name'], $taxpayer['surname'], $newVerificationCode);
            return json_encode([
                "status" => "success",
                "message" => "New verification code generated",
                "verification_code" => $newVerificationCode // Optional: Remove in production for security reasons
            ]);
        } else {
            return json_encode(["status" => "error", "message" => "Failed to regenerate verification code"]);
        }
    }

    // Get all taxpayers with filters
    // public function getAllTaxpayers($queryParams) {
    //     // Base query to fetch taxpayers
    //     $query = "SELECT t.id, t.created_by, t.tax_number, t.category, t.presumptive, t.first_name, 
    //                      t.surname, t.email, t.phone, t.state, t.lga, t.address, t.employment_status, 
    //                      t.number_of_staff, t.business_own, t.created_time, t.updated_time, 
    //                      ts.tin_status
    //               FROM taxpayer t
    //               INNER JOIN taxpayer_security ts ON t.id = ts.taxpayer_id
    //               WHERE 1=1";
    //     $params = [];
    //     $types = "";
    
    //     // Apply filters
    //     if (!empty($queryParams['id'])) {
    //         $query .= " AND t.id = ?";
    //         $params[] = $queryParams['id'];
    //         $types .= "i";
    //     }
    //     if (!empty($queryParams['created_by'])) {
    //         $query .= " AND t.created_by = ?";
    //         $params[] = $queryParams['created_by'];
    //         $types .= "s";
    //     }
    //     if (!empty($queryParams['tax_number'])) {
    //         $query .= " AND t.tax_number LIKE ?";
    //         $params[] = '%' . $queryParams['tax_number'] . '%';
    //         $types .= "s";
    //     }
    //     if (!empty($queryParams['category'])) {
    //         $query .= " AND t.category = ?";
    //         $params[] = $queryParams['category'];
    //         $types .= "s";
    //     }
    //     if (!empty($queryParams['presumptive'])) {
    //         $query .= " AND t.presumptive = ?";
    //         $params[] = $queryParams['presumptive'];
    //         $types .= "s";
    //     }
    //     if (!empty($queryParams['first_name'])) {
    //         $query .= " AND t.first_name LIKE ?";
    //         $params[] = '%' . $queryParams['first_name'] . '%';
    //         $types .= "s";
    //     }
    //     if (!empty($queryParams['surname'])) {
    //         $query .= " AND t.surname LIKE ?";
    //         $params[] = '%' . $queryParams['surname'] . '%';
    //         $types .= "s";
    //     }
    //     if (!empty($queryParams['email'])) {
    //         $query .= " AND t.email LIKE ?";
    //         $params[] = '%' . $queryParams['email'] . '%';
    //         $types .= "s";
    //     }
    //     if (!empty($queryParams['phone'])) {
    //         $query .= " AND t.phone = ?";
    //         $params[] = $queryParams['phone'];
    //         $types .= "s";
    //     }
    //     if (!empty($queryParams['state'])) {
    //         $query .= " AND t.state = ?";
    //         $params[] = $queryParams['state'];
    //         $types .= "s";
    //     }
    //     if (!empty($queryParams['lga'])) {
    //         $query .= " AND t.lga = ?";
    //         $params[] = $queryParams['lga'];
    //         $types .= "s";
    //     }
    //     if (!empty($queryParams['address'])) {
    //         $query .= " AND t.address LIKE ?";
    //         $params[] = '%' . $queryParams['address'] . '%';
    //         $types .= "s";
    //     }
    //     if (!empty($queryParams['employment_status'])) {
    //         $query .= " AND t.employment_status = ?";
    //         $params[] = $queryParams['employment_status'];
    //         $types .= "s";
    //     }
    //     if (!empty($queryParams['number_of_staff_min']) && !empty($queryParams['number_of_staff_max'])) {
    //         $query .= " AND t.number_of_staff BETWEEN ? AND ?";
    //         $params[] = $queryParams['number_of_staff_min'];
    //         $params[] = $queryParams['number_of_staff_max'];
    //         $types .= "ii";
    //     }
    //     if (!empty($queryParams['business_own'])) {
    //         $query .= " AND t.business_own = ?";
    //         $params[] = $queryParams['business_own'];
    //         $types .= "s";
    //     }
    //     if (!empty($queryParams['created_time_start']) && !empty($queryParams['created_time_end'])) {
    //         $query .= " AND t.created_time BETWEEN ? AND ?";
    //         $params[] = $queryParams['created_time_start'];
    //         $params[] = $queryParams['created_time_end'];
    //         $types .= "ss";
    //     }
    //     if (!empty($queryParams['updated_time_start']) && !empty($queryParams['updated_time_end'])) {
    //         $query .= " AND t.updated_time BETWEEN ? AND ?";
    //         $params[] = $queryParams['updated_time_start'];
    //         $params[] = $queryParams['updated_time_end'];
    //         $types .= "ss";
    //     }
    
    //     // Execute query with pagination
    //     $page = isset($queryParams['page']) ? (int)$queryParams['page'] : 1;
    //     $limit = isset($queryParams['limit']) ? (int)$queryParams['limit'] : 10;
    //     $offset = ($page - 1) * $limit;
    
    //     $query .= " LIMIT ? OFFSET ?";
    //     $params[] = $limit;
    //     $params[] = $offset;
    //     $types .= "ii";
    
    //     $stmt = $this->conn->prepare($query);
    //     if ($types) {
    //         $stmt->bind_param($types, ...$params);
    //     }
    //     $stmt->execute();
    //     $result = $stmt->get_result();
    
    //     // Fetch results
    //     $taxpayers = [];
    //     while ($row = $result->fetch_assoc()) {
    //         $taxpayers[] = $row;
    //     }
    
    //     // Get total count for pagination
    //     $totalQuery = "SELECT COUNT(*) as total FROM taxpayer";
    //     $totalStmt = $this->conn->prepare($totalQuery);
    //     $totalStmt->execute();
    //     $totalResult = $totalStmt->get_result();
    //     $total = $totalResult->fetch_assoc()['total'];
    //     $totalPages = ceil($total / $limit);
    
    //     // Return JSON response
    //     return json_encode([
    //         "status" => "success",
    //         "data" => $taxpayers,
    //         "pagination" => [
    //             "current_page" => $page,
    //             "per_page" => $limit,
    //             "total_pages" => $totalPages,
    //             "total_records" => $total
    //         ]
    //     ]);
    // }

    public function getAllTaxpayers($queryParams)
{
    $taxpayers = [];

    $params = [];
    $types = "";

    // Define taxpayer query
    $taxpayerQuery = "
        SELECT t.id, t.created_by, t.tax_number, t.category, t.presumptive, t.first_name, 
               t.surname, t.email, t.phone, t.state, t.lga, t.address, t.employment_status, 
               t.number_of_staff, t.business_own, t.created_time, t.updated_time, 
               ts.tin_status, 'taxpayer' AS source
        FROM taxpayer t
        INNER JOIN taxpayer_security ts ON t.id = ts.taxpayer_id
        WHERE 1=1
    ";

    // Define enumerator query
    $enumeratorQuery = "
        SELECT etp.id, NULL AS created_by, etp.tax_number, NULL AS category, NULL AS presumptive, 
               etp.first_name, etp.last_name AS surname, etp.email, etp.phone, 
               etp.state, etp.lga, etp.address, etp.employment_status, 
               etp.staff_quota AS number_of_staff, NULL AS business_own, 
               etp.timeIn AS created_time, NULL AS updated_time, 
               etp.tin_status, 'enumerator_tax_payers' AS source
        FROM enumerator_tax_payers etp
        WHERE 1=1
    ";

    // Add filters to taxpayer query
    foreach ($queryParams as $key => $value) {
        switch ($key) {
            case 'id':
                $taxpayerQuery .= " AND t.id = ?";
                $enumeratorQuery .= " AND etp.id = ?";
                $params[] = $value;
                $types .= "i";
                break;
            case 'created_by':
                $taxpayerQuery .= " AND t.created_by = ?";
                $params[] = $value;
                $types .= "s";
                break;
            case 'tax_number':
                $taxpayerQuery .= " AND t.tax_number LIKE ?";
                $enumeratorQuery .= " AND etp.tax_number LIKE ?";
                $params[] = '%' . $value . '%';
                $types .= "s";
                break;
            case 'category':
                $taxpayerQuery .= " AND t.category = ?";
                $params[] = $value;
                $types .= "s";
                break;
            case 'presumptive':
                $taxpayerQuery .= " AND t.presumptive = ?";
                $params[] = $value;
                $types .= "s";
                break;
            case 'first_name':
                $taxpayerQuery .= " AND t.first_name LIKE ?";
                $enumeratorQuery .= " AND etp.first_name LIKE ?";
                $params[] = '%' . $value . '%';
                $types .= "s";
                break;
            case 'surname':
                $taxpayerQuery .= " AND t.surname LIKE ?";
                $enumeratorQuery .= " AND etp.last_name LIKE ?";
                $params[] = '%' . $value . '%';
                $types .= "s";
                break;
            case 'email':
                $taxpayerQuery .= " AND t.email LIKE ?";
                $enumeratorQuery .= " AND etp.email LIKE ?";
                $params[] = '%' . $value . '%';
                $types .= "s";
                break;
            case 'phone':
                $taxpayerQuery .= " AND t.phone = ?";
                $enumeratorQuery .= " AND etp.phone = ?";
                $params[] = $value;
                $types .= "s";
                break;
            case 'state':
                $taxpayerQuery .= " AND t.state = ?";
                $enumeratorQuery .= " AND etp.state = ?";
                $params[] = $value;
                $types .= "s";
                break;
            case 'lga':
                $taxpayerQuery .= " AND t.lga = ?";
                $enumeratorQuery .= " AND etp.lga = ?";
                $params[] = $value;
                $types .= "s";
                break;
            case 'address':
                $taxpayerQuery .= " AND t.address LIKE ?";
                $enumeratorQuery .= " AND etp.address LIKE ?";
                $params[] = '%' . $value . '%';
                $types .= "s";
                break;
            case 'employment_status':
                $taxpayerQuery .= " AND t.employment_status = ?";
                $enumeratorQuery .= " AND etp.employment_status = ?";
                $params[] = $value;
                $types .= "s";
                break;
            case 'number_of_staff_min':
            case 'number_of_staff_max':
                if (isset($queryParams['number_of_staff_min'], $queryParams['number_of_staff_max'])) {
                    $taxpayerQuery .= " AND t.number_of_staff BETWEEN ? AND ?";
                    $enumeratorQuery .= " AND etp.staff_quota BETWEEN ? AND ?";
                    $params[] = $queryParams['number_of_staff_min'];
                    $params[] = $queryParams['number_of_staff_max'];
                    $types .= "ii";
                }
                break;
            case 'business_own':
                $taxpayerQuery .= " AND t.business_own = ?";
                $params[] = $value;
                $types .= "s";
                break;
            case 'created_time_start':
            case 'created_time_end':
                if (isset($queryParams['created_time_start'], $queryParams['created_time_end'])) {
                    $taxpayerQuery .= " AND t.created_time BETWEEN ? AND ?";
                    $enumeratorQuery .= " AND etp.timeIn BETWEEN ? AND ?";
                    $params[] = $queryParams['created_time_start'];
                    $params[] = $queryParams['created_time_end'];
                    $types .= "ss";
                }
                break;
            case 'updated_time_start':
            case 'updated_time_end':
                if (isset($queryParams['updated_time_start'], $queryParams['updated_time_end'])) {
                    $taxpayerQuery .= " AND t.updated_time BETWEEN ? AND ?";
                    $params[] = $queryParams['updated_time_start'];
                    $params[] = $queryParams['updated_time_end'];
                    $types .= "ss";
                }
                break;
        }
    }

    // Execute taxpayer query
    $stmt1 = $this->conn->prepare($taxpayerQuery);
    if (!empty($types)) {
        $stmt1->bind_param($types, ...$params);
    }
    $stmt1->execute();
    $result1 = $stmt1->get_result();
    while ($row = $result1->fetch_assoc()) {
        $taxpayers[] = $row;
    }
    $stmt1->close();

    // Execute enumerator query
    $stmt2 = $this->conn->prepare($enumeratorQuery);
    if (!empty($types)) {
        $stmt2->bind_param($types, ...$params);
    }
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    while ($row = $result2->fetch_assoc()) {
        $taxpayers[] = $row;
    }
    $stmt2->close();

    // Pagination
    $page = isset($queryParams['page']) ? (int)$queryParams['page'] : 1;
    $limit = isset($queryParams['limit']) ? (int)$queryParams['limit'] : 10;
    $offset = ($page - 1) * $limit;

    $paginatedTaxpayers = array_slice($taxpayers, $offset, $limit);
    $totalRecords = count($taxpayers);
    $totalPages = ceil($totalRecords / $limit);

    // Return the response
    echo json_encode([
        "status" => "success",
        "data" => $paginatedTaxpayers,
        "pagination" => [
            "current_page" => $page,
            "per_page" => $limit,
            "total_pages" => $totalPages,
            "total_records" => $totalRecords
        ]
    ]);
}

    
    
    
    

    // Get taxpayer statistics (total, self-registered, admin-registered)
    public function getTaxpayerStatistics() {
        // Query to count total taxpayers
        $totalQuery = "SELECT COUNT(*) AS total FROM taxpayer";

        // Query to count self-registered taxpayers
        $selfQuery = "SELECT COUNT(*) AS total_self FROM taxpayer WHERE created_by = 'self'";

        // Query to count admin-registered taxpayers
        $adminQuery = "SELECT COUNT(*) AS total_admin FROM taxpayer WHERE created_by = 'admin'";

        // Query to count Inactive taxpayers
        $adminQueryInactiveTaxpayers = "SELECT COUNT(*) AS total_admin_inactive_taxpayer FROM taxpayer_security WHERE verification_status = 'pending'";

        // Query to count Active taxpayers
        $adminQueryActiveTaxpayers = "SELECT COUNT(*) AS total_admin_active_taxpayer FROM taxpayer_security WHERE verification_status = 'verified'";

        try {
            // Execute total taxpayers query
            $totalResult = $this->conn->query($totalQuery);
            $totalCount = $totalResult->fetch_assoc()['total'];

            // Execute self-registered taxpayers query
            $selfResult = $this->conn->query($selfQuery);
            $selfCount = $selfResult->fetch_assoc()['total_self'];

            // Execute admin-registered taxpayers query
            $adminResult = $this->conn->query($adminQuery);
            $adminCount = $adminResult->fetch_assoc()['total_admin'];

            // Execute inactive taxpayers query
            $adminQueryInactiveTaxpayersResult = $this->conn->query($adminQueryInactiveTaxpayers);
            $adminQueryInactiveTaxpayersCount = $adminQueryInactiveTaxpayersResult->fetch_assoc()['total_admin_inactive_taxpayer'];

            // Execute acitve taxpayers query
            $adminQueryActiveTaxpayersResult = $this->conn->query($adminQueryActiveTaxpayers);
            $adminQueryActiveTaxpayersCount = $adminQueryActiveTaxpayersResult->fetch_assoc()['total_admin_active_taxpayer'];

            // Return JSON response
            return json_encode([
                "status" => "success",
                "data" => [
                    "total_registered_taxpayers" => (int)$totalCount,
                    "total_self_registered_taxpayers" => (int)$selfCount,
                    "total_admin_registered_taxpayers" => (int)$adminCount,
                    "total_admin_inactive_taxpayer" => (int)$adminQueryInactiveTaxpayersCount,
                    "total_admin_active_taxpayer" => (int)$adminQueryActiveTaxpayersCount
                ]
            ]);
        } catch (Exception $e) {
            return json_encode([
                "status" => "error",
                "message" => "Failed to fetch statistics",
                "error" => $e->getMessage()
            ]);
        }
    }

    // Update TIN status of a taxpayer
    public function updateTinStatus($input) {
        // Validate input
        if (empty($input['tax_number']) && empty($input['phone']) && empty($input['email'])) {
            return json_encode(["status" => "error", "message" => "Provide tax_number, phone, or email"]);
        }
        if (empty($input['tin_status']) || !in_array($input['tin_status'], ['issued', 'pending'])) {
            return json_encode(["status" => "error", "message" => "TIN status must be either 'Issued' or 'Pending'"]);
        }

        // Determine the identifier (tax_number, phone, or email)
        $identifier = !empty($input['tax_number']) ? $input['tax_number'] : (!empty($input['phone']) ? $input['phone'] : $input['email']);
        $tinStatus = $input['tin_status'];

        // Query to update TIN status
        $query = "
            UPDATE taxpayer_security ts
            INNER JOIN taxpayer t ON t.id = ts.taxpayer_id
            SET ts.tin_status = ?
            WHERE t.tax_number = ? OR t.phone = ? OR t.email = ?
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssss", $tinStatus, $identifier, $identifier, $identifier);
        $stmt->execute();

        // Check if the update was successful
        if ($stmt->affected_rows > 0) {
            return json_encode(["status" => "success", "message" => "TIN status updated successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => "Failed to update TIN status or no changes were made"]);
        }
    }
}
