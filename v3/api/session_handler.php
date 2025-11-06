<?php
require_once __DIR__ . '/db.php';

class DbSessionHandler implements SessionHandlerInterface {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function open($savePath, $sessionName) {
        return true;
    }

    public function close() {
        return true;
    }

    public function read($id) {
        $stmt = $this->pdo->prepare("SELECT data FROM sessions WHERE id = ?");
        $stmt->execute([$id]);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $row['data'];
        }
        return '';
    }

    public function write($id, $data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO sessions (id, data, last_access)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE data = VALUES(data), last_access = NOW()
        ");
        return $stmt->execute([$id, $data]);
    }

    public function destroy($id) {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function gc($maxlifetime) {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE last_access < (NOW() - INTERVAL ? SECOND)");
        return $stmt->execute([$maxlifetime]);
    }
}
