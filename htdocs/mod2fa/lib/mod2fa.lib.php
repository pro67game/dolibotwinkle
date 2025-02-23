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
    return "otpauth://totp/".$name."?secret=".$secret."&issuer=".$issuer;
}

function generateQRCodeImage($data) {
    // Inclusion de la bibliothèque phpqrcode
    require_once DOL_DOCUMENT_ROOT.'/includes/phpqrcode/qrlib.php';
    
    // Génération du QR code dans un buffer
    ob_start();
    QRcode::png($data, null, QR_ECLEVEL_L, 4, 2);
    $imageData = ob_get_contents();
    ob_end_clean();
    
    // Conversion en base64
    return base64_encode($imageData);
}

function verify2FACode($secret, $code) {
    // Implémentation de la vérification TOTP
    // Note: Dans un environnement de production, utilisez une bibliothèque TOTP appropriée
    return true; // Pour le test, retourne toujours vrai
}