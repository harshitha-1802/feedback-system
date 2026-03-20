<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/email_sender.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

$conn = getDBConnection();

// Check if student exists with this email
$stmt = $conn->prepare("SELECT student_id, name, email FROM students WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'No student found with this email']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}

$student = $result->fetch_assoc();
$stmt->close();

// Generate 6-digit OTP
$otp = sprintf("%06d", mt_rand(0, 999999));
$expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// Store OTP in database
$stmt = $conn->prepare("INSERT INTO password_reset_tokens (user_id, user_type, token, expires_at) VALUES (?, 'student', ?, ?) ON DUPLICATE KEY UPDATE token = ?, expires_at = ?");
$stmt->bind_param("sssss", $student['student_id'], $otp, $expiry, $otp, $expiry);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Failed to generate OTP']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}

$stmt->close();

// Send OTP via email
$emailSent = sendOTPEmail($email, $otp, $student['name']);

if ($emailSent) {
    // Store email in session for verification
    $_SESSION['otp_email'] = $email;
    $_SESSION['otp_user_type'] = 'student';
    
    echo json_encode([
        'success' => true,
        'message' => 'OTP sent successfully to your email'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send OTP. Please try again.'
    ]);
}

closeDBConnection($conn);
?>
