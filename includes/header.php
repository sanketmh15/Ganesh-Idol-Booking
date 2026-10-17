
<?php require_once __DIR__ . '/functions.php'; ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?>Girijatmak Arts</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="topbar premium-navbar">
    <a class="brand premium-brand" href="index.php">
        <span class="brand-icon">🙏</span>
        <span class="brand-text">
            <strong>GIRIJATMAK ARTS</strong>
            <small>|| श्री गणेशाय नमः ||</small>
        </span>
    </a>

    <nav class="premium-nav">
        <a href="index.php">Home</a>
        <a href="idols.php">Idol Collection</a>

        <?php if (current_user()): ?>
            <a href="my-bookings.php">My Bookings</a>

            <?php if (current_user()['role'] === 'admin'): ?>
                <a href="admin.php">Admin Panel</a>
            <?php endif; ?>

            <a href="logout.php">
                Logout (<?= e(current_user()['name']) ?>)
            </a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a class="nav-register" href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

<main class="container">
<?php show_flash(); ?>
