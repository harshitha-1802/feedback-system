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

// Get student's department and semester
$stmt = $conn->prepare("SELECT s.student_id, s.name, s.dept_id, s.semester, d.dept_name, d.dept_code 
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
$dept_id = $student['dept_id'];
$semester = $student['semester'];
$stmt->close();

// Get courses for student's department and semester with assigned faculty
$query = "SELECT c.course_id, c.course_code, c.course_name, f.faculty_id, f.name as faculty_name
          FROM courses c
          LEFT JOIN faculty_courses fc ON c.course_id = fc.course_id AND fc.semester = ?
          LEFT JOIN faculty f ON fc.faculty_id = f.faculty_id
          WHERE c.dept_id = ? AND c.semester = ?
          ORDER BY c.course_name";

$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $semester, $dept_id, $semester);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    // Check if feedback already submitted for this course
    $check_stmt = $conn->prepare("SELECT feedback_id FROM feedback 
                                   WHERE student_id = ? AND faculty_id = ? 
                                   AND course_id = ? AND semester = ?");
    $check_stmt->bind_param("ssii", $student_id, $row['faculty_id'], $row['course_id'], $semester);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    $row['feedback_submitted'] = ($check_result->num_rows > 0);
    $check_stmt->close();
    
    $courses[] = $row;
}

$stmt->close();

// Check if all feedback submitted
$all_submitted = true;
foreach ($courses as $course) {
    if (!$course['feedback_submitted']) {
        $all_submitted = false;
        break;
    }
}

echo json_encode([
    'success' => true,
    'student_info' => [
        'student_id' => $student['student_id'],
        'name' => $student['name'],
        'semester' => $student['semester'],
        'department' => $student['dept_name'],
        'dept_code' => $student['dept_code']
    ],
    'courses' => $courses,
    'all_feedback_submitted' => $all_submitted
]);

closeDBConnection($conn);
?>
