<?php
require_once __DIR__ . '/../config/database.php';
session_start();
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/admin_logger.php';

if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $status = trim($_POST['status'] ?? 'pending');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        $errors[] = 'Permintaan tidak valid (CSRF token salah).';
    } elseif (!in_array($status, ['pending', 'processing', 'shipped', 'delivered'])) {
        $errors[] = 'Status tidak valid.';
    } elseif ($orderId > 0) {
        try {
            $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
            $stmt->execute([$status, $orderId]);
            
            // Log activity
            log_admin_activity($pdo, 'update_order_status', 'Update status pesanan menjadi ' . $status, 'order', $orderId);
            
            $success = 'Status pesanan berhasil diperbarui.';
        } catch (PDOException $e) {
            $errors[] = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}

$orders = $pdo->query('SELECT o.id, o.total_harga, o.status, o.created_at, u.nama AS nama_user FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <h2 class="section-title">Kelola Pesanan</h2>
        <?php if ($errors): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errors[0]) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <div class="admin-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="produk.php">Produk</a>
            <a href="kategori.php">Kategori</a>
            <a href="user.php">User</a>
            <a href="pembayaran.php">Pembayaran</a>
            <a href="laporan.php">Laporan</a>
        </div>

        <article class="card" style="grid-column: 1 / -1;">
            <h3>Daftar Pesanan</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($order['id']) ?></td>
                            <td><?= htmlspecialchars($order['nama_user']) ?></td>
                            <td>Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars(ucfirst($order['status'])) ?></td>
                            <td><?= htmlspecialchars($order['created_at']) ?></td>
                            <td>
                                <form method="post" action="pesanan.php" style="display: flex; gap: 8px;">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                                    <select name="status" style="padding: 6px; border: 1px solid #ddd; border-radius: 4px;">
                                        <option value="pending" <?= 'pending' === $order['status'] ? 'selected' : '' ?>>Pending</option>
                                        <option value="processing" <?= 'processing' === $order['status'] ? 'selected' : '' ?>>Processing</option>
                                        <option value="shipped" <?= 'shipped' === $order['status'] ? 'selected' : '' ?>>Shipped</option>
                                        <option value="delivered" <?= 'delivered' === $order['status'] ? 'selected' : '' ?>>Delivered</option>
                                    </select>
                                    <button type="submit" class="button button-primary" style="padding: 6px 12px; font-size: 14px;">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </article>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php';
