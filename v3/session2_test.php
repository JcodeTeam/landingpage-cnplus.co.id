<?php
require_once __DIR__ . '/api/db.php';
require_once __DIR__ . '/api/session_handler.php';

$handler = new DbSessionHandler($pdo);
session_set_save_handler($handler, true);
session_name('web1_session');
session_start();

echo "<pre>";
print_r($_SESSION);
echo "</pre>";