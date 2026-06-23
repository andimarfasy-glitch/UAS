<?php
/**
 * Resend Email Verification
 * Sends new verification token for unverified accounts
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/email.php';
require_once __DIR__ . '/includes/csrf.php';

$success = false;
$error = null;
$email = trim($_GET['email'] ?? '');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        $error = 'Permintaan tidak valid (CSRF token salah).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        // Find user
        $stmt = $pdo->prepare('SELECT id, email_verified FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $error = 'Email tidak terdaftar.';
        } elseif ($user['email_verified']) {
            $error = 'Email ini sudah diverifikasi. Silakan login.';
        } else {
            // Delete old tokens
            $pdo->prepare('DELETE FROM email_verification_tokens WHERE user_id = ?')->execute([$user['id']]);
            
            // Generate new token
            $token = generate_token();
            $token_stmt = $pdo->prepare('
                INSERT INTO email_verification_tokens (user_id, token, expires_at) 
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))
            ');
            $token_stmt->execute([$user['id'], $token]);
            
            // Send email
            $base_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
            send_verification_email($email, $token, $base_url);
            
            $success = true;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h2 class="section-title">Kirim Ulang Email Verifikasi</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <strong>✓ Email verifikasi berhasil dikirim!</strong><br>
                Link verifikasi baru telah dikirim ke email Anda. Silakan cek inbox (atau folder Spam).
            </div>
            <p style="margin-top: 20px;">
                <a href="login.php" class="button button-secondary">Kembali ke Login</a>
            </p>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="post" action="resend-verification.php" class="form-card">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                
                <label for="email">Email yang terdaftar:</label>
                <input type="email" id="email" name="email" placeholder="email@domain.com" required 
                       value="<?= htmlspecialchars($email) ?>">
                
                <button type="submit" class="button button-primary">Kirim Ulang Email Verifikasi</button>
            </form>
            
            <p style="margin-top: 20px; text-align: center; color: #666;">
                Sudah verifikasi? <a href="login.php">Kembali ke Login</a>
            </p>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
