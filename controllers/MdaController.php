<?php
require_once 'config/database.php';
require_once 'helpers/validation_helper.php';  // Use the MDA duplicate check

class MdaController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Create a new MDA and its contact information.
     */
    public function createMda($data) {
        // Validate required fields for MDA
        if (!isset($data['fullname'], $data['mda_code'], $data['email'], $data['phone'], $data['industry'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields: fullname, mda_code, email, phone, industry']);
            http_response_code(400); // Bad request
            return;
        }

        // Check if the MDA fullname or MDA code already exists in the 'mda' table
        if (isDuplicateMda($this->conn, $data['fullname'], $data['mda_code'])) {
            echo json_encode(['status' => 'error', 'message' => 'MDA with this name or MDA code already exists']);
            http_response_code(409); // Conflict
            return;
        }

        // Start transaction
        $this->conn->begin_transaction();

        try {
            // Insert MDA into 'mda' table
            $query = "INSERT INTO mda (fullname, mda_code, email, phone, industry, allow_payment, status, time_in) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                'ssssssi',
                $data['fullname'],
                $data['mda_code'],
                $data['email'],
                $data['phone'],
                $data['industry'],
                $data['allow_payment'], // Default to allow payment (1 = true)
                $data['status']       // Default status to active (1 = active)
            );

            if (!$stmt->execute()) {
                throw new Exception('Error creating MDA: ' . $stmt->error);
            }

            // Get the MDA ID for the newly created MDA
            $mda_id = $stmt->insert_id;

            // Insert MDA contact information if provided
            if (isset($data['contact_info']) && is_array($data['contact_info'])) {
                $this->createMdaContactInfo($mda_id, $data['contact_info']);
            }

            // Commit transaction
            $this->conn->commit();

            // Return success response
            echo json_encode([
                'status' => 'success',
                'message' => 'MDA created successfully',
                'mda_id' => $mda_id
            ]);

        } catch (Exception $e) {
            // Rollback transaction in case of an error
            $this->conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        } finally {
            $stmt->close();
        }
    }

    /**
     * Helper function to insert MDA contact information into 'mda_contact_info' table.
     */
    private function createMdaContactInfo($mda_id, $contact_info) {
        // Ensure contact information fields are present
        if (!isset($contact_info['state'], $contact_info['geolocation'], $contact_info['lga'], $contact_info['address'])) {
            throw new Exception('Missing required contact info fields: state, geolocation, lga, address');
        }

        $query = "INSERT INTO mda_contact_info (mda_id, state, geolocation, lga, address) 
                  VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'issss',
            $mda_id,
            $contact_info['state'],
            $contact_info['geolocation'],
            $contact_info['lga'],
            $contact_info['address']
        );

        if (!$stmt->execute()) {
            throw new Exception('Error creating MDA contact information: ' . $stmt->error);
        }

        $stmt->close();
    }

    /**
     * Update MDA information and optional contact information.
     */
    public function updateMda($data) {
        // Validate required fields
        if (!isset($data['mda_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required field: mda_id']);
            http_response_code(400); // Bad request
            return;
        }

        $mda_id = $data['mda_id'];

        // Check if the MDA exists
        $query = "SELECT id FROM mda WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $mda_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'MDA not found']);
            http_response_code(404); // Not found
            $stmt->close();
            return;
        }

        $stmt->close();

        // Optionally, check for duplicates (if updating fullname or mda_code)
        if (isset($data['fullname']) || isset($data['mda_code'])) {
            $query = "SELECT id FROM mda WHERE (fullname = ? OR mda_code = ?) AND id != ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $fullname = $data['fullname'] ?? '';
            $mda_code = $data['mda_code'] ?? '';
            $stmt->bind_param('ssi', $fullname, $mda_code, $mda_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                echo json_encode(['status' => 'error', 'message' => 'MDA with this name or MDA code already exists']);
                http_response_code(409); // Conflict
                $stmt->close();
                return;
            }

            $stmt->close();
        }

        // Update the MDA details
        $query = "UPDATE mda SET fullname = COALESCE(?, fullname), mda_code = COALESCE(?, mda_code), email = COALESCE(?, email), phone = COALESCE(?, phone), industry = COALESCE(?, industry), allow_payment = COALESCE(?, allow_payment), status = COALESCE(?, status) WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'ssssssii',
            $data['fullname'],
            $data['mda_code'],
            $data['email'],
            $data['phone'],
            $data['industry'],
            $data['allow_payment'],
            $data['status'],
            $mda_id
        );

        if (!$stmt->execute()) {
            echo json_encode(['status' => 'error', 'message' => 'Error updating MDA: ' . $stmt->error]);
            http_response_code(500); // Internal Server Error
            return;
        }

        $stmt->close();

        // Update MDA contact information if provided
        if (isset($data['contact_info']) && is_array($data['contact_info'])) {
            $this->updateMdaContactInfo($mda_id, $data['contact_info']);
        }

        // Return success response
        echo json_encode([
            'status' => 'success',
            'message' => 'MDA updated successfully'
        ]);
    }

    /**
     * Update MDA contact information.
     */
    private function updateMdaContactInfo($mda_id, $contact_info) {
        $query = "UPDATE mda_contact_info SET state = COALESCE(?, state), geolocation = COALESCE(?, geolocation), lga = COALESCE(?, lga), address = COALESCE(?, address) WHERE mda_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'ssssi',
            $contact_info['state'],
            $contact_info['geolocation'],
            $contact_info['lga'],
            $contact_info['address'],
            $mda_id
        );

        if (!$stmt->execute()) {
            throw new Exception('Error updating MDA contact information: ' . $stmt->error);
        }

        $stmt->close();
    }

    /**
     * Fetch all MDAs with pagination.
     */
    public function getAllMdas($page, $limit) {
        // Set default page and limit if not provided
        $page = isset($page) ? (int)$page : 1;
        $limit = isset($limit) ? (int)$limit : 10;

        // Calculate the offset
        $offset = ($page - 1) * $limit;

        // Fetch the total number of MDAs
        $count_query = "SELECT COUNT(*) as total FROM mda";
        $result = $this->conn->query($count_query);
        $total_mdas = $result->fetch_assoc()['total'];

        // Fetch the paginated MDAs
        $query = "SELECT * FROM mda LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $mdas = $result->fetch_all(MYSQLI_ASSOC);

        // Calculate total pages
        $total_pages = ceil($total_mdas / $limit);

        // Return response
        echo json_encode([
            'status' => 'success',
            'data' => [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_mdas' => $total_mdas,
                'mdas' => $mdas
            ]
        ]);

        $stmt->close();
    }

    /**
     * Fetch MDA by various filters (id, fullname, mda_code, email, allow_payment, status).
     */
    public function getMdaByFilters($filters) {
        // Base query
        $query = "SELECT * FROM mda WHERE 1 = 1"; // 1 = 1 is a dummy condition to simplify appending other conditions
        $params = [];
        $types = '';

        // Add conditions dynamically
        if (isset($filters['id'])) {
            $query .= " AND id = ?";
            $params[] = $filters['id'];
            $types .= 'i';
        }

        if (isset($filters['fullname'])) {
            // Using LIKE for partial matching on fullname
            $query .= " AND fullname LIKE ?";
            $params[] = '%' . $filters['fullname'] . '%';
            $types .= 's';
        }

        if (isset($filters['mda_code'])) {
            $query .= " AND mda_code = ?";
            $params[] = $filters['mda_code'];
            $types .= 's';
        }

        if (isset($filters['email'])) {
            $query .= " AND email = ?";
            $params[] = $filters['email'];
            $types .= 's';
        }

        if (isset($filters['allow_payment'])) {
            $query .= " AND allow_payment = ?";
            $params[] = $filters['allow_payment'];
            $types .= 'i';
        }

        if (isset($filters['status'])) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
            $types .= 'i';
        }

        // Prepare and bind the query
        $stmt = $this->conn->prepare($query);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params); // Spread operator for dynamic params
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $mdas = $result->fetch_all(MYSQLI_ASSOC);

        if (count($mdas) > 0) {
            // Return matching MDA(s)
            echo json_encode(['status' => 'success', 'data' => $mdas]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No matching MDA found']);
            http_response_code(404); // Not found
        }

        $stmt->close();
    }
}
