<?php
include 'db_connection.php'; // Include the database connection

$conn = getDbConnection();
if (!$conn) {
    die(json_encode(['error' => 'Failed to connect to the database']));
}

$id = $_GET['id'] ?? null; // Get the project ID from the query parameter

if ($id) {
    $stmt = $conn->prepare('SELECT * FROM projects_rows__2_ WHERE id = ?');
    if ($stmt) {
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
        echo json_encode(['error' => 'Failed to prepare SQL statement: ' . $conn->error]);
    }
} else {
    echo json_encode(['error' => 'Invalid project ID']);
}

$conn->close(); // Close the connection
?>
