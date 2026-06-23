<?php
/**
 * Forgot Password Page
 * User dapat request password reset token via email
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/email.php';
require_once __DIR__ . '/includes/csrf.php';

$success = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        $error = 'Permintaan tidak valid (CSRF token salah).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        // Find user
        $stmt = $pdo->prepare('SELECT id, email FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Security: jangan beritahu email tidak terdaftar (prevent email enumeration)
            $success = true;
        } else {
            try {
                // Delete old tokens (active max 1 per user)
                $pdo->prepare('DELETE FROM password_reset_tokens WHERE user_id = ? AND used_at IS NULL')->execute([$user['id']]);
                
                // Generate reset token (valid for 1 hour)
                $token = generate_token();
                $token_stmt = $pdo->prepare('
                    INSERT INTO password_reset_tokens (user_id, token, expires_at) 
                    VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))
                ');
                $token_stmt->execute([$user['id'], $token]);
                
                // Send email
                $base_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
                send_password_reset_email($email, $token, $base_url);
                
                $success = true;
            } catch (PDOException $e) {
                $error = 'Terjadi kesalahan. Silakan coba lagi.';
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h2 class="section-title">Lupa Password</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <strong>✓ Email reset password telah dikirim!</strong><br>
                Jika email Anda terdaftar, Anda akan menerima link untuk reset password.
                Silakan cek inbox Anda (atau folder Spam).<br><br>
                <strong>Link berlaku selama 1 jam.</strong>
            </div>
            <p style="margin-top: 20px;">
                <a href="login.php" class="button button-secondary">Kembali ke Login</a>
            </p>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="post" action="forgot-password.php" class="form-card">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                
                <p style="margin-bottom: 20px; color: #666;">
                    Masukkan email yang terdaftar, dan kami akan mengirim link untuk reset password Anda.
                </p>
                
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" placeholder="email@domain.com" required 
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                
                <button type="submit" class="button button-primary">Kirim Link Reset Password</button>
            </form>
            
            <p style="margin-top: 20px; text-align: center; color: #666;">
                Ingat password Anda? <a href="login.php">Kembali ke Login</a>
            </p>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
