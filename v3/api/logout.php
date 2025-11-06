<?php
session_start();
require_once './db.php';

if (!empty($_COOKIE['admin_token'])) {
    $rawToken = $_COOKIE['admin_token'];
    $tokenHash = hash('sha256', $rawToken);

    $stmt = $pdo->prepare("UPDATE admin SET login_token_hash = NULL, login_token_expires_at = NULL WHERE login_token_hash = ?");
    $stmt->execute([$tokenHash]);

    setcookie('admin_token', '', time() - 3600, '/');
    unset($_COOKIE['admin_token']);
    setcookie('admin_username', '', time() - 3600, '/');
    unset($_COOKIE['admin_username']);
}

$_SESSION = [];
session_unset();
session_destroy();

header('Location: ../admin/');
exit;