<?php
require_once 'config/database.php';

class EnumeratorController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Fetch enumerator admins with optional filters, pagination, and tax payer count
    public function getEnumeratorAdmins($queryParams) {
        // Default pagination
        $page = isset($queryParams['page']) ? (int)$queryParams['page'] : 1;
        $limit = isset($queryParams['limit']) ? (int)$queryParams['limit'] : 10;
        $offset = ($page - 1) * $limit;

        // Base query to get enumerator admins with tax payer count
        $query = "SELECT eu.*, 
                         COUNT(etp.id) AS tax_payer_count 
                  FROM enumerator_users eu
                  LEFT JOIN enumerator_tax_payers etp 
                         ON eu.id = etp.created_by_enumerator_user
                  WHERE 1=1";
        $params = [];
        $types = "";

        // Optional filters
        if (!empty($queryParams['name'])) {
            $query .= " AND eu.fullname LIKE ?";
            $params[] = '%' . $queryParams['name'] . '%';
            $types .= "s";
        }
        if (!empty($queryParams['email'])) {
            $query .= " AND eu.email = ?";
            $params[] = $queryParams['email'];
            $types .= "s";
        }
        if (isset($queryParams['status'])) {
            $query .= " AND eu.status = ?";
            $params[] = $queryParams['status'];
            $types .= "i";
        }
        if (!empty($queryParams['agent_id'])) {
            $query .= " AND eu.agent_id = ?";
            $params[] = $queryParams['agent_id'];
            $types .= "s";
        }
        if (!empty($queryParams['state'])) {
            $query .= " AND eu.state = ?";
            $params[] = $queryParams['state'];
            $types .= "s";
        }
        if (!empty($queryParams['lga'])) {
            $query .= " AND eu.lga = ?";
            $params[] = $queryParams['lga'];
            $types .= "s";
        }
        if (!empty($queryParams['start_date']) && !empty($queryParams['end_date'])) {
            $query .= " AND eu.timeIn BETWEEN ? AND ?";
            $params[] = $queryParams['start_date'];
            $params[] = $queryParams['end_date'];
            $types .= "ss";
        }

        // Group by each enumerator and add pagination
        $query .= " GROUP BY eu.id LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        // Execute query
        $stmt = $this->conn->prepare($query);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch results and unset the password field
        $admins = [];
        while ($row = $result->fetch_assoc()) {
            unset($row['password']);  // Remove the password field
            $admins[] = $row;
        }

        // Get total count for pagination
        $totalQuery = "SELECT COUNT(DISTINCT id) as total FROM enumerator_users WHERE 1=1";
        if (!empty($queryParams['name'])) {
            $totalQuery .= " AND fullname LIKE '%" . $queryParams['name'] . "%'";
        }
        if (!empty($queryParams['email'])) {
            $totalQuery .= " AND email = '" . $queryParams['email'] . "'";
        }
        if (isset($queryParams['status'])) {
            $totalQuery .= " AND status = " . (int)$queryParams['status'];
        }
        if (!empty($queryParams['agent_id'])) {
            $totalQuery .= " AND agent_id = '" . $queryParams['agent_id'] . "'";
        }
        if (!empty($queryParams['state'])) {
            $totalQuery .= " AND state = '" . $queryParams['state'] . "'";
        }
        if (!empty($queryParams['lga'])) {
            $totalQuery .= " AND lga = '" . $queryParams['lga'] . "'";
        }
        if (!empty($queryParams['start_date']) && !empty($queryParams['end_date'])) {
            $totalQuery .= " AND timeIn BETWEEN '" . $queryParams['start_date'] . "' AND '" . $queryParams['end_date'] . "'";
        }

        $totalResult = $this->conn->query($totalQuery);
        $total = $totalResult->fetch_assoc()['total'];
        $totalPages = ceil($total / $limit);

        // Return JSON response
        return json_encode([
            "status" => "success",
            "data" => $admins,
            "pagination" => [
                "current_page" => $page,
                "per_page" => $limit,
                "total_pages" => $totalPages,
                "total_records" => $total
            ]
        ]);
    }

    // Fetch tax payers under a specific enumerator admin (or all if no enumerator_id is provided)
    public function getTaxPayersByEnumerator($queryParams) {
        // Default pagination
        $page = isset($queryParams['page']) ? (int)$queryParams['page'] : 1;
        $limit = isset($queryParams['limit']) ? (int)$queryParams['limit'] : 10;
        $offset = ($page - 1) * $limit;

        // Base query to get tax payers, conditionally filtering by enumerator_id
        $query = "SELECT * FROM enumerator_tax_payers WHERE 1=1";
        $params = [];
        $types = "";

        // Optional enumerator filter
        if (!empty($queryParams['enumerator_id'])) {
            $query .= " AND created_by_enumerator_user = ?";
            $params[] = $queryParams['enumerator_id'];
            $types .= "i";
        }

        // Additional filters (example for tax_number, state, lga, etc.)
        if (!empty($queryParams['tax_number'])) {
            $query .= " AND tax_number = ?";
            $params[] = $queryParams['tax_number'];
            $types .= "s";
        }
        if (!empty($queryParams['first_name'])) {
            $query .= " AND first_name LIKE ?";
            $params[] = '%' . $queryParams['first_name'] . '%';
            $types .= "s";
        }
        // Add other filters here as needed ...

        // Add pagination
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        // Execute query
        $stmt = $this->conn->prepare($query);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch results and unset sensitive fields
        $taxPayers = [];
        while ($row = $result->fetch_assoc()) {
            unset($row['password'], $row['verification_code']); // Remove sensitive fields
            $taxPayers[] = $row;
        }

        // Get total count for pagination
        $totalQuery = "SELECT COUNT(*) as total FROM enumerator_tax_payers WHERE 1=1";
        $totalParams = [];
        $totalTypes = "";

        // Replicate filters in the total count query
        if (!empty($queryParams['enumerator_id'])) {
            $totalQuery .= " AND created_by_enumerator_user = ?";
            $totalParams[] = $queryParams['enumerator_id'];
            $totalTypes .= "i";
        }
        // (Add other filters to $totalQuery as above)

        $totalStmt = $this->conn->prepare($totalQuery);
        if ($totalTypes) {
            $totalStmt->bind_param($totalTypes, ...$totalParams);
        }
        $totalStmt->execute();
        $totalResult = $totalStmt->get_result();
        $total = $totalResult->fetch_assoc()['total'];
        $totalPages = ceil($total / $limit);

        // Return JSON response
        return json_encode([
            "status" => "success",
            "data" => $taxPayers,
            "pagination" => [
                "current_page" => $page,
                "per_page" => $limit,
                "total_pages" => $totalPages,
                "total_records" => $total
            ]
        ]);
    }

    // Get Enum taxpayer statistics 
    public function getEnumTaxpayerStatistics() {
        // Query to count total taxpayers
        $totalQueryEnumAgent = "SELECT COUNT(*) AS total_enumerator_agent FROM enumerator_users";

        // Query to count self-registered taxpayers
        $totalQueryTaxpayer = "SELECT COUNT(*) AS total_enumerator_tax_payers FROM enumerator_tax_payers";

        try {
            // Execute total taxpayers query
            $totalEnumAgentResult = $this->conn->query($totalQueryEnumAgent);
            $totalEnumAgentCount = $totalEnumAgentResult->fetch_assoc()['total_enumerator_agent'];

            // Execute self-registered taxpayers query
            $totalEnumTapayerResult = $this->conn->query($totalQueryTaxpayer);
            $totalEnumTapayerCount = $totalEnumTapayerResult->fetch_assoc()['total_enumerator_tax_payers'];

          
            // Return JSON response
            return json_encode([
                "status" => "success",
                "data" => [
                    "total_enumerator_agent" => (int)$totalEnumAgentCount,
                    "total_enumerator_tax_payers" => (int)$totalEnumTapayerCount
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
