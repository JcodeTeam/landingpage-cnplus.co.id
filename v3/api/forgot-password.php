<?php
session_start();
require_once './db.php'; // Sesuaikan path sesuai struktur folder

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';

    if (empty($username)) {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Username harus diisi']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, email FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            ob_clean();
            echo json_encode(['status' => 'error', 'message' => 'Username tidak ditemukan']);
            exit;
        }

        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        date_default_timezone_set('UTC');
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);

        $update = $pdo->prepare("UPDATE admin SET reset_token_hash = ?, reset_token_expires_at = ? WHERE id = ?");
        $update->execute([$tokenHash, $expiresAt, $admin['id']]);

        $resetLink = "https://cnplus.co.id/v3/admin/reset-password.php?token=" . urlencode($rawToken);

        $to = $admin['email'];
        $subject = "Reset Password CNPLUS Admin";
        $message = "
            <p>Halo,</p>
            <p>Kami menerima permintaan untuk reset password akun admin CNPLUS Anda.</p>
            <p>Klik tautan berikut untuk mengatur ulang password Anda (berlaku 1 jam):</p>
            <p><a href='$resetLink'>$resetLink</a></p>
            <p>Jika Anda tidak meminta reset password, abaikan pesan ini.</p>
            <br>
            <p>CNPLUS Security System</p>
        ";

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: CNPLUS Admin <no-reply@cnplus.co.id>\r\n";

        if (mail($to, $subject, $message, $headers)) {
            ob_clean();
            echo json_encode([
                'status' => 'success',
                'message' => 'Link reset password telah dikirim ke email terdaftar.'
            ]);
        } else {
            ob_clean();
            echo json_encode([
                'status' => 'error',
                'message' => 'Gagal mengirim email. Pastikan server mendukung fungsi mail().'
            ]);
        }
    } catch (PDOException $e) {
        error_log("Forgot password error: " . $e->getMessage());
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan server']);
    }
    exit;
}
