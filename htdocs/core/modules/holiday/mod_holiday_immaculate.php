<?php
/* Copyright (C) 2011-2019		Juanjo Menent	    <jmenent@2byte.es>
 * Copyright (C) 2018			Charlene Benke		<charlie@patas-monkey.com>
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
 *  \file       htdocs/core/modules/holiday/mod_holiday_immaculate.php
 *  \ingroup    contract
 *  \brief      File of class to manage contract numbering rules Magre
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/holiday/modules_holiday.php';

/**
 *	Class to manage contract numbering rules Magre
 */
class mod_holiday_immaculate extends ModelNumRefHolidays
{
	/**
	 * PowerERP version of the loaded document
	 * @var string
	 */
	public $version = 'powererp';

	/**
	 * @var string Error message
	 */
	public $error = '';

	/**
	 * @var string Nom du modele
	 * @deprecated
	 * @see $name
	 */
	public $nom = 'Immaculate';

	/**
	 * @var string model name
	 */
	public $name = 'Immaculate';

	/**
	 * @var int Automatic numbering
	 */
	public $code_auto = 1;

	/**
	 *	Return default description of numbering model
	 *
	 *	@return     string      text description
	 */
	public function info()
	{
		global $db, $conf, $langs;

		$langs->load("bills");

		$form = new Form($db);

		$texte = $langs->trans('GenericNumRefModelDesc')."<br>\n";
		$texte .= '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
		$texte .= '<input type="hidden" name="token" value="'.newToken().'">';
		$texte .= '<input type="hidden" name="action" value="updateMask">';
		$texte .= '<input type="hidden" name="maskconstholiday" value="HOLIDAY_IMMACULATE_MASK">';
		$texte .= '<table class="nobordernopadding" width="100%">';

		$tooltip = $langs->trans("GenericMaskCodes", $langs->transnoentities("Holiday"), $langs->transnoentities("Holiday"));
		$tooltip .= $langs->trans("GenericMaskCodes2");
		$tooltip .= $langs->trans("GenericMaskCodes3");
		$tooltip .= $langs->trans("GenericMaskCodes4a", $langs->transnoentities("Holiday"), $langs->transnoentities("Holiday"));
		$tooltip .= $langs->trans("GenericMaskCodes5");

		$texte .= '<tr><td>'.$langs->trans("Mask").':</td>';
		$texte .= '<td class="right">'.$form->textwithpicto('<input type="text" class="flat minwidth175" name="maskholiday" value="'.getDolGlobalString('HOLIDAY_IMMACULATE_MASK').'">', $tooltip, 1, 1).'</td>';
		$texte .= '<td class="left" rowspan="2">&nbsp; <input type="submit" class="button button-edit" name="Button"value="'.$langs->trans("Modify").'"></td>';
		$texte .= '</tr>';
		$texte .= '</table>';
		$texte .= '</form>';

		return $texte;
	}

	/**
	 *	Return numbering example
	 *
	 *	@return     string      Example
	 */
	public function getExample()
	{
		global $conf, $langs, $user;

		$old_login = $user->login;
		$user->login = 'UUUUUUU';
		$numExample = $this->getNextValue($user, '');
		$user->login = $old_login;

		if (!$numExample) {
			$numExample = $langs->trans('NotConfigured');
		}
		return $numExample;
	}

	/**
	 *	Return next value
	 *
	 *	@param	Societe		$objsoc     third party object
	 *	@param	Object		$holiday	holiday object
	 *	@return string      			Value if OK, 0 if KO
	 */
	public function getNextValue($objsoc, $holiday)
	{
		global $db, $conf;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

		$mask = getDolGlobalString('HOLIDAY_IMMACULATE_MASK');

		if (!$mask) {
			$this->error = 'NotConfigured';
			return 0;
		}

		$numFinal = get_next_value($db, $mask, 'holiday', 'ref', '', $objsoc, $holiday->date_create);

		return  $numFinal;
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return next value
	 *
	 *  @param  User		$fuser     	User object
	 *  @param  Object		$objforref	Holiday object
	 *  @return string      			Value if OK, 0 if KO
	 */
	public function holiday_get_num($fuser, $objforref)
	{
		// phpcs:enable
		return $this->getNextValue($fuser, $objforref);
	}
}
