<?php
// This file will contain reusable functions for the application.
// We must include the database connection here as well, since functions might need it.
require_once 'db.php';

/**
 * Checks if a user is currently logged in.
 *
 * @return boolean True if the user is logged in, false otherwise.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirects to the login page if the user is not logged in.
 * This is used to protect pages that require authentication.
 */
function require_login() {
    if (!is_logged_in()) {
        // Store the requested URL in the session to redirect back after login
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
}

/**
 * Checks if the logged-in user is an administrator.
 *
 * @return boolean True if the user is an admin, false otherwise.
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Redirects if the user is not an admin.
 */
function require_admin() {
    if (!is_admin()) {
        // You can redirect to the home page or show an access denied message
        header('Location: ' . BASE_URL . '/index.php');
        exit();
    }
}

/**
 * Gets the number of items in the current user's cart.
 *
 * @param mysqli $conn The database connection object.
 * @return int The total number of items in the cart.
 */
function get_cart_item_count($conn) {
    if (!is_logged_in()) {
        return 0;
    }

    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT SUM(quantity) as total_items FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // If the cart is empty, fetch_assoc() might return NULL, so we default to 0.
    return $result['total_items'] ?? 0;
}

?>
