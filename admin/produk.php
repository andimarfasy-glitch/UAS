<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/admin_logger.php';
require_once __DIR__ . '/../includes/image_upload.php';
session_start();
if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($posted_csrf)) {
        $errors[] = 'Permintaan tidak valid (CSRF token salah).';
    } else if (isset($_POST['action']) && $_POST['action'] === 'add') {
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
            // Handle file upload
            $gambar = null;
            if (!empty($_FILES['gambar']['name'])) {
                $file = $_FILES['gambar'];
                $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];
                $max_size = 2 * 1024 * 1024; // 2MB
                
                // Validate file
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                
                if (!in_array($mime_type, $allowed_mimes)) {
                    $errors[] = 'Format gambar hanya JPG, PNG, atau GIF.';
                } elseif ($file['size'] > $max_size) {
                    $errors[] = 'Ukuran gambar maksimal 2MB.';
                } elseif (!is_dir(__DIR__ . '/../assets/images/products')) {
                    mkdir(__DIR__ . '/../assets/images/products', 0755, true);
                    // Generate filename
                    $filename = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
                    if (move_uploaded_file($file['tmp_name'], __DIR__ . '/../assets/images/products/' . $filename)) {
                        $gambar = $filename;
                    } else {
                        $errors[] = 'Gagal upload gambar.';
                    }
                } else {
                    // Folder exists, upload file
                    $filename = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
                    if (move_uploaded_file($file['tmp_name'], __DIR__ . '/../assets/images/products/' . $filename)) {
                        $gambar = $filename;
                    } else {
                        $errors[] = 'Gagal upload gambar.';
                    }
                }
            }
            
            // Insert product if no errors
            if (empty($errors)) {
                $stmt = $pdo->prepare('INSERT INTO products (category_id, nama_produk, deskripsi, harga, stok, gambar) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([$categoryId, $nama, $deskripsi, $harga, $stok, $gambar]);
                $productId = $pdo->lastInsertId();
                log_admin_activity($pdo, 'add_product', "Produk '$nama' ditambahkan", 'product', $productId);
                $success = 'Produk berhasil ditambahkan.';
            }
        }
    }

    if (isset($_POST['delete_product']) && empty($errors)) {
        $productId = (int) ($_POST['delete_product'] ?? 0);
        if ($productId > 0) {
            $stmt = $pdo->prepare('SELECT nama_produk FROM products WHERE id = ? LIMIT 1');
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            $delStmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
            $delStmt->execute([$productId]);
            log_admin_activity($pdo, 'delete_product', "Produk '{$product['nama_produk']}' dihapus", 'product', $productId);
            $success = 'Produk berhasil dihapus.';
        }
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
                <form method="post" action="produk.php" enctype="multipart/form-data" class="form-card">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
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

                    <label for="gambar">Gambar Produk (opsional)</label>
                    <input type="file" id="gambar" name="gambar" accept="image/jpeg,image/png,image/gif">
                    <small style="color: #666;">Format: JPG, PNG, GIF | Ukuran maks: 2MB</small>

                    <button type="submit" class="button button-primary">Simpan Produk</button>
                </form>
            </article>

            <article class="card" style="grid-column: 1 / -1;">
                <h3>Daftar Produk</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Gambar</th>
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
                                <td>
                                    <?php if (!empty($product['gambar'])): ?>
                                        <img src="../assets/images/products/<?= htmlspecialchars($product['gambar']) ?>" alt="<?= htmlspecialchars($product['nama_produk']) ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 12px;">Tidak ada gambar</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($product['nama_produk']) ?></td>
                                <td><?= htmlspecialchars($product['nama_kategori'] ?? 'Umum') ?></td>
                                <td>Rp <?= number_format($product['harga'], 0, ',', '.') ?></td>
                                <td><?= htmlspecialchars($product['stok']) ?></td>
                                <td>
                                    <form method="post" action="produk.php" onsubmit="return confirm('Hapus produk ini?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
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
