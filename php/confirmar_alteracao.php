<?php
// confirmar_alteracao.php
require_once 'conexao.php';

$token    = trim($_GET['token'] ?? '');
$mensagem = '';
$tipo     = '';

if (!$token) {
    $mensagem = 'Link inválido.';
    $tipo     = 'erro';
} else {
    $pdo  = conectar();
    $stmt = $pdo->prepare("SELECT * FROM confirmacoes_pendentes
                            WHERE token = ? AND usado = 0 AND expira_em > NOW()
                            LIMIT 1");
    $stmt->execute([$token]);
    $pendente = $stmt->fetch();

    if (!$pendente) {
        $mensagem = 'Este link é inválido ou já expirou. Solicite a alteração novamente na sua tela de perfil.';
        $tipo     = 'erro';
    } else {
        if ($pendente['tipo'] === 'email') {
            // Garante que o e-mail não foi ocupado por outra conta enquanto o link estava pendente
            $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $check->execute([$pendente['valor_novo'], $pendente['usuario_id']]);

            if ($check->fetch()) {
                $mensagem = 'Este e-mail já foi utilizado por outra conta enquanto a confirmação estava pendente.';
                $tipo     = 'erro';
            } else {
                $pdo->prepare("UPDATE usuarios SET email = ? WHERE id = ?")
                    ->execute([$pendente['valor_novo'], $pendente['usuario_id']]);

                $mensagem = 'E-mail atualizado com sucesso! Use o novo e-mail para entrar na sua conta.';
                $tipo     = 'sucesso';
            }
        } elseif ($pendente['tipo'] === 'senha') {
            $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?")
                ->execute([$pendente['valor_novo'], $pendente['usuario_id']]);

            $mensagem = 'Senha atualizada com sucesso! Use a nova senha para entrar na sua conta.';
            $tipo     = 'sucesso';
        }

        $pdo->prepare("UPDATE confirmacoes_pendentes SET usado = 1 WHERE id = ?")
            ->execute([$pendente['id']]);

        // Por segurança, encerra a sessão atual: o usuário precisa logar de novo
        // com o e-mail/senha atualizados.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        session_destroy();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar alteração - MIND LINKS</title>
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

    <div class="form-area" style="text-align:center; align-items:center;">
        <h1>Confirmação</h1>

        <?php if ($tipo === 'sucesso'): ?>
            <i class="fa-solid fa-circle-check" style="font-size:3rem; color:#5ffe6f; margin-bottom:15px;"></i>
        <?php else: ?>
            <i class="fa-solid fa-circle-exclamation" style="font-size:3rem; color:#ff6b6b; margin-bottom:15px;"></i>
        <?php endif; ?>

        <div class="alert alert-<?= $tipo === 'sucesso' ? 'success' : 'error' ?>">
            <?= htmlspecialchars($mensagem) ?>
        </div>

        <div class="links">
            <p><a href="login.php">Ir para o login →</a></p>
        </div>
    </div>
</div>
</body>
</html>
