<?php
// config/conexao.php

$env = parse_ini_file(__DIR__ . '/../.env');

define('DB_HOST', '127.0.0.1:3312');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mindlinks');

define('SITE_URL', 'http://localhost/mindlinks/php');
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'mindlinkstm@gmail.com');
define('SMTP_PASS', 'mtsb dziy abng rspa');
define('SMTP_PORT', '587');
define('SMTP_FROM_NAME', 'Mindlinks');

function conectar(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }
    return $pdo;
}