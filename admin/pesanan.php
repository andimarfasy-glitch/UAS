<?php
require_once __DIR__ . '/../config/database.php';
session_start();
if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $status = trim($_POST['status'] ?? 'pending');
    if ($orderId > 0) {
        $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $orderId]);
        $success = 'Status pesanan berhasil diperbarui.';
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
                                <form method="post" action="pesanan.php">
                                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                                    <select name="status">
                                        <?php foreach (['pending', 'paid', 'processing', 'completed', 'cancelled'] as $statusOption): ?>
                                            <option value="<?= $statusOption ?>" <?= $statusOption === $order['status'] ? 'selected' : '' ?>><?= ucfirst($statusOption) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="button button-primary">Update</button>
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
