<?php
// includes/functions.php — fungsi bantu (helper) untuk seluruh aplikasi
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('USERS_FILE', __DIR__ . '/../data/users.json');

/** Sanitasi output/input dengan htmlspecialchars() */
function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/** Baca semua user dari file JSON */
function load_users(): array {
    if (!file_exists(USERS_FILE)) {
        return [];
    }
    $data = json_decode(file_get_contents(USERS_FILE), true);
    return is_array($data) ? $data : [];
}

/** Simpan semua user ke file JSON (dengan file lock) */
function save_users(array $users): bool {
    $json = json_encode(array_values($users), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents(USERS_FILE, $json, LOCK_EX) !== false;
}

/** Cari user berdasarkan email (case-insensitive) */
function find_user_by_email(string $email): ?array {
    foreach (load_users() as $user) {
        if (strcasecmp($user['email'], $email) === 0) {
            return $user;
        }
    }
    return null;
}

/** Flash message: pesan sekali tampil */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert alert-' . e($f['type']) . '">' . e($f['message']) . '</div>';
    }
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

/** Token CSRF sederhana */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): bool {
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

/** Login otomatis dari cookie "Remember Me" (bonus) */
function try_remember_login(): void {
    if (!empty($_SESSION['user']) || empty($_COOKIE['remember'])) {
        return;
    }
    $parts = explode(':', $_COOKIE['remember'], 2);
    if (count($parts) !== 2) {
        return;
    }
    [$id, $token] = $parts;
    foreach (load_users() as $user) {
        if ($user['id'] === $id && !empty($user['remember_hash'])
            && hash_equals($user['remember_hash'], hash('sha256', $token))) {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email']];
            return;
        }
    }
}

function is_logged_in(): bool {
    try_remember_login();
    return !empty($_SESSION['user']);
}

/** Halaman terproteksi: redirect ke login jika belum login */
function require_login(): void {
    if (!is_logged_in()) {
        set_flash('error', 'Silakan login terlebih dahulu.');
        redirect('login.php');
    }
}
