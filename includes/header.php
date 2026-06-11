<?php
$pageTitle = $pageTitle ?? 'EMS - Evaluation Management System';
$showNav = $showNav ?? true;
$showPublicNav = $showPublicNav ?? false;
$bodyClass = $bodyClass ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">
<?php if ($showPublicNav): ?>
    <?php require_once __DIR__ . '/public_nav.php'; ?>
<?php elseif ($showNav && !empty($_SESSION['user_id'])): ?>
<header class="site-header">
    <div class="container header-inner">
        <a href="#" class="logo">EMS Cloud</a>
        <nav class="user-nav">
            <span class="user-badge"><?= htmlspecialchars($_SESSION['full_name'] ?? '') ?> (<?= htmlspecialchars(ucfirst($_SESSION['role'] ?? '')) ?>)</span>
            <a href="/logout.php" class="btn btn-outline btn-sm">Logout</a>
        </nav>
    </div>
</header>
<?php endif; ?>
<main class="main-content">
