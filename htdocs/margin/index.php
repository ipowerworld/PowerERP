<?php
/* Copyright (C) 2012	Christophe Battarel	<christophe.battarel@altairis.fr>
 * Copyright (C) 2014   Marcos García       <marcosgdf@gmail.com>
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
 *	\file       htdocs/margin/index.php
 *	\ingroup    product margins
 *	\brief      Page d'index du module margin
 */

// Load PowerERP environment
require '../main.inc.php';

if ($user->rights->produit->lire) {
	$page = 'productMargins';
} elseif ($user->hasRight('societe', 'lire')) {
	$page = 'customerMargins';
} else {
	$page = 'agentMargins';
}

header('Location: '.DOL_URL_ROOT.'/margin/'.$page.'.php');
