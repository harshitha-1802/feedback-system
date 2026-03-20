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

// Get all faculty with their feedback statistics
$query = "SELECT f.faculty_id, f.name as faculty_name, f.email,
          d.dept_name, d.dept_code,
          COUNT(DISTINCT fb.feedback_id) as total_feedback,
          AVG(CASE fb.teaching_quality 
              WHEN 'Excellent' THEN 4 
              WHEN 'Good' THEN 3 
              WHEN 'Average' THEN 2 
              WHEN 'Poor' THEN 1 
          END) as avg_teaching,
          AVG(CASE fb.communication 
              WHEN 'Excellent' THEN 4 
              WHEN 'Good' THEN 3 
              WHEN 'Average' THEN 2 
              WHEN 'Poor' THEN 1 
          END) as avg_communication,
          AVG(CASE fb.subject_knowledge 
              WHEN 'Excellent' THEN 4 
              WHEN 'Good' THEN 3 
              WHEN 'Average' THEN 2 
              WHEN 'Poor' THEN 1 
          END) as avg_knowledge,
          AVG(CASE fb.punctuality 
              WHEN 'Excellent' THEN 4 
              WHEN 'Good' THEN 3 
              WHEN 'Average' THEN 2 
              WHEN 'Poor' THEN 1 
          END) as avg_punctuality
          FROM faculty f
          JOIN departments d ON f.dept_id = d.dept_id
          LEFT JOIN feedback fb ON f.faculty_id = fb.faculty_id
          GROUP BY f.faculty_id, f.name, f.email, d.dept_name, d.dept_code
          ORDER BY d.dept_name, f.name";

$result = $conn->query($query);

$faculty_list = [];
while ($row = $result->fetch_assoc()) {
    $faculty_list[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $faculty_list
]);

closeDBConnection($conn);
?>
