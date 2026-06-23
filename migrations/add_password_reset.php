<?php
/**
 * Migration: Add password reset support
 * Purpose: Track password reset tokens for forgot password feature
 */

require_once '../config/database.php';

try {
    // Create password_reset_tokens table
    $result = $pdo->query("SHOW TABLES LIKE 'password_reset_tokens'");
    if ($result->rowCount() === 0) {
        $pdo->exec("
            CREATE TABLE password_reset_tokens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                token VARCHAR(255) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NULL,
                used_at TIMESTAMP NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_token (token),
                INDEX idx_expires_at (expires_at)
            )
        ");
        echo "✓ Table 'password_reset_tokens' berhasil dibuat\n";
    } else {
        echo "✓ Table 'password_reset_tokens' sudah ada\n";
    }

} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
