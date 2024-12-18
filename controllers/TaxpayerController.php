<?php
require_once 'config/database.php';

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
            SELECT ts.verification_status, t.id AS taxpayer_id
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
    public function getAllTaxpayers($queryParams) {
        // Base query to fetch taxpayers
        $query = "SELECT id, created_by, tax_number, category, presumptive, first_name, surname, email, phone, 
                            state, lga, address, employment_status, number_of_staff, business_own, 
                            created_time, updated_time
                    FROM taxpayer WHERE 1=1";
        $params = [];
        $types = "";

        // Apply filters
        if (!empty($queryParams['id'])) {
            $query .= " AND id = ?";
            $params[] = $queryParams['id'];
            $types .= "i";
        }
        if (!empty($queryParams['created_by'])) {
            $query .= " AND created_by = ?";
            $params[] = $queryParams['created_by'];
            $types .= "s";
        }
        if (!empty($queryParams['tax_number'])) {
            $query .= " AND tax_number LIKE ?";
            $params[] = '%' . $queryParams['tax_number'] . '%';
            $types .= "s";
        }
        if (!empty($queryParams['category'])) {
            $query .= " AND category = ?";
            $params[] = $queryParams['category'];
            $types .= "s";
        }
        if (!empty($queryParams['presumptive'])) {
            $query .= " AND presumptive = ?";
            $params[] = $queryParams['presumptive'];
            $types .= "s";
        }
        if (!empty($queryParams['first_name'])) {
            $query .= " AND first_name LIKE ?";
            $params[] = '%' . $queryParams['first_name'] . '%';
            $types .= "s";
        }
        if (!empty($queryParams['surname'])) {
            $query .= " AND surname LIKE ?";
            $params[] = '%' . $queryParams['surname'] . '%';
            $types .= "s";
        }
        if (!empty($queryParams['email'])) {
            $query .= " AND email LIKE ?";
            $params[] = '%' . $queryParams['email'] . '%';
            $types .= "s";
        }
        if (!empty($queryParams['phone'])) {
            $query .= " AND phone = ?";
            $params[] = $queryParams['phone'];
            $types .= "s";
        }
        if (!empty($queryParams['state'])) {
            $query .= " AND state = ?";
            $params[] = $queryParams['state'];
            $types .= "s";
        }
        if (!empty($queryParams['lga'])) {
            $query .= " AND lga = ?";
            $params[] = $queryParams['lga'];
            $types .= "s";
        }
        if (!empty($queryParams['address'])) {
            $query .= " AND address LIKE ?";
            $params[] = '%' . $queryParams['address'] . '%';
            $types .= "s";
        }
        if (!empty($queryParams['employment_status'])) {
            $query .= " AND employment_status = ?";
            $params[] = $queryParams['employment_status'];
            $types .= "s";
        }
        if (!empty($queryParams['number_of_staff_min']) && !empty($queryParams['number_of_staff_max'])) {
            $query .= " AND number_of_staff BETWEEN ? AND ?";
            $params[] = $queryParams['number_of_staff_min'];
            $params[] = $queryParams['number_of_staff_max'];
            $types .= "ii";
        }
        if (!empty($queryParams['business_own'])) {
            $query .= " AND business_own = ?";
            $params[] = $queryParams['business_own'];
            $types .= "s";
        }
        if (!empty($queryParams['created_time_start']) && !empty($queryParams['created_time_end'])) {
            $query .= " AND created_time BETWEEN ? AND ?";
            $params[] = $queryParams['created_time_start'];
            $params[] = $queryParams['created_time_end'];
            $types .= "ss";
        }
        if (!empty($queryParams['updated_time_start']) && !empty($queryParams['updated_time_end'])) {
            $query .= " AND updated_time BETWEEN ? AND ?";
            $params[] = $queryParams['updated_time_start'];
            $params[] = $queryParams['updated_time_end'];
            $types .= "ss";
        }

        // Execute query with pagination
        $page = isset($queryParams['page']) ? (int)$queryParams['page'] : 1;
        $limit = isset($queryParams['limit']) ? (int)$queryParams['limit'] : 10;
        $offset = ($page - 1) * $limit;

        $query .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $this->conn->prepare($query);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch results
        $taxpayers = [];
        while ($row = $result->fetch_assoc()) {
            $taxpayers[] = $row;
        }

        // Get total count for pagination
        $totalQuery = "SELECT COUNT(*) as total FROM taxpayer WHERE 1=1";
        $totalStmt = $this->conn->prepare($totalQuery);
        $totalStmt->execute();
        $totalResult = $totalStmt->get_result();
        $total = $totalResult->fetch_assoc()['total'];
        $totalPages = ceil($total / $limit);

        // Return JSON response
        return json_encode([
            "status" => "success",
            "data" => $taxpayers,
            "pagination" => [
                "current_page" => $page,
                "per_page" => $limit,
                "total_pages" => $totalPages,
                "total_records" => $total
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

            // Return JSON response
            return json_encode([
                "status" => "success",
                "data" => [
                    "total_registered_taxpayers" => (int)$totalCount,
                    "total_self_registered_taxpayers" => (int)$selfCount,
                    "total_admin_registered_taxpayers" => (int)$adminCount
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
}
