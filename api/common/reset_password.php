<?php
require_once '../../config/database.php';

setJSONHeader();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$token = sanitizeInput($_POST['token'] ?? '');
$new_password = $_POST['new_password'] ?? '';

// Validation
if (empty($token) || empty($new_password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!validatePassword($new_password)) {
    echo json_encode(['success' => false, 'message' => 'Password must contain uppercase, lowercase, and special character (min 6 chars)']);
    exit;
}

$conn = getDBConnection();

// Verify token
$stmt = $conn->prepare("SELECT user_id, user_type FROM password_reset_tokens WHERE token = ? AND expires_at > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}

$token_data = $result->fetch_assoc();
$user_id = $token_data['user_id'];
$user_type = $token_data['user_type'];
$stmt->close();

// Hash new password
$hashed_password = hashPassword($new_password);

// Update password
$table = $user_type;
if ($user_type === 'student') {
    $table = 'students';
    $id_field = 'student_id';
} elseif ($user_type === 'faculty') {
    $id_field = 'faculty_id';
} else {
    $id_field = 'admin_id';
}

$stmt = $conn->prepare("UPDATE $table SET password = ? WHERE $id_field = ?");
$stmt->bind_param("ss", $hashed_password, $user_id);

if ($stmt->execute()) {
    // Delete used token
    $stmt2 = $conn->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
    $stmt2->bind_param("s", $token);
    $stmt2->execute();
    $stmt2->close();
    
    echo json_encode(['success' => true, 'message' => 'Password reset successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to reset password']);
}

$stmt->close();
closeDBConnection($conn);
?>
