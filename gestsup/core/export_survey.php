<?php
################################################################################
# @Name : ./core/export_survey.php
# @Description : dump csv files of survey
# @call : /stat.php
# @parameters : 
# @Author : Flox
# @Create : 01/05/2017
# @Update : 01/09/2022
# @Version : 3.2.26
################################################################################

//initialize variables 
require_once(__DIR__."/../core/init_get.php");

//locales
$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
if ($lang=='fr') {$_GET['lang'] = 'fr_FR';}
else {$_GET['lang'] = 'en_US';}

define('PROJECT_DIR', realpath('../'));
define('LOCALE_DIR', PROJECT_DIR .'/locale');
define('DEFAULT_LOCALE', '($_GET[lang]');
require_once('../vendor/components/php-gettext/gettext.inc');
$encoding = 'UTF-8';
$locale = (isset($_GET['lang']))? $_GET['lang'] : DEFAULT_LOCALE;
T_setlocale(LC_MESSAGES, $locale);
T_bindtextdomain($_GET['lang'], LOCALE_DIR);
T_bind_textdomain_codeset($_GET['lang'], $encoding);
T_textdomain($_GET['lang']);

//database connection
require "../connect.php"; 

//load parameter table
$qry=$db->prepare("SELECT * FROM tparameters");
$qry->execute();
$rparameters=$qry->fetch();
$qry->closeCursor();

if(!$rparameters['survey']) {echo 'ERROR : survey function disable'; exit;}

//load token table
$qry=$db->prepare("SELECT `id` FROM ttoken WHERE action='admin_function_access' AND token=:token AND `user_id`=:user_id AND `ip`=:ip");
$qry->execute(array('token' => $_GET['token'],'user_id' => $_GET['user_id'],'ip' => $_SERVER['REMOTE_ADDR']));
$token=$qry->fetch();
$qry->closeCursor();

//secure connect from authenticated user
if($token)
{
	//get current date
	$daydate=date('Y-m-d');

	//output headers so that the file is downloaded rather than displayed
	header('Content-Encoding: UTF-8');
	header("Content-Type: text/csv; charset=UTF-8");
	header("Content-Disposition: attachment; filename=\"$daydate-GestSup-export-survey.csv\"");
	

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


	//create a file pointer connected to the output stream
	$output = fopen('php://output', 'w');
	
	$col_title=array();
	array_push($col_title,T_('Date'));
	array_push($col_title,T_('N° Ticket'));
	array_push($col_title,T_('Titre ticket'));
	array_push($col_title,T_('Utilisateur'));
	
	$qry = $db->prepare("SELECT text FROM tsurvey_questions ORDER BY number");
	$qry->execute();
	while ($row = $qry->fetch(PDO::FETCH_ASSOC)) 
	{
		array_push($col_title,$row['text']);
	}
	$qry->closeCursor();
	
	//output the column headings
	fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
	fputcsv($output, $col_title,";");
	
	//get each ticket
	$qry = $db->prepare("SELECT distinct(tsurvey_answers.ticket_id) FROM `tsurvey_answers`,`tincidents` WHERE tsurvey_answers.ticket_id=tincidents.id AND tincidents.disable='0' ORDER BY ticket_id DESC");
	$qry->execute();
	while ($row = $qry->fetch(PDO::FETCH_ASSOC)) 
	{	
		$outputrow=array();
		//get validate date
		$qry2=$db->prepare("SELECT MAX(date) FROM tsurvey_answers WHERE ticket_id=:ticket_id");
		$qry2->execute(array('ticket_id' => $row['ticket_id']));
		$date=$qry2->fetch();
		$qry2->closeCursor();
		
		//count number of questions for this ticket
		$qry2=$db->prepare("SELECT count(id) FROM tsurvey_answers WHERE ticket_id=:ticket_id");
		$qry2->execute(array('ticket_id' => $row['ticket_id']));
		$count=$qry2->fetch();
		$qry2->closeCursor();
		
		//get firstname and lastname of user attached with this ticket
		$qry2=$db->prepare("SELECT firstname,lastname FROM tusers WHERE id=(SELECT user FROM tincidents WHERE id=:id)");
		$qry2->execute(array('id' => $row['ticket_id']));
		$user=$qry2->fetch();
		$qry2->closeCursor();
		
		//get title of this ticket
		$qry2=$db->prepare("SELECT title FROM tincidents WHERE id=:id");
		$qry2->execute(array('id' => $row['ticket_id']));
		$title=$qry2->fetch();
		$qry2->closeCursor();
		
		//for each ticket
		array_push($outputrow,$date[0]);
		array_push($outputrow,$row['ticket_id']);
		array_push($outputrow,$title[0]);
		array_push($outputrow,"$user[firstname] $user[lastname]");
		for($i=1;$i<=$count[0];$i++)
		{
			//get answer data for question $i
			$qry2=$db->prepare("SELECT answer FROM tsurvey_answers WHERE ticket_id=:ticket_id AND question_id=(SELECT id FROM tsurvey_questions WHERE number=:number)");
			$qry2->execute(array('ticket_id' => $row['ticket_id'],'number' => $i));
			$answer=$qry2->fetch();
			$qry2->closeCursor();
			
			$col=$i+2;
			array_push($outputrow,$answer[0]);
		}
		fputcsv($output,$outputrow,';');
	}
	$qry->closeCursor();
} else {
	echo 'ERROR : Wrong token';
}
$db = null;
?>