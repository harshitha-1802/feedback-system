# API Documentation - Student Feedback Management System

## Base URL
```
http://localhost/feedback_system/api/
```

## Authentication
All authenticated endpoints require an active PHP session. Login endpoints create sessions.

---

## Admin APIs

### 1. Admin Login
**Endpoint:** `POST /api/admin/login.php`

**Request Body:**
```json
{
  "admin_id": "admin",
  "password": "Admin@123"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "redirect": "admin-dashboard.html"
}
```

**Error Response (200):**
```json
{
  "success": false,
  "message": "Invalid Admin ID or Password"
}
```

---

### 2. Add Student
**Endpoint:** `POST /api/admin/add_student.php`

**Authentication:** Required (Admin session)

**Request Body:**
```json
{
  "student_id": "S001",
  "name": "John Doe",
  "email": "john@bvrit.ac.in",
  "phone": "9876543210",
  "department": "CSE",
  "semester": 3,
  "password": "Test@123"
}
```

**Validations:**
- Name: Letters and spaces only
- Email: Must be @bvrit.ac.in
- Phone: Exactly 10 digits
- Password: Min 6 chars, uppercase, lowercase, special char
- Semester: 1-8
- Student ID and Email must be unique

**Success Response:**
```json
{
  "success": true,
  "message": "Student added successfully",
  "data": {
    "student_id": "S001",
    "name": "John Doe",
    "email": "john@bvrit.ac.in"
  }
}
```

---

### 3. Add Faculty
**Endpoint:** `POST /api/admin/add_faculty.php`

**Authentication:** Required (Admin session)

**Request Body:**
```json
{
  "faculty_id": "F001",
  "name": "Dr Smith",
  "email": "smith@bvrit.ac.in",
  "phone": "9876543211",
  "department": "CSE",
  "password": "Faculty@123"
}
```

**Validations:**
- Same as student (except no semester)
- Faculty ID and Email must be unique

**Success Response:**
```json
{
  "success": true,
  "message": "Faculty added successfully",
  "data": {
    "faculty_id": "F001",
    "name": "Dr Smith",
    "email": "smith@bvrit.ac.in"
  }
}
```

---

### 4. Assign Course to Faculty
**Endpoint:** `POST /api/admin/assign_course.php`

**Authentication:** Required (Admin session)

**Request Body:**
```json
{
  "faculty_id": "F001",
  "course_id": 1,
  "semester": 3,
  "academic_year": "2024-25"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Course assigned successfully"
}
```

---

### 5. Manage Courses - List All
**Endpoint:** `GET /api/admin/manage_courses.php?action=list`

**Authentication:** Required (Admin session)

**Success Response:**
```json
{
  "success": true,
  "data": [
    {
      "course_id": 1,
      "course_code": "CS301",
      "course_name": "Database Management System",
      "semester": 3,
      "dept_code": "CSE",
      "dept_name": "Computer Science and Engineering"
    }
  ]
}
```

---

### 6. Manage Courses - Add New
**Endpoint:** `POST /api/admin/manage_courses.php?action=add`

**Authentication:** Required (Admin session)

**Request Body:**
```json
{
  "course_code": "CS401",
  "course_name": "Machine Learning",
  "department": "CSE",
  "semester": 4
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Course added successfully"
}
```

---

### 7. Manage Courses - Delete
**Endpoint:** `POST /api/admin/manage_courses.php?action=delete`

**Authentication:** Required (Admin session)

**Request Body:**
```json
{
  "course_id": 1
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Course deleted successfully"
}
```

---

### 8. Get All Students
**Endpoint:** `GET /api/admin/get_all_students.php`

**Authentication:** Required (Admin session)

**Success Response:**
```json
{
  "success": true,
  "data": [
    {
      "student_id": "S001",
      "name": "John Doe",
      "email": "john@bvrit.ac.in",
      "phone": "9876543210",
      "semester": 3,
      "is_active": 1,
      "dept_code": "CSE",
      "dept_name": "Computer Science and Engineering",
      "created_at": "2024-01-15 10:30:00"
    }
  ]
}
```

---

### 9. Get All Faculty
**Endpoint:** `GET /api/admin/get_all_faculty.php`

**Authentication:** Required (Admin session)

**Success Response:**
```json
{
  "success": true,
  "data": [
    {
      "faculty_id": "F001",
      "name": "Dr Smith",
      "email": "smith@bvrit.ac.in",
      "phone": "9876543211",
      "is_active": 1,
      "dept_code": "CSE",
      "dept_name": "Computer Science and Engineering",
      "assigned_courses": "DBMS, Java Programming",
      "created_at": "2024-01-15 11:00:00"
    }
  ]
}
```

---

## Student APIs

### 1. Student Login
**Endpoint:** `POST /api/student/login.php`

**Request Body:**
```json
{
  "student_id": "S001",
  "password": "Test@123"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "redirect": "student-dashboard.html",
  "data": {
    "name": "John Doe",
    "student_id": "S001",
    "department": "Computer Science and Engineering",
    "semester": 3
  }
}
```

**Error Responses:**
```json
{
  "success": false,
  "message": "Student ID not found. Contact admin."
}
```
```json
{
  "success": false,
  "message": "Your account is inactive. Contact admin."
}
```

---

### 2. Get Student Courses
**Endpoint:** `GET /api/student/get_courses.php`

**Authentication:** Required (Student session)

**Success Response:**
```json
{
  "success": true,
  "data": [
    {
      "course_id": 1,
      "course_code": "CS301",
      "course_name": "Database Management System",
      "faculty_id": "F001",
      "faculty_name": "Dr Smith",
      "feedback_given": 0
    }
  ]
}
```

**Note:** `feedback_given` is 1 if student already submitted feedback, 0 otherwise

---

### 3. Submit Feedback
**Endpoint:** `POST /api/student/submit_feedback.php`

**Authentication:** Required (Student session)

**Request Body:**
```json
{
  "faculty_id": "F001",
  "course_id": 1,
  "semester": 3,
  "teaching_quality": "Excellent",
  "communication": "Good",
  "subject_knowledge": "Excellent",
  "punctuality": "Good",
  "comments": "Great teacher, very helpful"
}
```

**Valid Rating Values:**
- Excellent
- Good
- Average
- Poor

**Success Response:**
```json
{
  "success": true,
  "message": "Feedback submitted successfully"
}
```

**Error Response (Duplicate):**
```json
{
  "success": false,
  "message": "You have already submitted feedback for this course"
}
```

---

## Faculty APIs

### 1. Faculty Login
**Endpoint:** `POST /api/faculty/login.php`

**Request Body:**
```json
{
  "faculty_id": "F001",
  "password": "Faculty@123"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "redirect": "faculty-dashboard.html",
  "data": {
    "name": "Dr Smith",
    "faculty_id": "F001",
    "department": "Computer Science and Engineering"
  }
}
```

---

### 2. View Feedback
**Endpoint:** `GET /api/faculty/view_feedback.php`

**Authentication:** Required (Faculty session)

**Success Response:**
```json
{
  "success": true,
  "data": [
    {
      "feedback_id": 1,
      "semester": 3,
      "teaching_quality": "Excellent",
      "communication": "Good",
      "subject_knowledge": "Excellent",
      "punctuality": "Good",
      "comments": "Great teacher",
      "submitted_at": "2024-01-20 14:30:00",
      "course_code": "CS301",
      "course_name": "Database Management System",
      "student_name": "John Doe",
      "student_id": "S001"
    }
  ],
  "averages": {
    "avg_teaching": 3.75,
    "avg_communication": 3.50,
    "avg_knowledge": 3.80,
    "avg_punctuality": 3.60,
    "total_feedback": 10
  }
}
```

**Note:** Averages are calculated as:
- Excellent = 4
- Good = 3
- Average = 2
- Poor = 1

---

## Common APIs

### 1. Logout
**Endpoint:** `POST /api/common/logout.php`

**Authentication:** Required (Any session)

**Success Response:**
```json
{
  "success": true,
  "message": "Logged out successfully",
  "redirect": "index.html"
}
```

---

### 2. Forgot Password
**Endpoint:** `POST /api/common/forgot_password.php`

**Request Body:**
```json
{
  "user_id": "S001",
  "user_type": "student",
  "email": "john@bvrit.ac.in"
}
```

**Valid User Types:**
- admin
- student
- faculty

**Success Response:**
```json
{
  "success": true,
  "message": "Password reset token generated. Check your email.",
  "token": "abc123...",
  "user_id": "S001",
  "user_type": "student"
}
```

**Note:** In production, token should be sent via email, not in response

---

### 3. Reset Password
**Endpoint:** `POST /api/common/reset_password.php`

**Request Body:**
```json
{
  "token": "abc123...",
  "new_password": "NewPass@123"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Password reset successfully"
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Invalid or expired token"
}
```

---

## Error Handling

All endpoints return JSON responses with consistent structure:

**Success:**
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {}
}
```

**Error:**
```json
{
  "success": false,
  "message": "Error description"
}
```

---

## HTTP Status Codes

All responses return HTTP 200 OK. Check the `success` field in JSON to determine if operation succeeded.

---

## Session Management

Sessions are created on successful login and destroyed on logout. Session data includes:
- `user_type`: admin, student, or faculty
- `user_id`: User's ID
- `name`: User's name
- `email`: User's email
- Additional role-specific data

---

## Security Notes

1. **Password Storage:** Bcrypt hashing with cost factor 10
2. **SQL Injection:** All queries use prepared statements
3. **XSS Prevention:** All inputs sanitized with htmlspecialchars
4. **CSRF:** Implement CSRF tokens in production
5. **HTTPS:** Use HTTPS in production environment

---

## Rate Limiting

Not implemented in current version. Consider adding in production:
- Login attempts: 5 per 15 minutes
- API calls: 100 per minute per user

---

## Testing with cURL

### Admin Login:
```bash
curl -X POST http://localhost/feedback_system/api/admin/login.php \
  -d "admin_id=admin&password=Admin@123"
```

### Add Student:
```bash
curl -X POST http://localhost/feedback_system/api/admin/add_student.php \
  -b cookies.txt -c cookies.txt \
  -d "student_id=S001&name=John Doe&email=john@bvrit.ac.in&phone=9876543210&department=CSE&semester=3&password=Test@123"
```

---

## Database Schema Reference

### Key Tables:
- `admin` - Admin credentials
- `students` - Student information
- `faculty` - Faculty information
- `departments` - Department master data
- `courses` - Course/subject information
- `faculty_courses` - Faculty-course assignments
- `feedback` - Student feedback records
- `password_reset_tokens` - Password reset tokens

For complete schema, see `database/schema.sql`
