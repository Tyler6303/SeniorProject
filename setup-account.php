<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON and CORS headers
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database connection file
include 'db_connection.php';
$conn = getDbConnection('admin');

// Retrieve POST data
$data = json_decode(file_get_contents('php://input'), true);
$full_name = $data['full_name'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$superadmin = $data['superadmin'] ?? 0;
$token = $data['token'] ?? '';

if (empty($full_name) || empty($email) || empty($password) || empty($token)) {
    echo json_encode(["error" => "All fields are required."]);
    http_response_code(400);
    exit();
}

// Token validation
if (strlen($token) !== 32) {
    echo json_encode(["error" => "Invalid or expired token."]);
    http_response_code(403);
    exit();
}

// Check for existing email
$check_stmt = $conn->prepare("SELECT id FROM admin_db WHERE email = ?");
if (!$check_stmt) {
    error_log("Prepare statement for email check failed: " . $conn->error);
    echo json_encode(["error" => "Error checking account existence."]);
    http_response_code(500);
    exit();
}
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    echo json_encode(["error" => "An account with this email already exists."]);
    $check_stmt->close();
    http_response_code(400); // Bad Request
    exit();
}
$check_stmt->close();

// Hash the password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Insert the new admin into the database
$insert_stmt = $conn->prepare("INSERT INTO admin_db (full_name, email, password, superadmin, created_at) VALUES (?, ?, ?, ?, NOW())");
if (!$insert_stmt) {
    error_log("Insert statement preparation failed: " . $conn->error);
    echo json_encode(["error" => "Error creating account. Please try again later."]);
    http_response_code(500);
    exit();
}
$insert_stmt->bind_param("sssi", $full_name, $email, $hashed_password, $superadmin);

if ($insert_stmt->execute()) {
    echo json_encode(["success" => "Account setup successful!"]);
} else {
    error_log("Error executing insert statement: " . $insert_stmt->error);
    echo json_encode(["error" => "Error creating account. Please try again later."]);
    http_response_code(500);
}

// Close connection
$insert_stmt->close();
$conn->close();
?>
