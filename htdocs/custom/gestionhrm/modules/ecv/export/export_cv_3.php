<?php

$html='';
$ecv->fetch($id);
$user_cv = new User($db);
// $user_cv->fetch($ecv->fk_user);

$adherent_cv = new Adherent($db);
if($ecv->useroradherent == 'ADHERENT'){
    $adherent_cv->fetch($ecv->fk_user);
    $objuserecv = $adherent_cv;
}else{
    $user_cv->fetch($ecv->fk_user);
    $objuserecv = $user_cv;
}

 global $powererp_main_data_root;
// print_r($user_cv->getFullAddress(1,', ',$conf->global->MAIN_SHOW_REGION_IN_STATE_SELECT));die();
$filter='AND fk_ecv='.$id.' AND fk_user='.$ecv->fk_user;
$html.='<table style="width:100%; border:none !important;" cellpadding="5px"; cellspadding="5px" >'; 
    $html.='<tr>';
        $html.='<td style="background-color:#282e38; color:white; text-align:center;"><br><br><br><div><strong style="font-size:30px;"><span style="color:#dc172e;">'.$objuserecv->firstname.'</span><span> '.$objuserecv->lastname.'</span></strong></div> <div style="font-size:12px;">'.$ecv->poste.'</div>';
        $html.='<br><br></td>';
    $html.='</tr>';
    $html.='<tr>';
        $html.='<td style="width:35%;" align="center"><br><br>';
            if(!empty($objuserecv->photo) ){ 

                if($ecv->useroradherent == 'ADHERENT'){
                    $dir=$powererp_main_data_root.'/adherent/'.$ecv->fk_user.'/photos/';
                    if( file_exists($dir) && is_dir($dir) ){
                        $html.= '<img id="photo_user" src="'.DOL_DATA_ROOT.'/adherent/'.$ecv->fk_user.'/photos/'.$objuserecv->photo.'"  height="35mm" ><br>';
                    }
                }else{
                    $dir=$powererp_main_data_root.'/users/'.$ecv->fk_user.'/0/';
                    if( file_exists($dir) && is_dir($dir) ){
                        $html.= '<img id="photo_user" src="'.DOL_DATA_ROOT.'/users/'.$ecv->fk_user.'/0/'.$objuserecv->photo.'"  height="35mm" ><br>';
                    }
                    else{
                        $html.= '<img id="photo_user" src="'.DOL_DATA_ROOT.'/users/'.$ecv->fk_user.'/'.$objuserecv->photo.'"  height="35mm" ><br>';
                    }
                }
            }
            else{
                $html.= '<img id="photo_user" src="'.DOL_MAIN_URL_ROOT.'/public/theme/common/user_man.png" height="35mm" style="border-radius: 50%;border: 1px solid red;"><br>';
            }
            
        $html.='</td>';
        $html.='<td style="width:60%; "><br><br>';
            $html.='<div style="line-height:20px; font-size:15px; width:20px !important;"><b>'.$langs->trans("ecv_objectifs").',</b></div><br><span>'.$ecv->objectifs.'</span><br>';
        $html.='</td>';
        $html.='<td style="width:5%;"> </td>';
    $html.='</tr>';
$html.='</table>';
 $html.='<table style="width:100%; border:none !important;" cellpadding="5px"; cellspadding="5px" >'; 
    $html.='<tr>'; 
        $html.='<td style="width:35%">'; 
            $html.='<table style="width:100%; border:none !important;" cellpadding="3px"; cellspadding="3px" >'; 
                
                

                if($ecv->useroradherent == 'ADHERENT'){
                    if($objuserecv->phone_mobile){
                        $html.='<tr>';
                            $html.='';
                            $html.='<td><table width="100%"><tr> <td width="5%"></td><td width="15%" align="center"><img src="'.dol_buildpath('/ecv/images/tel_black.png',2).'" height="12px"></td><td width="80%">'.trim($objuserecv->phone_mobile).'</td></tr></table></td>';
                        $html.='</tr>';
                    }
                }else{
                    if($objuserecv->user_mobile){
                        $html.='<tr>';
                            $html.='';
                            $html.='<td><table width="100%"><tr> <td width="5%"></td><td width="15%" align="center"><img src="'.dol_buildpath('/ecv/images/tel_black.png',2).'" height="12px"></td><td width="80%">'.trim($objuserecv->user_mobile).'</td></tr></table></td>';
                        $html.='</tr>';
                    }
                }
                if($objuserecv->email){
                    $html.='<tr>';
                        $html.='';
                        $html.='<td ><table width="100%"><tr><td width="5%"></td><td width="15%" align="center"><img src="'.dol_buildpath('/ecv/images/email_2black.png',2).'"  width="12px"></td><td width="80%">'.trim($objuserecv->email).'</td></tr></table></td>';
                    $html.='</tr>';
                }
                if($objuserecv->address){
                    $html.='<tr>';
                        $html.='';
                        $html.='<td><table width="100%"><tr><td width="5%"></td><td width="15%" align="center"><img src="'.dol_buildpath('/ecv/images/adressblack.png',2).'"  height="12px"></td><td width="80%">'.$objuserecv->getFullAddress(1,', ',$conf->global->MAIN_SHOW_REGION_IN_STATE_SELECT).'</td></tr></table></td>';
                    $html.='</tr>';
                }
                $filter='AND fk_ecv='.$id.' AND fk_user='.$ecv->fk_user;
                $ecvpermis->fetchAll('','',0,0,$filter);
                if(count($ecvpermis->rows) > 0){
                    $typs = "";
                    foreach ($ecvpermis->rows as $key => $v) {
                        $typs .= $v->type.", ";
                    }
                    $typs = trim($typs,", ");
                    // $itemp = $ecvpermis->rows[0];
                    // if($itemp->exist == "yes"){
                        $html.='<tr>';
                            $html.='<td><table width="100%"><tr><td width="5%"></td><td width="15%" align="center"><img src="'.dol_buildpath('/ecv/images/permisblack.png',2).'"  height="12px"></td><td width="80%">'.$langs->trans("ecv_permis").': '.$typs.'</td></tr></table></td>';
                        $html.='</tr>';
                    // }
                }

                $html.='<br>';
                $html.='<br>';
                $filter='AND fk_ecv='.$id.' AND fk_user='.$ecv->fk_user;
                $ecvcompetances->fetchAll('','',0,0,$filter);
                if(count($ecvcompetances->rows) > 0){
                    $html.='<tr>';
                        $html.='<td width="5%"></td><td style="border-bottom-style:dashed; border-bottom-color:#dc172e; width:92%" align="left" >'; 
                            $html.= '<strong style="font-size:16px; color:#dc172e">'.$langs->trans("ecv_competences").': </strong>'; 
                        $html.='</td><td width="3%"></td>'; 
                    $html.='</tr>';
                     $html.='<tr>';
                        $html.='<td colspan="3"  style="line-height:1px;"></td>'; 
                    $html.='</tr>';
                    foreach ($ecvcompetances->rows as $val) {
                        $html.='<tr>';
                            $competance = new competances($db);
                            $competance->fetch($val->fk_competance);
                            if($competance->icon){
                                $html.='<td style="width:7%;"></td><td style="width:58%;"align="left">';

                                $minifile = getImageFileNameForSize($competance->icon, '');  
                                $urlfile = $conf->ecv->dir_output.'/competances/'.$minifile;
                                if(@getimagesize($urlfile))
                                $html.='<img alt="Photo" src="'.$urlfile.'" height="13px" > ';


                                $html .=$competance->name.'</td>';

                                $html.='<td style="width:35%;" align="right">';
                                    for($i=1; $i <= 5; $i++){
                                        if($i <= $val->value){
                                            $html.='<img src="'.dol_buildpath('/ecv/img/etoile-red.png',2).'"  height="13px" >';
                                        }
                                        else
                                            $html.='<img src="'.dol_buildpath('/ecv/img/null-etoile.png',2).'" height="13px" >';
                                    }
                                $html.='</td>';
                            }
                        $html.='</tr>';
                    }
                }
                    $html.='<tr><td colspan="2"><span style="line-height:10px"></span></td></tr>';

                $ecvlangues->fetchAll('','',0,0,$filter);
                if(count($ecvlangues->rows) > 0){
                    $html.='<tr>';
                        $html.='<td width="5%"></td> <td style="border-bottom-style:dashed; border-bottom-color:#dc172e; width:92%;" align="left" ><strong style="font-size:16px; color:#dc172e; ">'.$langs->trans("ecv_langues").':</strong></td><td width="3%"></td>';
                    $html.='</tr>';
                    $filter='AND fk_ecv='.$id.' AND fk_user='.$ecv->fk_user;
                    $langs->loadLangs(array('admin', 'languages', 'other', 'companies', 'products', 'members', 'projects', 'hrm', 'agenda'));
                     $html.='<tr>';
                        $html.='<td colspan="3"  style="line-height:1px;"></td>'; 
                    $html.='</tr>';
                    foreach ($ecvlangues->rows as $val) {
                        $html.='<tr>';
                            $name=$langs->trans("Language_".$val->name);
                            $ar=explode('(', $name);
                            if(count($ar)>0){
                                $name=$ar[0];
                            }
                            $srcimg = picto_from_langcode($val->name);
                            $urlc = DOL_MAIN_URL_ROOT;
                            $urln = DOL_URL_ROOT;
                            $srcimg = str_replace($urln, $urlc, $srcimg);
                            $html.='<td width="5%"></td> <td width="55%" >'.$srcimg.'&nbsp;&nbsp;'.$name.'</td>';
                            $html.='<td width="40%" align="right">';
                                for($i=1; $i <= 5; $i++){
                                    if($i<=$val->value){
                                        $html.='<img src="'.dol_buildpath('/ecv/img/langue-red.png',2).'" height="10px" > ';
                                    }else{
                                        $html.='<img src="'.dol_buildpath('/ecv/img/langue-null.png',2).'" height="10px" > ';
                                    }
                                }
                            $html.='</td><td width="5%"></td>';
                        $html.='</tr>';
                    }
                }
                $html.='<tr><td colspan="2"><span style="line-height:10px"></span></td></tr>';

                $filter='AND fk_ecv='.$id.' AND fk_user='.$ecv->fk_user;
                $ecvqualifications->fetchAll('','',0,0,$filter);
                if(count($ecvqualifications->rows) > 0){
                    $html.='<tr>';
                        $html.='<td width="5%"></td> <td style="border-bottom-style:dashed; border-bottom-color:#dc172e; width:85%" align="left" ><br><br><strong style="font-size:16px;color:#dc172e;">'.$langs->trans("ecv_qualification").':</strong></td>';
                    $html.='<td width="10%"></td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        $html.='<td width="5%"></td>';
                        $html.='<td align="left" width="95%" >';
                            foreach ($ecvqualifications->rows as $key => $value) {
                                $html.='<table cellpadding="0px"; cellspadding="0px" width="100%"><tr><td width="5%" style="" align="center"><b>-</b></td><td width="90%" align="left">'.$value->name.'</td></tr></table><br>';
                            }
                        $html.='</td>';
                    $html.='</tr>';
                }

                
            $html.='</table>'; 
        $html.='</td>'; 

        $html.='<td style="width:65%"><br>'; 
           $html.='<table style="width:100%; border:none !important;"   >';
                
                $ecvexperiences->fetchAll('','',0,0,$filter);
                if(count($ecvexperiences->rows)>0){
                    $html.='<tr>';
                        $html.='<td style="border-bottom-style:dashed; border-bottom-color:#dc172e; width:95%"><strong style="font-size:16px; color:#dc172e;" >'.$langs->trans("ecv_experiences").':</strong></td>';

                        $html.='<td style="width:5%"></td>';
                    $html.='</tr>';
                    $html.='<tr><td colspan="2"><span style="line-height:20px"></span></td></tr>';
                    foreach ($ecvexperiences->rows as $key => $value) {
                        $html.='<tr>';
                            $html.='<td style="width:18%; color:grey;" align="right"><span><b> '.$ecv->format_year($value->debut).'-';
                            if($value->nosjours == 1){
                                $html.=$langs->trans("ecv_no_jours");
                            }
                            elseif($value->nosjours == 0){ 
                                $html.=$ecv->format_year($value->fin);
                            }
                            $html.=' </b></span> <img src="'.dol_buildpath('/ecv/img/langue-red.png',2).'" height="8px" height="8px" > </td>';

                            $html.='<td style="width:67%"><b>'.$value->societe.'</b>';
                            $html.='</td>';
                            $html.='<td style="width:10%" align="right">';
                                if($value->profile_soc){
                                    $minifile = getImageFileNameForSize($value->profile_soc, '');  
                                    $urlfile = $conf->ecv->dir_output.'/'.$ecv->rowid.'/experiences/'.$value->rowid.'/'.$minifile;
                                    if(@getimagesize($urlfile))
                                    $html.='<img src="'.$urlfile.'" height="15px" >';

                                }
                            $html.='</td>';
                            $html.='<td style="width:5%">';
                            $html.='</td>';
                        $html.='</tr>';
                        $html.='<tr>';
                            $html.='<td style="width:18%;" align="center">';
                            $html.='</td>';
                            $html.='<td style="width:77%"><div><span style="text-align:justify;">'.nl2br($value->description).'</span></div></td>';
                            $html.='<td style="width:5%"></td>';
                        $html.='</tr>';
                        $html.='<tr><td colspan="2"><span style="line-height:20px"></span></td></tr>';
                    }
                }

                $ecvformations->fetchAll('','',0,0,$filter);
                if(count($ecvformations->rows)>0){
                    $html.='<tr>';
                        $html.='<td style="border-bottom-style:dashed; border-bottom-color:#dc172e; width:95%"><strong style="font-size:16px; color:#dc172e;">'.$langs->trans("ecv_formations").':</strong></td>';
                        $html.='<td style="width:5%"></td>';
                    $html.='</tr>';
                    $html.='<tr><td colspan="2"><span style="line-height:20px"></span></td></tr>';
                    foreach ($ecvformations->rows as $key => $value) {
                        
                        $html.='<tr>';
                            $html.='<td style="width:18%; color:grey;" align="center"><b>'.$ecv->format_year($value->debut).'-';
                             if($value->nosjours == 1){
                                $html.=$langs->trans("ecv_no_jours");
                            }
                            elseif($value->nosjours == 0){  
                                $html.=$ecv->format_year($value->fin);
                            }
                            $html.=' </b> <img src="'.dol_buildpath('/ecv/img/langue-red.png',2).'" height="8px" ></td>';
                            $html.='<td style="width:67%"><b>'.$value->etablissement.'</b><br>'.$value->filiere;
                            $html.='<br></td>';
                            $html.='<td style="width:5%"></td>';
                        $html.='</tr>';
                    }
                }

                $ecvcertificats->fetchAll('','',0,0,$filter);
                if(count($ecvcertificats->rows)>0){
                    $html.='<tr>';
                        $html.='<td  style="border-bottom-style:dashed; border-bottom-color:#dc172e; width:95%"><strong style="font-size:16px; color:#dc172e;">'.$langs->trans("ecv_certificats").':</strong></td>';
                    $html.='</tr>';
                    $html.='<tr><td colspan="2"><span style="line-height:20px"></span></td></tr>';
                    foreach ($ecvcertificats->rows as $key => $value) {
                        
                        $html.='<tr>';
                            $html.='<td style="width:18%; color:grey;" align="center"><b>'.$ecv->format_year($value->debut).'-'.$ecv->format_year($value->fin).' </b> <img src="'.dol_buildpath('/ecv/img/langue-red.png',2).'" height="8px" ></td>';
                            $html.='<td style="width:82%"><b>'.$value->intitule.'</b>';
                            $html.='</td>';
                        $html.='</tr>';
                        $html.='<tr>';
                        $html.='<td style="width:18%;" align="center">';

                        $minifile = getImageFileNameForSize($value->copie, '');  
                        $urlfile = $conf->ecv->dir_output.'/'.$ecv->rowid.'/certificats/'.$value->rowid.'/'.$minifile;
                        if(@getimagesize($urlfile))
                        $html .= '<img src="'.$urlfile.'" height="35px" >';
                        
                        $html.='</td>';
                        $html.='<td style="width:77%"><span style="text-align:justify;">'.nl2br($value->description).'</span></td>';
                        $html.='<td style="width:5%"></td>';
                        $html.='</tr>';
                        $html.='<tr><td colspan="2"><span style="line-height:5px"></span></td></tr>';
                    }
                }

            $html.='</table>';
        $html.='</td>'; 
    $html.='</tr>'; 
$html.='</table>'; 

    
 