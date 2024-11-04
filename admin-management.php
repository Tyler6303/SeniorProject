<?php
// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON and CORS headers
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: https://plymouthstate-csit-senior-projects.usnh.domains");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, Role");
header("Access-Control-Allow-Credentials: true");

session_start(); // Start the session to access session variables
error_log("Session data at start of script: " . print_r($_SESSION, true));

// Handle OPTIONS request for CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database credentials
$servername = "localhost";
$dbname = "plymout2_Projects";
$password = "Rot02cla"; // Shared password for both roles

function getDbConnection($role = 'user') {
    global $servername, $dbname, $password;
    $username = ($role === 'admin') ? 'plymout2_admin' : 'plymout2_user';
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        error_log("DB Connection ($role): Failed - " . $conn->connect_error);
        echo json_encode(["error" => "Database connection failed."]);
        http_response_code(500);
        exit();
    }
    error_log("DB Connection ($role): Successful");
    return $conn;
}

// Determine user role from session
$role = 'user'; // Default role
if (isset($_SESSION['auth']['isAdmin']) && $_SESSION['auth']['isAdmin'] === true) {
    $role = (isset($_SESSION['auth']['isSuperAdmin']) && $_SESSION['auth']['isSuperAdmin'] === true) ? 'superadmin' : 'admin';
}
error_log("Determined user role based on session data: $role");

// Fetch admins (accessible to both admin and superadmin)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($role !== 'admin' && $role !== 'superadmin') {
        error_log("Permission denied for role: $role");
        echo json_encode(["error" => "Permission denied."]);
        http_response_code(403);
        exit();
    }

    $conn = getDbConnection('admin');
    $result = $conn->query("SELECT id, full_name, superadmin, email, created_at FROM admin_db");

    if ($result) {
        $admins = [];
        while ($row = $result->fetch_assoc()) {
            // Explicitly check superadmin status and set role
            $row['role'] = (int)$row['superadmin'] === 1 ? "Superadmin" : "Admin";
            $admins[] = $row;
        }
        echo json_encode($admins);
    } else {
        error_log("Failed to fetch admin accounts: " . $conn->error);
        echo json_encode(["error" => "Failed to fetch admin accounts."]);
        http_response_code(500);
    }
    $conn->close();
    exit();
}

// Create a new admin (superadmin-only functionality)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($role !== 'superadmin') {
        error_log("Create operation denied for role: $role");
        echo json_encode(["error" => "Permission denied."]);
        http_response_code(403);
        exit();
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $full_name = $data['full_name'] ?? '';
    $password = password_hash($data['password'] ?? '', PASSWORD_BCRYPT);
    $email = $data['email'] ?? '';
    $superadmin = $data['superadmin'] ?? 0;

    $conn = getDbConnection('admin');
    $stmt = $conn->prepare("INSERT INTO admin_db (full_name, password, email, superadmin) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssi", $full_name, $password, $email, $superadmin);
        if ($stmt->execute()) {
            echo json_encode(["success" => "Admin created successfully.", "id" => $stmt->insert_id]);
        } else {
            error_log("Failed to create admin account: " . $stmt->error);
            echo json_encode(["error" => "Failed to create admin account."]);
            http_response_code(500);
        }
        $stmt->close();
    } else {
        error_log("SQL statement preparation failed: " . $conn->error);
        echo json_encode(["error" => "Failed to prepare SQL statement."]);
        http_response_code(500);
    }
    $conn->close();
    exit();
}

// Update admin information (superadmin-only functionality)
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    if ($role !== 'superadmin') {
        error_log("Update operation denied for role: $role");
        echo json_encode(["error" => "Permission denied."]);
        http_response_code(403);
        exit();
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? '';
    $email = $data['email'] ?? '';
    $superadmin = $data['superadmin'] ?? 0;

    $conn = getDbConnection('admin');
    $stmt = $conn->prepare("UPDATE admin_db SET email = ?, superadmin = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("sii", $email, $superadmin, $id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(["success" => "Admin updated successfully."]);
        } else {
            error_log("Failed to update admin account: " . $stmt->error);
            echo json_encode(["error" => "Failed to update admin account."]);
            http_response_code(500);
        }
        $stmt->close();
    } else {
        error_log("SQL statement preparation failed: " . $conn->error);
        echo json_encode(["error" => "Failed to prepare SQL statement."]);
        http_response_code(500);
    }
    $conn->close();
    exit();
}

// Delete an admin (superadmin-only functionality)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if ($role !== 'superadmin') {
        error_log("Delete operation denied for role: $role");
        echo json_encode(["error" => "Permission denied."]);
        http_response_code(403);
        exit();
    }

    $id = $_GET['id'] ?? '';
    $conn = getDbConnection('admin');
    $stmt = $conn->prepare("DELETE FROM admin_db WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(["success" => "Admin deleted successfully."]);
        } else {
            error_log("Failed to delete admin account: " . $stmt->error);
            echo json_encode(["error" => "Failed to delete admin account."]);
            http_response_code(500);
        }
        $stmt->close();
    } else {
        error_log("SQL statement preparation failed: " . $conn->error);
        echo json_encode(["error" => "Failed to prepare SQL statement."]);
        http_response_code(500);
    }
    $conn->close();
    exit();
}
?>
