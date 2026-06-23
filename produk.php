<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

// Ambil kategori untuk filter dropdown
$categories = $pdo->query('SELECT id, nama_kategori FROM categories ORDER BY nama_kategori')->fetchAll();

// Ambil filter dari GET
$search = trim($_GET['search'] ?? '');
$category_id = isset($_GET['category']) ? (int) $_GET['category'] : 0;
$min_price = isset($_GET['min_price']) ? (float) $_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float) $_GET['max_price'] : 999999999;
$sort = $_GET['sort'] ?? 'newest';

// Build query dinamis
$query = 'SELECT p.id, p.nama_produk, p.deskripsi, p.harga, p.stok, c.nama_kategori
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.id
          WHERE 1=1';
$params = [];

// Filter search
if ($search !== '') {
    $query .= ' AND (p.nama_produk LIKE ? OR p.deskripsi LIKE ?)';
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
}

// Filter kategori
if ($category_id > 0) {
    $query .= ' AND p.category_id = ?';
    $params[] = $category_id;
}

// Filter harga
if ($min_price > 0 || $max_price < 999999999) {
    $query .= ' AND p.harga BETWEEN ? AND ?';
    $params[] = $min_price;
    $params[] = $max_price;
}

// Sort
switch ($sort) {
    case 'price_asc':
        $query .= ' ORDER BY p.harga ASC';
        break;
    case 'price_desc':
        $query .= ' ORDER BY p.harga DESC';
        break;
    case 'name_asc':
        $query .= ' ORDER BY p.nama_produk ASC';
        break;
    case 'name_desc':
        $query .= ' ORDER BY p.nama_produk DESC';
        break;
    default: // newest
        $query .= ' ORDER BY p.created_at DESC';
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();
$total_count = count($products);
?>
<section class="section">
    <div class="container">
        <h2 class="section-title">Daftar Produk</h2>
        
        <!-- Search & Filter Form -->
        <div class="card" style="margin-bottom:32px;padding:24px;">
            <h3 style="margin-top:0;">Cari & Filter Produk</h3>
            <form method="get" action="produk.php" style="display:grid;gap:16px;">
                <!-- Search Text -->
                <div>
                    <label for="search">Cari Produk</label>
                    <input type="text" id="search" name="search" placeholder="Masukkan nama atau deskripsi produk" value="<?= htmlspecialchars($search) ?>" style="width:100%;">
                </div>
                
                <!-- Filter Grid -->
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
                    <!-- Kategori -->
                    <div>
                        <label for="category">Kategori</label>
                        <select id="category" name="category" style="width:100%;">
                            <option value="0">Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['id']) ?>" <?= $category_id === (int)$cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nama_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Min Price -->
                    <div>
                        <label for="min_price">Harga Min (Rp)</label>
                        <input type="number" id="min_price" name="min_price" placeholder="0" value="<?= $min_price > 0 ? htmlspecialchars($min_price) : '' ?>" style="width:100%;">
                    </div>
                    
                    <!-- Max Price -->
                    <div>
                        <label for="max_price">Harga Max (Rp)</label>
                        <input type="number" id="max_price" name="max_price" placeholder="9999999999" value="<?= $max_price < 999999999 ? htmlspecialchars($max_price) : '' ?>" style="width:100%;">
                    </div>
                    
                    <!-- Sort -->
                    <div>
                        <label for="sort">Urutkan</label>
                        <select id="sort" name="sort" style="width:100%;">
                            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Terbaru</option>
                            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Harga: Rendah ke Tinggi</option>
                            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Harga: Tinggi ke Rendah</option>
                            <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Nama: A-Z</option>
                            <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Nama: Z-A</option>
                        </select>
                    </div>
                </div>
                
                <!-- Buttons -->
                <div style="display:flex;gap:12px;">
                    <button type="submit" class="button button-primary">Cari</button>
                    <a href="produk.php" class="button button-secondary" style="display:inline-flex;align-items:center;justify-content:center;">Reset</a>
                </div>
            </form>
        </div>
        
        <!-- Result Info -->
        <div style="margin-bottom:24px;color:#666;">
            <p>Ditemukan <strong><?= htmlspecialchars($total_count) ?></strong> produk</p>
        </div>
        
        <!-- Products Grid -->
        <div class="card-grid">
            <?php if ($products): ?>
                <?php foreach ($products as $product): ?>
                    <article class="card">
                        <?php if (!empty($product['gambar'])): ?>
                            <img src="assets/images/products/<?= htmlspecialchars($product['gambar']) ?>" alt="<?= htmlspecialchars($product['nama_produk']) ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 4px; margin-bottom: 12px;">
                        <?php else: ?>
                            <div style="width: 100%; height: 200px; background-color: #e0e0e0; border-radius: 4px; margin-bottom: 12px; display: flex; align-items: center; justify-content: center; color: #999;">
                                Tidak ada gambar
                            </div>
                        <?php endif; ?>
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
