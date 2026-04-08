CREATE DATABASE IF NOT EXISTS portfolio_db;
USE portfolio_db;

-- Profile
CREATE TABLE IF NOT EXISTS profile (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    tagline VARCHAR(200) NOT NULL,
    bio TEXT NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    location VARCHAR(100),
    linkedin_url VARCHAR(255),
    github_url VARCHAR(255)
);

-- Skills
CREATE TABLE IF NOT EXISTS skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0
);

-- Projects
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0
);

-- Internships
CREATE TABLE IF NOT EXISTS internships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company VARCHAR(150) NOT NULL,
    role VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0
);

-- Contact messages (saved when visitor submits the form)
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_name VARCHAR(100) NOT NULL,
    sender_email VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed profile
INSERT INTO profile (full_name, tagline, bio, email, phone, location, linkedin_url, github_url)
SELECT
    'Monil Patel',
    'Web & Android Developer | CSE Student',
    'Passionate Computer Science student with strong foundation in OOP, Data Structures, DBMS and scalable application design. I build clean, modern and user-friendly web & Android applications.',
    'monilpatel154@gmail.com',
    '8849740412',
    'Bengaluru, India',
    'https://www.linkedin.com/in/monil-patel-946845255/',
    'https://github.com/Monilpatel154'
WHERE NOT EXISTS (SELECT 1 FROM profile WHERE email = 'monilpatel154@gmail.com');

-- Seed skills
INSERT INTO skills (name, sort_order) VALUES
    ('JavaScript', 1), ('TypeScript', 2), ('Kotlin', 3), ('Python', 4),
    ('Next.js', 5), ('HTML', 6), ('CSS', 7), ('PostgreSQL', 8),
    ('Git', 9), ('Power BI', 10)
ON DUPLICATE KEY UPDATE sort_order = VALUES(sort_order);

-- Seed projects
INSERT INTO projects (title, description, sort_order)
SELECT 'Multi-Transportation Android App', 'Modular Android application with structured navigation and reusable components.', 1
WHERE NOT EXISTS (SELECT 1 FROM projects WHERE title = 'Multi-Transportation Android App');

INSERT INTO projects (title, description, sort_order)
SELECT 'Student Dashboard - PostgreSQL', 'Designed optimized database schema with efficient SQL queries.', 2
WHERE NOT EXISTS (SELECT 1 FROM projects WHERE title = 'Student Dashboard - PostgreSQL');

INSERT INTO projects (title, description, sort_order)
SELECT 'Socialz Web Platform', 'Responsive frontend built using TypeScript and Tailwind CSS.', 3
WHERE NOT EXISTS (SELECT 1 FROM projects WHERE title = 'Socialz Web Platform');

-- Seed internships
INSERT INTO internships (company, role, description, sort_order)
SELECT 'RTsense', 'Web Development Intern', 'Improved UI, fixed production bugs and enhanced system performance.', 1
WHERE NOT EXISTS (SELECT 1 FROM internships WHERE company = 'RTsense');

INSERT INTO internships (company, role, description, sort_order)
SELECT 'Microsoft Elevate', 'Power BI Internship', 'Built KPI dashboards and generated business insights.', 2
WHERE NOT EXISTS (SELECT 1 FROM internships WHERE company = 'Microsoft Elevate');
