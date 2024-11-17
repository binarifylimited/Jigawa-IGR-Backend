<?php
/**
 * Check if a user already exists in a specific table with the given email or phone.
 * 
 * @param mysqli $conn       The database connection object
 * @param string $table      The table name to check (e.g., 'administrative_users', 'payer_user')
 * @param string $email      The email to check for duplicates
 * @param string $phone      The phone to check for duplicates
 * @return bool              True if a duplicate user is found, False otherwise
 */
function isDuplicateUser($conn, $table, $email, $phone) {
    // Prepare the SQL query to check for existing user by email or phone
    $query = "SELECT id FROM $table WHERE email = ? OR phone = ? LIMIT 1";
    
    // Prepare the statement
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $email, $phone);
    
    // Execute the query
    $stmt->execute();
    $stmt->store_result();
    
    // Check if any rows were returned (i.e., a duplicate user was found)
    if ($stmt->num_rows > 0) {
        return true;  // Duplicate user found
    }
    
    return false;  // No duplicate found
}