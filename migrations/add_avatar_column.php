<?php
// Skrip migrasi sederhana: tambahkan kolom `avatar` pada tabel `users` jika belum ada.
require_once __DIR__ . '/../config/database.php';

try {
    // Dapatkan nama database yang sedang digunakan oleh koneksi PDO
    $stmt = $pdo->query("SELECT DATABASE() as db");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $dbName = $row['db'];

    $check = $pdo->prepare(
        "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND COLUMN_NAME = 'avatar'"
    );
    $check->execute([$dbName]);
    $exists = (int) $check->fetch(PDO::FETCH_ASSOC)['cnt'] > 0;

    if ($exists) {
        echo "Kolom 'avatar' sudah ada pada tabel users. Tidak ada perubahan.\n";
        exit(0);
    }

    $sql = "ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER role";
    $pdo->exec($sql);
    echo "Berhasil menambahkan kolom 'avatar' pada tabel users.\n";
    exit(0);
} catch (PDOException $e) {
    echo "Gagal melakukan migrasi: " . $e->getMessage() . "\n";
    exit(1);
}
