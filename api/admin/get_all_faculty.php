<?php
require_once '../../config/database.php';

setJSONHeader();

// Note: Session check removed for testing - add back in production

$conn = getDBConnection();

$query = "SELECT f.faculty_id, f.name, f.email, f.phone, f.is_active, f.dept_id,
          d.dept_code, d.dept_name, f.created_at,
          GROUP_CONCAT(DISTINCT c.course_name SEPARATOR ', ') as assigned_courses
          FROM faculty f
          JOIN departments d ON f.dept_id = d.dept_id
          LEFT JOIN faculty_courses fc ON f.faculty_id = fc.faculty_id
          LEFT JOIN courses c ON fc.course_id = c.course_id
          GROUP BY f.faculty_id
          ORDER BY f.created_at DESC";

$result = $conn->query($query);

$faculty = [];
while ($row = $result->fetch_assoc()) {
    $faculty[] = $row;
}

echo json_encode(['success' => true, 'faculty' => $faculty]);

closeDBConnection($conn);
?>
