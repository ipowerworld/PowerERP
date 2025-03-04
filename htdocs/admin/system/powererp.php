<?php
/* Copyright (C) 2005-2020	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2007		Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2007-2012	Regis Houssin			<regis.houssin@inodbox.com>
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
 *  \file       htdocs/admin/system/powererp.php
 *  \brief      Page to show PowerERP information
 */

// Load PowerERP environment
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/memory.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/geturl.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

// Load translation files required by the page
$langs->loadLangs(array("install", "other", "admin"));

$action = GETPOST('action', 'aZ09');

if (!$user->admin) {
	accessforbidden();
}

$sfurl = '';
$version = '0.0';



/*
 *	Actions
 */

if ($action == 'getlastversion') {
	$result = getURLContent('https://sourceforge.net/projects/powererp/rss');
	//var_dump($result['content']);
	if (function_exists('simplexml_load_string')) {
		$sfurl = simplexml_load_string($result['content'], 'SimpleXMLElement', LIBXML_NOCDATA|LIBXML_NONET);
	} else {
		setEventMessages($langs->trans("ErrorPHPDoesNotSupport", "xml"), null, 'errors');
	}
}


/*
 * View
 */

$form = new Form($db);

$help_url = '';
$title = $langs->trans("InfoPowerERP");

llxHeader('', $title, $help_url);

print load_fiche_titre($title, '', 'title_setup');

// Version
print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><td class="titlefieldcreate">'.$langs->trans("Version").'</td><td>'.$langs->trans("Value").'</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("CurrentVersion").' ('.$langs->trans("Programs").')</td><td>'.DOL_VERSION;
// If current version differs from last upgrade
if (empty($conf->global->MAIN_VERSION_LAST_UPGRADE)) {
	// Compare version with last install database version (upgrades never occured)
	if (DOL_VERSION != $conf->global->MAIN_VERSION_LAST_INSTALL) {
		print ' '.img_warning($langs->trans("RunningUpdateProcessMayBeRequired", DOL_VERSION, $conf->global->MAIN_VERSION_LAST_INSTALL));
	}
} else {
	// Compare version with last upgrade database version
	if (DOL_VERSION != $conf->global->MAIN_VERSION_LAST_UPGRADE) {
		print ' '.img_warning($langs->trans("RunningUpdateProcessMayBeRequired", DOL_VERSION, $conf->global->MAIN_VERSION_LAST_UPGRADE));
	}
}

$version = DOL_VERSION;
if (preg_match('/[a-z]+/i', $version)) {
	$version = 'develop'; // If version contains text, it is not an official tagged version, so we use the full change log.
}
print ' &nbsp; <a href="https://raw.githubusercontent.com/PowerERP/powererp/'.$version.'/ChangeLog" target="_blank" rel="noopener noreferrer external">'.$langs->trans("SeeChangeLog").'</a>';

$newversion = '';
if (function_exists('curl_init')) {
	$conf->global->MAIN_USE_RESPONSE_TIMEOUT = 10;
	print ' &nbsp; &nbsp; - &nbsp; &nbsp; ';
	if ($action == 'getlastversion') {
		if ($sfurl) {
			$i = 0;
			while (!empty($sfurl->channel[0]->item[$i]->title) && $i < 10000) {
				$title = $sfurl->channel[0]->item[$i]->title;
				$reg = array();
				if (preg_match('/([0-9]+\.([0-9\.]+))/', $title, $reg)) {
					$newversion = $reg[1];
					$newversionarray = explode('.', $newversion);
					$versionarray = explode('.', $version);
					//var_dump($newversionarray);var_dump($versionarray);
					if (versioncompare($newversionarray, $versionarray) > 0) {
						$version = $newversion;
					}
				}
				$i++;
			}

			// Show version
			print $langs->trans("LastStableVersion").' : <b>'.(($version != '0.0') ? $version : $langs->trans("Unknown")).'</b>';
			if ($version != '0.0') {
				print ' &nbsp; <a href="https://raw.githubusercontent.com/PowerERP/powererp/'.$version.'/ChangeLog" target="_blank" rel="noopener noreferrer external">'.$langs->trans("SeeChangeLog").'</a>';
			}
		} else {
			print $langs->trans("LastStableVersion").' : <b>'.$langs->trans("UpdateServerOffline").'</b>';
		}
	} else {
		print $langs->trans("LastStableVersion").' : <a href="'.$_SERVER["PHP_SELF"].'?action=getlastversion" class="butAction smallpaddingimp">'.$langs->trans("Check").'</a>';
	}
}

// Now show link to the changelog
//print ' &nbsp; &nbsp; - &nbsp; &nbsp; ';

$version = DOL_VERSION;
if (preg_match('/[a-z]+/i', $version)) {
	$version = 'develop'; // If version contains text, it is not an official tagged version, so we use the full change log.
}

print '</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("VersionLastUpgrade").' ('.$langs->trans("Database").')</td><td>'.getDolGlobalString('MAIN_VERSION_LAST_UPGRADE').'</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("VersionLastInstall").'</td><td>'.getDolGlobalString('MAIN_VERSION_LAST_INSTALL').'</td></tr>'."\n";
print '</table>';
print '</div>';
print '<br>';

// Session
print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><td class="titlefieldcreate">'.$langs->trans("Session").'</td><td>'.$langs->trans("Value").'</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("SessionSavePath").'</td><td>'.session_save_path().'</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("SessionName").'</td><td>'.session_name().'</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("SessionId").'</td><td>'.session_id().'</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("CurrentSessionTimeOut").' (session.gc_maxlifetime)</td>';
print '<td>';
print ini_get('session.gc_maxlifetime').' '.$langs->trans("seconds");
print '<!-- session.gc_maxlifetime = '.ini_get("session.gc_maxlifetime").' -->'."\n";
print '<!-- session.gc_probability = '.ini_get("session.gc_probability").' -->'."\n";
print '<!-- session.gc_divisor = '.ini_get("session.gc_divisor").' -->'."\n";
print $form->textwithpicto('', $langs->trans("SessionExplanation", ini_get("session.gc_probability"), ini_get("session.gc_divisor")));
print "</td></tr>\n";
print '<tr class="oddeven"><td>'.$langs->trans("CurrentTheme").'</td><td>'.$conf->theme.'</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("CurrentMenuHandler").'</td><td>';
print $conf->standard_menu;
print '</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("Screen").'</td><td>';
print $_SESSION['dol_screenwidth'].' x '.$_SESSION['dol_screenheight'];
print '</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("Session").'</td><td class="wordbreak">';
$i = 0;
foreach ($_SESSION as $key => $val) {
	if ($i > 0) {
		print ', ';
	}
	if (is_array($val)) {
		print $key.' => array(...)';
	} else {
		print $key.' => '.dol_escape_htmltag($val);
	}
	$i++;
}
print '</td></tr>'."\n";
print '</table>';
print '</div>';
print '<br>';


// Shmop
if (isset($conf->global->MAIN_OPTIMIZE_SPEED) && ($conf->global->MAIN_OPTIMIZE_SPEED & 0x02)) {
	$shmoparray = dol_listshmop();

	print '<div class="div-table-responsive-no-min">';
	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre">';
	print '<td class="titlefieldcreate">'.$langs->trans("LanguageFilesCachedIntoShmopSharedMemory").'</td>';
	print '<td>'.$langs->trans("NbOfEntries").'</td>';
	print '<td class="right">'.$langs->trans("Address").'</td>';
	print '</tr>'."\n";

	foreach ($shmoparray as $key => $val) {
		print '<tr class="oddeven"><td>'.$key.'</td>';
		print '<td>'.count($val).'</td>';
		print '<td class="right">'.dol_getshmopaddress($key).'</td>';
		print '</tr>'."\n";
	}

	print '</table>';
	print '</div>';
	print '<br>';
}


// Localisation
print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><td class="titlefieldcreate">'.$langs->trans("LocalisationPowerERPParameters").'</td><td>'.$langs->trans("Value").'</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("LanguageBrowserParameter", "HTTP_ACCEPT_LANGUAGE").'</td><td>'.$_SERVER["HTTP_ACCEPT_LANGUAGE"].'</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("CurrentUserLanguage").'</td><td>'.$langs->getDefaultLang().'</td></tr>'."\n";
// Thousands
$thousand = $langs->transnoentitiesnoconv("SeparatorThousand");
if ($thousand == 'SeparatorThousand') {
	$thousand = ' '; // ' ' does not work on trans method
}
if ($thousand == 'None') {
	$thousand = '';
}
print '<tr class="oddeven"><td>'.$langs->trans("CurrentValueSeparatorThousand").'</td><td>'.($thousand == ' ' ? $langs->transnoentitiesnoconv("Space") : $thousand).'</td></tr>'."\n";
// Decimals
$dec = $langs->transnoentitiesnoconv("SeparatorDecimal");
print '<tr class="oddeven"><td>'.$langs->trans("CurrentValueSeparatorDecimal").'</td><td>'.$dec.'</td></tr>'."\n";
// Show results of functions to see if everything works
print '<tr class="oddeven"><td>&nbsp; => price2num(1233.56+1)</td><td>'.price2num(1233.56 + 1, '2').'</td></tr>'."\n";
print '<tr class="oddeven"><td>&nbsp; => price2num('."'1".$thousand."234".$dec."56')</td><td>".price2num("1".$thousand."234".$dec."56", '2')."</td></tr>\n";
if (($thousand != ',' && $thousand != '.') || ($thousand != ' ')) {
	print '<tr class="oddeven"><td>&nbsp; => price2num('."'1 234.56')</td><td>".price2num("1 234.56", '2')."</td>";
	print "</tr>\n";
}
print '<tr class="oddeven"><td>&nbsp; => price(1234.56)</td><td>'.price(1234.56).'</td></tr>'."\n";

// Timezones

// Database timezone
if ($conf->db->type == 'mysql' || $conf->db->type == 'mysqli') {
	print '<tr class="oddeven"><td>'.$langs->trans("MySQLTimeZone").' (database)</td><td>'; // Timezone server base
	$sql = "SHOW VARIABLES where variable_name = 'system_time_zone'";
	$resql = $db->query($sql);
	if ($resql) {
		$obj = $db->fetch_object($resql);
		print $form->textwithtooltip($obj->Value, $langs->trans('TZHasNoEffect'), 2, 1, img_info(''));
	}
	print '</td></tr>'."\n";
}
$txt = $langs->trans("OSTZ").' (variable system TZ): '.(!empty($_ENV["TZ"]) ? $_ENV["TZ"] : $langs->trans("NotDefined")).'<br>'."\n";
$txt .= $langs->trans("PHPTZ").' (date_default_timezone_get() / php.ini date.timezone): '.(getServerTimeZoneString()." / ".(ini_get("date.timezone") ? ini_get("date.timezone") : $langs->trans("NotDefined")))."<br>\n"; // date.timezone must be in valued defined in http://fr3.php.net/manual/en/timezones.europe.php
$txt .= $langs->trans("PowerERP constant MAIN_SERVER_TZ").': '.(empty($conf->global->MAIN_SERVER_TZ) ? $langs->trans("NotDefined") : $conf->global->MAIN_SERVER_TZ);
print '<tr class="oddeven"><td>'.$langs->trans("CurrentTimeZone").'</td><td>'; // Timezone server PHP
$a = getServerTimeZoneInt('now');
$b = getServerTimeZoneInt('winter');
$c = getServerTimeZoneInt('summer');
$daylight = round($c - $b);
//print $a." ".$b." ".$c." ".$daylight;
$val = ($a >= 0 ? '+' : '').$a;
$val .= ' ('.($a == 'unknown' ? 'unknown' : ($a >= 0 ? '+' : '').($a * 3600)).')';
$val .= ' &nbsp; &nbsp; &nbsp; '.getServerTimeZoneString();
$val .= ' &nbsp; &nbsp; &nbsp; '.$langs->trans("DaylingSavingTime").': '.(is_null($daylight) ? 'unknown' : ($a == $c ?yn($daylight) : yn(0).($daylight ? '  &nbsp; &nbsp; ('.$langs->trans('YesInSummer').')' : '')));
print $form->textwithtooltip($val, $txt, 2, 1, img_info(''));
print '</td></tr>'."\n"; // value defined in http://fr3.php.net/manual/en/timezones.europe.php
print '<tr class="oddeven"><td>&nbsp; => '.$langs->trans("CurrentHour").'</td><td>'.dol_print_date(dol_now('gmt'), 'dayhour', 'tzserver').'</td></tr>'."\n";
print '<tr class="oddeven"><td>&nbsp; => dol_print_date(0,"dayhourtext")</td><td>'.dol_print_date(0, "dayhourtext").'</td>';
print '<tr class="oddeven"><td>&nbsp; => dol_get_first_day(1970,1,false)</td><td>'.dol_get_first_day(1970, 1, false).' &nbsp; &nbsp; (=> dol_print_date() or idate() of this value = '.dol_print_date(dol_get_first_day(1970, 1, false), 'dayhour').')</td>';
print '<tr class="oddeven"><td>&nbsp; => dol_get_first_day(1970,1,true)</td><td>'.dol_get_first_day(1970, 1, true).' &nbsp; &nbsp; (=> dol_print_date() or idate() of this value = '.dol_print_date(dol_get_first_day(1970, 1, true), 'dayhour').')</td>';
// Client
$tz = (int) $_SESSION['dol_tz'] + (int) $_SESSION['dol_dst'];
print '<tr class="oddeven"><td>'.$langs->trans("ClientTZ").'</td><td>'.($tz ? ($tz >= 0 ? '+' : '').$tz : '').' ('.($tz >= 0 ? '+' : '').($tz * 60 * 60).')';
print ' &nbsp; &nbsp; &nbsp; '.$_SESSION['dol_tz_string'];
print ' &nbsp; &nbsp; &nbsp; '.$langs->trans("DaylingSavingTime").': ';
if ($_SESSION['dol_dst'] > 0) {
	print yn(1);
} else {
	print yn(0);
}
if (!empty($_SESSION['dol_dst_first'])) {
	print ' &nbsp; &nbsp; ('.dol_print_date(dol_stringtotime($_SESSION['dol_dst_first']), 'dayhour', 'gmt').' - '.dol_print_date(dol_stringtotime($_SESSION['dol_dst_second']), 'dayhour', 'gmt').')';
}
print '</td></tr>'."\n";
print '</td></tr>'."\n";
print '<tr class="oddeven"><td>&nbsp; => '.$langs->trans("ClientHour").'</td><td>'.dol_print_date(dol_now('gmt'), 'dayhour', 'tzuser').'</td></tr>'."\n";

$filesystemencoding = ini_get("unicode.filesystem_encoding"); // Disponible avec PHP 6.0
print '<tr class="oddeven"><td>'.$langs->trans("File encoding").' (php.ini unicode.filesystem_encoding)</td><td>'.$filesystemencoding.'</td></tr>'."\n";

$tmp = ini_get("unicode.filesystem_encoding"); // Disponible avec PHP 6.0
if (empty($tmp) && !empty($_SERVER["WINDIR"])) {
	$tmp = 'iso-8859-1'; // By default for windows
}
if (empty($tmp)) {
	$tmp = 'utf-8'; // By default for other
}
if (!empty($conf->global->MAIN_FILESYSTEM_ENCODING)) {
	$tmp = $conf->global->MAIN_FILESYSTEM_ENCODING;
}
print '<tr class="oddeven"><td>&nbsp; => '.$langs->trans("File encoding").'</td><td>'.$tmp.'</td></tr>'."\n"; // date.timezone must be in valued defined in http://fr3.php.net/manual/en/timezones.europe.php

print '</table>';
print '</div>';
print '<br>';



// Parameters in conf.php file (when a parameter start with ?, it is shown only if defined)
$configfileparameters = array(
	'powererp_main_prod' => 'Production mode (Hide all error messages)',
	'powererp_main_instance_unique_id' => $langs->trans("InstanceUniqueID"),
	'separator0' => '',
	'powererp_main_url_root' => $langs->trans("URLRoot"),
	'?powererp_main_url_root_alt' => $langs->trans("URLRoot").' (alt)',
	'powererp_main_document_root'=> $langs->trans("DocumentRootServer"),
	'?powererp_main_document_root_alt' => $langs->trans("DocumentRootServer").' (alt)',
	'powererp_main_data_root' => $langs->trans("DataRootServer"),
	'separator1' => '',
	'powererp_main_db_host' => $langs->trans("DatabaseServer"),
	'powererp_main_db_port' => $langs->trans("DatabasePort"),
	'powererp_main_db_name' => $langs->trans("DatabaseName"),
	'powererp_main_db_type' => $langs->trans("DriverType"),
	'powererp_main_db_user' => $langs->trans("DatabaseUser"),
	'powererp_main_db_pass' => $langs->trans("DatabasePassword"),
	'powererp_main_db_character_set' => $langs->trans("DBStoringCharset"),
	'powererp_main_db_collation' => $langs->trans("DBSortingCollation"),
	'?powererp_main_db_prefix' => $langs->trans("DatabasePrefix"),
	'powererp_main_db_readonly' => $langs->trans("ReadOnlyMode"),
	'separator2' => '',
	'powererp_main_authentication' => $langs->trans("AuthenticationMode"),
	'?multicompany_transverse_mode'=>  $langs->trans("MultiCompanyMode"),
	'separator'=> '',
	'?powererp_main_auth_ldap_login_attribute' => 'powererp_main_auth_ldap_login_attribute',
	'?powererp_main_auth_ldap_host' => 'powererp_main_auth_ldap_host',
	'?powererp_main_auth_ldap_port' => 'powererp_main_auth_ldap_port',
	'?powererp_main_auth_ldap_version' => 'powererp_main_auth_ldap_version',
	'?powererp_main_auth_ldap_dn' => 'powererp_main_auth_ldap_dn',
	'?powererp_main_auth_ldap_admin_login' => 'powererp_main_auth_ldap_admin_login',
	'?powererp_main_auth_ldap_admin_pass' => 'powererp_main_auth_ldap_admin_pass',
	'?powererp_main_auth_ldap_debug' => 'powererp_main_auth_ldap_debug',
	'separator3' => '',
	'?powererp_lib_FPDF_PATH' => 'powererp_lib_FPDF_PATH',
	'?powererp_lib_TCPDF_PATH' => 'powererp_lib_TCPDF_PATH',
	'?powererp_lib_FPDI_PATH' => 'powererp_lib_FPDI_PATH',
	'?powererp_lib_TCPDI_PATH' => 'powererp_lib_TCPDI_PATH',
	'?powererp_lib_NUSOAP_PATH' => 'powererp_lib_NUSOAP_PATH',
	'?powererp_lib_GEOIP_PATH' => 'powererp_lib_GEOIP_PATH',
	'?powererp_lib_ODTPHP_PATH' => 'powererp_lib_ODTPHP_PATH',
	'?powererp_lib_ODTPHP_PATHTOPCLZIP' => 'powererp_lib_ODTPHP_PATHTOPCLZIP',
	'?powererp_js_CKEDITOR' => 'powererp_js_CKEDITOR',
	'?powererp_js_JQUERY' => 'powererp_js_JQUERY',
	'?powererp_js_JQUERY_UI' => 'powererp_js_JQUERY_UI',
	'?powererp_font_DOL_DEFAULT_TTF' => 'powererp_font_DOL_DEFAULT_TTF',
	'?powererp_font_DOL_DEFAULT_TTF_BOLD' => 'powererp_font_DOL_DEFAULT_TTF_BOLD',
	'separator4' => '',
	'powererp_main_restrict_os_commands' => 'Restrict CLI commands for backups',
	'powererp_main_restrict_ip' => 'Restrict access to some IPs only',
	'?powererp_mailing_limit_sendbyweb' => 'Limit nb of email sent by page',
	'?powererp_mailing_limit_sendbycli' => 'Limit nb of email sent by cli',
	'?powererp_mailing_limit_sendbyday' => 'Limit nb of email sent per day',
	'?powererp_strict_mode' => 'Strict mode is on/off',
	'?powererp_nocsrfcheck' => 'Disable CSRF security checks'
);

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td class="titlefieldcreate">'.$langs->trans("Parameters").' ';
print $langs->trans("ConfigurationFile").' ('.$conffiletoshowshort.')';
print '</td>';
print '<td>'.$langs->trans("Parameter").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print '</tr>'."\n";

foreach ($configfileparameters as $key => $value) {
	$ignore = 0;

	if (empty($ignore)) {
		$newkey = preg_replace('/^\?/', '', $key);

		if (preg_match('/^\?/', $key) && empty(${$newkey})) {
			if ($newkey != 'multicompany_transverse_mode' || !isModEnabled('multicompany')) {
				continue; // We discard parameters starting with ?
			}
		}
		if (strpos($newkey, 'separator') !== false && $lastkeyshown == 'separator') {
			continue;
		}

		print '<tr class="oddeven">';
		if (strpos($newkey, 'separator') !== false) {
			print '<td colspan="3">&nbsp;</td>';
		} else {
			// Label
			print "<td>".$value.'</td>';
			// Key
			print '<td>'.$newkey.'</td>';
			// Value
			print "<td>";
			if (in_array($newkey, array('powererp_main_db_pass', 'powererp_main_auth_ldap_admin_pass'))) {
				if (empty($powererp_main_prod)) {
					print '<!-- '.${$newkey}.' -->';
					print showValueWithClipboardCPButton(${$newkey}, 0, '********');
				} else {
					print '**********';
				}
			} elseif ($newkey == 'powererp_main_url_root' && preg_match('/__auto__/', ${$newkey})) {
				print ${$newkey}.' => '.constant('DOL_MAIN_URL_ROOT');
			} elseif ($newkey == 'powererp_main_document_root_alt') {
				$tmparray = explode(',', $powererp_main_document_root_alt);
				$i = 0;
				foreach ($tmparray as $value2) {
					if ($i > 0) {
						print ', ';
					}
					print $value2;
					if (!is_readable($value2)) {
						$langs->load("errors");
						print ' '.img_warning($langs->trans("ErrorCantReadDir", $value2));
					}
					++$i;
				}
			} elseif ($newkey == 'powererp_main_instance_unique_id') {
				//print $conf->file->instance_unique_id;
				global $powererp_main_cookie_cryptkey, $powererp_main_instance_unique_id;
				$valuetoshow = $powererp_main_instance_unique_id ? $powererp_main_instance_unique_id : $powererp_main_cookie_cryptkey; // Use $powererp_main_instance_unique_id first then $powererp_main_cookie_cryptkey
				if (empty($powererp_main_prod)) {
					print '<!-- '.$powererp_main_instance_unique_id.' -->';
					print showValueWithClipboardCPButton($valuetoshow, 0, '********');
				} else {
					print '**********';
				}
				if (empty($valuetoshow)) {
					print img_warning("EditConfigFileToAddEntry", 'powererp_main_instance_unique_id');
				}
				print '</td></tr>';
				print '<tr class="oddeven"><td></td><td>&nbsp; => '.$langs->trans("HashForPing").'</td><td>'.md5('powererp'.$valuetoshow).'</td></tr>'."\n";
			} elseif ($newkey == 'powererp_main_prod') {
				print ${$newkey};

				$valuetoshow = ${$newkey};
				if (empty($valuetoshow)) {
					print img_warning($langs->trans('SwitchThisForABetterSecurity', 1));
				}
			} elseif ($newkey == 'powererp_nocsrfcheck') {
				print ${$newkey};

				$valuetoshow = ${$newkey};
				if (!empty($valuetoshow)) {
					print img_warning($langs->trans('SwitchThisForABetterSecurity', 0));
				}
			} elseif ($newkey == 'powererp_main_db_readonly') {
				print ${$newkey};

				$valuetoshow = ${$newkey};
				if (!empty($valuetoshow)) {
					print img_warning($langs->trans('ReadOnlyMode', 1));
				}
			} else {
				print (empty(${$newkey}) ? '' : ${$newkey});
			}
			if ($newkey == 'powererp_main_url_root' && ${$newkey} != DOL_MAIN_URL_ROOT) {
				print ' (currently overwritten by autodetected value: '.DOL_MAIN_URL_ROOT.')';
			}
			print "</td>";
		}
		print "</tr>\n";
		$lastkeyshown = $newkey;
	}
}
print '</table>';
print '</div>';
print '<br>';



// Parameters in database
print '<div class="div-table-responsive-no-min">';
print '<table class="noborder">';
print '<tr class="liste_titre">';
print '<td class="titlefield">'.$langs->trans("Parameters").' '.$langs->trans("Database").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
if (!isModEnabled('multicompany') || !$user->entity) {
	print '<td class="center width="80px"">'.$langs->trans("Entity").'</td>'; // If superadmin or multicompany disabled
}
print "</tr>\n";

$sql = "SELECT";
$sql .= " rowid";
$sql .= ", ".$db->decrypt('name')." as name";
$sql .= ", ".$db->decrypt('value')." as value";
$sql .= ", type";
$sql .= ", note";
$sql .= ", entity";
$sql .= " FROM ".MAIN_DB_PREFIX."const";
if (!isModEnabled('multicompany')) {
	// If no multicompany mode, admins can see global and their constantes
	$sql .= " WHERE entity IN (0,".$conf->entity.")";
} else {
	// If multicompany mode, superadmin (user->entity=0) can see everything, admin are limited to their entities.
	if ($user->entity) {
		$sql .= " WHERE entity IN (".$db->sanitize($user->entity.",".$conf->entity).")";
	}
}
$sql .= " ORDER BY entity, name ASC";
$resql = $db->query($sql);
if ($resql) {
	$num = $db->num_rows($resql);
	$i = 0;

	while ($i < $num) {
		$obj = $db->fetch_object($resql);

		print '<tr class="oddeven">';
		print '<td class="tdoverflowmax600" title="'.dol_escape_htmltag($obj->name).'">'.dol_escape_htmltag($obj->name).'</td>'."\n";
		print '<td class="tdoverflowmax300">';
		if (isASecretKey($obj->name)) {
			if (empty($powererp_main_prod)) {
				print '<!-- '.$obj->value.' -->';
			}
			print '**********';
		} else {
			print dol_escape_htmltag($obj->value);
		}
		print '</td>'."\n";
		if (!isModEnabled('multicompany') || !$user->entity) {
			print '<td class="center" width="80px">'.$obj->entity.'</td>'."\n"; // If superadmin or multicompany disabled
		}
		print "</tr>\n";

		$i++;
	}
}

print '</table>';
print '</div>';

// End of page
llxFooter();
$db->close();
