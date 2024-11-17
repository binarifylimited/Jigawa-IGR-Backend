<?php
// Check for duplicate MDA by fullname or mda_code
function isDuplicateMda($conn, $fullname, $mda_code) {
    $query = "SELECT id FROM mda WHERE fullname = ? OR mda_code = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $fullname, $mda_code);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}
