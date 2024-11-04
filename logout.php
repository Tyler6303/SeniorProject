<?php
session_start();
session_destroy(); // Destroy session data
echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
?>
