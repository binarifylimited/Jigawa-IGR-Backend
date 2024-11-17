<?php
class Database {
    private $host = 'localhost';      // Database host
    private $db_name = 'plateauigr_ibs'; // Your database name
    private $username = 'root';// Database username
    private $password = '';// Database password
    public $conn;

    // Get the database connection using MySQLi
    public function getConnection() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);

        // Check the connection
        if ($this->conn->connect_error) {
            die('Connection failed: ' . $this->conn->connect_error);
        }
        
        return $this->conn;
    }
}
