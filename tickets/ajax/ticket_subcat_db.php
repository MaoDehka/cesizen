<?php
################################################################################
# @Name : ticket_subcat_db.php
# @Description : send subcat of current selected category
# @Call : ./ticket.php
# @Parameters :  
# @Author : Flox
# @Create : 25/08/2020
# @Update : 28/10/2020
# @Version : 3.2.6
################################################################################

//security check
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') 
{
    //db connection
    require('../connect.php');

    //switch SQL MODE to allow empty values
    $db->exec('SET sql_mode = ""');

    //load parameters table
    $qry=$db->prepare("SELECT * FROM `tparameters`");
    $qry->execute();
    $rparameters=$qry->fetch();
    $qry->closeCursor();

    //display error parameter
    if($rparameters['debug']) {
        ini_set('display_errors', 'On');
        ini_set('display_startup_errors', 'On');
        ini_set('html_errors', 'On');
        error_reporting(E_ALL);
    } else {
        ini_set('display_errors', 'Off');
        ini_set('display_startup_errors', 'Off');
        ini_set('html_errors', 'Off');
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    }

    //init string
    if(!isset($_POST['CategoryId'])) $_POST['CategoryId'] = '';
    $subcat_arr=array();

    //secure string
    $_POST['CategoryId']=htmlspecialchars($_POST['CategoryId'], ENT_QUOTES, 'UTF-8');

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
	echo json_encode(array("status" => "failed"));
}
?>