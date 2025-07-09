<?php
################################################################################
# @Name : ajax/procedure.php
# @Description : submit data from modal form to database
# @Call : ./procedure.js
# @Parameters :  
# @Author : Flox
# @Create : 29/09/2021
# @Update : 17/01/2024
# @Version : 3.2.47
################################################################################

//check call
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') 
{
    //init and secure POST var
	require_once('../core/init_post.php');
	require_once('../core/init_get.php');

    //db connection
    require_once('../connect.php');

    //call functions
    require_once('../core/functions.php');

    //switch SQL MODE to allow empty values
    $db->exec('SET sql_mode = ""');

    //load parameters table
    $qry=$db->prepare("SELECT * FROM `tparameters`");
    $qry->execute();
    $rparameters=$qry->fetch();
    $qry->closeCursor();

    //display error parameter
    if($rparameters['debug']) {
        ini_set('display_errors', 'On');ini_set('display_startup_errors', 'On');ini_set('html_errors', 'On');error_reporting(E_ALL);
    } else {
        ini_set('display_errors', 'Off');ini_set('display_startup_errors', 'Off');ini_set('html_errors', 'Off');error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    }

    //load language
    if(!$_POST['language']) {$_POST['language']='en_US';}
    define('PROJECT_DIR', realpath('../'));
    define('LOCALE_DIR', PROJECT_DIR .'/locale');
    define('DEFAULT_LOCALE', $_POST['language']);
    require_once('../vendor/components/php-gettext/gettext.inc');
    $encoding = 'UTF-8';
    $locale = (isset($_POST['language']))? $_POST['language'] : 'DEFAULT_LOCALE';
    T_setlocale(LC_MESSAGES, $locale);
    T_bindtextdomain($_POST['language'], LOCALE_DIR);
    T_bind_textdomain_codeset($_POST['language'], $encoding);
    T_textdomain($_POST['language']);

    //check existing token
    if($_POST['token'])
    {
        //check token validity
        $qry=$db->prepare("SELECT `token`,`user_id`,`procedure_id` FROM ttoken WHERE action='procedure_access' AND `token`=:token AND `ip`=:ip");
        $qry->execute(array('token' => $_POST['token'],'ip' => $_SERVER['REMOTE_ADDR']));
        $token=$qry->fetch();
        $qry->closeCursor();
        if(!empty($token['token'])) {
            //init var
            $error=0;
            $success=0;
            $action=0;
            $uploaded_file=0;

            //load user table
            $qry=$db->prepare("SELECT * FROM `tusers` WHERE id=:id");
            $qry->execute(array('id' => $token['user_id']));
            $ruser=$qry->fetch();
            $qry->closeCursor();

            //load rights table
            $qry=$db->prepare("SELECT * FROM `trights` WHERE profile=:profile");
            $qry->execute(array('profile' => $ruser['profile']));
            $rright=$qry->fetch();
            $qry->closeCursor();

            //db update
            if($_POST['action']=='add')
            {
                if($rright['procedure_add'])
                {
                    //insert in database
                    $qry=$db->prepare("INSERT INTO `tprocedures` (`name`,`text`,`category`,`subcat`,`company_id`) VALUES (:name,:text,:category,:subcat,:company_id)");
                    $qry->execute(array('name' => $_POST['name'],'text' => $_POST['text'],'category' => $_POST['category'],'subcat' => $_POST['subcat'],'company_id' => $_POST['company']));
                    $procedure_id=$db->lastInsertId();
                    $error=0;
                    $action='add';
                    $message=T_('Procédure ajoutée');
                } else {
                    $error=T_("Vous n'avez pas les droits d'ajouter une procédure");
                }
            } elseif($_POST['action']=='edit' && $token['procedure_id'])
            {
                if($rright['procedure_modify'])
                {
                    $procedure_id=$token['procedure_id'];
                    $qry=$db->prepare("UPDATE `tprocedures` SET `name`=:name, `text`=:text, `category`=:category,`subcat`=:subcat,`company_id`=:company_id  WHERE `id`=:id");
                    $qry->execute(array('name' => $_POST['name'],'text' => $_POST['text'],'category' => $_POST['category'],'subcat' => $_POST['subcat'],'company_id' => $_POST['company'],'id' => $procedure_id));
                    $error=0;
                    $action='edit';
                    $message=T_('Procédure modifiée');
                } else{
                    $error=T_("Vous n'avez pas les droits de modifier une procédure");
                }
            } else {
                $error=T_("Une erreur s'est produite, contactez votre administrateur");
            }

            //upload file
            if($_FILES['procedure_file']['name']) {
                //call upload file function
                $file_uid_uploaded=UploadFile($_FILES['procedure_file']['name'],$_FILES['procedure_file']['tmp_name'],'../upload/procedure/','procedure',$procedure_id,$token['user_id']);

                //generate file to display
                if($file_uid_uploaded['uid'])
                {
                    $uploaded_file='<a class="ml-2" href="index.php?page=procedure&download='.$file_uid_uploaded['uid'].'"><i class="fa fa-paperclip text-primary-m2"><!----></i> '.$_FILES['procedure_file']['name'].'</a>';
                    if($rright['procedure_modify']) {$uploaded_file.=' <a href="./index.php?page=procedure&id='.$procedure_id.'&action=edit&delete_file='.$file_uid_uploaded['uid'].'" title="'.T_('Supprimer').'"<i class="fa fa-trash text-danger"><!----></i></a>';}
                    $uploaded_file.='<br />';
                }
                if($file_uid_uploaded['error']) {$error=$file_uid_uploaded['error'];}
            }

            //send ajax return
            if($error)
            {
                echo json_encode(array("status" => "error","message" => $error,"delay" => $rparameters['time_display_msg']));
            } else {
                echo json_encode(array("status" => "success","message" => $message ,"delay" => $rparameters['time_display_msg'],"action" => $action,"procedure_id" => $procedure_id,"uploaded_file" => $uploaded_file));
            }
        } else {
            echo json_encode(array("status" => "error","message" => T_("Jeton invalide, contactez votre administrateur"),"delay" => $rparameters['time_display_msg']));
        }
    } else {
        echo json_encode(array("status" => "error","message" => T_("Jeton inexistant, contactez votre administrateur").$_POST['token'],"delay" => $rparameters['time_display_msg']));
    }
} else {
	echo json_encode(array("status" => "error","message" => 'Unauthorized access',"delay" => $rparameters['time_display_msg']));
    logit('security','Unauthorized access on ajax/procedure.php',0);
}
 //close database access
 $db = null;
?>