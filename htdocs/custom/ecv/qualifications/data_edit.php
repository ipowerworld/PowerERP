<?php
$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");       // For root directory
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php"); // For "custom" 


dol_include_once('/ecv/class/ecv.class.php');
dol_include_once('/ecv/class/ecvqualifications.class.php');
$ecv              = new ecv($db);
$ecvqualifications   = new ecvqualifications($db);
    $id=GETPOST('id');

    // $id_delete=GETPOST('delete_id');
    // if(!empty($id_delete)){
    //     $representant->fetch($id_delete);
    //     $error = $representant->delete();
    // }
    $ecvqualifications->fetch($id);
    $item=$ecvqualifications;
 
    $data.='<tr>';
            $data.='<input name="id" type="hidden" value="'.$id.'">';
            $data.='<input name="id_ecv" type="hidden" value="'.$item->fk_ecv.'">';
            $data.='<input name="action"  type="hidden" value="edit">';
        $data.='<td ><input  type="text" name="name" value="'.$item->name.'" autocomplete="off" style="width:99%;" /></td> ';

        $data.='<td align="center" ><input type="submit" value="'.$langs->trans('Validate').'" name="bouton" class="butAction valider" /> <img src="'.dol_buildpath('/ecv/images/check.png',2).'" height="25px" class="img_valider"></td>';
    $data.='</tr>';
    $data.= '<tr><td colspan="3" style="border:none !important" align="right"><a href="./index.php?id_ecv='.$item->fk_ecv.'" class="butAction">'.$langs->trans('Cancel').'</a></td><tr>';
    
    $data.='<script>$(document).ready(function(){ $(".valider").hide(); $(".img_valider").click(function(){$(".valider").trigger("click")}); });</script>';
    	
    echo $data;
?>
<script>
    $(document).ready(function(){
        $( "#debut" ).change( function(){
            $min=$(this).val();
            $("#fin").datepicker( "option", "minDate", $min );
        });
        $( "#fin" ).change( function(){
            $max=$(this).val();
            $("#debut").datepicker( "option", "maxDate", $max );
        });
        

    });
</script>