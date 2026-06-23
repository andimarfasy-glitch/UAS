<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/csrf.php';
session_start();
require_once __DIR__ . '/includes/header.php';
$errors = [];
$success = '';

if (empty($_SESSION['user'])) {
    $cartItems = [];
} else {
    $userId = $_SESSION['user']['id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $posted_csrf = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($posted_csrf)) {
            $errors[] = 'Permintaan tidak valid (CSRF token salah).';
        } else if (isset($_POST['remove_item'])) {
            $cartId = (int) ($_POST['remove_item'] ?? 0);
            $delete = $pdo->prepare('DELETE FROM carts WHERE id = ? AND user_id = ?');
            $delete->execute([$cartId, $userId]);
            $success = 'Produk berhasil dihapus dari keranjang.';
        }

        if (isset($_POST['update_qty']) && empty($errors)) {
            $cartId = (int) ($_POST['cart_id'] ?? 0);
            $qty = max(1, (int) ($_POST['qty'] ?? 1));
            $stmt = $pdo->prepare('SELECT c.id, p.stok FROM carts c JOIN products p ON c.product_id = p.id WHERE c.id = ? AND c.user_id = ? LIMIT 1');
            $stmt->execute([$cartId, $userId]);
            $cartItem = $stmt->fetch();

            if ($cartItem) {
                $qty = min($qty, $cartItem['stok']);
                $update = $pdo->prepare('UPDATE carts SET qty = ? WHERE id = ?');
                $update->execute([$qty, $cartId]);
                $success = 'Jumlah produk berhasil diperbarui.';
            }
        }
    }

    $cartStmt = $pdo->prepare('SELECT c.id AS cart_id, c.qty, p.id AS product_id, p.nama_produk, p.harga, p.stok FROM carts c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?');
    $cartStmt->execute([$userId]);
    $cartItems = $cartStmt->fetchAll();
}
?>
<section class="section">
    <div class="container">
        <h2 class="section-title">Keranjang</h2>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errors[0]) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (empty($_SESSION['user'])): ?>
            <div class="card">
                <p>Silakan <a href="login.php">login</a> untuk melihat dan mengelola keranjang Anda.</p>
            </div>
        <?php elseif (!$cartItems): ?>
            <div class="card">
                <p>Keranjang Anda kosong. Tambahkan produk ke keranjang untuk memulai proses checkout.</p>
            </div>
        <?php else: ?>
            <div class="cart-table-wrap">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $total = 0; foreach ($cartItems as $item): ?>
                            <?php $subtotal = $item['harga'] * $item['qty']; $total += $subtotal; ?>
                            <tr>
                                <td><?= htmlspecialchars($item['nama_produk']) ?></td>
                                <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                <td>
                                    <form method="post" action="cart.php" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <input type="hidden" name="cart_id" value="<?= htmlspecialchars($item['cart_id']) ?>">
                                        <input type="number" name="qty" value="<?= htmlspecialchars($item['qty']) ?>" min="1" max="<?= htmlspecialchars($item['stok']) ?>" class="input-qty">
                                        <button type="submit" name="update_qty" value="1" class="button button-small">Update</button>
                                    </form>
                                </td>
                                <td>Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                                <td>
                                    <form method="post" action="cart.php">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <button type="submit" name="remove_item" value="<?= htmlspecialchars($item['cart_id']) ?>" class="button button-secondary">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="cart-summary">
                    <p><strong>Total:</strong> Rp <?= number_format($total, 0, ',', '.') ?></p>
                    <a href="checkout.php" class="button button-primary">Lanjut ke Checkout</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php';
