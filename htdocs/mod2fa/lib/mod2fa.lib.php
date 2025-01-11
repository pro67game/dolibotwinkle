<?php

/**
 * Generate a random secret key
 *
 * @return string Secret key
 */
function mod2fa_generate_secret()
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // Base32 alphabet
    $secret = '';
    for ($i = 0; $i < 16; $i++) {
        $secret .= $chars[random_int(0, 31)];
    }
    return $secret;
}

/**
 * Verify TOTP code
 *
 * @param string $secret Secret key
 * @param string $code Code to verify
 * @return bool True if valid, false if not
 */
function mod2fa_verify_code($secret, $code)
{
    require_once DOL_DOCUMENT_ROOT.'/includes/phpqrcode/qrlib.php';
    
    // Current timestamp divided by 30 second time window
    $timestamp = floor(time() / 30);
    
    // Try current and previous window
    for ($i = -1; $i <= 1; $i++) {
        if (mod2fa_generate_code($secret, $timestamp + $i) === $code) {
            return true;
        }
    }
    return false;
}

/**
 * Generate TOTP code
 *
 * @param string $secret Secret key
 * @param int $timestamp Timestamp
 * @return string Generated code
 */
function mod2fa_generate_code($secret, $timestamp)
{
    $secret = base32_decode($secret);
    
    // Pack timestamp into binary string
    $msg = pack('N*', 0) . pack('N*', $timestamp);
    
    // Hash it with users secret key
    $hash = hash_hmac('sha1', $msg, $secret, true);
    
    // Use last nipple of result as index/offset
    $offset = ord(substr($hash, -1)) & 0x0F;
    
    // grab 4 bytes of the result
    $result = unpack('N', substr($hash, $offset, 4));
    $result = $result[1];
    
    // Only 31 bits
    $result = $result & 0x7FFFFFFF;
    
    return str_pad($result % 1000000, 6, '0', STR_PAD_LEFT);
}

/**
 * Base32 decode
 *
 * @param string $str String to decode
 * @return string Decoded string
 */
function base32_decode($str)
{
    $str = strtoupper($str);
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $vals = array_flip(str_split($chars));
    $arr = str_split($str);
    $bin = '';
    foreach ($arr as $chr) {
        if (!isset($vals[$chr])) continue;
        $bin .= sprintf('%05b', $vals[$chr]);
    }
    $pad = strlen($bin) % 8;
    if ($pad > 0) $bin = substr($bin, 0, -$pad);
    return pack('B*', $bin);
}