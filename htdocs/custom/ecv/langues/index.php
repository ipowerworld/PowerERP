<?php
$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");       // For root directory
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php"); // For "custom" 


dol_include_once('/ecv/class/ecv.class.php');
dol_include_once('/ecv/class/ecvlangues.class.php');
dol_include_once('/core/class/html.form.class.php');
dol_include_once('/ecv/lib/ecv.lib.php');


$langs->load('ecv@ecv');

$modname = $langs->trans("ecv_langues");

// Initial Objects
$ecv  = new ecv($db);
$ecvlangues = new ecvlangues($db);
$form           = new Form($db);

$var 				= true;
$sortfield 			= ($_GET['sortfield']) ? $_GET['sortfield'] : "rowid";
$sortorder 			= ($_GET['sortorder']) ? $_GET['sortorder'] : "DESC";
$id 				= $_GET['id'];
$action   			= $_GET['action'];

if (!$user->rights->ecv->gestion->consulter) {
	accessforbidden();
}


$id_ecv         = GETPOST('id_ecv');
$action         = GETPOST('action');
$request_method         = $_SERVER['REQUEST_METHOD'];
$ecv->fetch($id_ecv);
$filter .= (!empty($id_ecv)) ? " AND fk_ecv = '".$id_ecv."'  AND fk_user=".$ecv->fk_user : "";

if ($action == 'create' && $request_method === 'POST') {
	$langues=GETPOST('langues_new');
	$id_ecv=GETPOST('id_ecv');
	$ecv->fetch($id_ecv);
	foreach ($langues as $key => $value) {
		
		$data =  array( 
	        'name'       =>  $value['name'],
	        'value'      =>  $value['value'],
	        'fk_ecv'   	 =>  $id_ecv,
	        'fk_user'  	 =>  $ecv->fk_user,
	    );
		$isvalid = $ecvlangues->create(1, $data);
		
	}
    header('Location: index.php?id_ecv='.$id_ecv);
    exit;
}
// print_r($action.'/'.$request_method);die();
if ($action == 'edit' && $request_method === 'POST') {
    $id = GETPOST('id');
    // $name = GETPOST('name');
    $value = GETPOST('value');
    $ecv->fetch($id_ecv);

   
    $data =  array( 
        'value'       =>  $value,	
        'fk_ecv'   	     =>  $id_ecv,
        'fk_user'  	     =>  $ecv->fk_user,
    );
    $isvalid = $ecvlangues->update($id, $data);
    if ($isvalid > 0) {
        header('Location: index.php?id_ecv='.$id_ecv);
        exit;
    } 
}


$limit 	= $conf->liste_limit+1;

$page 	= GETPOST("page",'int');
$page = is_numeric($page) ? $page : 0;
$page = $page == -1 ? 0 : $page;
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;


if ($action == 'confirm_delete' && GETPOST('confirm') == 'yes' ) {
    if (!$id || $id <= 0) {
        header('Location: ./index.php?action=request&error=dalete_failed&id='.$id);
        exit;
    }

    $page  = GETPOST('page');
    $id_ecv  = GETPOST('id_ecv');
	$ecv->fetch($id_ecv);
    $ecvlangues->fetch($id);
    $error =  $ecvlangues->delete();
    if ($error == 1) {
        header('Location: index.php?id_ecv='.$id_ecv);
        exit;
    }
    else {      
        header('Location: index.php');
        exit;
    }
}

$nbrtotal = $ecvlangues->fetchAll($sortorder, $sortfield, $limit, $offset, $filter);

$morejs  = array();
llxHeader(array(), $modname,'','','','',$morejs,0,0);


print_barre_liste($modname, $page, $_SERVER["PHP_SELF"], "", $sortfield, $sortorder, "", $nbrtotal, $nbrtotalnofiltr);

if( ($id && empty($action)) || $action == "delete" ){
    if($action == "delete"){
        print $form->formconfirm("index.php?id=".$id."&id_ecv=".$id_ecv,$langs->trans('Confirmation') , $langs->trans('msgconfirmdelet'),"confirm_delete", 'index.php?id_ecv='.$id_ecv.'&page='.$page, 0, 1);
    }
}

$id_ecv=GETPOST('id_ecv');
if(!empty($id_ecv)){
	$head = ecvAdminPrepareHead($id_ecv);
    dol_fiche_head($head,'langues','',	0,"ecv@ecv");
}

print '<link rel="stylesheet" href= "'.dol_buildpath('/ecv/langues/css/rating.css',2).'">';

print '<form method="get" action="'.$_SERVER["PHP_SELF"].'" class="form_ecv langsecv">'."\n";
	print '<input name="pagem" type="hidden" value="'.$page.'">';
	print '<input name="offsetm" type="hidden" value="'.$offset.'">';
	print '<input name="limitm" type="hidden" value="'.$limit.'">';
	print '<input name="filterm" type="hidden" value="'.$filter.'">';

	// print '<div style="float: right; margin: 8px;">';
	// if(!empty($id_ecv))
	// 	print '<a href="card.php?action=add&id_ecv='.$id_ecv.'" class="butAction" >'.$langs->trans("Add").'</a>';
	// else
	// 	print '<a href="card.php?action=add" class="butAction" >'.$langs->trans("Add").'</a>';
	// print '</div>';

	print '<table id="table-1" class="noborder" style="width: 100%;" >';
		print '<thead>';
			print '<tr class="liste_titre">';
				field($langs->trans("ecv_titre_langue"),'ecv_titre_langue');
				field($langs->trans("ecv_valeur_langue"),'ecv_valeur_langue');
				print '<th align="center">'.$langs->trans("Action").'</th>';
			print '</tr>';
		print '</thead>';
		print '<tbody>';
			$colspn = 5;
			$ecv->fetch($id_ecv);

				if (count($ecvlangues->rows) > 0) {
					for ($i=0; $i < count($ecvlangues->rows) ; $i++) {


						$var = !$var;
						$item = $ecvlangues->rows[$i];
						$langs->loadLangs(array('admin', 'languages', 'other', 'companies', 'products', 'members', 'projects', 'hrm', 'agenda'));
						print '<tr '.$bc[$var].' >';
				    		print '<td align="center" style="">'.picto_from_langcode($item->name).' '.$langs->trans("Language_".$item->name).'</td>';
				            print '<td align="center" >';
				    		print '<div class="rating" style="float:none;">';
				    		$rating='<input type="radio" disabled id="star5_'.$item->rowid.'" name="langues['.$item->rowid.'][rating]" value="5" /><label for="star5_'.$item->rowid.'"></label>';
				                $rating.='<input type="radio" disabled id="star4_'.$item->rowid.'" name="langues['.$item->rowid.'][rating]" value="4" /><label for="star4_'.$item->rowid.'"></label>';
				                $rating.='<input type="radio" disabled id="star3_'.$item->rowid.'" name="langues['.$item->rowid.'][rating]" value="3" /><label for="star3_'.$item->rowid.'"></label>';
				                $rating.='<input type="radio" disabled id="star2_'.$item->rowid.'" name="langues['.$item->rowid.'][rating]" value="2" /><label for="star2_'.$item->rowid.'"></label>';
				                $rating.='<input type="radio" disabled id="star1_'.$item->rowid.'" name="langues['.$item->rowid.'][rating]" value="1" /><label for="star1_'.$item->rowid.'"></label>';
			    				$rating = str_replace('value="'.$item->value.'"', 'value="'.$item->value.'" checked', $rating);
			    				print $rating;
				            print '</div></td>';
							print '<td align="center" style="width:10%; ">';
								print '<img src="'.dol_buildpath('/ecv/images/edit.png',2).'" class="img_edit" data-id="'.$item->rowid.'">  <a href="./index.php?id='.$item->id.'&action=delete&id_ecv='.$id_ecv.'" ><img src="'.DOL_MAIN_URL_ROOT.'/theme/md/img/delete.png" class="img_delete"></a>';
							print '</td>';
						print '</tr>';
					}
				}else{
					print '<tr><td align="center" colspan="'.$colspn.'">'.$langs->trans("NoResults").'</td></tr>';
				}
		print '</tbody>';
	print '</table>';
print '</form>';

print '<br><br>';

print '<form method="post" action="'.$_SERVER["PHP_SELF"].'" enctype="multipart/form-data" class="form_ecv langsecv">';
	print '<div class="nouveau" style="display:none">';
		print '<div style="background-color:#3c4664;padding:8px; color:white" width="100%" class="titre_ecv">Les détails de la langue';
		print '</div>';
		print '<div>';
			print '<table id="exp" width="100%" cellpadding="5px"; cellspadding="5px">';
				print '<thead>';
					print '<tr>';
						print '<th align="center">'.$langs->trans('ecv_titre_langue').'</th>';
						print '<th align="center">'.$langs->trans('ecv_valeur_langue').'</th>';
						print '<th align="center" id="action">'.$langs->trans('Action').'</th>';
					print '</tr>';
				print '</thead>';
				print '<tbody id="new_langue"><input name="id_ecv" type="hidden" value="'.$id_ecv.'">';
				print '</tbody>';
			print '</table>';
		print '</div>';
	print '</div>';
	print '<div style="float: right; margin: 8px;">';
		print '<a  class="butAction" id="nouveau" style="background-color:#3c4664; color:white" > '.$langs->trans('New').' </a>';
	print '</div>';
	print '<div style="text-align: center; margin: 8px;">';
		print '<input type="submit" value="'.$langs->trans('Validate').'" name="bouton" style="display:none;" class="butAction" id="valider" />';
	print '</div>';
print '</form>';

function field($titre,$champ){
	global $langs;
	print '<th class="" style="padding:5px; 0 5px 5px; text-align:center;">'.$langs->trans($titre).'<br>';
		
	print '</th>';
}





?>

<script src="jquery.artarax.rating.star.js"></script>

<script>
	$(document).ready(function(){
		$('.fiche').find('.tabBar').removeClass('tabBarWithBottom');
		$('#select_onechambre>select').select2();

		$('#nouveau').click(function(){
        	$('#action').html('<?php echo $langs->trans("Delete"); ?>');
        	$('.titre_ecv').html('Nouveau langue');
			$('.nouveau').show();
			$('#valider').show();
            $id=$('#new_langue tr').length+1;
			console.log($id);
           		$.ajax({
           			url:"<?php echo dol_buildpath('/ecv/langues/data_langues.php?data=select',2) ?>",
		            type:"POST",
		            data:{'langue_id':$id},
		            success:function(data){
		                $('#select_langue_'+$id).html(data);
		            }
           		})
            	$('#new_langue').append('<tr > <input name="action"  type="hidden" value="create"><td id="select_langue_'+$id+'"></td> <td><div class="rating"><input type="radio" id="star_new5_'+$id+'" name="langues_new['+$id+'][value]" value="5" /><label for="star_new5_'+$id+'"></label> <input type="radio" id="star_new4_'+$id+'" name="langues_new['+$id+'][value]" value="4" /><label for="star_new4_'+$id+'"></label> <input type="radio" id="star_new3_'+$id+'" name="langues_new['+$id+'][value]" value="3" /><label for="star_new3_'+$id+'"></label><input type="radio" id="star_new2_'+$id+'" name="langues_new['+$id+'][value]" value="2" /><label for="star_new2_'+$id+'"></label><input type="radio" id="star_new1_'+$id+'" name="langues_new['+$id+'][value]" value="1" /><label for="star_new1_'+$id+'"></label> </div> </td><td style="width:10%" align="center"><img src="<?php echo DOL_MAIN_URL_ROOT.'/theme/md/img/delete.png' ?>" class="img_delete" onclick="delete_tr(this);"></td></tr>');
            $('.select_langues').select2();
        });

        $('.img_edit').click(function(){
        	$('#action').html('<?php echo $langs->trans("Modify"); ?>');
        	$('.titre_ecv').html('Les détails d\'une langue');
			$('.nouveau').show();
			$('#nouveau').hide();
			$('#valider').hide();
			$id=$(this).data('id');
			$.ajax({
				data:{'langue_id':$id},
				url:"<?php echo dol_buildpath('/ecv/langues/data_langues.php?data=edit',2)?>",
				type:'POST',
				success:function(data){
					$('#new_langue').html(data);
				}
			});
		});
	});
	function delete_tr(tr){
		$(tr).parent().parent().remove();
	}
</script>
<?php

llxFooter();