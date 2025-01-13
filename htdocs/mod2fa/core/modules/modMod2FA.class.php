<?php
include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';

class modMod2FA extends DolibarrModules
{
    public function __construct($db)
    {
        global $langs, $conf;

        $this->db = $db;
        $this->numero = 500000;
        $this->rights_class = 'mod2fa';
        $this->family = "security";
        $this->module_position = '50';
        $this->name = preg_replace('/^mod/i', '', get_class($this));
        $this->description = "Module d'authentification à deux facteurs";
        $this->descriptionlong = "Intégration de l'authentification 2FA via Google Authenticator";
        $this->editor_name = 'Votre Nom';
        $this->version = '1.0';
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
        $this->picto = 'lock';
        
        $this->module_parts = array(
            'hooks' => array(
                'usercard',
                'main'
            ),
            'triggers' => 1,
        );

        $this->config_page_url = array("setup.php@mod2fa");
    }

    public function init($options = '')
    {
        $sql = array();
        
        $sql[] = "CREATE TABLE IF NOT EXISTS ".MAIN_DB_PREFIX."user_2fa (
            rowid int(11) NOT NULL AUTO_INCREMENT,
            fk_user int(11) NOT NULL,
            secret varchar(255),
            enabled tinyint(1) DEFAULT 0,
            datec datetime DEFAULT NULL,
            tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (rowid),
            UNIQUE KEY uk_user_2fa_fk_user (fk_user)
        ) ENGINE=InnoDB;";

        // Installation de phpqrcode
        $phpqrcodePath = DOL_DOCUMENT_ROOT.'/includes/phpqrcode';
        if (!is_dir($phpqrcodePath)) {
            // Créer le dossier includes s'il n'existe pas
            if (!is_dir(DOL_DOCUMENT_ROOT.'/includes')) {
                mkdir(DOL_DOCUMENT_ROOT.'/includes');
            }
            
            // Télécharger et extraire phpqrcode
            $phpqrcodeUrl = 'https://sourceforge.net/projects/phpqrcode/files/latest/download';
            $tempFile = tempnam(sys_get_temp_dir(), 'phpqrcode');
            file_put_contents($tempFile, file_get_contents($phpqrcodeUrl));
            
            // Extraire l'archive
            $zip = new ZipArchive;
            if ($zip->open($tempFile) === TRUE) {
                $zip->extractTo($phpqrcodePath);
                $zip->close();
                
                // Déplacer les fichiers du sous-dossier vers le dossier principal
                $subDir = glob($phpqrcodePath.'/phpqrcode-*', GLOB_ONLYDIR);
                if (!empty($subDir)) {
                    $subDir = $subDir[0];
                    $files = glob($subDir.'/*');
                    foreach($files as $file) {
                        rename($file, $phpqrcodePath.'/'.basename($file));
                    }
                    rmdir($subDir);
                }
            }
            unlink($tempFile);
        }

        return $this->_init($sql, $options);
    }
}