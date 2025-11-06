<?php
session_start();

require_once './db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        echo json_encode(['status'=>'error','message'=>'Username dan password harus diisi']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username=?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password,$admin['password'])) {
        $rawToken = bin2hex(random_bytes(32)); 
        $tokenHash = hash('sha256', $rawToken);
        $expiresInSeconds = 86400; 
        $expiresAt = date('Y-m-d H:i:s', time() + $expiresInSeconds);
        
        $update = $pdo->prepare("UPDATE admin SET login_token_hash = ?, login_token_expires_at = ? WHERE id = ?");
        $update->execute([$tokenHash, $expiresAt, $admin['id']]);
        
        $cookieOptions = [
            'expires' => time() + $expiresInSeconds,
            'path' => '/',
            'domain' => 'cnplus.co.id',       
            'secure' => true,   
            'httponly' => true,    
            'samesite' => 'Strict' 
        ];
        
        setcookie('admin_token', $rawToken, $cookieOptions);
        setcookie('admin_username', $username, $cookieOptions);
        
        session_write_close();
        echo json_encode(['status'=>'success','redirect'=>'../admin/dashboard.php']);
    } else {
        echo json_encode(['status'=>'error','message'=>'Username atau password salah']);
    }
    exit;
}
