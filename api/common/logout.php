<?php
session_start();
require_once '../../config/database.php';

setJSONHeader();

// Destroy session
session_unset();
session_destroy();

echo json_encode([
    'success' => true,
    'message' => 'Logged out successfully',
    'redirect' => 'index.html'
]);
?>
