<?php
require_once 'config/database.php';
require_once 'helpers/user_helper.php';  // Include the universal duplicate check helper
require_once 'helpers/auth_helper.php';  // For JWT authentication


class RegistrationController {
    private $conn;

    // Constructor to initialize database connection
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Register a new administrative user and their permissions.
     */
    public function registerAdminUser($data) {
        // Validate required fields
        if (!isset($data['fullname'], $data['email'], $data['phone'], $data['password'], $data['role'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields: fullname, email, phone, password, role']);
            http_response_code(400); // Bad request
            return;
        }

        // Check if the email or phone already exists in the 'administrative_users' table
        if (isDuplicateUser($this->conn, 'administrative_users', $data['email'], $data['phone'])) {
            echo json_encode(['status' => 'error', 'message' => 'User with this email or phone number already exists']);
            http_response_code(409); // Conflict
            return;
        }

        // Hash the password
        $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);

        // Prepare SQL query to insert admin user
        $query = "INSERT INTO administrative_users 
            (fullname, email, phone, password, role, img, verification_status, date_created, date_updated)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        // Prepare the statement
        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bind_param(
            'ssssssi',
            $data['fullname'],
            $data['email'],
            $data['phone'],
            $hashed_password,
            $data['role'],
            $data['img'], // Optional field
            $data['verification_status'] // Default to not verified
        );

        // Execute the query to insert the admin user
        if ($stmt->execute()) {
            $admin_id = $stmt->insert_id; // Get the inserted user ID
            
            // Insert permissions for the admin user
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $this->insertAdminPermissions($admin_id, $data['permissions']);
            }

            echo json_encode(['status' => 'success', 'message' => 'User registered successfully', 'admin_id' => $admin_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error registering user: ' . $stmt->error]);
        }

        // Close the statement
        $stmt->close();
    }

    /**
     * Insert permissions for the admin user.
     */
    private function insertAdminPermissions($admin_id, $permissions) {
        // Prepare the SQL query to insert permissions
        $query = "INSERT INTO admin_permissions (admin_id, permission_id, date_created) VALUES (?, ?, NOW())";

        // Prepare the statement
        $stmt = $this->conn->prepare($query);

        foreach ($permissions as $permission_id) {
            // Bind the parameters for each permission
            $stmt->bind_param('ii', $admin_id, $permission_id);
            
            // Execute the query
            if (!$stmt->execute()) {
                echo json_encode(['status' => 'error', 'message' => 'Error inserting permission: ' . $stmt->error]);
                $stmt->close();
                return;
            }
        }

        // Close the statement
        $stmt->close();
    }

    /**
     * Register a new MDA user and assign permissions.
     */
    public function registerMDAUser($data) {
        // Validate required fields
        if (!isset($data['mda_id'], $data['name'], $data['email'], $data['phone'], $data['password'], $data['office_name'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields: mda_id, name, email, phone_number, password, office_name']);
            http_response_code(400); // Bad request
            return;
        }

        // Check if the email or phone already exists in the 'mda_users' table
        if (isDuplicateUser($this->conn, 'mda_users', $data['email'], $data['phone'])) {
            echo json_encode(['status' => 'error', 'message' => 'MDA user with this email or phone number already exists']);
            http_response_code(409); // Conflict
            return;
        }

        // Hash the password
        $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);

        // Prepare SQL query to insert MDA user
        $query = "INSERT INTO mda_users (mda_id, name, email, phone, password, created_at, img, office_name) 
                  VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)";

        // Prepare the statement
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'issssss',
            $data['mda_id'],
            $data['name'],
            $data['email'],
            $data['phone'],
            $hashed_password,
            $data['img'],  // Optional image
            $data['office_name']
        );

        // Execute the query
        if ($stmt->execute()) {
            $mda_user_id = $stmt->insert_id;

            // Insert permissions for the MDA user
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $this->insertMDAPermissions($mda_user_id, $data['permissions']);
            }

            echo json_encode(['status' => 'success', 'message' => 'MDA User registered successfully', 'mda_user_id' => $mda_user_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error registering MDA user: ' . $stmt->error]);
        }

        $stmt->close();
    }

    /**
     * Insert permissions for the MDA user.
     */
    private function insertMDAPermissions($mda_user_id, $permissions) {
        $query = "INSERT INTO mda_admin_permissions (mda_admin_id, permission_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);

        foreach ($permissions as $permission_id) {
            $stmt->bind_param('ii', $mda_user_id, $permission_id);
            if (!$stmt->execute()) {
                echo json_encode(['status' => 'error', 'message' => 'Error inserting MDA permission: ' . $stmt->error]);
                $stmt->close();
                return;
            }
        }
        $stmt->close();
    }

    public function registerEnumeratorUser($data) {
        // Validate required fields
        if (!isset($data['agent_id'], $data['fullname'], $data['email'], $data['phone'], $data['password'], $data['state'], $data['lga'], $data['address'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields: agent_id, fullname, email, phone, password, state, lga, address']);
            http_response_code(400); // Bad request
            return;
        }

        // Check if the email or phone already exists in the 'enumerator_users' table
        if (isDuplicateUser($this->conn, 'enumerator_users', $data['email'], $data['phone'])) {
            echo json_encode(['status' => 'error', 'message' => 'Enumerator user with this email or phone number already exists']);
            http_response_code(409); // Conflict
            return;
        }

        // Hash the password
        $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);

        // Prepare SQL query to insert enumerator user
        $query = "INSERT INTO enumerator_users (agent_id, fullname, password, email, address, state, lga, phone, img, timeIn, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

        // Prepare the statement
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'sssssssssi', 
            $data['agent_id'], 
            $data['fullname'], 
            $hashed_password, 
            $data['email'], 
            $data['address'], 
            $data['state'], 
            $data['lga'], 
            $data['phone'], 
            $data['img'],  // Optional image
            $data['status']    // Default status to active (1)
        );

        // Execute the query
        if ($stmt->execute()) {
            $enumerator_user_id = $stmt->insert_id;
            echo json_encode(['status' => 'success', 'message' => 'Enumerator User registered successfully', 'enumerator_user_id' => $enumerator_user_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error registering enumerator user: ' . $stmt->error]);
        }

        $stmt->close();
    }

     /**
     * Register a new special user.
     */
    public function registerSpecialUser($data) {
        // Validate required fields
        if (!isset($data['name'], $data['email'], $data['phone'], $data['password'], $data['state'], $data['lga'], $data['address'], $data['industry'], $data['official_TIN'], $data['category'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields: name, email, phone, password, state, lga, address, industry, official_TIN, category']);
            http_response_code(400); // Bad request
            return;
        }

        // Check if the email or phone already exists in the 'special_users_' table
        if (isDuplicateUser($this->conn, 'special_users_', $data['email'], $data['phone'])) {
            echo json_encode(['status' => 'error', 'message' => 'Special user with this email or phone number already exists']);
            http_response_code(409); // Conflict
            return;
        }

        // Generate a unique 10-digit payer_id
        $payer_id = $this->generateUniquePayerId();

        // Hash the password
        $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);

        // Prepare SQL query to insert special user
        $query = "INSERT INTO special_users_ (payer_id, name, industry, staff_quota, official_TIN, email, phone, password, state, lga, address, category, timeIn) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        // Prepare the statement
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'ississssssss',  // 11 variables
            $payer_id,      // Generated payer_id
            $data['name'],
            $data['industry'],
            $data['staff_quota'],  // Optional staff_quota
            $data['official_TIN'],
            $data['email'],
            $data['phone'],
            $hashed_password,
            $data['state'],
            $data['lga'],
            $data['address'],
            $data['category']
        );

        // Execute the query
        if ($stmt->execute()) {
            $special_user_id = $stmt->insert_id;
            echo json_encode(['status' => 'success', 'message' => 'Special User registered successfully', 'special_user_id' => $special_user_id, 'payer_id' => $payer_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error registering special user: ' . $stmt->error]);
        }

        $stmt->close();
    }

    /**
     * Generate a unique 10-digit payer_id.
     */
    private function generateUniquePayerId() {
        do {
            // Generate a random 10-digit number
            $payer_id = random_int(1000000000, 9999999999);  // 10-digit random number

            // Check if the payer_id is already in use
            $query = "SELECT id FROM special_users_ WHERE payer_id = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $payer_id);
            $stmt->execute();
            $stmt->store_result();

            // If no rows are returned, the payer_id is unique
        } while ($stmt->num_rows > 0);

        return $payer_id;
    }

     /**
     * Register a new special user employee and their salary/benefits.
     */
    public function registerEmployeeWithSalary($data) {
        // Validate required fields for employee
        if (!isset($data['fullname'], $data['email'], $data['phone'], $data['payer_id'], $data['associated_special_user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields: fullname, email, phone, payer_id, associated_special_user_id']);
            http_response_code(400); // Bad request
            return;
        }

        // Validate required fields for salary and benefits
        if (!isset($data['basic_salary'], $data['housing'], $data['transport'], $data['utility'], $data['medical'], $data['entertainment'], $data['leaves'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields: basic_salary, housing, transport, utility, medical, entertainment, leaves']);
            http_response_code(400); // Bad request
            return;
        }

        // Check if the email or phone already exists in the 'special_user_employees' table
        if (isDuplicateUser($this->conn, 'special_user_employees', $data['email'], $data['phone'])) {
            echo json_encode(['status' => 'error', 'message' => 'Employee with this email or phone number already exists']);
            http_response_code(409); // Conflict
            return;
        }

        // Start transaction
        $this->conn->begin_transaction();

        try {
            // Insert employee into 'special_user_employees' table
            $query = "INSERT INTO special_user_employees (fullname, email, phone, payer_id, associated_special_user_id, created_date) 
                      VALUES (?, ?, ?, ?, ?, NOW())";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                'ssssi',
                $data['fullname'],
                $data['email'],
                $data['phone'],
                $data['payer_id'],
                $data['associated_special_user_id']
            );

            if (!$stmt->execute()) {
                throw new Exception('Error registering employee: ' . $stmt->error);
            }

            // Get the employee ID for the newly registered employee
            $employee_id = $stmt->insert_id;

            // Calculate annual gross income
            $annual_gross_income = $data['basic_salary'] + $data['housing'] + $data['transport'] + $data['utility'] + $data['medical'] + $data['entertainment'];

            // Calculate monthly tax payable based on the provided annual gross income
            $monthly_tax_payable = $this->calculateMonthlyTaxPayable($annual_gross_income);

            // Insert salary and benefits into 'employee_salary_and_benefits' table
            $query = "INSERT INTO employee_salary_and_benefits (employee_id, basic_salary, date_employed, housing, transport, utility, medical, entertainment, leaves, annual_gross_income, new_gross, monthly_tax_payable, created_date) 
                      VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                'idddddddddd',
                $employee_id,
                $data['basic_salary'],
                $data['housing'],
                $data['transport'],
                $data['utility'],
                $data['medical'],
                $data['entertainment'],
                $data['leaves'],
                $annual_gross_income,
                $annual_gross_income,  // New gross income (before deductions)
                $monthly_tax_payable
            );

            if (!$stmt->execute()) {
                throw new Exception('Error registering salary and benefits: ' . $stmt->error);
            }

            // Commit transaction
            $this->conn->commit();

            // Return success response
            echo json_encode(['status' => 'success', 'message' => 'Employee and salary registered successfully', 'employee_id' => $employee_id]);

        } catch (Exception $e) {
            // Rollback transaction in case of an error
            $this->conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        } finally {
            $stmt->close();
        }
    }

    /**
     * Calculate the monthly tax payable based on the annual gross income.
     */
    private function calculateMonthlyTaxPayable($annual_gross_income) {
        // Deduction rates
        $nhf_rate = 0.025;   // National Housing Fund (2.5%)
        $pension_rate = 0.08;  // Pension (8%)
        $nhis_rate = 0.05;   // National Health Insurance Scheme (5%)
        $life_insurance_rate = 0.00;  // Life Insurance (currently 0%)
        $gratuities_rate = 0.00;  // Gratuities (currently 0%)

        // Deductions
        $nhf_deduction = $annual_gross_income * $nhf_rate;
        $pension_deduction = $annual_gross_income * $pension_rate;
        $nhis_deduction = $annual_gross_income * $nhis_rate;
        $total_deductions = $nhf_deduction + $pension_deduction + $nhis_deduction;

        // New gross income
        $new_gross_income = $annual_gross_income - $total_deductions;

        // Consolidated relief allowance (20% of new gross income)
        $consolidated_relief_allowance = $new_gross_income * 0.20;
        $additional_relief = ($new_gross_income <= 200000) ? 0 : 0;
        $total_allowance = $consolidated_relief_allowance + $additional_relief;

        // Chargeable income (annual gross income minus allowances)
        $chargeable_income = $annual_gross_income - $total_allowance;

        // Progressive tax bands
        $first_band = min(300000, $chargeable_income) * 0.07;
        $second_band = max(0, min(300000, $chargeable_income - 300000)) * 0.11;
        $third_band = max(0, min(500000, $chargeable_income - 600000)) * 0.15;
        $fourth_band = max(0, min(500000, $chargeable_income - 1100000)) * 0.19;
        $fifth_band = max(0, min(1600000, $chargeable_income - 1600000)) * 0.21;
        $sixth_band = max(0, $chargeable_income - 3200000) * 0.24;

        // Total annual tax
        $annual_tax_due = $first_band + $second_band + $third_band + $fourth_band + $fifth_band + $sixth_band;

        // Monthly tax payable
        $monthly_tax_payable = $annual_tax_due / 12;

        return $monthly_tax_payable;
    }

    /**
     * Register a new taxpayer (individual or corporate) and associated information.
     */
    public function registerTaxpayer($data) {
        // Validate required fields for taxpayer
        if (!isset($data['first_name'], $data['surname'], $data['created_by'], $data['email'], $data['phone'], $data['category'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields: first_name, surname, email, phone, category']);
            http_response_code(400); // Bad request
            return;
        }

        // Check if the email or phone already exists in the 'taxpayer' table
        if (isDuplicateUser($this->conn, 'taxpayer', $data['email'], $data['phone'])) {
            echo json_encode(['status' => 'error', 'message' => 'Taxpayer with this email or phone number already exists']);
            http_response_code(409); // Conflict
            return;
        }

        // Start transaction
        $this->conn->begin_transaction();

        try {
            // Generate unique tax number
            $tax_number = $this->generateUniqueTaxNumber();

            // Insert taxpayer into 'taxpayer' table
            $query = "INSERT INTO taxpayer (created_by, tax_number, category, first_name, surname, email, phone, state, lga, address, employment_status, number_of_staff, business_own, img, created_time, updated_time) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                'sssssssssssiss',
                $data['created_by'],
                $tax_number,
                $data['category'],
                $data['first_name'],
                $data['surname'],
                $data['email'],
                $data['phone'],
                $data['state'],
                $data['lga'],
                $data['address'],
                $data['employment_status'],
                $data['number_of_staff'],  // Optional field
                $data['business_own'],      // Optional field
                $data['img']               // Optional field (profile image)
            );

            if (!$stmt->execute()) {
                throw new Exception('Error registering taxpayer: ' . $stmt->error);
            }

            // Get the taxpayer ID for the newly registered taxpayer
            $taxpayer_id = $stmt->insert_id;

            // Insert taxpayer business details (optional)
            if (isset($data['business_type'], $data['annual_revenue'], $data['value_business'])) {
                $this->registerTaxpayerBusiness($taxpayer_id, $data);
            }

            // Insert taxpayer identification details (TIN, NIN, etc.)
            if (isset($data['tin'], $data['nin'], $data['bvn'], $data['id_type'], $data['id_number'])) {
                $this->registerTaxpayerIdentification($taxpayer_id, $data);
            }

            // Insert taxpayer representative details (optional)
            if (isset($data['rep_firstname'], $data['rep_surname'], $data['rep_email'], $data['rep_phone'])) {
                $this->registerTaxpayerRepresentative($taxpayer_id, $data);
            }

            // Insert taxpayer security details (password, verification)
            $verification_code = $this->registerTaxpayerSecurity($taxpayer_id, $data);

            // Commit transaction
            $this->conn->commit();

            // Return success response with the verification code
            echo json_encode([
                'status' => 'success',
                'message' => 'Taxpayer registered successfully',
                'taxpayer_id' => $taxpayer_id,
                'tax_number' => $tax_number,
                'verification_code' => $verification_code
            ]);

        } catch (Exception $e) {
            // Rollback transaction in case of an error
            $this->conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        } finally {
            $stmt->close();
        }
    }

    // Helper function to generate a unique tax number
    private function generateUniqueTaxNumber() {
        do {
            // Generate a random 10-digit tax number
            $tax_number = random_int(1000000000, 9999999999);

            // Check if the tax_number is already in use
            $query = "SELECT id FROM taxpayer WHERE tax_number = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $tax_number);
            $stmt->execute();
            $stmt->store_result();

        } while ($stmt->num_rows > 0);

        return $tax_number;
    }

    // Helper function to insert business details
    private function registerTaxpayerBusiness($taxpayer_id, $data) {
        $query = "INSERT INTO taxpayer_business (taxpayer_id, business_type, annual_revenue, value_business) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'isss',
            $taxpayer_id,
            $data['business_type'],
            $data['annual_revenue'],
            $data['value_business']
        );

        if (!$stmt->execute()) {
            throw new Exception('Error registering taxpayer business details: ' . $stmt->error);
        }
    }

    // Helper function to insert identification details
    private function registerTaxpayerIdentification($taxpayer_id, $data) {
        $query = "INSERT INTO taxpayer_identification (taxpayer_id, tin, nin, bvn, id_type, id_number) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'isssss',
            $taxpayer_id,
            $data['tin'],
            $data['nin'],
            $data['bvn'],
            $data['id_type'],
            $data['id_number']
        );

        if (!$stmt->execute()) {
            throw new Exception('Error registering taxpayer identification details: ' . $stmt->error);
        }
    }

    // Helper function to insert representative details
    private function registerTaxpayerRepresentative($taxpayer_id, $data) {
        $query = "INSERT INTO taxpayer_representative (taxpayer_id, rep_firstname, rep_surname, rep_email, rep_phone, rep_position, rep_state, rep_lga, rep_address) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'issssssss',
            $taxpayer_id,
            $data['rep_firstname'],
            $data['rep_surname'],
            $data['rep_email'],
            $data['rep_phone'],
            $data['rep_position'],  // Optional field
            $data['rep_state'],
            $data['rep_lga'],
            $data['rep_address']
        );

        if (!$stmt->execute()) {
            throw new Exception('Error registering taxpayer representative details: ' . $stmt->error);
        }
    }

    // Helper function to insert security details and generate verification code
    private function registerTaxpayerSecurity($taxpayer_id, $data) {
        // Hash the password
        $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);

        // Generate a 6-digit alphanumeric verification code
        $verification_code = strtoupper(bin2hex(random_bytes(3)));  // 6-character alphanumeric code

        $query = "INSERT INTO taxpayer_security (taxpayer_id, password, verification_status, verification_code, tin_status, new_tin) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'isissi',
            $taxpayer_id,
            $hashed_password,
            $data['verification_status'],  // Default verification status to 0 (unverified)
            $verification_code,
            $data['tin_status'],           // Default TIN status to 0 (unverified)
            $data['new_tin']            // Optional field
        );

        if (!$stmt->execute()) {
            throw new Exception('Error registering taxpayer security details: ' . $stmt->error);
        }

        // Return the generated verification code
        return $verification_code;
    }

    /**
     * Register a new enumerator tax payer, corporate info (if corporate), and their properties.
     */
    public function registerEnumeratorTaxPayer($data) {
        // Get the authenticated user ID (enumerator) from the JWT token
        $auth_user = authenticate();
        $enumerator_user_id = $auth_user['user_id'];
        $user_type = $auth_user['user_type'];

        // Ensure that only enumerators can register tax payers
        if ($user_type !== 'enumerator') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only enumerators can register tax payers']);
            http_response_code(403); // Forbidden
            return;
        }

        // Validate required fields for enumerator tax payer
        if (!isset($data['first_name'], $data['last_name'], $data['email'], $data['phone'], $data['password'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields: first_name, last_name, email, phone, password']);
            http_response_code(400); // Bad request
            return;
        }

        // Check if the email or phone already exists in the 'enumerator_tax_payers' table
        if (isDuplicateUser($this->conn, 'enumerator_tax_payers', $data['email'], $data['phone'])) {
            echo json_encode(['status' => 'error', 'message' => 'Tax payer with this email or phone number already exists']);
            http_response_code(409); // Conflict
            return;
        }

        // Start transaction
        $this->conn->begin_transaction();

        try {
            // Generate unique tax number
            $tax_number = $this->generateUniqueEnumTaxNumber();

            // Generate a 6-digit alphanumeric verification code
            $verification_code = strtoupper(bin2hex(random_bytes(3)));

            // Hash the password
            $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);

            // Insert enumerator tax payer into 'enumerator_tax_payers' table
            $query = "INSERT INTO enumerator_tax_payers (tax_number, first_name, last_name, email, phone, password, tin, employment_status, id_type, id_number, business_status, business_type, position, state, lga, address, area, verification_date, account_status, tin_status, category, account_type, property_owner, revenue_return, valuation, created_by_enumerator_user, img, verification_code, staff_quota, timeIn) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                'sssssssssssssssssssssssiisssi',
                $tax_number,
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['phone'],
                $hashed_password,
                $data['tin'],  // Optional field
                $data['employment_status'],  // Optional field
                $data['id_type'],  // Optional field
                $data['id_number'],  // Optional field
                $data['business_status'],
                $data['business_type'],
                $data['position'],
                $data['state'],
                $data['lga'],
                $data['address'],
                $data['area'],
                $data['verification_date'],  // Optional field
                $data['account_status'],  // Default to active
                $data['tin_status'],  // Default to 0 (not verified)
                $data['category'],  // Optional field
                $data['account_type'],  // Optional field
                $data['property_owner'],  // Optional field
                $data['revenue_return'],  // Optional field
                $data['valuation'],  // Optional field
                $enumerator_user_id,  // Retrieved from token
                $data['img'],  // Optional field
                $verification_code,
                $data['staff_quota']  // Optional field
            );

            if (!$stmt->execute()) {
                throw new Exception('Error registering tax payer: ' . $stmt->error);
            }

            // Get the tax payer's tax number (used for corporate info and property association)
            $taxpayer_id = $stmt->insert_id;

            // Insert corporate information if the category is corporate
            if (isset($data['corporate_info']) && strtolower($data['category']) === 'corporate') {
                $this->registerCorporateInfo($tax_number, $data['corporate_info']);
            }

            // Insert property details (if provided)
            if (isset($data['properties']) && is_array($data['properties'])) {
                $this->registerPropertyInfo($tax_number, $data['properties']);
            }

            // Commit transaction
            $this->conn->commit();

            // Return success response with the verification code
            echo json_encode([
                'status' => 'success',
                'message' => 'Tax payer registered successfully',
                'taxpayer_id' => $taxpayer_id,
                'tax_number' => $tax_number,
                'verification_code' => $verification_code
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
     * Helper function to register corporate information for corporate tax payers.
     */
    private function registerCorporateInfo($tax_number, $corporate_info) {
        // Ensure required fields for corporate info are present
        if (!isset($corporate_info['name'], $corporate_info['industry'], $corporate_info['staff_quota'], $corporate_info['email'], $corporate_info['business_type'], $corporate_info['revenue_return'], $corporate_info['valuation'])) {
            throw new Exception('Missing required corporate fields: name, industry, staff_quota, email, business_type, revenue_return, valuation');
        }
        $cat = 'corporate';
        $query = "INSERT INTO enumerator_corporate_info (user_tax_number, category, name, industry, staff_quota, tin, email, state, lga, address, area, tax_category, business_type, revenue_return, valuation, img, time_in) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'ssssssssssssssss',
            $tax_number,
            $cat,  // Category is always 'corporate'
            $corporate_info['name'],
            $corporate_info['industry'],
            $corporate_info['staff_quota'],
            $corporate_info['tin'],  // Optional field
            $corporate_info['email'],
            $corporate_info['state'],
            $corporate_info['lga'],
            $corporate_info['address'],
            $corporate_info['area'],
            $corporate_info['tax_category'],  // Optional field
            $corporate_info['business_type'],
            $corporate_info['revenue_return'],
            $corporate_info['valuation'],
            $corporate_info['img']  // Optional image field
        );

        if (!$stmt->execute()) {
            throw new Exception('Error registering corporate info: ' . $stmt->error);
        }

        $stmt->close();
    }

    /**
     * Helper function to register properties associated with the enumerator tax payer.
     */
    private function registerPropertyInfo($tax_number, $properties) {
        $query = "INSERT INTO enumerator_property_info (user_tax_number, property_id, property_file, property_type, property_area, latitude, longitude, state, lga, address, area, tax_category, img, timeIn) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->conn->prepare($query);

        foreach ($properties as $property) {
            $stmt->bind_param(
                'sssssssssssss',
                $tax_number,
                $property['property_id'],  // Optional field
                $property['property_file'],  // Optional field (file path)
                $property['property_type'],
                $property['property_area'],
                $property['latitude'],  // Optional field
                $property['longitude'],  // Optional field
                $property['state'],
                $property['lga'],
                $property['address'],
                $property['area'],
                $property['tax_category'],  // Optional field
                $property['img']  // Optional image field
            );

            if (!$stmt->execute()) {
                throw new Exception('Error registering property: ' . $stmt->error);
            }
        }

        $stmt->close();
    }

    /**
     * Helper function to generate a unique tax number.
     */
    private function generateUniqueEnumTaxNumber() {
        do {
            // Generate a random 10-digit tax number
            $tax_number = random_int(1000000000, 9999999999);

            // Check if the tax_number is already in use
            $query = "SELECT id FROM enumerator_tax_payers WHERE tax_number = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $tax_number);
            $stmt->execute();
            $stmt->store_result();

        } while ($stmt->num_rows > 0);

        return $tax_number;
    }
}
