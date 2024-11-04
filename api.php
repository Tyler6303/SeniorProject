<?php
// Enable error reporting (only for development)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response and CORS
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// Include the database connection script
include 'db_connection.php';

// Initialize the database connection with the correct role
$conn = getDbConnection('user');

// Ensure the request method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if the request is for upcoming projects
    $upcoming = isset($_GET['upcoming']) && $_GET['upcoming'] == '1' ? 1 : 0;

    // Log the type of projects being requested
    error_log("Fetching " . ($upcoming ? "upcoming" : "past") . " projects.");

    // SQL query to fetch the relevant projects
    $sql = "SELECT id, year, semester, project, student_fname, student_lname, advisor, coadvisor 
            FROM projects_rows__2_ 
            WHERE upcoming = $upcoming";

    // Execute the query
    $result = $conn->query($sql);

    if (!$result) {
        // Log the query error and return a response
        error_log("Query Failed: " . $conn->error);
        echo json_encode(["error" => "Query failed"]);
        exit;
    }

    // Return an empty array if no results are found
    if ($result->num_rows === 0) {
        error_log("No projects found.");
        echo json_encode([]);
        exit;
    }

    // Collect the results into an array
    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }

    // Log the number of projects retrieved
    error_log("Retrieved " . count($projects) . " projects.");

    // Send the projects as a JSON response
    echo json_encode($projects);
} else {
    // Handle unsupported request methods
    error_log("Unsupported request method.");
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Unsupported request method"]);
}

// Close the database connection
$conn->close();
?>
