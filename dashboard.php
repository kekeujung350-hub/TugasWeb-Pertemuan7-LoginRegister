<?php
require_once 'includes/functions.php';
require_login(); // halaman terproteksi

$user = $_SESSION['user'];
$pageTitle = 'Dashboard';
include 'includes/header.php';
?>
<div class="card">
    <h1>Dashboard</h1>
    <?php show_flash(); ?>
    <p>Halo, <strong><?= e($user['name']) ?></strong> 👋</p>
    <p class="muted">Email: <?= e($user['email']) ?></p>
    <p>Anda berhasil login. Halaman ini hanya bisa diakses setelah login.</p>
    <div class="actions">
        <a class="btn" href="edit_profile.php">Edit Profil</a>
        <a class="btn btn-danger" href="logout.php">Logout</a>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
