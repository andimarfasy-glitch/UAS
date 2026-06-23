<?php
require_once __DIR__ . '/../config/database.php';
session_start();
if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_id'])) {
    $paymentId = (int) ($_POST['payment_id'] ?? 0);
    $status = trim($_POST['status'] ?? 'pending');
    if ($paymentId > 0) {
        $stmt = $pdo->prepare('UPDATE payments SET status = ? WHERE id = ?');
        $stmt->execute([$status, $paymentId]);
        $success = 'Status pembayaran berhasil diperbarui.';
    }
}

$payments = $pdo->query('SELECT p.id, p.payment_method, p.amount, p.status, p.transaction_id, p.paid_at, o.id AS order_id, u.nama AS user_name FROM payments p JOIN orders o ON p.order_id = o.id JOIN users u ON o.user_id = u.id ORDER BY p.id DESC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <h2 class="section-title">Kelola Pembayaran</h2>
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
            <a href="pesanan.php">Pesanan</a>
            <a href="laporan.php">Laporan</a>
        </div>

        <article class="card" style="grid-column: 1 / -1;">
            <h3>Daftar Pembayaran</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Order</th>
                        <th>User</th>
                        <th>Metode</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Transaksi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($payment['id']) ?></td>
                            <td>#<?= htmlspecialchars($payment['order_id']) ?></td>
                            <td><?= htmlspecialchars($payment['user_name']) ?></td>
                            <td><?= htmlspecialchars($payment['payment_method']) ?></td>
                            <td>Rp <?= number_format($payment['amount'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars(ucfirst($payment['status'])) ?></td>
                            <td><?= htmlspecialchars($payment['transaction_id'] ?? '-') ?></td>
                            <td>
                                <form method="post" action="pembayaran.php">
                                    <input type="hidden" name="payment_id" value="<?= htmlspecialchars($payment['id']) ?>">
                                    <select name="status">
                                        <?php foreach (['pending', 'paid', 'failed', 'cancelled'] as $statusOption): ?>
                                            <option value="<?= $statusOption ?>" <?= $statusOption === $payment['status'] ? 'selected' : '' ?>><?= ucfirst($statusOption) ?></option>
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
