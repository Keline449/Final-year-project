-- =============================================================================
-- Evaluation Management System (EMS) - Full Website Database
-- =============================================================================
-- Database name : ems_db
-- Engine        : MySQL / MariaDB (XAMPP)
-- Charset       : utf8mb4
--
-- HOW TO IMPORT (choose one):
--   1. phpMyAdmin: http://localhost/phpmyadmin → Import → select this file → Go
--   2. Command line (from project folder):
--        C:\xampp\mysql\bin\mysql.exe -u root < database\schema.sql
--
-- Connection settings (edit config/database.php if needed):
--   Host: localhost | User: root | Password: (empty) | Database: ems_db
-- =============================================================================

CREATE DATABASE IF NOT EXISTS ems_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ems_db;

-- Drop existing tables (safe re-import; order respects foreign keys)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS answers;
DROP TABLE IF EXISTS exam_submissions;
DROP TABLE IF EXISTS questions;
DROP TABLE IF EXISTS evaluations;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS departments;
SET FOREIGN_KEY_CHECKS = 1;

-- -----------------------------------------------------------------------------
-- departments: academic units
-- -----------------------------------------------------------------------------
CREATE TABLE departments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_departments_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- users: students and lecturers (registration & login)
-- -----------------------------------------------------------------------------
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student', 'lecturer') NOT NULL,
    department_id INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role (role),
    KEY idx_users_department (department_id),
    CONSTRAINT fk_users_department
        FOREIGN KEY (department_id) REFERENCES departments(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- evaluations: exams/forms created by lecturers
-- -----------------------------------------------------------------------------
CREATE TABLE evaluations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lecturer_id INT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    form_file VARCHAR(255) NULL COMMENT 'Uploaded PDF/DOC in uploads/',
    exam_start_time DATETIME NULL,
    exam_end_time DATETIME NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    results_published TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_evaluations_lecturer (lecturer_id),
    KEY idx_evaluations_active (is_active),
    CONSTRAINT fk_evaluations_lecturer
        FOREIGN KEY (lecturer_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- questions: items belonging to an evaluation
-- -----------------------------------------------------------------------------
CREATE TABLE questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evaluation_id INT UNSIGNED NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('multiple_choice', 'short_answer', 'essay') NOT NULL DEFAULT 'short_answer',
    options_json TEXT NULL COMMENT 'JSON array for multiple_choice options',
    points INT UNSIGNED NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    KEY idx_questions_evaluation (evaluation_id),
    CONSTRAINT fk_questions_evaluation
        FOREIGN KEY (evaluation_id) REFERENCES evaluations(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- exam_submissions: one row per student per evaluation
-- -----------------------------------------------------------------------------
CREATE TABLE exam_submissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    evaluation_id INT UNSIGNED NOT NULL,
    submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    score DECIMAL(5,2) NULL,
    grade VARCHAR(10) NULL,
    UNIQUE KEY uq_submission_student_eval (student_id, evaluation_id),
    KEY idx_submissions_evaluation (evaluation_id),
    KEY idx_submissions_submitted (submitted_at),
    CONSTRAINT fk_submissions_student
        FOREIGN KEY (student_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_submissions_evaluation
        FOREIGN KEY (evaluation_id) REFERENCES evaluations(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- answers: student responses linked to a submission and question
-- -----------------------------------------------------------------------------
CREATE TABLE answers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_id INT UNSIGNED NOT NULL,
    question_id INT UNSIGNED NOT NULL,
    answer_text TEXT NOT NULL,
    KEY idx_answers_submission (submission_id),
    KEY idx_answers_question (question_id),
    CONSTRAINT fk_answers_submission
        FOREIGN KEY (submission_id) REFERENCES exam_submissions(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_answers_question
        FOREIGN KEY (question_id) REFERENCES questions(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Seed data
-- -----------------------------------------------------------------------------

INSERT INTO departments (name) VALUES
('Computer Science'),
('Information Technology'),
('Software Engineering'),
('Data Science'),
('Cyber Security');
