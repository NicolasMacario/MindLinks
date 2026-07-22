<?php
session_start();
require_once 'conexao.php';
 

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}
 
$usuarioId = $_SESSION['usuario']['id'];
 
$pdo = conectar();
 

$stmt = $pdo->prepare('SELECT nome, email, criado_em FROM usuarios WHERE id = ?');
$stmt->execute([$usuarioId]);
$usuario = $stmt->fetch();
 
if (!$usuario) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$nome  = htmlspecialchars($usuario['nome']);
$email = htmlspecialchars($usuario['email']);
$membroDesde = date('d/m/Y', strtotime($usuario['criado_em']));
$iniciais = strtoupper(mb_substr($usuario['nome'], 0, 1));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meu Perfil</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
 
    <main class="profile-card">
 
        <h1 class="profile-name"><?= $nome ?></h1>
        <p class="profile-email"><?= $email ?></p>
 
        <dl class="profile-details">
            <div class="profile-row">
                <dt>Nome</dt>
                <dd><?= $nome ?></dd>
            </div>
            <div class="profile-row">
                <dt>E-mail</dt>
                <dd><?= $email ?></dd>
            </div>
            <div class="profile-row">
                <dt>Membro desde</dt>
                <dd><?= $membroDesde ?></dd>
            </div>
        </dl>
 
        <a href="logout.php" class="profile-logout">Sair</a>
    </main>
 
</body>
</html>