<?php
/* Copyright (C) 2003-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
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
 *	    \file       htdocs/core/modules/societe/modules_societe.class.php
 *		\ingroup    societe
 *		\brief      File with parent class of submodules to manage numbering and document generation
 */
require_once DOL_DOCUMENT_ROOT.'/core/class/commondocgenerator.class.php';


/**
 *	Parent class for third parties models of doc generators
 */
abstract class ModeleThirdPartyDoc extends CommonDocGenerator
{
	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return list of active generation modules
	 *
	 * 	@param	DoliDB		$dbs				Database handler
	 *  @param	integer		$maxfilenamelength  Max length of value to show
	 * 	@return	array							List of templates
	 */
	public static function liste_modeles($dbs, $maxfilenamelength = 0)
	{
		// phpcs:enable

		$type = 'company';
		$list = array();

		include_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
		$list = getListOfModels($dbs, $type, $maxfilenamelength);

		return $list;
	}
}

/**
 *		Parent class for third parties code generators
 */
abstract class ModeleThirdPartyCode
{
	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 * @var array Error code (or message) array
	 */
	public $errors;


	/**     Renvoi la description par defaut du modele de numerotation
	 *
	 *		@param	Translate	$langs		Object langs
	 *      @return string      			Texte descripif
	 */
	public function info($langs)
	{
		$langs->load("bills");
		return $langs->trans("NoDescription");
	}

	/**     Return name of module
	 *
	 *		@param	Translate	$langs		Object langs
	 *      @return string      			Nom du module
	 */
	public function getNom($langs)
	{
		return $this->name;
	}


	/**     Return an example of numbering
	 *
	 *		@param	Translate	$langs		Object langs
	 *      @return string      			Example
	 */
	public function getExample($langs)
	{
		$langs->load("bills");
		return $langs->trans("NoExample");
	}

	/**
	 *  Checks if the numbers already in the database do not
	 *  cause conflicts that would prevent this numbering working.
	 *
	 *  @return     boolean     false if conflict, true if ok
	 */
	public function canBeActivated()
	{
		return true;
	}

	/**
	 *  Return next value available
	 *
	 *	@param	Societe		$objsoc		Object thirdparty
	 *	@param	int			$type		Type
	 *  @return string      			Value
	 */
	public function getNextValue($objsoc = 0, $type = -1)
	{
		global $langs;
		return $langs->trans("Function_getNextValue_InModuleNotWorking");
	}


	/**
	 *  Return version of module
	 *
	 *  @return     string      Version
	 */
	public function getVersion()
	{
		global $langs;
		$langs->load("admin");

		if ($this->version == 'development') {
			return $langs->trans("VersionDevelopment");
		} elseif ($this->version == 'experimental') {
			return $langs->trans("VersionExperimental");
		} elseif ($this->version == 'powererp') {
			return DOL_VERSION;
		} elseif ($this->version) {
			return $this->version;
		} else {
			return $langs->trans("NotAvailable");
		}
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Renvoie la liste des modeles de numérotation
	 *
	 *  @param	DoliDB	$dbs     			Database handler
	 *  @param  integer	$maxfilenamelength  Max length of value to show
	 *  @return	array|int					List of numbers
	 */
	public static function liste_modeles($dbs, $maxfilenamelength = 0)
	{
		// phpcs:enable
		$list = array();
		$sql = "";

		$resql = $dbs->query($sql);
		if ($resql) {
			$num = $dbs->num_rows($resql);
			$i = 0;
			while ($i < $num) {
				$row = $dbs->fetch_row($resql);
				$list[$row[0]] = $row[1];
				$i++;
			}
		} else {
			return -1;
		}
		return $list;
	}

	/**
	 *  Return description of module parameters
	 *
	 *  @param	Translate	$langs      Output language
	 *  @param	Societe		$soc		Third party object
	 *  @param	int			$type		-1=Nothing, 0=Customer, 1=Supplier
	 *  @return	string					HTML translated description
	 */
	public function getToolTip($langs, $soc, $type)
	{
		global $conf;

		$langs->loadLangs(array("admin", "companies"));

		$strikestart = '';
		$strikeend = '';
		if (!empty($conf->global->MAIN_COMPANY_CODE_ALWAYS_REQUIRED) && !empty($this->code_null)) {
			$strikestart = '<strike>';
			$strikeend = '</strike> '.yn(1, 1, 2).' ('.$langs->trans("ForcedToByAModule", $langs->transnoentities("yes")).')';
		}

		$s = '';
		if ($type == -1) {
			$s .= $langs->trans("Name").': <b>'.$this->getNom($langs).'</b><br>';
		} elseif ($type == -1) {
			$s .= $langs->trans("Version").': <b>'.$this->getVersion().'</b><br>';
		} elseif ($type == 0) {
			$s .= $langs->trans("CustomerCodeDesc").'<br>';
		} elseif ($type == 1) {
			$s .= $langs->trans("SupplierCodeDesc").'<br>';
		}
		if ($type != -1) {
			$s .= $langs->trans("ValidityControledByModule").': <b>'.$this->getNom($langs).'</b><br>';
		}
		$s .= '<br>';
		$s .= '<u>'.$langs->trans("ThisIsModuleRules").':</u><br>';
		if ($type == 0) {
			$s .= $langs->trans("RequiredIfCustomer").': '.$strikestart;
			$s .= yn(!$this->code_null, 1, 2).$strikeend;
			$s .= '<br>';
		} elseif ($type == 1) {
			$s .= $langs->trans("RequiredIfSupplier").': '.$strikestart;
			$s .= yn(!$this->code_null, 1, 2).$strikeend;
			$s .= '<br>';
		} elseif ($type == -1) {
			$s .= $langs->trans("Required").': '.$strikestart;
			$s .= yn(!$this->code_null, 1, 2).$strikeend;
			$s .= '<br>';
		}
		$s .= $langs->trans("CanBeModifiedIfOk").': ';
		$s .= yn($this->code_modifiable, 1, 2);
		$s .= '<br>';
		$s .= $langs->trans("CanBeModifiedIfKo").': '.yn($this->code_modifiable_invalide, 1, 2).'<br>';
		$s .= $langs->trans("AutomaticCode").': '.yn($this->code_auto, 1, 2).'<br>';
		$s .= '<br>';
		if ($type == 0 || $type == -1) {
			$nextval = $this->getNextValue($soc, 0);
			if (empty($nextval)) {
				$nextval = $langs->trans("Undefined");
			}
			$s .= $langs->trans("NextValue").($type == -1 ? ' ('.$langs->trans("Customer").')' : '').': <b>'.$nextval.'</b><br>';
		}
		if ($type == 1 || $type == -1) {
			$nextval = $this->getNextValue($soc, 1);
			if (empty($nextval)) {
				$nextval = $langs->trans("Undefined");
			}
			$s .= $langs->trans("NextValue").($type == -1 ? ' ('.$langs->trans("Supplier").')' : '').': <b>'.$nextval.'</b>';
		}
		return $s;
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *   Check if mask/numbering use prefix
	 *
	 *   @return    int	    0=no, 1=yes
	 */
	public function verif_prefixIsUsed()
	{
		// phpcs:enable
		return 0;
	}
}


/**
 *		Parent class for third parties accountancy code generators
 */
abstract class ModeleAccountancyCode
{
	/**
	 * @var string Error code (or message)
	 */
	public $error = '';


	/**
	 *  Return description of module
	 *
	 *  @param	Translate	$langs		Object langs
	 *  @return string      			Description of module
	 */
	public function info($langs)
	{
		$langs->load("bills");
		return $langs->trans("NoDescription");
	}

	/**
	 *  Return an example of result returned by getNextValue
	 *
	 *  @param	Translate	$langs		Object langs
	 *  @param	societe		$objsoc		Object thirdparty
	 *  @param	int			$type		Type of third party (1:customer, 2:supplier, -1:autodetect)
	 *  @return	string					Example
	 */
	public function getExample($langs, $objsoc = 0, $type = -1)
	{
		$langs->load("bills");
		return $langs->trans("NoExample");
	}

	/**
	 *  Checks if the numbers already in the database do not
	 *  cause conflicts that would prevent this numbering working.
	 *
	 *  @return     boolean     false if conflict, true if ok
	 */
	public function canBeActivated()
	{
		return true;
	}

	/**
	 *  Return version of module
	 *
	 *  @return     string      Version
	 */
	public function getVersion()
	{
		global $langs;
		$langs->load("admin");

		if ($this->version == 'development') {
			return $langs->trans("VersionDevelopment");
		} elseif ($this->version == 'experimental') {
			return $langs->trans("VersionExperimental");
		} elseif ($this->version == 'powererp') {
			return DOL_VERSION;
		} elseif ($this->version) {
			return $this->version;
		} else {
			return $langs->trans("NotAvailable");
		}
	}

	/**
	 *  Return description of module parameters
	 *
	 *  @param	Translate	$langs      Output language
	 *  @param	Societe		$soc		Third party object
	 *  @param	int			$type		-1=Nothing, 0=Customer, 1=Supplier
	 *  @return	string					HTML translated description
	 */
	public function getToolTip($langs, $soc, $type)
	{
		global $conf, $db;

		$langs->load("admin");

		$s = '';
		if ($type == -1) {
			$s .= $langs->trans("Name").': <b>'.$this->name.'</b><br>';
			$s .= $langs->trans("Version").': <b>'.$this->getVersion().'</b><br>';
		}
		//$s.='<br>';
		//$s.='<u>'.$langs->trans("ThisIsModuleRules").':</u><br>';
		$s .= '<br>';
		if ($type == 0 || $type == -1) {
			$result = $this->get_code($db, $soc, 'customer');
			$nextval = $this->code;
			if (empty($nextval)) {
				$nextval = $langs->trans("Undefined");
			}
			$s .= $langs->trans("NextValue").($type == -1 ? ' ('.$langs->trans("Customer").')' : '').': <b>'.$nextval.'</b><br>';
		}
		if ($type == 1 || $type == -1) {
			$result = $this->get_code($db, $soc, 'supplier');
			$nextval = $this->code;
			if (empty($nextval)) {
				$nextval = $langs->trans("Undefined");
			}
			$s .= $langs->trans("NextValue").($type == -1 ? ' ('.$langs->trans("Supplier").')' : '').': <b>'.$nextval.'</b>';
		}
		return $s;
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Set accountancy account code for a third party into this->code
	 *
	 *  @param	DoliDB	$db             Database handler
	 *  @param  Societe	$societe        Third party object
	 *  @param  int		$type			'customer' or 'supplier'
	 *  @return	int						>=0 if OK, <0 if KO
	 */
	public function get_code($db, $societe, $type = '')
	{
		// phpcs:enable
		global $langs;

		return $langs->trans("NotAvailable");
	}
}
