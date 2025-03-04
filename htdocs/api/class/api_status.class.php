<?php
/* Copyright (C) 2015   Jean-François Ferry     <jfefe@aternatik.fr>
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

require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';


/**
 * API that gives the status of the PowerERP instance.
 *
 * @access protected
 * @class  PowerERPApiAccess {@requires user,external}
 */
class Status
{
	/**
	 * Get status (PowerERP version)
	 *
	 * @return array
	 */
	public function index()
	{
		global $conf;

		return array(
			'success' => array(
				'code' => 200,
				'powererp_version' => DOL_VERSION,
				'access_locked' => (empty($conf->global->MAIN_ONLY_LOGIN_ALLOWED) ? '0' : $conf->global->MAIN_ONLY_LOGIN_ALLOWED),
			),
		);
	}
}
