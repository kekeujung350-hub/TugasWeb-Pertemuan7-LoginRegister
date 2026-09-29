<?php
require_once 'includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Token keamanan tidak valid. Muat ulang halaman.';
    }

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    $oldEmail = e($email);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $errors[] = 'Email dan password wajib diisi dengan benar.';
    }

    if (!$errors) {
        $user = find_user_by_email($email);
        // password_verify() membandingkan dengan hash
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true); // cegah session fixation
            $_SESSION['user'] = [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
            ];

            // Bonus: Remember Me dengan cookie
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $users = load_users();
                foreach ($users as &$u) {
                    if ($u['id'] === $user['id']) {
                        $u['remember_hash'] = hash('sha256', $token);
                    }
                }
                unset($u);
                save_users($users);
                setcookie('remember', $user['id'] . ':' . $token, [
                    'expires'  => time() + 60 * 60 * 24 * 30,
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }

            set_flash('success', 'Selamat datang, ' . $user['name'] . '!');
            redirect('dashboard.php');
        }
        // Pesan generik agar tidak membocorkan email mana yang terdaftar
        $errors[] = 'Email atau password salah.';
    }
}

$pageTitle = 'Login';
include 'includes/header.php';
?>
<div class="card">
    <h1>Login</h1>
    <?php show_flash(); ?>
    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form method="POST" action="login.php" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= $oldEmail ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <label class="checkbox">
            <input type="checkbox" name="remember"> Ingat saya (30 hari)
        </label>

        <button type="submit">Login</button>
    </form>
    <p class="muted">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
</div>
<?php include 'includes/footer.php'; ?>
