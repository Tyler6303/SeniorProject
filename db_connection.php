<?php
// Enable error reporting (for development only)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON and CORS headers
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Database credentials
$servername = "localhost";
$dbname = "plymout2_Projects";
$password = "Rot02cla"; // Shared password for both roles

/**
 * Create a database connection based on role.
 * @param string $role 'admin' or 'user'
 * @return mysqli Database connection object
 */
function getDbConnection($role = 'user') {
    global $servername, $dbname, $password;

    // Determine the username based on the role
    $username = ($role === 'admin') ? 'plymout2_admin' : 'plymout2_user';

    // Initialize the MySQL connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check for connection errors
    if ($conn->connect_error) {
        error_log("DB Connection ($role): Failed - " . $conn->connect_error);
        echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
        http_response_code(500); // Internal Server Error
        exit;
    }

    // Log a successful connection
    error_log("DB Connection ($role): Successful");

    return $conn;
}
?>
