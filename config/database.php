<?php
// class Database {
//     private $host = 'localhost';      // Database host
//     private $db_name = 'jigawa'; // Your database name
//     private $username = 'root';// Database username
//     private $password = '';// Database password
//     public $conn;

//     // Get the database connection using MySQLi
//     public function getConnection() {
//         $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);

//         // Check the connection
//         if ($this->conn->connect_error) {
//             die('Connection failed: ' . $this->conn->connect_error);
//         }
        
//         return $this->conn;
//     }
// }

class Database {
    private $host = 'mysql-188738-0.cloudclusters.net';      // Database host
    private $db_name = 'test_db'; // Your database name
    private $username = 'test';// Database username
    private $password = '12345678';// Database password
    private $port = 10049;// Database port

    public $conn;

    // Get the database connection using MySQLi
    public function getConnection() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name, $this->port);

        // Check the connection
        if ($this->conn->connect_error) {
            die('Connection failed: ' . $this->conn->connect_error);
        }
        
        return $this->conn;
    }
}