<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/rate_limit.php';
require_once __DIR__ . '/includes/email.php';
$errors = [];
$info = null;
$unverified_email = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $posted_csrf = $_POST['csrf_token'] ?? '';

    // Cek rate limit berdasarkan IP dan email
    $rate_limit_key = $_SERVER['REMOTE_ADDR'] . '|' . $email;
    if (is_rate_limited($rate_limit_key)) {
        $errors[] = 'Terlalu banyak percobaan login gagal. Coba lagi dalam 15 menit.';
    } elseif (!verify_csrf_token($posted_csrf)) {
        $errors[] = 'Permintaan tidak valid (CSRF token salah).';
    } elseif ($email === '' || $password === '') {
        $errors[] = 'Email dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT id, nama, email, password, role, email_verified FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Email atau password salah.';
            record_login_attempt($rate_limit_key);
        } elseif (!$user['email_verified']) {
            // Email belum diverifikasi
            $unverified_email = $user['email'];
            $info = 'Email Anda belum diverifikasi. Silakan cek link verifikasi yang telah dikirim ke email Anda.';
        } else {
            session_start();
            $_SESSION['user'] = [
                'id' => $user['id'],
                'nama' => $user['nama'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];
            // Reset rate limit setelah login berhasil
            reset_rate_limit($rate_limit_key);
            // Regenerasi token CSRF setelah login
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: index.php');
            exit;
        }
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <h2 class="section-title">Login</h2>
        <?php if ($errors): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errors[0]) ?></div>
        <?php endif; ?>
        <?php if ($info): ?>
            <div class="alert alert-warning">
                <strong>⚠ Verifikasi Email Diperlukan</strong><br>
                <?= htmlspecialchars($info) ?><br><br>
                Tidak menerima email? 
                <a href="resend-verification.php?email=<?= urlencode($unverified_email) ?>" style="color: #003087; text-decoration: underline;">
                    Kirim ulang link verifikasi
                </a>
            </div>
        <?php endif; ?>
        <form method="post" action="login.php" class="form-card">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="email@domain.com" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Masukkan password" required>

            <button type="submit" class="button button-primary">Masuk</button>
        </form>
        <p style="margin-top: 20px; text-align: center; color: #666;">
            <a href="forgot-password.php">Lupa Password?</a> | 
            <a href="register.php">Buat Akun Baru</a>
        </p>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php';
