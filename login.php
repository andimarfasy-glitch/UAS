<?php
require_once __DIR__ . '/config/database.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $errors[] = 'Email dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT id, nama, email, password, role FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Email atau password salah.';
        } else {
            session_start();
            $_SESSION['user'] = [
                'id' => $user['id'],
                'nama' => $user['nama'],
                'email' => $user['email'],
                'role' => $user['role'],
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
        <h2 class="section-title">Login</h2>
        <?php if ($errors): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errors[0]) ?></div>
        <?php endif; ?>
        <form method="post" action="login.php" class="form-card">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="email@domain.com" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Masukkan password" required>

            <button type="submit" class="button button-primary">Masuk</button>
        </form>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php';
