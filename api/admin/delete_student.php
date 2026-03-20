<?php
require_once '../../config/database.php';

setJSONHeader();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$student_id = sanitizeInput($_POST['student_id'] ?? '');

if (empty($student_id)) {
    echo json_encode(['success' => false, 'message' => 'Student ID is required']);
    exit;
}

$conn = getDBConnection();

// Check if student exists
$stmt = $conn->prepare("SELECT name FROM students WHERE student_id = ?");
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
$stmt->close();

// Delete student (this will also delete related feedback due to CASCADE)
$stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
$stmt->bind_param("s", $student_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Student "' . $student['name'] . '" deleted successfully'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete student']);
}

$stmt->close();
closeDBConnection($conn);
?>