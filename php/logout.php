<?php
// logout.php
require_once 'conexao.php';
session_start();
 
if (!empty($_SESSION['usuario']['id']) && !empty($_SESSION['usuario']['token_sessao'])) {
    $pdo = conectar();
    $pdo->prepare("DELETE FROM sessoes_ativas WHERE usuario_id = ? AND token_sessao = ?")
        ->execute([$_SESSION['usuario']['id'], $_SESSION['usuario']['token_sessao']]);
}
 
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;
 