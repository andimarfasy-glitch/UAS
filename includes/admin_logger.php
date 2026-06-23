<?php
// Admin activity logging helper
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Catat aktivitas admin ke tabel admin_logs
 */
function log_admin_activity(
    \PDO $pdo,
    string $action,
    string $description = '',
    string $entity_type = '',
    int $entity_id = 0
): bool {
    try {
        if (empty($_SESSION['user'])) {
            return false;
        }
        
        $admin_id = $_SESSION['user']['id'] ?? null;
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        
        $stmt = $pdo->prepare(
            'INSERT INTO admin_logs (admin_id, action, description, entity_type, entity_id, ip_address) 
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        
        return $stmt->execute([
            $admin_id,
            $action,
            $description,
            $entity_type,
            $entity_id,
            $ip_address
        ]);
    } catch (PDOException $e) {
        // Silent fail untuk tidak mengganggu flow aplikasi
        return false;
    }
}
