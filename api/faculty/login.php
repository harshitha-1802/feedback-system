<?php
session_start();
require_once '../../config/database.php';

setJSONHeader();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$faculty_id = sanitizeInput($_POST['faculty_id'] ?? '');
$password = $_POST['password'] ?? '';

// Validation
if (empty($faculty_id) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

$conn = getDBConnection();

// Check if faculty exists and is active
$stmt = $conn->prepare("SELECT f.faculty_id, f.name, f.password, f.email, f.phone, f.is_active, d.dept_code, d.dept_name 
                        FROM faculty f 
                        JOIN departments d ON f.dept_id = d.dept_id 
                        WHERE f.faculty_id = ?");
$stmt->bind_param("s", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Faculty ID not found. Contact admin.']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}

$faculty = $result->fetch_assoc();

// Check if faculty is active
if ($faculty['is_active'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Your account is inactive. Contact admin.']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}

// Verify password
if (!verifyPassword($password, $faculty['password'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid password']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}

// Set session
$_SESSION['user_type'] = 'faculty';
$_SESSION['user_id'] = $faculty['faculty_id'];
$_SESSION['name'] = $faculty['name'];
$_SESSION['email'] = $faculty['email'];
$_SESSION['department'] = $faculty['dept_code'];

echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'redirect' => 'faculty-dashboard.html',
    'data' => [
        'name' => $faculty['name'],
        'faculty_id' => $faculty['faculty_id'],
        'department' => $faculty['dept_name']
    ]
]);

$stmt->close();
closeDBConnection($conn);
?>
