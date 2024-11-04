<?php
// Enable error reporting (for development)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON and CORS headers
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: https://plymouthstate-csit-senior-projects.usnh.domains");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true"); // Allow credentials (cookies)

// Start the session with secure settings
session_set_cookie_params([
    'lifetime' => 3600,  // 1-hour session duration
    'path' => '/',
    'domain' => 'plymouthstate-csit-senior-projects.usnh.domains', // Ensure it matches your frontend
    'secure' => true,  // Use HTTPS only
    'httponly' => true,  // Prevent JavaScript access to cookies
    'samesite' => 'None'  // Allow cross-origin cookies
]);
session_start(); // Start the session

include 'db_connection.php'; // Include your DB connection logic
$conn = getDbConnection('admin');

// Handle only POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    // Prepare the SQL statement to fetch the user by email
    $stmt = $conn->prepare("SELECT id, password, superadmin FROM admin_db WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Regenerate the session ID for security
            session_regenerate_id(true);

            // Store user data in the session
            $_SESSION['auth'] = [
                'email' => $email,
                'isAdmin' => (bool) $user['superadmin'],
                'logged_in' => true
            ];

            // Send a success response with admin status
            echo json_encode([
                "success" => true,
                "isAdmin" => $_SESSION['auth']['isAdmin']
            ]);
        } else {
            // Handle invalid password
            http_response_code(401); // Unauthorized
            echo json_encode(["error" => "Invalid email or password"]);
        }
    } else {
        // Handle user not found
        http_response_code(401); // Unauthorized
        echo json_encode(["error" => "Invalid email or password"]);
    }

    $stmt->close(); // Close the statement
} else {
    // Handle unsupported request methods
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Unsupported request method"]);
}

$conn->close(); // Close the database connection
?>
