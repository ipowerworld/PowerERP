<?php 
$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");       // For root directory
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php"); // For " 

dol_include_once('/recrutement/class/candidatures.class.php');
dol_include_once('/recrutement/class/etapescandidature.class.php');
dol_include_once('/recrutement/class/departement.class.php');
dol_include_once('/recrutement/class/etiquettes.class.php');
dol_include_once('/recrutement/class/origines.class.php');
dol_include_once('/recrutement/lib/recrutement.lib.php');
dol_include_once('/recrutement/class/postes.class.php');
dol_include_once('/recrutement/class/cv.class.php');
dol_include_once('/core/class/html.form.class.php');


require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';


$langs->load('recrutement@recrutement');
$langs->load('users');
$modname = $langs->trans("candidature");

// Initial Objects
$candidature  = new candidatures($db);
$postes       = new postes($db);
$cv           = new cv_recrutement($db);
$form         = new Form($db);
$poste = new postes($db);

$departements = new departements($db);
$etiquette    = new etiquettes($db);
$origine      = new origines($db);
$contact      = new Contact($db);
$etapes       = new etapescandidature($db);
$user_        = new User($db);

// Get parameters
$etat         = GETPOST('etat', 'alpha');
$request_method = $_SERVER['REQUEST_METHOD'];
$action         = GETPOST('action', 'alpha');
$page           = GETPOST('page');
$id             = (int) ( (!empty($_GET['id'])) ? $_GET['id'] : GETPOST('id') ) ;

// print_r($user->rights);die;

$error  = false;
if (!$user->rights->recrutement->gestion->consulter) {
    accessforbidden();
}

if(in_array($action, ["add","edit"])) {
    if (!$user->rights->recrutement->gestion->update) {
      accessforbidden();
    }
}
if($action == "delete") {
    if (!$user->rights->recrutement->gestion->delete) {
      accessforbidden();
    }

}


if($etat == 'refuse'){
    $resu = $candidature->update(GETPOST('id'),['refuse'=>1]);
    header('Location: ./card.php?id='.$id);
    exit();
   
}
if($etat == 'relance'){
    $candidature->fetch($id);
    $candidature->update($id,['refuse'=>0]);
    header('Location: ./card.php?id='.$id);
    exit();
   
}
if($etat == 'cree_employe'){
    $data='';
    $user_ = new User($db);
    $candidature->fetch($id);
    $poste->fetch($candidature->poste);
    $user_->login=$candidature->nom.'_'.$candidature->prenom;
    $user_->lastname=$candidature->nom;
    $user_->firstname=$candidature->prenom;
    $user_->email=$candidature->email;
    $user_->job=$poste->label;
    $user_->office_phone=$candidature->tel;
    $user_->user_mobile=$candidature->mobile;
    $user_->fk_user=$candidature->responsable;

    if($candidature->etape == 5 && !empty($user->rights->user->user->creer) ){
        $employe= $user_->create($user);
        if(!empty($employe) && $employe > 0){
            $data=['employe'=>$employe];
            $isvalid = $candidature->update($id,$data);
            $msg = 'L\'employé a été créé avec succès';
            $type_msg = 'warnings';
            $data = 'ok';
        }
        else{
            $msg='Erreur';
            $type_msg='errors';
            $data='Erreur';
        }
    }
    if($isvalid <= 0){
        $type_msg='errors';
        $msg='Les étapes de candidature incomplètes';
    }
    setEventMessages($msg, null, $type_msg);
    
    header('Location: ./card.php?id='.$id);
    exit();

}
// ------------------------------------------------------------------------- Actions "Create/Update/Delete"
if ($action == 'create' && $request_method === 'POST') {
    require_once 'z-actions/create.php';
}

if ($action == 'update' && $request_method === 'POST') {
    require_once 'z-actions/edit.php';
}

// If delete of request
if ($action == 'confirm_delete' && GETPOST('confirm') == 'yes' ) {
    require_once 'z-actions/show.php';
}

if ($action == 'confirm_deconstruction' && GETPOST('confirm') == 'yes' ) {
    require_once 'z-actions/edit.php';
}

if ($action == 'confirm_rebut' && GETPOST('confirm') == 'yes' ) {
    require_once 'z-actions/edit.php';
}

$action_export=GETPOST('action_export');
$id_cv=GETPOST('id_cv');


$morejs  = array();
llxHeader(array(), $modname,'','','','',$morejs,0,0);
print_fiche_titre($modname);

$head = menu_candidature($id);
// if($action != 'add'){
    dol_fiche_head(
        $head,
        'general',
        '', 
        0,
        "recrutement@recrutement"
    );
// }


?>
<style type="text/css">


   
</style>
<?php
    // die($action);
    // ------------------------------------------------------------------------- Views
    if($action == "add")
        require_once 'z-actions/create.php';

    if($action == "edit")
        require_once 'z-actions/edit.php';

    if( ($id && empty($action)) || $action == "delete" )
        require_once 'z-actions/show.php';

    llxFooter();
    if (is_object($db)) $db->close();

    ?>


<script>
    $(document).ready(function(){
        $('#niveau').select2();
        // $('#validatesumitbutton').click(function(e){
        //      e.preventDefault();
        //     $('input[type="submit"]').click();
        // });
        $('#select_contact').change(function(){
            get_contact($(this));
        });
        $('#cree_employe').click(function(){
            $id=$('#cree_employe').data('id');
            $.ajax({
                data:{'id_':$id,},
                url:"<?php echo dol_buildpath('/recrutement/candidatures/info_contact.php?action_=cree_employe',2) ?>",
                type:'POST',
                success:function($data){
                    if($data == 'Ok'){
                        $('#cree_employe').css('display','none');
                        $('#refuse').css('display','block');
                    }
                        location.reload();
                }
            });
        });

        $('#refuse').click(function(){
            $id=$('#refuse').data('id');
            $.ajax({
                data:{'id_':$id,},
                url:"<?php echo dol_buildpath('/recrutement/candidatures/info_contact.php?action_=refuse',2) ?>",
                type:'POST',
                success:function($data){
                    if($data == 'Ok'){
                        $('#refuse').css('display','none');
                        $('#relance').css('display','block');
                    }
                        location.reload();
                }
            });
        });

        $('#relance').click(function(){
            $id=$('#relance').data('id');
            $.ajax({
                data:{'id_':$id,},
                url:"<?php echo dol_buildpath('/recrutement/candidatures/info_contact.php?action_=relance',2) ?>",
                type:'POST',
                success:function($data){
                    if($data == 'Ok'){
                        $('#relance').css('display','none');
                        $('#refuse').css('display','block');
                    }
                        location.reload();
                }
            });
        });
        
    });
    function get_contact(that) {
        $id = $(that).val();
        console.log($id);
        $.ajax({
            data:{'id':$id,},
            url:"<?php echo dol_buildpath('/recrutement/candidatures/info_contact.php?action_=get_contact',2) ?>",
            type:'POST',
            dataType: 'json',
            success:function(data){
                // console.log(data);
                    $('#email').val( data['email']);
                    $('#tel').val( data['tel']);
                    $('#mobile').val( data['mobil']);
                    // $('#email').val( data['email']);
                    // location.reload();
               
            }
            

        });
    }
</script>
