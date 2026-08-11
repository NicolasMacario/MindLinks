<?php
// confirmar_alteracao.php
require_once 'conexao.php';
require_once 'src/PHPMailer.php';
require_once 'src/SMTP.php';
require_once 'src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarEmailConfirmacaoNovo(string $destino, string $nome, string $link): bool
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
        $mail->addAddress($destino, $nome);
        $mail->isHTML(false);
        $mail->Subject = 'Confirme seu novo e-mail - MIND LINKS';
        $mail->Body    = "Olá, {$nome}!\n\n"
            . "Confirmamos a solicitação de troca de e-mail no seu endereço atual.\n\n"
            . "Para concluir, confirme este novo endereço clicando no link abaixo (válido por 1 hora):\n\n{$link}\n\n"
            . "Se não foi você quem solicitou, ignore este e-mail.";

        return $mail->send();
    } catch (Exception $e) {
        error_log('Erro ao enviar e-mail: ' . $mail->ErrorInfo);
        return false;
    }
}

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
        // ── Etapa 1: confirmação feita no e-mail ATUAL ──
        if ($pendente['tipo'] === 'email_atual') {
            $pdo->prepare("UPDATE confirmacoes_pendentes SET usado = 1 WHERE id = ?")
                ->execute([$pendente['id']]);

            $stmtUser = $pdo->prepare("SELECT nome, email FROM usuarios WHERE id = ?");
            $stmtUser->execute([$pendente['usuario_id']]);
            $dono = $stmtUser->fetch();

            $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $check->execute([$pendente['valor_novo'], $pendente['usuario_id']]);

            if ($check->fetch()) {
                $mensagem = 'Este e-mail já foi utilizado por outra conta. Solicite a alteração novamente.';
                $tipo     = 'erro';
            } else {
                $novoToken = bin2hex(random_bytes(32));
                $expira    = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $pdo->prepare("INSERT INTO confirmacoes_pendentes
                                (usuario_id, tipo, valor_novo, token, expira_em)
                                VALUES (?, 'email_novo', ?, ?, ?)")
                    ->execute([$pendente['usuario_id'], $pendente['valor_novo'], $novoToken, $expira]);

                $link    = SITE_URL . '/confirmar_alteracao.php?token=' . $novoToken;
                $enviado = enviarEmailConfirmacaoNovo($pendente['valor_novo'], $dono['nome'], $link);

                if ($enviado) {
                    $mensagem = "Confirmação recebida! Agora enviamos um link para o seu novo e-mail "
                        . "({$pendente['valor_novo']}) para concluir a troca.";
                    $tipo = 'sucesso';
                } else {
                    $mensagem = 'Não foi possível enviar o e-mail de confirmação para o novo endereço. '
                        . 'Tente novamente mais tarde.';
                    $tipo = 'erro';
                }
            }
        }

        // ── Etapa 2: confirmação feita no e-mail NOVO → troca efetivada ──
        elseif ($pendente['tipo'] === 'email_novo') {
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

            $pdo->prepare("UPDATE confirmacoes_pendentes SET usado = 1 WHERE id = ?")
                ->execute([$pendente['id']]);

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION = [];
            session_destroy();
        }

        // ── Troca de senha ──
        elseif ($pendente['tipo'] === 'senha') {
            $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?")
                ->execute([$pendente['valor_novo'], $pendente['usuario_id']]);

            $pdo->prepare("UPDATE confirmacoes_pendentes SET usado = 1 WHERE id = ?")
                ->execute([$pendente['id']]);

            $mensagem = 'Senha atualizada com sucesso! Use a nova senha para entrar na sua conta.';
            $tipo     = 'sucesso';

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION = [];
            session_destroy();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-tema="claro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar alteração - MIND LINKS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="auth.css">
</head>
<body>

<div class="container">
    <div class="box">
        <h2>Confirmação</h2>

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