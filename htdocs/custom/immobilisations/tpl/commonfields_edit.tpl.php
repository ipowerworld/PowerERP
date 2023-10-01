<?php
/* Copyright (C) 2017-2019  Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *
 * Need to have following variables defined:
 * $object (invoice, order, ...)
 * $action
 * $conf
 * $langs
 * $form
 */

// Protection to avoid direct call of template
if (empty($conf) || !is_object($conf)) {
	print "Error, template page can't be called as URL";
	exit;
}
if (!is_object($form)) {
	$form = new Form($db);
}

?>
<!-- BEGIN PHP TEMPLATE commonfields_edit.tpl.php -->
<?php

	/**     IF action ===    transferIMMO     **/
	if ($action === 'transferImmo') {

		$object->fields = dol_sort_array($object->fields, 'position');

		foreach ($object->fields as $key => $val) {
			// Discard if extrafield is a hidden field on form
			if (abs($val['visible']) != 1 && abs($val['visible']) != 3 && abs($val['visible']) != 4) {
				continue;
			}

			if (array_key_exists('enabled', $val) && isset($val['enabled']) && !verifCond($val['enabled'])) {
				continue; // We don't want this field
			}

			if (in_array($key, array('fk_product', 'accountancy_code_asset', 'accountancy_code_depreciation_asset', 'accountancy_code_depreciation_expense', 'family', 'periode','description','note_public','note_private','date_creation','date_consommation','tms','fk_user_creat','fk_user_modif','import_key','status','approvment','ref','entity','label','fk_fournisseur','fk_product','fk_Project','fk_categorie','amount_ht','amount_vat'))) {
				continue; // Ref and status are already in dol_banner
			}


			// if($val['label'] === 'Code comptable (immobilisation)' || 
			// 	$val['label'] === 'Code comptable (compte d\'amortissement)' ||
			// 	$val['label'] === 'Code comptable (compte de charges d\'amortissement)' || 
			// 	// $val['label'] === 'Approbateur' || 
			// 	$val['label'] === 'Famille' || 
			// 	$val['label'] === 'Periodicité' || 
			// 	$val['label'] === 'Produit'
			// 	// || $val['labelé'] === 'Fournisseur'
			// 	) {
			// 	continue;
			// }

			print '<tr class="field_'.$key.'"><td';
			print ' class="titlefieldcreate';
			if (isset($val['notnull']) && $val['notnull'] > 0) {
				print ' fieldrequired';
			}
			if (preg_match('/^(text|html)/', $val['type'])) {
				print ' tdtop';
			}
			print '">';
			if (!empty($val['help'])) {
				print $form->textwithpicto($langs->trans($val['label']), $langs->trans($val['help']));
			} else {
				print $langs->trans($val['label']);
			}
			print '</td>';
			print '<td class="valuefieldcreate">';
			if (!empty($val['picto'])) {
				print img_picto('', $val['picto'], '', false, 0, 0, '', 'pictofixedwidth');
			}
			if (in_array($val['type'], array('int', 'integer'))) {
				$value = GETPOSTISSET($key) ?GETPOST($key, 'int') : $object->$key;
			} elseif ($val['type'] == 'double') {
				$value = GETPOSTISSET($key) ? price2num(GETPOST($key, 'alphanohtml')) : $object->$key;
			} elseif (preg_match('/^(text|html)/', $val['type'])) {
				$tmparray = explode(':', $val['type']);
				if (!empty($tmparray[1])) {
					$check = $tmparray[1];
				} else {
					$check = 'restricthtml';
				}
				$value = GETPOSTISSET($key) ? GETPOST($key, $check) : $object->$key;
			} elseif ($val['type'] == 'price') {
				$value = GETPOSTISSET($key) ? price2num(GETPOST($key)) : price2num($object->$key);
			} elseif ($key == 'lang') {
				$value = GETPOSTISSET($key) ? GETPOST($key, 'aZ09') : $object->lang;
			} else {
				$value = GETPOSTISSET($key) ? GETPOST($key, 'alpha') : $object->$key;
			}
			//var_dump($val.' '.$key.' '.$value);
			if (!empty($val['noteditable'])) {
				print $object->showOutputField($val, $key, $value, '', '', '', 0);
			} else {
				if ($key == 'lang') {
					print img_picto('', 'language', 'class="pictofixedwidth"');
					print $formadmin->select_language($value, $key, 0, null, 1, 0, 0, 'minwidth300', 2);
				} else {
					print $object->showInputField($val, $key, $value, '', '', '', 0);
				}
			}
			print '</td>';
			print '</tr>';
		}

	} else {

		foreach ($object->fields as $key => $val) {
			// Discard if extrafield is a hidden field on form
			if (abs($val['visible']) != 1 && abs($val['visible']) != 3 && abs($val['visible']) != 4) {
				continue;
			}

			if (array_key_exists('enabled', $val) && isset($val['enabled']) && !verifCond($val['enabled'])) {
				continue; // We don't want this field
			}

			if (in_array($key, array('fk_product', 'accountancy_code_asset', 'accountancy_code_depreciation_asset', 'accountancy_code_depreciation_expense', 'family', 'periode'))) {
				continue; // Ref and status are already in dol_banner
			}

			// if($val['label'] === 'Code comptable (immobilisation)' || 
			// 	$val['label'] === 'Code comptable (compte d\'amortissement)' ||
			// 	$val['label'] === 'Code comptable (compte de charges d\'amortissement)' || 
			// 	// $val['label'] === 'Approbateur' || 
			// 	$val['label'] === 'Famille' || 
			// 	$val['label'] === 'Periodicité' || 
			// 	$val['label'] === 'Produit'
			// 	// || $val['labelé'] === 'Fournisseur'
			// 	) {
			// 	continue;
			// }

			print '<tr class="field_'.$key.'"><td';
			print ' class="titlefieldcreate';
			if (isset($val['notnull']) && $val['notnull'] > 0) {
				print ' fieldrequired';
			}
			if (preg_match('/^(text|html)/', $val['type'])) {
				print ' tdtop';
			}
			print '">';
			if (!empty($val['help'])) {
				print $form->textwithpicto($langs->trans($val['label']), $langs->trans($val['help']));
			} else {
				print $langs->trans($val['label']);
			}
			print '</td>';
			print '<td class="valuefieldcreate">';
			if (!empty($val['picto'])) {
				print img_picto('', $val['picto'], '', false, 0, 0, '', 'pictofixedwidth');
			}
			if (in_array($val['type'], array('int', 'integer'))) {
				$value = GETPOSTISSET($key) ?GETPOST($key, 'int') : $object->$key;
			} elseif ($val['type'] == 'double') {
				$value = GETPOSTISSET($key) ? price2num(GETPOST($key, 'alphanohtml')) : $object->$key;
			} elseif (preg_match('/^(text|html)/', $val['type'])) {
				$tmparray = explode(':', $val['type']);
				if (!empty($tmparray[1])) {
					$check = $tmparray[1];
				} else {
					$check = 'restricthtml';
				}
				$value = GETPOSTISSET($key) ? GETPOST($key, $check) : $object->$key;
			} elseif ($val['type'] == 'price') {
				$value = GETPOSTISSET($key) ? price2num(GETPOST($key)) : price2num($object->$key);
			} elseif ($key == 'lang') {
				$value = GETPOSTISSET($key) ? GETPOST($key, 'aZ09') : $object->lang;
			} else {
				$value = GETPOSTISSET($key) ? GETPOST($key, 'alpha') : $object->$key;
			}
			//var_dump($val.' '.$key.' '.$value);
			if (!empty($val['noteditable'])) {
				print $object->showOutputField($val, $key, $value, '', '', '', 0);
			} else {
				if ($key == 'lang') {
					print img_picto('', 'language', 'class="pictofixedwidth"');
					print $formadmin->select_language($value, $key, 0, null, 1, 0, 0, 'minwidth300', 2);
				} else {
					print $object->showInputField($val, $key, $value, '', '', '', 0);
				}
			}
			print '</td>';
			print '</tr>';
		}

	}
	


?>
<!-- END PHP TEMPLATE commonfields_edit.tpl.php -->
