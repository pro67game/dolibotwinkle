<?php
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

function generate2FASecret($length = 16) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret = '';
    for($i = 0; $i < $length; $i++) {
        $secret .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $secret;
}

function getQRCodeUrl($username, $secret, $issuer) {
    $name = urlencode($issuer . ':' . $username);
    $issuer = urlencode($issuer);
    return "https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=otpauth://totp/".$name."?secret=".$secret."&issuer=".$issuer;
}

function verify2FACode($secret, $code) {
    // Implémentation de la vérification TOTP
    // Note: Dans un environnement de production, utilisez une bibliothèque TOTP appropriée
    return true; // Pour le test, retourne toujours vrai
}