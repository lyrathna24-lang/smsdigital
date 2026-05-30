SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ១. តារាងឆ្នាំសិក្សា (Academic Years)
CREATE TABLE IF NOT EXISTS academic_years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year_name VARCHAR(50) NOT NULL,
    is_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ២. តារាងសាលារៀន (Schools)
CREATE TABLE IF NOT EXISTS schools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    province VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    commune VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ៣. តារាងថ្នាក់រៀន (Classes)
CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    class_name VARCHAR(50) NOT NULL,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ៤. តារាងគ្រូបង្រៀន (Teachers)
CREATE TABLE IF NOT EXISTS teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20),
    start_date DATE,
    profile_picture TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ៥. តារាងប្រវត្តិរូបសិស្ស (Students)
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    student_code VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    gender ENUM('ប្រុស', 'ស្រី') NOT NULL,
    dob DATE,
    parent_phone VARCHAR(20),
    profile_picture TEXT,
    status ENUM('កំពុងសិក្សា', 'បោះបង់', 'ផ្ទេរ') DEFAULT 'កំពុងសិក្សា',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ៦. តារាងអ្នកប្រើប្រាស់ (Users)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'school') DEFAULT 'school',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ៦ក. តារាងតូកិនឧបករណ៍ (Device Tokens for Multi-Device Login)
CREATE TABLE IF NOT EXISTS device_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(128) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    device_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(user_id),
    INDEX(expires_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ៧. តារាងអវត្តមានសិស្ស (Attendance)
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('វត្តមាន', 'អវត្តមាន', 'ច្បាប់') NOT NULL,
    reason TEXT,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ៨. តារាងពិន្ទុប្រចាំខែ (Monthly Scores)
CREATE TABLE IF NOT EXISTS monthly_scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    semester ENUM('1', '2') NOT NULL,
    month_name VARCHAR(50) NOT NULL,
    total_score DECIMAL(10,2) DEFAULT 0,
    average DECIMAL(5,2) DEFAULT 0,
    rank INT,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ៩. តារាងពិន្ទុប្រចាំឆមាស (Semester Exams)
CREATE TABLE IF NOT EXISTS semester_exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    semester ENUM('1', '2') NOT NULL,
    total_score DECIMAL(10,2) DEFAULT 0,
    average DECIMAL(5,2) DEFAULT 0,
    rank INT,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ១០. តារាងការកំណត់ (Site Settings)
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ១១. តារាងចំណាត់ថ្នាក់ប្រចាំខែ (Monthly Rankings)
CREATE TABLE IF NOT EXISTS monthly_rankings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    month_name VARCHAR(50) NOT NULL,
    semester ENUM('1', '2') NOT NULL,
    total_score DECIMAL(10,2),
    average DECIMAL(10,2),
    rank INT,
    grade VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ១២. តារាងកិត្តិយស (Honor Rolls)
CREATE TABLE IF NOT EXISTS honor_rolls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    month_name VARCHAR(50) NOT NULL,
    semester ENUM('1', '2') NOT NULL,
    total_score DECIMAL(10,2),
    average DECIMAL(10,2),
    rank INT,
    grade VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- បញ្ចូលទិន្នន័យ (Dummy Data)
INSERT IGNORE INTO academic_years (year_name, is_active) VALUES ('២០២៥-២០២៦', TRUE);
INSERT IGNORE INTO schools (school_code, name, province, district, commune) VALUES ('០៧០៧០៥០៥០៥៧', 'សាលាបឋមសិក្សា ព្រៃថ្មី', 'កំពត', 'ទឹកឈូ', 'កណ្តាល');
INSERT IGNORE INTO classes (school_id, academic_year_id, class_name) VALUES (1, 1, '៥ ក'), (1, 1, '៥ ខ');
INSERT IGNORE INTO teachers (class_id, name, phone_number, start_date) VALUES (1, 'នាង នី', '0964531036', '2025-11-01');
INSERT IGNORE INTO students (class_id, student_code, first_name, last_name, gender, dob, parent_phone) VALUES (1, 'STU-001', 'សុខ', 'សាន្ត', 'ប្រុស', '2015-05-12', '012345678'), (1, 'STU-002', 'ចាន់', 'ធីតា', 'ស្រី', '2015-08-20', '098765432');

SET FOREIGN_KEY_CHECKS = 1;