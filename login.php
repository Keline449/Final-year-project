<?php
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['user_id'])) {
    redirectByRole($_SESSION['role']);
}

$role = $_GET['role'] ?? $_POST['role'] ?? 'student';
if (!in_array($role, ['student', 'lecturer'], true)) {
    $role = 'student';
}

$errors = [];
$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? 'student';
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $name = trim($_POST['name'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email.';
    }
    if (strlen($password) < 1) {
        $errors[] = 'Password is required.';
    }
    if ($role === 'lecturer' && strlen($name) < 2) {
        $errors[] = 'Name is required for lecturer login.';
    }

    if (empty($errors)) {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ? AND role = ?');
        $stmt->execute([$email, $role]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($role === 'lecturer' && strcasecmp($user['full_name'], strtoupper($name)) !== 0) {
                $errors[] = 'Name does not match our records.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                redirectByRole($user['role']);
            }
        } else {
            $errors[] = 'Invalid credentials. Check email, name (if required), password, and role.';
        }
    }
}

$pageTitle = ucfirst($role) . ' Login - EMS';
$showNav = false;
$showPublicNav = true;
$bodyClass = 'page-auth page-login';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container form-page">
    <div class="form-card form-card-auth">
        <h1><?= htmlspecialchars(ucfirst($role)) ?> Login</h1>

        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>
        <?php foreach ($errors as $err): ?>
            <div class="alert alert-error"><?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>

        <div class="role-tabs">
            <a href="?role=student" class="<?= $role === 'student' ? 'active' : '' ?>">Student</a>
            <a href="?role=lecturer" class="<?= $role === 'lecturer' ? 'active' : '' ?>">Lecturer</a>
        </div>

        <form method="post" class="ems-form">
            <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">

            <?php if ($role === 'student'): ?>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <small>Same password used during registration</small>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label for="email">Lecturer Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="name">Lecturer Name (CAPITAL LETTERS)</label>
                    <input type="text" id="name" name="name" required style="text-transform: uppercase;" placeholder="YOUR FULL NAME">
                </div>
                <div class="form-group">
                    <label for="password">Lecturer Password</label>
                    <input type="password" id="password" name="password" required>
                    <small>Same password used during registration</small>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>

        <p class="form-footer">No account? <a href="register.php">Register here</a></p>
        <p class="form-footer"><a href="index.php">&larr; Back to Home</a></p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
