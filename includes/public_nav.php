<?php
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
?>
<header class="public-header">
    <div class="container public-header-inner">
        <a href="/evaluationmanagement/index.php" class="logo">EMS Cloud</a>
        <nav class="public-nav" aria-label="Main navigation">
            <a href="/evaluationmanagement/index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Home</a>
            <a href="/evaluationmanagement/register.php" class="<?= $currentPage === 'register.php' ? 'active' : '' ?>">Registration</a>
            <a href="/evaluationmanagement/login.php" class="<?= $currentPage === 'login.php' ? 'active' : '' ?>">Login</a>
        </nav>
    </div>
</header>
