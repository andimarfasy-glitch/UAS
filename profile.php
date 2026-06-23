<?php
require_once __DIR__ . '/config/database.php';
session_start();
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = '';
$userId = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if ($nama === '' || $email === '') {
        $errors[] = 'Nama dan email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    } elseif ($password !== '' && $password !== $confirm) {
        $errors[] = 'Password dan konfirmasi tidak cocok.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            $errors[] = 'Email sudah digunakan oleh akun lain.';
        } else {
            $params = [$nama, $email, $userId];
            $sql = 'UPDATE users SET nama = ?, email = ?';
            if ($password !== '') {
                $sql .= ', password = ?';
                $params = [$nama, $email, password_hash($password, PASSWORD_DEFAULT), $userId];
            }
            $sql .= ' WHERE id = ?';
            $update = $pdo->prepare($sql);
            $update->execute($params);
            $_SESSION['user']['nama'] = $nama;
            $_SESSION['user']['email'] = $email;
            $success = 'Profil berhasil diperbarui.';
        }
    }
}

$userStmt = $pdo->prepare('SELECT nama, email, role, created_at FROM users WHERE id = ? LIMIT 1');
$userStmt->execute([$userId]);
$user = $userStmt->fetch();

require_once __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <h2 class="section-title">Profil Saya</h2>
        <?php if ($errors): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errors[0]) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" action="profile.php" class="form-card">
            <label for="nama">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" required value="<?= htmlspecialchars($user['nama']) ?>">

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>">

            <label for="password">Password Baru</label>
            <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah">

            <label for="confirm_password">Konfirmasi Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password baru">

            <button type="submit" class="button button-primary">Simpan Perubahan</button>
        </form>

        <div class="card" style="margin-top: 32px;">
            <p><strong>Peran:</strong> <?= htmlspecialchars(ucfirst($user['role'])) ?></p>
            <p><strong>Terdaftar sejak:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php';
