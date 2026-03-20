<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// Enable error logging for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';
$email = isset($_SESSION['otp_email']) ? $_SESSION['otp_email'] : '';

if (empty($otp)) {
    echo json_encode(['success' => false, 'message' => 'OTP is required']);
    exit;
}

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please request OTP again']);
    exit;
}

try {
    $conn = getDBConnection();

    // Get faculty details
    $stmt = $conn->prepare("SELECT faculty_id, name, email, dept_id FROM faculty WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Faculty not found']);
        $stmt->close();
        closeDBConnection($conn);
        exit;
    }

    $faculty = $result->fetch_assoc();
    $stmt->close();

    // Verify OTP
    $stmt = $conn->prepare("SELECT token, expires_at FROM password_reset_tokens WHERE user_id = ? AND user_type = 'faculty' AND token = ? AND expires_at > NOW()");
    $stmt->bind_param("ss", $faculty['faculty_id'], $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP']);
        $stmt->close();
        closeDBConnection($conn);
        exit;
    }

    $stmt->close();

    // Delete used OTP
    $stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE user_id = ? AND user_type = 'faculty'");
    $stmt->bind_param("s", $faculty['faculty_id']);
    $stmt->execute();
    $stmt->close();

    // Create session
    $_SESSION['user_id'] = $faculty['faculty_id'];
    $_SESSION['user_type'] = 'faculty';
    $_SESSION['user_name'] = $faculty['name'];
    $_SESSION['user_email'] = $faculty['email'];
    $_SESSION['dept_id'] = $faculty['dept_id'];

    // Clear OTP session data
    unset($_SESSION['otp_email']);
    unset($_SESSION['otp_user_type']);

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'redirect' => 'faculty-dashboard.html'
    ]);

    closeDBConnection($conn);
    
} catch (Exception $e) {
    error_log("OTP Verification Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?>
