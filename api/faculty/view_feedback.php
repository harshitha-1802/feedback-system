<?php
session_start();
require_once '../../config/database.php';

setJSONHeader();

// Check if faculty is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'faculty') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$faculty_id = $_SESSION['user_id'];

$conn = getDBConnection();

// Get feedback for faculty
$query = "SELECT f.feedback_id, f.semester, f.teaching_quality, f.communication, 
          f.subject_knowledge, f.punctuality, f.comments, f.submitted_at,
          c.course_code, c.course_name, s.name as student_name, s.student_id
          FROM feedback f
          JOIN courses c ON f.course_id = c.course_id
          JOIN students s ON f.student_id = s.student_id
          WHERE f.faculty_id = ?
          ORDER BY f.submitted_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();

$feedbacks = [];
while ($row = $result->fetch_assoc()) {
    $feedbacks[] = $row;
}

// Calculate average ratings
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

$stmt2 = $conn->prepare($avg_query);
$stmt2->bind_param("s", $faculty_id);
$stmt2->execute();
$avg_result = $stmt2->get_result();
$averages = $avg_result->fetch_assoc();

echo json_encode([
    'success' => true, 
    'data' => $feedbacks,
    'averages' => $averages
]);

$stmt->close();
$stmt2->close();
closeDBConnection($conn);
?>
