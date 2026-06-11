<?php
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
?>
<header class="public-header">
    <div class="container public-header-inner">
        <a href="/index.php" class="logo">EMS Cloud</a>
        <nav class="public-nav" aria-label="Main navigation">
            <a href="/index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Home</a>
            <a href="/register.php" class="<?= $currentPage === 'register.php' ? 'active' : '' ?>">Registration</a>
            <a href="/login.php" class="<?= $currentPage === 'login.php' ? 'active' : '' ?>">Login</a>
        </nav>
    </div>
</header>
