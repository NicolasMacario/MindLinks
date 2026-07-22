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
</head>
<body>
 
    <main>
 
        <h1><?= $nome ?></h1>
 
        <dl>
            <div>
                <dt>E-mail</dt>
                <dd><?= $email ?></dd>
            </div>
            <div>
                <dt>Membro desde</dt>
                <dd><?= $membroDesde ?></dd>
            </div>
        </dl>
    </main>
 
</body>
</html>