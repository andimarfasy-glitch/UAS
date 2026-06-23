<?php
require_once __DIR__ . '/config/database.php';
session_start();
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$orderId = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
if ($orderId <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT o.*, p.payment_method, p.status AS payment_status, p.transaction_id, p.paid_at
                       FROM orders o
                       LEFT JOIN payments p ON p.order_id = o.id
                       WHERE o.id = ? AND o.user_id = ?
                       LIMIT 1');
$stmt->execute([$orderId, $_SESSION['user']['id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <h2 class="section-title">Pembayaran</h2>
        <div class="card">
            <p>Pesanan Anda telah dibuat dengan ID <strong>#<?= htmlspecialchars($order['id']) ?></strong>.</p>
            <p>Status pembayaran: <strong><?= htmlspecialchars(ucfirst($order['payment_status'] ?? 'pending')) ?></strong></p>
            <p>Metode pembayaran: <strong><?= htmlspecialchars($order['payment_method'] ?? '-') ?></strong></p>
            <?php if (!empty($order['transaction_id'])): ?>
                <p>ID transaksi: <strong><?= htmlspecialchars($order['transaction_id']) ?></strong></p>
            <?php endif; ?>
            <p>Jumlah yang dibayarkan: <strong>Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></strong></p>
            <p>Alamat pengiriman:</p>
            <p><?= nl2br(htmlspecialchars($order['alamat'])) ?></p>
            <p>Ini adalah placeholder pembayaran. Integrasi Midtrans dapat ditambahkan pada halaman ini.</p>
            <a href="orders.php" class="button button-secondary">Lihat Riwayat Pesanan</a>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php';
