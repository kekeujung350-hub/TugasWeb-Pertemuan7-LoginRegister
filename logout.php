<?php
require_once 'includes/functions.php';

// Hapus token remember-me di file JSON & cookie
if (!empty($_SESSION['user'])) {
    $users = load_users();
    foreach ($users as &$u) {
        if ($u['id'] === $_SESSION['user']['id']) {
            $u['remember_hash'] = null;
        }
    }
    unset($u);
    save_users($users);
}
setcookie('remember', '', time() - 3600, '/');

// Hancurkan session
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

session_start();
set_flash('success', 'Anda telah logout.');
redirect('login.php');
