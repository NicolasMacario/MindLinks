<?php
// esqueci-senha.php
require_once 'conexao.php';
require_once('src/PHPMailer.php');
require_once('src/SMTP.php');
require_once('src/Exception.php');
 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
 
$mensagem = '';
$tipo     = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
 
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = 'Digite um e-mail válido.';
        $tipo     = 'erro';
    } else {
        $pdo  = conectar();
        $stmt = $pdo->prepare("SELECT id, nome FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
 
        // Sempre exibe a mesma mensagem (evita enumeração de e-mails)
        $mensagem = 'Se este e-mail estiver cadastrado, você receberá as instruções em instantes.';
        $tipo     = 'sucesso';
 
        if ($usuario) {
            // Invalida tokens anteriores não usados
            $pdo->prepare("UPDATE recuperacao_senha SET usado = 1 WHERE usuario_id = ? AND usado = 0")
                ->execute([$usuario['id']]);
 
            // Gera token seguro e define expiração (1 hora)
            $token  = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
 
            $pdo->prepare("INSERT INTO recuperacao_senha (usuario_id, token, expira_em) VALUES (?, ?, ?)")
                ->execute([$usuario['id'], $token, $expira]);
 
            $link = SITE_URL . "/reset_senha.php?token=" . $token;
 
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
                $mail->addAddress($email, $usuario['nome']);
 
                $mail->isHTML(false);
                $mail->Subject = 'Redefinição de senha';
                $mail->Body    = "Olá, {$usuario['nome']}!\n\nClique no link abaixo para redefinir sua senha (válido por 1 hora):\n\n$link\n\nSe não foi você, ignore este e-mail.";
 
                $mail->send();
            } catch (Exception $e) {
                error_log("Erro ao enviar e-mail: " . $mail->ErrorInfo);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci minha senha - MIND LINKS</title>
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
        <h1>ESQUECI SENHA</h1>
 
        <?php if ($mensagem): ?>
            <div class="alert alert-<?= $tipo === 'sucesso' ? 'success' : 'error' ?>">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>
 
        <?php if ($tipo !== 'sucesso'): ?>
        <form method="POST">
            <div class="input-group">
                <i class="fa-solid fa-envelope icon"></i>
                <input type="email" name="email" class="input" placeholder="Seu e-mail"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
            </div>
            <button type="submit" class="button-login">Enviar link de redefinição</button>
        </form>
        <?php endif; ?>
 
        <div class="links">
            <p><a href="login.php">← Voltar para o login</a></p>
        </div>
    </div>
</div>
</body>
</html>