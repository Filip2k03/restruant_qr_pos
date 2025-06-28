<?php
// Turn on error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session on every page
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// -- DATABASE CONNECTION --
define('DB_HOST', 'localhost');
define('DB_USER', 'stephan'); // Your database username (default is root)
define('DB_PASS', 'superuser');     // Your database password (default is empty for XAMPP)
define('DB_NAME', 'restruant_db'); // The database name you created

// Create a new MySQLi object to connect to the database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check for connection errors
if ($conn->connect_error) {
    // If a connection error occurs, stop the script and display the error.
    die("Connection failed: " . $conn->connect_error);
}

// -- SITE CONFIGURATION --
define('SITE_NAME', 'The Cozy Corner Cafe');
// Use an absolute path for your site URL. 
// Change http://localhost/cozy-corner-cafe to your actual domain in production.
define('BASE_URL', 'http://localhost:8000/'); 
?>
