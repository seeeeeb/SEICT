<?php
// Initialize active session framework
session_start();

// 1. Unset and clear all memory session global references
$_SESSION = array();

// 2. Clear out the physical session cookie tracking metrics inside the user's browser cache
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 3. Destroy the actual background server identity tokens completely
session_destroy();

// 4. Send the user safely back out to the main portal selection index dashboard
header("Location: index.php");
exit;
?>