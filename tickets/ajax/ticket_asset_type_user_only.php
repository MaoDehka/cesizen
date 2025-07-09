<?php
################################################################################
# @Name : ticket_asset_type_user_only.php
# @Description : send asset of selected asset type
# @Call : ./ticket.php
# @Parameters :  
# @Author : Flox
# @Create : 27/09/2023
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
    require('../connect.php');

    //switch SQL MODE to allow empty values
    $db->exec('SET sql_mode = ""');

    //check token validity
    $qry=$db->prepare("SELECT `token` FROM ttoken WHERE action='ticket_access' AND `token`=:token AND `ip`=:ip");
    $qry->execute(array('token' => $_POST['token'],'ip' => $_SERVER['REMOTE_ADDR']));
    $token=$qry->fetch();
    $qry->closeCursor();
    if(!empty($token['token'])) {

        //load parameters table
        $qry=$db->prepare("SELECT * FROM `tparameters`");
        $qry->execute();
        $rparameters=$qry->fetch();
        $qry->closeCursor();

        //display error parameter
        if($rparameters['debug']) {
            ini_set('display_errors', 'On');ini_set('display_startup_errors', 'On');ini_set('html_errors', 'On');error_reporting(E_ALL);
        } else {
            ini_set('display_errors', 'Off');ini_set('display_startup_errors', 'Off'); ini_set('html_errors', 'Off');error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
        }

        //init asset array
        $asset_arr=array();

        $asset_arr[] = array("id" => 0, "netbios" => 'Aucun', "selected" => '');

        //put each db value in array 
        $qry=$db->prepare("SELECT `id`,`netbios`,`user` FROM `tassets` WHERE `type` LIKE :asset_type AND `user`=:user ORDER BY `netbios`");
        $qry->execute(array('asset_type' => $_POST['TypeId'],'user' => $_POST['UserId']));
        while($asset=$qry->fetch()) 
        {
            $asset_arr[] = array("id" => $asset['id'], "netbios" => $asset['netbios'], "selected" => '');
        }
        $qry->closeCursor();

        //return array
        echo json_encode($asset_arr);

        //close database access
        $db = null;
    } else {
        echo json_encode(array("status" => "error","message" => "Invalid token"));
    }
} else {
	echo json_encode(array("status" => "error","message" => 'Unauthorized access'));
}
?>