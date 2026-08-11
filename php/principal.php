<?php
require_once 'auth_check.php';
/** @var PDO $pdo */
/** @var string $tema */
/** @var string $idioma */
// $pdo, $tema e $idioma já vêm prontos do auth_check.php

$usuario = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($idioma) ?>" data-tema="<?= htmlspecialchars($tema) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('bem_vindo') ?> - MIND LINKS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="auth.css">
</head>
<body>

<nav class="navbar">
    <div class="logo">Mind Links</div>
    <ul class="nav-links">
        <li><a href="principal.php" class="ativo"><?= t('inicio') ?></a></li>
        <li><a href="perfil.php"><?= t('seu_perfil') ?></a></li>
        <li><a href="configuracoes.php"><?= t('configuracoes') ?></a></li>
    </ul>
</nav>

<div class="container">
    <div class="box">
        <h1><?= t('bem_vindo') ?></h1>

        <i class="fa-solid fa-circle-user" style="font-size:5rem; color:#7a3df5; margin:20px 0;"></i>

        <p style="color:#c699ff; font-size:1.2rem; font-weight:600; margin-bottom:8px;">
            <?= htmlspecialchars($usuario['nome']) ?>
        </p>

        <p style="margin-bottom:30px;">
            <?= htmlspecialchars($usuario['email']) ?>
        </p>

        <a href="perfil.php" class="btn-inicio"><?= t('ir_perfil') ?></a>
        <a href="configuracoes.php" class="btn-inicio" style="margin-top:10px;"><?= t('configuracoes') ?></a>

        <div class="links">
            <p><a href="logout.php"><?= t('sair') ?></a></p>
        </div>
    </div>
</div>

</body>
</html>