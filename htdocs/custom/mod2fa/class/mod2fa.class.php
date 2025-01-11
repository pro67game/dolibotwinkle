<?php
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

class Mod2FA
{
    private $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function generateSecret()
    {
        $random = random_bytes(20);
        return base32_encode($random);
    }

    public function verifyCode($secret, $code)
    {
        require_once DOL_DOCUMENT_ROOT.'/custom/mod2fa/lib/GoogleAuthenticator.php';
        $ga = new GoogleAuthenticator();
        return $ga->verifyCode($secret, $code, 2);
    }

    public function enableFor($userId, $secret)
    {
        $sql = "INSERT INTO ".MAIN_DB_PREFIX."user_2fa 
                (fk_user, secret, enabled, datec) 
                VALUES (".$userId.", '".$this->db->escape($secret)."', 1, NOW())
                ON DUPLICATE KEY UPDATE secret = VALUES(secret), enabled = VALUES(enabled)";
        
        return $this->db->query($sql);
    }

    public function isEnabled($userId)
    {
        $sql = "SELECT enabled FROM ".MAIN_DB_PREFIX."user_2fa WHERE fk_user = ".$userId;
        $resql = $this->db->query($sql);
        if ($resql && $this->db->num_rows($resql) > 0) {
            $obj = $this->db->fetch_object($resql);
            return $obj->enabled == 1;
        }
        return false;
    }
}