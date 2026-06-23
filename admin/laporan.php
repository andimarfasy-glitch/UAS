<?php
require_once __DIR__ . '/../config/database.php';
session_start();
if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$totalRevenue = $pdo->query('SELECT COALESCE(SUM(total_harga), 0) FROM orders')->fetchColumn();
$totalOrders = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalProducts = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalUsers = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

// Admin logs data
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'summary';
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$logs = [];
$total_logs = 0;
$filter_action = isset($_GET['action']) ? $_GET['action'] : '';

if ($tab === 'logs') {
    // Hitung total logs
    $count_sql = 'SELECT COUNT(*) FROM admin_logs';
    $count_params = [];
    if ($filter_action) {
        $count_sql .= ' WHERE action = ?';
        $count_params[] = $filter_action;
    }
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($count_params);
    $total_logs = (int) $count_stmt->fetchColumn();
    $total_pages = ceil($total_logs / $per_page);
    
    // Ambil logs
    $logs_sql = 'SELECT al.id, al.action, al.description, al.entity_type, al.ip_address, al.created_at, u.nama as admin_nama FROM admin_logs al LEFT JOIN users u ON al.admin_id = u.id';
    $logs_params = [];
    if ($filter_action) {
        $logs_sql .= ' WHERE al.action = ?';
        $logs_params[] = $filter_action;
    }
    $logs_sql .= ' ORDER BY al.created_at DESC LIMIT ? OFFSET ?';
    $logs_params[] = $per_page;
    $logs_params[] = $offset;
    
    $logs_stmt = $pdo->prepare($logs_sql);
    $logs_stmt->execute($logs_params);
    $logs = $logs_stmt->fetchAll();
    
    // Ambil unique actions untuk filter
    $actions_stmt = $pdo->query('SELECT DISTINCT action FROM admin_logs ORDER BY action');
    $actions = $actions_stmt->fetchAll(PDO::FETCH_COLUMN);
}

require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <h2 class="section-title">Laporan</h2>
        
        <!-- Tab Navigation -->
        <div class="tab-nav" style="margin-bottom:24px;">
            <a href="laporan.php?tab=summary" class="tab-link <?= $tab === 'summary' ? 'active' : '' ?>">Ringkasan</a>
            <a href="laporan.php?tab=logs" class="tab-link <?= $tab === 'logs' ? 'active' : '' ?>">Aktivitas Admin</a>
        </div>
        
        <?php if ($tab === 'summary'): ?>
            <!-- Ringkasan Statistik -->
            <div class="card-grid">
                <article class="card">
                    <h3>Pendapatan Total</h3>
                    <p>Rp <?= number_format($totalRevenue, 0, ',', '.') ?></p>
                </article>
                <article class="card">
                    <h3>Total Pesanan</h3>
                    <p><?= htmlspecialchars($totalOrders) ?></p>
                </article>
                <article class="card">
                    <h3>Total Produk</h3>
                    <p><?= htmlspecialchars($totalProducts) ?></p>
                </article>
                <article class="card">
                    <h3>Total Pengguna</h3>
                    <p><?= htmlspecialchars($totalUsers) ?></p>
                </article>
            </div>
        <?php else: ?>
            <!-- Admin Activity Logs -->
            <div class="card" style="margin-bottom:24px;">
                <h3>Aktivitas Admin</h3>
                <form method="get" action="laporan.php" style="margin-bottom:16px;display:flex;gap:8px;">
                    <input type="hidden" name="tab" value="logs">
                    <select name="action" class="form-control" style="flex:1;">
                        <option value="">Semua Aktivitas</option>
                        <?php foreach ($actions as $act): ?>
                            <option value="<?= htmlspecialchars($act) ?>" <?= $filter_action === $act ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $act))) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="button button-small">Filter</button>
                </form>
                
                <?php if ($logs): ?>
                    <table class="admin-table" style="font-size:13px;">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Admin</th>
                                <th>Aksi</th>
                                <th>Keterangan</th>
                                <th>Tipe Entity</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= htmlspecialchars($log['created_at']) ?></td>
                                    <td><?= htmlspecialchars($log['admin_nama'] ?? 'Unknown') ?></td>
                                    <td><span style="background:#e3f2fd;padding:4px 8px;border-radius:4px;"><?= htmlspecialchars(str_replace('_', ' ', $log['action'])) ?></span></td>
                                    <td><?= htmlspecialchars($log['description'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($log['entity_type'] ?? '-') ?></td>
                                    <td style="font-size:12px;"><?= htmlspecialchars($log['ip_address'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div style="margin-top:16px;text-align:center;">
                            <?php if ($page > 1): ?>
                                <a href="laporan.php?tab=logs&page=1<?= $filter_action ? '&action=' . urlencode($filter_action) : '' ?>" class="button button-small">« Awal</a>
                                <a href="laporan.php?tab=logs&page=<?= $page - 1 . ($filter_action ? '&action=' . urlencode($filter_action) : '') ?>" class="button button-small">‹ Sebelum</a>
                            <?php endif; ?>
                            <span style="margin:0 8px;">Halaman <?= htmlspecialchars($page) ?> dari <?= htmlspecialchars($total_pages) ?></span>
                            <?php if ($page < $total_pages): ?>
                                <a href="laporan.php?tab=logs&page=<?= $page + 1 . ($filter_action ? '&action=' . urlencode($filter_action) : '') ?>" class="button button-small">Sesudah ›</a>
                                <a href="laporan.php?tab=logs&page=<?= $total_pages . ($filter_action ? '&action=' . urlencode($filter_action) : '') ?>" class="button button-small">Akhir »</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div style="padding:16px;text-align:center;color:#666;">
                        <p>Tidak ada aktivitas yang dicatat.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="admin-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="produk.php">Produk</a>
            <a href="kategori.php">Kategori</a>
            <a href="user.php">User</a>
            <a href="pesanan.php">Pesanan</a>
            <a href="pembayaran.php">Pembayaran</a>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php';
