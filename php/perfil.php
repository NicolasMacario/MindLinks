<?php
session_start();
require_once 'conexao.php';
require_once 'src/PHPMailer.php';
require_once 'src/SMTP.php';
require_once 'src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (empty($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

$pdo       = conectar();
$usuarioId = (int) $_SESSION['usuario']['id'];

// Sempre busca os dados atuais no banco (a sessão pode estar desatualizada)
$stmt = $pdo->prepare("SELECT id, nome, email, senha, foto_perfil FROM usuarios WHERE id = ? LIMIT 1");
$stmt->execute([$usuarioId]);
$usuario = $stmt->fetch();

if (!$usuario) {
    session_destroy();
    header('Location: login.php');
    exit;
}

/**
 * Envia o e-mail de confirmação de alteração (e-mail atual, e-mail novo ou senha).
 */
function enviarEmailConfirmacao(string $destino, string $nome, string $link, string $tipo): bool
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

        if ($tipo === 'email_atual') {
            $mail->Subject = 'Confirme a solicitação de troca de e-mail - MIND LINKS';
            $mail->Body    = "Olá, {$nome}!\n\n"
                . "Recebemos uma solicitação para alterar o e-mail da sua conta MIND LINKS.\n\n"
                . "Para continuar, confirme que foi você quem solicitou clicando no link abaixo (válido por 1 hora):\n\n{$link}\n\n"
                . "Depois dessa confirmação, enviaremos outro link para o novo e-mail informado.\n\n"
                . "Se não foi você quem solicitou, ignore este e-mail e sua conta continuará inalterada.";
        } elseif ($tipo === 'email_novo') {
            $mail->Subject = 'Confirme seu novo e-mail - MIND LINKS';
            $mail->Body    = "Olá, {$nome}!\n\n"
                . "Confirmamos a solicitação de troca de e-mail no seu endereço atual.\n\n"
                . "Para concluir, confirme este novo endereço clicando no link abaixo (válido por 1 hora):\n\n{$link}\n\n"
                . "Se não foi você quem solicitou, ignore este e-mail.";
        } else {
            $mail->Subject = 'Confirme a troca de senha - MIND LINKS';
            $mail->Body    = "Olá, {$nome}!\n\n"
                . "Recebemos uma solicitação para alterar a senha da sua conta MIND LINKS.\n\n"
                . "Clique no link abaixo para confirmar a nova senha (válido por 1 hora):\n\n{$link}\n\n"
                . "Se não foi você quem solicitou, ignore este e-mail e sua senha atual continuará válida.";
        }

        return $mail->send();
    } catch (Exception $e) {
        error_log('Erro ao enviar e-mail: ' . $mail->ErrorInfo);
        return false;
    }
}

$erro    = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    // ───────────────────────────── NOME ─────────────────────────────
    if ($acao === 'nome') {
        $novoNome = trim($_POST['nome'] ?? '');

        if ($novoNome === '') {
            $erro = 'Informe um nome válido.';
        } elseif (mb_strlen($novoNome) > 100) {
            $erro = 'O nome deve ter no máximo 100 caracteres.';
        } else {
            $pdo->prepare("UPDATE usuarios SET nome = ? WHERE id = ?")
                ->execute([$novoNome, $usuarioId]);

            $usuario['nome']             = $novoNome;
            $_SESSION['usuario']['nome'] = $novoNome;
            $sucesso = 'Nome atualizado com sucesso!';
        }
    }

    // ───────────────────────── FOTO DE PERFIL ────────────────────────
    elseif ($acao === 'foto') {
        if (empty($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
            $erro = 'Selecione uma imagem para enviar.';
        } elseif ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            $erro = 'Falha no envio da imagem. Tente novamente.';
        } else {
            $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $finfo      = new finfo(FILEINFO_MIME_TYPE);
            $mime       = $finfo->file($_FILES['foto']['tmp_name']);

            if (!isset($permitidos[$mime])) {
                $erro = 'Formato inválido. Envie uma imagem JPG, PNG ou WEBP.';
            } elseif ($_FILES['foto']['size'] > 3 * 1024 * 1024) {
                $erro = 'A imagem deve ter no máximo 3MB.';
            } else {
                $dir = __DIR__ . '/uploads/perfil';
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                $nomeArquivo = 'user_' . $usuarioId . '_' . time() . '.' . $permitidos[$mime];
                $caminho     = $dir . '/' . $nomeArquivo;

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminho)) {
                    if (!empty($usuario['foto_perfil'])) {
                        $antiga = $dir . '/' . $usuario['foto_perfil'];
                        if (is_file($antiga)) {
                            unlink($antiga);
                        }
                    }

                    $pdo->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?")
                        ->execute([$nomeArquivo, $usuarioId]);

                    $usuario['foto_perfil'] = $nomeArquivo;
                    $sucesso = 'Foto de perfil atualizada!';
                } else {
                    $erro = 'Não foi possível salvar a imagem. Tente novamente.';
                }
            }
        }
    }

    // ───────────────────────────── E-MAIL ────────────────────────────
    // 1ª etapa: valida senha + novo e-mail e manda link de confirmação
    // para o e-mail ATUAL. Só depois de confirmado ali é que mandamos
    // o segundo link para o e-mail NOVO (ver confirmar_alteracao.php).
    elseif ($acao === 'email') {
        $senhaAtual = $_POST['senha_atual_email'] ?? '';
        $novoEmail  = trim($_POST['novo_email'] ?? '');

        if (!password_verify($senhaAtual, $usuario['senha'])) {
            $erro = 'Senha atual incorreta.';
        } elseif (!filter_var($novoEmail, FILTER_VALIDATE_EMAIL)) {
            $erro = 'Informe um e-mail válido.';
        } elseif (strcasecmp($novoEmail, $usuario['email']) === 0) {
            $erro = 'Este já é o seu e-mail atual.';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$novoEmail, $usuarioId]);

            if ($stmt->fetch()) {
                $erro = 'Este e-mail já está em uso por outra conta.';
            } else {
                $pdo->prepare("UPDATE confirmacoes_pendentes SET usado = 1
                                WHERE usuario_id = ? AND tipo IN ('email_atual', 'email_novo') AND usado = 0")
                    ->execute([$usuarioId]);

                $token  = bin2hex(random_bytes(32));
                $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $pdo->prepare("INSERT INTO confirmacoes_pendentes
                                (usuario_id, tipo, valor_novo, token, expira_em)
                                VALUES (?, 'email_atual', ?, ?, ?)")
                    ->execute([$usuarioId, $novoEmail, $token, $expira]);

                $link    = SITE_URL . '/confirmar_alteracao.php?token=' . $token;
                $enviado = enviarEmailConfirmacao($usuario['email'], $usuario['nome'], $link, 'email_atual');

                if ($enviado) {
                    $sucesso = "Enviamos um link de confirmação para o seu e-mail atual ({$usuario['email']}). "
                        . "Confirme por lá para prosseguirmos com a troca.";
                } else {
                    $erro = 'Não foi possível enviar o e-mail de confirmação. Tente novamente mais tarde.';
                }
            }
        }
    }

    // ───────────────────────────── SENHA ─────────────────────────────
    elseif ($acao === 'senha') {
        $senhaAtual = $_POST['senha_atual_senha'] ?? '';
        $novaSenha  = $_POST['nova_senha'] ?? '';
        $confSenha  = $_POST['conf_senha'] ?? '';

        if (!password_verify($senhaAtual, $usuario['senha'])) {
            $erro = 'Senha atual incorreta.';
        } elseif (strlen($novaSenha) < 8) {
            $erro = 'A nova senha deve ter pelo menos 8 caracteres.';
        } elseif (!preg_match('/[A-Za-z]/', $novaSenha) || !preg_match('/[0-9]/', $novaSenha)) {
            $erro = 'Use ao menos uma letra e um número na nova senha.';
        } elseif ($novaSenha !== $confSenha) {
            $erro = 'As senhas não coincidem.';
        } elseif (password_verify($novaSenha, $usuario['senha'])) {
            $erro = 'A nova senha deve ser diferente da atual.';
        } else {
            $pdo->prepare("UPDATE confirmacoes_pendentes SET usado = 1
                            WHERE usuario_id = ? AND tipo = 'senha' AND usado = 0")
                ->execute([$usuarioId]);

            $token         = bin2hex(random_bytes(32));
            $expira        = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $novaSenhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

            $pdo->prepare("INSERT INTO confirmacoes_pendentes
                            (usuario_id, tipo, valor_novo, token, expira_em)
                            VALUES (?, 'senha', ?, ?, ?)")
                ->execute([$usuarioId, $novaSenhaHash, $token, $expira]);

            $link    = SITE_URL . '/confirmar_alteracao.php?token=' . $token;
            $enviado = enviarEmailConfirmacao($usuario['email'], $usuario['nome'], $link, 'senha');

            if ($enviado) {
                $sucesso = 'Enviamos um link de confirmação para o seu e-mail. '
                    . 'Clique nele para concluir a troca de senha.';
            } else {
                $erro = 'Não foi possível enviar o e-mail de confirmação. Tente novamente mais tarde.';
            }
        }
    }
}

$avatarUrl = !empty($usuario['foto_perfil'])
    ? 'uploads/perfil/' . rawurlencode($usuario['foto_perfil'])
    : null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tela de Perfil - MIND LINKS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="auth.css">
</head>
<body>
<div class="container-auth">
    <div class="form-area">
        <h1>Seu Perfil</h1>

        <?php if ($erro): ?>
            <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>


        <?php if ($avatarUrl): ?>
            <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Foto de perfil">
        <?php else: ?>
            <i class="fa-solid fa-circle-user"></i>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="formFoto">
            <input type="hidden" name="acao" value="foto">
            <input type="file" id="foto" name="foto" accept="image/png, image/jpeg, image/webp" hidden>
            <label for="foto" style="color:#c699ff; font-size:0.85rem; cursor:pointer;">Alterar foto</label>
        </form>

        <p>
            <?= htmlspecialchars($usuario['email']) ?>
        </p>


        <p>Nome</p>
        <form method="POST">
            <input type="hidden" name="acao" value="nome">
            <div class="input-group">
                <i class="fa-solid fa-user icon"></i>
                <input type="text" name="nome" class="input" required
                    value="<?= htmlspecialchars($usuario['nome']) ?>">
            </div>
            <button type="submit" class="button-login">Salvar nome</button>
        </form>

        <p>E-mail</p>
        <p>
            Primeiro confirmamos no seu e-mail atual e depois no novo.
        </p>
        <form method="POST">
            <input type="hidden" name="acao" value="email">
            <div class="input-group">
                <i class="fa-solid fa-envelope icon"></i>
                <input type="email" name="novo_email" class="input" placeholder="Novo e-mail" required>
            </div>
            <div class="input-group">
                <i class="fa-solid fa-lock icon"></i>
                <input type="password" name="senha_atual_email" class="input" placeholder="Senha atual" required>
            </div>
            <button type="submit" class="button-login">Alterar e-mail</button>
        </form>

        <!-- ─────────── Senha ─────────── -->
        <p>Senha</p>
        <p>
            Você receberá um link de confirmação no seu e-mail atual.
        </p>
        <form method="POST">
            <input type="hidden" name="acao" value="senha">
            <div class="input-group">
                <i class="fa-solid fa-lock icon"></i>
                <input type="password" name="senha_atual_senha" class="input" placeholder="Senha atual" required>
            </div>
            <div class="input-group">
                <i class="fa-solid fa-lock icon"></i>
                <input type="password" id="nova_senha" name="nova_senha" class="input" placeholder="Nova senha (mín. 8 caracteres)" required>
                <i class="fa-solid fa-eye eye-icon" id="toggleNova"></i>
            </div>
            <div class="input-group">
                <i class="fa-solid fa-lock icon"></i>
                <input type="password" id="conf_senha" name="conf_senha" class="input"
                    placeholder="Confirmar nova senha" required>
                <i class="fa-solid fa-eye eye-icon" id="toggleConf"></i>
            </div>
            <button type="submit" class="button-login">Alterar senha</button>
        </form>

        <div class="links">
            <p><a href="principal.php">← Voltar para a tela inicial</a></p>
        </div>
    </div>
</div>

<script>
document.getElementById('foto').addEventListener('change', function () {
    if (this.files && this.files.length > 0) {
        document.getElementById('formFoto').submit();
    }
});

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
document.getElementById('toggleConf')?.addEventListener('click', () => toggle('conf_senha', 'toggleConf'));
</script>
</body>
</html>