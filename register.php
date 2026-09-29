<?php
require_once 'includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$old = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Token keamanan tidak valid. Muat ulang halaman.';
    }

    // Sanitasi input dengan htmlspecialchars()
    $name     = e(trim($_POST['name'] ?? ''));
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $old = ['name' => $name, 'email' => e($email)];

    // Validasi
    if ($name === '' || mb_strlen($name) < 3) {
        $errors[] = 'Nama wajib diisi (minimal 3 karakter).';
    }
    // Validasi email dengan filter_var()
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors[] = 'Password harus mengandung huruf dan angka.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    // Cek duplikasi email
    if (!$errors && find_user_by_email($email)) {
        $errors[] = 'Email sudah terdaftar. Gunakan email lain atau login.';
    }

    if (!$errors) {
        $users = load_users();
        $users[] = [
            'id'            => bin2hex(random_bytes(8)),
            'name'          => $name,
            'email'         => strtolower($email),
            'password'      => password_hash($password, PASSWORD_DEFAULT), // hash password
            'remember_hash' => null,
            'created_at'    => date('Y-m-d H:i:s'),
        ];
        if (save_users($users)) {
            set_flash('success', 'Registrasi berhasil! Silakan login.');
            redirect('login.php');
        }
        $errors[] = 'Gagal menyimpan data. Periksa izin folder data/.';
    }
}

$pageTitle = 'Register';
include 'includes/header.php';
?>
<div class="card">
    <h1>Daftar Akun</h1>
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
    <form method="POST" action="register.php" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <label for="name">Nama Lengkap</label>
        <input type="text" id="name" name="name" value="<?= $old['name'] ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= $old['email'] ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Konfirmasi Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit">Daftar</button>
    </form>
    <p class="muted">Sudah punya akun? <a href="login.php">Login di sini</a></p>
</div>
<?php include 'includes/footer.php'; ?>
