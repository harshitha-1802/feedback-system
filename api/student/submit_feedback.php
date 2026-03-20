<?php
session_start();
require_once '../../config/database.php';

setJSONHeader();

// Check if student is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$student_id = $_SESSION['user_id'];

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['feedbacks']) || !is_array($input['feedbacks'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

$feedbacks = $input['feedbacks'];

if (empty($feedbacks)) {
    echo json_encode(['success' => false, 'message' => 'No feedback data provided']);
    exit;
}

$valid_ratings = ['Excellent', 'Good', 'Average', 'Poor'];
$conn = getDBConnection();

// Start transaction
$conn->begin_transaction();

try {
    $success_count = 0;
    $error_messages = [];
    
    foreach ($feedbacks as $feedback) {
        $faculty_id = sanitizeInput($feedback['faculty_id'] ?? '');
        $course_id = intval($feedback['course_id'] ?? 0);
        $semester = intval($feedback['semester'] ?? 0);
        $teaching_quality = sanitizeInput($feedback['teaching_quality'] ?? '');
        $communication = sanitizeInput($feedback['communication'] ?? '');
        $subject_knowledge = sanitizeInput($feedback['subject_knowledge'] ?? '');
        $punctuality = sanitizeInput($feedback['punctuality'] ?? '');
        $comments = sanitizeInput($feedback['comments'] ?? '');
        
        // Validation
        if (empty($faculty_id) || $course_id <= 0 || $semester < 1 || $semester > 8) {
            $error_messages[] = "Invalid data for course ID: $course_id";
            continue;
        }
        
        if (!in_array($teaching_quality, $valid_ratings) || 
            !in_array($communication, $valid_ratings) || 
            !in_array($subject_knowledge, $valid_ratings) || 
            !in_array($punctuality, $valid_ratings)) {
            $error_messages[] = "Invalid rating values for course ID: $course_id";
            continue;
        }
        
        // Check if feedback already submitted
        $stmt = $conn->prepare("SELECT feedback_id FROM feedback 
                                WHERE student_id = ? AND faculty_id = ? 
                                AND course_id = ? AND semester = ?");
        $stmt->bind_param("ssii", $student_id, $faculty_id, $course_id, $semester);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_messages[] = "Feedback already submitted for course ID: $course_id";
            $stmt->close();
            continue;
        }
        $stmt->close();
        
        // Insert feedback
        $stmt = $conn->prepare("INSERT INTO feedback (student_id, faculty_id, course_id, semester, 
                                teaching_quality, communication, subject_knowledge, punctuality, comments) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiisssss", $student_id, $faculty_id, $course_id, $semester, 
                          $teaching_quality, $communication, $subject_knowledge, $punctuality, $comments);
        
        if ($stmt->execute()) {
            $success_count++;
        } else {
            $error_messages[] = "Failed to submit feedback for course ID: $course_id";
        }
        $stmt->close();
    }
    
    // Commit transaction
    $conn->commit();
    
    if ($success_count > 0) {
        $message = "Successfully submitted feedback for $success_count course(s)";
        if (!empty($error_messages)) {
            $message .= ". Some errors: " . implode(", ", $error_messages);
        }
        echo json_encode(['success' => true, 'message' => $message, 'count' => $success_count]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit feedback: ' . implode(", ", $error_messages)]);
    }
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error submitting feedback: ' . $e->getMessage()]);
}

closeDBConnection($conn);
?>
