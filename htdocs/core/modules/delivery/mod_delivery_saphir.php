<?php
/* Copyright (C) 2003-2007 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2008 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2007 Regis Houssin        <regis.houssin@inodbox.com>
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
 *	\file       htdocs/core/modules/delivery/mod_delivery_saphir.php
 *	\ingroup    expedition
 *	\brief      Fichier contenant la classe du modele de numerotation de reference de livraison Saphir
 */
require_once DOL_DOCUMENT_ROOT.'/core/modules/delivery/modules_delivery.php';

/**
 *	\class      mod_delivery_saphir
 *	\brief      Classe du modele de numerotation de reference de livraison Saphir
 */
class mod_delivery_saphir extends ModeleNumRefDeliveryOrder
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
	public $nom = 'Saphir';

	/**
	 * @var string model name
	 */
	public $name = 'Saphir';


	/**
	 *  Returns the description of the numbering model
	 *
	 *  @return     string      Texte descripif
	 */
	public function info()
	{
		global $conf, $langs, $db;

		$langs->load("bills");

		$form = new Form($db);

		$texte = $langs->trans('GenericNumRefModelDesc')."<br>\n";
		$texte .= '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
		$texte .= '<input type="hidden" name="token" value="'.newToken().'">';
		$texte .= '<input type="hidden" name="action" value="updateMask">';
		$texte .= '<input type="hidden" name="maskconstdelivery" value="DELIVERY_SAPHIR_MASK">';
		$texte .= '<table class="nobordernopadding" width="100%">';

		$tooltip = $langs->trans("GenericMaskCodes", $langs->transnoentities("Delivery"), $langs->transnoentities("Delivery"));
		$tooltip .= $langs->trans("GenericMaskCodes2");
		$tooltip .= $langs->trans("GenericMaskCodes3");
		$tooltip .= $langs->trans("GenericMaskCodes4a", $langs->transnoentities("Delivery"), $langs->transnoentities("Delivery"));
		$tooltip .= $langs->trans("GenericMaskCodes5");

		// Parametrage du prefix
		$texte .= '<tr><td>'.$langs->trans("Mask").':</td>';
		$texte .= '<td class="right">'.$form->textwithpicto('<input type="text" class="flat minwidth175" name="maskdelivery" value="'.$conf->global->DELIVERY_SAPHIR_MASK.'">', $tooltip, 1, 1).'</td>';

		$texte .= '<td class="left" rowspan="2">&nbsp; <input type="submit" class="button button-edit" name="Button"value="'.$langs->trans("Modify").'"></td>';

		$texte .= '</tr>';

		$texte .= '</table>';
		$texte .= '</form>';

		return $texte;
	}

	/**
	 *  Return an example of number
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
	 *  @param	Societe		$objsoc     	Object third party
	 *  @param  Object		$object			Object delivery
	 *  @return string      				Value if OK, 0 if KO
	 */
	public function getNextValue($objsoc, $object)
	{
		global $db, $conf;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

		// On defini critere recherche compteur
		$mask = $conf->global->DELIVERY_SAPHIR_MASK;

		if (!$mask) {
			$this->error = 'NotConfigured';
			return 0;
		}

		$numFinal = get_next_value($db, $mask, 'delivery', 'ref', '', $objsoc, $object->delivery_date);

		return  $numFinal;
	}


	/**
	 *  Return next free value
	 *
	 *  @param	Societe		$objsoc     Object third party
	 * 	@param	string		$objforref	Object for number to search
	 *  @return string      			Next free value
	 */
	public function getNumRef($objsoc, $objforref)
	{
		return $this->getNextValue($objsoc, $objforref);
	}


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return next free ref
	 *
	 *  @param	Societe		$objsoc      	Object thirdparty
	 *  @param  Object		$object			Objet livraison
	 *  @return string      				Texte descripif
	 */
	public function delivery_get_num($objsoc = 0, $object = '')
	{
		// phpcs:enable
		return $this->getNextValue($objsoc, $object);
	}
}
