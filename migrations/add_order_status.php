<?php
/**
 * Migration: Add status column to orders table
 * Purpose: Track order status (pending, processing, shipped, delivered)
 */

require_once '../config/database.php';

try {
    // Check if column already exists
    $result = $pdo->query("SHOW COLUMNS FROM orders LIKE 'status'");
    if ($result->rowCount() > 0) {
        echo "✓ Column 'status' sudah ada di table orders\n";
        exit;
    }

    // Add status column
    $pdo->exec("ALTER TABLE orders ADD COLUMN status ENUM('pending', 'processing', 'shipped', 'delivered') DEFAULT 'pending' AFTER total_amount");
    
    echo "✓ Column 'status' berhasil ditambahkan ke table orders\n";
    echo "  Status values: pending, processing, shipped, delivered\n";

} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
