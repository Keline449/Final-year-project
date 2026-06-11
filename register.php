<?php
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['user_id'])) {
    redirectByRole($_SESSION['role']);
}

$errors = [];
$old = ['full_name' => '', 'email' => '', 'role' => $_GET['role'] ?? 'student'];
if (!in_array($old['role'], ['student', 'lecturer'], true)) {
    $old['role'] = 'student';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['role'] = $_POST['role'] ?? 'student';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!validateFullName($old['full_name'])) {
        $errors[] = 'Full name must be in CAPITAL LETTERS only (letters, spaces, dots, hyphens).';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (!in_array($old['role'], ['student', 'lecturer'], true)) {
        $errors[] = 'Invalid user status selected.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        try {
            $db = getDB();
            $check = $db->prepare('SELECT id FROM users WHERE email = ?');
            $check->execute([$old['email']]);
            if ($check->fetch()) {
                $errors[] = 'This email is already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare('INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)');
                $stmt->execute([$old['full_name'], $old['email'], $hash, $old['role']]);
                flash('success', 'Registration successful! Please login with your credentials.');
                header('Location: login.php?role=' . urlencode($old['role']));
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = 'Registration failed. Database error: ' . $e->getMessage();
        }
    }
}

$pageTitle = ($old['role'] === 'lecturer' ? 'Lecturer' : 'User') . ' Registration - EMS';
$showNav = false;
$showPublicNav = true;
$bodyClass = 'page-auth page-register' . ($old['role'] === 'lecturer' ? ' register-lecturer' : ' register-student');
require_once __DIR__ . '/includes/header.php';
?>

<div class="container form-page">
    <div class="form-card form-card-auth<?= $old['role'] === 'lecturer' ? ' form-card-lecturer' : '' ?>">
        <h1><?= $old['role'] === 'lecturer' ? 'Lecturer Registration' : 'User Registration' ?></h1>
        <p class="subtitle">Register to the Evaluation Management System</p>

        <?php foreach ($errors as $err): ?>
            <div class="alert alert-error"><?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>

        <form method="post" class="ems-form" id="registerForm">
            <div class="form-group">
                <label for="full_name">Full Name (CAPITAL LETTERS)</label>
                <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($old['full_name']) ?>"
                       placeholder="JOHN DOE SMITH" required pattern="[A-Z\s\.\-\']+" style="text-transform: uppercase;">
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email']) ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="6">
                <small>Same password will be used on the login page</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
            </div>

            <div class="form-group">
                <label for="role">User Status</label>
                <select id="role" name="role" required>
                    <option value="student" <?= $old['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                    <option value="lecturer" <?= $old['role'] === 'lecturer' ? 'selected' : '' ?>>Lecturer</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>

        <p class="form-footer">Already registered? <a href="login.php">Go to Login</a></p>
        <p class="form-footer"><a href="index.php">&larr; Back to Home</a></p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
