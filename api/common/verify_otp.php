<?php
require_once '../../config/database.php';

setJSONHeader();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = sanitizeInput($_POST['user_id'] ?? '');
$user_type = sanitizeInput($_POST['user_type'] ?? '');
$otp = sanitizeInput($_POST['otp'] ?? '');

// Validation
if (empty($user_id) || empty($user_type) || empty($otp)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!in_array($user_type, ['admin', 'student', 'faculty'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid user type']);
    exit;
}

$conn = getDBConnection();

// Verify OTP
$stmt = $conn->prepare("SELECT token FROM password_reset_tokens 
                        WHERE user_id = ? AND user_type = ? AND token = ? AND expires_at > NOW()");
$stmt->bind_param("sss", $user_id, $user_type, $otp);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}

$token_data = $result->fetch_assoc();
$stmt->close();

// OTP verified successfully - return the token for password reset
echo json_encode([
    'success' => true,
    'message' => 'OTP verified successfully',
    'token' => $otp
]);

closeDBConnection($conn);
?>
