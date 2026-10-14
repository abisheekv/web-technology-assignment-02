CREATE DATABASE IF NOT EXISTS campus_portal;
USE campus_portal;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password CHAR(64) NOT NULL,              -- SHA-256 hash
  full_name VARCHAR(100) NOT NULL,
  role VARCHAR(20) DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  roll_no VARCHAR(30) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  course VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS student_documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  file_type VARCHAR(100) NOT NULL,
  file_size INT NOT NULL,
  doc_category VARCHAR(50) NOT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

INSERT INTO users (username, password, full_name, role)
VALUES ('admin', SHA2('admin123', 256), 'Administrator', 'admin')
ON DUPLICATE KEY UPDATE id = id;

INSERT INTO students (name, roll_no, email, course) VALUES
('Akilan MK', '24CS014', 'akilan.mk@kpriet.ac.in', 'B.E. Computer Science and Engineering'),
('Harish Kumar', '24CS028', 'harish.k@kpriet.ac.in', 'B.Tech Information Technology'),
('Naveen Prasath', '24CS039', 'naveen.p@kpriet.ac.in', 'B.Tech Artificial Intelligence and Data Science')
ON DUPLICATE KEY UPDATE id = id;
