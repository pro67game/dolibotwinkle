<?php
// Load Dolibarr environment
if (false === (@include '../../main.inc.php')) {  // From htdocs directory
    require '../../../main.inc.php'; // From "custom" directory
}

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once '../lib/mod2fa.lib.php';
require_once '../class/mod2fa.class.php';

global $langs, $user;
$langs->loadLangs(array("admin", "mod2fa@mod2fa"));

// Security check
if (!$user->admin) {
    accessforbidden();
}

$action = GETPOST('action', 'alpha');

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('mod2fasetup', 'globalsetup'));

/*
 * Actions
 */

if ($action == 'update') {
    $enforce2fa = GETPOST('enforce2fa', 'alpha');
    $res = dolibarr_set_const($db, "MOD2FA_ENFORCE", $enforce2fa, 'chaine', 0, '', $conf->entity);
    
    if ($res > 0) {
        setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    } else {
        setEventMessages($langs->trans("Error"), null, 'errors');
    }
}

/*
 * View
 */

$form = new Form($db);

llxHeader('', $langs->trans("Mod2FASetup"));

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($langs->trans("Mod2FASetup"), $linkback, 'title_setup');

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

print '<div class="center"><input type="submit" class="button" value="'.$langs->trans("Save").'"></div>';

print '</form>';

// Page end
llxFooter();
$db->close();