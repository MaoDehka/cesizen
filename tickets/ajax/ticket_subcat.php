<?php
################################################################################
# @Name : ticket_subcat.php
# @Description : send subcat of current selected category
# @Call : ./ticket.php
# @Parameters :  
# @Author : Flox
# @Create : 25/08/2020
# @Update : 12/02/2024
# @Version : 3.2.48
################################################################################

//security check
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') 
{
    //init and secure POST var
	require_once('../core/init_post.php');
	require_once('../core/init_get.php');

    //load function
    require_once('../core/functions.php');

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
        $subcat_arr=array();

        //put each db value in array 
        $qry=$db->prepare("SELECT `id`,`name` FROM `tsubcat` WHERE `cat`=:id OR `id`='0' ORDER BY `id`!='0',`name`");
        $qry->execute(array('id' => $_POST['CategoryId']));
        while($subcat=$qry->fetch()) 
        {
            $subcat_arr[] = array("id" => $subcat['id'], "name" => $subcat['name']);
        }
        $qry->closeCursor();
        //return array
        echo json_encode($subcat_arr);

        //close database access
        $db = null;
    } else {
        LogIt('error','ERROR : Invalid token '.$_POST['token'].' on /ajax/ticket_subcat.php',0);
        echo json_encode(array("status" => "error","message" => "Invalid token"));
    }
} else {
    LogIt('error','ERROR : Unauthorized access on /ajax/ticket_subcat.php',0);
    echo json_encode(array("status" => "error","message" => 'Unauthorized access'));
}
?>