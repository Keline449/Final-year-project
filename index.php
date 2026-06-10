<?php
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['user_id'])) {
    redirectByRole($_SESSION['role']);
}

$pageTitle = 'Welcome - EMS';
$showNav = false;
$showPublicNav = true;
$showWelcome = true;
require_once __DIR__ . '/includes/header.php';
?>

<div class="hero welcome-hero">
    <div class="container">
        <div class="welcome-notification" id="welcomeNotification" role="alert">
            <h1>Welcome to the Evaluation Management System</h1>
            <p>Cloud-based online student evaluation platform.</p>
        </div>

        <div class="nav-cards">
            <a href="register.php" class="nav-card">
                <h2>Register</h2>
                <p>Create a new account as a Student or Lecturer</p>
            </a>
            <a href="login.php" class="nav-card">
                <h2>Login</h2>
                <p>Sign in with your registered credentials</p>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
