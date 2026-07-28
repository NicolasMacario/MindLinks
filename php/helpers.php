<?php
// helpers.php
// Funções auxiliares usadas em várias páginas (login, configurações...).
 
/**
 * Extrai um nome amigável de navegador + sistema operacional a partir do User-Agent.
 */
function detectarDispositivo(string $userAgent): string
{
    $navegador = 'Navegador desconhecido';
    if (stripos($userAgent, 'Edg/') !== false) {
        $navegador = 'Edge';
    } elseif (stripos($userAgent, 'Chrome') !== false && stripos($userAgent, 'Chromium') === false) {
        $navegador = 'Chrome';
    } elseif (stripos($userAgent, 'Firefox') !== false) {
        $navegador = 'Firefox';
    } elseif (stripos($userAgent, 'Safari') !== false && stripos($userAgent, 'Chrome') === false) {
        $navegador = 'Safari';
    } elseif (stripos($userAgent, 'OPR') !== false || stripos($userAgent, 'Opera') !== false) {
        $navegador = 'Opera';
    }
 
    $sistema = 'dispositivo desconhecido';
    if (stripos($userAgent, 'Windows') !== false) {
        $sistema = 'Windows';
    } elseif (stripos($userAgent, 'Android') !== false) {
        $sistema = 'Android';
    } elseif (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) {
        $sistema = 'iOS';
    } elseif (stripos($userAgent, 'Mac OS') !== false) {
        $sistema = 'macOS';
    } elseif (stripos($userAgent, 'Linux') !== false) {
        $sistema = 'Linux';
    }
 
    return "{$navegador} · {$sistema}";
}
 
/**
 * Converte uma data/hora do banco em um texto relativo simples ("há 5 min", etc).
 */
function tempoRelativo(string $dataHora): string
{
    $diff = time() - strtotime($dataHora);
 
    if ($diff < 60) {
        return 'agora mesmo';
    }
    if ($diff < 3600) {
        $min = (int) floor($diff / 60);
        return "há {$min} min";
    }
    if ($diff < 86400) {
        $h = (int) floor($diff / 3600);
        return "há {$h} h";
    }
    $d = (int) floor($diff / 86400);
    return "há {$d} dia" . ($d > 1 ? 's' : '');
}