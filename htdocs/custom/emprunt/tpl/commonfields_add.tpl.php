<?php
/* Copyright (C) 2017  Laurent Destailleur  <eldy@users.sourceforge.net>
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

?>
<!-- BEGIN PHP TEMPLATE commonfields_add.tpl.php -->
<?php

$object->fields = dol_sort_array($object->fields, 'position');
$include_users = $object->fetch_users_approver_emprunt();


foreach ($object->fields as $key => $val) {
	// Discard if extrafield is a hidden field on form
	if (abs($val['visible']) != 1 && abs($val['visible']) != 3) {
		continue;
	}

	if (array_key_exists('enabled', $val) && isset($val['enabled']) && !verifCond($val['enabled'])) {
		continue; // We don't want this field
	}

	print '<tr class="field_'.$key.'">';
	print '<td';
	print ' class="titlefieldcreate';
	if (isset($val['notnull']) && $val['notnull'] > 0) {
		print ' fieldrequired';
	}
	if ($val['type'] == 'text' || $val['type'] == 'html') {
		print ' tdtop';
	}
	print '"';
	print '>';
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
		$value = GETPOST($key, 'int');
	} elseif ($val['type'] == 'double') {
		$value = price2num(GETPOST($key, 'alphanohtml'));
	} elseif ($val['type'] == 'text' || $val['type'] == 'html') {
		$value = GETPOST($key, 'restricthtml');
	} elseif ($val['type'] == 'date') {
		$value = dol_mktime(12, 0, 0, GETPOST($key.'month', 'int'), GETPOST($key.'day', 'int'), GETPOST($key.'year', 'int'));
	} elseif ($val['type'] == 'datetime') {
		$value = dol_mktime(GETPOST($key.'hour', 'int'), GETPOST($key.'min', 'int'), 0, GETPOST($key.'month', 'int'), GETPOST($key.'day', 'int'), GETPOST($key.'year', 'int'));
	} elseif ($val['type'] == 'boolean') {
		$value = (GETPOST($key) == 'on' ? 1 : 0);
	} elseif ($val['type'] == 'price') {
		$value = price2num(GETPOST($key));
	} elseif ($key == 'lang') {
		$value = GETPOST($key, 'aZ09');
	}  else {
		$value = GETPOST($key, 'alphanohtml');
	}
	if (!empty($val['noteditable'])) {
		print $object->showOutputField($val, $key, $value, '', '', '', 0);
	} else {
		if ($key == 'lang') {
			print img_picto('', 'language', 'class="pictofixedwidth"');
			print $formadmin->select_language($value, $key, 0, null, 1, 0, 0, 'minwidth300', 2);
		} elseif($key == 'validate'){
			$value = $object->fetch_users_approver_emprunt();
			if (empty($include_users)) {
				print img_warning().' '.$langs->trans("NobodyHasPermissionToValidateHolidays");
			} else {
				$defaultselectuser = (empty($user->fk_user_holiday_validator) ? $user->fk_user : $user->fk_user_holiday_validator); // Will work only if supervisor has permission to approve so is inside include_users
				if (!empty($conf->global->HOLIDAY_DEFAULT_VALIDATOR)) {
					$defaultselectuser = $conf->global->HOLIDAY_DEFAULT_VALIDATOR; // Can force default approver
				}
				if (!GETPOST('validate', 'int') > 0) {
					$defaultselectuser = GETPOST('validate', 'int');
				}
				$s = $form->select_dolusers($defaultselectuser, "validate", 1, '', 0, $include_users, '', '0,'.$conf->entity, 0, 0, '', 0, '', 'minwidth200 maxwidth500');
				print img_picto('', 'user').$form->textwithpicto($s, $langs->trans("AnyOtherInThisListCanValidate"));
			}
			
		}else {
			print $object->showInputField($val, $key, $value, '', '', '', 0);
		}
	}

	// if (!GETPOST('validate', 'int') > 0) {
	// 	$defaultselectuser = GETPOST('validate', 'int');
	// }
	// $s = $form->select_dolusers($defaultselectuser, "validate", 1, '', 0, $include_users, '', '0,'.$conf->entity, 0, 0, '', 0, '', 'minwidth200 maxwidth500');
	// print img_picto('', 'user').$form->textwithpicto($s, $langs->trans("AnyOtherInThisListCanValidate"));

	

	print '</td>';
	print '</tr>';
}

?>
<!-- END PHP TEMPLATE commonfields_add.tpl.php -->
