<?php
################################################################################
# @Name : procedure_subcat.php
# @Description : send subcat of current selected category
# @Call : ./ticket.php
# @Parameters :  
# @Author : Flox
# @Create : 30/09/2021
# @Update : 17/01/2024
# @Version : 3.2.47
################################################################################

//security check
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') 
{
    //init and secure var
	require_once('../core/init_post.php');
	require_once('../core/init_get.php');

    //db connection
    require_once('../connect.php');

    //switch SQL MODE to allow empty values
    $db->exec('SET sql_mode = ""');

    //call functions
    require_once('../core/functions.php');

    //check token validity
    $qry=$db->prepare("SELECT `token`FROM ttoken WHERE action='procedure_access' AND `token`=:token AND `ip`=:ip");
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
        if($rparameters['debug']) 
        {
            ini_set('display_errors', 'On'); ini_set('display_startup_errors', 'On');ini_set('html_errors', 'On');error_reporting(E_ALL);
        } else {
            ini_set('display_errors', 'Off');ini_set('display_startup_errors', 'Off');ini_set('html_errors', 'Off'); error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
        }

        //init string
        $subcat_arr=array();

        //put each db value in array 
        $qry=$db->prepare("SELECT `id`,`name` FROM `tsubcat` WHERE `cat`=:id ORDER BY `name`");
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
        echo json_encode(array("status" => "error","message" => "Invalid token"));
    }

} else {
	echo json_encode('ERROR : Unauthorized access');
    logit('security','Unauthorized access on ajax/procedure_subcat.php',0);
}
?>