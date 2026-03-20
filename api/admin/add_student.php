<?php
session_start();
require_once '../../config/database.php';

setJSONHeader();

// Check if admin is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get and sanitize inputs
$student_id = sanitizeInput($_POST['student_id'] ?? '');
$name = sanitizeInput($_POST['name'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$phone = sanitizeInput($_POST['phone'] ?? '');
$department = sanitizeInput($_POST['department'] ?? '');
$semester = intval($_POST['semester'] ?? 0);
$password = $_POST['password'] ?? '';

// Validation
$errors = [];

if (empty($student_id)) {
    $errors[] = 'Student ID is required';
}

if (empty($name) || !preg_match('/^[A-Za-z]+( [A-Za-z]+)*$/', $name)) {
    $errors[] = 'Valid name is required (letters and spaces only)';
}

if (empty($email) || !validateEmail($email)) {
    $errors[] = 'Valid @bvrit.ac.in email is required';
}

if (empty($phone) || !validatePhone($phone)) {
    $errors[] = 'Valid 10-digit phone number is required';
}

if (empty($department)) {
    $errors[] = 'Department is required';
}

if ($semester < 1 || $semester > 8) {
    $errors[] = 'Valid semester (1-8) is required';
}

if (empty($password) || !validatePassword($password)) {
    $errors[] = 'Password must contain uppercase, lowercase, and special character (min 6 chars)';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

$conn = getDBConnection();

// Get department ID
$stmt = $conn->prepare("SELECT dept_id FROM departments WHERE dept_code = ?");
$stmt->bind_param("s", $department);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid department']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}

$dept_id = $result->fetch_assoc()['dept_id'];
$stmt->close();

// Check if student ID already exists
$stmt = $conn->prepare("SELECT student_id FROM students WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Student ID already exists']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}
$stmt->close();

// Check if email already exists
$stmt = $conn->prepare("SELECT email FROM students WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}
$stmt->close();

// Hash password
$hashed_password = hashPassword($password);

// Insert student
$stmt = $conn->prepare("INSERT INTO students (student_id, name, email, phone, password, dept_id, semester) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssis", $student_id, $name, $email, $phone, $hashed_password, $dept_id, $semester);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Student added successfully',
        'data' => [
            'student_id' => $student_id,
            'name' => $name,
            'email' => $email
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add student: ' . $stmt->error]);
}

$stmt->close();
closeDBConnection($conn);
?>
