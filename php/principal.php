<?php
session_start();

if (empty($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal - MIND LINKS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="auth.css">
</head>
<body>
<div class="container-auth">
    <div class="form-area" style="width:400px; text-align:center; align-items:center;">
        <h1>BEM-VINDO</h1>

        <i class="fa-solid fa-circle-user" style="font-size:5rem; color:#7a3df5; margin-bottom:20px;"></i>

        <p style="color:#c699ff; font-size:1.2rem; font-weight:600; margin-bottom:8px;">
            <?= htmlspecialchars($usuario['nome']) ?>
        </p>

        <p style="color:#aaaaaa; font-size:0.9rem; margin-bottom:30px;">
            <?= htmlspecialchars($usuario['email']) ?>
        </p>

        <a href="perfil.php" class="btn">Ir para a tela de perfil</a>
    </div>
</div>
</body>
</html>