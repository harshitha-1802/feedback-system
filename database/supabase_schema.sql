-- =============================================
-- Supabase/PostgreSQL Schema
-- Paste this in Supabase SQL Editor and Run
-- =============================================

-- Admin Table
CREATE TABLE IF NOT EXISTS admin (
    admin_id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Departments Table
CREATE TABLE IF NOT EXISTS departments (
    dept_id SERIAL PRIMARY KEY,
    dept_code VARCHAR(10) UNIQUE NOT NULL,
    dept_name VARCHAR(100) NOT NULL
);

-- Courses Table
CREATE TABLE IF NOT EXISTS courses (
    course_id SERIAL PRIMARY KEY,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    dept_id INT NOT NULL REFERENCES departments(dept_id) ON DELETE CASCADE,
    semester INT NOT NULL,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Students Table
CREATE TABLE IF NOT EXISTS students (
    student_id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    dept_id INT NOT NULL REFERENCES departments(dept_id) ON DELETE CASCADE,
    semester INT NOT NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Faculty Table
CREATE TABLE IF NOT EXISTS faculty (
    faculty_id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    dept_id INT NOT NULL REFERENCES departments(dept_id) ON DELETE CASCADE,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Faculty Course Assignment Table
CREATE TABLE IF NOT EXISTS faculty_courses (
    assignment_id SERIAL PRIMARY KEY,
    faculty_id VARCHAR(50) NOT NULL REFERENCES faculty(faculty_id) ON DELETE CASCADE,
    course_id INT NOT NULL REFERENCES courses(course_id) ON DELETE CASCADE,
    semester INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    assigned_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(faculty_id, course_id, semester, academic_year)
);

-- Feedback Table
CREATE TABLE IF NOT EXISTS feedback (
    feedback_id SERIAL PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL REFERENCES students(student_id) ON DELETE CASCADE,
    faculty_id VARCHAR(50) NOT NULL REFERENCES faculty(faculty_id) ON DELETE CASCADE,
    course_id INT NOT NULL REFERENCES courses(course_id) ON DELETE CASCADE,
    semester INT NOT NULL,
    teaching_quality VARCHAR(10) CHECK (teaching_quality IN ('Excellent','Good','Average','Poor')) NOT NULL,
    communication VARCHAR(10) CHECK (communication IN ('Excellent','Good','Average','Poor')) NOT NULL,
    subject_knowledge VARCHAR(10) CHECK (subject_knowledge IN ('Excellent','Good','Average','Poor')) NOT NULL,
    punctuality VARCHAR(10) CHECK (punctuality IN ('Excellent','Good','Average','Poor')) NOT NULL,
    comments TEXT,
    submitted_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(student_id, faculty_id, course_id, semester)
);

-- Password Reset Tokens Table
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    token_id SERIAL PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    user_type VARCHAR(10) CHECK (user_type IN ('admin','student','faculty')) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT NOW()
);

-- =============================================
-- Seed Data
-- =============================================

INSERT INTO departments (dept_code, dept_name) VALUES
('CSE', 'Computer Science and Engineering'),
('IT', 'Information Technology'),
('ECE', 'Electronics and Communication Engineering'),
('EEE', 'Electrical and Electronics Engineering')
ON CONFLICT DO NOTHING;

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
('EEE302', 'Control Systems', 4, 3)
ON CONFLICT DO NOTHING;

-- =============================================
-- Disable Row Level Security (for development)
-- Enable and configure properly before going live
-- =============================================
ALTER TABLE admin DISABLE ROW LEVEL SECURITY;
ALTER TABLE departments DISABLE ROW LEVEL SECURITY;
ALTER TABLE courses DISABLE ROW LEVEL SECURITY;
ALTER TABLE students DISABLE ROW LEVEL SECURITY;
ALTER TABLE faculty DISABLE ROW LEVEL SECURITY;
ALTER TABLE faculty_courses DISABLE ROW LEVEL SECURITY;
ALTER TABLE feedback DISABLE ROW LEVEL SECURITY;
ALTER TABLE password_reset_tokens DISABLE ROW LEVEL SECURITY;
