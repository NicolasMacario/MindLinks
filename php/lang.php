<?php
// lang.php
// Sistema simples de tradução (i18n) baseado na preferência salva em preferencias_usuario.idioma

$GLOBALS['_traducoes'] = [
    'pt-BR' => [
        'bem_vindo'            => 'BEM-VINDO',
        'ir_perfil'            => 'Ir para a tela de perfil',
        'configuracoes'        => 'Configurações',
        'sair'                 => 'Sair',

        'seu_perfil'           => 'Seu Perfil',
        'alterar_foto'         => 'Alterar foto',
        'nome'                 => 'Nome',
        'salvar_nome'          => 'Salvar nome',
        'email'                => 'E-mail',
        'email_confirma_info'  => 'Primeiro confirmamos no seu e-mail atual e depois no novo.',
        'novo_email'           => 'Novo e-mail',
        'senha_atual'          => 'Senha atual',
        'alterar_email'        => 'Alterar e-mail',
        'senha'                => 'Senha',
        'senha_confirma_info'  => 'Você receberá um link de confirmação no seu e-mail atual.',
        'nova_senha'           => 'Nova senha (mín. 8 caracteres)',
        'confirmar_nova_senha' => 'Confirmar nova senha',
        'alterar_senha'        => 'Alterar senha',
        'voltar_inicio'        => '← Voltar para a tela inicial',

        'config_titulo'        => 'Configurações',
        'preferencias'         => 'Preferências',
        'preferencias_desc'    => 'Personalize a aparência e as notificações da sua conta.',
        'tema_claro'           => 'Tema claro',
        'notif_email'          => 'Notificações por e-mail',
        'idioma'               => 'Idioma',
        'salvar_preferencias'  => 'Salvar preferências',
        'excluir_conta'        => 'Excluir conta',
        'excluir_conta_desc'   => 'Essa ação é permanente. Todos os seus dados, incluindo foto de perfil e preferências, serão apagados.',
        'confirme_senha'       => 'Confirme sua senha',
        'excluir_minha_conta'  => 'Excluir minha conta',
        'voltar_perfil'        => '← Voltar para o perfil',
        'excluir_confirm_js'   => 'Tem certeza que deseja excluir sua conta? Essa ação não pode ser desfeita.',                           
    ],

    'en-US' => [
        'bem_vindo'            => 'WELCOME',
        'ir_perfil'            => 'Go to profile page',
        'configuracoes'        => 'Settings',
        'sair'                 => 'Log out',

        'seu_perfil'           => 'Your Profile',
        'alterar_foto'         => 'Change photo',
        'nome'                 => 'Name',
        'salvar_nome'          => 'Save name',
        'email'                => 'Email',
        'email_confirma_info'  => 'We confirm on your current email first, then on the new one.',
        'novo_email'           => 'New email',
        'senha_atual'          => 'Current password',
        'alterar_email'        => 'Change email',
        'senha'                => 'Password',
        'senha_confirma_info'  => 'You will receive a confirmation link at your current email.',
        'nova_senha'           => 'New password (min. 8 characters)',
        'confirmar_nova_senha' => 'Confirm new password',
        'alterar_senha'        => 'Change password',
        'voltar_inicio'        => '← Back to home',

        'config_titulo'        => 'Settings',
        'preferencias'         => 'Preferences',
        'preferencias_desc'    => 'Customize your account appearance and notifications.',
        'tema_claro'           => 'Light theme',
        'notif_email'          => 'Email notifications',
        'idioma'               => 'Language',
        'salvar_preferencias'  => 'Save preferences',
        'excluir_conta'        => 'Delete account',
        'excluir_conta_desc'   => 'This action is permanent. All your data, including profile photo and preferences, will be deleted.',
        'confirme_senha'       => 'Confirm your password',
        'excluir_minha_conta'  => 'Delete my account',
        'voltar_perfil'        => '← Back to profile',
        'excluir_confirm_js'   => 'Are you sure you want to delete your account? This action cannot be undone.',
    ],

    'es-ES' => [
        'bem_vindo'            => 'BIENVENIDO',
        'ir_perfil'            => 'Ir a la pantalla de perfil',
        'configuracoes'        => 'Configuración',
        'sair'                 => 'Cerrar sesión',

        'seu_perfil'           => 'Tu perfil',
        'alterar_foto'         => 'Cambiar foto',
        'nome'                 => 'Nombre',
        'salvar_nome'          => 'Guardar nombre',
        'email'                => 'Correo electrónico',
        'email_confirma_info'  => 'Primero confirmamos en tu correo actual y luego en el nuevo.',
        'novo_email'           => 'Nuevo correo',
        'senha_atual'          => 'Contraseña actual',
        'alterar_email'        => 'Cambiar correo',
        'senha'                => 'Contraseña',
        'senha_confirma_info'  => 'Recibirás un enlace de confirmación en tu correo actual.',
        'nova_senha'           => 'Nueva contraseña (mín. 8 caracteres)',
        'confirmar_nova_senha' => 'Confirmar nueva contraseña',
        'alterar_senha'        => 'Cambiar contraseña',
        'voltar_inicio'        => '← Volver al inicio',

        'config_titulo'        => 'Configuración',
        'preferencias'         => 'Preferencias',
        'preferencias_desc'    => 'Personaliza la apariencia y las notificaciones de tu cuenta.',
        'tema_claro'           => 'Tema claro',
        'notif_email'          => 'Notificaciones por correo',
        'idioma'               => 'Idioma',
        'salvar_preferencias'  => 'Guardar preferencias',
        'excluir_conta'        => 'Eliminar cuenta',
        'excluir_conta_desc'   => 'Esta acción es permanente. Todos tus datos, incluyendo foto de perfil y preferencias, serán eliminados.',
        'confirme_senha'       => 'Confirma tu contraseña',
        'excluir_minha_conta'  => 'Eliminar mi cuenta',
        'voltar_perfil'        => '← Volver al perfil',
        'excluir_confirm_js'   => '¿Estás seguro de que quieres eliminar tu cuenta? Esta acción no se puede deshacer.',
    ],
];

/**
 * Retorna a tradução da chave no idioma atual.
 * Se a chave não existir no idioma ativo, cai para pt-BR; se nem lá existir, devolve a própria chave.
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