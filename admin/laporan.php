<?php
require_once __DIR__ . '/../config/database.php';
session_start();
if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$totalRevenue = $pdo->query('SELECT COALESCE(SUM(total_harga), 0) FROM orders')->fetchColumn();
$totalOrders = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalProducts = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalUsers = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <h2 class="section-title">Laporan</h2>
        <div class="card-grid">
            <article class="card">
                <h3>Pendapatan Total</h3>
                <p>Rp <?= number_format($totalRevenue, 0, ',', '.') ?></p>
            </article>
            <article class="card">
                <h3>Total Pesanan</h3>
                <p><?= htmlspecialchars($totalOrders) ?></p>
            </article>
            <article class="card">
                <h3>Total Produk</h3>
                <p><?= htmlspecialchars($totalProducts) ?></p>
            </article>
            <article class="card">
                <h3>Total Pengguna</h3>
                <p><?= htmlspecialchars($totalUsers) ?></p>
            </article>
        </div>
        <div class="admin-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="produk.php">Produk</a>
            <a href="kategori.php">Kategori</a>
            <a href="user.php">User</a>
            <a href="pesanan.php">Pesanan</a>
            <a href="pembayaran.php">Pembayaran</a>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php';
