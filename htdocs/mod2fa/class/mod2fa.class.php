<?php

class Mod2FA
{
    public $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

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

    public function disable($user_id)
    {
        $sql = "UPDATE ".MAIN_DB_PREFIX."user_2fa";
        $sql.= " SET enabled = 0, secret = NULL";
        $sql.= " WHERE fk_user = ".$user_id;

        $resql = $this->db->query($sql);
        if ($resql) {
            return 1;
        }
        return -1;
    }

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

    public function isUserInGroup2FA($user_id)
    {
        $sql = "SELECT COUNT(*) as nb FROM ".MAIN_DB_PREFIX."usergroup_user as ugu";
        $sql.= " INNER JOIN ".MAIN_DB_PREFIX."usergroup as ug ON ugu.fk_usergroup = ug.rowid";
        $sql.= " WHERE ugu.fk_user = ".$user_id;
        $sql.= " AND ug.nom = '2FA_enabled'";

        $resql = $this->db->query($sql);
        if ($resql) {
            $obj = $this->db->fetch_object($resql);
            return ($obj->nb > 0);
        }
        return false;
    }
}