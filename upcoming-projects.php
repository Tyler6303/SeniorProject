<?php
// Enable error reporting (only for development)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response and CORS
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// Include the database connection script
include 'db_connection.php';

// Initialize the database connection with 'user' role
$conn = getDbConnection('user');

// Ensure the request method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Query to fetch upcoming projects
    $sql = "SELECT id, year, semester, project, student_fname, student_lname, advisor, coadvisor 
            FROM projects_rows__2_ 
            WHERE upcoming = 1";

    $result = $conn->query($sql);

    // Handle query errors
    if (!$result) {
        error_log("Query Failed: " . $conn->error);
        echo json_encode(["error" => "Query failed"]);
        exit;
    }

    // If no rows are found, return an empty array
    if ($result->num_rows === 0) {
        error_log("No Upcoming Projects Found");
        echo json_encode([]);
        exit;
    }

    // Collect results into an array
    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }

    // Log the number of projects retrieved
    error_log("Retrieved " . count($projects) . " upcoming projects");

    // Return the projects as JSON
    echo json_encode($projects);
} else {
    // Handle unsupported request methods
    error_log("Unsupported Request Method");
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Unsupported request method"]);
}

// Close the database connection
$conn->close();
?>
