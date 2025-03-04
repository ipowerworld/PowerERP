<?php
/* Copyright (C) 2007-2011	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2008-2012	Regis Houssin		<regis.houssin@inodbox.com>
 * Copyright (C) 2008-2011	Juanjo Menent		<jmenent@2byte.es>
 * Copyright (C) 2014       Teddy Andreotti    	<125155@supinfo.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *       \file       htdocs/user/passwordforgotten.php
 *       \brief      Page to ask a new password
 */

define("NOLOGIN", 1); // This means this output page does not require to be logged.

// Load PowerERP environment
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
if (!empty($conf->ldap->enabled)) {
	require_once DOL_DOCUMENT_ROOT.'/core/class/ldap.class.php';
}

// Load translation files required by page
$langs->loadLangs(array('errors', 'users', 'companies', 'ldap', 'other'));

// Security check
if (!empty($conf->global->MAIN_SECURITY_DISABLEFORGETPASSLINK)) {
	header("Location: ".DOL_URL_ROOT.'/');
	exit;
}

$action = GETPOST('action', 'aZ09');
$mode = $powererp_main_authentication;
if (!$mode) {
	$mode = 'http';
}

$username = GETPOST('username', 'alphanohtml');
$passworduidhash = GETPOST('passworduidhash', 'alpha');
$setnewpassword = GETPOST('setnewpassword', 'aZ09');

$conf->entity = (GETPOST('entity', 'int') ? GETPOST('entity', 'int') : 1);

// Instantiate hooks of thirdparty module only if not already define
$hookmanager->initHooks(array('passwordforgottenpage'));


if (GETPOST('dol_hide_leftmenu', 'alpha') || !empty($_SESSION['dol_hide_leftmenu'])) {
	$conf->dol_hide_leftmenu = 1;
}
if (GETPOST('dol_hide_topmenu', 'alpha') || !empty($_SESSION['dol_hide_topmenu'])) {
	$conf->dol_hide_topmenu = 1;
}
if (GETPOST('dol_optimize_smallscreen', 'alpha') || !empty($_SESSION['dol_optimize_smallscreen'])) {
	$conf->dol_optimize_smallscreen = 1;
}
if (GETPOST('dol_no_mouse_hover', 'alpha') || !empty($_SESSION['dol_no_mouse_hover'])) {
	$conf->dol_no_mouse_hover = 1;
}
if (GETPOST('dol_use_jmobile', 'alpha') || !empty($_SESSION['dol_use_jmobile'])) {
	$conf->dol_use_jmobile = 1;
}


/**
 * Actions
 */

$parameters = array('username' => $username);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	$message = $hookmanager->error;
}

if (empty($reshook)) {
	// Validate new password
	if ($action == 'validatenewpassword' && $username && $passworduidhash) {
		$edituser = new User($db);
		$result = $edituser->fetch('', $username, '', 0, $conf->entity);
		if ($result < 0) {
			$message = '<div class="error">'.dol_escape_htmltag($langs->trans("ErrorTechnicalError")).'</div>';
		} else {
			global $powererp_main_instance_unique_id;

			//print $edituser->pass_temp.'-'.$edituser->id.'-'.$powererp_main_instance_unique_id.' '.$passworduidhash;
			if ($edituser->pass_temp && dol_verifyHash($edituser->pass_temp.'-'.$edituser->id.'-'.$powererp_main_instance_unique_id, $passworduidhash)) {
				// Clear session
				unset($_SESSION['dol_login']);
				$_SESSION['dol_loginmesg'] = '<!-- warning -->'.$langs->transnoentitiesnoconv('NewPasswordValidated'); // Save message for the session page

				$newpassword = $edituser->setPassword($user, $edituser->pass_temp, 0);
				dol_syslog("passwordforgotten.php new password for user->id=".$edituser->id." validated in database");

				header("Location: ".DOL_URL_ROOT.'/');
				exit;
			} else {
				$langs->load("errors");
				$message = '<div class="error">'.$langs->trans("ErrorFailedToValidatePasswordReset").'</div>';
			}
		}
	}

	// Action to set a temporary password and send email for reset
	if ($action == 'buildnewpassword' && $username) {
		$sessionkey = 'dol_antispam_value';
		$ok = (array_key_exists($sessionkey, $_SESSION) === true && (strtolower($_SESSION[$sessionkey]) == strtolower(GETPOST('code'))));

		// Verify code
		if (!$ok) {
			$message = '<div class="error">'.$langs->trans("ErrorBadValueForCode").'</div>';
		} else {
			$isanemail = preg_match('/@/', $username);

			$edituser = new User($db);
			$result = $edituser->fetch('', $username, '', 1, $conf->entity);
			if ($result == 0 && $isanemail) {
				$result = $edituser->fetch('', '', '', 1, $conf->entity, $username);
			}

			// Set the message to show (must be the same if login/email exists or not
			// to avoid to guess them.
			$messagewarning = '<div class="warning paddingtopbottom'.(empty($conf->global->MAIN_LOGIN_BACKGROUND) ? '' : ' backgroundsemitransparent boxshadow').'">';
			if (!$isanemail) {
				$messagewarning .= $langs->trans("IfLoginExistPasswordRequestSent");
			} else {
				$messagewarning .= $langs->trans("IfEmailExistPasswordRequestSent");
			}
			$messagewarning .= '</div>';

			if ($result <= 0 && $edituser->error == 'USERNOTFOUND') {
				usleep(20000);	// add delay to simulate setPassword and send_password actions delay (0.02s)
				$message .= $messagewarning;
				$username = '';
			} else {
				if (empty($edituser->email)) {
					usleep(20000);	// add delay to simulate setPassword and send_password actions delay (0.02s)
					$message .= $messagewarning;
				} else {
					$newpassword = $edituser->setPassword($user, '', 1);
					if (is_numeric($newpassword) && $newpassword < 0) {
						// Technical failure
						$message = '<div class="error">'.$langs->trans("ErrorFailedToChangePassword").'</div>';
					} else {
						// Success
						if ($edituser->send_password($user, $newpassword, 1) > 0) {
							$message .= $messagewarning;
							$username = '';
						} else {
							// Technical failure
							$message .= '<div class="error">'.$edituser->error.'</div>';
						}
					}
				}
			}
		}
	}
}


/**
 * View
 */

$dol_url_root = DOL_URL_ROOT;

// Title
$title = 'PowerERP '.DOL_VERSION;
if (!empty($conf->global->MAIN_APPLICATION_TITLE)) {
	$title = $conf->global->MAIN_APPLICATION_TITLE;
}

// Select templates
if (file_exists(DOL_DOCUMENT_ROOT."/theme/".$conf->theme."/tpl/passwordforgotten.tpl.php")) {
	$template_dir = DOL_DOCUMENT_ROOT."/theme/".$conf->theme."/tpl/";
} else {
	$template_dir = DOL_DOCUMENT_ROOT."/core/tpl/";
}

if (!$username) {
	$focus_element = 'username';
} else {
	$focus_element = 'password';
}

// Send password button enabled ?
$disabled = 'disabled';
if (preg_match('/powererp/i', $mode)) {
	$disabled = '';
}
if (!empty($conf->global->MAIN_SECURITY_ENABLE_SENDPASSWORD)) {
	$disabled = ''; // To force button enabled
}

// Show logo (search in order: small company logo, large company logo, theme logo, common logo)
$width = 0;
$rowspan = 2;
$urllogo = DOL_URL_ROOT.'/theme/common/login_logo.png';
if (!empty($mysoc->logo_small) && is_readable($conf->mycompany->dir_output.'/logos/thumbs/'.$mysoc->logo_small)) {
	$urllogo = DOL_URL_ROOT.'/viewimage.php?cache=1&amp;modulepart=mycompany&amp;file='.urlencode('logos/thumbs/'.$mysoc->logo_small);
} elseif (!empty($mysoc->logo_small) && is_readable($conf->mycompany->dir_output.'/logos/'.$mysoc->logo)) {
	$urllogo = DOL_URL_ROOT.'/viewimage.php?cache=1&amp;modulepart=mycompany&amp;file='.urlencode('logos/'.$mysoc->logo);
	$width = 128;
} elseif (is_readable(DOL_DOCUMENT_ROOT.'/theme/'.$conf->theme.'/img/powererp_logo.svg')) {
	$urllogo = DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/powererp_logo.svg';
} elseif (is_readable(DOL_DOCUMENT_ROOT.'/theme/powererp_logo.svg')) {
	$urllogo = DOL_URL_ROOT.'/theme/powererp_logo.svg';
}

// Security graphical code
if (function_exists("imagecreatefrompng") && !$disabled) {
	$captcha = 1;
	$captcha_refresh = img_picto($langs->trans("Refresh"), 'refresh', 'id="captcha_refresh_img"');
}

// Execute hook getPasswordForgottenPageOptions (for table)
$parameters = array('entity' => GETPOST('entity', 'int'));
$hookmanager->executeHooks('getPasswordForgottenPageOptions', $parameters); // Note that $action and $object may have been modified by some hooks
if (is_array($hookmanager->resArray) && !empty($hookmanager->resArray)) {
	$morelogincontent = $hookmanager->resArray; // (deprecated) For compatibility
} else {
	$morelogincontent = $hookmanager->resPrint;
}

// Execute hook getPasswordForgottenPageExtraOptions (eg for js)
$parameters = array('entity' => GETPOST('entity', 'int'));
$reshook = $hookmanager->executeHooks('getPasswordForgottenPageExtraOptions', $parameters); // Note that $action and $object may have been modified by some hooks.
$moreloginextracontent = $hookmanager->resPrint;

if (empty($setnewpassword)) {
	include $template_dir.'passwordforgotten.tpl.php'; // To use native PHP
} else {
	include $template_dir.'passwordreset.tpl.php'; // To use native PHP
}
