<?php
/* Copyright (C) 2012 		Regis Houssin		<regis.houssin@capnetworks.com>
 * Copyright (C) 2016 		Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2018 		Charlene Benke		<charlie@patas-monkey.com>

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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *       \file       myschdule/ficheinter/ajax/event-update.php
 *       \brief      File to load update an event
 */

if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1'); // Disables token renewal
if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1');
//if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');
if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');
//if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');

$res=0;
if (! $res && file_exists("../../../main.inc.php")) 
	$res=@include("../../../main.inc.php");		// For root directory
if (! $res && file_exists("../../../../main.inc.php")) 
	$res=@include("../../../../main.inc.php");	// For "custom" directory

dol_include_once('/core/lib/date.lib.php');

$datedeb = GETPOST('start', 'alpha');
$datefin = GETPOST('end', 'alpha');
$id = GETPOST('id', 'int');
/*
 * View
 */

top_httphead();

//print '<!-- Ajax page called with url '.dol_escape_htmltag($_SERVER["PHP_SELF"]).'?'.dol_escape_htmltag($_SERVER["QUERY_STRING"]).' -->'."\n";

// Load original field value
if (! empty($datedeb)  && ! empty($datefin) ) {
	$timedeb = New DateTime($datedeb);
	$timefin = New DateTime($datefin);
	// on d�termine la dur�e par le delta entre d�but et fin
	$interval= $timedeb->diff($timefin);
	$duree = $interval->days*86400 + $interval->h*3600 + $interval->i*60 + $interval->s;

	$sql= " UPDATE ".MAIN_DB_PREFIX."actioncomm";
	$sql.= " set datep= '".$datedeb."'";
	$sql.= " , datep2= '".$datefin."'";
	$sql.= " , durationp= ".$duree;
	$sql.= " WHERE id =".$id;

	$return['query']=$sql;
	$resql = $db->query($sql);
	echo json_encode($return);

}