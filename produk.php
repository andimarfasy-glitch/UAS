<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$stmt = $pdo->query('SELECT p.id, p.nama_produk, p.deskripsi, p.harga, p.stok, c.nama_kategori
                     FROM products p
                     LEFT JOIN categories c ON p.category_id = c.id
                     ORDER BY p.created_at DESC');
$products = $stmt->fetchAll();
?>
<section class="section">
    <div class="container">
        <h2 class="section-title">Daftar Produk</h2>
        <div class="card-grid">
            <?php if ($products): ?>
                <?php foreach ($products as $product): ?>
                    <article class="card">
                        <h3><?= htmlspecialchars($product['nama_produk']) ?></h3>
                        <p><?= htmlspecialchars($product['deskripsi']) ?></p>
                        <p><strong>Harga:</strong> Rp <?= number_format($product['harga'], 0, ',', '.') ?></p>
                        <p><strong>Kategori:</strong> <?= htmlspecialchars($product['nama_kategori'] ?? 'Umum') ?></p>
                        <p><strong>Stok:</strong> <?= htmlspecialchars($product['stok']) ?></p>
                        <a href="detail-produk.php?id=<?= htmlspecialchars($product['id']) ?>" class="button button-secondary">Lihat Detail</a>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <article class="card">
                    <p>Tidak ada produk tersedia saat ini.</p>
                </article>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php';
