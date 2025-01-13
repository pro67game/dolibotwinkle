<?php
if (false === (@include '../../main.inc.php')) {
    require '../../../main.inc.php';
}

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once '../lib/mod2fa.lib.php';
require_once '../class/mod2fa.class.php';

global $langs, $user, $db;
$langs->loadLangs(array("admin", "mod2fa@mod2fa"));

if (!$user->admin) accessforbidden();

$action = GETPOST('action', 'alpha');
$id = GETPOST('id', 'int');

// Initialize technical object
$hookmanager->initHooks(array('mod2fasetup'));

// Actions
if ($action == 'update') {
    $enforce2fa = GETPOST('enforce2fa', 'alpha');
    $res = dolibarr_set_const($db, "MOD2FA_ENFORCE", $enforce2fa, 'chaine', 0, '', $conf->entity);
    
    if ($res > 0) {
        setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    } else {
        setEventMessages($langs->trans("Error"), null, 'errors');
    }
} elseif ($action == 'toggle2fa') {
    $userId = GETPOST('user_id', 'int');
    $mod2fa = new Mod2FA($db);
    $status = $mod2fa->getStatus($userId);
    
    if ($status && $status['enabled']) {
        if ($mod2fa->disable($userId) > 0) {
            setEventMessages($langs->trans("2FADisabled"), null, 'mesgs');
        }
    } else {
        $secret = generate2FASecret();
        if ($mod2fa->enable($userId, $secret) > 0) {
            setEventMessages($langs->trans("2FAEnabled"), null, 'mesgs');
        }
    }
    header('Location: '.$_SERVER["PHP_SELF"]);
    exit;
}

// View
llxHeader('', $langs->trans("Mod2FASetup"));

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($langs->trans("Mod2FASetup"), $linkback, 'title_setup');

// Configuration page
print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="update">';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameters").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print "</tr>\n";

// Option to enforce 2FA
print '<tr class="oddeven">';
print '<td>'.$langs->trans("Enforce2FA").'</td>';
print '<td>';
print $form->selectyesno("enforce2fa", $conf->global->MOD2FA_ENFORCE, 1);
print '</td></tr>';

print '</table>';

print '<br>';

// Liste des utilisateurs avec leur statut 2FA
print load_fiche_titre($langs->trans("Users2FAStatus"), '', '');

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("User").'</td>';
print '<td>'.$langs->trans("2FAStatus").'</td>';
print '<td>'.$langs->trans("Actions").'</td>';
print "</tr>\n";

$sql = "SELECT u.rowid, u.login, u.firstname, u.lastname, t.enabled as tfa_enabled, t.secret";
$sql.= " FROM ".MAIN_DB_PREFIX."user as u";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."user_2fa as t ON t.fk_user = u.rowid";
$sql.= " WHERE u.statut = 1";

$resql = $db->query($sql);
if ($resql) {
    $num = $db->num_rows($resql);
    $i = 0;
    while ($i < $num) {
        $obj = $db->fetch_object($resql);
        print '<tr class="oddeven">';
        print '<td>'.$obj->firstname.' '.$obj->lastname.' ('.$obj->login.')</td>';
        print '<td>'.($obj->tfa_enabled ? $langs->trans("Enabled") : $langs->trans("Disabled")).'</td>';
        print '<td class="center">';
        
        // Bouton Activer/Désactiver
        print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=toggle2fa&user_id='.$obj->rowid.'&token='.newToken().'">';
        print ($obj->tfa_enabled ? $langs->trans("Disable2FA") : $langs->trans("Enable2FA"));
        print '</a>';
        
        // Bouton QR Code (visible uniquement si 2FA est activé)
        if ($obj->tfa_enabled && $obj->secret) {
            $qrCodeUrl = getQRCodeUrl($obj->login, $obj->secret, $conf->global->MAIN_INFO_SOCIETE_NOM);
            print ' <a class="butAction" href="#" onclick="showQRCode(\''.$qrCodeUrl.'\'); return false;">';
            print $langs->trans("ShowQRCode");
            print '</a>';
        }
        
        print '</td>';
        print '</tr>';
        $i++;
    }
}

print '</table>';

// Modal pour afficher le QR Code
print '<div id="qrCodeModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.4);">';
print '<div style="background-color:#fefefe; margin:15% auto; padding:20px; border:1px solid #888; width:300px; text-align:center;">';
print '<img id="qrCodeImage" src="" style="max-width:200px; margin:10px;"/>';
print '<br><button onclick="hideQRCode()" class="button">'.$langs->trans("Close").'</button>';
print '</div>';
print '</div>';

// JavaScript pour gérer l'affichage du QR Code
print '<script type="text/javascript">
function showQRCode(url) {
    document.getElementById("qrCodeImage").src = url;
    document.getElementById("qrCodeModal").style.display = "block";
}
function hideQRCode() {
    document.getElementById("qrCodeModal").style.display = "none";
}
</script>';

print '<div class="center"><input type="submit" class="button" value="'.$langs->trans("Save").'"></div>';
print '</form>';

llxFooter();
$db->close();