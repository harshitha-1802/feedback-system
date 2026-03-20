<?php
require_once '../../config/database.php';

setJSONHeader();

// Note: Session check removed for testing - add back in production

$conn = getDBConnection();

$query = "SELECT s.student_id, s.name, s.email, s.phone, s.semester, s.is_active, s.dept_id,
          d.dept_code, d.dept_name, s.created_at
          FROM students s
          JOIN departments d ON s.dept_id = d.dept_id
          ORDER BY s.created_at DESC";

$result = $conn->query($query);

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

echo json_encode(['success' => true, 'students' => $students]);

closeDBConnection($conn);
?>
