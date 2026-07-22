<?php
require_once 'conexao.php';
session_start();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha']      ?? '';

    if (!$email || !$senha) {
        $erro = 'Preencha e-mail e senha.';
    } else {
        $pdo  = conectar();
        $stmt = $pdo->prepare("SELECT id, nome, email, senha FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if (!$usuario || $usuario['senha'] !== $senha) {
            $erro = 'E-mail ou senha incorretos.';
        } else {
            $_SESSION['usuario'] = [
                'id'    => $usuario['id'],
                'nome'  => $usuario['nome'],
                'email' => $usuario['email'],
            ];
            header('Location: principal.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MIND LINKS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="auth.css">
</head>
<body>
<div class="container-auth">
 
    <div class="logo-area">
        <div class="logo-title">MIND LINKS</div>
        <img src="img/logoBranco.png" alt="Logo" class="logo">
        <div class="logo-subtitle">MIND LINKS</div>
    </div>
 
    <div class="form-area">
        <h1>LOGIN</h1>
 
        <?php if ($erro): ?>
            <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
 
        <form method="POST">
            <div class="input-group">
                <i class="fa-solid fa-envelope icon"></i>
                <input type="email" name="email" class="input" placeholder="Seu e-mail"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
            </div>
 
            <div class="input-group">
                <i class="fa-solid fa-lock icon"></i>
                <input type="password" id="senha" name="senha" class="input" placeholder="Senha" required>
                <i class="fa-solid fa-eye eye-icon" id="toggleSenha"></i>
            </div>
 
            <button type="submit" class="button-login">Entrar</button>
        </form>
 
        <div class="links">
            <p>Não tem conta? <a href="cadastro.php">Cadastre-se aqui</a></p>
            <p><a href="esqueceu_senha.php">Esqueci minha senha</a></p>
        </div>
    </div>
</div>
 
<script>
document.getElementById('toggleSenha').addEventListener('click', function () {
    const input = document.getElementById('senha');
    if (input.type === 'password') {
        input.type = 'text';
        this.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        this.classList.replace('fa-eye-slash', 'fa-eye');
    }
});
</script>
</body>
</html>