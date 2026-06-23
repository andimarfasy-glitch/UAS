<?php
// Skrip migrasi: tambahkan tabel `admin_logs` jika belum ada.
require_once __DIR__ . '/../config/database.php';

try {
    // Dapatkan nama database
    $stmt = $pdo->query("SELECT DATABASE() as db");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $dbName = $row['db'];

    // Cek apakah tabel admin_logs sudah ada
    $check = $pdo->prepare(
        "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'admin_logs'"
    );
    $check->execute([$dbName]);
    $exists = (int) $check->fetch(PDO::FETCH_ASSOC)['cnt'] > 0;

    if ($exists) {
        echo "Tabel 'admin_logs' sudah ada. Tidak ada perubahan.\n";
        exit(0);
    }

    $sql = "CREATE TABLE `admin_logs` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `admin_id` INT UNSIGNED NOT NULL,
      `action` VARCHAR(100) NOT NULL,
      `description` TEXT,
      `entity_type` VARCHAR(50) DEFAULT NULL,
      `entity_id` INT UNSIGNED DEFAULT NULL,
      `ip_address` VARCHAR(45) DEFAULT NULL,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `idx_admin_logs_admin` (`admin_id`),
      KEY `idx_admin_logs_action` (`action`),
      KEY `idx_admin_logs_created` (`created_at`),
      CONSTRAINT `fk_admin_logs_admin` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);
    echo "Berhasil membuat tabel 'admin_logs'.\n";
    exit(0);
} catch (PDOException $e) {
    echo "Gagal melakukan migrasi: " . $e->getMessage() . "\n";
    exit(1);
}
