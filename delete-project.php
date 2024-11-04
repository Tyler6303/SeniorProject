<?php
include 'db_connection.php'; // Include the database connection

header('Content-Type: application/json'); // Set content type to JSON
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable error reporting

// Start session to validate admin access
session_start();

// Check if the user is authenticated and is an admin
if (!isset($_SESSION['auth']) || !$_SESSION['auth']['logged_in'] || !$_SESSION['auth']['isAdmin']) {
    error_log('Unauthorized delete attempt.');
    echo json_encode(['error' => 'Unauthorized access. Admin rights required.']);
    http_response_code(403); // Forbidden
    exit();
}

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $conn = getDbConnection('admin'); // Use admin role for delete

        // Prepare the SQL delete query
        $stmt = $conn->prepare('DELETE FROM projects_rows__2_ WHERE id = ?');
        $stmt->bind_param('i', $id);

        // Execute the query
        if ($stmt->execute()) {
            error_log("Project with ID $id deleted successfully.");
            echo json_encode(['success' => true]);
        } else {
            error_log("Delete Error: " . $stmt->error);
            echo json_encode(['error' => 'Failed to delete project: ' . $stmt->error]);
        }

        $stmt->close();
        $conn->close();
    } catch (mysqli_sql_exception $e) {
        error_log("MySQL Error: " . $e->getMessage());
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        http_response_code(500);
    }
} else {
    error_log('Invalid project ID provided.');
    echo json_encode(['error' => 'Invalid project ID.']);
    http_response_code(400);
}
?>
