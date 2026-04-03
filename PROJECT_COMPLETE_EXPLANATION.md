# Student Feedback Management System - Complete Project Explanation

## Project Overview
A web-based system for students to submit feedback about faculty members, faculty to view their feedback, and admins to manage the entire system.

---

## Technology Stack

### Frontend (What Users See)
- **HTML** - Structure of web pages
- **CSS** - Styling and design
- **JavaScript** - Interactive functionality
- **Unsplash Images** - Background images for login pages

### Backend (Data & Logic)
**Language Used: JavaScript**

Your backend is entirely written in **JavaScript** and consists of two parts:

#### 1. Client-Side Backend (Browser)
- **File:** `supabase.js`
- **Runs in:** User's web browser
- **What it does:**
  - Connects to Supabase database
  - Handles all business logic (login, validation, data operations)
  - Manages user sessions using localStorage
  - Sends OTP emails via EmailJS
  - Performs password hashing (SHA-256)
  - Validates inputs (email, phone, password)

#### 2. Server-Side Backend (Cloud)
- **Platform:** Supabase (Backend-as-a-Service)
- **Database:** PostgreSQL
- **What it provides:**
  - Automatic REST APIs for all database tables
  - Real-time data synchronization
  - Database hosting and management
  - Built-in security (Row Level Security)
  - No need to write server code!

**How They Work Together:**
```
User Browser
    ↓
JavaScript (supabase.js)
    ↓
Supabase Cloud APIs
    ↓
PostgreSQL Database
```

### Email Service
- **EmailJS** - Sending OTP emails
  - Service ID: service_yww8wxe
  - Template ID: template_fd264pg
  - Public Key: HKhx43qkIhDUqyBog

---

## Backend Architecture Explained

### What is Supabase?
Supabase is a **Backend-as-a-Service (BaaS)** platform. Think of it as:
- A ready-made backend server
- A PostgreSQL database
- Automatic APIs for your tables
- All managed in the cloud

**Traditional Backend vs Your Backend:**

**Traditional Way (PHP/Node.js):**
```
1. Write server code (PHP/Node.js)
2. Create API endpoints manually
3. Write database queries
4. Deploy server
5. Maintain server
```

**Your Way (Supabase):**
```
1. Create database tables in Supabase
2. APIs automatically generated
3. Use JavaScript to call APIs
4. No server deployment needed
5. Supabase maintains everything
```

---

## Deep Dive: supabase.js File

This is your **ONLY backend code file**. Let's break it down:

### Section 1: Configuration
```javascript
const SUPABASE_URL = 'https://sxwcofbbfqqhwqurpbgk.supabase.co';
const SUPABASE_ANON_KEY = 'eyJhbGci...';
```
**What this does:**
- Connects your app to your specific Supabase project
- ANON_KEY allows public access to database (with security rules)

### Section 2: Initialize Supabase Client
```javascript
const { createClient } = supabase;
const db = createClient(SUPABASE_URL, SUPABASE_ANON_KEY);
```
**What this does:**
- Creates a connection object (`db`)
- This `db` object is used for ALL database operations
- Like creating a phone line to talk to the database

### Section 3: Session Management
```javascript
function setSession(userType, userData) {
    localStorage.setItem('user_type', userType);
    localStorage.setItem('user_data', JSON.stringify(userData));
}
```
**What this does:**
- Saves user login info in browser's localStorage
- Keeps user logged in even after page refresh
- Like saving a cookie that remembers who you are

**Example:**
```javascript
// When admin logs in:
setSession('admin', {
    admin_id: 'admin1',
    name: 'John Doe',
    email: 'john@bvrit.ac.in'
});

// Later, check if user is logged in:
const session = getSession();
if (session.user_type === 'admin') {
    // User is admin, show admin dashboard
}
```

### Section 4: Password Security
```javascript
async function hashPassword(password) {
    const encoder = new TextEncoder();
    const data = encoder.encode(password);
    const hashBuffer = await crypto.subtle.digest('SHA-256', data);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
}
```
**What this does:**
- Converts plain password into encrypted hash
- Uses SHA-256 algorithm (very secure)
- Original password cannot be recovered from hash

**Example:**
```javascript
// User enters: "MyPass@123"
// After hashing: "5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8"
// This hash is stored in database, not the actual password
```

### Section 5: Validation Functions
```javascript
function validateEmail(email) {
    return /^[^\s@]+@bvrit\.ac\.in$/.test(email);
}
```
**What this does:**
- Checks if email ends with @bvrit.ac.in
- Returns true or false

**Example:**
```javascript
validateEmail('student@bvrit.ac.in')  // ✅ true
validateEmail('student@gmail.com')     // ❌ false
```

### Section 6: Email OTP Service
```javascript
async function sendOTPEmail(to_email, otp) {
    return emailjs.send(EMAILJS_SERVICE_ID, EMAILJS_TEMPLATE_ID, {
        to_email: to_email,
        passcode: otp
    }, EMAILJS_PUBLIC_KEY);
}
```
**What this does:**
- Sends email with OTP code
- Uses EmailJS service (no email server needed)
- Returns promise (success or failure)

---

## How Backend Operations Work

### Example 1: Admin Login

**Step-by-step flow:**

1. **User enters credentials** (admin-login.html)
```javascript
const admin_id = 'admin1';
const password = 'MyPass@123';
```

2. **Hash the password** (supabase.js)
```javascript
const hashedPassword = await hashPassword(password);
// Result: "5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8"
```

3. **Query Supabase database**
```javascript
const { data, error } = await db
    .from('admin')
    .select('*')
    .eq('admin_id', admin_id)
    .eq('password', hashedPassword)
    .single();
```
**What happens here:**
- `db.from('admin')` - Look in admin table
- `.select('*')` - Get all columns
- `.eq('admin_id', admin_id)` - Where admin_id matches
- `.eq('password', hashedPassword)` - And password matches
- `.single()` - Expect exactly one result

4. **Check result**
```javascript
if (data) {
    // Login successful
    setSession('admin', data);
    window.location.href = 'admin-dashboard.html';
} else {
    // Login failed
    alert('Invalid credentials');
}
```

---

### Example 2: Add New Student

**Step-by-step flow:**

1. **Admin fills form** (add-student.html)
```javascript
const studentData = {
    student_id: 'S001',
    name: 'John Doe',
    email: 'john@bvrit.ac.in',
    phone: '9876543210',
    password: 'Student@123',
    dept_id: 1,
    semester: 3
};
```

2. **Validate inputs** (supabase.js)
```javascript
if (!validateEmail(studentData.email)) {
    alert('Email must be @bvrit.ac.in');
    return;
}

if (!validatePhone(studentData.phone)) {
    alert('Phone must be 10 digits');
    return;
}

if (!validatePassword(studentData.password)) {
    alert('Password too weak');
    return;
}
```

3. **Hash password**
```javascript
studentData.password = await hashPassword(studentData.password);
```

4. **Insert into database**
```javascript
const { data, error } = await db
    .from('students')
    .insert([studentData]);
```
**What happens:**
- Supabase automatically creates INSERT SQL query
- Sends to PostgreSQL database
- Returns success or error

5. **Handle response**
```javascript
if (error) {
    alert('Error: ' + error.message);
} else {
    alert('Student added successfully!');
}
```

---

### Example 3: Submit Feedback

**Step-by-step flow:**

1. **Student fills feedback form** (give-feedback.html)
```javascript
const feedbackData = {
    student_id: 'S001',
    faculty_id: 'F001',
    course_id: 1,
    semester: 3,
    teaching_quality: 'Excellent',
    communication: 'Good',
    subject_knowledge: 'Excellent',
    punctuality: 'Good',
    comments: 'Great teacher!'
};
```

2. **Check if already submitted**
```javascript
const { data: existing } = await db
    .from('feedback')
    .select('feedback_id')
    .eq('student_id', feedbackData.student_id)
    .eq('course_id', feedbackData.course_id)
    .eq('semester', feedbackData.semester);

if (existing && existing.length > 0) {
    alert('You already submitted feedback for this course');
    return;
}
```

3. **Insert feedback**
```javascript
const { data, error } = await db
    .from('feedback')
    .insert([feedbackData]);
```

4. **Show success**
```javascript
if (!error) {
    alert('Feedback submitted successfully!');
    window.location.href = 'student-dashboard.html';
}
```

---

## Database Operations (CRUD)

### CREATE - Add Data
```javascript
// Add new admin
await db.from('admin').insert([{
    admin_id: 'admin2',
    name: 'Jane Smith',
    email: 'jane@bvrit.ac.in',
    phone: '9876543210',
    password: hashedPassword
}]);
```

### READ - Get Data
```javascript
// Get all students
const { data } = await db.from('students').select('*');

// Get specific student
const { data } = await db.from('students')
    .select('*')
    .eq('student_id', 'S001')
    .single();

// Get with filter
const { data } = await db.from('students')
    .select('*')
    .eq('semester', 3)
    .eq('dept_id', 1);
```

### UPDATE - Modify Data
```javascript
// Update student email
await db.from('students')
    .update({ email: 'newemail@bvrit.ac.in' })
    .eq('student_id', 'S001');
```

### DELETE - Remove Data
```javascript
// Delete student
await db.from('students')
    .delete()
    .eq('student_id', 'S001');
```

---

## Why This Backend Approach is Better

### Advantages:

1. **No Server Management**
   - No need to setup Apache/Nginx
   - No need to configure PHP/Node.js
   - Supabase handles everything

2. **Automatic APIs**
   - Don't write API endpoints manually
   - Supabase creates them automatically
   - Just call them from JavaScript

3. **One Language**
   - Only JavaScript (frontend + backend)
   - No need to learn PHP/Python/Java

4. **Easy Deployment**
   - Just upload HTML files
   - No server deployment needed
   - Works immediately

5. **Scalable**
   - Supabase handles traffic automatically
   - Database scales as needed
   - No performance tuning required

6. **Secure**
   - Built-in security features
   - Row Level Security (RLS)
   - Automatic SQL injection prevention

---

## Comparison with PHP Backend

### PHP Way (Old Project):
```
File Structure:
├── api/
│   ├── admin/
│   │   ├── login.php
│   │   ├── add_student.php
│   │   ├── add_faculty.php
│   │   └── (15 more PHP files)
│   ├── student/
│   │   └── (6 PHP files)
│   └── faculty/
│       └── (4 PHP files)
├── config/
│   ├── database.php
│   └── email_config.php
└── (30 HTML files)

Total: 25+ PHP files + config files
```

### Supabase Way (Current Project):
```
File Structure:
├── supabase.js (ONE file!)
├── database/
│   └── supabase_schema.sql
└── (30 HTML files)

Total: 1 JavaScript file
```

**Result:** 25+ PHP files replaced by 1 JavaScript file!

---

## Summary

### Your Backend Stack:
- **Language:** JavaScript
- **Platform:** Supabase (Backend-as-a-Service)
- **Database:** PostgreSQL (managed by Supabase)
- **Email:** EmailJS
- **Code Files:** 1 file (supabase.js)

### How It Works:
1. User interacts with HTML page
2. JavaScript (supabase.js) processes request
3. Calls Supabase API
4. Supabase queries PostgreSQL database
5. Returns data to JavaScript
6. JavaScript updates HTML page

### Key Concept:
**You don't write backend server code. Supabase IS your backend server. You just write JavaScript to talk to it!**

---

## Project Structure

### Frontend Files (HTML Pages)

#### Landing & Authentication Pages:
1. **index.html** - Homepage with 3 module options (Admin/Faculty/Student)
2. **admin-login.html** - Admin login with password or OTP
3. **faculty-login.html** - Faculty login with password or OTP
4. **student-login.html** - Student login with password or OTP

#### Password Reset Pages:
5. **admin_forgot_password.html** - Admin forgot password
6. **admin_reset_password.html** - Admin reset password
7. **faculty-forgot.html** - Faculty forgot password
8. **faculty-reset.html** - Faculty reset password
9. **faculty-verify.html** - Faculty OTP verification
10. **student-forgot.html** - Student forgot password
11. **student-reset.html** - Student reset password
12. **student-verify.html** - Student OTP verification

#### Admin Dashboard Pages:
13. **admin-dashboard.html** - Admin main dashboard with 8 modules
14. **add-student.html** - Add new student
15. **add-faculty.html** - Add new faculty
16. **manage-students.html** - View/Edit/Delete students
17. **manage-faculty.html** - View/Edit/Delete faculty
18. **manage-admins.html** - View/Edit/Delete admins (NEW)
19. **edit-student.html** - Edit student details
20. **edit-faculty.html** - Edit faculty details
21. **manage-courses.html** - Add/View/Delete courses
22. **assign-courses.html** - Assign courses to faculty
23. **view-feedback.html** - View all feedback
24. **view-feedback-details.html** - View detailed feedback
25. **view-feedback-status.html** - View feedback statistics

#### Faculty Dashboard Pages:
26. **faculty-dashboard.html** - Faculty main dashboard
27. **faculty-view-feedback.html** - View feedback received

#### Student Dashboard Pages:
28. **student-dashboard.html** - Student main dashboard
29. **student-profile.html** - View student profile
30. **give-feedback.html** - Submit feedback for faculty

---

### Backend Files

#### Core Backend:
1. **supabase.js** - Main backend configuration file
   - Supabase connection setup
   - EmailJS configuration
   - Helper functions (authentication, validation, password hashing)
   - Session management using localStorage

#### Database:
2. **database/supabase_schema.sql** - Database structure
   - Tables: admin, students, faculty, departments, courses, faculty_courses, feedback, password_reset_tokens

---

## Database Tables

### 1. admin
- admin_id (Primary Key)
- name
- email (@bvrit.ac.in)
- phone (10 digits)
- password (hashed)
- is_active (1 or 0)
- created_at

### 2. students
- student_id (Primary Key)
- name
- email (@bvrit.ac.in)
- phone
- password (hashed)
- dept_id (Foreign Key)
- semester (1-8)
- is_active
- created_at

### 3. faculty
- faculty_id (Primary Key)
- name
- email (@bvrit.ac.in)
- phone
- password (hashed)
- dept_id (Foreign Key)
- is_active
- created_at

### 4. departments
- dept_id (Primary Key)
- dept_code (CSE, IT, ECE, EEE)
- dept_name

### 5. courses
- course_id (Primary Key)
- course_code
- course_name
- dept_id (Foreign Key)
- semester
- created_at

### 6. faculty_courses
- assignment_id (Primary Key)
- faculty_id (Foreign Key)
- course_id (Foreign Key)
- semester
- academic_year
- assigned_at

### 7. feedback
- feedback_id (Primary Key)
- student_id (Foreign Key)
- faculty_id (Foreign Key)
- course_id (Foreign Key)
- semester
- teaching_quality (Excellent/Good/Average/Poor)
- communication (Excellent/Good/Average/Poor)
- subject_knowledge (Excellent/Good/Average/Poor)
- punctuality (Excellent/Good/Average/Poor)
- comments (text)
- submitted_at

### 8. password_reset_tokens
- token_id (Primary Key)
- user_id
- user_type (admin/student/faculty)
- token
- expires_at
- created_at

---

## Key Features

### Admin Features:
1. Login with password or OTP
2. Add/Edit/Delete students
3. Add/Edit/Delete faculty
4. Add/Edit/Delete admins (NEW)
5. Manage courses (Add/Delete)
6. Assign courses to faculty
7. View all feedback
8. View faculty-specific feedback
9. Password reset via email OTP

### Faculty Features:
1. Login with password or OTP
2. View dashboard
3. View feedback received from students
4. See average ratings
5. Password reset via email OTP

### Student Features:
1. Login with password or OTP
2. View dashboard
3. View profile
4. View assigned courses
5. Submit feedback for faculty
6. Can only submit once per course
7. Password reset via email OTP

---

## Authentication Flow

### Password Login:
1. User enters ID and password
2. System checks Supabase database
3. Password is hashed and compared
4. Session created in localStorage
5. Redirect to dashboard

### OTP Login:
1. User clicks "Login with OTP"
2. Enters email address
3. System generates 6-digit OTP
4. OTP sent via EmailJS to user's email
5. User enters OTP
6. System verifies OTP
7. Session created
8. Redirect to dashboard

---

## Security Features

1. **Password Hashing** - SHA-256 encryption
2. **Email Validation** - Must be @bvrit.ac.in
3. **Phone Validation** - Exactly 10 digits
4. **Password Strength** - Min 6 chars, uppercase, lowercase, special char
5. **Session Management** - localStorage with user type checking
6. **OTP Expiry** - OTPs valid for 10 minutes
7. **Input Sanitization** - All inputs validated
8. **Duplicate Prevention** - Students can't submit feedback twice for same course

---

## How Data Flows

### Example: Student Submits Feedback

1. **Student logs in** → supabase.js checks students table
2. **Dashboard loads** → Fetches courses from faculty_courses table
3. **Student clicks "Give Feedback"** → Opens give-feedback.html
4. **Selects course and faculty** → Dropdown populated from database
5. **Fills ratings and comments** → Form validation in JavaScript
6. **Clicks Submit** → Data sent to Supabase feedback table
7. **Success message** → Feedback stored, student can't submit again for that course

### Example: Admin Adds New Student

1. **Admin logs in** → supabase.js checks admin table
2. **Clicks "Add Student"** → Opens add-student.html
3. **Fills form** → Validates email (@bvrit.ac.in), phone (10 digits), password strength
4. **Clicks Submit** → Password hashed, data sent to Supabase students table
5. **Success** → Student can now login

---

## Deployment Options

### Current Status:
- ✅ Frontend deployed on Render: https://feedback-system-bwev.onrender.com
- ✅ Backend on Supabase (cloud database)
- ✅ Emails via EmailJS (cloud service)

### How It Works Live:
1. **Frontend** - HTML/CSS/JS files hosted on Render
2. **Backend** - Supabase handles all database operations
3. **No PHP needed** - Everything runs in browser + Supabase
4. **No XAMPP needed** - Supabase is the server

---

## Local Development (Your Laptop)

### What You Need:
- ✅ Web browser (Chrome/Edge)
- ✅ Text editor (VS Code/Notepad++)
- ✅ Internet connection (to connect to Supabase)

### How to Run Locally:
1. Open any HTML file in browser
2. JavaScript connects to Supabase automatically
3. No XAMPP needed!
4. No local server needed!

### Why No XAMPP Needed:
- Your old project used PHP (needed XAMPP)
- New project uses Supabase (cloud-based)
- Everything runs in browser
- Database is on Supabase servers

---

## File Count
- **Total HTML files:** 30
- **Backend files:** 1 (supabase.js)
- **Database files:** 1 (supabase_schema.sql)
- **Config files:** 0 (credentials in supabase.js)

---

## Email Configuration

### EmailJS Setup:
- Service: Gmail
- From: patilbhanu50@gmail.com
- Template: Custom OTP template
- All emails sent to @bvrit.ac.in addresses

### OTP Features:
- 6-digit random code
- Valid for 10 minutes
- Sent to real email addresses from database
- Used for login and password reset

---

## Design Features

### Login Pages Design:
- Split-screen layout
- Left side: Background image with gradient overlay
  - Student: Students studying
  - Faculty: Classroom/teaching
  - Admin: Office/professional
- Right side: Login form
- "Login with OTP" option
- Forgot password link

### Dashboard Design:
- Card-based layout
- Icon for each module
- Hover effects
- Responsive design
- Clean and modern UI

---

## Validation Rules

### Email:
- Must end with @bvrit.ac.in
- Valid email format

### Phone:
- Exactly 10 digits
- Numbers only

### Password:
- Minimum 6 characters
- At least 1 uppercase letter
- At least 1 lowercase letter
- At least 1 special character

### Name:
- Letters and spaces only
- No numbers or special characters

### Semester:
- Must be between 1 and 8

---

## User Roles & Permissions

### Admin Can:
- ✅ Add/Edit/Delete students
- ✅ Add/Edit/Delete faculty
- ✅ Add/Edit/Delete admins
- ✅ Manage courses
- ✅ Assign courses to faculty
- ✅ View all feedback
- ✅ View faculty-specific feedback

### Faculty Can:
- ✅ View their own feedback
- ✅ See average ratings
- ✅ View student comments
- ❌ Cannot edit or delete feedback
- ❌ Cannot see other faculty feedback

### Student Can:
- ✅ View their profile
- ✅ View assigned courses
- ✅ Submit feedback for each course
- ✅ Submit only once per course
- ❌ Cannot edit submitted feedback
- ❌ Cannot see other students' feedback

---

## How to Make Changes

### To Update Frontend:
1. Edit HTML files directly
2. Save changes
3. Refresh browser to see changes
4. No compilation needed

### To Update Backend Logic:
1. Edit supabase.js file
2. Save changes
3. Refresh browser
4. Changes apply immediately

### To Update Database:
1. Go to Supabase dashboard
2. Use Table Editor or SQL Editor
3. Make changes
4. Changes reflect immediately in app

---

## Deployment Process

### Current Deployment:
1. **Frontend:** Hosted on Render (static site)
2. **Backend:** Supabase (cloud database)
3. **Emails:** EmailJS (cloud service)

### To Update Live Site:
1. Make changes to files
2. Push to GitHub:
   ```bash
   git add .
   git commit -m "Your message"
   git push origin main
   ```
3. Render auto-deploys from GitHub
4. Changes live in 2-3 minutes

---

## Summary

**Your project is now a modern cloud-based application:**
- ✅ No PHP needed
- ✅ No XAMPP needed for live site
- ✅ No local server needed
- ✅ Everything runs in browser + cloud
- ✅ Easy to deploy and maintain
- ✅ Scalable and fast

**The backend is Supabase, not PHP anymore!**
