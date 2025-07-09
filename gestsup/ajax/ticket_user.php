<?php
################################################################################
# @Name : ticket_user.php
# @Description : submit data from modal form to database
# @Call : ./includes/ticket_user.php
# @Parameters :  
# @Author : Flox
# @Create : 25/08/2020
# @Update : 24/01/2024
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

            //check none use
            if($_GET['user_id']==0)
            {
                echo json_encode(array("status" => "error","message" => 'Forbidden none user update'));
                exit;
            }

            //check right
            if(!$rright['ticket_user_actions'])
            {
                echo json_encode(array("status" => "error","message" => 'Missing right ticket_user_actions'));
                LogIt('error','ERROR 13 : Missing right ticket_user_actions on /ajax/ticket_user.php',0);
                exit;
            } else {
                //mail control
                if(!filter_var($_POST['usermail'], FILTER_VALIDATE_EMAIL)) {$_POST['usermail']='';}

                //remove space in tel number
                $_POST['phone']=str_replace(' ','',$_POST['phone']);
                $_POST['mobile']=str_replace(' ','',$_POST['mobile']);

                //add user
                if($_POST['add']) 
                {
                    $qry=$db->prepare("INSERT INTO `tusers` (`profile`,`firstname`,`lastname`,`phone`,`mobile`,`mail`,`company`) VALUES (2,:firstname,:lastname,:phone,:mobile,:mail,:company)");
                    $qry->execute(array('firstname' => $_POST['firstname'],'lastname' => $_POST['lastname'],'phone' => $_POST['phone'],'mobile' => $_POST['mobile'],'mail' => $_POST['usermail'],'company' => $_POST['company']));
                    echo json_encode(array("status" => "success", "user_id" => $db->lastInsertId(), "firstname" => $_POST['firstname'],"lastname" => $_POST['lastname']));
                //modify user
                }elseif($_POST['modifyuser']) 
                {
                    $qry=$db->prepare("UPDATE `tusers` SET `firstname`=:firstname, `lastname`=:lastname, `phone`=:phone, `mobile`=:mobile, `mail`=:mail, `company`=:company WHERE `id`=:id");
                    $qry->execute(array('firstname' => $_POST['firstname'],'lastname' => $_POST['lastname'],'phone' => $_POST['phone'],'mobile' => $_POST['mobile'],'mail' => $_POST['usermail'],'company' => $_POST['company'],'id' => $_GET['user_id']));
                    echo json_encode(array("status" => "success", "user_id" => $_GET['user_id'], "firstname" => $_POST['firstname'],"lastname" => $_POST['lastname']));
                } else {
                    echo json_encode(array("status" => "failed", "message" => "Unavailable method"));
                    LogIt('error','ERROR 14 : Unavailable method on /ajax/ticket_user.php',0);
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