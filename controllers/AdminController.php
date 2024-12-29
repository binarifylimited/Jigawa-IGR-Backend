<?php
require_once 'config/database.php';
require_once 'helpers/user_helper.php';  // Include the universal duplicate check helper
require_once 'helpers/auth_helper.php';  // For JWT authentication

class AdminController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getTotalAmountPaid($filters) {
        // Base query
        $query = "
            SELECT 
                SUM(pc.amount_paid) AS total_amount_paid
            FROM 
                payment_collection pc
            WHERE 1=1
        ";
        $params = [];
        $types = '';
    
        // Add date range filter
        if (!empty($filters['month']) && !empty($filters['year'])) {
            $query .= " AND MONTH(pc.date_payment_created) = ? AND YEAR(pc.date_payment_created) = ?";
            $params[] = $filters['month'];
            $params[] = $filters['year'];
            $types .= 'ii';
        }
    
        // Prepare and execute the query
        $stmt = $this->conn->prepare($query);
    
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
    
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
    
        // Return the result
        echo json_encode([
            "status" => "success",
            "total_amount_paid" => $data['total_amount_paid'] ?? 0 // Default to 0 if no records found
        ]);
    
        $stmt->close();
    }

    public function getTotalAmountPaidYearly($filters) {
        // Base query
        $query = "
            SELECT 
                SUM(pc.amount_paid) AS total_amount_paid
            FROM 
                payment_collection pc
            WHERE 1=1
        ";
        $params = [];
        $types = '';
    
        // Add year filter
        if (!empty($filters['year'])) {
            $query .= " AND YEAR(pc.date_payment_created) = ?";
            $params[] = $filters['year'];
            $types .= 'i';
        }
    
        // Prepare and execute the query
        $stmt = $this->conn->prepare($query);
    
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
    
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
    
        // Return the result
        echo json_encode([
            "status" => "success",
            "total_amount_paid" => $data['total_amount_paid'] ?? 0 // Default to 0 if no records found
        ]);
    
        $stmt->close();
    }
    
    public function getExpectedMonthlyRevenue($filters) {
        // Base query
        $query = "
            SELECT 
                inv.revenue_head
            FROM 
                invoices inv
            WHERE 1=1
        ";
        $params = [];
        $types = '';
    
        // Add date range filter (month and year)
        if (!empty($filters['month']) && !empty($filters['year'])) {
            $query .= " AND MONTH(inv.date_created) = ? AND YEAR(inv.date_created) = ?";
            $params[] = $filters['month'];
            $params[] = $filters['year'];
            $types .= 'ii';
        }
    
        // Prepare and execute the query
        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
    
        $totalInvoicedAmount = 0;
    
        // Process each invoice's revenue head
        while ($row = $result->fetch_assoc()) {
            $revenueHeads = json_decode($row['revenue_head'], true);
            foreach ($revenueHeads as $revenueHead) {
                $totalInvoicedAmount += $revenueHead['amount']; // Add the amount from each revenue head
            }
        }
    
        $stmt->close();
    
        // Return the result
        echo json_encode([
            "status" => "success",
            "expected_monthly_revenue" => $totalInvoicedAmount
        ]);
    }

    public function getTotalSpecialUsers() {
        // Base query
        $query = "SELECT COUNT(*) AS total_special_users FROM special_users_";
    
        // Execute the query
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
    
        // Return the result
        echo json_encode([
            "status" => "success",
            "total_special_users" => $data['total_special_users'] ?? 0 // Default to 0 if no records found
        ]);
    
        $stmt->close();
    }

    public function getTotalEmployees() {
        // Base query
        $query = "SELECT COUNT(*) AS total_employees FROM special_user_employees";
    
        // Execute the query
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
    
        // Return the result
        echo json_encode([
            "status" => "success",
            "total_employees" => $data['total_employees'] ?? 0 // Default to 0 if no records found
        ]);
    
        $stmt->close();
    }

    public function getTotalAnnualEstimate($filters) {
        // Base query
        $query = "SELECT SUM(annual_gross_income) AS total_annual_estimate FROM employee_salary_and_benefits WHERE 1=1";
    
        $params = [];
        $types = '';
    
        // Add year filter
        if (!empty($filters['year'])) {
            $query .= " AND YEAR(created_date) = ?";
            $params[] = $filters['year'];
            $types .= 'i';
        }
    
        // Prepare and execute the query
        $stmt = $this->conn->prepare($query);
    
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
    
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
    
        // Return the result
        echo json_encode([
            "status" => "success",
            "total_annual_estimate" => $data['total_annual_estimate'] ?? 0 // Default to 0 if no records found
        ]);
    
        $stmt->close();
    }
    
    
    
    
    
    
}