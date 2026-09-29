<?php
// Bonus: halaman edit profile
require_once 'includes/functions.php';
require_login();

$errors = [];
$current = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Token keamanan tidak valid.';
    }

    $name     = e(trim($_POST['name'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($name === '' || mb_strlen($name) < 3) {
        $errors[] = 'Nama wajib diisi (minimal 3 karakter).';
    }
    if ($password !== '' && (strlen($password) < 8
        || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password))) {
        $errors[] = 'Password baru minimal 8 karakter dan mengandung huruf + angka.';
    }

    if (!$errors) {
        $users = load_users();
        foreach ($users as &$u) {
            if ($u['id'] === $current['id']) {
                $u['name'] = $name;
                if ($password !== '') {
                    $u['password'] = password_hash($password, PASSWORD_DEFAULT);
                }
            }
        }
        unset($u);
        save_users($users);
        $_SESSION['user']['name'] = $name;
        set_flash('success', 'Profil berhasil diperbarui.');
        redirect('dashboard.php');
    }
}

$pageTitle = 'Edit Profil';
include 'includes/header.php';
?>
<div class="card">
    <h1>Edit Profil</h1>
    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>
    <form method="POST" action="edit_profile.php" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <label for="name">Nama Lengkap</label>
        <input type="text" id="name" name="name" value="<?= e($_SESSION['user']['name']) ?>" required>

        <label>Email (tidak dapat diubah)</label>
        <input type="email" value="<?= e($current['email']) ?>" disabled>

        <label for="password">Password Baru <span class="muted">(kosongkan jika tidak diganti)</span></label>
        <input type="password" id="password" name="password">

        <button type="submit">Simpan</button>
    </form>
    <p class="muted"><a href="dashboard.php">← Kembali ke Dashboard</a></p>
</div>
<?php include 'includes/footer.php'; ?>
