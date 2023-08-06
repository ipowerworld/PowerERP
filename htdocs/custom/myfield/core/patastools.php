<?php
/* Copyright (C) 2001-2005	Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2004-2010	Laurent Destailleur 	<eldy@users.sourceforge.net>
 * Copyright (C) 2005-2010	Regis Houssin			<regis.houssin@capnetworks.com>
 * Copyright (C) 2014-2017	Charlene Benke			<charlie@patas-monkey.com>
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
 *	   \file	   customTabs/core/patastools.php
 *	   \brief	  Home page for top menu patas tools
 */

$res=@include("../../main.inc.php");					// For root directory
if (! $res && file_exists($_SERVER['DOCUMENT_ROOT']."/../main.inc.php"))
	$res=@include($_SERVER['DOCUMENT_ROOT']."/../main.inc.php"); // Use on dev env only
if (! $res) $res=@include("../../../main.inc.php");		// For "custom" directory

$langs->load("companies");
$langs->load("other");

// Security check
$socid=0;
if ($user->societe_id > 0) $socid=$user->societe_id;

/*
 * View
 */

$socstatic=new Societe($db);

llxHeader("", $langs->trans("PatasTools"), "");

$text=$langs->trans("PatasMonkeyTools");

print_fiche_titre($text, '', 'patastools.png@myfield');


$fileinfo= @file_get_contents('http://www.patas-monkey.com/docs/patasToolsInfos.html');
if ($fileinfo === FALSE)	// not connected
	print $langs->trans("PatasToolsNews");
else
	print $fileinfo;

llxFooter();
$db->close();