<?php
/* Copyright (C) 2005      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2016 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin        <regis.houssin@inodbox.com>
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
 *	\file       htdocs/comm/mailing/class/mailing.class.php
 *	\ingroup    mailing
 *	\brief      File of class to manage emailings module
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';


/**
 *	Class to manage emailings module
 */
class Mailing extends CommonObject
{
	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'mailing';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'mailing';

	/**
	 * @var string String with name of icon for myobject. Must be the part after the 'object_' into object_myobject.png
	 */
	public $picto = 'email';

	/**
	 * @var string title
	 */
	public $title;

	/**
	 * @var string subject
	 */
	public $sujet;

	/**
	 * @var string body
	 */
	public $body;

	/**
	 * @var int number of email
	 */
	public $nbemail;

	/**
	 * @var string background color
	 */
	public $bgcolor;

	/**
	 * @var string background image
	 */
	public $bgimage;

	/**
	 * @var int status
	 */
	public $statut; // Status 0=Draft, 1=Validated, 2=Sent partially, 3=Sent completely

	/**
	 * @var string email from
	 */
	public $email_from;

	/**
	 * @var string email to
	 */
	public $sendto;

	/**
	 * @var string email reply to
	 */
	public $email_replyto;

	/**
	 * @var string email errors to
	 */
	public $email_errorsto;

	/**
	 * @var string first joined file
	 */
	public $joined_file1;

	/**
	 * @var string second joined file
	 */
	public $joined_file2;

	/**
	 * @var string third joined file
	 */
	public $joined_file3;

	/**
	 * @var string fourth joined file
	 */
	public $joined_file4;

	/**
	 * @var int id of user create
	 */
	public $user_creation;

	/**
	 * @var int id of user create
	 * @deprecated
	 */
	public $user_creat;

	/**
	 * @var int id of user validate
	 */
	public $user_validation;

	/**
	 * @var int id of user validate
	 * @deprecated
	 */
	public $user_valid;

	/**
	 * @var integer|string date_creation
	 * @deprecated
	 */
	public $date_creat;

	/**
	 * @var integer|string date_creation
	 */
	public $date_creation;

	/**
	 * @var int date validate
	 * @deprecated
	 */
	public $date_valid;

	/**
	 * @var int date validate
	 */
	public $date_validation;

	/**
	 * @var array extraparams
	 */
	public $extraparams = array();

	/**
	 * @var array statut dest
	 */
	public $statut_dest = array();

	/**
	 * @var array statuts
	 */
	public $statuts = array();

	/**
	 * @var array substitutionarray
	 */
	public $substitutionarray;

	/**
	 * @var array substitutionarrayfortest
	 */
	public $substitutionarrayfortest;


	/**
	 *  Constructor
	 *
	 *  @param      DoliDb      $db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;

		// List of language codes for status
		$this->statuts[0] = 'MailingStatusDraft';
		$this->statuts[1] = 'MailingStatusValidated';
		$this->statuts[2] = 'MailingStatusSentPartialy';
		$this->statuts[3] = 'MailingStatusSentCompletely';

		$this->statut_dest[-1] = 'MailingStatusError';
		$this->statut_dest[0] = 'MailingStatusNotSent';
		$this->statut_dest[1] = 'MailingStatusSent';
		$this->statut_dest[2] = 'MailingStatusRead';
		$this->statut_dest[3] = 'MailingStatusReadAndUnsubscribe'; // Read but ask to not be contacted anymore
	}

	/**
	 *  Create an EMailing
	 *
	 *  @param	User	$user 		Object of user making creation
	 *  @return int	   				-1 if error, Id of created object if OK
	 */
	public function create($user)
	{
		global $conf, $langs;

		// Check properties
		if ($this->body === 'InvalidHTMLString') {
			$this->error = 'InvalidHTMLString';
			return -1;
		}

		$this->db->begin();

		$this->title = trim($this->title);
		$this->email_from = trim($this->email_from);

		if (!$this->email_from) {
			$this->error = $langs->trans("ErrorMailFromRequired");
			return -1;
		}

		$now = dol_now();

		$sql = "INSERT INTO ".MAIN_DB_PREFIX."mailing";
		$sql .= " (date_creat, fk_user_creat, entity)";
		$sql .= " VALUES ('".$this->db->idate($now)."', ".((int) $user->id).", ".((int) $conf->entity).")";

		if (!$this->title) {
			$this->title = $langs->trans("NoTitle");
		}

		dol_syslog("Mailing::Create", LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result) {
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."mailing");

			if ($this->update($user) > 0) {
				$this->db->commit();
			} else {
				$this->error = $this->db->lasterror();
				$this->db->rollback();
				return -1;
			}

			return $this->id;
		} else {
			$this->error = $this->db->lasterror();
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 *  Update emailing record
	 *
	 *  @param  User	$user 		Object of user making change
	 *  @return int				    < 0 if KO, > 0 if OK
	 */
	public function update($user)
	{
		// Check properties
		if ($this->body === 'InvalidHTMLString') {
			$this->error = 'InvalidHTMLString';
			return -1;
		}

		$sql = "UPDATE ".MAIN_DB_PREFIX."mailing ";
		$sql .= " SET titre = '".$this->db->escape($this->title)."'";
		$sql .= ", sujet = '".$this->db->escape($this->sujet)."'";
		$sql .= ", body = '".$this->db->escape($this->body)."'";
		$sql .= ", email_from = '".$this->db->escape($this->email_from)."'";
		$sql .= ", email_replyto = '".$this->db->escape($this->email_replyto)."'";
		$sql .= ", email_errorsto = '".$this->db->escape($this->email_errorsto)."'";
		$sql .= ", bgcolor = '".($this->bgcolor ? $this->db->escape($this->bgcolor) : null)."'";
		$sql .= ", bgimage = '".($this->bgimage ? $this->db->escape($this->bgimage) : null)."'";
		$sql .= " WHERE rowid = ".(int) $this->id;

		dol_syslog("Mailing::Update", LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result) {
			return 1;
		} else {
			$this->error = $this->db->lasterror();
			return -1;
		}
	}

	/**
	 *	Get object from database
	 *
	 *	@param	int		$rowid      Id of emailing
	 *	@return	int					<0 if KO, >0 if OK
	 */
	public function fetch($rowid)
	{
		global $conf;

		$sql = "SELECT m.rowid, m.titre as title, m.sujet, m.body, m.bgcolor, m.bgimage";
		$sql .= ", m.email_from, m.email_replyto, m.email_errorsto";
		$sql .= ", m.statut, m.nbemail";
		$sql .= ", m.fk_user_creat, m.fk_user_valid";
		$sql .= ", m.date_creat";
		$sql .= ", m.date_valid";
		$sql .= ", m.date_envoi";
		$sql .= ", m.extraparams";
		$sql .= " FROM ".MAIN_DB_PREFIX."mailing as m";
		$sql .= " WHERE m.rowid = ".(int) $rowid;

		dol_syslog(get_class($this)."::fetch", LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result) {
			if ($this->db->num_rows($result)) {
				$obj = $this->db->fetch_object($result);

				$this->id = $obj->rowid;
				$this->ref = $obj->rowid;
				$this->statut = $obj->statut;
				$this->nbemail = $obj->nbemail;
				$this->title = $obj->title;

				$this->sujet = $obj->sujet;
				if (!empty($conf->global->FCKEDITOR_ENABLE_MAILING) && dol_textishtml(dol_html_entity_decode($obj->body, ENT_COMPAT | ENT_HTML5))) {
					$this->body = dol_html_entity_decode($obj->body, ENT_COMPAT | ENT_HTML5);
				} else {
					$this->body = $obj->body;
				}

				$this->bgcolor = $obj->bgcolor;
				$this->bgimage = $obj->bgimage;

				$this->email_from = $obj->email_from;
				$this->email_replyto = $obj->email_replyto;
				$this->email_errorsto = $obj->email_errorsto;

				$this->user_creat = $obj->fk_user_creat;
				$this->user_creation = $obj->fk_user_creat;
				$this->user_valid = $obj->fk_user_valid;
				$this->user_validation = $obj->fk_user_valid;

				$this->date_creat = $this->db->jdate($obj->date_creat);
				$this->date_creation = $this->db->jdate($obj->date_creat);
				$this->date_valid = $this->db->jdate($obj->date_valid);
				$this->date_validation = $this->db->jdate($obj->date_valid);
				$this->date_envoi = $this->db->jdate($obj->date_envoi);

				$this->extraparams = (array) json_decode($obj->extraparams, true);

				return 1;
			} else {
				dol_syslog(get_class($this)."::fetch Erreur -1");
				return -1;
			}
		} else {
			dol_syslog(get_class($this)."::fetch Erreur -2");
			return -2;
		}
	}


	/**
	 *	Load an object from its id and create a new one in database
	 *
	 *  @param	User	$user		    User making the clone
	 *	@param  int		$fromid     	Id of object to clone
	 *	@param	int		$option1		1=Clone content, 0=Forget content
	 *	@param	int		$option2		1=Clone recipients
	 *	@return	int						New id of clone
	 */
	public function createFromClone(User $user, $fromid, $option1, $option2)
	{
		global $langs;

		$error = 0;

		$object = new Mailing($this->db);

		$this->db->begin();

		// Load source object
		$object->fetch($fromid);
		$object->id = 0;
		$object->statut = 0;

		// Clear fields
		$object->title = $langs->trans("CopyOf").' '.$object->title.' '.dol_print_date(dol_now());

		// If no option copy content
		if (empty($option1)) {
			// Clear values
			$object->nbemail            = 0;
			$object->sujet              = '';
			$object->body               = '';
			$object->bgcolor            = '';
			$object->bgimage            = '';

			//$object->email_from         = '';		// We do not reset from email because it is a mandatory value
			$object->email_replyto      = '';
			$object->email_errorsto     = '';

			$object->user_creat         = $user->id;
			$object->user_valid         = '';

			$object->date_creat         = '';
			$object->date_valid         = '';
			$object->date_envoi         = '';
		}

		// Create clone
		$object->context['createfromclone'] = 'createfromclone';
		$result = $object->create($user);

		// Other options
		if ($result < 0) {
			$this->error = $object->error;
			$this->errors = array_merge($this->errors, $object->errors);
			$error++;
		}

		if (!$error) {
			// Clone recipient targets
			if (!empty($option2)) {
				require_once DOL_DOCUMENT_ROOT.'/core/modules/mailings/modules_mailings.php';

				$mailing_target = new MailingTargets($this->db);

				$target_array = array();

				$sql = "SELECT fk_contact,";
				$sql .= " lastname,";
				$sql .= " firstname,";
				$sql .= " email,";
				$sql .= " other,";
				$sql .= " source_url,";
				$sql .= " source_id ,";
				$sql .= " source_type";
				$sql .= " FROM ".MAIN_DB_PREFIX."mailing_cibles";
				$sql .= " WHERE fk_mailing = ".((int) $fromid);

				$result = $this->db->query($sql);
				if ($result) {
					if ($this->db->num_rows($result)) {
						while ($obj = $this->db->fetch_object($result)) {
							$target_array[] = array(
								'fk_contact'=>$obj->fk_contact,
								'lastname'=>$obj->lastname,
								'firstname'=>$obj->firstname,
								'email'=>$obj->email,
								'other'=>$obj->other,
								'source_url'=>$obj->source_url,
								'source_id'=>$obj->source_id,
								'source_type'=>$obj->source_type
							);
						}
					}
				} else {
					$this->error = $this->db->lasterror();
					return -1;
				}

				$mailing_target->addTargetsToDatabase($object->id, $target_array);
			}
		}

		unset($object->context['createfromclone']);

		// End
		if (!$error) {
			$this->db->commit();
			return $object->id;
		} else {
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 *  Validate emailing
	 *
	 *  @param	User	$user      	Objet user qui valide
	 * 	@return	int					<0 if KO, >0 if OK
	 */
	public function valid($user)
	{
		$now = dol_now();

		$sql = "UPDATE ".MAIN_DB_PREFIX."mailing ";
		$sql .= " SET statut = 1, date_valid = '".$this->db->idate($now)."', fk_user_valid=".$user->id;
		$sql .= " WHERE rowid = ".((int) $this->id);

		dol_syslog("Mailing::valid", LOG_DEBUG);
		if ($this->db->query($sql)) {
			return 1;
		} else {
			$this->error = $this->db->lasterror();
			return -1;
		}
	}


	/**
	 *  Delete emailing
	 *
	 *  @param	int		$rowid      Id if emailing to delete
	 *  @param	int		$notrigger	Disable triggers
	 *  @return int         		>0 if OK, <0 if KO
	 */
	public function delete($rowid, $notrigger = 0)
	{
		global $user;

		$this->db->begin();

		$sql = "DELETE FROM ".MAIN_DB_PREFIX."mailing";
		$sql .= " WHERE rowid = ".((int) $rowid);

		dol_syslog("Mailing::delete", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$res = $this->delete_targets();
			if ($res <= 0) {
				$this->db->rollback();
				$this->error = $this->db->lasterror();
				return -1;
			}
		} else {
			$this->db->rollback();
			$this->error = $this->db->lasterror();
			return -1;
		}

		if (!$notrigger) {
			$result = $this->call_trigger('MAILING_DELETE', $user);
			if ($result < 0) {
				$this->db->rollback();
				return -1;
			}
		}

		$this->db->commit();
		return 1;
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Delete targets emailing
	 *
	 *  @return int       1 if OK, 0 if error
	 */
	public function delete_targets()
	{
		// phpcs:enable
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."mailing_cibles";
		$sql .= " WHERE fk_mailing = ".((int) $this->id);

		dol_syslog("Mailing::delete_targets", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$this->refreshNbOfTargets();

			return 1;
		} else {
			$this->error = $this->db->lasterror();
			return 0;
		}
	}


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Change status of each recipient
	 *
	 *	@param	User	$user      	Objet user qui valide
	 *  @return int         		<0 if KO, >0 if OK
	 */
	public function reset_targets_status($user)
	{
		// phpcs:enable
		$sql = "UPDATE ".MAIN_DB_PREFIX."mailing_cibles";
		$sql .= " SET statut = 0";
		$sql .= " WHERE fk_mailing = ".((int) $this->id);

		dol_syslog("Mailing::reset_targets_status", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			return 1;
		} else {
			$this->error = $this->db->lasterror();
			return -1;
		}
	}


	/**
	 *  Count number of target with status
	 *
	 *  @param  string	$mode   Mode ('alreadysent' = Sent success or error, 'alreadysentok' = Sent success, 'alreadysentko' = Sent error)
	 *  @return int        		Nb of target with status
	 */
	public function countNbOfTargets($mode)
	{
		$sql = "SELECT COUNT(rowid) as nb FROM ".MAIN_DB_PREFIX."mailing_cibles";
		$sql .= " WHERE fk_mailing = ".((int) $this->id);
		if ($mode == 'alreadysent') {
			$sql .= " AND statut <> 0";
		} elseif ($mode == 'alreadysentok') {
			$sql .= " AND statut > 0";
		} elseif ($mode == 'alreadysentko') {
			$sql .= " AND statut = -1";
		} else {
			$this->error = 'BadValueForParameterMode';
			return -2;
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$obj = $this->db->fetch_object($resql);
			if ($obj) {
				return $obj->nb;
			}
		} else {
			$this->error = $this->db->lasterror();
			return -1;
		}
		return 0;
	}

	/**
	 *  Refresh denormalized value ->nbemail into emailing record
	 *  Note: There is also the method update_nb into modules_mailings that is used for this.
	 *
	 *  @return int        		<0 if KO, >0 if OK
	 */
	public function refreshNbOfTargets()
	{
		$sql = "SELECT COUNT(rowid) as nb FROM ".MAIN_DB_PREFIX."mailing_cibles";
		$sql .= " WHERE fk_mailing = ".((int) $this->id);

		$resql = $this->db->query($sql);
		if ($resql) {
			$obj = $this->db->fetch_object($resql);
			if ($obj) {
				$nbforupdate = $obj->nb;

				$sql = 'UPDATE '.MAIN_DB_PREFIX.'mailing SET nbemail = '.((int) $nbforupdate);
				$sql .= ' WHERE rowid = '.((int) $this->id);

				$resqlupdate = $this->db->query($sql);
				if (! $resqlupdate) {
					$this->error = $this->db->lasterror();
					return -1;
				} else {
					$this->nbemail = (int) $nbforupdate;
				}
			}
		} else {
			$this->error = $this->db->lasterror();
			return -1;
		}

		return 1;
	}

	/**
	 *  Return a link to the object card (with optionally the picto)
	 *
	 *	@param	int		$withpicto					Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *	@param	string	$option						On what the link point to ('nolink', ...)
	 *  @param	int  	$notooltip					1=Disable tooltip
	 *  @param  string  $morecss            		Add more css on link
	 *  @param  int     $save_lastsearch_value    	-1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *	@return	string								String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
	{
		global $db, $conf, $langs, $hookmanager;
		global $powererp_main_authentication, $powererp_main_demo;
		global $menumanager;

		if (!empty($conf->dol_no_mouse_hover)) {
			$notooltip = 1; // Force disable tooltips
		}

		$result = '';
		$companylink = '';

		$label = '<u>'.$langs->trans("ShowEMailing").'</u>';
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('Ref').':</b> '.$this->ref;

		$url = DOL_URL_ROOT.'/comm/mailing/card.php?id='.$this->id;

		if ($option != 'nolink') {
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) {
				$add_save_lastsearch_values = 1;
			}
			if ($add_save_lastsearch_values) {
				$url .= '&save_lastsearch_values=1';
			}
		}

		$linkclose = '';
		if (empty($notooltip)) {
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
				$label = $langs->trans("ShowEMailing");
				$linkclose .= ' alt="'.dol_escape_htmltag($label, 1).'"';
			}
			$linkclose .= ' title="'.dol_escape_htmltag($label, 1).'"';
			$linkclose .= ' class="classfortooltip'.($morecss ? ' '.$morecss : '').'"';
		} else {
			$linkclose = ($morecss ? ' class="'.$morecss.'"' : '');
		}

		$linkstart = '<a href="'.$url.'"';
		$linkstart .= $linkclose.'>';
		$linkend = '</a>';

		$result .= $linkstart;
		if ($withpicto) {
			$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
		}
		if ($withpicto != 2) {
			$result .= $this->ref;
		}
		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action;
		$hookmanager->initHooks(array('emailingdao'));
		$parameters = array('id'=>$this->id, 'getnomurl' => &$result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) {
			$result = $hookmanager->resPrint;
		} else {
			$result .= $hookmanager->resPrint;
		}

		return $result;
	}

	/**
	 *  Return label of status of emailing (draft, validated, ...)
	 *
	 *  @param  int		$mode           0=Long label, 1=Short label, 2=Picto+Short label, 3=Picto, 4=Picto+Short label, 5=Short label+Picto, 6=Picto+Long label, 7=Very short label+Picto
	 *  @return string        			Label
	 */
	public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->statut, $mode);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return the label of a given status
	 *
	 *  @param	int		$status        	Id status
	 *  @param  int		$mode           0=Long label, 1=Short label, 2=Picto+Short label, 3=Picto, 4=Picto+Short label, 5=Short label+Picto, 6=Picto+Long label, 7=Very short label+Picto
	 *  @return string        			Label
	 */
	public function LibStatut($status, $mode = 0)
	{
		// phpcs:enable
		global $langs;
		$langs->load("mailing");

		$labelStatus = $langs->transnoentitiesnoconv($this->statuts[$status]);
		$labelStatusShort = $langs->transnoentitiesnoconv($this->statuts[$status]);

		$statusType = 'status'.$status;
		if ($status == 2) {
			$statusType = 'status3';
		}
		if ($status == 3) {
			$statusType = 'status6';
		}

		return dolGetStatus($labelStatus, $labelStatusShort, '', $statusType, $mode);
	}


	/**
	 *  Return the label of a given status  of a recipient
	 *  TODO Add class mailin_target.class.php
	 *
	 *  @param	int		$status        	Id status
	 *  @param  int		$mode           0=Long label, 1=Short label, 2=Picto+Short label, 3=Picto, 4=Picto+Short label, 5=Short label+Picto, 6=Picto+Long label, 7=Very short label+Picto
	 *  @param	string	$desc			Desc error
	 *  @return string        			Label
	 */
	public static function libStatutDest($status, $mode = 0, $desc = '')
	{
		global $langs;
		$langs->load("mails");

		$labelStatus = array();
		$labelStatusShort = array();

		$labelStatus[-1] = $langs->transnoentitiesnoconv('MailingStatusError');
		$labelStatus[0] = $langs->transnoentitiesnoconv('MailingStatusNotSent');
		$labelStatus[1] = $langs->transnoentitiesnoconv('MailingStatusSent');
		$labelStatus[2] = $langs->transnoentitiesnoconv('MailingStatusRead');
		$labelStatus[3] = $langs->transnoentitiesnoconv('MailingStatusNotContact');
		$labelStatusShort[-1] = $langs->transnoentitiesnoconv('MailingStatusError');
		$labelStatusShort[0] = $langs->transnoentitiesnoconv('MailingStatusNotSent');
		$labelStatusShort[1] = $langs->transnoentitiesnoconv('MailingStatusSent');
		$labelStatusShort[2] = $langs->transnoentitiesnoconv('MailingStatusRead');
		$labelStatusShort[3] = $langs->transnoentitiesnoconv('MailingStatusNotContact');

		$statusType = 'status'.$status;
		if ($status == -1) {
			$statusType = 'status8';
		}
		if ($status == 1) {
			$statusType = 'status6';
		}
		if ($status == 2) {
			$statusType = 'status4';
		}

		$param = array();
		if ($status == - 1) {
			$param = array('badgeParams'=>array('attr'=>array('title'=>$desc)));
		}
		return dolGetStatus($labelStatus[$status], $labelStatusShort[$status], '', $statusType, $mode, '', $param);
	}
}
