<?php

$username = 'Students';
$password = 'ADBMS';
$connection_string = 'localhost/xe';

$conn = null;
$stid = null;
$cursor = null;

try {
    $conn = oci_connect($username, $password, $connection_string);

    if (!$conn) {
        $e = oci_error();
        throw new Exception("Oracle Database Connection Failed: " . htmlentities($e['message'], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    $plsql_call = 'BEGIN Get_All_Users_Registered(:p_users_cursor); END;';
    $stid = oci_parse($conn, $plsql_call);

    if (!$stid) {
        $e = oci_error($conn);
        throw new Exception("Error preparing PL/SQL procedure call: " . htmlentities($e['message'], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    $cursor = oci_new_cursor($conn);

    oci_bind_by_name($stid, ':p_users_cursor', $cursor, -1, OCI_B_CURSOR);

    $r = oci_execute($stid);
    if (!$r) {
        $e = oci_error($stid);
        throw new Exception("Error executing PL/SQL procedure: " );
    }

    $r_cursor = oci_execute($cursor);
    if (!$r_cursor) {
        $e = oci_error($cursor);
        throw new Exception("Error executing REF CURSOR: " );
    }

    $html_output = '<h2>Registered User Information</h2>';
    $html_output .= '<table border="1" style="width:100%; border-collapse: collapse;">';
    $html_output .= '<thead><tr>';

    $ncols = oci_num_fields($cursor);
    for ($i = 1; $i <= $ncols; ++$i) {
        $colname = oci_field_name($cursor, $i);
        $html_output .= '<th style="padding: 8px; text-align: left; background-color: #f2f2f2;">' . htmlentities($colname, ENT_QUOTES, 'UTF-8') . '</th>';
    }
    $html_output .= '</tr></thead>';
    $html_output .= '<tbody>';

    while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
        $html_output .= '<tr>';
        foreach ($row as $item) {
            $html_output .= '<td style="padding: 8px; border-top: 1px solid #ddd;">' . ($item !== null ? htmlentities($item, ENT_QUOTES, 'UTF-8') : '&nbsp;') . '</td>';
        }
        $html_output .= '</tr>';
    }
    $html_output .= '</tbody>';
    $html_output .= '</table>';

    oci_free_statement($stid);
    oci_free_statement($cursor);
    oci_close($conn);

    echo $html_output;

} catch (Exception $e) {
    http_response_code(500);
    echo '<p style="color: red; padding: 10px; border: 1px solid red; background-color: #ffe6e6;">Failed to retrieve user information: ' . $e->getMessage() . '</p>';

    if ($stid && is_resource($stid)) {
        oci_free_statement($stid);
    }
    if ($cursor && is_resource($cursor)) {
        oci_free_statement($cursor);
    }
    if ($conn && is_resource($conn)) {
        oci_close($conn);
    }
}

?>
