<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response and CORS
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// Include the database connection file
include 'db_connection.php';

// Get the database connection as a user
$conn = getDbConnection('admin'); // Use 'admin' role for registering admins

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $full_name = $input['full_name'] ?? '';
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    $superadmin = $input['superadmin'] ?? 0;

    if (empty($full_name) || empty($email) || empty($password)) {
        echo json_encode(['error' => 'All fields are required.']);
        exit;
    }

    try {
        $sql = "INSERT INTO admin_db (full_name, email, password, superadmin, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("SQL Prepare Error: " . $conn->error);
        }

        $stmt->bind_param('sssi', $full_name, $email, $password, $superadmin);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Admin registered successfully.']);
        } else {
            throw new Exception("Execution Error: " . $stmt->error);
        }

        $stmt->close();
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Unsupported request method.']);
}

$conn->close();
?>
