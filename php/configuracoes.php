<?php
// configuracoes.php
require_once 'auth_check.php';
$pdo = conectar();
require_once 'helpers.php';
 
$usuarioId = (int) $_SESSION['usuario']['id'];
 
$erro    = '';
$sucesso = '';
 
// ─────────────────────── Preferências (cria linha padrão se não existir) ───────────────────────
$stmt = $pdo->prepare("SELECT * FROM preferencias_usuario WHERE usuario_id = ?");
$stmt->execute([$usuarioId]);
$prefs = $stmt->fetch();
 
if (!$prefs) {
    $prefs = ['tema' => 'escuro', 'notificacoes_email' => 1, 'idioma' => 'pt-BR'];
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
 
    // ───────────────────────────── PREFERÊNCIAS ─────────────────────────────
    if ($acao === 'preferencias') {
        $tema               = ($_POST['tema'] ?? 'escuro') === 'claro' ? 'claro' : 'escuro';
        $notificacoesEmail  = isset($_POST['notificacoes_email']) ? 1 : 0;
        $idiomasPermitidos  = ['pt-BR', 'en-US', 'es-ES'];
        $idioma             = in_array($_POST['idioma'] ?? '', $idiomasPermitidos, true) ? $_POST['idioma'] : 'pt-BR';
 
        $pdo->prepare("INSERT INTO preferencias_usuario (usuario_id, tema, notificacoes_email, idioma)
                        VALUES (?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            tema = VALUES(tema),
                            notificacoes_email = VALUES(notificacoes_email),
                            idioma = VALUES(idioma)")
            ->execute([$usuarioId, $tema, $notificacoesEmail, $idioma]);
 
        $prefs   = ['tema' => $tema, 'notificacoes_email' => $notificacoesEmail, 'idioma' => $idioma];
        $sucesso = 'Preferências salvas com sucesso!';
    }
 
    // ───────────────────────────── EXCLUIR CONTA ─────────────────────────────
    elseif ($acao === 'excluir_conta') {
        $senhaConfirma = $_POST['senha_confirma'] ?? '';
 
        $stmt = $pdo->prepare("SELECT senha, foto_perfil FROM usuarios WHERE id = ?");
        $stmt->execute([$usuarioId]);
        $dadosUsuario = $stmt->fetch();
 
        if (!$dadosUsuario || !password_verify($senhaConfirma, $dadosUsuario['senha'])) {
            $erro = 'Senha incorreta. A conta não foi excluída.';
        } else {
            try {
                $pdo->beginTransaction();
 
                $pdo->prepare("DELETE FROM confirmacoes_pendentes WHERE usuario_id = ?")->execute([$usuarioId]);
                $pdo->prepare("DELETE FROM recuperacao_senha WHERE usuario_id = ?")->execute([$usuarioId]);
                $pdo->prepare("DELETE FROM preferencias_usuario WHERE usuario_id = ?")->execute([$usuarioId]);
                $pdo->prepare("DELETE FROM sessoes_ativas WHERE usuario_id = ?")->execute([$usuarioId]);
                $pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$usuarioId]);
 
                $pdo->commit();
 
                if (!empty($dadosUsuario['foto_perfil'])) {
                    $caminhoFoto = __DIR__ . '/uploads/perfil/' . $dadosUsuario['foto_perfil'];
                    if (is_file($caminhoFoto)) {
                        unlink($caminhoFoto);
                    }
                }
 
                $_SESSION = [];
                session_destroy();
                header('Location: login.php?conta_excluida=1');
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log('Erro ao excluir conta: ' . $e->getMessage());
                $erro = 'Não foi possível excluir a conta agora. Tente novamente mais tarde.';
            }
        }
    }
}
 
?>
<!DOCTYPE html>
<html lang="pt-BR" data-tema="<?= htmlspecialchars($prefs['tema']) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - MIND LINKS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="auth.css">

</head>
<body>
<div class="container-auth">
    <div class="form-area config-page">
        <h1>Configurações</h1>
 
        <?php if ($erro): ?>
            <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
 
        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>
 
        <!-- ─────────────────────── Preferências ─────────────────────── -->
        <div class="config-section">
            <h2>Preferências</h2>
            <p class="descricao">Personalize a aparência e as notificações da sua conta.</p>
 
            <form method="POST">
                <input type="hidden" name="acao" value="preferencias">
 
                <div class="campo-linha">
                    <label for="tema">Tema claro</label>
                    <label class="switch">
                        <input type="checkbox" id="tema" name="tema" value="claro"
                            <?= $prefs['tema'] === 'claro' ? 'checked' : '' ?>>
                        <span class="switch-track"></span>
                    </label>
                </div>
 
                <div class="campo-linha">
                    <label for="notificacoes_email">Notificações por e-mail</label>
                    <label class="switch">
                        <input type="checkbox" id="notificacoes_email" name="notificacoes_email"
                            <?= $prefs['notificacoes_email'] ? 'checked' : '' ?>>
                        <span class="switch-track"></span>
                    </label>
                </div>
 
                <div style="margin-bottom:16px;">
                    <label for="idioma" style="display:block; margin-bottom:6px; font-size:0.92rem;">Idioma</label>
                    <select name="idioma" id="idioma" class="input">
                        <option value="pt-BR" <?= $prefs['idioma'] === 'pt-BR' ? 'selected' : '' ?>>Português (Brasil)</option>
                        <option value="en-US" <?= $prefs['idioma'] === 'en-US' ? 'selected' : '' ?>>English (US)</option>
                        <option value="es-ES" <?= $prefs['idioma'] === 'es-ES' ? 'selected' : '' ?>>Español</option>
                    </select>
                </div>
 
                <button type="submit" class="button-login">Salvar preferências</button>
            </form>
        </div>
 
        <!-- ─────────────────────── Excluir conta ─────────────────────── -->
        <div class="config-section" style="border-color: rgba(255, 107, 107, 0.35);">
            <h2 style="color:#ff6b6b;">Excluir conta</h2>
            <p class="descricao">
                Essa ação é permanente. Todos os seus dados, incluindo foto de perfil e preferências, serão apagados.
            </p>
 
            <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir sua conta? Essa ação não pode ser desfeita.');">
                <input type="hidden" name="acao" value="excluir_conta">
                <div class="input-group">
                    <i class="fa-solid fa-lock icon"></i>
                    <input type="password" name="senha_confirma" class="input" placeholder="Confirme sua senha" required>
                </div>
                <button type="submit" class="btn-perigo">Excluir minha conta</button>
            </form>
        </div>
 
        <div class="links">
            <p><a href="perfil.php">← Voltar para o perfil</a></p>
            <p><a href="logout.php">Sair</a></p>
        </div>
    </div>
</div>
 
<script>
// Alterna o atributo data-tema no <html> instantaneamente ao mexer no switch,
// antes mesmo de salvar (feedback visual imediato).
document.getElementById('tema').addEventListener('change', function () {
    document.documentElement.setAttribute('data-tema', this.checked ? 'claro' : 'escuro');
});
</script>
</body>
</html>