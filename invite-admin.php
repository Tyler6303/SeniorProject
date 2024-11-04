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

// Retrieve POST data
$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$full_name = $data['full_name'] ?? '';
$superadmin = $data['superadmin'] ?? 0;

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["error" => "Invalid email address."]);
    http_response_code(400);
    exit();
}

// Generate a unique token (not saved to the database here)
$token = bin2hex(random_bytes(16));
$setupLink = "https://plymouthstate-csit-senior-projects.usnh.domains/register.html?token=$token&superadmin=$superadmin";

// Prepare the email content
$subject = "Admin Account Setup Invitation";
$message = "Hello $full_name,\n\nYou've been invited to set up your admin account. Please use the following link:\n\n$setupLink\n\nThis link will expire in 24 hours.\n\nBest regards,\nThe Admin Team";
$headers = "From: no-reply@plymouthstate-csit-senior-projects.usnh.domains\r\n";

// Send the email
if (mail($email, $subject, $message, $headers)) {
    echo json_encode(["success" => "Invitation sent successfully."]);
} else {
    echo json_encode(["error" => "Failed to send email."]);
    http_response_code(500);
}
?>
