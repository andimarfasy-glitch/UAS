<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/admin_logger.php';
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
        $namaKategori = trim($_POST['nama_kategori'] ?? '');
        if ($namaKategori === '') {
            $errors[] = 'Nama kategori wajib diisi.';
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO categories (nama_kategori) VALUES (?)');
                $stmt->execute([$namaKategori]);
                $categoryId = $pdo->lastInsertId();
                log_admin_activity($pdo, 'add_category', "Kategori '$namaKategori' ditambahkan", 'category', $categoryId);
                $success = 'Kategori berhasil ditambahkan.';
            } catch (PDOException $e) {
                $errors[] = 'Kategori sudah ada atau terjadi kesalahan.';
            }
        }
    }

    if (isset($_POST['delete_category']) && empty($errors)) {
        $categoryId = (int) ($_POST['delete_category'] ?? 0);
        if ($categoryId > 0) {
            try {
                $stmt = $pdo->prepare('SELECT nama_kategori FROM categories WHERE id = ? LIMIT 1');
                $stmt->execute([$categoryId]);
                $category = $stmt->fetch();
                
                $delStmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
                $delStmt->execute([$categoryId]);
                log_admin_activity($pdo, 'delete_category', "Kategori '{$category['nama_kategori']}' dihapus", 'category', $categoryId);
                $success = 'Kategori berhasil dihapus.';
            } catch (PDOException $e) {
                $errors[] = 'Kategori tidak bisa dihapus karena masih digunakan oleh produk.';
            }
        }
    }
}

$categories = $pdo->query('SELECT id, nama_kategori, created_at FROM categories ORDER BY nama_kategori')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <h2 class="section-title">Kelola Kategori</h2>
        <?php if ($errors): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errors[0]) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <div class="admin-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="produk.php">Produk</a>
            <a href="user.php">User</a>
            <a href="pesanan.php">Pesanan</a>
            <a href="pembayaran.php">Pembayaran</a>
            <a href="laporan.php">Laporan</a>
        </div>

        <div class="card-grid">
            <article class="card">
                <h3>Tambah Kategori Baru</h3>
                <form method="post" action="kategori.php" class="form-card">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="action" value="add">
                    <label for="nama_kategori">Nama Kategori</label>
                    <input type="text" id="nama_kategori" name="nama_kategori" required>
                    <button type="submit" class="button button-primary">Simpan Kategori</button>
                </form>
            </article>

            <article class="card" style="grid-column: 1 / -1;">
                <h3>Daftar Kategori</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nama Kategori</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?= htmlspecialchars($category['nama_kategori']) ?></td>
                                <td><?= htmlspecialchars($category['created_at']) ?></td>
                                <td>
                                    <form method="post" action="kategori.php" onsubmit="return confirm('Hapus kategori ini?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <button type="submit" name="delete_category" value="<?= htmlspecialchars($category['id']) ?>" class="button button-secondary">Hapus</button>
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
