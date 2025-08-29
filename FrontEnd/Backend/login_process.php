<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

$username_db = 'Students';
$password_db = 'ADBMS';
$connection_string = 'localhost/xe';

$conn = null;
$stid = null;

try {
    $conn = oci_connect($username_db, $password_db, $connection_string);
    if (!$conn) {
        $e = oci_error();
        error_log("Oracle DB Connection Failed: " . $e['message'] . " at " . date('Y-m-d H:i:s'));
        echo json_encode(['success' => false, 'message' => 'Database connection failed. Please try again later.']);
        http_response_code(500); 
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['userName']) && isset($_POST['password'])) {
        $userName = htmlspecialchars($_POST['userName'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $inputPassword = $_POST['password']; 

        $plsql_call = "BEGIN Authenticate_User(
            :p_userName, :p_password_in, :p_login_status,
            :p_first_name,:p_last_name, :p_role, :p_hashed_password_out
        ); END;";

        $stid = oci_parse($conn, $plsql_call);

        if (!$stid) {
            $e = oci_error($conn);
            error_log("Error preparing PL/SQL login procedure: " . $e['message'] . " at " . date('Y-m-d H:i:s'));
            echo json_encode(['success' => false, 'message' => 'An internal error occurred during login setup.']);
            http_response_code(500);
            exit();
        }

     
        oci_bind_by_name($stid, ':p_userName', $userName, -1, SQLT_CHR);
        oci_bind_by_name($stid, ':p_password_in', $inputPassword, -1, SQLT_CHR);

        $loginStatus = null;
        $firstName = null;
        $lastName = null;
        $role = null;
        $hashedPasswordFromDB = null; 

        oci_bind_by_name($stid, ':p_login_status', $loginStatus, 100, SQLT_CHR);
        oci_bind_by_name($stid, ':p_first_name', $firstName, 255, SQLT_CHR);
        oci_bind_by_name($stid, ':p_last_name', $lastName, 255, SQLT_CHR);
        oci_bind_by_name($stid, ':p_role', $role, 50, SQLT_CHR);
        oci_bind_by_name($stid, ':p_hashed_password_out', $hashedPasswordFromDB, 255, SQLT_CHR);

        $r = oci_execute($stid);
        if (!$r) {
            $e = oci_error($stid);
            error_log("Error executing PL/SQL login procedure: " . $e['message'] . " at " . date('Y-m-d H:i:s'));
            echo json_encode(['success' => false, 'message' => 'An error occurred during authentication process.']);
            http_response_code(500); 
            exit();
        }

        if ($loginStatus === 'SUCCESS' && password_verify($inputPassword, $hashedPasswordFromDB)) {
            $_SESSION['userName'] = $userName; 
            $_SESSION['role'] = $role;  
            $_SESSION['firstName'] = $firstName;
            $_SESSION['lastName'] = $lastName;
             

            $responseUserDetails = [
                'firstname' => $firstName,
                'username' => $userName, 
                'role' => $role,
                 'lastName' => $lastName 
            ];

            echo json_encode([
                'success' => true,
                'message' => 'Login successful!',
                'role' => $role,
                'userDetails' => $responseUserDetails
            ]);
            http_response_code(200); 
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
            http_response_code(401); 
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method or missing credentials.']);
        http_response_code(400); 
    }

} catch (Exception $e) {
    error_log("Unhandled Login Error: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile() . " at " . date('Y-m-d H:i:s'));
    echo json_encode(['success' => false, 'message' => 'An unexpected server error occurred: ' . $e->getMessage()]);
    http_response_code(500); 

} finally {
    if (isset($stid) && is_resource($stid)) {
        oci_free_statement($stid);
    }
    if (isset($conn) && is_resource($conn)) {
        oci_close($conn);
    }
}
?>
