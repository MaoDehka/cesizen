<?php
################################################################################
# @Name : ticket_user_check.php
# @Description : check if user exist in database to display warning
# @Call : ./includes/ticket_user_check.php
# @Parameters :  
# @Author : Flox
# @Create : 06/12/2023
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

    //load function
    require('../core/functions.php');

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
        ini_set('display_errors', 'Off');ini_set('display_startup_errors', 'Off'); ini_set('html_errors', 'Off');error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    }

    //check existing token
    if(!$_GET['token']) {
        echo json_encode(array("status" => "error","message" => 'Missing token'));
        LogIt('error','ERROR 11 : Missing token on /ajax/ticket_user.php',0);
        exit;
    } else {
        //check valid token
        $qry=$db->prepare("SELECT `user_id` FROM `ttoken` WHERE token=:token AND `ip`=:ip");
        $qry->execute(array('token' => $_GET['token'],'ip' => $_SERVER['REMOTE_ADDR']));
        $token=$qry->fetch();
        $qry->closeCursor();
        if(empty($token['user_id']))
        {
            echo json_encode(array("status" => "error","message" => 'Invalid token'.$_GET['token']));
            LogIt('error','ERROR 12 : Invalid token on /ajax/ticket_user.php',0);
            exit;
        } else {
            //check 
            $qry=$db->prepare("SELECT `id`FROM `tusers` WHERE `mail`=:mail");
            $qry->execute(array('mail' => $_POST['usermail']));
            $row=$qry->fetch();
            $qry->closeCursor();
            if(!empty($row) && $_POST['usermail'])
            {
                echo json_encode(array("status" => "success", "find_user" => "mail"));
            } else {
                $qry=$db->prepare("SELECT `id`FROM `tusers` WHERE REPLACE(mobile, ' ', '') LIKE REPLACE(:mobile, ' ', '') ");
                $qry->execute(array('mobile' => $_POST['mobile']));
                $row=$qry->fetch();
                $qry->closeCursor();
                if(!empty($row) && $_POST['mobile'])
                {
                    echo json_encode(array("status" => "success", "find_user" => "mobile"));
                } else {
                    echo json_encode(array("status" => "success", "find_user" => "0"));
                }
            }
        }
    }
    //close database access
	$db = null;
} else {
    echo json_encode(array("status" => "error","message" => 'Unauthorized access'));
    LogIt('error','ERROR 15 : Unauthorized access on /ajax/ticket_user.php',0);
}
?>