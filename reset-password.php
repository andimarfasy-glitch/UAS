<?php
/**
 * Reset Password Page
 * User menggunakan token dari email untuk set password baru
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/email.php';
require_once __DIR__ . '/includes/csrf.php';

$success = false;
$error = null;
$token = trim($_GET['token'] ?? '');
$token_data = null;

// Verify token pada GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $token !== '') {
    $token_data = verify_token($pdo, $token, 'password_reset_tokens');
    if (!$token_data) {
        $error = 'Link reset password tidak valid atau telah kadaluarsa (berlaku 1 jam).';
    }
}

// Handle password reset pada POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim($_POST['token'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Verify token
    $token_data = verify_token($pdo, $token, 'password_reset_tokens');
    
    if (!verify_csrf_token($csrf_token)) {
        $error = 'Permintaan tidak valid (CSRF token salah).';
    } elseif (!$token_data) {
        $error = 'Link reset password tidak valid atau telah kadaluarsa.';
    } elseif ($password === '' || $confirm === '') {
        $error = 'Password dan konfirmasi wajib diisi.';
    } elseif ($password !== $confirm) {
        $error = 'Password dan konfirmasi tidak cocok.';
    } else {
        // Validate password strength
        if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $error = 'Password minimal 8 karakter dan harus mengandung huruf serta angka.';
        } else {
            try {
                $pdo->beginTransaction();
                
                // Update password
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                $stmt->execute([$hash, $token_data['user_id']]);
                
                // Mark token as used
                $stmt = $pdo->prepare('UPDATE password_reset_tokens SET used_at = NOW() WHERE id = ?');
                $stmt->execute([$token_data['id']]);
                
                $pdo->commit();
                $success = true;
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = 'Terjadi kesalahan saat reset password. Silakan coba lagi.';
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h2 class="section-title">Reset Password</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <strong>✓ Password berhasil direset!</strong><br>
                Anda sekarang dapat login dengan password baru Anda.
            </div>
            <p style="margin-top: 20px;">
                <a href="login.php" class="button button-primary">Login Sekarang</a>
            </p>
        <?php elseif ($error): ?>
            <div class="alert alert-error">
                <strong>✗ Reset password gagal</strong><br>
                <?= htmlspecialchars($error) ?>
            </div>
            <p style="margin-top: 20px;">
                <a href="forgot-password.php" class="button button-secondary">Minta Link Reset Password Baru</a>
            </p>
        <?php elseif ($token_data): ?>
            <form method="post" action="reset-password.php" class="form-card">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                
                <p style="margin-bottom: 20px; color: #666;">
                    Masukkan password baru Anda. Password harus minimal 8 karakter dan mengandung huruf serta angka.
                </p>
                
                <label for="password">Password Baru:</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password baru" required>
                
                <label for="confirm_password">Konfirmasi Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>
                
                <button type="submit" class="button button-primary">Reset Password</button>
            </form>
        <?php else: ?>
            <div class="alert alert-warning">
                Token reset password tidak ditemukan. Silakan klik link di email Anda untuk reset password.
            </div>
            <p style="margin-top: 20px;">
                <a href="forgot-password.php" class="button button-secondary">Minta Link Reset Password</a>
            </p>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
