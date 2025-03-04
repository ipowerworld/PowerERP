<?php
/* Copyright (C) 2004-2017	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2006		Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2007-2017	Regis Houssin			<regis.houssin@inodbox.com>
 * Copyright (C) 2011		Philippe Grand			<philippe.grand@atoo-net.com>
 * Copyright (C) 2012		Juanjo Menent			<jmenent@2byte.es>
 * Copyright (C) 2018       Ferran Marcet           <fmarcet@2byte.es>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FI8TNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *		\file       htdocs/theme/md/manifest.json.php
 *		\brief      File for The Web App
 */

if (!defined('NOREQUIREUSER')) {
	define('NOREQUIREUSER', '1');
}
if (!defined('NOREQUIREDB')) {
	define('NOREQUIREDB', '1');
}
if (!defined('NOREQUIRESOC')) {
	define('NOREQUIRESOC', '1');
}
if (!defined('NOREQUIRETRAN')) {
	define('NOREQUIRETRAN', '1');
}
if (!defined('NOTOKENRENEWAL')) {
	define('NOTOKENRENEWAL', '1');
}
if (!defined('NOLOGIN')) {
	define('NOLOGIN', '1');
}
if (!defined('NOREQUIREMENU')) {
	define('NOREQUIREMENU', '1');
}
if (!defined('NOREQUIREHTML')) {
	define('NOREQUIREHTML', '1');
}
if (!defined('NOREQUIREAJAX')) {
	define('NOREQUIREAJAX', '1');
}
if (!defined('NOSESSION')) {
	define('NOSESSION', '1');
}

require_once __DIR__.'/../../main.inc.php';

$appli = constant('DOL_APPLICATION_TITLE');
if (!empty($conf->global->MAIN_APPLICATION_TITLE)) {
	$appli = $conf->global->MAIN_APPLICATION_TITLE;
}

top_httphead('text/json');
// Important: Following code is to avoid page request by browser and PHP CPU at each PowerERP page access.
if (empty($powererp_nocache)) {
	header('Cache-Control: max-age=10800, public, must-revalidate');
	// For a text/json, we must set an Expires to avoid to have it forced to an expired value by the web server
	header('Expires: '.gmdate('D, d M Y H:i:s', dol_now('gmt') + 10800).' GMT');
} else {
	header('Cache-Control: no-cache');
}

?>
{
	"name": "<?php echo $appli; ?>",
	"icons": [
		{
			"src": "<?php echo DOL_URL_ROOT.'/theme/powererp_256x256_color.png'; ?>",
			"sizes": "256x256",
			"type": "image/png"
		}
	],
	"theme_color": "#ffffff",
	"background_color": "#ffffff",
	"display": "standalone"
}