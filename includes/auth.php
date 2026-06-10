<?php
session_start();

require_once __DIR__ . '/../config/database.php';

function requireRole(string ...$roles): void
{
    if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
        header('Location: /evaluationmanagement/login.php');
        exit;
    }
    if (!in_array($_SESSION['role'], $roles, true)) {
        header('Location: /evaluationmanagement/index.php');
        exit;
    }
}

function redirectByRole(string $role): void
{
    $base = '/evaluationmanagement';
    switch ($role) {
        case 'student':
            header("Location: {$base}/student/dashboard.php");
            break;
        case 'lecturer':
            header("Location: {$base}/lecturer/dashboard.php");
            break;
        default:
            header("Location: {$base}/index.php");
    }
    exit;
}

function validateFullName(string $name): bool
{
    return $name === strtoupper($name) && preg_match('/^[A-Z\s\.\-\']+$/', $name) && strlen(trim($name)) >= 3;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function currentUser(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = getDB()->prepare('SELECT u.*, d.name AS department_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}
