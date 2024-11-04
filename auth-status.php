<?php
// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON and CORS headers
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: https://plymouthstate-csit-senior-projects.usnh.domains");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Adjust session settings
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'domain' => '.plymouthstate-csit-senior-projects.usnh.domains', // Note the dot prefix for domain-wide session
    'secure' => true,
    'httponly' => true,
    'samesite' => 'None'
]);
session_start();

// Debugging: Log cookies to ensure session ID is being sent
error_log("Cookies: " . print_r($_COOKIE, true));

// Handle OPTIONS request for CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
$servername = "localhost";
$dbname = "plymout2_Projects";
$username = "plymout2_user";
$password = "Rot02cla";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo json_encode(["error" => "Database connection failed."]);
    exit();
}

// Check login status and superadmin status
if (isset($_SESSION['auth']) && $_SESSION['auth']['logged_in'] === true) {
    $userEmail = $_SESSION['auth']['email'] ?? '';

    if (!empty($userEmail)) {
        $sql = "SELECT superadmin FROM admin_db WHERE email = ?";
        
        // Prepare and execute the statement
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $userEmail);
            $stmt->execute();
            $stmt->bind_result($superadmin);
            $stmt->fetch();
            $stmt->close();

            // Set session variables based on the query
            $_SESSION['auth']['isAdmin'] = true; // Assuming all logged-in users are admins
            $_SESSION['auth']['isSuperAdmin'] = (bool)$superadmin;

            // Debugging: Log the session and database results
            error_log("User email: " . $userEmail);
            error_log("Database superadmin value: " . (int)$superadmin);
            error_log("Session data after update: " . print_r($_SESSION, true));
            
            // Return JSON response with role details
            echo json_encode([
                "isLoggedIn" => true,
                "isAdmin" => true,
                "isSuperAdmin" => (bool)$superadmin
            ]);
        } else {
            error_log("Failed to prepare SQL statement: " . $conn->error);
            echo json_encode(["error" => "Failed to retrieve user status."]);
            exit();
        }
    } else {
        error_log("User email not set in session.");
        echo json_encode([
            "isLoggedIn" => false,
            "isAdmin" => false,
            "isSuperAdmin" => false
        ]);
    }
} else {
    error_log("User not logged in or session data missing.");
    echo json_encode([
        "isLoggedIn" => false,
        "isAdmin" => false,
        "isSuperAdmin" => false
    ]);
}

$conn->close();
?>
