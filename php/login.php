<?php
require_once 'conexao.php';
require_once 'helpers.php';
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

        if (!$usuario || !password_verify($senha, $usuario['senha'])) {
            $erro = 'E-mail ou senha incorretos.';
        } else {
            $tokenSessao = bin2hex(random_bytes(32));

            $pdo->prepare("INSERT INTO sessoes_ativas
                            (usuario_id, token_sessao, dispositivo, ip, criado_em, ultimo_acesso)
                            VALUES (?, ?, ?, ?, NOW(), NOW())")
                ->execute([
                    $usuario['id'],
                    $tokenSessao,
                    detectarDispositivo($_SERVER['HTTP_USER_AGENT'] ?? ''),
                    $_SERVER['REMOTE_ADDR'] ?? null,
                ]);

            $_SESSION['usuario'] = [
                'id'           => $usuario['id'],
                'nome'         => $usuario['nome'],
                'email'        => $usuario['email'],
                'token_sessao' => $tokenSessao,
            ];
            header('Location: principal.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-tema="claro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MIND LINKS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="auth.css">
</head>
<body>

<div class="container-split">

    <div class="lado-esquerda">
        <h2>LOGIN</h2>

        <?php if ($erro): ?>
            <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
        <?php elseif (isset($_GET['deslogado'])): ?>
            <div class="alert alert-success">Você saiu de todos os dispositivos com segurança.</div>
        <?php elseif (isset($_GET['expirada'])): ?>
            <div class="alert alert-error">Sua sessão expirou. Faça login novamente.</div>
        <?php elseif (isset($_GET['conta_excluida'])): ?>
            <div class="alert alert-success">Sua conta foi excluída com sucesso.</div>
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

            <button type="submit" class="btn-primario">Entrar</button>
        </form>

        <h3>Não tem conta? <a href="cadastro.php">Cadastre-se aqui</a></h3>
        <h3><a href="esqueceu_senha.php">Esqueci minha senha</a></h3>
    </div>

    <div class="lado-direita">
        <h1>MIND LINKS</h1>
        <p>Conecte suas ideias, organize seus pensamentos e mantenha tudo em um só lugar.</p>
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