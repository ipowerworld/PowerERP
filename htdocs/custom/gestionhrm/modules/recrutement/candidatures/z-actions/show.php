<?php

if ($action == 'confirm_delete' && GETPOST('confirm') == 'yes' ) {
    
    if (!$id || $id <= 0) {
        header('Location: ./card.php?action=request&error=dalete_failed&id='.$id);
        exit;
    }

    $page  = GETPOST('page');
    $id  = GETPOST('id');

    $candidature->fetch($id);
    $cvs = new cv_recrutement($db);
    $cvs->fetchAll('','',0,0,' AND candidature='.$id);
    $error = $candidature->delete();
    if ($error == 1) {
        if(count($cvs->rows)>0){
            for ($i=0; $i < count($cvs->rows); $i++) { 
                $items=$cvs->rows[$i];
                $cv = new cv_recrutement($db);
                $cv->fetch($items->rowid);
                $cv_delete  = $cv->delete();
                if($cv_delete){
                    $upload_dir = $conf->recrutement->dir_output.'/candidatures/'.$id.'/cv/'.$items->rowid.'/'.$items->fichier;
                    if(file_exists($upload_dir)){
                        unlink($upload_dir);
                    }
                }
            }
        }
        header('Location: index.php?delete='.$id.'&page='.$page);
        exit;
    }
    else {      
        header('Location: card.php?delete=1&page='.$page);
        exit;
    }
}


if( ($id && empty($action)) || $action == "delete" ){
    
    // $h = 0;
    // $head = array();
    // $head[$h][0] = dol_buildpath("/avancementtravaux/card.php?id=".$id, 1);
    // $head[$h][1] = $langs->trans($modname);
    // $head[$h][2] = 'affichage';
    // $h++;
    // dol_fiche_head($head,'affichage',"",0,"logo@avancementtravaux");


    if($action == "delete"){
        print $form->formconfirm("card.php?id=".$id."&page=".$page,$langs->trans('Confirmation') , $langs->trans('msgconfirmdelet'),"confirm_delete", 'index.php?page='.$page, 0, 1);
    }

    // if (!$user->rights->avancementtravaux->gestion->consulter) {
    //     accessforbidden();
    // }
    // $avancementtravaux->fetchAll('','',0,0,' and rowid = '.$id);
    $candidature->fetch($id);
    $item = $candidature;
    $departements = new departements($db);
    $etiquette    = new etiquettes($db);
    $origine      = new origines($db);
    $contact      = new Contact($db);
    $user_        = new User($db);
    $etapes       = new etapescandidature($db);

    // $extrafields = new ExtraFields($db);
    // $extralabels=$extrafields->fetch_name_optionals_label($item->table_element);
    print '<form method="post" action="'.$_SERVER["PHP_SELF"].'"  class="showcandidature formrecrutements">';

    print '<input type="hidden" name="confirm" itemue="no" id="confirm" />';
    print '<input type="hidden" name="id" value="'.$id.'" />';
    print '<input type="hidden" name="page" value="'.$page.'" />';


    // print '<div class="createemployrecrutbutt">';
    //      if(empty($candidature->employe)){

    //         print '<a class="butAction" style="background-color:green !important;color:white !important; float: right;" data-id="'.$item->rowid.'" id="cree_employe" >'.$langs->trans('cree_employe').'</a>';
    //     }
    //     if($candidature->employe && $item->refuse==0){
    //         print '<a class="butAction" style="background-color:green !important;color:white !important; float: right; " data-id="'.$item->rowid.'" id="refuse" >'.$langs->trans('refuse').'</a>';
    //     }
    //     if($item->refuse){
    //         print '<a class="butAction" style="background-color:green !important;color:white !important; float: right; " data-id="'.$item->rowid.'" id="relance" >'.$langs->trans('relance').'</a>';
    //     }
    // print '</div>';



    // print '<br>';
    // print '<div style="width:100%;padding:5px;margin-bottom:20px;background-color:rgb(60,70,100);"><strong style="font-size:16px;color:white;">'.$langs->trans('etapes').'</strong></div>';
    // print '<div class="alletapesrecru">';
    //     $etapes->fetchAll();
    //     // print_r($etapes);die();
    //     $nb=count($etapes->rows);
    //     $etapecandid ='';
    //     for ($i=0; $i < $nb; $i++) { 
    //         $etap=$etapes->rows[$i];

    //         $etapecandid .= '<label class="etapes" >';
    //             $etapecandid .= '<input type="radio" id="'.$etap->rowid.'"  style="display:none;" value="'.$etap->rowid.'" name="etape" class="etapes">';
    //             $etapecandid .= ' <span class="radio"></span>';
    //             $etapecandid .= '<span style="font-size:14px"> '.$langs->trans($etap->label).'</span>';
    //         $etapecandid .= '</label>';

    //     }  
    //         $etapecandid = str_replace('<input type="radio" id="'.$item->etape.'"', '<input type="radio" id="'.$item->etape.'" checked ', $etapecandid);

    //         print $etapecandid ;
       
    // print '</div>';
    // print '<br>';
    // print '<div style="width:100%; padding:5px; margin:10px 0 10px;background-color:rgb(60,70,100);"><strong style="font-size:16px;color:white;">'.$langs->trans('sujet').'</strong></div>';
    
    // Nom & etiquettes
    print '<table class="border nc_table_ headertablerecru" width="100%">';
        print '<tbody>';

            print '<tr>';
                // print '<td colspan="" class="sujetrecrutementmod">'.$item->sujet.'</td>';
                print '<td colspan="" class="sujetrecrutementmod">'.$item->nom.' '.$item->prenom.'</td>';
                print '<td colspan="" class="alletapesrecru">';
                    $etapes->fetchAll();
                    // print_r($etapes);die();
                    $nb=count($etapes->rows);
                    $etapecandid ='';
                    for ($i=0; $i < $nb; $i++) { 
                        $etap=$etapes->rows[$i];

                        $etapecandid .= '<label class="etapes" >';
                            $etapecandid .= '<input type="radio" id="'.$etap->rowid.'"  style="display:none;" value="'.$etap->rowid.'" name="etape" class="etapes">';
                            $etapecandid .= ' <span class="radio"></span>';
                            $etapecandid .= '<span style="font-size:14px"> '.$langs->trans($etap->label).'</span>';
                        $etapecandid .= '</label>';

                    }  
                    $etapecandid = str_replace('<input type="radio" id="'.$item->etape.'"', '<input type="radio" id="'.$item->etape.'" checked ', $etapecandid);

                    print $etapecandid ;
                print '</td>';
            print '</tr>';

            print '<tr>';
                // print '<td class="firsttd200px">'.$langs->trans('nom_candidat').'</td>';
                print '<td >';
                // print '<span class="nom_complet">'.$item->prenom.' '.$item->nom.'</span>';
                    print '<span class="etiquettesrecru">';
                    if($item->etiquettes){
                        // $etiquettes=json_decode($item->etiquettes);
                        $etiquettes=explode(",", $item->etiquettes);
                        if($etiquettes !=''){
                            foreach ($etiquettes as $key => $value) {
                                $etiquette->fetch($value);
                                print '<span style="padding:5px; background-color:'.$etiquette->color.'; color:white;border-radius:5px;"> '.$etiquette->label.'</span>&nbsp;';
                            }
                        }
                    }
                    print '</span>';
                print '</td>';
                // print_r($item);die();
            // print '</tr>';

            // print '<tr>';
                // print '<th style="text-align:left;">'.$langs->trans('etiquettes').'</th>';
                // print '<td>';
                // if($item->etiquettes){
                //     $etiquettes=json_decode($item->etiquettes);
                //     foreach ($etiquettes as $key => $value) {
                //         $etiquette->fetch($value);
                //         print '<span style="padding:5px; background-color:'.$etiquette->color.'; color:white;border-radius:5px;"> '.$etiquette->label.'</span>&nbsp;';
                //     }
                // }
                // print '</td>';
            print '</tr>';
        print '</tbody>';
    print '</table>';
    
    print '<div class="clear" style="margin-top: 4px;"></div>';

    // info
    print '<div class="fichecenter" >';
        print '<div class="fichehalfleft">';
            print '<table class="border nc_table_" width="100%">';
                print '<body>';
                    print '<tr>';
                        print '<td style="text-align:left;" class=" firsttd200px">'.$langs->trans('contact').'</td>';
                        print '<td >';
                        if($item->contact){
                            $contact->fetch($item->contact);
                            // print $contact->firstname.' '.$contact->lastname;
                            print $contact->getNomUrl(1);
                        }
                        print '</td>';
                    print '</tr>';

                    print '<tr>';
                        print '<td style="text-align:left;">'.$langs->trans('email_contact').'</td>';
                        print '<td >'.$item->email.'</td>';
                    print '</tr>';

                    print '<tr>';
                        print '<td style="text-align:left;">'.$langs->trans('tel').'</td>';
                        print '<td >'.$item->tel.'</td>';
                    print '</tr>';

                    print '<tr>';
                        print '<td style="text-align:left;">'.$langs->trans('mobile').'</td>';
                        print '<td >'.$item->mobile.'</td>';
                    print '</tr>';

                    print '<tr>';
                        print '<td style="text-align:left;">'.$langs->trans('niveau').'</td>';
                        print '<td >'.$langs->trans($item->niveau).'</td>';
                    print '</tr>';

                print '</tbody>';
            print '</table>';
            print '<br>';
        print '</div>';

        print '<div class="fichehalfright">';
        print '<div class="ficheaddleft">';
            print '<table class="border nc_table_" width="100%" >';
                print '<tbody>';
                    print '<tr>';
                        print '<td style="text-align:left;" class=" firsttd200px">'.$langs->trans('responsable_candidature').'</td>';
                        print '<td style="">';
                        if($item->responsable){
                            $user_->fetch($item->responsable);
                            // print $user_->firstname.' '.$user_->lastname;
                            print $user_->getNomUrl(1);
                        }
                        print '</td>';
                    print '</tr>';

                    print '<tr>';
                        print '<td  style="text-align:left;">'.$langs->trans('appreciation').'</td>';
                        print '<td  style="">';
                            print '<div class="rating">';
                                    $rating ='<input type="radio" id="star5" name="appreciation" class="appreciation" value="5" /><label for="star5" title="'.$langs->trans('Excellent').'"></label>';
                                    $rating .='<input type="radio" id="star4" name="appreciation" class="appreciation" value="4"/><label  title="'.$langs->trans('Très_bien').'" for="star4"></label>';
                                    $rating .='<input type="radio" id="star3" name="appreciation" class="appreciation" value="3" /><label for="star3" title="'.$langs->trans('Bon').'"></label>';
                                    $rating .='<input type="radio" id="star2" name="appreciation" class="appreciation" value="2"/><label  title="'.$langs->trans('Satisfaisant').'" for="star2"></label>';
                                    $rating .='<input type="radio" id="star1" name="appreciation" class="appreciation" value="1"/><label  title="'.$langs->trans('Insuffisant').'" for="star1"></label>';

                                    $rating = str_replace('value="'.$item->appreciation.'"', 'value="'.$item->appreciation.'" checked', $rating);
                                    print $rating;
                                print '</div>';
                                $status=[1=>$langs->trans('Insuffisant'),2=>$langs->trans('Satisfaisant'),3=>$langs->trans('Bien'),4=>$langs->trans('Très_bien'),5=>$langs->trans('Excellent')];
                                $clscolor = "redbg";
                                if($item->appreciation > 2)
                                    $clscolor = "greenbg"; 
                                print '<div class="txt appreciationdetail '.$clscolor.'">';
                                    print $status[$item->appreciation];
                                print '</div>';
                        print '</td>';
                    print '</tr>';

                    print '<tr>';
                        print '<td style="text-align:left;">'.$langs->trans('origine').'</td>';
                        print '<td style="">';
                        if($item->origine){
                            $origine->fetch($item->origine);
                            print $origine->source;
                        }
                        print '</td>';
                    print '</tr>';

                    print '<tr>';
                        print '<td style="text-align:left;">'.$langs->trans('apport_par').'</td>';
                        print '<td style="">'.$item->apport_par.'</td>';
                    print '</tr>';
                print '</tbody>';
            print '</table>';
        print '</div>';
        print '</div>';
        print '<div class="clear"></div>';
    print '</div>';

    // post & contrat
    print '<div class="fichecenter postandcontrat">';
        print '<div class="fichehalfleft">';
            print '<div class="topheaderrecrutmenus"><span>'.$langs->trans('poste').'</span></div>';
            print '<div class="divcontaintable">';
            print '<table class="border nc_table_" width="100%">';
                print '<body>';
                    print '<tr>';
                        print '<td style="text-align:left;" class=" firsttd200px">'.$langs->trans('fonction').'</td>';
                        print '<td >';
                            if($item->poste){
                                $postes->fetch($item->poste);
                                // print $postes->label;
                                print '<a href="'.dol_buildpath('/recrutement/card.php?id='.$item->poste,2).'" >'.$postes->label.'</a>';
                            }
                        print '</td>';
                    print '</tr>';
                    print '<tr>';
                        print '<td style="text-align:left;">'.$langs->trans('departement').'</td>';
                        print '<td style="">';
                            if($item->departement){
                                $departements->fetch($item->departement);
                                // print $departements->label;
                                print '<a href="'.dol_buildpath('/recrutement/departements/card.php?id='.$item->departement,2).'" >'.$departements->label.'</a>';
                            }
                        print '</td>';
                    print '</tr>';
                    print '<tr>';
                        print '<td >'.$langs->trans('date_depot').'</td>';
                        print '<td>';
                            if($item->date_depot){
                                $date=explode('-', $item->date_depot);
                                $date_depot=$date[2].'/'.$date[1].'/'.$date[0];
                                print $date_depot;
                            }
                        print '</td>';
                    print '</tr>';

                    print '</tr>';


                print '</tbody>';
            print '</table>';
            print '</div>';
        print '</div>';

        print '<div class="fichehalfright">';
        print '<div class="ficheaddleft">';    
            print '<div class="bordercontainr">';    
            print '<div class="topheaderrecrutmenus"><span>'.$langs->trans('contrat').'</span></div>';
            print '<div class="divcontaintable">';
            print '<table class="border nc_table_" width="100%">';
                print '<body>';

                    print '<tr>';
                        print '<td style="text-align:left;" class=" firsttd200px" >'.$langs->trans('salaire_demande').'</td>';
                        print '<td >'.number_format($item->salaire_demande,2).' '.$langs->getCurrencySymbol($conf->currency).'</td>';
                    print '</tr>';

                    print '<tr>';
                        print '<td style="text-align:left;">'.$langs->trans('salaire_propose').'</td>';
                        print '<td >'.number_format($item->salaire_propose,2).' '.$langs->getCurrencySymbol($conf->currency).'</td>';
                    print '</tr>';

                    print '<tr>';
                        print '<td style="text-align:left;">'.$langs->trans('date_disponible').'</td>';
                        print '<td>';
                            if($item->date_disponible){
                                $date=explode('-', $item->date_disponible);
                                $date_disponible=$date[2].'/'.$date[1].'/'.$date[0];
                                print $date_disponible;
                            }
                        print '</td>';
                    print '</tr>';


                print '</tbody>';
            print '</table>';
            print '</div>';
            print '</div>';
        print '</div>';
        print '</div>';

        print '<div class="clear"></div>';
    print '</div>';

    // Description
    print '<table class="border nc_table_" width="100%">';
        print '<body>';
            print '<tr>';
                print '<td style="width:180px;text-align:left;" >'.$langs->trans('resume').'</td>';
                print '<td >';
                    print '<div class="textareadescription">'.nl2br($item->resume).'</div>';
                print '</td>';
            print '</tr>';
        print '</tbody>';
    print '</table>';



    // Actions
    print '<table class="" width="100%">';
    print '<tr>';
        print '<td colspan="2" >';
            print '<br>';
            // if($item->refuse)
            print '<a href="./card.php?id='.$id.'&action=edit" class="butAction">'.$langs->trans('Modify').'</a>';
            print '<a href="./card.php?id='.$id.'&action=delete" class="butAction butActionDelete">'.$langs->trans('Delete').'</a>';
            print '<a href="./index.php?page='.$page.'" class="butAction">'.$langs->trans('Cancel').'</a>';
            print '<span class="createemployrecrutbutt">';
                if(empty($candidature->employe)){
                    print '<a class="butAction" style="float: right;" data-id="'.$item->rowid.'" id="cree_employe2" href="./card.php?id='.$id.'&etat=cree_employe" >'.$langs->trans('cree_employe').'</a>';
                }
                if($item->refuse==0){
                    print '<a class="butAction butActionDelete" style="float: right; " data-id="'.$item->rowid.'" id="refuse2" href="./card.php?id='.$id.'&etat=refuse">'.$langs->trans('refuse').'</a>';
                }
                if($item->refuse){
                    print '<a class="butAction" style="float: right; " data-id="'.$item->rowid.'" id="relance" href="./card.php?id='.$id.'&etat=relance">'.$langs->trans('relance').'</a>';
                }
            print '</span>';
        print '</td>';
    print '</tr>';
    print '</table>';

    print '</form>';
    
    print '<div id="lightbox" style="display:none;"><p>X</p><div id="content"><img src="" /></div></div>';
    
}

?>

<script>
    $(function(){
        $('.etapes').attr('disabled','disabled');
        $('.appreciation').attr('disabled','disabled');
    });
</script>