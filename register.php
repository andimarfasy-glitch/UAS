<?php
require_once __DIR__ . '/config/database.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if ($nama === '' || $email === '' || $password === '' || $confirm === '') {
        $errors[] = 'Semua kolom harus diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    } elseif ($password !== $confirm) {
        $errors[] = 'Password dan konfirmasi tidak cocok.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email sudah terdaftar. Silakan gunakan email lain atau login.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare('INSERT INTO users (nama, email, password) VALUES (?, ?, ?)');
            $insert->execute([$nama, $email, $hash]);
            $userId = $pdo->lastInsertId();
            session_start();
            $_SESSION['user'] = [
                'id' => $userId,
                'nama' => $nama,
                'email' => $email,
                'role' => 'user',
            ];
            header('Location: index.php');
            exit;
        }
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <h2 class="section-title">Daftar</h2>
        <?php if ($errors): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errors[0]) ?></div>
        <?php endif; ?>
        <form method="post" action="register.php" class="form-card">
            <label for="nama">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" placeholder="Nama lengkap" required value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>">

            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="email@domain.com" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Masukkan password" required>

            <label for="confirm_password">Konfirmasi Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>

            <button type="submit" class="button button-primary">Daftar</button>
        </form>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php';
