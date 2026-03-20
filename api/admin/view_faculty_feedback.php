<?php
session_start();
require_once '../../config/database.php';

setJSONHeader();

// Check if admin is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$faculty_id = sanitizeInput($_GET['faculty_id'] ?? '');

if (empty($faculty_id)) {
    echo json_encode(['success' => false, 'message' => 'Faculty ID is required']);
    exit;
}

$conn = getDBConnection();

// Get faculty details
$stmt = $conn->prepare("SELECT f.faculty_id, f.name, f.email, d.dept_name 
                        FROM faculty f 
                        JOIN departments d ON f.dept_id = d.dept_id 
                        WHERE f.faculty_id = ?");
$stmt->bind_param("s", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Faculty not found']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}

$faculty_info = $result->fetch_assoc();
$stmt->close();

// Get all feedback for this faculty
$query = "SELECT fb.feedback_id, fb.semester, fb.teaching_quality, fb.communication, 
          fb.subject_knowledge, fb.punctuality, fb.comments, fb.submitted_at,
          c.course_code, c.course_name,
          s.student_id, s.name as student_name
          FROM feedback fb
          JOIN courses c ON fb.course_id = c.course_id
          JOIN students s ON fb.student_id = s.student_id
          WHERE fb.faculty_id = ?
          ORDER BY fb.submitted_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();

$feedbacks = [];
while ($row = $result->fetch_assoc()) {
    $feedbacks[] = $row;
}
$stmt->close();

// Calculate averages
$avg_query = "SELECT 
              AVG(CASE teaching_quality 
                  WHEN 'Excellent' THEN 4 
                  WHEN 'Good' THEN 3 
                  WHEN 'Average' THEN 2 
                  WHEN 'Poor' THEN 1 
              END) as avg_teaching,
              AVG(CASE communication 
                  WHEN 'Excellent' THEN 4 
                  WHEN 'Good' THEN 3 
                  WHEN 'Average' THEN 2 
                  WHEN 'Poor' THEN 1 
              END) as avg_communication,
              AVG(CASE subject_knowledge 
                  WHEN 'Excellent' THEN 4 
                  WHEN 'Good' THEN 3 
                  WHEN 'Average' THEN 2 
                  WHEN 'Poor' THEN 1 
              END) as avg_knowledge,
              AVG(CASE punctuality 
                  WHEN 'Excellent' THEN 4 
                  WHEN 'Good' THEN 3 
                  WHEN 'Average' THEN 2 
                  WHEN 'Poor' THEN 1 
              END) as avg_punctuality,
              COUNT(*) as total_feedback
              FROM feedback
              WHERE faculty_id = ?";

$stmt = $conn->prepare($avg_query);
$stmt->bind_param("s", $faculty_id);
$stmt->execute();
$avg_result = $stmt->get_result();
$averages = $avg_result->fetch_assoc();
$stmt->close();

echo json_encode([
    'success' => true,
    'faculty_info' => $faculty_info,
    'feedbacks' => $feedbacks,
    'averages' => $averages
]);

closeDBConnection($conn);
?>
