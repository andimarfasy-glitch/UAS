<?php
require_once __DIR__ . '/../config/database.php';
session_start();
if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $userId = (int) ($_POST['delete_user'] ?? 0);
    if ($userId > 0 && $userId !== $_SESSION['user']['id']) {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $success = 'Pengguna berhasil dihapus.';
    } else {
        $errors[] = 'Anda tidak dapat menghapus akun Anda sendiri.';
    }
}

$users = $pdo->query('SELECT id, nama, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <h2 class="section-title">Kelola User</h2>
        <?php if ($errors): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errors[0]) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <div class="admin-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="produk.php">Produk</a>
            <a href="kategori.php">Kategori</a>
            <a href="pesanan.php">Pesanan</a>
            <a href="pembayaran.php">Pembayaran</a>
            <a href="laporan.php">Laporan</a>
        </div>

        <article class="card">
            <h3>Daftar User</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Peran</th>
                        <th>Terdaftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['nama']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars(ucfirst($user['role'])) ?></td>
                            <td><?= htmlspecialchars($user['created_at']) ?></td>
                            <td>
                                <?php if ($user['id'] !== $_SESSION['user']['id']): ?>
                                    <form method="post" action="user.php" onsubmit="return confirm('Hapus pengguna ini?');">
                                        <button type="submit" name="delete_user" value="<?= htmlspecialchars($user['id']) ?>" class="button button-secondary">Hapus</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: var(--color-body-light);">(Anda)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </article>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php';
