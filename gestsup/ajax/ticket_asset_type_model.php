<?php
################################################################################
# @Name : ticket_asset_model.php
# @Description : send model of selected asset type
# @Call : ./ticket.php
# @Parameters :  
# @Author : Flox
# @Create : 24/10/2023
# @Update : 17/01/2024
# @Version : 3.2.47
################################################################################

//security check
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') 
{
    //init and secure POST var
	require_once('../core/init_post.php');
	require_once('../core/init_get.php');

    //db connection
    require_once('../connect.php');

    //switch SQL MODE to allow empty values
    $db->exec('SET sql_mode = ""');

    //load parameters table
    $qry=$db->prepare("SELECT * FROM `tparameters`");
    $qry->execute();
    $rparameters=$qry->fetch();
    $qry->closeCursor();

    //check token validity
    $qry=$db->prepare("SELECT `token` FROM ttoken WHERE action='ticket_access' AND `token`=:token AND `ip`=:ip");
    $qry->execute(array('token' => $_POST['token'],'ip' => $_SERVER['REMOTE_ADDR']));
    $token=$qry->fetch();
    $qry->closeCursor();
    if(!empty($token['token'])) {
        //display error parameter
        if($rparameters['debug']) {
            ini_set('display_errors', 'On');ini_set('display_startup_errors', 'On');ini_set('html_errors', 'On');error_reporting(E_ALL);
        } else {
            ini_set('display_errors', 'Off');ini_set('display_startup_errors', 'Off'); ini_set('html_errors', 'Off');error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
        }

        //init asset array
        $model_arr=array();

        $model_arr[] = array("id" => 0, "name" => 'Aucun', "selected" => '');

        //put each db value in array 
        $qry=$db->prepare("SELECT `id`,`name` FROM `tassets_model` WHERE `type` LIKE :asset_type ORDER BY `name`");
        $qry->execute(array('asset_type' => $_POST['TypeId']));
        while($model=$qry->fetch()) 
        {
            $model_arr[] = array("id" => $model['id'], "name" => $model['name'], "selected" => '');
        }
        $qry->closeCursor();

        //return array
        echo json_encode($model_arr);

        //close database access
        $db = null;
    } else {
        echo json_encode(array("status" => "error","message" => "Invalid token"));
    }
} else {
	echo json_encode(array("status" => "error","message" => 'Unauthorized access'));
}
?>