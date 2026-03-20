<?php
require_once '../../config/database.php';
require_once '../../config/email_sender.php';

setJSONHeader();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = sanitizeInput($_POST['user_id'] ?? '');
$user_type = sanitizeInput($_POST['user_type'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');

// Validation
if (empty($user_id) || empty($user_type)) {
    echo json_encode(['success' => false, 'message' => 'User ID and type are required']);
    exit;
}

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Validate email domain - only @bvrit.ac.in allowed
if (!preg_match('/@bvrit\.ac\.in$/i', $email)) {
    echo json_encode(['success' => false, 'message' => 'Only @bvrit.ac.in email addresses are allowed']);
    exit;
}

if (!in_array($user_type, ['admin', 'student', 'faculty'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid user type']);
    exit;
}

$conn = getDBConnection();

// Determine table and ID field
$table = $user_type;
if ($user_type === 'admin') {
    $id_field = 'admin_id';
} elseif ($user_type === 'student') {
    $table = 'students';
    $id_field = 'student_id';
} else {
    $id_field = 'faculty_id';
}

// First check if user ID exists
// Admin table doesn't have 'name' field, so handle it separately
if ($user_type === 'admin') {
    $stmt = $conn->prepare("SELECT email FROM $table WHERE $id_field = ?");
} else {
    $stmt = $conn->prepare("SELECT name, email FROM $table WHERE $id_field = ?");
}
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User ID not found. Please check your ID.']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}

$user = $result->fetch_assoc();
$stored_email = $user['email'];
$user_name = ($user_type === 'admin') ? 'Admin' : $user['name'];
$stmt->close();

// Check if email matches (case-insensitive, trim whitespace)
$email_trimmed = trim($email);
$stored_email_trimmed = trim($stored_email);

if (strtolower($stored_email_trimmed) !== strtolower($email_trimmed)) {
    // Show more detailed error
    $masked = maskEmail($stored_email);
    echo json_encode([
        'success' => false, 
        'message' => 'Email does not match. Registered email: ' . $masked,
        'debug' => [
            'entered_email' => $email_trimmed,
            'entered_length' => strlen($email_trimmed),
            'stored_length' => strlen($stored_email_trimmed),
            'match' => false
        ]
    ]);
    closeDBConnection($conn);
    exit;
}

/**
 * Mask email for privacy
 */
function maskEmail($email) {
    $parts = explode('@', $email);
    if (count($parts) != 2) return $email;
    
    $name = $parts[0];
    $domain = $parts[1];
    
    if (strlen($name) <= 2) {
        $masked_name = $name[0] . '*';
    } else {
        $masked_name = substr($name, 0, 2) . str_repeat('*', strlen($name) - 2);
    }
    
    return $masked_name . '@' . $domain;
}

// Generate OTP (6 digits)
$otp = sprintf("%06d", mt_rand(0, 999999));
$expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes', time()));

// Delete old tokens for this user
$stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE user_id = ? AND user_type = ?");
$stmt->bind_param("ss", $user_id, $user_type);
$stmt->execute();
$stmt->close();

// Insert new OTP token
$stmt = $conn->prepare("INSERT INTO password_reset_tokens (user_id, user_type, token, expires_at) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $user_id, $user_type, $otp, $expires_at);

if ($stmt->execute()) {
    // Send OTP via email to all users
    $email_sent = sendOTPEmail($stored_email, $otp, $user_name);
    
    if ($email_sent) {
        echo json_encode([
            'success' => true,
            'message' => 'OTP has been sent to your email: ' . maskEmail($stored_email)
        ]);
    } else {
        // Fallback to demo OTP if email fails
        echo json_encode([
            'success' => true,
            'message' => 'Demo OTP (Email service unavailable):',
            'otp' => $otp
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to generate OTP']);
}

$stmt->close();
closeDBConnection($conn);
?>
