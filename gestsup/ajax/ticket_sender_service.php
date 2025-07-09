<?php
################################################################################
# @Name : ticket_sender_service.php
# @Description : send service of current user selected on ticket to populate sender service field
# @Call : ./ticket.php
# @Parameters :  
# @Author : Flox
# @Create : 19/09/2021
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
            ini_set('display_errors', 'Off'); ini_set('display_startup_errors', 'Off'); ini_set('html_errors', 'Off');error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
        }

        //init array
        $service_arr=array();

        //put each db value in array 
        $qry=$db->prepare("SELECT `id`,`name` FROM `tservices` WHERE `id` IN (SELECT `service_id` FROM `tusers_services` WHERE `user_id`=:user_id) ORDER BY `name`");
        $qry->execute(array('user_id' => $_POST['UserId']));
        while($service=$qry->fetch()) 
        {
        $service_arr[] = array("id" => $service['id'], "name" => $service['name']);
        }
        $qry->closeCursor();

        //return array
        echo json_encode($service_arr);

        //close database access
        $db = null;
    } else {
        echo json_encode(array("status" => "error","message" => "Invalid token"));
    }
} else {
    echo json_encode(array("status" => "error","message" => 'Unauthorized access'));
}
?>