<?php
header('Content-Type: application/json');

$db_user = 'Students';
$db_pass = 'ADBMS';
$connection_string = 'localhost/xe';

$response = ['success' => false, 'message' => 'An unknown server error occurred.'];

$conn = null;
$stmt = null;

try {
    $json_data = file_get_contents('php://input');

    $data = json_decode($json_data, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data received.');
    }

    $senderUsername = $data['senderUsername'] ?? null;
    $receiverUsername = $data['receiverUsername'] ?? null;
    $amount = $data['amount'] ?? null;

    if (empty($senderUsername) || empty($receiverUsername) || !is_numeric($amount) || $amount <= 0) {
        throw new Exception('Invalid transfer details provided. Please ensure all fields are correctly filled and amount is positive.');
    }

    $conn = @oci_connect($db_user, $db_pass, $connection_string);

    if (!$conn) {
        $e = oci_error();
        throw new Exception("Could not connect to database: " . ($e['message'] ?? 'Unknown connection error.'));
    }

    // Ensure the database user ('Students' in this case) has EXECUTE privileges
    // on the SP_TRANSFER_MONEY procedure.
    // Example SQL to run in your database (as a DBA or the user who created the procedure):
    // GRANT EXECUTE ON SP_TRANSFER_MONEY TO Students;
    $stmt = oci_parse($conn, 'BEGIN SP_TRANSFER_MONEY(:sender, :receiver, :amount_in, :msg, :flag); END;');

    oci_bind_by_name($stmt, ':sender', $senderUsername, 50);
    oci_bind_by_name($stmt, ':receiver', $receiverUsername, 50);
    oci_bind_by_name($stmt, ':amount_in', $amount);

    $p_status_message = null;
    $p_success_flag_int = null;
    oci_bind_by_name($stmt, ':msg', $p_status_message, 255);
    oci_bind_by_name($stmt, ':flag', $p_success_flag_int, 1);

    $execute_result = oci_execute($stmt);

    if (!$execute_result) {
        $e = oci_error($stmt);
        throw new Exception("Error executing transfer procedure: " . ($e['message'] ?? 'Unknown execution error.'));
    }

    $response['success'] = ($p_success_flag_int == 1);
    $response['message'] = $p_status_message;

} catch (Exception $e) {
    $response['message'] = 'Transfer process error: ' . $e->getMessage();
} finally {
    if (isset($stmt) && is_resource($stmt)) {
        oci_free_statement($stmt);
    }
    if (isset($conn) && is_resource($conn)) {
        oci_close($conn);
    }
}

echo json_encode($response);
?>
