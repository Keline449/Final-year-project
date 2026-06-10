-- Run on an existing ems_db to apply updates without a full re-import.
-- phpMyAdmin: select ems_db → SQL → paste and run (skip lines that error if already applied).

USE ems_db;

-- Remove registration number (skip if column already removed)
ALTER TABLE users DROP INDEX uq_users_registration;
ALTER TABLE users DROP COLUMN registration_number;

-- Remove administrator accounts and restrict role enum
DELETE FROM users WHERE role = 'administrator';
ALTER TABLE users MODIFY role ENUM('student', 'lecturer') NOT NULL;

-- Add exam schedule columns (skip if columns already exist)
ALTER TABLE evaluations ADD COLUMN exam_start_time DATETIME NULL AFTER form_file;
ALTER TABLE evaluations ADD COLUMN exam_end_time DATETIME NULL AFTER exam_start_time;
