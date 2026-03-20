<?php
require_once '../../config/database.php';

setJSONHeader();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$faculty_id = sanitizeInput($_POST['faculty_id'] ?? '');
$name = sanitizeInput($_POST['name'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$phone = sanitizeInput($_POST['phone'] ?? '');
$dept_id = sanitizeInput($_POST['dept_id'] ?? '');
$is_active = sanitizeInput($_POST['is_active'] ?? '');

// Validation
if (empty($faculty_id) || empty($name) || empty($email) || empty($phone) || empty($dept_id) || $is_active === '') {
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

if (!in_array($is_active, ['0', '1'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

$conn = getDBConnection();

// Check if faculty exists
$stmt = $conn->prepare("SELECT faculty_id FROM faculty WHERE faculty_id = ?");
$stmt->bind_param("s", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Faculty not found']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}
$stmt->close();

// Check if email is already used by another faculty
$stmt = $conn->prepare("SELECT faculty_id FROM faculty WHERE email = ? AND faculty_id != ?");
$stmt->bind_param("ss", $email, $faculty_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already exists for another faculty']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}
$stmt->close();

// Update faculty
$stmt = $conn->prepare("UPDATE faculty SET name = ?, email = ?, phone = ?, dept_id = ?, is_active = ? WHERE faculty_id = ?");
$stmt->bind_param("ssssis", $name, $email, $phone, $dept_id, $is_active, $faculty_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Faculty updated successfully'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update faculty']);
}

$stmt->close();
closeDBConnection($conn);
?>