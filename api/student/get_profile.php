<?php
session_start();
require_once '../../config/database.php';

setJSONHeader();

// Check if student is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$student_id = $_SESSION['user_id'];

$conn = getDBConnection();

$stmt = $conn->prepare("SELECT s.student_id, s.name, s.email, s.phone, s.semester, d.dept_name, d.dept_code 
                        FROM students s 
                        JOIN departments d ON s.dept_id = d.dept_id 
                        WHERE s.student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}

$student = $result->fetch_assoc();

echo json_encode([
    'success' => true,
    'data' => [
        'student_id' => $student['student_id'],
        'name' => $student['name'],
        'email' => $student['email'],
        'phone' => $student['phone'],
        'semester' => $student['semester'],
        'department' => $student['dept_name'],
        'dept_code' => $student['dept_code']
    ]
]);

$stmt->close();
closeDBConnection($conn);
?>
