<?php
/* Copyright (C) 2016 delcroip <patrick@pmpd.eu>
 *
 * This program is free software;you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation;either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY;without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
// hide left menu
//$_POST['dol_hide_leftmenu'] = 1;
// Change this following line to use the correct relative path (../, ../../, etc)
include 'core/lib/includeMain.lib.php';
require_once 'core/lib/timesheet.lib.php';

$role_key = array_search('1', array_slice($apflows, 1));
if($apflows[1] == 0 && $role_key!== false) {
    // redirect to the correct page
    $role_key++;
    header("location:TimesheetOtherApproval.php?role=".$roles[$role_key]);//TOBETESTED
}
require_once 'core/lib/timesheet.lib.php';
require_once 'core/lib/generic.lib.php';
require_once 'class/TimesheetUserTasks.class.php';
if(!$user->rights->timesheet->approval) {
    $accessforbidden = accessforbidden("you need to have the approver rights");
}
//$userId = GETPOST('userid');
$userId = is_object($user)?$user->id:$user;
$action = GETPOST('action', 'alpha');
//should return the XMLDoc
$ajax = GETPOST('ajax', 'int');
$xml = GETPOST('xml', 'int');
if(!is_numeric($offset))$offset = 0;
$print = (GETPOST('optioncss', 'alpha') == 'print')?true:false;
$current = GETPOST('target', 'int');
if($current == ''){
    $current = 0;
}
//$toDate = GETPOST('toDate');
$timestamp = GETPOST('timestamp', 'alpha');
//$userid = is_object($user)?$user->id:$user;
// Load traductions files requiredby by page
//$langs->load("companies");
$langs->load("main");
$langs->load("projects");
$langs->load('timesheet@timesheet');
/*******************************************************************
* ACTIONS
*
* Put here all code to do according to value of "action" parameter
********************************************************************/
if($action== 'submit') {
    if(isset($_SESSION['timesheetAp'][$timestamp])) {
       // $_SESSION['timesheetAp'][$timestamp]['tsUser']
        $tsApproved = 0;
        $tsRejected = 0;
        $ret = 0;
        $errors = 0;
        $count = 0;
        $appflowOn = in_array('1', array_slice($apflows, 2));
        //$task_timesheet->db = $db;
        if(!empty($_POST['approval'])) {
            $notes = $_POST['note'];
            $notesTask = GETPOST('notesTask', 'array');
            $approvals = $_POST['approval'];
            foreach($_SESSION['timesheetAp'][$timestamp]['tsUser'] as $key => $tsUser) {
                $curTaskTimesheet = new TimesheetUserTasks($db);
                $count++;
                $curTaskTimesheet->fetch($key);
                $arrayTTA = $curTaskTimesheet->fetchTaskTimesheet();
                $curTaskTimesheet->updateActuals($arrayTTA, $notesTask);
                //if($approvals[$key]!=$tsUser)
                switch($approvals[$key]) {
                    case 'Approved':
                        $ret = $curTaskTimesheet->setStatus($user, (($appflowOn>0)?UNDERAPPROVAL:APPROVED), $key);
                        if(ret<0)$errors++;
                        else $tsApproved++;
                        break;
                    case 'Rejected':
                        $ret = $curTaskTimesheet->setStatus($user, REJECTED, $key);
                        if(ret<0)$errors++;
                        else $tsRejected++;
                        break;
                    case 'Submitted':
                    default:
                        break;
                }
                if($curTaskTimesheet->note!=$notes[$curTaskTimesheet->appId]) {
                    $curTaskTimesheet->note = $notes[$curTaskTimesheet->appId];
                    $curTaskTimesheet->update(user);
                }
            }
            if(($tsRejected+$tsApproved)>0) {
                $current--;
            }
            if($ret>=0) {
                if($tsApproved)setEventMessage($langs->transnoentitiesnoconv("NumberOfTimesheetApproved").$tsApproved);
                if($tsRejected)setEventMessage($langs->transnoentitiesnoconv("NumberOfTimesheetRejected").$tsRejected);
                if($errors)setEventMessage($langs->transnoentitiesnoconv("NumberOfErrors").$errors);
            } else {
                if($errors == 0) {
                    setEventMessage($langs->transnoentitiesnoconv("NothingChanged"), 'warning');
                } else {
                    setEventMessage($langs->transnoentitiesnoconv("InternalError").':'.$ret, 'errors');
                }
            }
        } else {
                setEventMessage($langs->transnoentitiesnoconv("NothingChanged"), 'warning');// shoudn't happend
        }
    } else {
            setEventMessage($langs->transnoentitiesnoconv("InternalError"), 'errors');
    }
}
if(!empty($timestamp)) {
    unset($_SESSION['timesheetAp'][$timestamp]);
}
$timestamp = getToken();
$subId = ($user->admin)?'all':getSubordinates($db, $userId, 2, array($userId), TEAM);
$selectList = getSelectAps($subId);
$level = intval($conf->global->TIMESHEET_MAX_APPROVAL);
$offset = 0;
if(is_array($selectList)&& count($selectList)) {
    if($current>=count($selectList))$current = 0;
    $offset = 0;
    for($i = 0;$i<$current;$i++)
    {
        $offset+= $selectList[$i]['count'];
    }
    $level = $selectList[$i]['count'];
}
$objectArray = getTStobeApproved($level, $offset, TEAM, $subId);
if(is_array($objectArray)) {
    $firstTimesheetUser = reset($objectArray);
    //$curUser = $firstTimesheetUser->userId;
    //$nextUser = $firstTimesheetUser->userId;
}
$i = 0;
//
/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/
/*
if($xml) {
    //renew timestqmp
    ob_clean();
   header("Content-type: text/xml;charset = utf-8");
  //  echo $task_timesheet->GetTimeSheetXML($userId, 5);//fixme
    ob_end_flush();
exit();
}*/
$task_timesheet = new TimesheetUserTasks($db);
$head = ($print)?'<style type = "text/css" >@page { size: A4 landscape;marks:none;margin: 1cm ;}</style>':'';
$morejs = array("/timesheet/core/js/jsparameters.php", "/timesheet/core/js/timesheet.js?".$conf->global->TIMESHEET_VERSION);
llxHeader($head, $langs->trans('Timesheet'), '', '', '', '', $morejs);
//calculate the week days
showTimesheetApTabs(TEAM);
echo '<div id = "Team" class = "tabBar">';
//tmstp = time();
if(is_object($firstTimesheetUser)) {
    if(!$print) echo getHTMLNavigation($optioncss, $selectList, $current);
    $Form .= $firstTimesheetUser->getHTMLFormHeader($ajax);
    foreach($objectArray as $key=> $task_timesheet) {

        if($i<$level) {
            $task_timesheet->fetchTaskTimesheet();
    //$ret+=$this->getTaskTimeIds();
    //FIXME module holiday should be activated ?
            $task_timesheet->fetchUserHoliday();
            $Form .= $task_timesheet->userName." - ".dol_print_date($task_timesheet->date_start, 'day');
            $Form .= $task_timesheet->getHTML(false, true);
            $_SESSION['timesheetAp'][$timestamp]['tsUser'][$task_timesheet->id] = $task_timesheet->status;
            if(!$print) {
                if($conf->global->TIMESHEET_ADD_DOCS == 1) {
                    dol_include_once('/core/class/html.formfile.class.php');
                    dol_include_once('/core/lib/files.lib.php');
                    $formfile = new FormFile($db);
                    $object = $task_timesheet;
                    $relativepathwithnofile = '';
                    $modulepart = 'timesheet';
                    $permission = 1;//$user->rights->timesheet->add;
                    $param = '&action = viewdoc&id='.$object->id;
                    $ref = dol_sanitizeFileName($object->ref);
                    $upload_dir = $conf->timesheet->dir_output.'/tasks/'.get_exdir($object->id, 2, 0, 0, $object, 'timesheet').$ref;
                    $filearray = dol_dir_list($upload_dir, 'files', 0, '', '\.meta$', $sortfield, (strtolower($sortorder) == 'desc'?SORT_DESC:SORT_ASC), 1);
                    ob_start();
                    $formfile->list_of_documents($filearray, $object, $modulepart, $param, 0, $relativepathwithnofile, $permission);
                    $Form .= ob_get_contents().'<br>'."\n";
                    ob_end_clean();
                }
                $Form .= '<label class = "butAction"><input type = "radio"  name = "approval['.$task_timesheet->id.']" value = "Approved" ><span>'.$langs->trans('approved').'</span></label>'."\n";/*FIXME*/
                $Form .= '<label class = "butAction"><input type = "radio"  name = "approval['.$task_timesheet->id.']" value = "Rejected" ><span>'.$langs->trans('rejected').'</span></label>'."\n";/*FIXME*/
                $Form .= '<label class = "butAction"><input type = "radio"  name = "approval['.$task_timesheet->id.']" value = "Submitted" checked ><span>'.$langs->trans('submitted').'</span></label>'."\n";/*FIXME*/
                $Form .= '<br><br><br>'."\n";
            }
            $i++;//use for the offset
        }
    }
   // $offset+=$i;
    if(!$print) {
        $Form .= $firstTimesheetUser->getHTMLFooterAp($current, $timestamp);
    } else {
        $Form .= '<table width = "100%"><tr><td align = "center">'.$langs->trans('customerSignature').'</td><td align = "center">'.$langs->trans('managerSignature').'</td><td align = "center">'.$langs->trans('employeeSignature').'</td></tr></table>';
    }
} else{
    $Form .= '<h1>'.$langs->trans('NothingToValidate').'</h1>';
    $staticTs = new TimesheetUserTasks($db);
    $Form .= $staticTs->getHTMLFooterAp($current, $timestamp);
}
//Javascript
$timetype = $conf->global->TIMESHEET_TIME_TYPE;
//$Form .= ' <script type = "text/javascript" src = "timesheet.js"></script>'."\n";
$Form .= '<script type = "text/javascript">'."\n\t";
$Form .= 'updateAll('.$conf->global->TIMESHEET_HIDE_ZEROS.');';
$Form .= "\n\t".'</script>'."\n";
// $Form .= '</div>';//TimesheetPage
print $Form;
echo '</div>';
// End of page
llxFooter();
$db->close();
/* Funciton to fect timesheet to be approuved.
*  @param    int               $level            number of ts to fetch
*  @param    int               $offset           number of ts to skip
*  @param    int               $role             team, project, customer ...
*  @param    array(int)             $role             if team, array fo subordinate_id, array of task_id for other
*  @return   array(task_timesheet)                     result
*/
function getTStobeApproved($level, $offset, $role, $subId)
{
    // FIXME LEVEL ISSUE
    global $db, $conf;
    if((!is_array($subId) || !count($subId)) && $subId!='all')return array();
    $byWeek = $conf->global->TIMESHEET_APPROVAL_BY_WEEK;
    //if($role = 'team')
    $sql = "SELECT *";
    if($byWeek == 2) {
        if($db->type!='pgsql') {
            $sql .= ", CONCAT(MONTH(date_start), '/', YEAR(date_start), '#', fk_userid) as usermonth";
        } else{
            $sql .= ", CONCAT(date_part('month', date_start), '/', date_part('year', date_start), '#', fk_userid) as usermonth";
        }
    }
    $sql .= " FROM ".MAIN_DB_PREFIX."project_task_timesheet as ts";
    $sql .= ' WHERE (ts.status='.SUBMITTED.' OR ts.status='.CHALLENGED.') ';
    switch($role) {
        case TEAM:
            if($subId!='all') $sql .= ' AND fk_userid in ('.implode(', ', $subId).')';
 //          $sql .= ' AND recipient = "'.$role.'"';
            break;
    }
    if($byWeek == 1) {
        $sql .= ' ORDER BY date_start DESC, fk_userid DESC';
    } elseif($byWeek == 0) {
        $sql .= ' ORDER BY fk_userid DESC, date_start DESC';
    } elseif($byWeek == 2) {
        if($db->type!='pgsql') {
            $sql .= ' ORDER BY YEAR(date_start) DESC, MONTH(date_start) DESC, fk_userid DESC';
        } else {
            $sql .= ' ORDER BY date_part(\'year\', date_start) DESC, ate_part(\'month\', date_start) DESC, fk_userid DESC';
        }
    }
    $sql .= ' LIMIT '.$level;
    $sql .= ' OFFSET '.$offset;
    dol_syslog("timesheet::getTStobeApproved sql=".$sql, LOG_DEBUG);
    $tsList = array();
    $resql = $db->query($sql);
    if($resql) {
        $num = $db->num_rows($resql);
        $i = 0;
        // Loop on each record found, so each couple (project id, task id)
        while($i < $num)
        {
            $error = 0;
            $obj = $db->fetch_object($resql);
            $tmpTs = NEW TimesheetUserTasks($db, $obj->fk_userid);
            $tmpTs->id = $obj->rowid;
            //$tmpTs->userId = $obj->fk_userid;
            $tmpTs->date_start = $tmpTs->db->jdate($obj->date_start);
            $tmpTs->date_start_end = $tmpTs->db->jdate($obj->date_start);
            $tmpTs->status = $obj->status;
            $tmpTs->planned_workload = $obj->planned_workload;
            $tmpTs->note = $obj->note;
            $tmpTs->date_creation = $tmpTs->db->jdate($obj->date_creation);
            $tmpTs->date_modification = $tmpTs->db->jdate($obj->date_modification);
            $tmpTs->user_creation = $obj->fk_user_creation;
            $tmpTs->user_modification = $obj->fk_user_modification;
            $tmpTs->whitelistmode = 2;// no impact
            $tmpTs->date_end = $tmpTs->db->jdate($obj->date_end);
            //}
            $i++;
            $tsList[] = $tmpTs;
        }
        $db->free($resql);
        return $tsList;
    } else {
            dol_print_error($db);
            return -1;
    }
}
/*
 * function to print the timesheet navigation header
 *
 *  @param    string               $optioncss            get print mode
 *  @param     int               $selectList           List of pages
 *  @param     object              $current                current page
 *  @return     string                                         HTML
 */
function getHTMLNavigation($optioncss, $selectList, $current = 0)
{
    global $langs, $db;
    $htmlSelect = '<select name = "target">';
    foreach($selectList as $key => $element) {
        $htmlSelect .= ' <option value = "'.$key.'" '.(($current == $key)?'selected':'').'>'.$element['label'].'</option>';
    }
    $htmlSelect .= '</select>';
    $form = new Form($db);
    $Nav = '<table class = "noborder" width = "50%">'."\n\t".'<tr>'."\n\t\t".'<th>'."\n\t\t\t";
    if($current!=0) {
        $Nav.= '<a href="?action=goTo&target='.($current-1).'"';
        if($optioncss != '')$Nav.=   '&amp;optioncss='.$optioncss;
        $Nav.=  '">  &lt;&lt;'.$langs->trans("Previous").' </a>'."\n\t\t";
    }
    $Nav .= "</th>\n\t\t<th>\n\t\t\t";
    $Nav.=  '<form name = "goTo" action="?action=goTo" method = "POST" >'."\n\t\t\t";
    $Nav.=   $langs->trans("GoTo").': '.$htmlSelect."\n\t\t\t";;
    $Nav.=  '<input type = "submit" value = "Go" /></form>'."\n\t\t</th>\n\t\t<th>\n\t\t\t";
    if($current<count($selectList)) {
        $Nav.=  '<a href="?action=goTo&target='.($current+1);
        if($optioncss != '') $Nav.=   '&amp;optioncss='.$optioncss;
        $Nav.=  '">'.$langs->trans("Next").' &gt;&gt;</a>';
    }
    $Nav .= "\n\t\t</th>\n\t</tr>\n </table>\n";
    return $Nav;
}
 /*
 * function to get the Approval elible for this user
 *
 *  @param    object            $db             database objet
 *  @param    array(int)/int        $userids        array of manager id
 *  @return  array(int => String)                                array(ID => userName)
 */
function getSelectAps($subId)
{
    if((!is_array($subId) || !count($subId)) && $subId!='all')return array();
    global $db, $langs, $conf;
    $sql = '';
    $sqlWhere .= ' WHERE ts.status  in ('.SUBMITTED.', '.CHALLENGED.')';
    if($subId!='all')$sqlWhere .= ' AND ts.fk_userid in ('.implode(', ', $subId).')';
    if($conf->global->TIMESHEET_APPROVAL_BY_WEEK == 1) {
        $sql = 'SELECT COUNT(ts.date_start) as nb, ts.date_start as id, ';
        $sql .= " DATE_FORMAT(ts.date_start, '".$langs->trans('Week')." %u(%m/%Y)') as label";
        $sql .= ' FROM '.MAIN_DB_PREFIX.'project_task_timesheet as ts';
        $sql .= ' JOIN '.MAIN_DB_PREFIX.'user as usr on ts.fk_userid = usr.rowid ';
        $sql .= $sqlWhere;
        $sql .= ' group by ts.date_start ORDER BY ts.date_start DESC';
    } elseif($conf->global->TIMESHEET_APPROVAL_BY_WEEK == 0) {
        $sql = 'SELECT COUNT(ts.rowid) as nb, ts.fk_userid as id, ';
        $sql .= " MAX(CONCAT(usr.firstname, ' ', usr.lastname)) as label";
        $sql .= ' FROM '.MAIN_DB_PREFIX.'project_task_timesheet as ts';
        $sql .= ' JOIN '.MAIN_DB_PREFIX.'user as usr on ts.fk_userid = usr.rowid ';
        $sql .= $sqlWhere;
        $sql .= ' group by ts.fk_userid ORDER BY ts.fk_userid DESC';
    } else{
        $sql = 'SELECT month, COUNT(rowid) as nb, month as id, ';
        $sql .= ' month as label';
        $sql .= ' FROM (SELECT DATE_FORMAT(ts.date_start, \' %m/%Y\') as month, ';
        $sql .= ' ts.rowid as rowid';
        $sql .= ' FROM '.MAIN_DB_PREFIX.'project_task_timesheet as ts';
        //$sql .= ' JOIN '.MAIN_DB_PREFIX.'user as usr on ts.fk_userid = usr.rowid ';
        $sql .= $sqlWhere.') AS T';
        $sql .= ' group by month ORDER BY  RIGHT(month, 4) DESC, month DESC';
    }
    dol_syslog('timesheetAp::getSelectAps ', LOG_DEBUG);
    $list = array();
    $resql = $db->query($sql);
    if($resql) {
        $i = 0;
        $j = 0;
        $num = $db->num_rows($resql);
        while($i<$num)
        {
            $obj = $db->fetch_object($resql);
            if($obj) {
                $j = 1;
                $nb = $obj->nb;
                // split the nb in x line to avoid going over the max approval
                while($nb>$conf->global->TIMESHEET_MAX_APPROVAL)
                {
                    $list[] = array("id"=>$obj->id, "label"=>$obj->label.' ('.$j."/".ceil($obj->nb/$conf->global->TIMESHEET_MAX_APPROVAL).')', "count"=>$conf->global->TIMESHEET_MAX_APPROVAL);
                    $nb-=$conf->global->TIMESHEET_MAX_APPROVAL;
                    $j++;
                }
                // at minimum a row shoud gnerate one option
                $list[] = array("id"=>$obj->id, "label"=>$obj->label.' '.(($obj->nb>$conf->global->TIMESHEET_MAX_APPROVAL)?'('.$j.'/'.ceil($obj->nb/$conf->global->TIMESHEET_MAX_APPROVAL).')':''), "count"=>$nb);
            }
            $i++;
        }
    } else {
        $error++;
        dol_print_error($db);
        $list = array();
    }
      return $list;
}
