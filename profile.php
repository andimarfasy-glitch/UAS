<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/csrf.php';
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
    $posted_csrf = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($posted_csrf)) {
        $errors[] = 'Permintaan tidak valid (CSRF token salah).';
    } else {
        if ($nama === '' || $email === '') {
            $errors[] = 'Nama dan email wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid.';
        } elseif ($password !== '' && $password !== $confirm) {
            $errors[] = 'Password dan konfirmasi tidak cocok.';
        } elseif ($password !== '') {
            // Validasi kekuatan password: minimal 8 karakter, minimal 1 huruf dan 1 angka
            if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
                $errors[] = 'Password minimal 8 karakter dan harus mengandung huruf serta angka.';
            }
        }

        // Jika belum ada error, lanjut cek dan update
        if (empty($errors)) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                $errors[] = 'Email sudah digunakan oleh akun lain.';
            } else {
                // Handle file upload jika ada
                $avatarFilename = null;
                if (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $file = $_FILES['avatar'];
                    if ($file['error'] === UPLOAD_ERR_OK) {
                        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime = finfo_file($finfo, $file['tmp_name']);
                        finfo_close($finfo);
                        if (!isset($allowed[$mime])) {
                            $errors[] = 'Tipe file avatar tidak didukung. Gunakan JPG, PNG, atau GIF.';
                        } elseif ($file['size'] > 2 * 1024 * 1024) {
                            $errors[] = 'Ukuran file maksimal 2MB.';
                        } else {
                            $ext = $allowed[$mime];
                            $avatarFilename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
                            $destDir = __DIR__ . '/assets/images/avatars';
                            if (!is_dir($destDir)) {
                                mkdir($destDir, 0755, true);
                            }
                            $destPath = $destDir . '/' . $avatarFilename;
                            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                                $errors[] = 'Gagal menyimpan file avatar.';
                                $avatarFilename = null;
                            }
                        }
                    } else {
                        $errors[] = 'Terjadi kesalahan saat mengunggah file avatar.';
                    }
                }

                if (empty($errors)) {
                    // Membangun query update dinamis
                    $fields = ['nama = ?', 'email = ?'];
                    $params = [$nama, $email];
                    if ($password !== '') {
                        $fields[] = 'password = ?';
                        $params[] = password_hash($password, PASSWORD_DEFAULT);
                    }
                    if ($avatarFilename !== null) {
                        $fields[] = 'avatar = ?';
                        $params[] = $avatarFilename;
                    }
                    $params[] = $userId;
                    $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
                    $update = $pdo->prepare($sql);
                    $update->execute($params);

                    $_SESSION['user']['nama'] = $nama;
                    $_SESSION['user']['email'] = $email;
                    if ($avatarFilename !== null) {
                        $_SESSION['user']['avatar'] = $avatarFilename;
                    }
                    $success = 'Profil berhasil diperbarui.';
                    // Regenerasi token setelah tindakan sensitif
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
            }
        }
    }
}

$userStmt = $pdo->prepare('SELECT nama, email, role, created_at, avatar FROM users WHERE id = ? LIMIT 1');
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

        <form method="post" action="profile.php" class="form-card" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
            <?php if (!empty($user['avatar'])): ?>
                <div style="margin-bottom:12px;">
                    <img src="assets/images/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" style="width:80px;height:80px;border-radius:8px;object-fit:cover;">
                </div>
            <?php endif; ?>
            <label for="nama">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" required value="<?= htmlspecialchars($user['nama']) ?>">

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>">

            <label for="password">Password Baru</label>
            <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah">

            <label for="confirm_password">Konfirmasi Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password baru">

            <label for="avatar">Foto Profil (opsional, JPG/PNG/GIF, max 2MB)</label>
            <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/gif">

            <button type="submit" class="button button-primary">Simpan Perubahan</button>
        </form>

        <div class="card" style="margin-top: 32px;">
            <p><strong>Peran:</strong> <?= htmlspecialchars(ucfirst($user['role'])) ?></p>
            <p><strong>Terdaftar sejak:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php';
