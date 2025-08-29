<?php
session_start();

$username_db = 'Students';
$password_db = 'ADBMS';
$connection_string = 'localhost/xe';

$conn = null;
$stid_insert = null;
$stid_register_user_balance = null;

try {
    $conn = oci_connect($username_db, $password_db, $connection_string);
    if (!$conn) {
        $e = oci_error();
        throw new Exception("Oracle Database Connection Failed: " . htmlentities($e['message'], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $firstName = htmlspecialchars($_POST['firstName'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $lastName = htmlspecialchars($_POST['lastName'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $userName = htmlspecialchars($_POST['userName'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $email = htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        $gender = htmlspecialchars($_POST['gender'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $age = filter_var($_POST['age'] ?? '', FILTER_VALIDATE_INT);
        $phone = htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $address = htmlspecialchars($_POST['address'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $errors = [];
        if (empty($firstName)) { $errors[] = "First Name is required."; }
        if (empty($lastName)) { $errors[] = "Last Name is required."; }
        if (empty($userName)) { $errors[] = "User Name is required."; }
        if (empty($email)) { $errors[] = "Email is required."; }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Invalid email format."; }
        if (empty($password)) { $errors[] = "Password is required."; }
        if ($password !== $confirmPassword) { $errors[] = "Passwords do not match."; }
        if ($age === false && !empty($_POST['age'])) { $errors[] = "Age must be a valid number."; }

        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $plsql_procedure = "BEGIN Insert_User_Registered(:p_firstName, :p_lastName, :p_userName, :p_email, :p_hashedPassword, :p_gender, :p_age, :p_phone, :p_address, :p_status_message); END;";

            $stid_insert = oci_parse($conn, $plsql_procedure);
            if (!$stid_insert) {
                $e = oci_error($conn);
                throw new Exception("Error preparing PL/SQL procedure ");
            }

            oci_bind_by_name($stid_insert, ':p_firstName', $firstName, -1, SQLT_CHR);
            oci_bind_by_name($stid_insert, ':p_lastName', $lastName, -1, SQLT_CHR);
            oci_bind_by_name($stid_insert, ':p_userName', $userName, -1, SQLT_CHR);
            oci_bind_by_name($stid_insert, ':p_email', $email, -1, SQLT_CHR);
            oci_bind_by_name($stid_insert, ':p_hashedPassword', $hashedPassword, -1, SQLT_CHR);
            oci_bind_by_name($stid_insert, ':p_gender', $gender, -1, SQLT_CHR);
            oci_bind_by_name($stid_insert, ':p_age', $age, -1, SQLT_INT);
            oci_bind_by_name($stid_insert, ':p_phone', $phone, -1, SQLT_CHR);
            oci_bind_by_name($stid_insert, ':p_address', $address, -1, SQLT_CHR);

            $statusMessage = null;
            oci_bind_by_name($stid_insert, ':p_status_message', $statusMessage, 4000, SQLT_CHR); 

            $r_call = oci_execute($stid_insert);

            if (!$r_call) {
                $e = oci_error($stid_insert);
                oci_rollback($conn);
                throw new Exception("Error executing PL/SQL procedure 'Insert_User_Registered': ");
            }

            if (strpos($statusMessage, 'Error:') === 0) {
                oci_rollback($conn);
                if (strpos($statusMessage, 'User with the provided unique details already exists') !== false) {
                    throw new Exception("Registration failed: " . $statusMessage, 1);
                } else {
                    throw new Exception("Registration failed: " . $statusMessage);
                }
            } else {
                $plsql_call_register_balance = "BEGIN Register_User(:p_userName, :p_balance_status_message); END;";
                $stid_register_user_balance = oci_parse($conn, $plsql_call_register_balance);

                if (!$stid_register_user_balance) {
                    $e = oci_error($conn);
                    oci_rollback($conn);
                    throw new Exception("Error preparing PL/SQL procedure 'Register_User' for balance: " . htmlentities($e['message'], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                }

                oci_bind_by_name($stid_register_user_balance, ':p_userName', $userName, -1, SQLT_CHR);
                $balanceStatusMessage = null;
                oci_bind_by_name($stid_register_user_balance, ':p_balance_status_message', $balanceStatusMessage, 4000, SQLT_CHR);

                $r_balance_call = oci_execute($stid_register_user_balance);

                if (!$r_balance_call) {
                    $e = oci_error($stid_register_user_balance);
                    oci_rollback($conn);
                    throw new Exception("Error executing PL/SQL procedure 'Register_User' for balance: " . htmlentities($e['message'], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                }

                if (strpos($balanceStatusMessage, 'Error:') === 0) {
                    oci_rollback($conn);
                    error_log("Failed to set default balance for user " . $userName . ": " . $balanceStatusMessage . " at " . date('Y-m-d H:i:s'));
                    throw new Exception("Registration completed, but failed to set default balance: " . $balanceStatusMessage);
                }
                oci_commit($conn);
              // echo "<h1 style='color:green'> Registration Successful!</h1>";
               header('Location: registrationSuccess.php');
                exit();
            }

        } else {
            http_response_code(400);
            echo "<h3 style='color: red;'>Registration Errors:</h3>";
            echo "<ul style='color: red;'>";
            foreach ($errors as $error) {
                echo "<li>" . $error . "</li>";
            }
            echo "</ul>";
        }

    } else {
        http_response_code(405);
        echo "<p style='color: red;'>Access denied. Please submit the form via POST.</p>";
    }

} catch (Exception $e) {
    error_log("Critical Registration Error: " . $e->getMessage() . " at " . date('Y-m-d H:i:s', time()));


} finally {
    if (isset($stid_insert) && is_resource($stid_insert)) {
        oci_free_statement($stid_insert);
    }
    if (isset($conn) && is_resource($conn)) {
        oci_close($conn);
    }
}
?>
