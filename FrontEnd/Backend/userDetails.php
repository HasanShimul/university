<?php
header('Content-Type: application/json; charset=UTF-8');

// --- Database Configuration ---
$username_db = 'Students';
$password_db = 'ADBMS';
$connection_string = 'localhost/xe'; // Replace with your Oracle connection string

$conn = null;
$stid = null;

try {
    // Establish Oracle Connection
    $conn = oci_connect($username_db, $password_db, $connection_string);
    if (!$conn) {
        $e = oci_error();
        error_log("Oracle DB Connection Failed: " . $e['message'] . " at " . date('Y-m-d H:i:s'));
        echo json_encode(['success' => false, 'message' => 'Database connection failed. Please try again later.']);
        http_response_code(500); // Internal Server Error for DB connection
        exit();
    }

    // Prepare SQL to select all columns from userRegistered
    // Using SELECT * as requested, but we will filter the 'password' column from the output.
    $sql = "SELECT * FROM userRegistered ORDER BY userName";

    $stid = oci_parse($conn, $sql);
    if (!$stid) {
        $e = oci_error($conn);
        error_log("Error parsing SQL for select all users: " . $e['message'] . " at " . date('Y-m-d H:i:s'));
        echo json_encode(['success' => false, 'message' => 'Internal server error during query setup.']);
        http_response_code(500);
        exit();
    }

    // Execute the query
    $r = oci_execute($stid);
    if (!$r) {
        $e = oci_error($stid);
        error_log("Error executing SQL for select all users: " . $e['message'] . " at " . date('Y-m-d H:i:s'));
        echo json_encode(['success' => false, 'message' => 'Failed to retrieve user data.']);
        http_response_code(500);
        exit();
    }

    // Fetch all results into an array
    $users = [];
    while (($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) !== false) {
        // Convert keys to lowercase for consistency with JavaScript object properties
        // OCI8 typically returns uppercase keys by default.
        $lowercaseRow = array_change_key_case($row, CASE_LOWER);

        // Explicitly remove the 'password' column before sending to the client for security.
        unset($lowercaseRow['password']);

        $users[] = $lowercaseRow;
    }

    // Return success response with user data (without password)
    echo json_encode(['success' => true, 'data' => $users]);
    http_response_code(200);

} catch (Exception $e) {
    error_log("Unhandled Error in select_all_users.php: " . $e->getMessage() . " on line " . $e->getLine() . " at " . date('Y-m-d H:i:s'));
    echo json_encode(['success' => false, 'message' => 'An unexpected server error occurred.']);
    http_response_code(500);

} finally {
    // Clean up resources
    if (isset($stid) && is_resource($stid)) {
        oci_free_statement($stid);
    }
    if (isset($conn) && is_resource($conn)) {
        oci_close($conn);
    }
}
?>
