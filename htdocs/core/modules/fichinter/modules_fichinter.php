<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2011-2019 Philippe Grand	    <philippe.grand@atoo-net.com>
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
 *  \file       htdocs/core/modules/fichinter/modules_fichinter.php
 *  \ingroup    ficheinter
 *  \brief      File that contains parent class for PDF interventions models
 *   			and parent class for interventions numbering models
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commondocgenerator.class.php';


/**
 *	Parent class to manage intervention document templates
 */
abstract class ModelePDFFicheinter extends CommonDocGenerator
{
	/**
	 * @var string Error code (or message)
	 */
	public $error = '';


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *	Return list of active generation modules
	 *
	 *  @param	DoliDB	$db     			Database handler
	 *  @param  integer	$maxfilenamelength  Max length of value to show
	 *  @return	array						List of templates
	 */
	public static function liste_modeles($db, $maxfilenamelength = 0)
	{
		// phpcs:enable
		global $conf;

		$type = 'ficheinter';
		$list = array();

		include_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
		$list = getListOfModels($db, $type, $maxfilenamelength);

		return $list;
	}
}


/**
 *  Parent class numbering models of intervention sheet references
 */
abstract class ModeleNumRefFicheinter
{
	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 * 	Return if a module can be used or not
	 *
	 * 	@return		boolean     true if module can be used
	 */
	public function isEnabled()
	{
		return true;
	}

	/**
	 * 	Returns the default description of the numbering template
	 *
	 * 	@return     string      Descriptive text
	 */
	public function info()
	{
		global $langs;
		$langs->load("ficheinter");
		return $langs->trans("NoDescription");
	}

	/**
	 * 	Return a numbering example
	 *
	 * 	@return     string      Example
	 */
	public function getExample()
	{
		global $langs;
		$langs->load("ficheinter");
		return $langs->trans("NoExample");
	}

	/**
	 *  Checks if the numbers already in the database do not
	 *  cause conflicts that would prevent this numbering working.
	 *
	 * 	@return     boolean     false if conflict, true if ok
	 */
	public function canBeActivated()
	{
		return true;
	}

	/**
	 * 	Return the next assigned value
	 *
	 *  @param	Societe		$objsoc     Object thirdparty
	 *  @param  Object		$object		Object we need next value for
	 *  @return string      			Value if KO, <0 if KO
	 */
	public function getNextValue($objsoc = 0, $object = '')
	{
		global $langs;
		return $langs->trans("NotAvailable");
	}

	/**
	 * 	Return the version of the numbering module
	 *
	 * 	@return     string      Value
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
}


// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
/**
 *  Create an intervention document on disk using template defined into FICHEINTER_ADDON_PDF
 *
 *  @param	DoliDB		$db  			objet base de donnee
 *  @param	Object		$object			Object fichinter
 *  @param	string		$modele			force le modele a utiliser ('' par defaut)
 *  @param	Translate	$outputlangs	objet lang a utiliser pour traduction
 *  @param  int			$hidedetails    Hide details of lines
 *  @param  int			$hidedesc       Hide description
 *  @param  int			$hideref        Hide ref
 *  @return int         				0 if KO, 1 if OK
 */
function fichinter_create($db, $object, $modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0)
{
	// phpcs:enable
	global $conf, $langs, $user;
	$langs->load("ficheinter");

	$error = 0;

	$srctemplatepath = '';

	// Positionne modele sur le nom du modele de fichinter a utiliser
	if (!dol_strlen($modele)) {
		if (!empty($conf->global->FICHEINTER_ADDON_PDF)) {
			$modele = $conf->global->FICHEINTER_ADDON_PDF;
		} else {
			$modele = 'soleil';
		}
	}

	// If selected modele is a filename template (then $modele="modelname:filename")
	$tmp = explode(':', $modele, 2);
	if (!empty($tmp[1])) {
		$modele = $tmp[0];
		$srctemplatepath = $tmp[1];
	}

	// Search template files
	$file = '';
	$classname = '';
	$filefound = 0;
	$dirmodels = array('/');
	if (is_array($conf->modules_parts['models'])) {
		$dirmodels = array_merge($dirmodels, $conf->modules_parts['models']);
	}
	foreach ($dirmodels as $reldir) {
		foreach (array('doc', 'pdf') as $prefix) {
			$file = $prefix."_".$modele.".modules.php";

			// On verifie l'emplacement du modele
			$file = dol_buildpath($reldir."core/modules/fichinter/doc/".$file, 0);
			if (file_exists($file)) {
				$filefound = 1;
				$classname = $prefix.'_'.$modele;
				break;
			}
		}
		if ($filefound) {
			break;
		}
	}

	// Charge le modele
	if ($filefound) {
		require_once $file;

		$obj = new $classname($db);

		// We save charset_output to restore it because write_file can change it if needed for
		// output format that does not support UTF8.
		$sav_charset_output = $outputlangs->charset_output;
		if ($obj->write_file($object, $outputlangs, $srctemplatepath, $hidedetails, $hidedesc, $hideref) > 0) {
			$outputlangs->charset_output = $sav_charset_output;

			// We delete old preview
			require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
			dol_delete_preview($object);

			return 1;
		} else {
			$outputlangs->charset_output = $sav_charset_output;
			dol_print_error($db, "fichinter_pdf_create Error: ".$obj->error);
			return 0;
		}
	} else {
		print $langs->trans("Error")." ".$langs->trans("ErrorFileDoesNotExists", $file);
		return 0;
	}
}
