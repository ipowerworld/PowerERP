<?php
/* Copyright (C) 2007-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2009-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2013	   Juanjo Menent		<jmenent@2byte.es>
 * Copyright (C) 2016      Jonathan TISSEAU     <jonathan.tisseau@86dev.fr>
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
 *       \file       htdocs/admin/mails.php
 *       \brief      Page to setup emails sending
 */

// Load PowerERP environment
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

// Load translation files required by the page
$langs->loadLangs(array('companies', 'products', 'admin', 'mails', 'other', 'errors'));

$action = GETPOST('action', 'aZ09');
$cancel = GETPOST('cancel', 'aZ09');

$usersignature = $user->signature;
// For action = test or send, we ensure that content is not html, even for signature, because this we want a test with NO html.
if ($action == 'test' || $action == 'send') {
	$usersignature = dol_string_nohtmltag($usersignature, 2);
}

$substitutionarrayfortest = array(
	'__DOL_MAIN_URL_ROOT__'=>DOL_MAIN_URL_ROOT,
	'__ID__' => 'RecipientIdRecord',
	//'__EMAIL__' => 'RecipientEMail',				// Done into actions_sendmails
	'__CHECK_READ__' => (!empty($object) && is_object($object) && is_object($object->thirdparty)) ? '<img src="'.DOL_MAIN_URL_ROOT.'/public/emailing/mailing-read.php?tag='.$object->thirdparty->tag.'&securitykey='.urlencode($conf->global->MAILING_EMAIL_UNSUBSCRIBE_KEY).'" width="1" height="1" style="width:1px;height:1px" border="0"/>' : '',
	'__USER_SIGNATURE__' => (($user->signature && empty($conf->global->MAIN_MAIL_DO_NOT_USE_SIGN)) ? $usersignature : ''), // Done into actions_sendmails
	'__SENDEREMAIL_SIGNATURE__' => (($user->signature && empty($conf->global->MAIN_MAIL_DO_NOT_USE_SIGN)) ? $usersignature : ''), // Done into actions_sendmails
	'__LOGIN__' => $user->login,
	'__LASTNAME__' => 'RecipientLastname',
	'__FIRSTNAME__' => 'RecipientFirstname',
	'__ADDRESS__'=> 'RecipientAddress',
	'__ZIP__'=> 'RecipientZip',
	'__TOWN_'=> 'RecipientTown',
	'__COUNTRY__'=> 'RecipientCountry'
);
complete_substitutions_array($substitutionarrayfortest, $langs);

// Security check
if (!$user->admin) {
	accessforbidden();
}


/*
 * Actions
 */

if ($action == 'update' && !$cancel) {
	// Send mode parameters
	powererp_set_const($db, "MAIN_MAIL_SENDMODE_EMAILING", GETPOST("MAIN_MAIL_SENDMODE_EMAILING"), 'chaine', 0, '', $conf->entity);
	powererp_set_const($db, "MAIN_MAIL_SMTP_PORT_EMAILING", GETPOST("MAIN_MAIL_SMTP_PORT_EMAILING"), 'chaine', 0, '', $conf->entity);
	powererp_set_const($db, "MAIN_MAIL_SMTP_SERVER_EMAILING", GETPOST("MAIN_MAIL_SMTP_SERVER_EMAILING"), 'chaine', 0, '', $conf->entity);
	powererp_set_const($db, "MAIN_MAIL_SMTPS_ID_EMAILING", GETPOST("MAIN_MAIL_SMTPS_ID_EMAILING"), 'chaine', 0, '', $conf->entity);
	if (GETPOSTISSET("MAIN_MAIL_SMTPS_PW_EMAILING")) {
		powererp_set_const($db, "MAIN_MAIL_SMTPS_PW_EMAILING", GETPOST("MAIN_MAIL_SMTPS_PW_EMAILING", 'none'), 'chaine', 0, '', $conf->entity);
	}
	if (GETPOSTISSET("MAIN_MAIL_SMTPS_AUTH_TYPE_EMAILING")) {
		powererp_set_const($db, "MAIN_MAIL_SMTPS_AUTH_TYPE_EMAILING", GETPOST("MAIN_MAIL_SMTPS_AUTH_TYPE_EMAILING", 'chaine'), 'chaine', 0, '', $conf->entity);
	}
	if (GETPOSTISSET("MAIN_MAIL_SMTPS_OAUTH_SERVICE_EMAILING")) {
		powererp_set_const($db, "MAIN_MAIL_SMTPS_OAUTH_SERVICE_EMAILING", GETPOST("MAIN_MAIL_SMTPS_OAUTH_SERVICE_EMAILING", 'chaine'), 'chaine', 0, '', $conf->entity);
	}
	powererp_set_const($db, "MAIN_MAIL_EMAIL_TLS_EMAILING", GETPOST("MAIN_MAIL_EMAIL_TLS_EMAILING"), 'chaine', 0, '', $conf->entity);
	powererp_set_const($db, "MAIN_MAIL_EMAIL_STARTTLS_EMAILING", GETPOST("MAIN_MAIL_EMAIL_STARTTLS_EMAILING"), 'chaine', 0, '', $conf->entity);
	powererp_set_const($db, "MAIN_MAIL_EMAIL_SMTP_ALLOW_SELF_SIGNED_EMAILING", GETPOST("MAIN_MAIL_EMAIL_SMTP_ALLOW_SELF_SIGNED_EMAILING"), 'chaine', 0, '', $conf->entity);

	header("Location: ".$_SERVER["PHP_SELF"]."?mainmenu=home&leftmenu=setup");
	exit;
}


// Actions to send emails
$id = 0;
$actiontypecode = ''; // Not an event for agenda
$triggersendname = ''; // Disable triggers
$paramname = 'id';
$mode = 'emailfortest';
$trackid = (($action == 'testhtml') ? "testhtml" : "test");
$sendcontext = 'emailing'; // Force to use dedicated context of setup for emailing
include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';

if ($action == 'presend' && GETPOST('trackid', 'alphanohtml') == 'test') {
	$action = 'test';
}
if ($action == 'presend' && GETPOST('trackid', 'alphanohtml') == 'testhtml') {
	$action = 'testhtml';
}




/*
 * View
 */

$form = new Form($db);

$linuxlike = 1;
if (preg_match('/^win/i', PHP_OS)) {
	$linuxlike = 0;
}
if (preg_match('/^mac/i', PHP_OS)) {
	$linuxlike = 0;
}

if (empty($conf->global->MAIN_MAIL_SENDMODE_EMAILING)) {
	$conf->global->MAIN_MAIL_SENDMODE_EMAILING = 'default';
}
$port = !empty($conf->global->MAIN_MAIL_SMTP_PORT_EMAILING) ? $conf->global->MAIN_MAIL_SMTP_PORT_EMAILING : ini_get('smtp_port');
if (!$port) {
	$port = 25;
}
$server = !empty($conf->global->MAIN_MAIL_SMTP_SERVER_EMAILING) ? $conf->global->MAIN_MAIL_SMTP_SERVER_EMAILING : ini_get('SMTP');
if (!$server) {
	$server = '127.0.0.1';
}


$wikihelp = 'EN:Setup_EMails|FR:Paramétrage_EMails|ES:Configuración_EMails';
llxHeader('', $langs->trans("Setup"), $wikihelp);

print load_fiche_titre($langs->trans("EMailsSetup"), '', 'title_setup');

$head = email_admin_prepare_head();

// List of sending methods
$listofmethods = array();
$listofmethods['default'] = $langs->trans('DefaultOutgoingEmailSetup');
$listofmethods['mail'] = 'PHP mail function';
//$listofmethods['simplemail']='Simplemail class';
$listofmethods['smtps'] = 'SMTP/SMTPS socket library';
if (version_compare(phpversion(), '7.0', '>=')) {
	$listofmethods['swiftmailer'] = 'Swift Mailer socket library';
}

// List of oauth services
$oauthservices = array();

foreach ($conf->global as $key => $val) {
	if (!empty($val) && preg_match('/^OAUTH_.*_ID$/', $key)) {
		$key = preg_replace('/^OAUTH_/', '', $key);
		$key = preg_replace('/_ID$/', '', $key);
		if (preg_match('/^.*-/', $key)) {
			$name = preg_replace('/^.*-/', '', $key);
		} else {
			$name = $langs->trans("NoName");
		}
		$provider = preg_replace('/-.*$/', '', $key);
		$provider = ucfirst(strtolower($provider));

		$oauthservices[$key] = $name." (".$provider.")";
	}
}

if ($action == 'edit') {
	if ($conf->use_javascript_ajax) {
		print "\n".'<script type="text/javascript">';
		print 'jQuery(document).ready(function () {
                    function initfields()
                    {
                        if (jQuery("#MAIN_MAIL_SENDMODE_EMAILING").val()==\'default\')
                        {
                            jQuery(".hideifdefault").hide();
						}
						else
						{
                            jQuery(".hideifdefault").show();
						}

						if (jQuery("#MAIN_MAIL_SENDMODE_EMAILING").val()==\'mail\')
                        {
                            jQuery(".drag").hide();
                            jQuery("#MAIN_MAIL_EMAIL_TLS_EMAILING").val(0);
                            jQuery("#MAIN_MAIL_EMAIL_TLS_EMAILING").prop("disabled", true);
                            jQuery("#MAIN_MAIL_EMAIL_STARTTLS_EMAILING").val(0);
                            jQuery("#MAIN_MAIL_EMAIL_STARTTLS_EMAILING").prop("disabled", true);
                            jQuery("#MAIN_MAIL_EMAIL_SMTP_ALLOW_SELF_SIGNED_EMAILING").val(0);
                            jQuery("#MAIN_MAIL_EMAIL_SMTP_ALLOW_SELF_SIGNED_EMAILING").prop("disabled", true);
														jQuery(".smtp_method").hide();
                            jQuery(".smtp_auth_method").hide();
                            ';
		if ($linuxlike) {
			print '
			               jQuery("#MAIN_MAIL_SMTP_SERVER_EMAILING").hide();
			               jQuery("#MAIN_MAIL_SMTP_PORT_EMAILING").hide();
			               jQuery("#smtp_server_mess").show();
			               jQuery("#smtp_port_mess").show();
			               ';
		} else {
			print '
			               jQuery("#MAIN_MAIL_SMTP_SERVER_EMAILING").prop("disabled", true);
			               jQuery("#MAIN_MAIL_SMTP_PORT_EMAILING").prop("disabled", true);
			               jQuery("#smtp_server_mess").hide();
			               jQuery("#smtp_port_mess").hide();
			               ';
		}
		print '
                        }
                        if (jQuery("#MAIN_MAIL_SENDMODE_EMAILING").val()==\'smtps\')
                        {
                            jQuery(".drag").show();
                            jQuery("#MAIN_MAIL_EMAIL_TLS_EMAILING").val('.$conf->global->MAIN_MAIL_EMAIL_TLS_EMAILING.');
                            jQuery("#MAIN_MAIL_EMAIL_TLS_EMAILING").removeAttr("disabled");
                            jQuery("#MAIN_MAIL_EMAIL_STARTTLS_EMAILING").val('.$conf->global->MAIN_MAIL_EMAIL_STARTTLS_EMAILING.');
                            jQuery("#MAIN_MAIL_EMAIL_STARTTLS_EMAILING").removeAttr("disabled");
                            jQuery("#MAIN_MAIL_EMAIL_SMTP_ALLOW_SELF_SIGNED_EMAILING").val('.$conf->global->MAIN_MAIL_EMAIL_SMTP_ALLOW_SELF_SIGNED_EMAILING.');
                            jQuery("#MAIN_MAIL_EMAIL_SMTP_ALLOW_SELF_SIGNED_EMAILING").removeAttr("disabled");
                            jQuery("#MAIN_MAIL_SMTP_SERVER_EMAILING").removeAttr("disabled");
                            jQuery("#MAIN_MAIL_SMTP_PORT_EMAILING").removeAttr("disabled");
                            jQuery("#MAIN_MAIL_SMTP_SERVER_EMAILING").show();
                            jQuery("#MAIN_MAIL_SMTP_PORT_EMAILING").show();
														jQuery("#smtp_port_mess").hide();
                            jQuery("#smtp_server_mess").hide();
														jQuery(".smtp_method").show();
                            jQuery(".smtp_auth_method").show();
													}
                        if (jQuery("#MAIN_MAIL_SENDMODE_EMAILING").val()==\'swiftmailer\')
                        {
                            jQuery(".drag").show();
                            jQuery("#MAIN_MAIL_EMAIL_TLS_EMAILING").val('.$conf->global->MAIN_MAIL_EMAIL_TLS_EMAILING.');
                            jQuery("#MAIN_MAIL_EMAIL_TLS_EMAILING").removeAttr("disabled");
                            jQuery("#MAIN_MAIL_EMAIL_STARTTLS_EMAILING").val('.$conf->global->MAIN_MAIL_EMAIL_STARTTLS_EMAILING.');
                            jQuery("#MAIN_MAIL_EMAIL_STARTTLS_EMAILING").removeAttr("disabled");
                            jQuery("#MAIN_MAIL_EMAIL_SMTP_ALLOW_SELF_SIGNED_EMAILING").val('.$conf->global->MAIN_MAIL_EMAIL_SMTP_ALLOW_SELF_SIGNED_EMAILING.');
                            jQuery("#MAIN_MAIL_EMAIL_SMTP_ALLOW_SELF_SIGNED_EMAILING").removeAttr("disabled");
                            jQuery("#MAIN_MAIL_SMTP_SERVER_EMAILING").removeAttr("disabled");
                            jQuery("#MAIN_MAIL_SMTP_PORT_EMAILING").removeAttr("disabled");
                            jQuery("#MAIN_MAIL_SMTP_SERVER_EMAILING").show();
                            jQuery("#MAIN_MAIL_SMTP_PORT_EMAILING").show();
                            jQuery("#smtp_server_mess").hide();
                            jQuery("#smtp_port_mess").hide();
														jQuery(".smtp_method").show();
														jQuery(".smtp_auth_method").show();
                        }
                    }
										function change_smtp_auth_method() {
											console.log(jQuery("#radio_pw").prop("checked"));
											if (jQuery("#MAIN_MAIL_SENDMODE_EMAILING").val()==\'smtps\' && jQuery("#radio_oauth").prop("checked")) {
												jQuery(".smtp_oauth_service").show();
												jQuery(".smtp_pw").hide();
											} else if (jQuery("#MAIN_MAIL_SENDMODE_EMAILING").val()==\'swiftmailer\' && jQuery("#radio_oauth").prop("checked")) {
												jQuery(".smtp_oauth_service").show();
												jQuery(".smtp_pw").hide();
											} else if(jQuery("#MAIN_MAIL_SENDMODE_EMAILING").val()==\'mail\' || jQuery("#MAIN_MAIL_SENDMODE_EMAILING").val()==\'default\'){
												jQuery(".smtp_oauth_service").hide();
												jQuery(".smtp_pw").hide();
											} else {
												jQuery(".smtp_oauth_service").hide();
												jQuery(".smtp_pw").show();
											}
										}
                    initfields();
										change_smtp_auth_method();
										jQuery("#MAIN_MAIL_SENDMODE_EMAILING").change(function() {
                        initfields();
											change_smtp_auth_method();
										});
										jQuery("#radio_pw, #radio_oauth").change(function() {
											change_smtp_auth_method();
										});
					jQuery("#MAIN_MAIL_EMAIL_TLS_EMAILING").change(function() {
						if (jQuery("#MAIN_MAIL_EMAIL_TLS_EMAILING").val() == 1)
							jQuery("#MAIN_MAIL_EMAIL_STARTTLS_EMAILING").val(0);
						else
							jQuery("#MAIN_MAIL_EMAIL_SMTP_ALLOW_SELF_SIGNED_EMAILING").val(0);
					});
					jQuery("#MAIN_MAIL_EMAIL_STARTTLS_EMAILING").change(function() {
						if (jQuery("#MAIN_MAIL_EMAIL_STARTTLS_EMAILING").val() == 1)
							jQuery("#MAIN_MAIL_EMAIL_TLS_EMAILING").val(0);
						else
							jQuery("#MAIN_MAIL_EMAIL_SMTP_ALLOW_SELF_SIGNED_EMAILING").val(0);
					});
        })';
		print '</script>'."\n";
	}

	print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="update">';

	print dol_get_fiche_head($head, 'common_emailing', '', -1);

	print '<span class="opacitymedium">'.$langs->trans("EMailsDesc")."</span><br>\n";
	print "<br><br>\n";


	clearstatcache();

	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre"><td class="titlefieldmiddle">'.$langs->trans("Parameter").'</td><td>'.$langs->trans("Value").'</td></tr>';

	// Method

	print '<tr class="oddeven"><td>'.$langs->trans("MAIN_MAIL_SENDMODE").'</td><td>';

	// SuperAdministrator access only
	if ((empty($conf->global->MAIN_MODULE_MULTICOMPANY)) || ($user->admin && !$user->entity)) {
		print $form->selectarray('MAIN_MAIL_SENDMODE_EMAILING', $listofmethods, $conf->global->MAIN_MAIL_SENDMODE_EMAILING);
	} else {
		$text = $listofmethods[$conf->global->MAIN_MAIL_SENDMODE_EMAILING];
		if (empty($text)) {
			$text = $langs->trans("Undefined");
		}
		$htmltext = $langs->trans("ContactSuperAdminForChange");
		print $form->textwithpicto($text, $htmltext, 1, 'superadmin');
		print '<input type="hidden" name="MAIN_MAIL_SENDMODE_EMAILING" value="'.$conf->global->MAIN_MAIL_SENDMODE_EMAILING.'">';
	}
	print '</td></tr>';

	// Host server

	print '<tr class="oddeven hideifdefault">';
	if (!$conf->use_javascript_ajax && $linuxlike && $conf->global->MAIN_MAIL_SENDMODE_EMAILING == 'mail') {
		print '<td>';
		print $langs->trans("MAIN_MAIL_SMTP_SERVER_NotAvailableOnLinuxLike");
		print '</td><td>';
		print '<span class="opacitymedium">'.$langs->trans("SeeLocalSendMailSetup").'</span>';
		print '</td>';
	} else {
		print '<td>';
		$mainserver = (!empty($conf->global->MAIN_MAIL_SMTP_SERVER_EMAILING) ? $conf->global->MAIN_MAIL_SMTP_SERVER_EMAILING : '');
		$smtpserver = ini_get('SMTP') ?ini_get('SMTP') : $langs->transnoentities("Undefined");
		if ($linuxlike) {
			print $langs->trans("MAIN_MAIL_SMTP_SERVER_NotAvailableOnLinuxLike");
		} else {
			print $langs->trans("MAIN_MAIL_SMTP_SERVER", $smtpserver);
		}
		print '</td><td>';
		// SuperAdministrator access only
		if (!isModEnabled('multicompany') || ($user->admin && !$user->entity)) {
			print '<input class="flat minwidth300" id="MAIN_MAIL_SMTP_SERVER_EMAILING" name="MAIN_MAIL_SMTP_SERVER_EMAILING" size="18" value="' . $mainserver . '">';
			print '<input type="hidden" id="MAIN_MAIL_SMTP_SERVER_EMAILING_sav" name="MAIN_MAIL_SMTP_SERVER_EMAILING_sav" value="' . $mainserver . '">';
			print '<span id="smtp_server_mess" class="opacitymedium">' . $langs->trans("SeeLocalSendMailSetup") . '</span>';
			print ' <span class="opacitymedium smtp_method">' . $langs->trans("SeeLinkToOnlineDocumentation") . '</span>';
		} else {
			$text = !empty($mainserver) ? $mainserver : $smtpserver;
			$htmltext = $langs->trans("ContactSuperAdminForChange");
			print $form->textwithpicto($text, $htmltext, 1, 'superadmin');
			print '<input type="hidden" id="MAIN_MAIL_SMTP_SERVER_EMAILING" name="MAIN_MAIL_SMTP_SERVER_EMAILING" value="' . $mainserver . '">';
		}
		print '</td>';
	}
	print '</tr>';

	// Port

	print '<tr class="oddeven hideifdefault"><td>';
	if (!$conf->use_javascript_ajax && $linuxlike && $conf->global->MAIN_MAIL_SENDMODE_EMAILING == 'mail') {
		print $langs->trans("MAIN_MAIL_SMTP_PORT_NotAvailableOnLinuxLike");
		print '</td><td>';
		print '<span class="opacitymedium">'.$langs->trans("SeeLocalSendMailSetup").'</span>';
	} else {
		$mainport = (!empty($conf->global->MAIN_MAIL_SMTP_PORT_EMAILING) ? $conf->global->MAIN_MAIL_SMTP_PORT_EMAILING : '');
		$smtpport = ini_get('smtp_port') ?ini_get('smtp_port') : $langs->transnoentities("Undefined");
		if ($linuxlike) {
			print $langs->trans("MAIN_MAIL_SMTP_PORT_NotAvailableOnLinuxLike");
		} else {
			print $langs->trans("MAIN_MAIL_SMTP_PORT", $smtpport);
		}
		print '</td><td>';
		// SuperAdministrator access only
		if (!isModEnabled('multicompany') || ($user->admin && !$user->entity)) {
			print '<input class="flat" id="MAIN_MAIL_SMTP_PORT_EMAILING" name="MAIN_MAIL_SMTP_PORT_EMAILING" size="3" value="' . $mainport . '">';
			print '<input type="hidden" id="MAIN_MAIL_SMTP_PORT_EMAILING_sav" name="MAIN_MAIL_SMTP_PORT_EMAILING_sav" value="' . $mainport . '">';
			print '<span id="smtp_port_mess" class="opacitymedium">' . $langs->trans("SeeLocalSendMailSetup") . '</span>';
		} else {
			$text = (!empty($mainport) ? $mainport : $smtpport);
			$htmltext = $langs->trans("ContactSuperAdminForChange");
			print $form->textwithpicto($text, $htmltext, 1, 'superadmin');
			print '<input type="hidden" id="MAIN_MAIL_SMTP_PORT_EMAILING" name="MAIN_MAIL_SMTP_PORT_EMAILING" value="' . $mainport . '">';
		}
	}
	print '</td></tr>';

	// AUTH method
	if (!empty($conf->use_javascript_ajax) || (isset($conf->global->MAIN_MAIL_SENDMODE_EMAILING) && in_array($conf->global->MAIN_MAIL_SENDMODE_EMAILING, array('smtps', 'swiftmailer')))) {
		print '<tr class="oddeven smtp_auth_method hideifdefault"><td>'.$langs->trans("MAIN_MAIL_SMTPS_AUTH_TYPE").'</td><td>';
		if (!isModEnabled('multicompany') || ($user->admin && !$user->entity)) {
			// Note: Default value for MAIN_MAIL_SMTPS_AUTH_TYPE if not defined is 'LOGIN' (but login/pass may be empty and they won't be provided in such a case)
			print '<input type="radio" id="radio_pw" name="MAIN_MAIL_SMTPS_AUTH_TYPE_EMAILING" value="LOGIN"'.(getDolGlobalString('MAIN_MAIL_SMTPS_AUTH_TYPE_EMAILING', 'LOGIN') == 'LOGIN' ? ' checked' : '').'> ';
			print '<label for="radio_pw" >'.$langs->trans("UsePassword").'</label>';
			print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			print '<input type="radio" id="radio_oauth" name="MAIN_MAIL_SMTPS_AUTH_TYPE_EMAILING" value="XOAUTH2"'.(getDolGlobalString('MAIN_MAIL_SMTPS_AUTH_TYPE_EMAILING') == 'XOAUTH2' ? ' checked' : '').'> ';
			print '<label for="radio_oauth" >'.$form->textwithpicto($langs->trans("UseOauth"), $langs->trans("OauthNotAvailableForAllAndHadToBeCreatedBefore")).'</label>';
		} else {
			$value = getDolGlobalString('MAIN_MAIL_SMTPS_AUTH_TYPE_EMAILING', 'LOGIN');
			$htmltext = $langs->trans("ContactSuperAdminForChange");
			print $form->textwithpicto($langs->trans("MAIN_MAIL_SMTPS_AUTH_TYPE"), $htmltext, 1, 'superadmin');
			print '<input type="hidden" id="MAIN_MAIL_SMTPS_AUTH_TYPE" name="MAIN_MAIL_SMTPS_AUTH_TYPE_EMAILING" value="'.$value.'">';
		}
		print '</td></tr>';
	}

	// ID
	if (!empty($conf->use_javascript_ajax) || (isset($conf->global->MAIN_MAIL_SENDMODE_EMAILING) && in_array($conf->global->MAIN_MAIL_SENDMODE_EMAILING, array('smtps', 'swiftmailer')))) {
		$mainstmpid = (!empty($conf->global->MAIN_MAIL_SMTPS_ID_EMAILING) ? $conf->global->MAIN_MAIL_SMTPS_ID_EMAILING : '');
		print '<tr class="drag drop oddeven hideifdefault"><td>' . $langs->trans("MAIN_MAIL_SMTPS_ID") . '</td><td>';
		// SuperAdministrator access only
		if (!isModEnabled('multicompany') || ($user->admin && !$user->entity)) {
			print '<input class="flat" name="MAIN_MAIL_SMTPS_ID_EMAILING" size="32" value="' . $mainstmpid . '">';
		} else {
			$htmltext = $langs->trans("ContactSuperAdminForChange");
			print $form->textwithpicto($conf->global->MAIN_MAIL_SMTPS_ID_EMAILING, $htmltext, 1, 'superadmin');
			print '<input type="hidden" name="MAIN_MAIL_SMTPS_ID_EMAILING" value="' . $mainstmpid . '">';
		}
		print '</td></tr>';
	}

	// PW
	if (!empty($conf->use_javascript_ajax) || (isset($conf->global->MAIN_MAIL_SENDMODE_EMAILING) && in_array($conf->global->MAIN_MAIL_SENDMODE_EMAILING, array('smtps', 'swiftmailer')))) {
		$mainsmtppw = (!empty($conf->global->MAIN_MAIL_SMTPS_PW_EMAILING) ? $conf->global->MAIN_MAIL_SMTPS_PW_EMAILING : '');
		print '<tr class="drag drop oddeven smtp_pw hideifdefault"><td>' . $langs->trans("MAIN_MAIL_SMTPS_PW") . '</td><td>';
		// SuperAdministrator access only
		if (!isModEnabled('multicompany') || ($user->admin && !$user->entity)) {
			print '<input class="flat" type="password" name="MAIN_MAIL_SMTPS_PW_EMAILING" size="32" value="' . $mainsmtppw . '">';
		} else {
			$htmltext = $langs->trans("ContactSuperAdminForChange");
			print $form->textwithpicto($conf->global->MAIN_MAIL_SMTPS_PW_EMAILING, $htmltext, 1, 'superadmin');
			print '<input type="hidden" name="MAIN_MAIL_SMTPS_PW_EMAILING" value="' . $mainsmtppw . '">';
		}
		print '</td></tr>';
	}

	// OAUTH service provider
	if (!empty($conf->use_javascript_ajax) || (isset($conf->global->MAIN_MAIL_SENDMODE_EMAILING) && in_array($conf->global->MAIN_MAIL_SENDMODE_EMAILING, array('smtps', 'swiftmailer')))) {
		print '<tr class="oddeven smtp_oauth_service hideifdefault"><td>'.$langs->trans("MAIN_MAIL_SMTPS_OAUTH_SERVICE").'</td><td>';

		// SuperAdministrator access only
		if ((empty($conf->global->MAIN_MODULE_MULTICOMPANY)) || ($user->admin && !$user->entity)) {
			print $form->selectarray('MAIN_MAIL_SMTPS_OAUTH_SERVICE_EMAILING', $oauthservices, $conf->global->MAIN_MAIL_SMTPS_OAUTH_SERVICE_EMAILING);
		} else {
			$text = $oauthservices[$conf->global->MAIN_MAIL_SMTPS_OAUTH_SERVICE_EMAILING];
			if (empty($text)) {
				$text = $langs->trans("Undefined");
			}
			$htmltext = $langs->trans("ContactSuperAdminForChange");
			print $form->textwithpicto($text, $htmltext, 1, 'superadmin');
			print '<input type="hidden" name="MAIN_MAIL_SMTPS_OAUTH_SERVICE_EMAILING" value="'.$conf->global->MAIN_MAIL_SMTPS_OAUTH_SERVICE_EMAILING.'">';
		}
		print '</td></tr>';
	}


	// TLS
	print '<tr class="oddeven hideifdefault"><td>'.$langs->trans("MAIN_MAIL_EMAIL_TLS").'</td><td>';
	if (!empty($conf->use_javascript_ajax) || (isset($conf->global->MAIN_MAIL_SENDMODE_EMAILING) && in_array($conf->global->MAIN_MAIL_SENDMODE_EMAILING, array('smtps', 'swiftmailer')))) {
		if (function_exists('openssl_open')) {
			print $form->selectyesno('MAIN_MAIL_EMAIL_TLS_EMAILING', (!empty($conf->global->MAIN_MAIL_EMAIL_TLS_EMAILING) ? $conf->global->MAIN_MAIL_EMAIL_TLS_EMAILING : 0), 1);
		} else {
			print yn(0).' ('.$langs->trans("YourPHPDoesNotHaveSSLSupport").')';
		}
	} else {
		print yn(0).' ('.$langs->trans("NotSupported").')';
	}
	print '</td></tr>';

	// STARTTLS
	print '<tr class="oddeven hideifdefault"><td>'.$langs->trans("MAIN_MAIL_EMAIL_STARTTLS").'</td><td>';
	if (!empty($conf->use_javascript_ajax) || (isset($conf->global->MAIN_MAIL_SENDMODE_EMAILING) && in_array($conf->global->MAIN_MAIL_SENDMODE_EMAILING, array('smtps', 'swiftmailer')))) {
		if (function_exists('openssl_open')) {
			print $form->selectyesno('MAIN_MAIL_EMAIL_STARTTLS_EMAILING', (!empty($conf->global->MAIN_MAIL_EMAIL_STARTTLS_EMAILING) ? $conf->global->MAIN_MAIL_EMAIL_STARTTLS_EMAILING : 0), 1);
		} else {
			print yn(0).' ('.$langs->trans("YourPHPDoesNotHaveSSLSupport").')';
		}
	} else {
		print yn(0).' ('.$langs->trans("NotSupported").')';
	}
	print '</td></tr>';

	// SMTP_ALLOW_SELF_SIGNED_EMAILING
	print '<tr class="oddeven hideifdefault"><td>'.$langs->trans("MAIN_MAIL_EMAIL_SMTP_ALLOW_SELF_SIGNED").'</td><td>';
	if (!empty($conf->use_javascript_ajax) || (isset($conf->global->MAIN_MAIL_SENDMODE_EMAILING) && in_array($conf->global->MAIN_MAIL_SENDMODE_EMAILING, array('smtps', 'swiftmailer')))) {
		if (function_exists('openssl_open')) {
			print $form->selectyesno('MAIN_MAIL_EMAIL_SMTP_ALLOW_SELF_SIGNED_EMAILING', (!empty($conf->global->MAIN_MAIL_EMAIL_SMTP_ALLOW_SELF_SIGNED_EMAILING) ? $conf->global->MAIN_MAIL_EMAIL_SMTP_ALLOW_SELF_SIGNED_EMAILING : 0), 1);
		} else {
			print yn(0).' ('.$langs->trans("YourPHPDoesNotHaveSSLSupport").')';
		}
	} else {
		print yn(0).' ('.$langs->trans("NotSupported").')';
	}
	print '</td></tr>';

	print '</table>';

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel();

	print '</form>';
} else {
	print dol_get_fiche_head($head, 'common_emailing', '', -1);

	print '<span class="opacitymedium">'.$langs->trans("EMailsDesc")."</span><br>\n";
	print "<br><br>\n";

	print '<div class="div-table-responsive-no-min">'; // You can use div-table-responsive-no-min if you dont need reserved height for your table
	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre"><td class="titlefieldmiddle">'.$langs->trans("Parameter").'</td><td>'.$langs->trans("Value").'</td></tr>';

	// Method
	print '<tr class="oddeven"><td>'.$langs->trans("MAIN_MAIL_SENDMODE").'</td><td>';
	$text = $listofmethods[getDolGlobalString('MAIN_MAIL_SENDMODE_EMAILING')];
	if (empty($text)) {
		$text = $langs->trans("Undefined").img_warning();
	}
	if (getDolGlobalString('MAIN_MAIL_SENDMODE_EMAILING') == 'default') {
		print '<span class="opacitymedium">'.$text.'</span>';
	} else {
		print $text;
	}
	print '</td></tr>';

	if (!empty($conf->global->MAIN_MAIL_SENDMODE_EMAILING) && $conf->global->MAIN_MAIL_SENDMODE_EMAILING != 'default') {
		// Host server
		if ($linuxlike && (isset($conf->global->MAIN_MAIL_SENDMODE_EMAILING) && $conf->global->MAIN_MAIL_SENDMODE_EMAILING == 'mail')) {
			print '<tr class="oddeven hideifdefault"><td>'.$langs->trans("MAIN_MAIL_SMTP_SERVER_NotAvailableOnLinuxLike").'</td><td>'.$langs->trans("SeeLocalSendMailSetup").'</td></tr>';
		} else {
			print '<tr class="oddeven hideifdefault"><td>'.$langs->trans("MAIN_MAIL_SMTP_SERVER", ini_get('SMTP') ?ini_get('SMTP') : $langs->transnoentities("Undefined")).'</td><td>'.(!empty($conf->global->MAIN_MAIL_SMTP_SERVER_EMAILING) ? $conf->global->MAIN_MAIL_SMTP_SERVER_EMAILING : '').'</td></tr>';
		}

		// Port
		if ($linuxlike && (isset($conf->global->MAIN_MAIL_SENDMODE_EMAILING) && $conf->global->MAIN_MAIL_SENDMODE_EMAILING == 'mail')) {
			print '<tr class="oddeven hideifdefault"><td>'.$langs->trans("MAIN_MAIL_SMTP_PORT_NotAvailableOnLinuxLike").'</td><td>'.$langs->trans("SeeLocalSendMailSetup").'</td></tr>';
		} else {
			print '<tr class="oddeven hideifdefault"><td>'.$langs->trans("MAIN_MAIL_SMTP_PORT", ini_get('smtp_port') ?ini_get('smtp_port') : $langs->transnoentities("Undefined")).'</td><td>'.(!empty($conf->global->MAIN_MAIL_SMTP_PORT_EMAILING) ? $conf->global->MAIN_MAIL_SMTP_PORT_EMAILING : '').'</td></tr>';
		}

		// AUTH method
		if (in_array(getDolGlobalString('MAIN_MAIL_SENDMODE_EMAILING'), array('smtps', 'swiftmailer'))) {
			$authtype = getDolGlobalString('MAIN_MAIL_SMTPS_AUTH_TYPE_EMAILING', 'LOGIN');
			$text = ($authtype === "LOGIN") ? $langs->trans("UsePassword") : ($authtype === "XOAUTH2" ?  $langs->trans("UseOauth") : '') ;
			print '<tr class="oddeven hideifdefault"><td>'.$langs->trans("MAIN_MAIL_SMTPS_AUTH_TYPE").'</td><td>'.$text.'</td></tr>';
		}

		// SMTPS ID
		if (isset($conf->global->MAIN_MAIL_SENDMODE_EMAILING) && in_array($conf->global->MAIN_MAIL_SENDMODE_EMAILING, array('smtps', 'swiftmailer'))) {
			print '<tr class="oddeven hideifdefault"><td>'.$langs->trans("MAIN_MAIL_SMTPS_ID").'</td><td>'.getDolGlobalString('MAIN_MAIL_SMTPS_ID_EMAILING').'</td></tr>';
		}

		// SMTPS PW
		if (isset($conf->global->MAIN_MAIL_SENDMODE_EMAILING) && in_array($conf->global->MAIN_MAIL_SENDMODE_EMAILING, array('smtps', 'swiftmailer')) && getDolGlobalString('MAIN_MAIL_SMTPS_AUTH_TYPE_EMAILING') != "XOAUTH2") {
			print '<tr class="oddeven hideifdefault"><td>'.$langs->trans("MAIN_MAIL_SMTPS_PW").'</td><td>'.preg_replace('/./', '*', getDolGlobalString('MAIN_MAIL_SMTPS_PW_EMAILING')).'</td></tr>';
		}

		// SMTPS oauth service
		if (in_array(getDolGlobalString('MAIN_MAIL_SENDMODE_EMAILING'), array('smtps', 'swiftmailer')) && getDolGlobalString('MAIN_MAIL_SMTPS_AUTH_TYPE_EMAILING') === "XOAUTH2") {
			$text = $oauthservices[$conf->global->MAIN_MAIL_SMTPS_OAUTH_SERVICE_EMAILING];
			if (empty($text)) {
				$text = $langs->trans("Undefined").img_warning();
			}
			print '<tr class="oddeven hideifdefault"><td>'.$langs->trans("MAIN_MAIL_SMTPS_OAUTH_SERVICE_EMAILING").'</td><td>'.$text.'</td></tr>';
		}


		// TLS
		print '<tr class="oddeven hideifdefault"><td>'.$langs->trans("MAIN_MAIL_EMAIL_TLS").'</td><td>';
		if (isset($conf->global->MAIN_MAIL_SENDMODE_EMAILING) && in_array($conf->global->MAIN_MAIL_SENDMODE_EMAILING, array('smtps', 'swiftmailer'))) {
			if (function_exists('openssl_open')) {
				print yn($conf->global->MAIN_MAIL_EMAIL_TLS_EMAILING);
			} else {
				print yn(0).' ('.$langs->trans("YourPHPDoesNotHaveSSLSupport").')';
			}
		} else {
			print '<span class="opacitymedium">'.yn(0).' ('.$langs->trans("NotSupported").')</span>';
		}
		print '</td></tr>';

		// STARTTLS
		print '<tr class="oddeven hideifdefault"><td>'.$langs->trans("MAIN_MAIL_EMAIL_STARTTLS").'</td><td>';
		if (isset($conf->global->MAIN_MAIL_SENDMODE_EMAILING) && in_array($conf->global->MAIN_MAIL_SENDMODE_EMAILING, array('smtps', 'swiftmailer'))) {
			if (function_exists('openssl_open')) {
				print yn($conf->global->MAIN_MAIL_EMAIL_STARTTLS_EMAILING);
			} else {
				print yn(0).' ('.$langs->trans("YourPHPDoesNotHaveSSLSupport").')';
			}
		} else {
			print yn(0).' ('.$langs->trans("NotSupported").')';
		}
		print '</td></tr>';

		// SMTP_ALLOW_SELF_SIGNED_EMAILING
		print '<tr class="oddeven hideifdefault"><td>'.$langs->trans("MAIN_MAIL_EMAIL_SMTP_ALLOW_SELF_SIGNED").'</td><td>';
		if (isset($conf->global->MAIN_MAIL_SENDMODE_EMAILING) && in_array($conf->global->MAIN_MAIL_SENDMODE_EMAILING, array('smtps', 'swiftmailer'))) {
			if (function_exists('openssl_open')) {
				print yn(getDolGlobalInt('MAIN_MAIL_EMAIL_SMTP_ALLOW_SELF_SIGNED_EMAILING'));
			} else {
				print yn(0).' ('.$langs->trans("YourPHPDoesNotHaveSSLSupport").')';
			}
		} else {
			print yn(0).' ('.$langs->trans("NotSupported").')';
		}
		print '</td></tr>';
	}

	print '</table>';
	print '</div>';

	print dol_get_fiche_end();


	if ($conf->global->MAIN_MAIL_SENDMODE_EMAILING == 'mail' && empty($conf->global->MAIN_FIX_FOR_BUGGED_MTA)) {
		print '<br>';
		/*
		// Warning 1
		if ($linuxlike) {
			$sendmailoption=ini_get('mail.force_extra_parameters');
			if (empty($sendmailoption) || ! preg_match('/ba/',$sendmailoption)) {
				print info_admin($langs->trans("SendmailOptionNotComplete"));
			}
		}*/
		// Warning 2
		print info_admin($langs->trans("SendmailOptionMayHurtBuggedMTA"));
	}


	// Buttons for actions

	print '<div class="tabsAction">';

	print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=edit&token='.newToken().'">'.$langs->trans("Modify").'</a>';

	if (!empty($conf->global->MAIN_MAIL_SENDMODE_EMAILING) && $conf->global->MAIN_MAIL_SENDMODE_EMAILING != 'default') {
		if ($conf->global->MAIN_MAIL_SENDMODE_EMAILING != 'mail' || !$linuxlike) {
			if (function_exists('fsockopen') && $port && $server) {
				print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?action=testconnect">' . $langs->trans("DoTestServerAvailability") . '</a>';
			}
		} else {
			print '<a class="butActionRefused classfortooltip" href="#" title="' . $langs->trans("FeatureNotAvailableOnLinux") . '">' . $langs->trans("DoTestServerAvailability") . '</a>';
		}

		print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?action=test&amp;mode=init">' . $langs->trans("DoTestSend") . '</a>';

		if (isModEnabled('fckeditor')) {
			print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?action=testhtml&amp;mode=init">' . $langs->trans("DoTestSendHTML") . '</a>';
		}
	}

	print '</div>';


	if ($conf->global->MAIN_MAIL_SENDMODE_EMAILING == 'mail' && !in_array($action, array('testconnect', 'test', 'testhtml'))) {
		$text = $langs->trans("WarningPHPMail");
		print info_admin($text);
	}

	// Run the test to connect
	if ($action == 'testconnect') {
		print '<div id="formmailaftertstconnect" name="formmailaftertstconnect"></div>';
		print load_fiche_titre($langs->trans("DoTestServerAvailability"));

		include_once DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php';
		$mail = new CMailFile('', '', '', '', array(), array(), array(), '', '', 0, '', '', '', '', $trackid, $sendcontext);

		$result = $mail->check_server_port($server, $port);
		if ($result) {
			print '<div class="ok">'.$langs->trans("ServerAvailableOnIPOrPort", $server, $port).'</div>';
		} else {
			$errormsg = $langs->trans("ServerNotAvailableOnIPOrPort", $server, $port);

			if ($mail->error) {
				$errormsg .= ' - '.$mail->error;
			}

			setEventMessages($errormsg, null, 'errors');
		}
		print '<br>';
	}

	// Show email send test form
	if ($action == 'test' || $action == 'testhtml') {
		print '<div id="formmailbeforetitle" name="formmailbeforetitle"></div>';
		print load_fiche_titre($action == 'testhtml' ? $langs->trans("DoTestSendHTML") : $langs->trans("DoTestSend"));

		print dol_get_fiche_head('');

		// Cree l'objet formulaire mail
		include_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
		$formmail = new FormMail($db);
		$formmail->trackid = (($action == 'testhtml') ? "testhtml" : "test");
		$formmail->fromname = (GETPOSTISSET('fromname') ? GETPOST('fromname', 'restricthtml') : $conf->global->MAIN_MAIL_EMAIL_FROM);
		$formmail->frommail = (GETPOSTISSET('frommail') ? GETPOST('frommail', 'restricthtml') : $conf->global->MAIN_MAIL_EMAIL_FROM);
		$formmail->fromid = $user->id;
		$formmail->fromalsorobot = 1;
		$formmail->withfromreadonly = 0;
		$formmail->withsubstit = 0;
		$formmail->withfrom = 1;
		$formmail->witherrorsto = 1;
		$formmail->withto = (GETPOSTISSET('sendto') ? GETPOST('sendto', 'restricthtml') : ($user->email ? $user->email : 1));
		$formmail->withtocc = (GETPOSTISSET('sendtocc') ? GETPOST('sendtocc', 'restricthtml') : 1); // ! empty to keep field if empty
		$formmail->withtoccc = (GETPOSTISSET('sendtoccc') ? GETPOST('sendtoccc', 'restricthtml') : 1); // ! empty to keep field if empty
		$formmail->withtopic = (GETPOSTISSET('subject') ? GETPOST('subject') : $langs->trans("Test"));
		$formmail->withtopicreadonly = 0;
		$formmail->withfile = 2;
		$formmail->withbody = (GETPOSTISSET('message') ? GETPOST('message', 'restricthtml') : ($action == 'testhtml' ? $langs->transnoentities("PredefinedMailTestHtml") : $langs->transnoentities("PredefinedMailTest")));
		$formmail->withbodyreadonly = 0;
		$formmail->withcancel = 1;
		$formmail->withdeliveryreceipt = 1;
		$formmail->withfckeditor = ($action == 'testhtml' ? 1 : 0);
		$formmail->ckeditortoolbar = 'powererp_mailings';
		// Tableau des substitutions
		$formmail->substit = $substitutionarrayfortest;
		// Tableau des parametres complementaires du post
		$formmail->param["action"] = "send";
		$formmail->param["models"] = "body";
		$formmail->param["mailid"] = 0;
		$formmail->param["returnurl"] = $_SERVER["PHP_SELF"];

		// Init list of files
		if (GETPOST("mode", "aZ09") == 'init') {
			$formmail->clear_attached_files();
		}

		print $formmail->get_form('addfile', 'removefile');

		print dol_get_fiche_end();
	}
}

// End of page
llxFooter();
$db->close();
