<?php
require_once '../../config/database.php';

setJSONHeader();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$student_id = sanitizeInput($_POST['student_id'] ?? '');
$name = sanitizeInput($_POST['name'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$phone = sanitizeInput($_POST['phone'] ?? '');
$dept_id = sanitizeInput($_POST['dept_id'] ?? '');
$semester = sanitizeInput($_POST['semester'] ?? '');
$is_active = sanitizeInput($_POST['is_active'] ?? '');

// Validation
if (empty($student_id) || empty($name) || empty($email) || empty($phone) || empty($dept_id) || empty($semester) || $is_active === '') {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

if (!preg_match('/@bvrit\.ac\.in$/i', $email)) {
    echo json_encode(['success' => false, 'message' => 'Only @bvrit.ac.in email addresses are allowed']);
    exit;
}

if (!preg_match('/^[0-9]{10}$/', $phone)) {
    echo json_encode(['success' => false, 'message' => 'Phone number must be 10 digits']);
    exit;
}

if (!in_array($dept_id, ['1', '2', '3', '4'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid department']);
    exit;
}

if (!in_array($semester, ['1', '2', '3', '4', '5', '6', '7', '8'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid semester']);
    exit;
}

if (!in_array($is_active, ['0', '1'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

$conn = getDBConnection();

// Check if student exists
$stmt = $conn->prepare("SELECT student_id FROM students WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}
$stmt->close();

// Check if email is already used by another student
$stmt = $conn->prepare("SELECT student_id FROM students WHERE email = ? AND student_id != ?");
$stmt->bind_param("ss", $email, $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already exists for another student']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}
$stmt->close();

// Update student
$stmt = $conn->prepare("UPDATE students SET name = ?, email = ?, phone = ?, dept_id = ?, semester = ?, is_active = ? WHERE student_id = ?");
$stmt->bind_param("sssiiis", $name, $email, $phone, $dept_id, $semester, $is_active, $student_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Student updated successfully'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update student']);
}

$stmt->close();
closeDBConnection($conn);
?>