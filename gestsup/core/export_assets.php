<?php
################################################################################
# @Name : ./core/export_assets.php
# @Description : dump csv files of all assets
# @Call : /stat.php
# @Parameters : 
# @Author : Flox
# @Create : 11/02/2016
# @Update : 24/10/2023
# @Version : 3.2.50 p1
################################################################################

//locales
$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
if($lang=='fr') {$_GET['lang'] = 'fr_FR';}
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

//initialize variables 
require_once(__DIR__."/../core/init_get.php");

//database connection
require "../connect.php"; 

//secure var
$db_user_id=strip_tags($_GET['user_id']);
$db_technician=strip_tags($_GET['technician']);
$db_service=strip_tags($_GET['service']);
$db_month=strip_tags($_GET['month']);
$db_company=strip_tags($_GET['company']);
$db_model=strip_tags($_GET['model']);
$db_netbios=strip_tags($_GET['netbios']);

$db_userid=htmlspecialchars($db_userid, ENT_QUOTES, 'UTF-8');
$db_technician=htmlspecialchars($db_technician, ENT_QUOTES, 'UTF-8');
$db_service=htmlspecialchars($db_service, ENT_QUOTES, 'UTF-8');
$db_month=htmlspecialchars($db_month, ENT_QUOTES, 'UTF-8');
$db_company=htmlspecialchars($db_company, ENT_QUOTES, 'UTF-8');
$db_model=htmlspecialchars($db_model, ENT_QUOTES, 'UTF-8');
$db_netbios=htmlspecialchars($db_netbios, ENT_QUOTES, 'UTF-8');

//check var
if(!is_numeric($db_userid) && $db_userid!='%') {echo 'ERROR : incorrect value'; exit;}
if(!is_numeric($db_technician) && $db_technician!='%') {echo 'ERROR : incorrect value'; exit;}
if(!is_numeric($db_service) && $db_service!='%') {echo 'ERROR : incorrect value'; exit;}
if(!is_numeric($db_month) && $db_month!='%') {echo 'ERROR : incorrect value'; exit;}
if(!is_numeric($db_company) && $db_company!='%') {echo 'ERROR : incorrect value'; exit;}
if(!is_numeric($db_model) && $db_model!='%') {echo 'ERROR : incorrect value'; exit;}
if(!is_numeric($db_netbios) && $db_netbios!='%') {echo 'ERROR : incorrect value'; exit;}

//check db token
$qry=$db->prepare("SELECT `id`,`token` FROM `ttoken` WHERE `action`='stat_access'  AND `token`=:token AND `user_id`=:user_id AND `ip`=:ip");
$qry->execute(array('token' => $_GET['token'],'user_id' => $db_user_id,'ip' => $_SERVER['REMOTE_ADDR']));
$token=$qry->fetch();
$qry->closeCursor();
if(empty($token['id'])) {echo "ERROR : Wrong token"; exit;}

//secure connect from authenticated user
if($_GET['token']==$token['token'] && $_GET['token']) 
{
	//get current date
	$daydate=date('Y-m-d');

	//output headers so that the file is downloaded rather than displayed
	header('Content-Encoding: UTF-8');
	header("Content-Type: text/csv; charset=UTF-8");
	header("Content-Disposition: attachment; filename=\"$daydate-GestSup-export-asset.csv\"");
	
	//load parameters table
	$qry = $db->prepare("SELECT * FROM `tparameters`");
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

	//create a file pointer connected to the output stream
	$output = fopen('php://output', 'w');
	
	//test if company exist to display company column on CSV file
	$qry = $db->prepare("SELECT count(id) FROM tcompany WHERE disable='0'");
	$qry->execute();
	$company_cnt=$qry->fetch();
	$qry->closeCursor();
	
	if($company_cnt[0]>1 && $rparameters['user_advanced']==1) {$company=1;} else {$company=0;}
	
	if($company) //add company column if company exist
	{
		//output the column headings
		fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
		fputcsv($output, array(T_('Numéro de l\'équipement'), T_('Numéro de série constructeur'), T_('Numéro de commande'), T_('Nom'),  T_('Nom NetBIOS'), T_('IP'),  T_('MAC'), T_('Description'), T_('Type'), T_('Fabriquant'), T_('Modèle'),T_('Utilisateur'), T_('Société'), T_('État'), T_('Service'), T_('Localisation'), T_('Date installation'), T_('Date fin de garantie'), T_('Date stock'), T_('Date de Standbye'), T_('Date de recyclage'), T_('Date du dernier ping'), T_('Numéro de prise'), T_('Technicien'), T_('Service de maintenance')),";");
		
		$qry = $db->prepare("
		SELECT tassets.sn_internal,tassets.sn_manufacturer,tassets.sn_indent,tassets.netbios,1,2,3,tassets.description,tassets.type,tassets.manufacturer,tassets.model,tassets.user,tusers.company,tassets.state,tassets.department,tassets.location,tassets.date_install,tassets.date_end_warranty,tassets.date_stock,tassets.date_standbye,tassets.date_recycle,tassets.date_last_ping,tassets.socket,tassets.technician,tassets.maintenance,tassets.id
		FROM tassets,tusers
		WHERE
		tassets.user=tusers.id AND
		tassets.technician LIKE :technician AND
		tassets.department LIKE :department AND
		tassets.model LIKE :model AND
		tassets.netbios LIKE :netbios AND
		tassets.date_install LIKE :install AND
		tassets.date_install LIKE :install2 AND
		tassets.disable='0' AND
		tusers.company LIKE :company
		");
		$qry->execute(array(
			'technician' => $db_technician,
			'department' => $db_service,
			'model' => $db_model,
			'netbios' => $db_netbios,
			'install' => "%-$_GET[month]-%",
			'install2' => "$_GET[year]-%",
			'company' => $db_company
		));
	} else {
		//output the column headings
		fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
		fputcsv($output, array(T_('Numéro de l\'équipement'), T_('Numéro de série constructeur'), T_('Numéro de commande'), T_('Nom'),  T_('Nom NetBIOS'), T_('IP'),  T_('MAC'), T_('Description'), T_('Type'), T_('Fabriquant'), T_('Modèle'),T_('Utilisateur'), T_('État'), T_('Service'), T_('Localisation'), T_('Date installation'), T_('Date fin de garantie'), T_('Date stock'), T_('Date de Standbye'), T_('Date de recyclage'), T_('Date du dernier ping'), T_('Numéro de prise'), T_('Technicien'), T_('Service de maintenance')),";");
		
		$qry = $db->prepare("
		SELECT sn_internal,sn_manufacturer,sn_indent,netbios,1,2,3,description,type,manufacturer,model,user,state,department,location,date_install,date_end_warranty,date_stock,date_standbye,date_recycle,date_last_ping,socket,technician,maintenance,id
		FROM tassets 
		WHERE
		tassets.technician LIKE :technician AND
		tassets.department LIKE :department AND
		tassets.model LIKE :model AND
		tassets.netbios LIKE :netbios AND
		tassets.date_install LIKE :install AND
		tassets.date_install LIKE :install2 AND
		tassets.disable='0' 
		");
		$qry->execute(array(
			'technician' => $db_technician,
			'department' => $db_service,
			'model' => $db_model,
			'netbios' => $db_netbios,
			'install' => "%-$_GET[month]-%",
			'install2' => "$_GET[year]-%"
		));
	}
	
	//loop over the rows, outputting them
	while ($row = $qry->fetch(PDO::FETCH_ASSOC))   
	{
		//get name data
		$qry2=$db->prepare("SELECT name FROM tassets_type WHERE id=:id ");
		$qry2->execute(array('id' => $row['type']));
		$row2=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($row2['name'])) {$row2=array(); $row2['name']='';}
		$row['type']="$row2[name]";
		
		$qry2=$db->prepare("SELECT name FROM tassets_manufacturer WHERE id=:id ");
		$qry2->execute(array('id' => $row['manufacturer']));
		$row2=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($row2['name'])) {$row2=array(); $row2['name']='';}
		$row['manufacturer']="$row2[name]";
		
		$qry2=$db->prepare("SELECT name FROM tassets_model WHERE id=:id");
		$qry2->execute(array('id' => $row['model']));
		$row2=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($row2['name'])) {$row2=array(); $row2['name']='';}
		$row['model']="$row2[name]";
		
		$qry2=$db->prepare("SELECT firstname, lastname FROM tusers WHERE id=:id");
		$qry2->execute(array('id' => $row['user']));
		$row2=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($row2['firstname'])) {$row2=array(); $row2['firstname']='';}
		if(empty($row2['lastname'])) {$row2['lastname']='';}
		$row['user']="$row2[firstname] $row2[lastname]";	
		
		if($company)
		{
			$qry2=$db->prepare("SELECT name FROM tcompany WHERE id=:id ");
			$qry2->execute(array('id' => $row['company']));
			$row2=$qry2->fetch();
			$qry2->closeCursor();
			if(empty($row2['name'])) {$row2['name']='';}
			$row['company']=$row2['name'];
		}
		
		$qry2=$db->prepare("SELECT name FROM tassets_state WHERE id=:id ");
		$qry2->execute(array('id' => $row['state']));
		$row2=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($row2['name'])) {$row2=array(); $row2['name']='';}
		$row['state']=$row2['name'];	
		
		$qry2=$db->prepare("SELECT name FROM tservices WHERE id=:id ");
		$qry2->execute(array('id' => $row['department']));
		$row2=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($row2['name'])) {$row2=array(); $row2['name']='';}		
		$row['department']=$row2['name'];
		
		$qry2=$db->prepare("SELECT name FROM tassets_location WHERE id=:id ");
		$qry2->execute(array('id' => $row['location']));
		$row2=$qry2->fetch();
		$qry2->closeCursor();	
		if(empty($row2['name'])) {$row2=array(); $row2['name']='';}
		$row['location']=$row2['name'];
		
		$qry2=$db->prepare("SELECT firstname, lastname FROM tusers WHERE id=:id ");
		$qry2->execute(array('id' => $row['technician']));
		$row2=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($row2['firstname'])) {$row2=array(); $row2['firstname']='';}
		if(empty($row2['lastname'])) {$row2['lastname']='';}
		$row['technician']="$row2[firstname] $row2[lastname]";	
		
		$qry2=$db->prepare("SELECT name FROM tservices WHERE id=:id ");
		$qry2->execute(array('id' => $row['maintenance']));
		$row2=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($row2['name'])) {$row2=array(); $row2['name']='';}
		$row['maintenance']=$row2['name'];
		
		//get netbios from iface
		$row[1]='';
		$qry2=$db->prepare("SELECT netbios FROM tassets_iface WHERE asset_id=:asset_id AND disable='0'");
		$qry2->execute(array('asset_id' => $row['id']));
		while ($row2=$qry2->fetch())
		{
			if($row2['netbios']) {$row[1].=$row2['netbios'].'   ';}
		}
		$qry2->closeCursor();
		//get ip from iface
		$row[2]='';
		$qry2=$db->prepare("SELECT ip FROM tassets_iface WHERE asset_id=:asset_id AND disable='0'");
		$qry2->execute(array('asset_id' => $row['id']));
		while ($row2=$qry2->fetch())
		{
			if($row2['ip']) {$row[2].=$row2['ip'].'   ';}
		}
		$qry2->closeCursor(); 
		
		//get mac from iface
		$row[3]='';
		$qry2=$db->prepare("SELECT mac FROM tassets_iface WHERE asset_id=:asset_id AND disable='0'");
		$qry2->execute(array('asset_id' => $row['id']));
		while ($row2=$qry2->fetch())
		{
			if($row2['mac']) {$row[3].=$row2['mac'].'   ';}
		}
		$qry2->closeCursor(); 
		$row['id']='';
		fputcsv($output, $row,';');
	}
	$qry->closeCursor();
} else {
	echo '<br /><br /><span style="font-size: x-large; color: red; text-align:center;"><b>'.T_('Accès à cette page interdite, contactez votre administrateur').'.</b></span>';	
}
$db = null;
?>