<?php
require_once 'config/database.php';

class SpecialUserController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Fetch all special users with filters, employee count, total monthly tax payable, annual tax, and total payments
    public function getAllSpecialUsers($queryParams) {
        // Base query with joins for employee count, monthly and annual tax, and total payments
        $query = "
            SELECT su.id, su.payer_id, su.name, su.industry, su.state, su.lga, su.email, su.phone, su.category, su.official_TIN,
                   COUNT(e.id) AS employee_count,
                   IFNULL(SUM(esb.monthly_tax_payable), 0) AS total_monthly_tax,
                   IFNULL(SUM(esb.monthly_tax_payable) * 12, 0) AS total_annual_tax,
                   IFNULL(SUM(pc.amount_paid), 0) AS total_payments
            FROM special_users_ su
            LEFT JOIN special_user_employees e ON su.id = e.associated_special_user_id
            LEFT JOIN employee_salary_and_benefits esb ON e.id = esb.employee_id
            LEFT JOIN payment_collection pc ON su.payer_id = pc.user_id
            WHERE 1=1
        ";
        
        $params = [];
        $types = "";

        // Apply filters conditionally
        if (!empty($queryParams['payer_id'])) {
            $query .= " AND su.payer_id = ?";
            $params[] = $queryParams['payer_id'];
            $types .= "s";
        }
        if (!empty($queryParams['name'])) {
            $query .= " AND su.name LIKE ?";
            $params[] = '%' . $queryParams['name'] . '%';
            $types .= "s";
        }
        if (!empty($queryParams['industry'])) {
            $query .= " AND su.industry = ?";
            $params[] = $queryParams['industry'];
            $types .= "s";
        }
        if (!empty($queryParams['official_TIN'])) {
            $query .= " AND su.official_TIN = ?";
            $params[] = $queryParams['official_TIN'];
            $types .= "s";
        }
        if (!empty($queryParams['email'])) {
            $query .= " AND su.email = ?";
            $params[] = $queryParams['email'];
            $types .= "s";
        }
        if (!empty($queryParams['phone'])) {
            $query .= " AND su.phone = ?";
            $params[] = $queryParams['phone'];
            $types .= "s";
        }
        if (!empty($queryParams['state'])) {
            $query .= " AND su.state = ?";
            $params[] = $queryParams['state'];
            $types .= "s";
        }
        if (!empty($queryParams['lga'])) {
            $query .= " AND su.lga = ?";
            $params[] = $queryParams['lga'];
            $types .= "s";
        }
        if (!empty($queryParams['category'])) {
            $query .= " AND su.category = ?";
            $params[] = $queryParams['category'];
            $types .= "s";
        }
        if (!empty($queryParams['start_timeIn']) && !empty($queryParams['end_timeIn'])) {
            $query .= " AND su.timeIn BETWEEN ? AND ?";
            $params[] = $queryParams['start_timeIn'];
            $params[] = $queryParams['end_timeIn'];
            $types .= "ss";
        }
        if (!empty($queryParams['min_staff_quota']) && !empty($queryParams['max_staff_quota'])) {
            $query .= " AND su.staff_quota BETWEEN ? AND ?";
            $params[] = $queryParams['min_staff_quota'];
            $params[] = $queryParams['max_staff_quota'];
            $types .= "ii";
        }

        // Group by each special user to get individual records with aggregated fields
        $query .= " GROUP BY su.id";

        // Execute query
        $stmt = $this->conn->prepare($query);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch results
        $specialUsers = [];
        while ($row = $result->fetch_assoc()) {
            $specialUsers[] = $row;
        }

        // Return JSON response
        return json_encode([
            "status" => "success",
            "data" => $specialUsers
        ]);
    }

    // Fetch employees under a specific special user with optional pagination and filters
    public function getEmployeesBySpecialUser($queryParams) {
        // Check if the associated_special_user_id is provided
        if (empty($queryParams['special_user_id'])) {
            return json_encode(["status" => "error", "message" => "Special user ID is required"]);
        }

        $specialUserId = $queryParams['special_user_id'];

        // Default pagination
        $page = isset($queryParams['page']) ? (int)$queryParams['page'] : 1;
        $limit = isset($queryParams['limit']) ? (int)$queryParams['limit'] : 10;
        $offset = ($page - 1) * $limit;

        // Base query to get employees for the specified special user
        $query = "SELECT e.*, esb.basic_salary, esb.date_employed, esb.housing, esb.transport,
                         esb.utility, esb.medical, esb.entertainment, esb.leaves,
                         esb.annual_gross_income, esb.new_gross, esb.monthly_tax_payable
                  FROM special_user_employees e
                  LEFT JOIN employee_salary_and_benefits esb ON e.id = esb.employee_id
                  WHERE e.associated_special_user_id = ?";
        $params = [$specialUserId];
        $types = "i";

        // Apply filters conditionally
        if (!empty($queryParams['id'])) {
            $query .= " AND e.id = ?";
            $params[] = $queryParams['id'];
            $types .= "i";
        }
        if (!empty($queryParams['fullname'])) {
            $query .= " AND e.fullname LIKE ?";
            $params[] = '%' . $queryParams['fullname'] . '%';
            $types .= "s";
        }
        if (!empty($queryParams['email'])) {
            $query .= " AND e.email = ?";
            $params[] = $queryParams['email'];
            $types .= "s";
        }
        if (!empty($queryParams['phone'])) {
            $query .= " AND e.phone = ?";
            $params[] = $queryParams['phone'];
            $types .= "s";
        }
        if (!empty($queryParams['payer_id'])) {
            $query .= " AND e.payer_id = ?";
            $params[] = $queryParams['payer_id'];
            $types .= "s";
        }
        if (!empty($queryParams['created_date_start']) && !empty($queryParams['created_date_end'])) {
            $query .= " AND e.created_date BETWEEN ? AND ?";
            $params[] = $queryParams['created_date_start'];
            $params[] = $queryParams['created_date_end'];
            $types .= "ss";
        }
        if (!empty($queryParams['basic_salary_min']) && !empty($queryParams['basic_salary_max'])) {
            $query .= " AND esb.basic_salary BETWEEN ? AND ?";
            $params[] = $queryParams['basic_salary_min'];
            $params[] = $queryParams['basic_salary_max'];
            $types .= "ii";
        }
        if (!empty($queryParams['date_employed_start']) && !empty($queryParams['date_employed_end'])) {
            $query .= " AND esb.date_employed BETWEEN ? AND ?";
            $params[] = $queryParams['date_employed_start'];
            $params[] = $queryParams['date_employed_end'];
            $types .= "ss";
        }
        if (!empty($queryParams['housing_min']) && !empty($queryParams['housing_max'])) {
            $query .= " AND esb.housing BETWEEN ? AND ?";
            $params[] = $queryParams['housing_min'];
            $params[] = $queryParams['housing_max'];
            $types .= "ii";
        }
        if (!empty($queryParams['transport_min']) && !empty($queryParams['transport_max'])) {
            $query .= " AND esb.transport BETWEEN ? AND ?";
            $params[] = $queryParams['transport_min'];
            $params[] = $queryParams['transport_max'];
            $types .= "ii";
        }
        if (!empty($queryParams['utility_min']) && !empty($queryParams['utility_max'])) {
            $query .= " AND esb.utility BETWEEN ? AND ?";
            $params[] = $queryParams['utility_min'];
            $params[] = $queryParams['utility_max'];
            $types .= "ii";
        }
        if (!empty($queryParams['medical_min']) && !empty($queryParams['medical_max'])) {
            $query .= " AND esb.medical BETWEEN ? AND ?";
            $params[] = $queryParams['medical_min'];
            $params[] = $queryParams['medical_max'];
            $types .= "ii";
        }
        if (!empty($queryParams['entertainment_min']) && !empty($queryParams['entertainment_max'])) {
            $query .= " AND esb.entertainment BETWEEN ? AND ?";
            $params[] = $queryParams['entertainment_min'];
            $params[] = $queryParams['entertainment_max'];
            $types .= "ii";
        }
        if (!empty($queryParams['leaves_min']) && !empty($queryParams['leaves_max'])) {
            $query .= " AND esb.leaves BETWEEN ? AND ?";
            $params[] = $queryParams['leaves_min'];
            $params[] = $queryParams['leaves_max'];
            $types .= "ii";
        }
        if (!empty($queryParams['annual_gross_income_min']) && !empty($queryParams['annual_gross_income_max'])) {
            $query .= " AND esb.annual_gross_income BETWEEN ? AND ?";
            $params[] = $queryParams['annual_gross_income_min'];
            $params[] = $queryParams['annual_gross_income_max'];
            $types .= "ii";
        }
        if (!empty($queryParams['new_gross_min']) && !empty($queryParams['new_gross_max'])) {
            $query .= " AND esb.new_gross BETWEEN ? AND ?";
            $params[] = $queryParams['new_gross_min'];
            $params[] = $queryParams['new_gross_max'];
            $types .= "ii";
        }
        if (!empty($queryParams['monthly_tax_payable_min']) && !empty($queryParams['monthly_tax_payable_max'])) {
            $query .= " AND esb.monthly_tax_payable BETWEEN ? AND ?";
            $params[] = $queryParams['monthly_tax_payable_min'];
            $params[] = $queryParams['monthly_tax_payable_max'];
            $types .= "ii";
        }

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

        // Fetch results
        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }

        // Get total count for pagination
        $totalQuery = "SELECT COUNT(*) as total FROM special_user_employees WHERE associated_special_user_id = ?";
        $totalStmt = $this->conn->prepare($totalQuery);
        $totalStmt->bind_param("i", $specialUserId);
        $totalStmt->execute();
        $totalResult = $totalStmt->get_result();
        $total = $totalResult->fetch_assoc()['total'];
        $totalPages = ceil($total / $limit);

        // Return JSON response
        return json_encode([
            "status" => "success",
            "data" => $employees,
            "pagination" => [
                "current_page" => $page,
                "per_page" => $limit,
                "total_pages" => $totalPages,
                "total_records" => $total
            ]
        ]);
    }
}
