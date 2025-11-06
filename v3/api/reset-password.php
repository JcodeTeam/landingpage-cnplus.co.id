<?php
require_once './db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($token) || empty($newPassword) || empty($confirmPassword)) {
        echo json_encode(['status' => 'error', 'message' => 'Semua kolom harus diisi']);
        exit;
    }

    if ($newPassword !== $confirmPassword) {
        echo json_encode(['status' => 'error', 'message' => 'Password tidak cocok']);
        exit;
    }

    $tokenHash = hash('sha256', $token);
    $stmt = $pdo->prepare("SELECT id FROM admin WHERE reset_token_hash = ? AND reset_token_expires_at > NOW()");
    $stmt->execute([$tokenHash]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        echo json_encode(['status' => 'error', 'message' => 'Token tidak valid atau sudah kedaluwarsa']);
        exit;
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $update = $pdo->prepare("UPDATE admin SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = ?");
    $update->execute([$hashedPassword, $admin['id']]);

    echo json_encode(['status' => 'success', 'redirect' => '../admin/']);
    exit;
}
