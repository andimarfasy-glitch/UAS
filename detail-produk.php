<?php
require_once __DIR__ . '/config/database.php';
$errors = [];
$success = '';
$product = null;
$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($productId <= 0) {
    header('Location: produk.php');
    exit;
}

$stmt = $pdo->prepare('SELECT p.*, c.nama_kategori FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? LIMIT 1');
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: produk.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    if (empty($_SESSION['user'])) {
        $errors[] = 'Silakan login terlebih dahulu untuk menambahkan ke keranjang.';
    } else {
        $qty = max(1, (int) ($_POST['qty'] ?? 1));
        if ($product['stok'] <= 0) {
            $errors[] = 'Maaf, produk ini sedang habis.';
        } else {
            $userId = $_SESSION['user']['id'];
            $cartStmt = $pdo->prepare('SELECT id, qty FROM carts WHERE user_id = ? AND product_id = ? LIMIT 1');
            $cartStmt->execute([$userId, $productId]);
            $cartItem = $cartStmt->fetch();

            if ($cartItem) {
                $newQty = min($product['stok'], $cartItem['qty'] + $qty);
                $update = $pdo->prepare('UPDATE carts SET qty = ? WHERE id = ?');
                $update->execute([$newQty, $cartItem['id']]);
            } else {
                $insert = $pdo->prepare('INSERT INTO carts (user_id, product_id, qty) VALUES (?, ?, ?)');
                $insert->execute([$userId, $productId, $qty]);
            }
            $success = 'Produk berhasil ditambahkan ke keranjang.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="product-detail">
            <div class="product-detail-media">
                <?php if (!empty($product['gambar'])): ?>
                    <img src="assets/images/products/<?= htmlspecialchars($product['gambar']) ?>" alt="<?= htmlspecialchars($product['nama_produk']) ?>" style="width: 100%; max-width: 400px; height: auto; border-radius: 8px;">
                <?php else: ?>
                    <div style="width: 100%; max-width: 400px; height: 400px; background-color: #e0e0e0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 18px;">
                        Tidak ada gambar
                    </div>
                <?php endif; ?>
            </div>
            <div class="product-detail-info">
                <h2 class="section-title"><?= htmlspecialchars($product['nama_produk']) ?></h2>
                <p class="product-meta"><strong>Kategori:</strong> <?= htmlspecialchars($product['nama_kategori'] ?? 'Umum') ?> | <strong>Stok:</strong> <?= htmlspecialchars($product['stok']) ?></p>
                <p class="product-price">Rp <?= number_format($product['harga'], 0, ',', '.') ?></p>
                <p><?= nl2br(htmlspecialchars($product['deskripsi'])) ?></p>

                <?php if ($errors): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($errors[0]) ?></div>
                <?php elseif ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="post" action="detail-produk.php?id=<?= $productId ?>" class="product-action-form">
                    <label for="qty">Jumlah</label>
                    <input type="number" id="qty" name="qty" value="1" min="1" max="<?= htmlspecialchars($product['stok']) ?>" class="input-qty">
                    <button type="submit" class="button button-primary">Tambah ke Keranjang</button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php';
