<?php
require_once 'conexao.php';

$erro    = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmaSenha = $_POST['confirma_senha'] ?? '';

    if (!$nome || !$email || !$senha || !$confirmaSenha) {
        $erro = 'Preencha todos os campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } elseif (strlen($senha) < 8) {
        $erro = 'A senha deve ter pelo menos 8 caracteres.';
    } elseif ($senha !== $confirmaSenha) {
        $erro = 'As senhas não conferem.';
    } else {
        $pdo  = conectar();
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $erro = 'Este e-mail já está cadastrado.';
        } else {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)")
                ->execute([$nome, $email, $senhaHash]);

            $sucesso = 'Cadastro realizado com sucesso!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-tema="claro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - MIND LINKS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="auth.css">
</head>
<body>

<div class="container-split">

    <div class="lado-esquerda">
        <h2>CADASTRO</h2>

        <?php if ($erro): ?>
            <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
            <h3><a href="login.php">Ir para o login →</a></h3>
        <?php else: ?>

        <form method="POST">
            <div class="input-group">
                <i class="fa-solid fa-user icon"></i>
                <input type="text" name="nome" class="input" placeholder="Seu nome"
                    value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required autofocus>
            </div>

            <div class="input-group">
                <i class="fa-solid fa-envelope icon"></i>
                <input type="email" name="email" class="input" placeholder="Seu e-mail"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>

            <div class="input-group">
                <i class="fa-solid fa-lock icon"></i>
                <input type="password" id="senha" name="senha" class="input" placeholder="Senha (mín. 8 caracteres)" required>
                <i class="fa-solid fa-eye eye-icon" id="toggleSenha"></i>
            </div>

            <div class="input-group">
                <i class="fa-solid fa-lock icon"></i>
                <input type="password" id="confirma_senha" name="confirma_senha" class="input" placeholder="Confirmar senha" required>
                <i class="fa-solid fa-eye eye-icon" id="toggleConfirma"></i>
            </div>

            <button type="submit" class="btn-primario">Criar Conta</button>
        </form>

        <h3>Já tem conta? <a href="login.php">Faça login aqui</a></h3>

        <?php endif; ?>
    </div>

    <div class="lado-direita">
        <h1>MIND LINKS</h1>
        <p>Crie sua conta e comece a conectar suas ideias hoje mesmo.</p>
    </div>

</div>

<script>
function toggle(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
document.getElementById('toggleSenha')?.addEventListener('click', () => toggle('senha', 'toggleSenha'));
document.getElementById('toggleConfirma')?.addEventListener('click', () => toggle('confirma_senha', 'toggleConfirma'));
</script>
</body>
</html>