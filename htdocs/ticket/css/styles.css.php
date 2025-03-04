<?php
/* Copyright (C) 2018	Laurent Destailleur		<eldy@users.sourceforge.net>
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
 *		\file       htdocs/ticket/css/styles.css.php
 *		\brief      File for CSS style sheet for ticket module
 */

//if (! defined('NOREQUIREUSER')) define('NOREQUIREUSER','1');	// Not disabled because need to load personalized language
//if (! defined('NOREQUIREDB'))   define('NOREQUIREDB','1');	// Not disabled to increase speed. Language code is found on url.
if (!defined('NOREQUIRESOC')) {
	define('NOREQUIRESOC', '1');
}
//if (! defined('NOREQUIRETRAN')) define('NOREQUIRETRAN','1');	// Not disabled because need to do translations
if (!defined('NOCSRFCHECK')) {
	define('NOCSRFCHECK', 1);
}
if (!defined('NOTOKENRENEWAL')) {
	define('NOTOKENRENEWAL', 1);
}
if (!defined('NOLOGIN')) {
	define('NOLOGIN', 1); // File must be accessed by logon page so without login
}
//if (! defined('NOREQUIREMENU'))   define('NOREQUIREMENU',1);  // We need top menu content
if (!defined('NOREQUIREHTML')) {
	define('NOREQUIREHTML', 1);
}
if (!defined('NOREQUIREAJAX')) {
	define('NOREQUIREAJAX', '1');
}

session_cache_limiter('public');

require_once '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

// Define css type
top_httphead('text/css');
// Important: Following code is to avoid page request by browser and PHP CPU at each PowerERP page access.
if (empty($powererp_nocache)) {
	header('Cache-Control: max-age=3600, public, must-revalidate');
} else {
	header('Cache-Control: no-cache');
}

?>

html {
	min-height: 100%; height: 100%;
}

html {
<?php
if (!empty($conf->global->TICKET_SHOW_MODULE_LOGO)) {
	print 'background: url("../public/img/bg_ticket.png") no-repeat 95% 90%;';
}
?>
}


div.ticketform {
	font-family: arial;
	position: static;
/*	padding: 2em 1em;
	overflow-x: auto;
	border: 2px solid rgb(153, 153, 153);
	background-color: rgb(255, 255, 255);
	box-shadow: 2px 2px 2px rgb(245, 245, 245);
	border-radius: 10px 10px 10px 10px;
	margin: 1.5em;
	background : #ffffff;
*/
	text-align: center;
}

div.ticketform .index_create, div.ticketform .index_display {
	display: inline-block;
	width: 200px;
	height: 60px;
	text-align: center;
	vertical-align: middle;
	margin: 20px;
	text-transform: uppercase;
}

#form_create_ticket, #form_view_ticket
{
	margin-left: 10px;
	margin-right: 10px;
	padding-left:1em;
	padding-right:1em;
	padding-top:1.5em;
	padding-bottom:12px;

	border: 1px solid #C0C0C0;
	background-color: #E0E0E0;

	-moz-box-shadow: 4px 4px 4px #DDD;
	-webkit-box-shadow: 4px 4px 4px #DDD;
	box-shadow: 4px 4px 4px #DDD;

	border-radius: 8px;
	border:solid 1px rgba(168,168,168,.4);
	border-top:solid 1px f8f8f8;
	background-color: #f8f8f8;
}

#form_create_ticket input.text, #form_create_ticket textarea { width:450px;}

@media only screen and (max-width: 767px)
{
	#form_create_ticket input.text,	#form_create_ticket textarea { width: unset;}

	#form_create_ticket, #form_view_ticket
	{
		margin-left: 0;
		margin-right: 0;
	}
}
