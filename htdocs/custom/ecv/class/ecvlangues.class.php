<?php 
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php'; 

// dol_include_once('/ecv/class/ecv.class.php');
dol_include_once('/ecv/class/ecv.class.php');

class ecvlangues extends Commonobject{ 
	public $errors = array();
	public $rowid;
	public $ref;

	public $element='ecvlangues';
	public $table_element='ecvlangues';
	
	public function __construct($db){
		$this->db = $db;
		return 1;
    }

	public function create($echo_sql=0,$insert)
	{
		$sql  = "INSERT INTO " . MAIN_DB_PREFIX .get_class($this)." ( ";

		foreach ($insert as $column => $value) {
			$alias = (is_numeric($value)) ? "" : "'";
			if($value != ""){
				$sql_column .= " , `".$column."`";
				$sql_value .= " , ".$alias.$value.$alias;
			}
		}

		$sql .= substr($sql_column, 2)." ) VALUES ( ".substr($sql_value, 2)." )";
		$resql = $this->db->query($sql);
    	// print_r($sql);die();

		if (!$resql) {
			$this->db->rollback();
			$this->errors[] = 'Error '.get_class($this).' '. $this->db->lasterror();
			return 0;
		} 
		// return $this->db->db->insert_id;
		return $this->db->last_insert_id(MAIN_DB_PREFIX.$this->table_element);
	}

	public function update($id, array $data,$echo_sql=0)
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		if (!$id || $id <= 0)
			return false;

        $sql = 'UPDATE ' . MAIN_DB_PREFIX .get_class($this). ' SET ';

        if (count($data) && is_array($data))
            foreach ($data as $key => $val) {
                $val = is_numeric($val) ? $val : "'". $val ."'";
                $val = ($val == '') ? 'NULL' : $val;
                $sql .= "`". $key. "` = ". $val .",";
            }

        $sql  = substr($sql, 0, -1);
        $sql .= ' WHERE rowid = ' . $id;
        // die($sql);

        $resql = $this->db->query($sql);

		if (!$resql) {
			$this->db->rollback();
			$this->errors[] = 'Error '.get_class($this).' : '. $this->db->lasterror();
			return -1;
		} 
		return 1;
	}

	public function delete($echo_sql=0)
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql 	= 'DELETE FROM ' . MAIN_DB_PREFIX .get_class($this).' WHERE rowid = ' . $this->rowid;
		$resql 	= $this->db->query($sql);
		
		if (!$resql) {
			$this->db->rollback();
			$this->errors[] = 'Error '.get_class($this).' : '.$this->db->lasterror();
			return -1;
		} 

		return 1;
	}

    
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, $filter = '', $filtermode = 'AND')
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql = "SELECT * FROM ";
		$sql .= MAIN_DB_PREFIX .get_class($this);

		if (!empty($filter)) {
			$sql .= " WHERE 1>0 ".$filter;
		}
		
		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}

		if (!empty($limit)) {
			if($offset==1)
				$sql .= " limit ".$limit;
			else
				$sql .= " limit ".$offset.",".$limit;				
		}

		// die($sql);
		$this->rows = array();
		$resql = $this->db->query($sql);

		if ($resql) {
			$num = $this->db->num_rows($resql);

			while ($obj = $this->db->fetch_object($resql)) {
				$line = new stdClass;
                $line->id    = $obj->rowid;
				$line->rowid 	= $obj->rowid;
				$line->value 	=  $obj->value;
				$line->name 	    =  $obj->name;
				$line->fk_user 	    =  $obj->fk_user;
				$line->fk_ecv 	    =  $obj->fk_ecv;
				// ....

				$this->rows[] 	= $line;
			}
			$this->db->free($resql);

			return $num;
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}


	public function fetch($id)
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql = 'SELECT * FROM ' . MAIN_DB_PREFIX .get_class($this). ' WHERE rowid = ' . $id;
		$resql = $this->db->query($sql);
		if ($resql) {
			$numrows = $this->db->num_rows($resql);
			
			if ($numrows) {
				$obj 			= $this->db->fetch_object($resql);
                $this->id       = $obj->rowid;
				$this->rowid 	= $obj->rowid;
				$this->name 	=  $obj->name;
				$this->value 	=  $obj->value;
				$this->fk_user 	=  $obj->fk_user;
				$this->fk_ecv 	=  $obj->fk_ecv;
                // ....
			}

			$this->db->free($resql);

			if ($numrows) {
				return 1 ;
			} else {
				return 0;
			}
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
			return -1;
		}
	}

	public function select_with_filter($selected=0,$name='select_',$showempty=1,$val="rowid",$opt="label",$id='',$attr=''){

	    global $conf;

	    $moreforfilter = '';
	    $nodatarole = '';
	    $id = (!empty($id)) ? $id : $name;

	    $moreforfilter.='<select width="100%" '.$attr.' class="flat" id="select_'.$id.'" name="'.$name.'">';
	    if ($showempty) $moreforfilter.='<option value="0">&nbsp;</option>';

    	$sql = "SELECT ".$val.",".$opt." FROM ".MAIN_DB_PREFIX.get_class($this);
		//echo $sql."<br>";
    	$resql = $this->db->query($sql);

		if ($resql) {
			$num = $this->db->num_rows($resql);

			while ($obj = $this->db->fetch_object($resql)) {
				$moreforfilter.='<option value="'.$obj->$val.'"';
	            if ($obj->$val == $selected) $moreforfilter.=' selected';
	            $moreforfilter.='>'.$obj->$opt.'</option>';
			}
			$this->db->free($resql);
		}

	    $moreforfilter.='</select>';
	    $moreforfilter.='<style>#s2id_select_'.$name.'{ width: 100% !important;}</style>';
	    return $moreforfilter;
	}

    function getNomUrl($withpicto=0, $option='', $get_params='', $notooltip=0, $save_lastsearch_value=-1)
    {
        global $langs, $conf, $user;

        if (! empty($conf->dol_no_mouse_hover)) $notooltip=1;   // Force disable tooltips

        $result='';
        $label='';
        $url='';

        // if ($user->rights->propal->lire){}

        $linkclose='';
        if (empty($notooltip))
        {
            $linkclose.= ' title="'.dol_escape_htmltag($label, 1).'"';
            $linkclose.=' class="classfortooltip"';
        }
        $linkstart = "";
        $linkend = "";
        $result = "";

        if ($this->name) {
            $linkstart = '<a href="'.$url.'"';
            $linkstart.=$linkclose.'>';
            $linkend='</a>';

            $result .= $linkstart;
            if ($withpicto) 
                $result.= '<img height="16" src="'.dol_buildpath('/ecv/img/object_ecv.png',1).'" >&nbsp;';
            if ($withpicto != 2) $result.= 'cvbb'.$this->name;
        }

        $result .= $linkend;

        return $result;
    }








    public function getcountrows(){
        $tot = 0;
        $sql = "SELECT COUNT(rowid) as tot FROM ".MAIN_DB_PREFIX.get_class($this);
        $resql = $this->db->query($sql);

        if($resql){
            while ($obj = $this->db->fetch_object($resql)) 
            {
                $tot = $obj->tot;
            }
        }
        return $tot;
    }

    public function getdateformat($date,$time=true){
        
        $d = explode(' ', $date);
        $date = explode('-', $d[0]);
        $d2 = explode(':', $d[1]);
        $result = $date[2]."/".$date[1]."/".$date[0];
        if ($time) {
            $result .= " ".$d2[0].":".$d2[1];
        }
        return $result;
    }

    public function getYears($debut="debut")
    {
        $sql = 'SELECT YEAR('.$debut.') as years FROM ' . MAIN_DB_PREFIX.get_class($this);
        $resql = $this->db->query($sql);
        $years = array();
        if ($resql) {
            $num = $this->db->num_rows($resql);
            while ($obj = $this->db->fetch_object($resql)) {
                $years[$obj->years] = $obj->years;
            }
            $this->db->free($resql);
        }

        return $years;
    }

    public function getmonth($year)
    {
        $sql = 'SELECT MONTH(debut) as years FROM ' . MAIN_DB_PREFIX.get_class($this).' WHERE YEAR(debut) = '.$year;
        $resql  = $this->db->query($sql);
        $years = array();
        if ($resql) {
            $num = $this->db->num_rows($resql);
            while ($obj = $this->db->fetch_object($resql)) {
                $years[$obj->years] = $obj->years;
            }
            $this->db->free($resql);
        }

        return $years;
    }

	public function select_user($selected=0,$name='select_',$showempty=1,$val="rowid",$opt="label",$id=''){
	    global $conf;
	    $moreforfilter = '';
	    $nodatarole = '';
	    $id = (!empty($id)) ? $id : $name;
	    
	    $objet = "label";
	    $moreforfilter.='<select class="flat" id="'.$id.'" name="'.$name.'" '.$nodatarole.'>';
	    if ($showempty) $moreforfilter.='<option value="0">&nbsp;</option>';

    	$sql= "SELECT * FROM ".MAIN_DB_PREFIX."user";
    	$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			
			while ($obj = $this->db->fetch_object($resql)) {
				$moreforfilter.='<option value="'.$obj->$val.'" data-ref="'.$obj->$opt.'"';
	            if ($obj->$val == $selected) $moreforfilter.=' selected';
	            $moreforfilter.='>'.$obj->lastname.' '.$obj->firstname.'</option>';
			}
			$this->db->free($resql);
		}

	    $moreforfilter.='</select>';
	    $moreforfilter.='<style>#s2id_select_'.$name.'{ width: 100% !important;}</style>';
	    return $moreforfilter;
	}

	

	// public function select_langues($id_ecv,$name='',$autre=0){
	// 	$ecv = new ecv($this->db);
	// 	$ecv->fetch($id_ecv);
	   
	// 	$select.='<option value="0" ></option>';

	//    	$competance="SELECT DISTINCT name FROM ".MAIN_DB_PREFIX."ecvlangues";
	//    	$resql = $this->db->query($competance);
	// 	$num = $this->db->num_rows($resql);
	// 	if ($resql) {
	// 		while ($obj = $this->db->fetch_object($resql)) {
	// 			$select.='<option value="'.$obj->name.'" ';
	// 			if($obj->name == $name){ $select.='selected';}
	// 			$select.='>'.$obj->name.' </option>';
	// 		}
	// 	}
		
	// 	if($autre == 1){
	// 		$select.='<option value="Autre" > Autre </option>';
	// 	}
	//     return $select;
	// }

	public function id_langues($id=0){
		$ids=[];
	   	$langues="SELECT DISTINCT rowid FROM ".MAIN_DB_PREFIX."ecvlangues";
	   	if($id){
			$ecv = new ecv($this->db);
			$ecv->fetch($id);
	   		$langues.=" WHERE fk_ecv=".$id." AND fk_user =".$ecv->fk_user;
	   	}
	   	$resql = $this->db->query($langues);
	   	while ($obj = $this->db->fetch_object($resql)) {
			$ids[]=$obj->rowid;
		}
		return $ids;
	}
	function select_language($selected='', $htmlname='lang_id', $showauto=0, $filter=null, $showempty='', $multiple='')
	{
		global $langs;

		$langs_available=$langs->get_available_languages(DOL_DOCUMENT_ROOT,12);

		$out='';

		$arrys = "";
		if(!empty($multiple))
			$arrys = "[]";

		$out.= '<select '.$multiple.' class="lg flat'.($morecss?' '.$morecss:'').'" id="'.$htmlname.'" name="'.$htmlname.$arrys.'"'.($disabled?' disabled':'').'>';
		if ($showempty)
		{
			$out.= '<option value="0"';
			if ($selected == '') $out.= ' selected';
			$out.= '>';
			if ($showempty != '1') $out.=$showempty;
			else $out.='&nbsp;';
			$out.='</option>';
		}
		if ($showauto)
		{
			$out.= '<option value="auto"';
			if ($selected == 'auto') $out.= ' selected';
			$out.= '>'.$langs->trans("AutoDetectLang").'</option>';
		}

		asort($langs_available);

		foreach ($langs_available as $key => $value)
		{
		    $valuetoshow=$value;
		    if ($showcode) $valuetoshow=$key.' - '.$value;

			if ($filter && is_array($filter))
			{
				if ( ! array_key_exists($key, $filter))
				{
					$out.= '<option value="'.$key.'">'.$valuetoshow.'</option>';
				}
			}
			if(is_array($selected)){
				// print_r($selected);
				// die();
				if (isset($selected[$key]))
				{
					$out.= '<option value="'.$key.'" selected>'.$valuetoshow.'</option>';
				}
				else
				{
					$out.= '<option value="'.$key.'">'.$valuetoshow.'</option>';
				}
			}else{
				if ($selected == $key)
				{
					$out.= '<option value="'.$key.'" selected>'.$valuetoshow.'</option>';
				}
				else
				{
					$out.= '<option value="'.$key.'">'.$valuetoshow.'</option>';
				}
			}
		}
		$out.= '</select>';

		// Make select dynamic
        include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
        $out.= ajax_combobox($htmlname);

		return $out;
	}

	public function getEcvWithSelectdLangues($selectedlangs = ""){

		$sql="SELECT * FROM ".MAIN_DB_PREFIX."ecvlangues WHERE name IN (".$selectedlangs.")";
		$resql = $this->db->query($sql);
		$ecvslangs = array();
		$ecvids = array();
		if($resql){
			$num = $this->db->num_rows($resql);
            while ($obj = $this->db->fetch_object($resql)) {
                $ecvslangs[$obj->fk_ecv][] = $obj->name;
            }
            $this->db->free($resql);
		}else{
			$this->errors[] = 'Error ' . $this->db->lasterror();
			print_r($this->errors);
			die();
		}

		$srchlang = str_replace("'", "", $selectedlangs);
		$srchlang = explode(',', $srchlang);
		foreach ($ecvslangs as $ecvid => $langs) {
			if(count($srchlang) <= count($langs))
				$ecvids[] = $ecvid;
		}

		return $ecvids;

	}
} 


?>