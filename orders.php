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
                <?php foreach ($orders as $order): 
                    // Determine status badge color
                    $status_color = 'gray';
                    $status_text = ucfirst($order['status']);
                    switch($order['status']) {
                        case 'pending': $status_color = 'orange'; break;
                        case 'processing': $status_color = 'blue'; break;
                        case 'shipped': $status_color = 'purple'; break;
                        case 'delivered': $status_color = 'green'; break;
                    }
                ?>
                    <article class="card">
                        <h3>Pesanan #<?= htmlspecialchars($order['id']) ?></h3>
                        <p><strong>Total:</strong> Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></p>
                        <p>
                            <strong>Status:</strong> 
                            <span style="background-color: <?= $status_color === 'orange' ? '#ff9800' : ($status_color === 'blue' ? '#2196f3' : ($status_color === 'purple' ? '#9c27b0' : '#4caf50')) ?>; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; display: inline-block;">
                                <?= htmlspecialchars($status_text) ?>
                            </span>
                        </p>
                        <p><strong>Tanggal:</strong> <?= htmlspecialchars($order['created_at']) ?></p>
                        <a href="payment.php?order_id=<?= htmlspecialchars($order['id']) ?>" class="button button-secondary">Lihat Detail</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php';
