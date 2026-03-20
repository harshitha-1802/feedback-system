<?php
session_start();
require_once '../../config/database.php';

setJSONHeader();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$student_id = sanitizeInput($_POST['student_id'] ?? '');
$password = $_POST['password'] ?? '';

// Validation
if (empty($student_id) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

$conn = getDBConnection();

// Check if student exists and is active
$stmt = $conn->prepare("SELECT s.student_id, s.name, s.password, s.email, s.phone, s.semester, s.is_active, d.dept_code, d.dept_name 
                        FROM students s 
                        JOIN departments d ON s.dept_id = d.dept_id 
                        WHERE s.student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Student ID not found. Contact admin.']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}

$student = $result->fetch_assoc();

// Check if student is active
if ($student['is_active'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Your account is inactive. Contact admin.']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}

// Verify password
if (!verifyPassword($password, $student['password'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid password']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}

// Set session
$_SESSION['user_type'] = 'student';
$_SESSION['user_id'] = $student['student_id'];
$_SESSION['name'] = $student['name'];
$_SESSION['email'] = $student['email'];
$_SESSION['department'] = $student['dept_code'];
$_SESSION['semester'] = $student['semester'];

echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'redirect' => 'student-dashboard.html',
    'data' => [
        'name' => $student['name'],
        'student_id' => $student['student_id'],
        'department' => $student['dept_name'],
        'semester' => $student['semester']
    ]
]);

$stmt->close();
closeDBConnection($conn);
?>
