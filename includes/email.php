<?php
/**
 * Email Utility Helper
 * Handles email sending untuk verification, password reset, notifications
 */

/**
 * Send email using PHP mail() function
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @param array $headers Optional additional headers
 * @return bool Success status
 */
function send_email($to, $subject, $body, $headers = []) {
    // Default headers
    $default_headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: noreply@playstation-shop.local'
    ];
    
    // Merge headers
    $all_headers = array_merge($default_headers, $headers);
    $headers_string = implode("\r\n", $all_headers);
    
    // For development: log emails to file
    $log_file = __DIR__ . '/../logs/emails.log';
    if (!is_dir(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }
    
    $log_entry = "[" . date('Y-m-d H:i:s') . "] TO: $to | SUBJECT: $subject\n" . str_repeat("=", 80) . "\n$body\n\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    
    // Send email (akan bekerja di production dengan SMTP configured)
    return mail($to, $subject, $body, $headers_string);
}

/**
 * Send email verification link
 * 
 * @param string $email User email
 * @param string $token Verification token
 * @param string $base_url Base URL aplikasi (e.g., http://localhost:8000)
 * @return bool Success status
 */
function send_verification_email($email, $token, $base_url) {
    $verify_link = $base_url . '/verify-email.php?token=' . urlencode($token);
    
    $subject = 'Verifikasi Email - PlayStation Shop';
    $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #003087; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .button { display: inline-block; background-color: #003087; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin: 20px 0; }
                .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #ccc; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Verifikasi Email Anda</h2>
                </div>
                <div class='content'>
                    <p>Halo,</p>
                    <p>Terima kasih telah mendaftar di PlayStation Shop! Untuk melanjutkan, silakan verifikasi email Anda dengan mengklik tombol di bawah:</p>
                    <a href='$verify_link' class='button'>Verifikasi Email</a>
                    <p>Atau salin link berikut ke browser Anda:</p>
                    <p><small>$verify_link</small></p>
                    <p><strong>Link ini akan berlaku selama 24 jam.</strong></p>
                    <div class='footer'>
                        <p>PlayStation Shop &copy; 2026. Jika Anda tidak membuat akun ini, abaikan email ini.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
    ";
    
    return send_email($email, $subject, $body);
}

/**
 * Send password reset link
 * 
 * @param string $email User email
 * @param string $token Reset token
 * @param string $base_url Base URL aplikasi
 * @return bool Success status
 */
function send_password_reset_email($email, $token, $base_url) {
    $reset_link = $base_url . '/reset-password.php?token=' . urlencode($token);
    
    $subject = 'Reset Password - PlayStation Shop';
    $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #003087; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .button { display: inline-block; background-color: #003087; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin: 20px 0; }
                .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #ccc; font-size: 12px; color: #666; }
                .warning { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Reset Password</h2>
                </div>
                <div class='content'>
                    <p>Halo,</p>
                    <p>Kami menerima permintaan untuk reset password akun Anda. Klik tombol di bawah untuk membuat password baru:</p>
                    <a href='$reset_link' class='button'>Reset Password</a>
                    <p>Atau salin link berikut ke browser Anda:</p>
                    <p><small>$reset_link</small></p>
                    <div class='warning'>
                        <strong>Link ini akan berlaku selama 1 jam.</strong><br>
                        Jika Anda tidak meminta reset password, abaikan email ini.
                    </div>
                    <div class='footer'>
                        <p>PlayStation Shop &copy; 2026.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
    ";
    
    return send_email($email, $subject, $body);
}

/**
 * Generate secure token untuk verification/reset
 * 
 * @return string 64-character hex token
 */
function generate_token() {
    return bin2hex(random_bytes(32));
}

/**
 * Verify token is valid (not expired)
 * 
 * @param PDO $pdo Database connection
 * @param string $token Token to verify
 * @param string $table_name Table containing token (email_verification_tokens or password_reset_tokens)
 * @return array|null Token data if valid, null if invalid/expired
 */
function verify_token($pdo, $token, $table_name) {
    $stmt = $pdo->prepare("
        SELECT * FROM $table_name 
        WHERE token = ? 
        AND expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
}

/**
 * Mark token as used (untuk password reset)
 * 
 * @param PDO $pdo Database connection
 * @param int $token_id Token ID
 * @return bool Success status
 */
function mark_token_used($pdo, $token_id) {
    $stmt = $pdo->prepare("
        UPDATE password_reset_tokens 
        SET used_at = NOW() 
        WHERE id = ?
    ");
    return $stmt->execute([$token_id]);
}

/**
 * Clean up expired tokens (call periodically)
 * 
 * @param PDO $pdo Database connection
 * @return int Number of rows deleted
 */
function cleanup_expired_tokens($pdo) {
    $deleted = 0;
    
    // Delete expired email verification tokens
    $stmt = $pdo->prepare("DELETE FROM email_verification_tokens WHERE expires_at < NOW()");
    $stmt->execute();
    $deleted += $stmt->rowCount();
    
    // Delete expired password reset tokens
    $stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE expires_at < NOW()");
    $stmt->execute();
    $deleted += $stmt->rowCount();
    
    return $deleted;
}
?>
