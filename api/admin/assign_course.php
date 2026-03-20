<?php
session_start();
require_once '../../config/database.php';

setJSONHeader();

// Check if admin is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$conn = getDBConnection();

// Handle different actions
$action = $_GET['action'] ?? 'assign';

if ($action === 'list') {
    // Get all course assignments
    $query = "SELECT fc.assignment_id, fc.faculty_id, fc.course_id, fc.semester, fc.academic_year,
              f.name as faculty_name,
              c.course_code, c.course_name
              FROM faculty_courses fc
              JOIN faculty f ON fc.faculty_id = f.faculty_id
              JOIN courses c ON fc.course_id = c.course_id
              ORDER BY fc.academic_year DESC, fc.semester, f.name";
    
    $result = $conn->query($query);
    
    $assignments = [];
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $assignments]);
    closeDBConnection($conn);
    exit;
}

if ($action === 'delete') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }
    
    $assignment_id = intval($_POST['assignment_id'] ?? 0);
    
    if ($assignment_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid assignment ID']);
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM faculty_courses WHERE assignment_id = ?");
    $stmt->bind_param("i", $assignment_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Assignment removed successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove assignment']);
    }
    
    $stmt->close();
    closeDBConnection($conn);
    exit;
}

// Default action: assign course
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$faculty_id = sanitizeInput($_POST['faculty_id'] ?? '');
$course_id = intval($_POST['course_id'] ?? 0);
$semester = intval($_POST['semester'] ?? 0);
$academic_year = sanitizeInput($_POST['academic_year'] ?? '');

// Validation
if (empty($faculty_id) || $course_id <= 0 || $semester < 1 || $semester > 8 || empty($academic_year)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Check if faculty exists
$stmt = $conn->prepare("SELECT faculty_id FROM faculty WHERE faculty_id = ?");
$stmt->bind_param("s", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Faculty not found in database. Please add faculty first.']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}
$stmt->close();

// Check if course exists
$stmt = $conn->prepare("SELECT course_id FROM courses WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Course not found']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}
$stmt->close();

// Check if assignment already exists
$stmt = $conn->prepare("SELECT assignment_id FROM faculty_courses WHERE faculty_id = ? AND course_id = ? AND semester = ? AND academic_year = ?");
$stmt->bind_param("siis", $faculty_id, $course_id, $semester, $academic_year);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'This course is already assigned to the faculty']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}
$stmt->close();

// Insert assignment
$stmt = $conn->prepare("INSERT INTO faculty_courses (faculty_id, course_id, semester, academic_year) VALUES (?, ?, ?, ?)");
$stmt->bind_param("siis", $faculty_id, $course_id, $semester, $academic_year);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Course assigned to faculty successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to assign course']);
}

$stmt->close();
closeDBConnection($conn);
?>
