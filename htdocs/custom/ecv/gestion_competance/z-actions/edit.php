<?php

if ($action == 'update' && $request_method === 'POST') {

    $page  = GETPOST('page');

    // $d1 = GETPOST('debut');
    // $f1 = GETPOST('fin');
   
    $name = addslashes(GETPOST('name'));
    // $data =  array( 
    //     'name'  =>  $name,
    //     'icon'  =>  $icon,
    // );

    $data['name'] = $name;
    if(!empty($icon))
        $data['icon'] = $icon;

    $isvalid = $competances->update($id, $data);
    $competances->fetch($id);

    if ($isvalid > 0) {
        $dir = $conf->ecv->dir_output.'/competances/';
        if($competances->icon && $_FILES['icon']['name']){
            $file=$dir."/".$competances->icon;
            unlink($file);
        }

        if ($_FILES['icon']) { 
            $TFile = $_FILES['icon'];
            $copie = array('icon' => dol_sanitizeFileName($TFile['name'],''));
            $upload_dir = $conf->ecv->dir_output.'/competances/';
            if (dol_mkdir($upload_dir) >= 0)
            {
                $destfull = $upload_dir.$TFile['name'];
                $info     = pathinfo($destfull); 
                
                $filname    = dol_sanitizeFileName($TFile['name'],'');
                $destfull   = $info['dirname'].'/'.$filname;
                $destfull   = dol_string_nohtmltag($destfull);
                $resupload  = dol_move_uploaded_file($TFile['tmp_name'], $destfull, 0, 0, $TFile['error'], 0);
                $competances->update($id,$copie);
            }
        }


        header('Location: ./card.php?id='.$id);
        exit;
    } 
    else {
        header('Location: ./card.php?id='. $id .'&update=0');
        exit;
    }
}
if($action == "edit"){

    print '<form method="post" action="'.$_SERVER["PHP_SELF"].'" enctype="multipart/form-data" class="competcecv">';

    print '<input type="hidden" name="action" value="update" />';
    print '<input type="hidden" name="id" value="'.$id.'" />';
    print '<input type="hidden" name="page" value="'.$page.'" />';

    $competances->fetchAll('','',0,0,' and rowid = '.$id);
    $item = $competances->rows[0];

    print '<table class="border" width="100%">';
    print '<tbody>';
    print '<tr>';
        print '<td>'.$langs->trans('ecv_competence').'</td>';
        print '<td><input type="text" class="" id="name" name="name" value="'.$item->name.'"  autocomplete="off"/>';
    print '</td>';
    print '</tr>';
    print '<tr>';
        print '<td >'.$langs->trans('ecv_icon_competence').'</td>';
        print '<td>';
            print '<div id="wrapper"> <ul style="list-style: none;">';
                if($item->icon){
                    print '<li>';
                        $minifile = getImageFileNameForSize($item->icon, '');  
                        // $dt_files = getAdvancedPreviewUrl('ecv', 'competances/'.$item->rowid.'/'.$minifile, 1, '&entity='.(!empty($object->entity)?$object->entity:$conf->entity));
                        $dt_files = getAdvancedPreviewUrl('ecv', 'competances/'.$minifile, 1, '&entity='.(!empty($object->entity)?$object->entity:$conf->entity));

                        print ' <a href="'.$dt_files['url'].'" class="'.$dt_files['css'].'" target="'.$dt_files['target'].'" mime="'.$dt_files['mime'].'">' ;
                            print '<img class="photo" title="'.$minifile.'" alt="Fichier binaire" src="'.$dt_files['url'].'" border="0" name="image" >';
                        print '</a> ';
                    print '</li>';
                }
            print '</ul></div>';
             print '<input type="file" accept="image/*" class="icon" id="icon" name="icon"  autocomplete="off"/>';
        print '</td>';
    print '</tr>';
   
    print '</tbody>';
    print '</table>';

    // Actions
    print '<table class="" width="100%">';
    print '<tr>';
        print '<td colspan="2" >';
        print '<br>';
        print '<input type="submit" value="'.$langs->trans('Validate').'" name="bouton" class="butAction" />';
        print '<a href="./card.php?id='.$id.'" class="butAction">'.$langs->trans('Cancel').'</a>';
        print '</td>';
    print '</tr>';
    print '</table>';

    print '<div id="lightbox" style="display:none;"><p>X</p><div id="content"><img src="" /></div></div>';
    print '</form>';
    
    ?>

    <?php
}

?>