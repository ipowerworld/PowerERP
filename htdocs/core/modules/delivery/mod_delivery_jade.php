<?php
/* Copyright (C) 2005      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2008 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2010 Regis Houssin        <regis.houssin@inodbox.com>
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
 * or see https://www.gnu.org/
 */

/**
 *   \file       htdocs/core/modules/delivery/mod_delivery_jade.php
 *   \ingroup    delivery
 *   \brief      Fichier contenant la classe du modele de numerotation de reference de bon de livraison Jade
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/delivery/modules_delivery.php';


/**
 *  \class      mod_delivery_jade
 *  \brief      Classe du modele de numerotation de reference de bon de livraison Jade
 */

class mod_delivery_jade extends ModeleNumRefDeliveryOrder
{
	/**
	 * PowerERP version of the loaded document
	 * @var string
	 */
	public $version = 'powererp'; // 'development', 'experimental', 'powererp'

	/**
	 * @var string Error message
	 */
	public $error = '';

	/**
	 * @var string Nom du modele
	 * @deprecated
	 * @see $name
	 */
	public $nom = 'Jade';

	/**
	 * @var string model name
	 */
	public $name = 'Jade';

	public $prefix = 'BL';


	/**
	 *   Returns the description of the numbering model
	 *
	 *   @return     string      Texte descripif
	 */
	public function info()
	{
		global $langs;
		return $langs->trans("SimpleNumRefModelDesc", $this->prefix);
	}

	/**
	 *  Return an example of numbering
	 *
	 *  @return     string      Example
	 */
	public function getExample()
	{
		return $this->prefix."0501-0001";
	}

	/**
	 *  Checks if the numbers already in the database do not
	 *  cause conflicts that would prevent this numbering working.
	 *
	 *  @return     boolean     false if conflict, true if ok
	 */
	public function canBeActivated()
	{
		global $langs, $conf, $db;

		$langs->load("bills");

		// Check invoice num
		$fayymm = '';
		$max = '';

		$posindice = strlen($this->prefix) + 6;
		$sql = "SELECT MAX(CAST(SUBSTRING(ref FROM ".$posindice.") AS SIGNED)) as max"; // This is standard SQL
		$sql .= " FROM ".MAIN_DB_PREFIX."delivery";
		$sql .= " WHERE ref LIKE '".$db->escape($this->prefix)."____-%'";
		$sql .= " AND entity = ".$conf->entity;

		$resql = $db->query($sql);
		if ($resql) {
			$row = $db->fetch_row($resql);
			if ($row) {
				$fayymm = substr($row[0], 0, 6);
				$max = $row[0];
			}
		}
		if ($fayymm && !preg_match('/'.$this->prefix.'[0-9][0-9][0-9][0-9]/i', $fayymm)) {
			$langs->load("errors");
			$this->error = $langs->trans('ErrorNumRefModel', $max);
			return false;
		}

		return true;
	}

	/**
	 * 	Return next free value
	 *
	 *  @param	Societe		$objsoc     Object thirdparty
	 *  @param  Object		$object		Object we need next value for
	 *  @return string      			Value if KO, <0 if KO
	 */
	public function getNextValue($objsoc, $object)
	{
		global $db, $conf;

		// First, we get the max value
		$posindice = strlen($this->prefix) + 6;
		$sql = "SELECT MAX(CAST(SUBSTRING(ref FROM ".$posindice.") AS SIGNED)) as max"; // This is standard SQL
		$sql .= " FROM ".MAIN_DB_PREFIX."delivery";
		$sql .= " WHERE ref LIKE '".$db->escape($this->prefix)."____-%'";
		$sql .= " AND entity = ".$conf->entity;

		$resql = $db->query($sql);
		dol_syslog("mod_delivery_jade::getNextValue", LOG_DEBUG);
		if ($resql) {
			$obj = $db->fetch_object($resql);
			if ($obj) {
				$max = intval($obj->max);
			} else {
				$max = 0;
			}
		} else {
			return -1;
		}

		$date = $object->date_delivery;
		if (empty($date)) {
			$date = dol_now();
		}
		$yymm = strftime("%y%m", $date);

		if ($max >= (pow(10, 4) - 1)) {
			$num = $max + 1; // If counter > 9999, we do not format on 4 chars, we take number as it is
		} else {
			$num = sprintf("%04s", $max + 1);
		}

		dol_syslog("mod_delivery_jade::getNextValue return ".$this->prefix.$yymm."-".$num);
		return $this->prefix.$yymm."-".$num;
	}


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return next free ref
	 *
	 *  @param  Societe     $objsoc         Object thirdparty
	 *  @param  Object      $object         Object livraison
	 *  @return string                      Texte descriptif
	 */
	public function delivery_get_num($objsoc = 0, $object = '')
	{
		// phpcs:enable
		return $this->getNextValue($objsoc, $object);
	}
}
