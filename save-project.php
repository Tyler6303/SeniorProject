<?php
include 'db_connection.php'; // Include the database connection

header('Content-Type: application/json'); // Set content type to JSON
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable error reporting

// Start session to validate admin access
session_start();

// Check if the user is authenticated and is an admin
if (!isset($_SESSION['auth']) || !$_SESSION['auth']['logged_in'] || !$_SESSION['auth']['isAdmin']) {
    error_log('Unauthorized access attempt.');
    echo json_encode(['error' => 'Unauthorized access. Admin rights required.']);
    http_response_code(403); // Forbidden
    exit();
}

try {
    $conn = getDbConnection('admin'); // Use admin role for updates

    // Retrieve and decode the JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    // Log the received data
    error_log("Received Data: " . print_r($data, true));

    // Validate that all required fields are present
    if (
        isset($data['id'], $data['student_fname'], $data['student_lname'], 
              $data['year'], $data['semester'], $data['project'], 
              $data['advisor'], $data['description'], $data['upcoming'])
    ) {
        $coadvisor = $data['coadvisor'] ?? null; // Optional co-advisor field

        // Prepare the SQL query
        $stmt = $conn->prepare(
            'UPDATE projects_rows__2_ 
             SET student_fname = ?, student_lname = ?, year = ?, semester = ?, 
                 project = ?, advisor = ?, coadvisor = ?, description = ?, upcoming = ? 
             WHERE id = ?'
        );

        if (!$stmt) {
            error_log("SQL Prepare Error: " . $conn->error);
            die(json_encode(['error' => 'SQL Prepare failed: ' . $conn->error]));
        }

        // Bind parameters by reference to ensure correct behavior
        $student_fname = $data['student_fname'];
        $student_lname = $data['student_lname'];
        $year = $data['year'];
        $semester = $data['semester'];
        $project = $data['project'];
        $advisor = $data['advisor'];
        $description = $data['description'];
        $upcoming = (int) $data['upcoming'];
        $id = $data['id'];

        $stmt->bind_param(
            'ssisssssii',
            $student_fname, $student_lname, $year, $semester,
            $project, $advisor, $coadvisor, $description,
            $upcoming, $id
        );

        // Execute the query
        if ($stmt->execute()) {
            error_log('Project updated successfully.');
            echo json_encode(['success' => true]);
        } else {
            error_log("SQL Execute Error: " . $stmt->error);
            echo json_encode(['error' => 'Failed to update project: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        error_log('Invalid input data received.');
        echo json_encode(['error' => 'Invalid input data']);
        http_response_code(400);
    }

    $conn->close(); // Close the connection
} catch (mysqli_sql_exception $e) {
    error_log("MySQL Error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    http_response_code(500);
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    http_response_code(500);
}
?>
