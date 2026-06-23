<?php
require_once __DIR__ . '/../config/database.php';
session_start();
if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $nama = trim($_POST['nama'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $harga = trim($_POST['harga'] ?? '');
        $stok = trim($_POST['stok'] ?? '');
        $categoryId = (int) ($_POST['category_id'] ?? 0);

        if ($nama === '' || $harga === '' || $stok === '' || $categoryId <= 0) {
            $errors[] = 'Semua bidang wajib diisi dan kategori harus dipilih.';
        } elseif (!is_numeric($harga) || !is_numeric($stok)) {
            $errors[] = 'Harga dan stok harus berupa angka.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO products (category_id, nama_produk, deskripsi, harga, stok) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$categoryId, $nama, $deskripsi, $harga, $stok]);
            $success = 'Produk berhasil ditambahkan.';
        }
    }

    if (isset($_POST['delete_product'])) {
        $productId = (int) ($_POST['delete_product'] ?? 0);
        if ($productId > 0) {
            $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
            $stmt->execute([$productId]);
            $success = 'Produk berhasil dihapus.';
        }
    }
}

$categories = $pdo->query('SELECT id, nama_kategori FROM categories ORDER BY nama_kategori')->fetchAll();
$products = $pdo->query('SELECT p.*, c.nama_kategori FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <h2 class="section-title">Kelola Produk</h2>
        <?php if ($errors): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errors[0]) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <div class="admin-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="kategori.php">Kategori</a>
            <a href="user.php">User</a>
            <a href="pesanan.php">Pesanan</a>
            <a href="pembayaran.php">Pembayaran</a>
            <a href="laporan.php">Laporan</a>
        </div>

        <div class="card-grid">
            <article class="card">
                <h3>Tambah Produk Baru</h3>
                <form method="post" action="produk.php" class="form-card">
                    <input type="hidden" name="action" value="add">
                    <label for="nama">Nama Produk</label>
                    <input type="text" id="nama" name="nama" required>

                    <label for="category_id">Kategori</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Pilih kategori</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category['id']) ?>"><?= htmlspecialchars($category['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="harga">Harga</label>
                    <input type="text" id="harga" name="harga" required>

                    <label for="stok">Stok</label>
                    <input type="number" id="stok" name="stok" required>

                    <label for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="4"></textarea>

                    <button type="submit" class="button button-primary">Simpan Produk</button>
                </form>
            </article>

            <article class="card" style="grid-column: 1 / -1;">
                <h3>Daftar Produk</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= htmlspecialchars($product['nama_produk']) ?></td>
                                <td><?= htmlspecialchars($product['nama_kategori'] ?? 'Umum') ?></td>
                                <td>Rp <?= number_format($product['harga'], 0, ',', '.') ?></td>
                                <td><?= htmlspecialchars($product['stok']) ?></td>
                                <td>
                                    <form method="post" action="produk.php" onsubmit="return confirm('Hapus produk ini?');">
                                        <button type="submit" name="delete_product" value="<?= htmlspecialchars($product['id']) ?>" class="button button-secondary">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </article>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php';
