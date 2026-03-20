<?php
require_once '../../config/database.php';

setJSONHeader();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$faculty_id = sanitizeInput($_POST['faculty_id'] ?? '');

if (empty($faculty_id)) {
    echo json_encode(['success' => false, 'message' => 'Faculty ID is required']);
    exit;
}

$conn = getDBConnection();

// Check if faculty exists
$stmt = $conn->prepare("SELECT name FROM faculty WHERE faculty_id = ?");
$stmt->bind_param("s", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Faculty not found']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}

$faculty = $result->fetch_assoc();
$stmt->close();

// Delete faculty (this will also delete related feedback and course assignments due to CASCADE)
$stmt = $conn->prepare("DELETE FROM faculty WHERE faculty_id = ?");
$stmt->bind_param("s", $faculty_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Faculty "' . $faculty['name'] . '" deleted successfully'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete faculty']);
}

$stmt->close();
closeDBConnection($conn);
?>