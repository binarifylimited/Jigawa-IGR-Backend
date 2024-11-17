<?php

require_once 'config/database.php';

class ApiAuditLogger {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function logRequest($endpoint, $method, $requestPayload, $responsePayload, $statusCode, $userId = null, $userType = null) {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $query = "INSERT INTO api_audit_logs (endpoint, method, request_payload, response_payload, status_code, ip_address, user_agent, user_id, user_type)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "ssssissis",
            $endpoint,
            $method,
            $requestPayload,
            $responsePayload,
            $statusCode,
            $ipAddress,
            $userAgent,
            $userId,
            $userType
        );
        $stmt->execute();
    }
}
