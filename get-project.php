<?php
include 'db_connection.php'; // Include database connection

header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = getDbConnection();

    $id = $_GET['id'] ?? null; // Retrieve ID from query string

    if ($id) {
        $stmt = $conn->prepare('SELECT * FROM projects_rows__2_ WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $project = $result->fetch_assoc();
            echo json_encode($project);
        } else {
            echo json_encode(['error' => 'Project not found']);
        }

        $stmt->close();
    } else {
        echo json_encode(['error' => 'Project ID is required']);
    }

    $conn->close();
} catch (mysqli_sql_exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    http_response_code(500);
} catch (Exception $e) {
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    http_response_code(500);
}
?>
