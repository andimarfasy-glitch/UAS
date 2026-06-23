<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/csrf.php';
session_start();
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user']['id'];
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($posted_csrf)) {
        $errors[] = 'Permintaan tidak valid (CSRF token salah).';
    } else {
        $alamat = trim($_POST['alamat'] ?? '');
        $paymentMethod = trim($_POST['payment_method'] ?? '');

        if ($alamat === '' || $paymentMethod === '') {
            $errors[] = 'Alamat dan metode pembayaran wajib diisi.';
        } else {
            $cartStmt = $pdo->prepare('SELECT c.*, p.nama_produk, p.harga, p.stok FROM carts c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?');
            $cartStmt->execute([$userId]);
            $cartItems = $cartStmt->fetchAll();

            if (!$cartItems) {
                $errors[] = 'Keranjang Anda kosong.';
            } else {
            $total = 0;
            foreach ($cartItems as $item) {
                $itemQty = min($item['qty'], $item['stok']);
                $total += $item['harga'] * $itemQty;
            }

            $orderInsert = $pdo->prepare('INSERT INTO orders (user_id, total_harga, status, alamat) VALUES (?, ?, ?, ?)');
            $orderInsert->execute([$userId, $total, 'pending', $alamat]);
            $orderId = $pdo->lastInsertId();

            $detailInsert = $pdo->prepare('INSERT INTO order_details (order_id, product_id, harga, qty, subtotal) VALUES (?, ?, ?, ?, ?)');
            foreach ($cartItems as $item) {
                $qty = min($item['qty'], $item['stok']);
                $detailInsert->execute([$orderId, $item['product_id'], $item['harga'], $qty, $item['harga'] * $qty]);
            }

            $paymentInsert = $pdo->prepare('INSERT INTO payments (order_id, payment_method, amount, status) VALUES (?, ?, ?, ?)');
            $paymentInsert->execute([$orderId, $paymentMethod, $total, 'pending']);

            $clearCart = $pdo->prepare('DELETE FROM carts WHERE user_id = ?');
            $clearCart->execute([$userId]);

            header('Location: payment.php?order_id=' . $orderId);
            exit;
        }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
$cartStmt = $pdo->prepare('SELECT c.*, p.nama_produk, p.harga FROM carts c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?');
$cartStmt->execute([$userId]);
$cartItems = $cartStmt->fetchAll();
?>
<section class="section">
    <div class="container">
        <h2 class="section-title">Checkout</h2>
        <?php if ($errors): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errors[0]) ?></div>
        <?php endif; ?>

        <?php if ($cartItems): ?>
            <div class="checkout-grid">
                <div class="summary-card">
                    <h3>Ringkasan Pesanan</h3>
                    <ul>
                        <?php $orderTotal = 0; foreach ($cartItems as $item): ?>
                            <?php $subtotal = $item['harga'] * $item['qty']; $orderTotal += $subtotal; ?>
                            <li><?= htmlspecialchars($item['nama_produk']) ?> × <?= htmlspecialchars($item['qty']) ?> — Rp <?= number_format($subtotal, 0, ',', '.') ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <p><strong>Total:</strong> Rp <?= number_format($orderTotal, 0, ',', '.') ?></p>
                </div>
                <form method="post" action="checkout.php" class="form-card">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <label for="alamat">Alamat Pengiriman</label>
                    <textarea id="alamat" name="alamat" rows="5" required><?= isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : '' ?></textarea>

                    <label for="payment_method">Metode Pembayaran</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="">Pilih metode pembayaran</option>
                        <option value="QRIS" <?= isset($_POST['payment_method']) && $_POST['payment_method'] === 'QRIS' ? 'selected' : '' ?>>QRIS</option>
                        <option value="Bank Transfer" <?= isset($_POST['payment_method']) && $_POST['payment_method'] === 'Bank Transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                        <option value="E-Wallet" <?= isset($_POST['payment_method']) && $_POST['payment_method'] === 'E-Wallet' ? 'selected' : '' ?>>E-Wallet</option>
                        <option value="Credit Card" <?= isset($_POST['payment_method']) && $_POST['payment_method'] === 'Credit Card' ? 'selected' : '' ?>>Credit Card</option>
                    </select>

                    <button type="submit" class="button button-primary">Proses Pembayaran</button>
                </form>
            </div>
        <?php else: ?>
            <div class="card">
                <p>Keranjang Anda kosong. Tambahkan produk terlebih dahulu sebelum checkout.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php';
