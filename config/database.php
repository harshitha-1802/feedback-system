<?php
// Database Configuration for XAMPP
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'feedback_system');

// Set default timezone
date_default_timezone_set('UTC');

// Create connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die(json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $conn->connect_error
        ]));
    }
    
    $conn->set_charset("utf8mb4");
    
    // Set MySQL timezone to match PHP timezone
    $conn->query("SET time_zone = '+00:00'");
    
    return $conn;
}

// Close connection
function closeDBConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}

// Sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate email format
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) && 
           preg_match('/@bvrit\.ac\.in$/', $email);
}

// Validate phone number (10 digits)
function validatePhone($phone) {
    return preg_match('/^[0-9]{10}$/', $phone);
}

// Validate password strength
function validatePassword($password) {
    // At least 6 characters, 1 uppercase, 1 lowercase, 1 special character
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{6,}$/', $password);
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Generate random token
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Set JSON header
function setJSONHeader() {
    header('Content-Type: application/json');
}

// Enable CORS (if needed)
function enableCORS() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('Access-Control-Allow-Headers: Content-Type');
}
?>
