<?php
/**
 * Email Verification Page
 * Handles email verification tokens from register process
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/email.php';

$success = false;
$error = null;
$token = trim($_GET['token'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $token !== '') {
    // Verify token
    $token_data = verify_token($pdo, $token, 'email_verification_tokens');
    
    if ($token_data) {
        // Token valid, verify email
        try {
            $pdo->beginTransaction();
            
            // Update user email_verified status
            $stmt = $pdo->prepare('UPDATE users SET email_verified = 1 WHERE id = ?');
            $stmt->execute([$token_data['user_id']]);
            
            // Delete used token
            $stmt = $pdo->prepare('DELETE FROM email_verification_tokens WHERE id = ?');
            $stmt->execute([$token_data['id']]);
            
            $pdo->commit();
            $success = true;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Terjadi kesalahan saat verifikasi. Silakan coba lagi.';
        }
    } else {
        $error = 'Link verifikasi tidak valid atau telah kadaluarsa (berlaku 24 jam).';
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h2 class="section-title">Verifikasi Email</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <strong>✓ Email berhasil diverifikasi!</strong><br>
                Akun Anda sekarang aktif. Anda dapat login dan mulai berbelanja di PlayStation Shop.
            </div>
            <p style="margin-top: 20px;">
                <a href="login.php" class="button button-primary">Login Sekarang</a>
            </p>
        <?php elseif ($error): ?>
            <div class="alert alert-error">
                <strong>✗ Verifikasi gagal</strong><br>
                <?= htmlspecialchars($error) ?>
            </div>
            <p style="margin-top: 20px;">
                <a href="register.php" class="button button-secondary">Kembali ke Daftar</a>
            </p>
        <?php else: ?>
            <div class="alert alert-warning">
                Token verifikasi tidak ditemukan. Silakan klik link di email Anda untuk memverifikasi akun.
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
