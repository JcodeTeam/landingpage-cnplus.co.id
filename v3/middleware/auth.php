<?php
require_once __DIR__ . '/../api/db.php'; 
session_start();

if (!isset($_COOKIE['admin_token'])) {
    header('Location: ./');
    exit;
}

$rawToken = $_COOKIE['admin_token'];
$tokenHash = hash('sha256', $rawToken);

$stmt = $pdo->prepare("SELECT id, username, login_token_expires_at FROM admin WHERE login_token_hash = ?");
$stmt->execute([$tokenHash]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    setcookie('admin_token', '', time() - 3600, '/');
    unset($_COOKIE['admin_token']);
    header('Location: ./');
    exit;
}

if (strtotime($user['login_token_expires_at']) < time()) {
    $del = $pdo->prepare("UPDATE admin SET login_token_hash = NULL, login_token_expires_at = NULL WHERE id = ?");
    $del->execute([$user['id']]);
    setcookie('admin_token', '', time() - 3600, '/');
    unset($_COOKIE['admin_token']);
    header('Location: ./');
    exit;
}
