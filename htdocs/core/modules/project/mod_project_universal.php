<?php
/* Copyright (C) 2010 Regis Houssin  <regis.houssin@inodbox.com>
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
 *	\file       htdocs/core/modules/project/mod_project_universal.php
 *	\ingroup    project
 *	\brief      Fichier contenant la classe du modele de numerotation de reference de projet Universal
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/project/modules_project.php';


/**
 * 	Classe du modele de numerotation de reference de projet Universal
 */
class mod_project_universal extends ModeleNumRefProjects
{
	/**
	 * @var DoliDB $db
	 */
	public $db;

	/**
	 * PowerERP version of the loaded document
	 * @var string
	 */
	public $version = 'powererp'; // 'development', 'experimental', 'powererp'

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 * @var string Nom du modele
	 * @deprecated
	 * @see $name
	 */
	public $nom = 'Universal';

	/**
	 * @var string model name
	 */
	public $name = 'Universal';


	/**
	 *  Returns the description of the numbering model
	 *
	 *  @return     string      Texte descripif
	 */
	public function info()
	{
		global $conf, $langs;

		// Load translation files required by the page
		$langs->loadLangs(array("projects", "admin"));

		$form = new Form($this->db);

		$texte = $langs->trans('GenericNumRefModelDesc')."<br>\n";
		$texte .= '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
		$texte .= '<input type="hidden" name="token" value="'.newToken().'">';
		$texte .= '<input type="hidden" name="action" value="updateMask">';
		$texte .= '<input type="hidden" name="maskconstproject" value="PROJECT_UNIVERSAL_MASK">';
		$texte .= '<table class="nobordernopadding" width="100%">';

		$tooltip = $langs->trans("GenericMaskCodes", $langs->transnoentities("Project"), $langs->transnoentities("Project"));
		$tooltip .= $langs->trans("GenericMaskCodes2");
		$tooltip .= $langs->trans("GenericMaskCodes3");
		$tooltip .= $langs->trans("GenericMaskCodes4a", $langs->transnoentities("Project"), $langs->transnoentities("Project"));
		$tooltip .= $langs->trans("GenericMaskCodes5");

		// Parametrage du prefix
		$texte .= '<tr><td>'.$langs->trans("Mask").':</td>';
		$texte .= '<td class="right">'.$form->textwithpicto('<input type="text" class="flat minwidth175" name="maskproject" value="'.$conf->global->PROJECT_UNIVERSAL_MASK.'">', $tooltip, 1, 1).'</td>';

		$texte .= '<td class="left" rowspan="2">&nbsp; <input type="submit" class="button button-edit" name="Button"value="'.$langs->trans("Modify").'"></td>';

		$texte .= '</tr>';

		$texte .= '</table>';
		$texte .= '</form>';

		return $texte;
	}

	/**
	 *  Return an example of numbering
	 *
	 *  @return     string      Example
	 */
	public function getExample()
	{
		global $conf, $langs, $mysoc;

		$old_code_client = $mysoc->code_client;
		$mysoc->code_client = 'CCCCCCCCCC';
		$numExample = $this->getNextValue($mysoc, '');
		$mysoc->code_client = $old_code_client;

		if (!$numExample) {
			$numExample = $langs->trans('NotConfigured');
		}
		return $numExample;
	}

	/**
	 *  Return next value
	 *
	 *  @param	Societe		$objsoc		Object third party
	 *  @param   Project		$project	Object project
	 *  @return  string					Value if OK, 0 if KO
	 */
	public function getNextValue($objsoc, $project)
	{
		global $db, $conf;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

		// On defini critere recherche compteur
		$mask = $conf->global->PROJECT_UNIVERSAL_MASK;

		if (!$mask) {
			$this->error = 'NotConfigured';
			return 0;
		}

		// Get entities
		$entity = getEntity('projectnumber', 1, $project);

		$date = (empty($project->date_c) ? dol_now() : $project->date_c);
		$numFinal = get_next_value($db, $mask, 'projet', 'ref', '', (is_object($objsoc) ? $objsoc : ''), $date, 'next', false, null, $entity);

		return  $numFinal;
	}


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return next reference not yet used as a reference
	 *
	 *  @param	Societe		$objsoc     Object third party
	 *  @param  Project		$project	Object project
	 *  @return string      			Next not used reference
	 */
	public function project_get_num($objsoc = 0, $project = '')
	{
		// phpcs:enable
		return $this->getNextValue($objsoc, $project);
	}
}
