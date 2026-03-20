-- Student Feedback Management System Database Schema
-- Run this in phpMyAdmin after creating database 'feedback_system'

CREATE DATABASE IF NOT EXISTS feedback_system;
USE feedback_system;

-- Admin Table
CREATE TABLE IF NOT EXISTS admin (
    admin_id VARCHAR(50) PRIMARY KEY,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Departments Table
CREATE TABLE IF NOT EXISTS departments (
    dept_id INT AUTO_INCREMENT PRIMARY KEY,
    dept_code VARCHAR(10) UNIQUE NOT NULL,
    dept_name VARCHAR(100) NOT NULL
);

-- Courses/Subjects Table
CREATE TABLE IF NOT EXISTS courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    dept_id INT NOT NULL,
    semester INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dept_id) REFERENCES departments(dept_id) ON DELETE CASCADE
);

-- Students Table
CREATE TABLE IF NOT EXISTS students (
    student_id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    dept_id INT NOT NULL,
    semester INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dept_id) REFERENCES departments(dept_id) ON DELETE CASCADE
);

-- Faculty Table
CREATE TABLE IF NOT EXISTS faculty (
    faculty_id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    dept_id INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dept_id) REFERENCES departments(dept_id) ON DELETE CASCADE
);

-- Faculty Course Assignment Table (Many-to-Many relationship)
CREATE TABLE IF NOT EXISTS faculty_courses (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id VARCHAR(50) NOT NULL,
    course_id INT NOT NULL,
    semester INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES faculty(faculty_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (faculty_id, course_id, semester, academic_year)
);

-- Feedback Table
CREATE TABLE IF NOT EXISTS feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    faculty_id VARCHAR(50) NOT NULL,
    course_id INT NOT NULL,
    semester INT NOT NULL,
    teaching_quality ENUM('Excellent', 'Good', 'Average', 'Poor') NOT NULL,
    communication ENUM('Excellent', 'Good', 'Average', 'Poor') NOT NULL,
    subject_knowledge ENUM('Excellent', 'Good', 'Average', 'Poor') NOT NULL,
    punctuality ENUM('Excellent', 'Good', 'Average', 'Poor') NOT NULL,
    comments TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (faculty_id) REFERENCES faculty(faculty_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    UNIQUE KEY unique_feedback (student_id, faculty_id, course_id, semester)
);

-- Password Reset Tokens Table
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    token_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    user_type ENUM('admin', 'student', 'faculty') NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_user (user_id, user_type)
);

-- Insert Default Departments
INSERT INTO departments (dept_code, dept_name) VALUES
('CSE', 'Computer Science and Engineering'),
('IT', 'Information Technology'),
('ECE', 'Electronics and Communication Engineering'),
('EEE', 'Electrical and Electronics Engineering');

-- Note: Create your admin account manually after setup
-- You can use the admin panel or insert directly with a hashed password

-- Insert Sample Courses
INSERT INTO courses (course_code, course_name, dept_id, semester) VALUES
('CS301', 'Database Management System', 1, 3),
('CS302', 'Java Programming', 1, 3),
('CS303', 'Python Programming', 1, 3),
('CS304', 'Data Structures', 1, 3),
('CS501', 'Machine Learning', 1, 5),
('CS502', 'Web Technologies', 1, 5),
('IT301', 'Software Engineering', 2, 3),
('IT302', 'Operating Systems', 2, 3),
('ECE301', 'Digital Signal Processing', 3, 3),
('ECE302', 'Microprocessors', 3, 3),
('EEE301', 'Power Systems', 4, 3),
('EEE302', 'Control Systems', 4, 3);
