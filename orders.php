<?php
require_once __DIR__ . '/config/database.php';
session_start();
require_once __DIR__ . '/includes/header.php';

if (empty($_SESSION['user'])) {
    $orders = [];
} else {
    $userId = $_SESSION['user']['id'];
    $stmt = $pdo->prepare('SELECT id, total_harga, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll();
}
?>
<section class="section">
    <div class="container">
        <h2 class="section-title">Riwayat Pesanan</h2>
        <?php if (empty($_SESSION['user'])): ?>
            <div class="card">
                <p>Silakan <a href="login.php">login</a> untuk melihat riwayat pesanan Anda.</p>
            </div>
        <?php elseif (!$orders): ?>
            <div class="card">
                <p>Belum ada pesanan. Setelah checkout, riwayat pesanan Anda akan muncul di halaman ini.</p>
            </div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($orders as $order): ?>
                    <article class="card">
                        <h3>Pesanan #<?= htmlspecialchars($order['id']) ?></h3>
                        <p><strong>Total:</strong> Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></p>
                        <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst($order['status'])) ?></p>
                        <p><strong>Tanggal:</strong> <?= htmlspecialchars($order['created_at']) ?></p>
                        <a href="payment.php?order_id=<?= htmlspecialchars($order['id']) ?>" class="button button-secondary">Lihat Detail</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php';
