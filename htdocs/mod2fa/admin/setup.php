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

$sql = "SELECT u.rowid, u.login, u.firstname, u.lastname, t.enabled as tfa_enabled";
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
        if (!$obj->tfa_enabled) {
            print '<a class="butAction" href="'.DOL_URL_ROOT.'/user/card.php?id='.$obj->rowid.'&action=view2fa">'.$langs->trans("Enable2FA").'</a>';
        } else {
            print '<a class="butActionDelete" href="'.DOL_URL_ROOT.'/user/card.php?id='.$obj->rowid.'&action=disable2fa">'.$langs->trans("Disable2FA").'</a>';
        }
        print '</td>';
        print '</tr>';
        $i++;
    }
}

print '</table>';

print '<div class="center"><input type="submit" class="button" value="'.$langs->trans("Save").'"></div>';
print '</form>';

llxFooter();
$db->close();