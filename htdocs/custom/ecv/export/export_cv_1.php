<?php


// $elaboration_pv->fetch($id);
// $item = $elaboration_pv;
// $projet = $projet->fetch($item->fk_projet);
// $tache = $tache->fetch($item->fk_tache);

$html ='<html> <body> <style type="text/css">
table td{border: 1px solid black;padding: 6px;}

}
</style>';

$html.='<table  cellpadding="5px"; cellspadding="5px" >'; 
    $html.='<tr>';
        $html.='<td align="center" style="border:none" ><strong style="font-size:20px; color:grey;">'.$langs->trans("Curriculum Vitae").'</strong><br><br></td>'; 
    $html.='</tr>'; 
    $html.='<tr><br>'; 
    $html.='</tr>'; 
$html.='</table>'; 
$html.='<br>'; 
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

$filter='AND fk_ecv='.$id.' AND fk_user='.$ecv->fk_user;

$html.='<table cellpadding="5px"; cellspadding="5px" >'; 
    $html.='<tr>';
        // $html.='<td ><b>'.$langs->trans("ecv_fonction").' : </b>'.$objuserecv->job.'</td>'; 
        $html.='<td ><b>'.$langs->trans("ecv_fonction").' : </b>'.$ecv->poste.'</td>'; 
    $html.='</tr>'; 

    $html.='<tr>';
        $html.='<td><b>'.$langs->trans("ecv_societe_d").' : </b>'.$conf->global->MAIN_INFO_SOCIETE_NOM.'</td>'; 
    $html.='</tr>'; 

    $html.='<tr>';
        $html.='<td><b>'.$langs->trans("ecv_nom_user").' : </b>'.$objuserecv->firstname.' '.$objuserecv->lastname.'</td>'; 
    $html.='</tr>'; 

    $html.='<tr>';
        $html.='<td><b>'.$langs->trans("ecv_naissance_user").' : </b>'.dol_print_date($objuserecv->birth, 'day').'</td>'; 
    $html.='</tr>'; 

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
                $html.='<td><b>'.$langs->trans("ecv_permis").': </b>'.$typs.'</td>'; 
            $html.='</tr>'; 
        // }
    }
    
$html.='</table>';
$html.='<br><br>'; 




$filter='AND fk_ecv='.$id.' AND fk_user='.$ecv->fk_user;
$ecvqualifications->fetchAll('','',0,0,$filter);
if(count($ecvqualifications->rows) > 0){
$html.='<div ><strong style="font-size:15px">'.$langs->trans("ecv_qualifications").' </strong> </div>';
$html.='<br>'; 
$html.='<table  cellpadding="10px"; cellspadding="5px" >';
    $html.='<tr><td>';
        $html.='<ul>';
            foreach ($ecvqualifications->rows as $key => $value) {
                        $html.='<li>'.$value->name.'</li>';
            }
        $html.='</ul>';
    $html.='</td></tr>';
$html.='</table>';
$html.='<br><br>'; 
}


$ecvformations->fetchAll('','',0,0,$filter);
if(count($ecvformations->rows) > 0){
$html.='<div><strong style="font-size:15px">'.$langs->trans("ecv_formations").'  </strong></div>';
    $html.='<br>'; 
    $html.='<table  cellpadding="5px"; cellspadding="5px" >';  
        $html.='<tr><td align="center" ><b><i>'.$langs->trans("ecv_etablissement_formation").'</i></b></td><td align="center"><b><i>'.$langs->trans("ecv_periode_formation").'</i></b></td><td align="center"><b><i>'.$langs->trans("ecv_diplome_de").'</i></b></td></tr>';     
        foreach ($ecvformations->rows as $key => $value) {
             $d=explode(' ', $value->debut);
            $date_d = explode('-', $d[0]);
            $debut = $date_d[2]."/".$date_d[1]."/".$date_d[0];
            //fin
            $f=explode(' ', $value->fin);
            $date_f = explode('-', $f[0]);
            $fin = $date_f[2]."/".$date_f[1]."/".$date_f[0];
            if($value->nosjours == 1){
                $fin=$langs->trans("ecv_no_jours");
            }
            $html.='<tr>';
                $html.='<td>'.$value->etablissement.'</td>';
                $html.='<td border="none" align="center" >'.$debut.' - '.$fin.'</td>';
                $html.='<td>'.$value->intitule.'</td>';
            $html.='</tr>';
        }
    $html.='</table>';
$html.='<br><br>';
}

$ecvexperiences->fetchAll('','',0,0,$filter);
if(count($ecvexperiences->rows) > 0){
$html.='<div> <strong style="font-size:15px">'.$langs->trans("ecv_experiences").' </strong></div>';
    $html.='<br>'; 
    $html.='<table  cellpadding="5px"; cellspadding="5px">';
        $html.='<tr><td align="center"><b><i>'.$langs->trans("ecv_periode_experience").'</i></b></td><td align="center"><b><i>'.$langs->trans("ecv_societe_experience").'</i></b></td><td align="center"><b><i>'.$langs->trans("ecv_projets").'</i></b></td></tr>';     
        foreach ($ecvexperiences->rows as $key => $value) {
            //debut
            $d=explode(' ', $value->debut);
            $date_d = explode('-', $d[0]);
            $date_d = $date_d[2]."/".$date_d[1]."/".$date_d[0];
            //fin
            $f=explode(' ', $value->fin);
            $date_f = explode('-', $f[0]);
            $date_f = $date_f[2]."/".$date_f[1]."/".$date_f[0];
            if($value->nosjours == 1){
                $date_f=$langs->trans("ecv_no_jours");
            }
            $html.='<tr>';
                $html.='<td align="center">'.$date_d.' - '.$date_f.'</td>';
                $html.='<td>'.$value->societe.'</td>';
                $html.='<td>';
                $projets = [];
                if(!empty($value->projets))
                $projets=json_decode($value->projets);
                $html.='<ul>';
                    foreach ($projets as $key => $val) {
                        $html.='<li>'.$val.'</li>';
                    }
                $html.='</ul></td>';
            $html.='</tr>';
        }
    $html.='</table>'; 
$html.='<br><br>'; 
}

$ecvcertificats->fetchAll('','',0,0,$filter);
if(count($ecvcertificats->rows) > 0){
$html.='<div><strong style="font-size:15px">'.$langs->trans("ecv_certificats").' </strong></div>';
    $html.='<br>'; 
    $html.='<table  cellpadding="5px"; cellspadding="5px" border="none" >';       
        $html.='<tr><td align="center"><b><i>'.$langs->trans("ecv_intitule_certificat").'</i></b></td><td align="center"><b><i>Année</i></b></td><td align="center"><b><i>'.$langs->trans("ecv_etablissement_certificat").'</i></b></td></tr>';     
        foreach ($ecvcertificats->rows as $key => $value) {

            $html.='<tr  border="none" >';
                $html.='<td border="none" >'.$value->intitule.'</td>';
                $html.='<td border="none" align="center">'.$ecv->format_year($value->fin).'</td>';
                $html.='<td border="none" >'.$value->etablissement.'</td>';
            $html.='</tr>';
        }
    $html.='</table>'; 
$html.='<br><br>';
}

$ecvcompetances->fetchAll('','',0,0,$filter);
if(count($ecvcompetances->rows) > 0){
    $html.='<div><strong style="font-size:16px">'.$langs->trans("ecv_competences").' </strong></div>';
    $html.='<br>'; 
    $html.='<table  cellpadding="5px"; cellspadding="5px" border="none" >';       
        foreach ($ecvcompetances->rows as $key => $v) {
            $competances->fetch($v->fk_competance);
            $html.='<tr  border="none" >';
                $html.='<td> ';

                $minifile = getImageFileNameForSize($competances->icon, '');  
                $urlfile = $conf->ecv->dir_output.'/competances/'.$minifile;

                if(@getimagesize($urlfile))
                $html .= '<img alt="Photo" src="'.$urlfile.'" height="20px" >  ';
                $html .= $competances->name.'</td>';
                $html.='<td>';
                    for($i=1; $i <= 5; $i++){
                        if($i<=$v->value){
                            $html.='<img src="'.dol_buildpath('/ecv/img/no-etoile.png',2).'"  height="20px" >';
                        }
                        else
                            $html.='<img src="'.dol_buildpath('/ecv/img/null-etoile.png',2).'" height="20px" >';
                    }
                $html.='</td>';
            $html.='</tr>';
        }
    $html.='</table>';       
      
$html.='<br><br>'; 
}

// $ecvpermis->fetchAll('','',0,0,$filter);
// if(count($ecvpermis->rows) > 0){
//     $itemp = $ecvpermis->rows[0];
//     if($itemp->exist == "yes"){
//     $html.='<div><strong style="font-size:16px">'.$langs->trans("ecv_permis_circulations").' </strong></div>';
//     $html.='<br>'; 
//     $html.='<table  cellpadding="5px"; cellspadding="5px" border="none" >';
    
//     $html.='<tr  border="none" >';
//         $html.='<td style="width:30%;"> ';
//         $html.= '<b>'.$langs->trans("ecv_annee_acquisition").'</b>';
//         $html.='</td>';
//         $html.='<td style="width:70%;">';
//         $html.= ' '.$itemp->year;
//         $html.='</td>';
//     $html.='</tr>';
    
//     $html.='<tr  border="none" >';
//         $html.='<td style="width:30%;"> ';
//         $html.= '<b>'.$langs->trans("ecv_permis_type").'</b>';
//         $html.='</td>';
//         $html.='<td style="width:70%;">';
//         $html.= ' '.$itemp->type;
//         $html.='</td>';
//     $html.='</tr>';

//     $html.='</table>';       
      
//     $html.='<br><br>'; 
//     }
// }

$ecvlangues->fetchAll('','',0,0,$filter);
if(count($ecvlangues->rows) > 0){
    $html.='<div><strong style="font-size:15px">'.$langs->trans("ecv_langues").' </strong></div>';
    $html.='<br>'; 
    $html.='<table  cellpadding="5px"; cellspadding="5px" border="none" >';       
        $langs->loadLangs(array('admin', 'languages', 'other', 'companies', 'products', 'members', 'projects', 'hrm', 'agenda'));
        foreach ($ecvlangues->rows as $key => $v) {
            $name=$langs->trans("Language_".$v->name);
            $ar=explode('(', $name);
            if(count($ar)>0){
                $name=$ar[0];
            }
            $html.='<tr  border="none" >';
                $srcimg = picto_from_langcode($v->name);
                $urlc = DOL_MAIN_URL_ROOT;
                $urln = DOL_URL_ROOT;
                $srcimg = str_replace($urln, $urlc, $srcimg);
                $html.='<td>'.$srcimg.' '.$name.'</td>';
                $html.='<td>';
                    for($i=1; $i <= 5; $i++){
                        if($i<=$v->value){
                            $html.='<img src="'.dol_buildpath('/ecv/img/langue-no-null.png',2).'" height="15px" >  ';
                        }else
                            $html.='<img src="'.dol_buildpath('/ecv/img/langue-null.png',2).'" height="15px" >  ';
                    }
                $html.='</td>';
            $html.='</tr>';
        }
    $html.='</table>';       
}
$html.='</body></html>';

// echo $html;
// die();