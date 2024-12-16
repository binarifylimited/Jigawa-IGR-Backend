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
}
