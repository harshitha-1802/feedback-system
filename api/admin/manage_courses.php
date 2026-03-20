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
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        // Get all courses with department info
        $query = "SELECT c.course_id, c.course_code, c.course_name, c.semester, d.dept_code, d.dept_name 
                  FROM courses c 
                  JOIN departments d ON c.dept_id = d.dept_id 
                  ORDER BY d.dept_code, c.semester, c.course_name";
        $result = $conn->query($query);
        
        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $courses]);
        break;
        
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $course_code = sanitizeInput($_POST['course_code'] ?? '');
        $course_name = sanitizeInput($_POST['course_name'] ?? '');
        $department = sanitizeInput($_POST['department'] ?? '');
        $semester = intval($_POST['semester'] ?? 0);
        
        if (empty($course_code) || empty($course_name) || empty($department) || $semester < 1 || $semester > 8) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }
        
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
        
        // Check if course code exists
        $stmt = $conn->prepare("SELECT course_id FROM courses WHERE course_code = ?");
        $stmt->bind_param("s", $course_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Course code already exists']);
            $stmt->close();
            closeDBConnection($conn);
            exit;
        }
        $stmt->close();
        
        // Insert course
        $stmt = $conn->prepare("INSERT INTO courses (course_code, course_name, dept_id, semester) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $course_code, $course_name, $dept_id, $semester);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Course added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add course']);
        }
        
        $stmt->close();
        break;
        
    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $course_id = intval($_POST['course_id'] ?? 0);
        
        if ($course_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid course ID']);
            exit;
        }
        
        $stmt = $conn->prepare("DELETE FROM courses WHERE course_id = ?");
        $stmt->bind_param("i", $course_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Course deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete course']);
        }
        
        $stmt->close();
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

closeDBConnection($conn);
?>
