<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$stmt = $pdo->query('SELECT id, nama_produk, deskripsi, harga FROM products ORDER BY created_at DESC LIMIT 3');
$featured = $stmt->fetchAll();
?>
<section class="hero-section">
    <div class="container hero-grid">
        <div class="hero-copy">
            <h1>Temukan produk PlayStation terbaik untuk kebutuhan gaming Anda</h1>
            <p>Marketplace e-commerce sederhana dengan tampilan desain PlayStation yang bersih, modern, dan responsif. Mulai dari console, controller, hingga game populer.</p>
            <div class="button-group">
                <a href="produk.php" class="button button-primary">Lihat Produk</a>
                <a href="register.php" class="button button-secondary">Daftar Sekarang</a>
            </div>
        </div>
        <div class="hero-media"></div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Produk Unggulan</h2>
        <?php if ($featured): ?>
            <div class="card-grid">
                <?php foreach ($featured as $product): ?>
                    <article class="card">
                        <h3><?= htmlspecialchars($product['nama_produk']) ?></h3>
                        <p><?= htmlspecialchars($product['deskripsi']) ?></p>
                        <p><strong>Harga:</strong> Rp <?= number_format($product['harga'], 0, ',', '.') ?></p>
                        <a href="detail-produk.php?id=<?= htmlspecialchars($product['id']) ?>" class="button button-secondary">Lihat Detail</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card">
                <p>Tidak ada produk unggulan saat ini.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Fitur Utama</h2>
        <div class="card-grid">
            <article class="card">
                <h3>Desain responsif</h3>
                <p>Antarmuka yang dirancang untuk tampil baik di desktop, tablet, dan ponsel.</p>
            </article>
            <article class="card">
                <h3>Integrasi Midtrans</h3>
                <p>Menyediakan gateway pembayaran dengan pilihan QRIS, bank transfer, dan e-wallet.</p>
            </article>
            <article class="card">
                <h3>Manajemen lengkap</h3>
                <p>Admin dapat mengelola produk, kategori, user, pesanan, dan laporan secara mudah.</p>
            </article>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php';
