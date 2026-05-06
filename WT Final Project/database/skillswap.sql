-- SkillSwap Database Schema
-- Run this file in phpMyAdmin or MySQL CLI

CREATE DATABASE IF NOT EXISTS skillswap CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE skillswap;

-- --------------------------------------------------------
-- Table: colleges
-- --------------------------------------------------------
CREATE TABLE colleges (
    college_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    city VARCHAR(100),
    state VARCHAR(100),
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8)
);

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    bio TEXT,
    avatar VARCHAR(255) DEFAULT NULL,
    college VARCHAR(150) DEFAULT NULL,
    college_id INT DEFAULT NULL,
    availability JSON DEFAULT NULL,
    avg_rating DECIMAL(3,2) DEFAULT 0.00,
    total_reviews INT DEFAULT 0,
    status ENUM('active','suspended','pending') DEFAULT 'active',
    role ENUM('user','admin') DEFAULT 'user',
    latitude DECIMAL(10,8) DEFAULT NULL,
    longitude DECIMAL(11,8) DEFAULT NULL,
    location_updated_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (college_id) REFERENCES colleges(college_id) ON DELETE SET NULL
);

-- --------------------------------------------------------
-- Table: skill_categories
-- --------------------------------------------------------
CREATE TABLE skill_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50) DEFAULT '📚',
    color VARCHAR(20) DEFAULT '#6C63FF',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- Table: skills (offered by users)
-- --------------------------------------------------------
CREATE TABLE skills (
    skill_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    credit_value INT DEFAULT 1,
    mode ENUM('online','in-person','both') DEFAULT 'both',
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES skill_categories(category_id)
);

-- --------------------------------------------------------
-- Table: skill_wants (skills users want to learn)
-- --------------------------------------------------------
CREATE TABLE skill_wants (
    want_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES skill_categories(category_id),
    UNIQUE KEY unique_user_category (user_id, category_id)
);

-- --------------------------------------------------------
-- Table: swap_requests
-- --------------------------------------------------------
CREATE TABLE swap_requests (
    swap_id INT AUTO_INCREMENT PRIMARY KEY,
    requester_id INT NOT NULL,
    receiver_id INT NOT NULL,
    requester_skill_id INT NOT NULL,
    receiver_skill_id INT NOT NULL,
    requester_credits INT DEFAULT 1,
    receiver_credits INT DEFAULT 1,
    message TEXT,
    status ENUM('pending','accepted','declined','completed','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (requester_id) REFERENCES users(user_id),
    FOREIGN KEY (receiver_id) REFERENCES users(user_id),
    FOREIGN KEY (requester_skill_id) REFERENCES skills(skill_id),
    FOREIGN KEY (receiver_skill_id) REFERENCES skills(skill_id)
);

-- --------------------------------------------------------
-- Table: sessions
-- --------------------------------------------------------
CREATE TABLE sessions (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    swap_id INT NOT NULL,
    scheduled_at DATETIME DEFAULT NULL,
    meet_link VARCHAR(500) DEFAULT NULL,
    meet_location TEXT DEFAULT NULL,
    status ENUM('scheduled','completed','cancelled','no_show') DEFAULT 'scheduled',
    completed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (swap_id) REFERENCES swap_requests(swap_id) ON DELETE CASCADE
);

-- --------------------------------------------------------
-- Table: messages
-- --------------------------------------------------------
CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    swap_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (swap_id) REFERENCES swap_requests(swap_id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(user_id)
);

-- --------------------------------------------------------
-- Table: reviews
-- --------------------------------------------------------
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    swap_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    reviewee_id INT NOT NULL,
    rating TINYINT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (swap_id) REFERENCES swap_requests(swap_id),
    FOREIGN KEY (reviewer_id) REFERENCES users(user_id),
    FOREIGN KEY (reviewee_id) REFERENCES users(user_id),
    UNIQUE KEY unique_review (swap_id, reviewer_id),
    CHECK (rating BETWEEN 1 AND 5)
);

-- --------------------------------------------------------
-- Table: notifications
-- --------------------------------------------------------
CREATE TABLE notifications (
    notif_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    ref_id INT DEFAULT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- --------------------------------------------------------
-- Table: reports
-- --------------------------------------------------------
CREATE TABLE reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id INT NOT NULL,
    reported_id INT NOT NULL,
    swap_id INT DEFAULT NULL,
    reason ENUM('no_show','harassment','fake_skill','spam','other') NOT NULL,
    description TEXT,
    status ENUM('pending','reviewed','resolved','dismissed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(user_id),
    FOREIGN KEY (reported_id) REFERENCES users(user_id)
);

-- ============================================================
-- SEED DATA
-- ============================================================

-- Colleges (Bengaluru + popular cities)
INSERT INTO colleges (name, city, state, latitude, longitude) VALUES
('Jain University', 'Bengaluru', 'Karnataka', 12.9716, 77.5946),
('RV College of Engineering', 'Bengaluru', 'Karnataka', 12.9232, 77.4985),
('BMS College of Engineering', 'Bengaluru', 'Karnataka', 12.9423, 77.5641),
('Christ University', 'Bengaluru', 'Karnataka', 12.9354, 77.6135),
('PES University', 'Bengaluru', 'Karnataka', 12.9154, 77.5063),
('Manipal Institute of Technology', 'Manipal', 'Karnataka', 13.3524, 74.7936),
('VIT University', 'Vellore', 'Tamil Nadu', 12.9696, 79.1559),
('SRM Institute of Science and Technology', 'Chennai', 'Tamil Nadu', 12.8231, 80.0444),
('Amrita Vishwa Vidyapeetham', 'Coimbatore', 'Tamil Nadu', 11.0168, 76.9558),
('Other', 'Other', 'India', 20.5937, 78.9629);

-- Skill Categories
INSERT INTO skill_categories (name, icon, color) VALUES
('Tech', '💻', '#6C63FF'),
('Design', '🎨', '#FF6584'),
('Languages', '🗣️', '#43B78D'),
('Music', '🎵', '#F59E0B'),
('Academics', '📚', '#3B82F6'),
('Soft Skills', '🤝', '#8B5CF6'),
('Fitness', '💪', '#EF4444'),
('Creative', '✨', '#EC4899'),
('Cooking', '🍳', '#F97316'),
('Life Skills', '🌟', '#14B8A6');

-- Admin user (password: admin123)
INSERT INTO users (name, email, password, role, college, status) VALUES
('Admin', 'admin@skillswap.com', '$2y$12$BfSpYGj8KsxHaV3W5hQaJOsO2TvJ8cCKXL1rF5X9N7YcU8MkJyMbC', 'admin', 'SkillSwap HQ', 'active');

-- Demo users (password: password123 for all demo accounts)
INSERT INTO users (name, email, password, role, college, college_id, bio, avg_rating, total_reviews, latitude, longitude) VALUES
('Arjun Sharma', 'arjun@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'Jain University', 1, 'CSE student passionate about web dev and guitar. I can teach React, Node.js, and basic guitar chords.', 4.50, 6, 12.9716, 77.5946),
('Priya Nair', 'priya@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'Christ University', 4, 'Design student who loves UI/UX and Bharatanatyam dance. Looking to learn Python and Data Science.', 4.80, 10, 12.9354, 77.6135),
('Rahul Verma', 'rahul@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'RV College of Engineering', 2, 'Mechanical engineering student who teaches guitar and Rubik`s cube. Want to learn web development.', 4.20, 5, 12.9232, 77.4985),
('Sneha Patel', 'sneha@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'PES University', 5, 'Finance and economics enthusiast. Can help with accountancy, Excel, and Hindi. Want to learn Figma.', 4.60, 8, 12.9154, 77.5063),
('Dev Kumar', 'dev@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'BMS College of Engineering', 3, 'Full-stack developer who loves teaching Python and SQL. Looking to improve public speaking skills.', 4.90, 15, 12.9423, 77.5641);

-- Skills offered by demo users
INSERT INTO skills (user_id, category_id, title, description, credit_value, mode) VALUES
(2, 1, 'React.js Web Development', 'I can teach React from basics to advanced hooks, state management with Context API, and building real projects.', 2, 'both'),
(2, 1, 'Node.js & Express', 'Backend development with Node.js, REST APIs, and Express framework. Includes MySQL/MongoDB integration.', 2, 'online'),
(2, 4, 'Acoustic Guitar Basics', 'Learn basic chords, strumming patterns, and play your first 5 songs in 4 sessions.', 1, 'in-person'),
(3, 2, 'Figma UI/UX Design', 'Complete Figma tutorial from basics to advanced prototyping. Includes design systems and component libraries.', 2, 'online'),
(3, 2, 'Canva & Social Media Design', 'Create stunning social media posts, presentations, and brand identity using Canva.', 1, 'online'),
(4, 4, 'Guitar - Intermediate Level', 'Scales, lead guitar, and song covers. For students who already know basic chords.', 2, 'in-person'),
(4, 1, 'Rubik`s Cube Solving', 'Learn to solve 3x3 Rubik`s cube using beginner`s method. Guaranteed in 2 sessions.', 1, 'both'),
(5, 5, 'Accountancy & Finance', 'Financial accounting, balance sheets, and basic economics. Great for commerce students.', 2, 'both'),
(5, 1, 'Advanced Excel & VBA', 'Formulas, pivot tables, VLOOKUP, macros, and data analysis with Excel.', 2, 'online'),
(6, 1, 'Python Programming', 'Python from scratch to intermediate. Covers OOP, file handling, and libraries like pandas and numpy.', 2, 'both'),
(6, 1, 'MySQL & Database Design', 'Relational database design, normalization, complex queries, and stored procedures.', 2, 'online');

-- Skill wants
INSERT INTO skill_wants (user_id, category_id, description) VALUES
(2, 2, 'Want to learn UI/UX design and Figma to enhance my frontend skills'),
(2, 6, 'Looking to improve public speaking for hackathon presentations'),
(3, 1, 'Want to learn Python and Data Science for my portfolio'),
(3, 5, 'Interested in basic finance and accountancy'),
(4, 1, 'Want to learn web development - HTML, CSS, JavaScript, React'),
(4, 2, 'Interested in learning UI/UX and Figma'),
(5, 2, 'Want to learn Figma for creating financial dashboards'),
(5, 3, 'Interested in learning French or Japanese'),
(6, 6, 'Want to improve public speaking and presentation skills'),
(6, 4, 'Always wanted to learn guitar');

-- Sample swap requests
INSERT INTO swap_requests (requester_id, receiver_id, requester_skill_id, receiver_skill_id, requester_credits, receiver_credits, message, status) VALUES
(2, 3, 1, 4, 2, 2, 'Hi Priya! I noticed you teach Figma and I teach React. Want to swap skills? I can help you build React projects and you can help me with UI design.', 'accepted'),
(4, 6, 8, 10, 2, 2, 'Hey Dev! I can teach you Accountancy and you can teach me Python. Sounds fair?', 'pending'),
(6, 2, 10, 3, 2, 1, 'Hi Arjun! I can teach Python and in return I want to learn guitar from you. Interested?', 'completed');

-- Session for completed swap
INSERT INTO sessions (swap_id, scheduled_at, meet_link, status, completed_at) VALUES
(3, '2026-04-05 10:00:00', 'https://meet.google.com/abc-defg-hij', 'completed', '2026-04-05 12:00:00');

-- Reviews for completed swap
INSERT INTO reviews (swap_id, reviewer_id, reviewee_id, rating, comment) VALUES
(3, 6, 2, 5, 'Arjun was an amazing guitar teacher! Very patient and the session was super productive.'),
(3, 2, 6, 5, 'Dev explained Python concepts brilliantly. His teaching style is very clear and practical.');

-- Sample notifications
INSERT INTO notifications (user_id, type, ref_id, message) VALUES
(3, 'swap_request', 1, 'Arjun Sharma sent you a skill swap request'),
(6, 'swap_request', 2, 'Sneha Patel sent you a skill swap request'),
(2, 'swap_accepted', 1, 'Priya Nair accepted your swap request'),
(2, 'review_received', 1, 'Dev Kumar left you a 5-star review');
