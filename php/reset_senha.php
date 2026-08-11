<?php
// redefinir-senha.php
require_once 'conexao.php';

$token   = trim($_POST['token'] ?? $_GET['token'] ?? '');
$erro    = '';
$sucesso = '';
$tokenOk = false;
$usuario = null;

if ($token) {
    $pdo  = conectar();
    $stmt = $pdo->prepare("
        SELECT r.id AS rec_id, r.usuario_id, u.nome, u.email
        FROM recuperacao_senha r
        JOIN usuarios u ON u.id = r.usuario_id
        WHERE r.token = ?
          AND r.usado = 0
          AND r.expira_em > NOW()
        LIMIT 1");
    $stmt->execute([$token]);
    $usuario = $stmt->fetch();
    $tokenOk = (bool) $usuario;
}

if (!$tokenOk && !$sucesso) {
    $erro = 'Este link é inválido ou já expirou. Solicite um novo link de redefinição.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenOk) {
    $nova   = $_POST['nova_senha'] ?? '';
    $conf   = $_POST['conf_senha'] ?? '';
    $tokenP = $_POST['token']      ?? '';

    if (strlen($nova) < 8) {
        $erro = 'A senha deve ter pelo menos 8 caracteres.';
    } elseif (!preg_match('/[A-Za-z]/', $nova) || !preg_match('/[0-9]/', $nova)) {
        $erro = 'Use ao menos uma letra e um número na nova senha.';
    } elseif ($nova !== $conf) {
        $erro = 'As senhas não coincidem.';
    } else {
        $pdo = conectar();

        $novaHash = password_hash($nova, PASSWORD_DEFAULT);

        $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?")
            ->execute([$novaHash, $usuario['usuario_id']]);

        $pdo->prepare("UPDATE recuperacao_senha SET usado = 1 WHERE token = ?")
            ->execute([$tokenP]);

        $sucesso = 'Senha redefinida com sucesso! Você já pode fazer login.';
        $tokenOk = false;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-tema="claro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir senha - MIND LINKS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="auth.css">
</head>
<body>

<div class="container-split">

    <div class="lado-esquerda">
        <h2>NOVA SENHA</h2>

        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
            <h3><a href="login.php">Ir para o login →</a></h3>

        <?php elseif ($erro && !$tokenOk && !$usuario): ?>
            <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
            <h3><a href="esqueci-senha.php">Solicitar novo link</a></h3>

        <?php else: ?>
            <p>
                Olá, <strong style="color:#8151c1;"><?= htmlspecialchars($usuario['nome']) ?></strong>!
                Crie uma nova senha para sua conta.
            </p>

            <?php if ($erro): ?>
                <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div class="input-group">
                    <i class="fa-solid fa-lock icon"></i>
                    <input type="password" id="nova_senha" name="nova_senha" class="input"
                        placeholder="Nova senha (mín. 8 caracteres)" required autofocus>
                    <i class="fa-solid fa-eye eye-icon" id="toggleNova"></i>
                </div>

                <div class="input-group">
                    <i class="fa-solid fa-lock icon"></i>
                    <input type="password" id="conf_senha" name="conf_senha" class="input"
                        placeholder="Confirmar nova senha" required>
                    <i class="fa-solid fa-eye eye-icon" id="toggleConf"></i>
                </div>

                <button type="submit" class="btn-primario">Salvar nova senha</button>
            </form>

            <h3><a href="login.php">← Cancelar e voltar ao login</a></h3>
        <?php endif; ?>
    </div>

    <div class="lado-direita">
        <h1>MIND LINKS</h1>
        <p>Escolha uma senha forte para manter sua conta segura.</p>
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
document.getElementById('toggleNova')?.addEventListener('click', () => toggle('nova_senha', 'toggleNova'));
document.getElementById('toggleConf')?.addEventListener('click', () => toggle('conf_senha',  'toggleConf'));
</script>
</body>
</html>