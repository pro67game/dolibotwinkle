<?php
include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';

class modMod2FA extends DolibarrModules
{
    public function __construct($db)
    {
        global $langs, $conf;

        $this->db = $db;
        $this->numero = 500000; // ID unique du module
        $this->rights_class = 'mod2fa';
        $this->family = "other";
        $this->module_position = '50';
        $this->name = preg_replace('/^mod/i', '', get_class($this));
        $this->description = "Module d'authentification à deux facteurs";
        $this->descriptionlong = "Intégration de l'authentification 2FA via Google Authenticator";
        $this->editor_name = 'Votre Nom';
        $this->version = '1.0';
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
        $this->picto = 'lock';
        $this->module_parts = array(
            'hooks' => array('main'),
            'triggers' => 1,
        );
    }

    public function init($options = '')
    {
        $sql = array();
        $result = $this->_load_tables('/mod2fa/sql/');
        
        // Création de la table pour stocker les secrets 2FA
        $sql[] = "CREATE TABLE IF NOT EXISTS ".MAIN_DB_PREFIX."user_2fa (
            rowid int(11) NOT NULL AUTO_INCREMENT,
            fk_user int(11) NOT NULL,
            secret varchar(255) NOT NULL,
            enabled tinyint(1) DEFAULT 0,
            datec datetime DEFAULT NULL,
            tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (rowid),
            UNIQUE KEY uk_user_2fa_fk_user (fk_user)
        ) ENGINE=InnoDB;";

        return $this->_init($sql, $options);
    }
}