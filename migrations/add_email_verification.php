<?php
/**
 * Migration: Add email verification support
 * Purpose: Track email verification tokens and verified status
 */

require_once '../config/database.php';

try {
    // 1. Add email_verified column to users
    $result = $pdo->query("SHOW COLUMNS FROM users LIKE 'email_verified'");
    if ($result->rowCount() === 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER email");
        echo "✓ Column 'email_verified' ditambahkan ke table users\n";
    } else {
        echo "✓ Column 'email_verified' sudah ada\n";
    }

    // 2. Create email_verification_tokens table
    $result = $pdo->query("SHOW TABLES LIKE 'email_verification_tokens'");
    if ($result->rowCount() === 0) {
        $pdo->exec("
            CREATE TABLE email_verification_tokens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                token VARCHAR(255) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_token (token),
                INDEX idx_expires_at (expires_at)
            )
        ");
        echo "✓ Table 'email_verification_tokens' berhasil dibuat\n";
    } else {
        echo "✓ Table 'email_verification_tokens' sudah ada\n";
    }

} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
