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
$faculty_id = sanitizeInput($_POST['faculty_id'] ?? '');
$name = sanitizeInput($_POST['name'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$phone = sanitizeInput($_POST['phone'] ?? '');
$department = sanitizeInput($_POST['department'] ?? '');
$password = $_POST['password'] ?? '';

// Validation
$errors = [];

if (empty($faculty_id)) {
    $errors[] = 'Faculty ID is required';
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

// Check if faculty ID already exists
$stmt = $conn->prepare("SELECT faculty_id FROM faculty WHERE faculty_id = ?");
$stmt->bind_param("s", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Faculty ID already exists']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}
$stmt->close();

// Check if email already exists
$stmt = $conn->prepare("SELECT email FROM faculty WHERE email = ?");
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

// Insert faculty
$stmt = $conn->prepare("INSERT INTO faculty (faculty_id, name, email, phone, password, dept_id) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssi", $faculty_id, $name, $email, $phone, $hashed_password, $dept_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Faculty added successfully',
        'data' => [
            'faculty_id' => $faculty_id,
            'name' => $name,
            'email' => $email
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add faculty: ' . $stmt->error]);
}

$stmt->close();
closeDBConnection($conn);
?>
