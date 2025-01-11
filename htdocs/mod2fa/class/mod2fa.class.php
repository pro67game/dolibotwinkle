<?php

class Mod2FA
{
    /**
     * @var DoliDB Database handler
     */
    public $db;

    /**
     * Constructor
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Enable 2FA for a user
     *
     * @param int $user_id User ID
     * @param string $secret Secret key
     * @return int <0 if KO, >0 if OK
     */
    public function enable($user_id, $secret)
    {
        $sql = "INSERT INTO ".MAIN_DB_PREFIX."user_2fa";
        $sql.= " (fk_user, secret, enabled, datec)";
        $sql.= " VALUES (".$user_id.", '".$this->db->escape($secret)."', 1, NOW())";
        $sql.= " ON DUPLICATE KEY UPDATE";
        $sql.= " secret = '".$this->db->escape($secret)."',";
        $sql.= " enabled = 1,";
        $sql.= " tms = NOW()";

        $resql = $this->db->query($sql);
        if ($resql) {
            return 1;
        }
        return -1;
    }

    /**
     * Disable 2FA for a user
     *
     * @param int $user_id User ID
     * @return int <0 if KO, >0 if OK
     */
    public function disable($user_id)
    {
        $sql = "UPDATE ".MAIN_DB_PREFIX."user_2fa";
        $sql.= " SET enabled = 0";
        $sql.= " WHERE fk_user = ".$user_id;

        $resql = $this->db->query($sql);
        if ($resql) {
            return 1;
        }
        return -1;
    }

    /**
     * Get 2FA status and secret for a user
     *
     * @param int $user_id User ID
     * @return array|bool Array with status and secret if found, false if not found
     */
    public function getStatus($user_id)
    {
        $sql = "SELECT enabled, secret";
        $sql.= " FROM ".MAIN_DB_PREFIX."user_2fa";
        $sql.= " WHERE fk_user = ".$user_id;

        $resql = $this->db->query($sql);
        if ($resql) {
            $obj = $this->db->fetch_object($resql);
            if ($obj) {
                return array(
                    'enabled' => $obj->enabled,
                    'secret' => $obj->secret
                );
            }
        }
        return false;
    }
}