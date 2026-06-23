<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current = basename($_SERVER['PHP_SELF']);
$isAdmin = !empty($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlayStation Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php">PlayStation Shop</a>
        <nav class="primary-nav">
            <a href="index.php" class="<?= $current === 'index.php' ? 'active' : '' ?>">Home</a>
            <a href="produk.php" class="<?= $current === 'produk.php' ? 'active' : '' ?>">Produk</a>
            <a href="cart.php" class="<?= $current === 'cart.php' ? 'active' : '' ?>">Keranjang</a>
            <a href="orders.php" class="<?= $current === 'orders.php' ? 'active' : '' ?>">Pesanan</a>
            <?php if (!empty($_SESSION['user'])): ?>
                <a href="profile.php" class="<?= $current === 'profile.php' ? 'active' : '' ?>">Profil</a>
            <?php endif; ?>
        </nav>
        <div class="header-actions">
            <?php if (!empty($_SESSION['user'])): ?>
                <?php if ($isAdmin): ?>
                    <a class="button button-secondary" href="admin/dashboard.php">Admin</a>
                <?php endif; ?>
                <span class="user-badge">
                    <?php if (!empty($_SESSION['user']['avatar'])): ?>
                        <img src="assets/images/avatars/<?= htmlspecialchars($_SESSION['user']['avatar']) ?>" alt="avatar" style="width:32px;height:32px;border-radius:50%;vertical-align:middle;margin-right:8px;object-fit:cover;">
                    <?php endif; ?>
                    Halo, <?= htmlspecialchars($_SESSION['user']['nama']) ?>
                </span>
                <a class="button button-secondary" href="logout.php">Logout</a>
            <?php else: ?>
                <a class="button button-secondary" href="login.php">Login</a>
                <a class="button button-secondary" href="register.php">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main class="site-main">
