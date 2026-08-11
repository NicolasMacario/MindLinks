<?php

require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/lang.php';
 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
if (empty($_SESSION['usuario']) || empty($_SESSION['usuario']['token_sessao'])) {
    header('Location: login.php');
    exit;
}
 
$pdo = conectar();
 
$stmt = $pdo->prepare("SELECT id FROM sessoes_ativas WHERE usuario_id = ? AND token_sessao = ? LIMIT 1");
$stmt->execute([$_SESSION['usuario']['id'], $_SESSION['usuario']['token_sessao']]);
 
if (!$stmt->fetch()) {
    // Sessão foi encerrada (ex.: "Sair de todos os dispositivos") ou expirou/nao existe mais
    $_SESSION = [];
    session_destroy();
    header('Location: login.php?expirada=1');
    exit;
}
 
// Atualiza o "último acesso" desta sessão para aparecer certo na lista de sessões ativas
$pdo->prepare("UPDATE sessoes_ativas SET ultimo_acesso = NOW() WHERE usuario_id = ? AND token_sessao = ?")
    ->execute([$_SESSION['usuario']['id'], $_SESSION['usuario']['token_sessao']]);
    $stmt = $pdo->prepare("
    SELECT tema, idioma
    FROM preferencias_usuario
    WHERE usuario_id = ?
    LIMIT 1
");

$stmt->execute([$_SESSION['usuario']['id']]);

$prefs = $stmt->fetch();

$tema = $prefs['tema'] ?? 'escuro';
$idioma = $prefs['idioma'] ?? 'pt-BR';
definirIdioma($idioma);

$stmt->execute([$_SESSION['usuario']['id']]);

$prefs = $stmt->fetch();

$tema = $prefs['tema'] ?? 'escuro';