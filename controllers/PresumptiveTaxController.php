<?php
require_once 'config/database.php';
require_once 'helpers/user_helper.php';  // Include the universal duplicate check helper
require_once 'helpers/auth_helper.php';  // For JWT authentication

class TaxController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function calculatePresumptiveTax($data)
    {
        // Validate required fields
        if (empty($data['tax_number'])) {
            echo json_encode(['status' => 'error', 'message' => 'Tax number (or payer_id for special users) is required']);
            http_response_code(400); // Bad Request
            return;
        }

        // Define tables to check
        $taxpayerTables = [
            [
                'table' => 'taxpayer',
                'identifier_column' => 'tax_number',
                'number_of_staff_column' => 'number_of_staff',
                'business_type_join' => 'LEFT JOIN taxpayer_business tb ON tp.id = tb.taxpayer_id',
                'business_type_column' => 'tb.business_type'
            ],
            [
                'table' => 'enumerator_tax_payers',
                'identifier_column' => 'tax_number',
                'number_of_staff_column' => 'staff_quota',
                'business_type_join' => '',
                'business_type_column' => 'business_type'
            ],
            [
                'table' => 'special_users_',
                'identifier_column' => 'payer_id',
                'number_of_staff_column' => 'staff_quota',
                'business_type_join' => '',
                'business_type_column' => 'industry'
            ]
        ];

        $taxpayer = null;

        // Search through the tables dynamically
        foreach ($taxpayerTables as $table) {
            $query = "
                SELECT tp.{$table['identifier_column']} AS identifier, tp.{$table['number_of_staff_column']} AS number_of_staff, {$table['business_type_column']} AS business_type
                FROM {$table['table']} tp
                {$table['business_type_join']}
                WHERE tp.{$table['identifier_column']} = ?
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('s', $data['tax_number']);
            $stmt->execute();
            $result = $stmt->get_result();
            $taxpayer = $result->fetch_assoc();
            $stmt->close();
            if ($taxpayer) {
                break; // Stop searching once taxpayer is found
            }
        }

        if (!$taxpayer) {
            echo json_encode(['status' => 'error', 'message' => 'Taxpayer not found']);
            http_response_code(404); // Not Found
            return;
        }

        // Determine the number of staff
        $numberOfStaff = !empty($data['number_of_staff']) 
        ? $data['number_of_staff'] 
        : $taxpayer['number_of_staff'];

        // Fetch presumptive tax details
        $businessType = !empty($data['business_type']) 
        ? $data['business_type'] 
        : $taxpayer['business_type'];
        $presumptiveTaxQuery = "
            SELECT pt.micro, pt.small, pt.medium, pt.frequency
            FROM presumptive_tax pt
            INNER JOIN presumptive_tax_businesses ptb ON pt.business_id = ptb.id
            WHERE ptb.business_type = ?
        ";
        $stmt = $this->conn->prepare($presumptiveTaxQuery);
        $stmt->bind_param('s', $businessType);
        $stmt->execute();
        $result = $stmt->get_result();
        $presumptiveTax = $result->fetch_assoc();
        $stmt->close();

        if (!$presumptiveTax) {
            echo json_encode(['status' => 'error', 'message' => 'No presumptive tax setup for the business type']);
            http_response_code(404); // Not Found
            return;
        }

        // Determine tax category and amount
        $taxCategory = '';
        $payableAmount = 0;
        $revenueHeadId = null;
        if ($numberOfStaff == "1-9") {
            $taxCategory = 'micro';
            $payableAmount = $presumptiveTax['micro'];
            $revenueHeadId = 11; // Micro
        } elseif ($numberOfStaff == "10-29") {
            $taxCategory = 'small';
            $payableAmount = $presumptiveTax['small'];
            $revenueHeadId = 12; // Small
        } elseif ($numberOfStaff == "30-50") {
            $taxCategory = 'medium';
            $payableAmount = $presumptiveTax['medium'];
            $revenueHeadId = 13; // Medium
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid staff count for presumptive tax calculation']);
            http_response_code(400); // Bad Request
            return;
        }

        // Prepare response
        $response = [
            'status' => 'success',
            'data' => [
                'identifier' => $taxpayer['identifier'],
                'business_type' => $businessType,
                'number_of_staff' => $numberOfStaff,
                'tax_category' => $taxCategory,
                'payable_amount' => $payableAmount,
                'payment_frequency' => $presumptiveTax['frequency'],
                'revenue_head_id' => $revenueHeadId
            ]
        ];

        echo json_encode($response);
    }




}