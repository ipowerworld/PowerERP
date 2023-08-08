<?php

if ($action == 'confirm_delete' && GETPOST('confirm') == 'yes' ) {
    
    if (!$id || $id <= 0) {
        header('Location: ./card.php?action=request&error=dalete_failed&id='.$id);
        exit;
    }

    $page  = GETPOST('page');

    $pannepiecederechange->fetch($id);
   // $Project->fetch($id);/////////////
    $error = $pannepiecederechange->delete();


    if ($error == 1) {
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
    // $head[$h][0] = dol_buildpath("/gestionpannes/card.php?id=".$id, 1);
    // $head[$h][1] = $langs->trans($modname);
    // $head[$h][2] = 'affichage';
    // $h++;
    // dol_fiche_head($head,'affichage',"",0,"logo@gestionpannes");


    if($action == "delete"){
        print $form->formconfirm("card.php?id=".$id."&page=".$page,$langs->trans('Confirmation') , $langs->trans('msgconfirmdelet'),"confirm_delete", 'index.php?page='.$page, 0, 1);
    }

    if (!$user->rights->gestionpannes->gestion->consulter) {
        accessforbidden();
    }

    $pannepiecederechange->fetchAll('','',0,0,' and rowid = '.$id);
    $item = $pannepiecederechange->rows[0];


    print '<form method="post" action="'.$_SERVER["PHP_SELF"].'" >';

    print '<input type="hidden" name="mainmenu" value="gestionpannes" />';
    print '<input type="hidden" name="confirm" value="no" id="confirm" />';
    print '<input type="hidden" name="id" value="'.$id.'" />';
    print '<input type="hidden" name="page" value="'.$page.'" />';
    print '<table class="border" width="100%">';
    print '<tbody>';
    print '<tr>';
        print '<td width="20%" >'.$langs->trans('Ref_l').'</td>';
        print '<td >'.$item->rowid.'</td>';
    print '</tr>';
    $produit->fetch($item->matreil_id);
    print '<tr>';
        print '<td >'.$langs->trans('materiel').'</td>';
        print '<td>'.$produit->getNomUrl(1)." - ".$produit->label.'</td>';
    print '</tr>';
      
    print '<tr>';
        print '<td >'.$langs->trans('quantite').'';
        print '<td>'.$item->quantite.'</td>';
    print '</tr>';
    $date=explode('-',$item->date_remplacement);
    $date=$date[2].'/'.$date[1].'/'.$date[0];
    print '<tr>';
        print '<td >'.$langs->trans('date_remplace').'';
        print '<td>'.$date.'</td>';
    print '</tr>';
            print '<tr>';
        print '<td >'.$langs->trans('description').'';
        print '<td>'.$item->commantaire.'</td>';
    print '</tr>';
    print '</table>';

    // Actions
    print '<table class="" width="100%">';
    print '<tr>';
        print '<td colspan="2" >';
            print '<br>';
            // print '<a href="./card.php?id='.$id.'&action=edit" class="butAction">'.$langs->trans('Modify').'</a>';
            // print '<a href="./card.php?id='.$id.'&action=delete" class="butAction butActionDelete">'.$langs->trans('Delete').'</a>';
            print '<a href="./index.php?page='.$page.'" class="butAction">'.$langs->trans('Annuler').'</a>';

        print '</td>';
    print '</tr>';
    print '</table>';

    print '</form>';
    
}

?>