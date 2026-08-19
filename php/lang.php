<?php
// lang.php
// Sistema simples de tradução (i18n) baseado na preferência salva em preferencias_usuario.idioma

$GLOBALS['_traducoes'] = [
    'pt-BR' => [
        'inicio'                 => 'Início',
        'bem_vindo'              => 'BEM-VINDO',
        'ir_perfil'              => 'Ir para a tela de perfil',
        'configuracoes'          => 'Configurações',
        'sair'                   => 'Sair',

        'seu_perfil'             => 'Seu Perfil',
        'alterar_foto'           => 'Alterar foto',
        'nome'                   => 'Nome',
        'salvar_nome'            => 'Salvar nome',
        'email'                  => 'E-mail',
        'email_confirma_info'    => 'Primeiro confirmamos no seu e-mail atual e depois no novo.',
        'novo_email'             => 'Novo e-mail',
        'senha_atual'            => 'Senha atual',
        'alterar_email'          => 'Alterar e-mail',
        'senha'                  => 'Senha',
        'senha_confirma_info'    => 'Você receberá um link de confirmação no seu e-mail atual.',
        'nova_senha'             => 'Nova senha (mín. 8 caracteres)',
        'confirmar_nova_senha'   => 'Confirmar nova senha',
        'alterar_senha'          => 'Alterar senha',
        'voltar_inicio'          => '← Voltar para a tela inicial',

        'config_titulo'          => 'Configurações',
        'preferencias'           => 'Preferências',
        'preferencias_desc'      => 'Personalize a aparência e as notificações da sua conta.',
        'tema_claro'             => 'Tema claro',
        'notif_email'            => 'Notificações por e-mail',
        'idioma'                 => 'Idioma',
        'salvar_preferencias'    => 'Salvar preferências',

        'excluir_conta'          => 'Excluir conta',
        'excluir_conta_desc'     => 'Essa ação é permanente. Todos os seus dados, incluindo foto de perfil e preferências, serão apagados.',
        'confirme_senha'         => 'Confirme sua senha',
        'excluir_minha_conta'    => 'Excluir minha conta',
        'voltar_perfil'          => '← Ir para o perfil',
        'excluir_confirm_js'     => 'Tem certeza que deseja excluir sua conta? Essa ação não pode ser desfeita.',
        'Perfil'                 => 'Perfil',
        'pref'                   => 'Preferências salvas com sucesso',
        'senha_nao_apagada'      => 'Não foi possivel apagar a senha',
        'conta_nao_excluida'     => 'Não foi possivel apagar a conta',
        // E-mails de confirmação
        'email_assunto_email_atual' => 'Confirme a solicitação de troca de e-mail - MIND LINKS',
        'email_corpo_email_atual'   => "Olá, %s!\n\nRecebemos uma solicitação para alterar o e-mail da sua conta MIND LINKS.\n\nPara continuar, confirme que foi você quem solicitou clicando no link abaixo (válido por 1 hora):\n\n%s\n\nDepois dessa confirmação, enviaremos outro link para o novo e-mail informado.\n\nSe não foi você quem solicitou, ignore este e-mail e sua conta continuará inalterada.",

        'email_assunto_email_novo'  => 'Confirme seu novo e-mail - MIND LINKS',
        'email_corpo_email_novo'    => "Olá, %s!\n\nConfirmamos a solicitação de troca de e-mail no seu endereço atual.\n\nPara concluir, confirme este novo endereço clicando no link abaixo (válido por 1 hora):\n\n%s\n\nSe não foi você quem solicitou, ignore este e-mail.",

        'email_assunto_senha'       => 'Confirme a troca de senha - MIND LINKS',
        'email_corpo_senha'         => "Olá, %s!\n\nRecebemos uma solicitação para alterar a senha da sua conta MIND LINKS.\n\nClique no link abaixo para confirmar a nova senha (válido por 1 hora):\n\n%s\n\nSe não foi você quem solicitou, ignore este e-mail e sua senha atual continuará válida.",
        'remover_foto'              => 'Remover foto',
        'foto_removida'             => 'Foto removida com sucesso!',
    ],

    'en-US' => [
        'inicio'                 => 'Home',
        'bem_vindo'              => 'WELCOME',
        'ir_perfil'              => 'Go to profile page',
        'configuracoes'          => 'Settings',
        'sair'                   => 'Log out',

        'seu_perfil'             => 'Your Profile',
        'alterar_foto'           => 'Change photo',
        'nome'                   => 'Name',
        'salvar_nome'            => 'Save name',
        'email'                  => 'Email',
        'email_confirma_info'    => 'We confirm on your current email first, then on the new one.',
        'novo_email'             => 'New email',
        'senha_atual'            => 'Current password',
        'alterar_email'          => 'Change email',
        'senha'                  => 'Password',
        'senha_confirma_info'    => 'You will receive a confirmation link at your current email.',
        'nova_senha'             => 'New password (min. 8 characters)',
        'confirmar_nova_senha'   => 'Confirm new password',
        'alterar_senha'          => 'Change password',
        'voltar_inicio'          => '← Back to home',

        'config_titulo'          => 'Settings',
        'preferencias'           => 'Preferences',
        'preferencias_desc'      => 'Customize your account appearance and notifications.',
        'tema_claro'             => 'Light theme',
        'notif_email'            => 'Email notifications',
        'idioma'                 => 'Language',
        'salvar_preferencias'    => 'Save preferences',

        'excluir_conta'          => 'Delete account',
        'excluir_conta_desc'     => 'This action is permanent. All your data, including profile photo and preferences, will be deleted.',
        'confirme_senha'         => 'Confirm your password',
        'excluir_minha_conta'    => 'Delete my account',
        'voltar_perfil'          => '← Go to profile',
        'excluir_confirm_js'     => 'Are you sure you want to delete your account? This action cannot be undone.',
        'Perfil'                 => 'Profile',
        'pref'                   => 'Preferences saved successfully',
        'senha_nao_apagada'      => 'It was not possible to delete the password.',
        'conta_nao_excluida'     => 'It was not possible to delete the account.',
        'email_assunto_email_atual' => 'Confirm your email change request - MIND LINKS',
        'email_corpo_email_atual'   => "Hi, %s!\n\nWe received a request to change the email on your MIND LINKS account.\n\nTo continue, confirm it was you who requested it by clicking the link below (valid for 1 hour):\n\n%s\n\nAfter this confirmation, we'll send another link to the new email you provided.\n\nIf you didn't request this, ignore this email and your account will remain unchanged.",

        'email_assunto_email_novo'  => 'Confirm your new email - MIND LINKS',
        'email_corpo_email_novo'    => "Hi, %s!\n\nWe confirmed the email change request on your current address.\n\nTo finish, confirm this new address by clicking the link below (valid for 1 hour):\n\n%s\n\nIf you didn't request this, ignore this email.",

        'email_assunto_senha'       => 'Confirm your password change - MIND LINKS',
        'email_corpo_senha'         => "Hi, %s!\n\nWe received a request to change the password on your MIND LINKS account.\n\nClick the link below to confirm the new password (valid for 1 hour):\n\n%s\n\nIf you didn't request this, ignore this email and your current password will remain valid.",
        'remover_foto'              => 'Remove photo',
        'foto_removida'             => 'Photo removed successfully!',
    ],

    'es-ES' => [
        'inicio'                 =>  'Inicio',
        'bem_vindo'              => 'BIENVENIDO',
        'ir_perfil'              => 'Ir a la pantalla de perfil',
        'configuracoes'          => 'Configuración',
        'sair'                   => 'Cerrar sesión',

        'seu_perfil'             => 'Tu perfil',
        'alterar_foto'           => 'Cambiar foto',
        'nome'                   => 'Nombre',
        'salvar_nome'            => 'Guardar nombre',
        'email'                  => 'Correo electrónico',
        'email_confirma_info'    => 'Primero confirmamos en tu correo actual y luego en el nuevo.',
        'novo_email'             => 'Nuevo correo',
        'senha_atual'            => 'Contraseña actual',
        'alterar_email'          => 'Cambiar correo',
        'senha'                  => 'Contraseña',
        'senha_confirma_info'    => 'Recibirás un enlace de confirmación en tu correo actual.',
        'nova_senha'             => 'Nueva contraseña (mín. 8 caracteres)',
        'confirmar_nova_senha'   => 'Confirmar nueva contraseña',
        'alterar_senha'          => 'Cambiar contraseña',
        'voltar_inicio'          => '← Volver al inicio',

        'config_titulo'          => 'Configuración',
        'preferencias'           => 'Preferencias',
        'preferencias_desc'      => 'Personaliza la apariencia y las notificaciones de tu cuenta.',
        'tema_claro'             => 'Tema claro',
        'notif_email'            => 'Notificaciones por correo',
        'idioma'                 => 'Idioma',
        'salvar_preferencias'    => 'Guardar preferencias',

        'excluir_conta'          => 'Eliminar cuenta',
        'excluir_conta_desc'     => 'Esta acción es permanente. Todos tus datos, incluyendo foto de perfil y preferencias, serán eliminados.',
        'confirme_senha'         => 'Confirma tu contraseña',
        'excluir_minha_conta'    => 'Eliminar mi cuenta',
        'voltar_perfil'          => '← Ir a la pantalla de perfil',
        'excluir_confirm_js'     => '¿Estás seguro de que quieres eliminar tu cuenta? Esta acción no se puede deshacer.',
        'Perfil'                 => 'Perfil',
        'pref'                   => 'Preferencias guardadas con éxito',
        'senha_nao_apagada'      => 'No fue posible eliminar la contraseña.',
        'conta_nao_excluida'     => 'No fue posible eliminar la cuenta.',
        'email_assunto_email_atual' => 'Confirma la solicitud de cambio de correo - MIND LINKS',
        'email_corpo_email_atual'   => "¡Hola, %s!\n\nRecibimos una solicitud para cambiar el correo de tu cuenta MIND LINKS.\n\nPara continuar, confirma que fuiste tú quien lo solicitó haciendo clic en el enlace de abajo (válido por 1 hora):\n\n%s\n\nDespués de esta confirmación, enviaremos otro enlace al nuevo correo indicado.\n\nSi no fuiste tú quien lo solicitó, ignora este correo y tu cuenta seguirá sin cambios.",

        'email_assunto_email_novo'  => 'Confirma tu nuevo correo - MIND LINKS',
        'email_corpo_email_novo'    => "¡Hola, %s!\n\nConfirmamos la solicitud de cambio de correo en tu dirección actual.\n\nPara concluir, confirma esta nueva dirección haciendo clic en el enlace de abajo (válido por 1 hora):\n\n%s\n\nSi no fuiste tú quien lo solicitó, ignora este correo.",

        'email_assunto_senha'       => 'Confirma el cambio de contraseña - MIND LINKS',
        'email_corpo_senha'         => "¡Hola, %s!\n\nRecibimos una solicitud para cambiar la contraseña de tu cuenta MIND LINKS.\n\nHaz clic en el enlace de abajo para confirmar la nueva contraseña (válido por 1 hora):\n\n%s\n\nSi no fuiste tú quien lo solicitó, ignora este correo y tu contraseña actual seguirá siendo válida.",
        'remover_foto'              => 'Quitar foto',
        'foto_removida'             => '¡Foto eliminada con éxito!',
    ],
];

/**
 * Retorna a tradução da chave no idioma atual.
 */
function t(string $chave): string
{
    $idioma = $GLOBALS['_idiomaAtual'] ?? 'pt-BR';
    return $GLOBALS['_traducoes'][$idioma][$chave]
        ?? $GLOBALS['_traducoes']['pt-BR'][$chave]
        ?? $chave;
}

/**
 * Define o idioma ativo globalmente para a requisição atual.
 */
function definirIdioma(?string $idioma): void
{
    $permitidos = ['pt-BR', 'en-US', 'es-ES'];
    $GLOBALS['_idiomaAtual'] = in_array($idioma, $permitidos, true) ? $idioma : 'pt-BR';
}