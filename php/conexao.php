<?php
// config/conexao.php

$env = parse_ini_file(__DIR__ . '/../.env');

define('DB_HOST', $env['DB_HOST']);
define('DB_USER', $env['DB_USER']);
define('DB_PASS', $env['DB_PASS']);
define('DB_NAME', $env['DB_NAME']);

define('SITE_URL', 'http://localhost/mindlinks/php');
define('SMTP_HOST', $env['SMTP_HOST']);
define('SMTP_USER', $env['SMTP_USER']);
define('SMTP_PASS', $env['SMTP_PASS']);
define('SMTP_PORT', $env['SMTP_PORT']);
define('SMTP_FROM_NAME', $env['SMTP_FROM_NAME']);

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