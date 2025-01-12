<?php

class Actionsmod2fa
{
    public $db;
    public $error;
    public $errors = array();

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs;

        $error = 0;
        $context = explode(':', $parameters['context']);

        if (in_array('usercard', $context)) {
            // Ne pas afficher les boutons si on est déjà dans l'action view2fa
            if ($action != 'view2fa') {
                require_once DOL_DOCUMENT_ROOT.'/mod2fa/class/mod2fa.class.php';
                $mod2fa = new Mod2FA($this->db);
                $status = $mod2fa->getStatus($object->id);

                // S'assurer que nous sommes sur la fiche de l'utilisateur connecté ou que nous sommes admin
                if ($object->id == $user->id || $user->admin) {
                    print '<div class="tabsAction">';
                    if (!$status || !$status['enabled']) {
                        print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=view2fa">';
                        print $langs->trans("Activer le 2FA");
                        print '</a>';
                    } else {
                        print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=disable2fa">';
                        print $langs->trans("Désactiver le 2FA");
                        print '</a>';
                    }
                    print '</div>';
                }
            }
        }

        return 0;
    }

    public function formObjectOptions($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs;

        $error = 0;
        $context = explode(':', $parameters['context']);

        if (in_array('usercard', $context)) {
            if ($action == 'view2fa') {
                require_once DOL_DOCUMENT_ROOT.'/mod2fa/class/mod2fa.class.php';
                require_once DOL_DOCUMENT_ROOT.'/mod2fa/lib/mod2fa.lib.php';

                print '<div class="div-table-responsive-no-min">';
                print '<table class="noborder centpercent">';
                print '<tr class="liste_titre">';
                print '<td>'.$langs->trans("Authentification à deux facteurs").'</td>';
                print '<td></td>';
                print '</tr>';

                // Génération du secret et du QR code
                $secret = generate2FASecret();
                $qrCodeUrl = getQRCodeUrl($object->login, $secret, $conf->global->MAIN_INFO_SOCIETE_NOM);

                print '<tr class="oddeven">';
                print '<td colspan="2">';
                print '<p>'.$langs->trans("Scannez ce QR code avec Google Authenticator").'</p>';
                print '<img src="'.$qrCodeUrl.'" />';
                print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">';
                print '<input type="hidden" name="token" value="'.newToken().'">';
                print '<input type="hidden" name="action" value="enable2fa">';
                print '<input type="hidden" name="secret" value="'.$secret.'">';
                print '<p>'.$langs->trans("Entrez le code affiché dans Google Authenticator").'</p>';
                print '<input type="text" name="code" value="">';
                print '<input type="submit" class="button" value="'.$langs->trans("Activer").'">';
                print '</form>';
                print '</td>';
                print '</tr>';

                print '</table>';
                print '</div>';
            }
        }

        return 0;
    }
}