<?php
require_once 'auth_check.php';
$pdo = conectar();

$stmt = $pdo->prepare("SELECT tema, idioma FROM preferencias_usuario WHERE usuario_id = ?");
$stmt->execute([$_SESSION['usuario']['id']]);

$prefs = $stmt->fetch();

$tema   = $prefs['tema']   ?? 'escuro';
$idioma = $prefs['idioma'] ?? 'pt-BR';

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
<div class="container-auth">
    <div class="form-area">
        <h1><?= t('bem_vindo') ?></h1>

        <i class="fa-solid fa-circle-user"></i>

        <p>
            <?= htmlspecialchars($usuario['nome']) ?>
        </p>

        <p style="color:#aaaaaa; font-size:0.9rem; margin-bottom:30px;">
            <?= htmlspecialchars($usuario['email']) ?>
        </p>

        <a href="perfil.php" class="btn"><?= t('ir_perfil') ?></a>
        <a href="configuracoes.php" class="btn"><?= t('configuracoes') ?></a>

        <div class="links">
            <p><a href="logout.php"><?= t('sair') ?></a></p>
        </div>
    </div>
</div>
</body>
</html>