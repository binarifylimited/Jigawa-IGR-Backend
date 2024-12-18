<?php
require_once 'vendor/autoload.php';  // Load JWT package
require_once 'config/database.php';  // Load the database connection

use \Firebase\JWT\JWT;

class AuthController {
    private $secret_key = 'your_secret_key_plateau_35c731567705ad451533eb8516558ca0dad1e3e56d095c50289aabbff516a7f7_your_secret_key_plateau';  // Use a strong secret key!
    private $conn;  // Database connection

    // Constructor: initialize database connection
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Login function that checks all user types automatically.
     * It will search for the user in all relevant tables and return a JWT token.
     */
    public function login($email, $password) {
        // Check each user type, one by one
        $user = $this->checkAdministrativeUsers($email, $password);
        if (!$user) {
            $user = $this->checkMdaUsers($email, $password);
        }
        if (!$user) {
            $user = $this->checkEnumeratorUsers($email, $password);
        }
        if (!$user) {
            $user = $this->checkTaxPayerUsers($email, $password);
        }
        if (!$user) {
            $user = $this->checkSpecialUsers($email, $password);
        }

        // If no user is found, return an error
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
            return;
        }

        // Credentials are valid, generate JWT
        $issued_at = time();
        $expiration_time = $issued_at + 3600;  // jwt valid for 1 hour
        $payload = array(
            'iss' => 'https://phpclusters-188739-0.cloudclusters.net',  // Issuer
            'iat' => $issued_at,               // Issued at
            'exp' => $expiration_time,         // Expiration time
            'user_id' => $user['id'], 
            'user_id_number' => $user['tax_number'],          // Store user ID in token
            'email' => $user['email'],         // Email in the payload
            'user_type' => $user['user_type'], // Store user type (admin, mda, etc.)
        );

        // Include extra fields based on user type
        if ($user['user_type'] == 'admin') {
            $payload['role'] = $user['role'];
            $payload['fullname'] = $user['fullname'];
        } elseif ($user['user_type'] == 'mda') {
            $payload['mda_id'] = $user['mda_id'];
            $payload['fullname'] = $user['name'];
        } elseif ($user['user_type'] == 'enumerator') {
            $payload['agent_id'] = $user['agent_id'];
            $payload['fullname'] = $user['fullname'];
        } elseif ($user['user_type'] == 'tax_payer') {
            $payload['tax_number'] = $user['tax_number'];
            $payload['TIN'] = $user['TIN'];
            $payload['fullname'] = $user['first_name'].' '.$user['surname'];
            $payload['first_name'] = $user['first_name'];
            $payload['surname'] = $user['surname'];
        } elseif ($user['user_type'] == 'special_user') {
            $payload['official_TIN'] = $user['official_TIN'];
            $payload['payer_id'] = $user['payer_id'];
            $payload['fullname'] = $user['name'];
        }

        // Encode the JWT
        $jwt = JWT::encode($payload, $this->secret_key, 'HS256');

        // Return the token to the client
        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful',
            'token' => $jwt  // Return the token
        ]);
    }

    // Check for admin users
    private function checkAdministrativeUsers($email, $password) {
        $query = 'SELECT id, email, fullname, password, role FROM administrative_users WHERE email = ? LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $user['user_type'] = 'admin';  // Identify the user type
                return $user;
            }
        }
        return false;
    }

    // Check for MDA users
    private function checkMdaUsers($email, $password) {
        $query = 'SELECT id, email, password, name, mda_id FROM mda_users WHERE email = ? LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $user['user_type'] = 'mda';  // Identify the user type
                return $user;
            }
        }
        return false;
    }

    // Check for enumerator users
    private function checkEnumeratorUsers($email, $password) {
        $query = 'SELECT id, email, password, fullname, agent_id FROM enumerator_users WHERE email = ? LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $user['user_type'] = 'enumerator';  // Identify the user type
                return $user;
            }
        }
        return false;
    }

    // Check for tax payer users
    private function checkTaxPayerUsers($email, $password) {
        $query = 'SELECT t.id, t.email, ts.password, t.first_name, t.surname, t.tax_number, ti.TIN FROM taxpayer t JOIN taxpayer_security ts ON t.id = ts.taxpayer_id JOIN taxpayer_identification ti ON t.id = ti.taxpayer_id WHERE t.email = ? LIMIT 1;';
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $user['user_type'] = 'tax_payer';  // Identify the user type
                return $user;
            }
        }
        return false;
    }

    // Check for special users
    private function checkSpecialUsers($email, $password) {
        $query = 'SELECT id, email, password, name, official_TIN, payer_id FROM special_users_ WHERE email = ? LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($password== $user['password']) {
                $user['user_type'] = 'special_user';  // Identify the user type
                return $user;
            }
        }
        return false;
    }
}
