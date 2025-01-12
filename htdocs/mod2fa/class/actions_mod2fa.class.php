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
            if ($action != 'view2fa') {
                require_once DOL_DOCUMENT_ROOT.'/mod2fa/class/mod2fa.class.php';
                $mod2fa = new Mod2FA($this->db);
                $status = $mod2fa->getStatus($object->id);

                // Vérifier si l'utilisateur est admin ou si c'est sa propre fiche
                if ($user->admin || $object->id == $user->id) {
                    print '<div class="tabsAction">';
                    
                    // Si 2FA n'est pas activé
                    if (!$status || !$status['enabled']) {
                        // Si l'utilisateur est dans le groupe 2FA_enabled ou si c'est un admin
                        if ($user->admin || $mod2fa->isUserInGroup2FA($object->id)) {
                            print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=view2fa">';
                            print $langs->trans("Enable2FA");
                            print '</a>';
                        }
                    } else {
                        print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=disable2fa">';
                        print $langs->trans("Disable2FA");
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

                // Vérifier si l'utilisateur est admin ou si c'est sa propre fiche
                if ($user->admin || $object->id == $user->id) {
                    print '<div class="div-table-responsive-no-min">';
                    print '<table class="noborder centpercent">';
                    print '<tr class="liste_titre">';
                    print '<td>'.$langs->trans("Two-Factor Authentication").'</td>';
                    print '<td></td>';
                    print '</tr>';

                    $secret = generate2FASecret();
                    $qrCodeUrl = getQRCodeUrl($object->login, $secret, $conf->global->MAIN_INFO_SOCIETE_NOM);

                    print '<tr class="oddeven">';
                    print '<td colspan="2">';
                    print '<p>'.$langs->trans("ScanQRCodeWithGoogleAuth").'</p>';
                    print '<img src="'.$qrCodeUrl.'" alt="QR Code" />';
                    print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">';
                    print '<input type="hidden" name="token" value="'.newToken().'">';
                    print '<input type="hidden" name="action" value="enable2fa">';
                    print '<input type="hidden" name="secret" value="'.$secret.'">';
                    print '<p>'.$langs->trans("EnterCodeFromGoogleAuth").'</p>';
                    print '<input type="text" name="code" value="">';
                    print '<input type="submit" class="button" value="'.$langs->trans("Enable").'">';
                    print '</form>';
                    print '</td>';
                    print '</tr>';

                    print '</table>';
                    print '</div>';
                }
            }
        }

        return 0;
    }
}