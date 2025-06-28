<?php
// We must start the session to be able to destroy it.
session_start();

// Unset all of the session variables.
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Redirect to the login page with a logged out message.
// We need to define BASE_URL manually here or include the config.
// Including config is safer.
require_once 'config/db.php';
header("Location: " . BASE_URL . "/login.php?logged_out=true");
exit();
?>
