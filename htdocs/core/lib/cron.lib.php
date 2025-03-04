<?php
/* Copyright (C) 2012 Nicolas Villa aka Boyquotes http://informetic.fr
 * Copyright (C) 2013 Florian Henry <florian.henry@opn-concept.pro>
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
 *	\file       core/lib/cron.lib.php
 *	\brief      Function for module cron
 *	\ingroup    cron
 */


/**
 * Return array of tabs to used on pages to setup cron module.
 *
 * @return 	array				Array of tabs
 */
function cronadmin_prepare_head()
{
	global $langs, $conf, $user;
	$h = 0;
	$head = array();

	$head[$h][0] = DOL_URL_ROOT.'/cron/admin/cron.php';
	$head[$h][1] = $langs->trans("Miscellaneous");
	$head[$h][2] = 'setup';
	$h++;

	$head[$h][0] = DOL_URL_ROOT.'/cron/list.php?mode=modulesetup';
	$head[$h][1] = $langs->trans("Module2300Name");
	$head[$h][2] = 'jobs';
	$h++;

	complete_head_from_modules($conf, $langs, null, $head, $h, 'cronadmin');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'cronadmin', 'remove');


	return $head;
}

/**
 * Return array of tabs to used on a cron job
 *
 * @param 	Cronjob	$object		Object cron
 * @return 	array				Array of tabs
 */
function cron_prepare_head(Cronjob $object)
{
	global $langs, $conf, $user;
	$h = 0;
	$head = array();

	$head[$h][0] = DOL_URL_ROOT.'/cron/card.php?id='.$object->id;
	$head[$h][1] = $langs->trans("CronTask");
	$head[$h][2] = 'card';
	$h++;

	$head[$h][0] = DOL_URL_ROOT.'/cron/info.php?id='.$object->id;
	$head[$h][1] = $langs->trans("Info");
	$head[$h][2] = 'info';
	$h++;

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'cron');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'cron', 'remove');

	return $head;
}

/**
 * Show information with URLs to launch jobs
 *
 * @return	int			0
 */
function dol_print_cron_urls()
{
	global $conf, $langs, $user;
	global $powererp_main_url_root;

	// Define $urlwithroot
	$urlwithouturlroot = preg_replace('/'.preg_quote(DOL_URL_ROOT, '/').'$/i', '', trim($powererp_main_url_root));
	$urlwithroot = $urlwithouturlroot.DOL_URL_ROOT; // This is to use external domain name found into config file
	//$urlwithroot=DOL_MAIN_URL_ROOT;					// This is to use same domain name than current

	// Cron launch
	print '<div class="div-table-responsive-no-min">';
	print $langs->trans("URLToLaunchCronJobs").':<br>';
	$url = $urlwithroot.'/public/cron/cron_run_jobs_by_url.php?'.(empty($conf->global->CRON_KEY) ? '' : 'securitykey='.$conf->global->CRON_KEY.'&').'userlogin='.$user->login;
	print '<div class="urllink">';
	print '<input type="text" id="publicurlmember" class="quatrevingtpercentminusx" value="'.$url.'">';
	print ' <a href="'.$url.'" target="_blank" rel="noopener noreferrer">'.img_picto('', 'globe')."</a>\n";
	print '</div>';
	print '<br> '.$langs->trans("OrToLaunchASpecificJob").'<br>';
	$url = $urlwithroot.'/public/cron/cron_run_jobs_by_url.php?'.(empty($conf->global->CRON_KEY) ? '' : 'securitykey='.$conf->global->CRON_KEY.'&').'userlogin='.$user->login.'&id=cronjobid';
	print '<div class="urllink">';
	print '<input type="text" id="publicurlmemberall" class="quatrevingtpercentminusx" value="'.$url.'">';
	print ' <a href="'.$url.'" target="_blank" rel="noopener noreferrer">'.img_picto('', 'globe')."</a>\n";
	print '</div>';
	print '</div>';
	print '<br>';

	print ajax_autoselect("publicurlmember");
	print ajax_autoselect("publicurlmemberall");

	$logintouse = 'firstadmin';
	if ($user->admin) {
		$logintouse = $user->login;
	}

	print '<u>'.$langs->trans("FileToLaunchCronJobs").':</u><br>';

	$pathtoscript = '/pathtoscript';
	if (!empty($conf->global->MAIN_DOL_SCRIPTS_ROOT)) {
		$pathtoscript = $conf->global->MAIN_DOL_SCRIPTS_ROOT;
	}

	$file = $pathtoscript.'/scripts/cron/cron_run_jobs.php '.(empty($conf->global->CRON_KEY) ? 'securitykey' : ''.$conf->global->CRON_KEY.'').' '.$logintouse.' [cronjobid]';
	print '<textarea class="quatrevingtpercent">'.$file."</textarea><br>\n";
	print '<br>';

	// Add note
	if (empty($conf->global->CRON_DISABLE_TUTORIAL_CRON)) {
		$linuxlike = 1;
		if (preg_match('/^win/i', PHP_OS)) {
			$linuxlike = 0;
		}
		if (preg_match('/^mac/i', PHP_OS)) {
			$linuxlike = 0;
		}
		print $langs->trans("Note").': ';
		if ($linuxlike) {
			print $langs->trans("CronExplainHowToRunUnix");
			print '<br>';
			print '<textarea class="quatrevingtpercent">*/5 * * * * '.$pathtoscript.'/scripts/cron/cron_run_jobs.php '.(empty($conf->global->CRON_KEY) ? 'securitykey' : ''.$conf->global->CRON_KEY.'').' '.$logintouse.' &gt; '.DOL_DATA_ROOT.'/cron_run_jobs.php.log</textarea><br>';
		} else {
			print $langs->trans("CronExplainHowToRunWin");
		}
	}

	return 0;
}
