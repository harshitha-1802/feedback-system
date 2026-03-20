<?php
session_start();
require_once '../../config/database.php';

setJSONHeader();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$admin_id = sanitizeInput($_POST['admin_id'] ?? '');
$password = $_POST['password'] ?? '';

// Validation
if (empty($admin_id) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

$conn = getDBConnection();

$stmt = $conn->prepare("SELECT admin_id, password, email, phone FROM admin WHERE admin_id = ?");
$stmt->bind_param("s", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Admin ID or Password']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}

$admin = $result->fetch_assoc();

if (!verifyPassword($password, $admin['password'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid Admin ID or Password']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}

// Set session
$_SESSION['user_type'] = 'admin';
$_SESSION['user_id'] = $admin['admin_id'];
$_SESSION['email'] = $admin['email'];

echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'redirect' => 'admin-dashboard.html'
]);

$stmt->close();
closeDBConnection($conn);
?>
