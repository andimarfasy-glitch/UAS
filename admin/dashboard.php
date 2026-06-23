<?php
require_once __DIR__ . '/../config/database.php';
session_start();
if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$userCount = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$productCount = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$orderCount = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$paymentCount = $pdo->query('SELECT COUNT(*) FROM payments')->fetchColumn();

require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <h2 class="section-title">Admin Dashboard</h2>
        <div class="card-grid">
            <article class="card">
                <h3>Total Pengguna</h3>
                <p><?= htmlspecialchars($userCount) ?></p>
            </article>
            <article class="card">
                <h3>Total Produk</h3>
                <p><?= htmlspecialchars($productCount) ?></p>
            </article>
            <article class="card">
                <h3>Total Pesanan</h3>
                <p><?= htmlspecialchars($orderCount) ?></p>
            </article>
            <article class="card">
                <h3>Total Pembayaran</h3>
                <p><?= htmlspecialchars($paymentCount) ?></p>
            </article>
        </div>
        <div class="admin-nav">
            <a href="produk.php">Produk</a>
            <a href="kategori.php">Kategori</a>
            <a href="user.php">User</a>
            <a href="pesanan.php">Pesanan</a>
            <a href="pembayaran.php">Pembayaran</a>
            <a href="laporan.php">Laporan</a>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php';
