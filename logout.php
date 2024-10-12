<?php
// Start or resume the session
session_start();

// Perform logout actions
// For example, you might want to unset session variables and destroy the session

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie if it exists
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 86400, '/');
}

// Destroy the session
session_destroy();

// Redirect to a login page or another appropriate page after logout
header('Location: login.php');
exit;
?>
