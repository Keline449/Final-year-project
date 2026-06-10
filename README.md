# Evaluation Management System (EMS)

Cloud-based online student evaluation platform for XAMPP (PHP + MySQL).

## Features

- **Welcome page** — Navigation with welcome notification
- **Registration** — Full name (CAPITAL LETTERS), EMS registration number (EMS0001–EMS9999), password, role (Student / Lecturer / Administrator)
- **Login** — Role-specific fields; redirects to the correct landing page
- **Student** — Start exam, submit answers, view published results (only after submitting an exam)
- **Lecturer** — Create evaluations, upload forms, manage questions, view/download/publish results
- **Administrator** — Department dropdown, manage users & departments, system reports

## Setup (XAMPP)

1. Start **Apache** and **MySQL** in XAMPP Control Panel.
2. Open phpMyAdmin: http://localhost/phpmyadmin
3. Import the database:
   - Go to **Import** → Choose file: `database/schema.sql` → **Go**
   - Or run in shell: `mysql -u root < database/schema.sql`
4. Open the app: **http://localhost/evaluationmanagement/**

## Default database

- Host: `localhost`
- Database: `ems_db`
- User: `root`
- Password: *(empty — default XAMPP)*

Edit `config/database.php` if your MySQL credentials differ.

### Default administrator (after import)

| Field | Value |
|-------|--------|
| Email | `admin@ems.local` |
| Name | `SYSTEM ADMINISTRATOR` |
| Password | `Admin@123` |

Change this password after first login.

## Sample workflow

1. Register a **Lecturer** (e.g. `EMS0001`, name in capitals).
2. Register a **Student** (e.g. `EMS0002`).
3. Lecturer: Create evaluation → add questions → students see them on **Start Exam**.
4. Student: Start exam → submit → lecturer scores & **Publish Results**.
5. Student: **View Results** (only after at least one submission).
6. Register an **Administrator** to manage users, departments, and reports.

## Folder structure

```
evaluationmanagement/
├── index.php          # Welcome / navigation
├── register.php
├── login.php
├── logout.php
├── config/
├── includes/
├── student/
├── lecturer/
├── admin/
├── assets/
├── database/schema.sql
└── uploads/           # Evaluation form files
```
