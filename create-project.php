<?php
// Enable error reporting (only for development purposes)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response and CORS
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Include the database connection
include 'db_connection.php';

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode the incoming JSON payload
    $data = json_decode(file_get_contents('php://input'), true);

    // Extract project data from the request
    $firstName = $data['student_fname'] ?? '';
    $lastName = $data['student_lname'] ?? '';
    $year = $data['year'] ?? '';
    $semester = $data['semester'] ?? '';
    $projectName = $data['project'] ?? '';
    $advisor = $data['advisor'] ?? '';
    $coadvisor = $data['coadvisor'] ?? null;  // Optional
    $description = $data['description'] ?? '';
    $upcoming = $data['upcoming'] ?? 0;  // Default to 0 (not upcoming)

    // Validate required fields
    if (
        empty($firstName) || empty($lastName) || empty($year) ||
        empty($semester) || empty($projectName) || empty($advisor) ||
        empty($description)
    ) {
        echo json_encode(["error" => "All fields except Co-Advisor are required."]);
        http_response_code(400); // Bad Request
        exit;
    }

    // Get a database connection
    $conn = getDbConnection('admin'); // Ensure admin privileges

    // Prepare the SQL statement
    $stmt = $conn->prepare(
        "INSERT INTO projects_rows__2_ 
        (student_fname, student_lname, year, semester, project, advisor, coadvisor, description, upcoming) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        echo json_encode(["error" => "Failed to prepare SQL statement."]);
        http_response_code(500); // Internal Server Error
        exit;
    }

    // Bind the parameters to the SQL query
    $stmt->bind_param(
        "ssssssssi",
        $firstName, $lastName, $year, $semester,
        $projectName, $advisor, $coadvisor, $description, $upcoming
    );

    // Execute the query and check for errors
    if ($stmt->execute()) {
        echo json_encode(["success" => "Project created successfully!"]);
    } else {
        echo json_encode(["error" => "Failed to create project: " . $stmt->error]);
    }

    // Close the statement and the database connection
    $stmt->close();
    $conn->close();
} else {
    // Handle unsupported request methods
    echo json_encode(["error" => "Invalid request method."]);
    http_response_code(405); // Method Not Allowed
    exit;
}
?>
