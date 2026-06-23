<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/email.php';
$errors = [];
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');
    $posted_csrf = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($posted_csrf)) {
        $errors[] = 'Permintaan tidak valid (CSRF token salah).';
    } elseif ($nama === '' || $email === '' || $password === '' || $confirm === '') {
        $errors[] = 'Semua kolom harus diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    } elseif ($password !== $confirm) {
        $errors[] = 'Password dan konfirmasi tidak cocok.';
    } else {
        // Validasi kekuatan password: minimal 8 karakter, minimal 1 huruf dan 1 angka
        if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password minimal 8 karakter dan harus mengandung huruf serta angka.';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Email sudah terdaftar. Silakan gunakan email lain atau login.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $insert = $pdo->prepare('INSERT INTO users (nama, email, password, email_verified) VALUES (?, ?, ?, 0)');
                $insert->execute([$nama, $email, $hash]);
                $userId = $pdo->lastInsertId();
                
                // Generate verification token
                $token = generate_token();
                $token_stmt = $pdo->prepare('
                    INSERT INTO email_verification_tokens (user_id, token, expires_at) 
                    VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))
                ');
                $token_stmt->execute([$userId, $token]);
                
                // Send verification email
                $base_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
                send_verification_email($email, $token, $base_url);
                
                // Mark as success and show message
                $success = true;
            }
        }
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <h2 class="section-title">Daftar</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <strong>Pendaftaran berhasil!</strong><br>
                Link verifikasi email telah dikirim ke <strong><?= htmlspecialchars($email) ?></strong>. 
                Silakan cek inbox Anda (atau folder Spam) dan klik link untuk memverifikasi email.
                <br><br>
                Link verifikasi berlaku selama 24 jam.
            </div>
            <p><a href="login.php" class="button button-secondary">Kembali ke Login</a></p>
        <?php else: ?>
            <?php if ($errors): ?>
                <div class="alert alert-error"><?= htmlspecialchars($errors[0]) ?></div>
            <?php endif; ?>
            <form method="post" action="register.php" class="form-card">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" placeholder="Nama lengkap" required value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>">

                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="email@domain.com" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password" required>

                <label for="confirm_password">Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>

                <button type="submit" class="button button-primary">Daftar</button>
            </form>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php';
