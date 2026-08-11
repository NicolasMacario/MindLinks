<?php
require_once 'auth_check.php';
/** @var PDO $pdo */
/** @var string $tema */
/** @var string $idioma */
require_once 'src/PHPMailer.php';
require_once 'src/SMTP.php';
require_once 'src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
            $mail->Subject = t('email_assunto_email_atual');
            $mail->Body    = sprintf(t('email_corpo_email_atual'), $nome, $link);
        } elseif ($tipo === 'email_novo') {
            $mail->Subject = t('email_assunto_email_novo');
            $mail->Body    = sprintf(t('email_corpo_email_novo'), $nome, $link);
        } else {
            $mail->Subject = t('email_assunto_senha');
            $mail->Body    = sprintf(t('email_corpo_senha'), $nome, $link);
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
<html lang="<?= htmlspecialchars($idioma) ?>" data-tema="<?= htmlspecialchars($tema) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('seu_perfil') ?> - MIND LINKS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="auth.css">
</head>
<body>

<nav class="navbar">
    <div class="logo">Mind Links</div>
    <ul class="nav-links">
        <li><a href="principal.php"><?= t('inicio') ?></a></li>
        <li><a href="perfil.php" class="ativo"><?= t('seu_perfil') ?></a></li>
        <li><a href="configuracoes.php"><?= t('configuracoes') ?></a></li>
    </ul>
</nav>

<div class="container">
    <div class="box alinhado-esq">
        <h2 style="align-self:center;"><?= t('seu_perfil') ?></h2>

        <?php if ($erro): ?>
            <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>

        <div style="align-self:center; text-align:center; margin-bottom:10px;">
            <?php if ($avatarUrl): ?>
                <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Foto de perfil" class="class_img">
            <?php else: ?>
                <i class="fa-solid fa-circle-user" style="font-size:6rem; color:#8151c1;"></i>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" id="formFoto">
                <input type="hidden" name="acao" value="foto">
                <input type="file" id="foto" name="foto" accept="image/png, image/jpeg, image/webp" hidden>
                <label for="foto" style="display:block; margin-top:8px; color:#c699ff; font-size:.85rem; cursor:pointer;">
                    <?= t('alterar_foto') ?>
                </label>
            </form>

            <p style="margin:6px 0 0;"><?= htmlspecialchars($usuario['email']) ?></p>
        </div>

        <p style="margin-bottom:6px; text-align:left; color:var(--texto-principal);"><?= t('nome') ?></p>
        <form method="POST" style="width:100%; margin-bottom:20px;">
            <input type="hidden" name="acao" value="nome">
            <div class="input-group">
                <i class="fa-solid fa-user icon"></i>
                <input type="text" name="nome" class="input" required
                    value="<?= htmlspecialchars($usuario['nome']) ?>">
            </div>
            <button type="submit" class="button-login" style="width:100%;"><?= t('salvar_nome') ?></button>
        </form>

        <p style="margin-bottom:2px; text-align:left; color:var(--texto-principal);"><?= t('email') ?></p>
        <p style="text-align:left; font-size:.85rem; margin-bottom:10px;"><?= t('email_confirma_info') ?></p>
        <form method="POST" style="width:100%; margin-bottom:20px;">
            <input type="hidden" name="acao" value="email">
            <div class="input-group">
                <i class="fa-solid fa-envelope icon"></i>
                <input type="email" name="novo_email" class="input" placeholder="<?= t('novo_email') ?>" required>
            </div>
            <div class="input-group">
                <i class="fa-solid fa-lock icon"></i>
                <input type="password" name="senha_atual_email" class="input" placeholder="<?= t('senha_atual') ?>" required>
            </div>
            <button type="submit" class="button-login" style="width:100%;"><?= t('alterar_email') ?></button>
        </form>

        <p style="margin-bottom:2px; text-align:left; color:var(--texto-principal);"><?= t('senha') ?></p>
        <p style="text-align:left; font-size:.85rem; margin-bottom:10px;"><?= t('senha_confirma_info') ?></p>
        <form method="POST" style="width:100%;">
            <input type="hidden" name="acao" value="senha">
            <div class="input-group">
                <i class="fa-solid fa-lock icon"></i>
                <input type="password" name="senha_atual_senha" class="input" placeholder="<?= t('senha_atual') ?>" required>
            </div>
            <div class="input-group">
                <i class="fa-solid fa-lock icon"></i>
                <input type="password" id="nova_senha" name="nova_senha" class="input" placeholder="<?= t('nova_senha') ?>" required>
                <i class="fa-solid fa-eye eye-icon" id="toggleNova"></i>
            </div>
            <div class="input-group">
                <i class="fa-solid fa-lock icon"></i>
                <input type="password" id="conf_senha" name="conf_senha" class="input"
                    placeholder="<?= t('confirmar_nova_senha') ?>" required>
                <i class="fa-solid fa-eye eye-icon" id="toggleConf"></i>
            </div>
            <button type="submit" class="button-login" style="width:100%;"><?= t('alterar_senha') ?></button>
        </form>

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