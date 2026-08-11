<?php

require_once 'auth_check.php';
/** @var PDO $pdo */
/** @var string $tema */
/** @var string $idioma */
require_once 'conexao.php';
require_once 'helpers.php';

$usuarioId = (int) $_SESSION['usuario']['id'];

$erro    = '';
$sucesso = '';

$prefs = ['tema' => $tema, 'notificacoes_email' => 1, 'idioma' => $idioma];
$stmtPrefs = $pdo->prepare("SELECT notificacoes_email FROM preferencias_usuario WHERE usuario_id = ?");
$stmtPrefs->execute([$usuarioId]);
$linha = $stmtPrefs->fetch();
if ($linha) {
    $prefs['notificacoes_email'] = $linha['notificacoes_email'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'preferencias') {
        $temaNovo          = ($_POST['tema'] ?? 'escuro') === 'claro' ? 'claro' : 'escuro';
        $notificacoesEmail = isset($_POST['notificacoes_email']) ? 1 : 0;
        $idiomasPermitidos = ['pt-BR', 'en-US', 'es-ES'];
        $idiomaNovo        = in_array($_POST['idioma'] ?? '', $idiomasPermitidos, true) ? $_POST['idioma'] : 'pt-BR';

        $pdo->prepare("INSERT INTO preferencias_usuario (usuario_id, tema, notificacoes_email, idioma)
                        VALUES (?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            tema = VALUES(tema),
                            notificacoes_email = VALUES(notificacoes_email),
                            idioma = VALUES(idioma)")
            ->execute([$usuarioId, $temaNovo, $notificacoesEmail, $idiomaNovo]);

        $prefs = ['tema' => $temaNovo, 'notificacoes_email' => $notificacoesEmail, 'idioma' => $idiomaNovo];

        definirIdioma($idiomaNovo);
        $tema   = $temaNovo;
        $idioma = $idiomaNovo;

        $sucesso = t('pref');
    }

    elseif ($acao === 'excluir_conta') {
        $senhaConfirma = $_POST['senha_confirma'] ?? '';

        $stmt = $pdo->prepare("SELECT senha, foto_perfil FROM usuarios WHERE id = ?");
        $stmt->execute([$usuarioId]);
        $dadosUsuario = $stmt->fetch();

        if (!$dadosUsuario || !password_verify($senhaConfirma, $dadosUsuario['senha'])) {
            $erro = t('senha_nao_apagada');
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
                $erro = t('conta_nao_excluida');
            }
        }
    }
}

// Rótulos do botão de idioma (bandeira + nome) — usados no dropdown abaixo
$rotulosIdioma = [
    'pt-BR' => '🇧🇷 Português',
    'en-US' => '🇺🇸 English',
    'es-ES' => '🇪🇸 Español',
];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($prefs['idioma']) ?>" data-tema="<?= htmlspecialchars($prefs['tema']) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('config_titulo') ?> - MIND LINKS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="auth.css">
</head>
<body>

<nav class="navbar">
    <div class="logo">Mind Links</div>
    <ul class="nav-links">
        <li><a href="principal.php"><?=  t('inicio') ?></a></li>
        <li><a href="perfil.php"><?=  t('Perfil') ?></a></li>
        <li><a href="configuracoes.php" class="ativo"><?= t('configuracoes') ?></a></li>
    </ul>
</nav>

<div class="container">
    <div class="box alinhado-esq">
        <h2 style="align-self:center;"><?= t('config_titulo') ?></h2>

        <?php if ($erro): ?>
            <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>

        <form method="POST" id="formPreferencias" style="width:100%;">
            <input type="hidden" name="acao" value="preferencias">

            <div class="campo-linha">
                <label for="tema"><?= t('tema_claro') ?></label>
                <label class="switch">
                    <input type="checkbox" id="tema" name="tema" value="claro"
                        <?= $prefs['tema'] === 'claro' ? 'checked' : '' ?>>
                    <span class="switch-track"></span>
                </label>
            </div>

            <div class="campo-linha">
                <label for="notificacoes_email"><?= t('notif_email') ?></label>
                <label class="switch">
                    <input type="checkbox" id="notificacoes_email" name="notificacoes_email"
                        <?= $prefs['notificacoes_email'] ? 'checked' : '' ?>>
                    <span class="switch-track"></span>
                </label>
            </div>

            <label style="display:block; margin-bottom:6px; font-size:.92rem;"><?= t('idioma') ?></label>
            <div class="seletor-idioma">
                <button type="button" class="btn-idioma" id="btnIdioma">
                    <span id="rotuloIdioma"><?= htmlspecialchars($rotulosIdioma[$prefs['idioma']] ?? $rotulosIdioma['pt-BR']) ?></span>
                    <span>▼</span>
                </button>

                <div class="opcoes-idioma" id="opcoesIdioma">
                    <?php foreach ($rotulosIdioma as $codigo => $rotulo): ?>
                        <button type="button" data-idioma="<?= htmlspecialchars($codigo) ?>">
                            <?= htmlspecialchars($rotulo) ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Guarda o idioma escolhido para ser enviado junto com o form -->
                <input type="hidden" name="idioma" id="idiomaSelecionado" value="<?= htmlspecialchars($prefs['idioma']) ?>">
            </div>

            <button type="submit" class="button-login" style="width:100%; margin-top:10px;">
                <?= t('salvar_preferencias') ?>
            </button>
        </form>

        <div class="box" style="border:1px solid var(--borda-caixa-perigo); margin-top:25px; box-shadow:none; min-height:auto; width:100%; box-sizing:border-box;">
            <h2 style="color:#ff6b6b;"><?= t('excluir_conta') ?></h2>
            <p><?= t('excluir_conta_desc') ?></p>

            <form method="POST" onsubmit="return confirm('<?= addslashes(t('excluir_confirm_js')) ?>');" style="width:100%;">
                <input type="hidden" name="acao" value="excluir_conta">
                <div class="input-group">
                    <i class="fa-solid fa-lock icon"></i>
                    <input type="password" name="senha_confirma" class="input" placeholder="<?= t('confirme_senha') ?>" required>
                </div>
                <button type="submit" class="btn-perigo" style="width:100%;"><?= t('excluir_minha_conta') ?></button>
            </form>
        </div>

        <div class="links">
            <p><a href="logout.php"><?= t('sair') ?></a></p>
        </div>
    </div>
</div>

<script>
// Alterna o data-tema no <html> instantaneamente ao mexer no switch
// (feedback visual imediato, antes mesmo de salvar no banco)
document.getElementById('tema').addEventListener('change', function () {
    document.documentElement.setAttribute('data-tema', this.checked ? 'claro' : 'escuro');
});

// Dropdown de idioma (visual do zip) ligado ao input hidden que vai no POST
const btnIdioma      = document.getElementById('btnIdioma');
const opcoesIdioma    = document.getElementById('opcoesIdioma');
const rotuloIdioma    = document.getElementById('rotuloIdioma');
const idiomaSelecionado = document.getElementById('idiomaSelecionado');

btnIdioma.addEventListener('click', () => opcoesIdioma.classList.toggle('aberto'));

document.querySelectorAll('#opcoesIdioma button').forEach(botao => {
    botao.addEventListener('click', () => {
        idiomaSelecionado.value = botao.dataset.idioma;
        rotuloIdioma.textContent = botao.textContent.trim();
        opcoesIdioma.classList.remove('aberto');
    });
});

// Fecha o dropdown se clicar fora dele
document.addEventListener('click', (e) => {
    if (!e.target.closest('.seletor-idioma')) {
        opcoesIdioma.classList.remove('aberto');
    }
});
</script>
</body>
</html>