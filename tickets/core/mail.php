<?php
################################################################################
# @Name : /core/mail.php
# @Description : page to send mail
# @Call : /preview_mail.php, /core/auto_mail.php
# @Parameters : ticket id destinataires
# @Author : Flox
# @Create : 15/07/2014
# @Update : 30/04/2024
# @Version : 3.2.50 p1
################################################################################

if(!$rparameters['mail']) {echo DisplayMessage('error',T_('Le connecteur SMTP est désactivé')); exit;}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;
use Greew\OAuth2\Client\Provider\Azure;

if(file_exists('core/functions.php')) {require_once('core/functions.php');}

//initialize variables 
if(!isset($_POST['usercopy'])) $_POST['usercopy'] = '';
if(!isset($_POST['usercopy2'])) $_POST['usercopy2'] = '';
if(!isset($_POST['usercopy3'])) $_POST['usercopy3'] = '';
if(!isset($_POST['usercopy4'])) $_POST['usercopy4'] = '';
if(!isset($_POST['usercopy5'])) $_POST['usercopy5'] = '';
if(!isset($_POST['usercopy6'])) $_POST['usercopy6'] = '';
if(!isset($_POST['manual_address'])) $_POST['manual_address'] = '';
if(!isset($_POST['usercopy_cci'])) $_POST['usercopy_cci'] = '';
if(!isset($_POST['usercopy2_cci'])) $_POST['usercopy2_cci'] = '';
if(!isset($_POST['usercopy3_cci'])) $_POST['usercopy3_cci'] = '';
if(!isset($_POST['usercopy4_cci'])) $_POST['usercopy4_cci'] = '';
if(!isset($_POST['usercopy5_cci'])) $_POST['usercopy5_cci'] = '';
if(!isset($_POST['usercopy6_cci'])) $_POST['usercopy6_cci'] = '';
if(!isset($_POST['manual_address_cci'])) $_POST['manual_address_cci'] = '';
if(!isset($_POST['receiver'])) $_POST['receiver'] = ''; 
if(!isset($_POST['withattachment'])) $_POST['withattachment'] = ''; 
if(!isset($_GET['state'])) $_GET['state'] = ''; 
if(!isset($_GET['userid'])) $_GET['userid'] = ''; 
if(!isset($_GET['view'])) $_GET['view'] = ''; 
if(!isset($_GET['date_start'])) $_GET['date_start'] = ''; 
if(!isset($_GET['date_end'])) $_GET['date_end'] = ''; 
if(!isset($fname11)) $fname11 = '';
if(!isset($fname21)) $fname21 = '';
if(!isset($fname31)) $fname31 = '';
if(!isset($fname41)) $fname41 = '';
if(!isset($fname51)) $fname51 = '';
if(!isset($resolution)) $resolution = '';
if(!isset($mail_text_end)) $mail_text_end = '';
if(!isset($rtech4['firstname'])) $rtech4['firstname'] = '';
if(!isset($rtech4['lastname'])) $rtech4['lastname'] = '';
if(!isset($rtech5['firstname'])) $rtech5['firstname'] = '';
if(!isset($rtech5['lastname'])) $rtech5['lastname'] = '';
if(!isset($rtechgroup4['name'])) $rtechgroup4['name'] = '';
if(!isset($rtechgroup5['name'])) $rtechgroup5['name'] = '';
if(!isset($mail_auto)) $mail_auto=true;
if(!isset($creatorrow['mail'])) $creatorrow['mail']='';
if(!isset($techrow['custom1'])) $techrow['custom1']='';
if(!isset($techrow['custom2'])) $techrow['custom2']='';
if(!isset($techrow['phone'])) $techrow['phone']='';
if(!isset($techrow['mobile'])) $techrow['mobile']='';
if(!isset($techrow['mail'])) $techrow['mail']='';
if(!isset($techrow['function'])) $techrow['function']='';
if(!isset($placerow['name'])) $placerow['name']='';
if(!isset($_SESSION['user_id'])) $_SESSION['user_id'] = '';
if(!isset($_GET['companyview'])) $_GET['companyview'] = '';
if(!isset($to)) $to='';
$resolution=''; //init var for loop

$mail_send_error=false;
$db_id=strip_tags($_GET['id']);
$dest_mail=0;

//database queries to get values for create mail	
$qry=$db->prepare("SELECT * FROM `tincidents` WHERE `id`=:id");
$qry->execute(array('id' => $db_id));
$globalrow=$qry->fetch();
$qry->closeCursor();

$qry=$db->prepare("SELECT `id`,`mail`,`firstname`,`lastname`,`company`,`language` FROM `tusers` WHERE `id`=:id");
$qry->execute(array('id' => $globalrow['user']));
$userrow=$qry->fetch();
$qry->closeCursor();

//define mail language with user language
if($userrow['language'])
{
	$_GET['lang']=$userrow['language'];
	require_once( __DIR__.'/../vendor/components/php-gettext/gettext.inc');
	$encoding = 'UTF-8';
	$locale = (isset($_GET['lang']))? $_GET['lang'] : DEFAULT_LOCALE;
	T_setlocale(LC_MESSAGES, $locale);
	T_bindtextdomain($_GET['lang'], LOCALE_DIR);
	T_bind_textdomain_codeset($_GET['lang'], $encoding);
	T_textdomain($_GET['lang']);
	//PHP 7 & Windows server fix
	putenv("LC_ALL=$_GET[lang]");
	setlocale(LC_ALL, $_GET['lang']);
}

$qry=$db->prepare("SELECT `id`,`mail`,`firstname`,`lastname`,`phone`,`mobile`,`custom1`,`custom2`,`function` FROM `tusers` WHERE id=:id");
$qry->execute(array('id' => $globalrow['technician']));
$techrow=$qry->fetch();
$qry->closeCursor();
if(!isset($techrow['firstname'])) {
	$techrow=array(); 
	$techrow['firstname']=''; 
	$techrow['lastname']=''; 
	$techrow['phone']=''; 
	$techrow['function']=''; 
	$techrow['mobile']=''; 
	$techrow['custom1']=''; 
	$techrow['custom2']='';
	$techrow['mail']='';
}
$technician_services='';
$qry=$db->prepare("SELECT `name` FROM `tservices`,`tusers_services` WHERE `tservices`.`id`=`tusers_services`.`service_id` AND `tusers_services`.`user_id`=:user_id");
$qry->execute(array('user_id' => $globalrow['technician']));
while($row=$qry->fetch()) {$technician_services.=$row['name'].' ';}
$qry->closeCursor();

$technician_service='';
$qry=$db->prepare("SELECT `name` FROM `tservices`,`tusers_services` WHERE `tservices`.`id`=`tusers_services`.`service_id` AND `tusers_services`.`user_id`=:user_id ORDER BY `tusers_services`.`id` LIMIT 1");
$qry->execute(array('user_id' => $globalrow['technician']));
while($row=$qry->fetch()) {$technician_service=$row['name'];}
$qry->closeCursor();

$qry=$db->prepare("SELECT `name` FROM `tcompany` WHERE disable='0' AND id=:id");
$qry->execute(array('id' => $userrow['company']));
$companyrow=$qry->fetch();
$qry->closeCursor();
if(!isset($companyrow['name'])) {$companyrow=array(); $companyrow['name']='';}

$qry=$db->prepare("SELECT `name` FROM `tpriority` WHERE id=:id");
$qry->execute(array('id' => $globalrow['priority']));
$priorityrow=$qry->fetch();
$qry->closeCursor();
if(!isset($priorityrow['name'])) {$priorityrow=array(); $priorityrow['name']='';}

$qry=$db->prepare("SELECT `name` FROM `tcriticality` WHERE id=:id");
$qry->execute(array('id' => $globalrow['criticality']));
$criticalityrow=$qry->fetch();
$qry->closeCursor();
if(!isset($criticalityrow['name'])) {$criticalityrow=array(); $criticalityrow['name']='';}

//group case
if($globalrow['t_group']!=0)
{
	$qry=$db->prepare("SELECT `name` FROM `tgroups` WHERE id=:id");
	$qry->execute(array('id' => $globalrow['t_group']));
	$grouptech=$qry->fetch();
	$qry->closeCursor();
}
if($globalrow['u_group']!=0)
{
	$qry=$db->prepare("SELECT `name` FROM `tgroups` WHERE id=:id");
	$qry->execute(array('id' => $globalrow['u_group']));
	$groupuser=$qry->fetch();
	$qry->closeCursor();
}

//case no send mail from mail2ticket
if($_SESSION['user_id'] && !$creatorrow['mail'])
{
	$qry=$db->prepare("SELECT mail FROM `tusers` WHERE id=:id");
	$qry->execute(array('id' => $_SESSION['user_id']));
	$creatorrow=$qry->fetch();
	$qry->closeCursor();
}	

$qry=$db->prepare("SELECT `name` FROM `tstates` WHERE id=:id");
$qry->execute(array('id' => $globalrow['state']));
$staterow=$qry->fetch();
$qry->closeCursor();
	
$qry=$db->prepare("SELECT `name` FROM `tcategory` WHERE id=:id");
$qry->execute(array('id' => $globalrow['category']));
$catrow=$qry->fetch();
$qry->closeCursor();
	
$qry=$db->prepare("SELECT `name` FROM `tsubcat` WHERE id=:id");
$qry->execute(array('id' => $globalrow['subcat']));
$subcatrow=$qry->fetch();
$qry->closeCursor();	

$qry=$db->prepare("SELECT `name` FROM `ttypes` WHERE id=:id");
$qry->execute(array('id' => $globalrow['type']));
$typerow=$qry->fetch();
$qry->closeCursor();

//case place parameter
if($rparameters['ticket_places'])
{
	$qry=$db->prepare("SELECT `id`,`name` FROM `tplaces` WHERE id=:id");
	$qry->execute(array('id' => $globalrow['place']));
	$placerow=$qry->fetch();
	$qry->closeCursor();
}

//generate resolution
if($rparameters['mail_order']) {
	$qry=$db->prepare("SELECT * FROM `tthreads` WHERE `ticket`=:ticket AND `private`=0 ORDER BY `date` DESC");
} else {
	$qry=$db->prepare("SELECT * FROM `tthreads` WHERE `ticket`=:ticket AND `private`=0 ORDER BY `date` ASC");
}
$qry->execute(array('ticket' => $db_id));
while($row=$qry->fetch()) 
{
	//remove display date from old post 
	$find_old=explode(" ", $row['date']);
	$find_old=$find_old[1];
	if($find_old!='12:00:00') $date_thread=date_convert($row['date']); else $date_thread='';
		
	if($row['type']==0)
	{
		$text=$row['text'];
		//find author name
		$qry2=$db->prepare("SELECT `firstname`,`lastname` FROM `tusers` WHERE id=:id");
		$qry2->execute(array('id' => $row['author']));
		$rauthor=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($rauthor['firstname'])) {$rauthor['firstname']='';}
		if(empty($rauthor['lastname'])) {$rauthor['lastname']='';}
		if($date_thread)
		{
			$resolution.="<b> $date_thread $rauthor[firstname] $rauthor[lastname] : </b><br /> $text  <hr />";
		} else {
			$resolution.="<b> $rauthor[firstname] $rauthor[lastname] : </b><br /> $text  <hr />";
		}
	} elseif($row['type']==1) {
		//generate attribution thread
		if($row['group1']!=0)
		{
			$qry2=$db->prepare("SELECT `name` FROM `tgroups` WHERE id=:id");
			$qry2->execute(array('id' => $row['group1']));
			$rtechgroup=$qry2->fetch();
			$qry2->closeCursor();

			$resolution=$resolution.' <b>'.$date_thread.' :</b> '.T_('Attribution du ticket au groupe').' '.$rtechgroup['name'].'.<br /><br />';
		} else {
			$qry2=$db->prepare("SELECT `firstname`,`lastname` FROM `tusers` WHERE id=:id");
			$qry2->execute(array('id' => $row['tech1']));
			$rtech3=$qry2->fetch();
			$qry2->closeCursor();
			if(!isset($rtech3['firstname'])) {$rtech3=array();$rtech3['firstname']='';}
			if(!isset($rtech3['lastname'])) {$rtech3['lastname']='';}
			
			$resolution=$resolution.' <b>'.$date_thread.' :</b> '.T_('Attribution du ticket à').' '.$rtech3['firstname'].' '.$rtech3['lastname'].'.<br /><br />';
		}
	} elseif($row['type']==2) {
		//generate transfert thread
		if($row['group1']!=0 && $row['group2']!=0) //case group to group 
		{
			$qry2=$db->prepare("SELECT `name` FROM `tgroups` WHERE id=:id");
			$qry2->execute(array('id' => $row['group1']));
			$rtechgroup1=$qry2->fetch();
			$qry2->closeCursor();
			if(!isset($rtechgroup1['name'])) {$rtechgroup1=array(); $rtechgroup1['name']=''; }
			
			$qry2=$db->prepare("SELECT `name` FROM `tgroups` WHERE id=:id");
			$qry2->execute(array('id' => $row['group2']));
			$rtechgroup2=$qry2->fetch();
			$qry2->closeCursor();
			if(!isset($rtechgroup2['name'])) {$rtechgroup2=array(); $rtechgroup2['name']=''; }
			
			$resolution=$resolution.' <b>'.$date_thread.' :</b> '.T_('Transfert du ticket du groupe').' '.$rtechgroup1['name'].' '.T_('au groupe ').' '.$rtechgroup2['name'].'. <br /><br />';
		} elseif(($row['tech1']==0 || $row['tech2']==0) && ($row['group1']==0 || $row['group2']==0)) { //case group to tech
			if($row['tech1']!=0) {
				$qry2=$db->prepare("SELECT `firstname`,`lastname` FROM `tusers` WHERE id=:id");
				$qry2->execute(array('id' => $row['tech1']));
				$rtech4=$qry2->fetch();
				$qry2->closeCursor();
				if(!isset($rtech4['firstname'])) {$rtech4=array(); $rtech4['firstname']=''; $rtech4['lastname']='';}
			}
			if($row['tech2']!=0) {
				$qry2=$db->prepare("SELECT `firstname`,`lastname` FROM `tusers` WHERE id=:id");
				$qry2->execute(array('id' => $row['tech2']));
				$rtech5=$qry2->fetch();
				$qry2->closeCursor();
				if(!isset($rtech5['firstname'])) {$rtech5=array(); $rtech5['firstname']=''; $rtech5['lastname']='';}
			}
			if($row['group1']!=0) {
				$qry2=$db->prepare("SELECT `name` FROM `tgroups` WHERE id=:id");
				$qry2->execute(array('id' => $row['group1']));
				$rtechgroup4=$qry2->fetch();
				$qry2->closeCursor();
				if(!isset($rtechgroup4['name'])) {$rtechgroup4=array(); $rtechgroup4['name']='';}
			}
			if($row['group2']!=0) {
				$qry2=$db->prepare("SELECT `name` FROM `tgroups` WHERE id=:id");
				$qry2->execute(array('id' => $row['group2']));
				$rtechgroup5=$qry2->fetch();
				$qry2->closeCursor();
				if(!isset($rtechgroup5['name'])) {$rtechgroup5=array(); $rtechgroup5['name']='';}
			}
			$resolution=$resolution.' <b>'.$date_thread.' :</b> '.T_('Transfert du ticket de').' '.$rtechgroup4['name'].$rtech4['firstname'].' '.$rtech4['lastname'].' '.T_('à').' '.$rtechgroup5['name'].$rtech5['firstname'].' '.$rtech5['lastname'].'. <br /><br />';
	} elseif($row['tech1']!=0 && $row['tech2']!=0) { //case tech to tech
			$qry2=$db->prepare("SELECT `firstname`,`lastname` FROM `tusers` WHERE id=:id");
			$qry2->execute(array('id' => $row['tech1']));
			$rtech1=$qry2->fetch();
			$qry2->closeCursor();
						
			$qry2=$db->prepare("SELECT `firstname`,`lastname` FROM `tusers` WHERE id=:id");
			$qry2->execute(array('id' => $row['tech2']));
			$rtech2=$qry2->fetch();
			$qry2->closeCursor();

			if(!isset($rtech1['firstname'])) {$rtech1=array(); $rtech1['firstname']='';}
			if(!isset($rtech1['lastname'])) {$rtech1['lastname']='';}
			if(!isset($rtech2['firstname'])) {$rtech2=array(); $rtech2['firstname']='';}
			if(!isset($rtech2['lastname'])) {$rtech2['lastname']='';}
			
			$resolution=$resolution.' <b>'.$date_thread.' :</b> '.T_('Transfert du ticket de').' '.$rtech1['firstname'].' '.$rtech1['lastname'].' '.T_('à').' '.$rtech2['firstname'].' '.$rtech2['lastname'].'. <br /><br />';
		}
	}
}
$qry->closeCursor();
$description = $globalrow['description'];

//set max image width
preg_match_all('/<img[^>]+>/i',$description, $imgs); 
foreach($imgs[0] as $img)
{
	$new_img=str_replace('style="width:', 'style="max-width:1200px; width:', $img);
	$description=str_replace($img,$new_img,$description);
}
preg_match_all('/<img[^>]+>/i',$resolution, $imgs); 
foreach($imgs[0] as $img)
{
	$new_img=str_replace('style="width:', 'style="max-width:1200px; width:', $img);
	$resolution=str_replace($img,$new_img,$resolution);
}

//date conversion
$date_create = date_cnv("$globalrow[date_create]");
$date_hope = date_cnv("$globalrow[date_hope]");
$date_res = date_cnv("$globalrow[date_res]");

if($date_create=='00/00/0000') {$date_create='';}
if($date_hope=='00/00/0000') {$date_hope='';}
if($date_res=='00/00/0000') {$date_res='';}
	
//generate mail object via db state name
$qry=$db->prepare("SELECT `mail_object` FROM `tstates` WHERE id=:id");
$qry->execute(array('id' => $globalrow['state']));
$robject=$qry->fetch();
$qry->closeCursor();
$object=T_($robject['mail_object']).' '.T_('pour le ticket').' n°'.$db_id.' : '.htmlspecialchars_decode($globalrow['title'], ENT_QUOTES);

//recipient user mail
$recipient=$userrow['mail'];

//define sender address
if($rparameters['mail_from_adr']){$sender=$rparameters['mail_from_adr'];} else {$sender=$creatorrow['mail'];}

if($rparameters['imap'] && $rparameters['imap_from_adr_service'] && $rparameters['imap_mailbox_service'] && $globalrow['u_service'])
{
	//check if ticket service have mail address on imap connector
	$qry=$db->prepare("SELECT `mail` FROM `tparameters_imap_multi_mailbox` WHERE `service_id`=:service_id");
	$qry->execute(array('service_id' => $globalrow['u_service']));
	$imap_svc=$qry->fetch();
	$qry->closeCursor();
	if(!empty($imap_svc['mail'])){$sender=$imap_svc['mail'];}
}

if(!$sender)
{
	echo DisplayMessage('error',T_("Aucune adresse mail d'émission n'est définie").$sender);
	//log
	if($rparameters['log']) {LogIt('error', 'ERROR 28 : SMTP, no sender mail defined', $_SESSION['user_id']);}
	die();
}

//define reply address
if($rparameters['mail_reply']=='sender'){
	$mail_reply=$sender;
} elseif($creatorrow['mail']) {
	$mail_reply=$creatorrow['mail'];
} else {
	$mail_reply=$rparameters['mail_from_adr'];
}

//display custom end text mail, else auto generate
if($rparameters['mail_txt_end'])
{
	//regenerate HTML tag
	$rparameters['mail_txt_end']=htmlspecialchars_decode($rparameters['mail_txt_end'], ENT_QUOTES);

	//generate mail end text
	$mail_text_end=str_replace("[tech_name]", "$techrow[firstname] $techrow[lastname]", $rparameters['mail_txt_end']);
	$mail_text_end=str_replace("[tech_phone]", "$techrow[phone]", $mail_text_end);
	if($rparameters['mail_link'] && $rparameters['server_url']) {
		$link='<a href="'.$rparameters['server_url'].'/index.php?page=ticket&id='.$_GET['id'].'">'.$rparameters['server_url'].'/index.php?page=ticket&id='.$_GET['id'].'</a>';
		$mail_text_end=str_replace("[link]", "$link", $mail_text_end);
	}
} else { //auto end mail
	if($rparameters['mail_link'] && $rparameters['server_url']) //integer link parameter
	{
		$link=', '.T_('ou consultez votre ticket sur ce lien').' : <a href="'.$rparameters['server_url'].'/index.php?page=ticket&id='.$_GET['id'].'">'.$rparameters['server_url'].'/index.php?page=ticket&id='.$_GET['id'].'</a>';	
	} else $link=".";
	if(($techrow['lastname']!='Aucun') && ($techrow['phone']!='')) //case technician phone
	{$mail_text_end=T_('Pour toutes informations complémentaires sur votre ticket, vous pouvez joindre').' '.$techrow['firstname'].' '.$techrow['lastname'].' '.T_('au').' '.$techrow['phone'].' '.$link;}
	elseif($rparameters['mail_link']==1) //case technician no phone
	{$mail_text_end=T_("Vous pouvez suivre l'état d'avancement de votre ticket sur ce lien : ").'<a href="'.$rparameters['server_url'].'/index.php?page=ticket&id='.$_GET['id'].'">'.$rparameters['server_url'].'/index.php?page=ticket&id='.$_GET['id'].'</a>';}
}

//add tag in mail to split fonction of imap connector
if($rparameters['imap']==1 && $rparameters['imap_reply']==1) {$msg='---- '.T_('Repondre au dessus de cette ligne').' ----';} else {$msg='';}

#template filename definition
$template_filename=__DIR__.'/../'.'template/mail/'.$rparameters['mail_template'];

if(file_exists($template_filename))
{
	//load template
	$mail_template=file_get_contents($template_filename);
	
	//translate none values
	if($userrow['firstname']=='Aucun') {$userrow['firstname']=T_('Aucun');}
	if($userrow['lastname']=='Aucun') {$userrow['lastname']=T_('Aucun');}
	if($techrow['firstname']=='Aucun') {$techrow['firstname']=T_('Aucun');}
	if($techrow['lastname']=='Aucun') {$techrow['lastname']=T_('Aucun');}
	if($catrow['name']=='Aucune') {$catrow['name']=T_('Aucune');}
	if($subcatrow['name']=='Aucune') {$subcatrow['name']=T_('Aucune');}
	
	//replace mail tag
	$mail_template=str_replace('#mail_color_title#', $rparameters['mail_color_title'], $mail_template);
	$mail_template=str_replace('#mail_color_text#', $rparameters['mail_color_text'], $mail_template);
	$mail_template=str_replace('#mail_object#', $object, $mail_template); 
	$mail_template=str_replace('#mail_txt#', T_(htmlspecialchars_decode($rparameters['mail_txt'], ENT_QUOTES)), $mail_template);
	$mail_template=str_replace('#mail_txt_end#', $mail_text_end, $mail_template);
	$mail_template=str_replace('#mail_color_title#', $rparameters['mail_color_title'], $mail_template);
	$mail_template=str_replace('#mail_color_text#', $rparameters['mail_color_text'], $mail_template);
	$mail_template=str_replace('#mail_color_bg#', $rparameters['mail_color_bg'], $mail_template);
		
	//translate field name
	$mail_template=str_replace('#type#', T_('Type'), $mail_template);
	$mail_template=str_replace('#title#', T_('Titre'), $mail_template);
	$mail_template=str_replace('#category#', T_('Catégorie'), $mail_template);
	$mail_template=str_replace('#user#', T_('Demandeur'), $mail_template);
	$mail_template=str_replace('#technician#', T_('Technicien'), $mail_template);
	$mail_template=str_replace('#state#', T_('État'), $mail_template);
	$mail_template=str_replace('#place#', T_('Lieu'), $mail_template);
	$mail_template=str_replace('#date_create#', T_('Date de la demande'), $mail_template);
	$mail_template=str_replace('#description#', T_('Description'), $mail_template);
	$mail_template=str_replace('#resolution#', T_('Résolution'), $mail_template);
	$mail_template=str_replace('#date_hope#', T_('Date de résolution estimée'), $mail_template);
	$mail_template=str_replace('#date_res#', T_('Date de résolution'), $mail_template);
	$mail_template=str_replace('#company#', T_('Société'), $mail_template);
	$mail_template=str_replace('#asset#', T_('Équipement'), $mail_template);
	
	//replace ticket tag
	$mail_template=str_replace('#ticket_id#', $globalrow['id'], $mail_template);
	$mail_template=str_replace('#ticket_type#', $typerow['name'], $mail_template);
	$mail_template=str_replace('#ticket_title#', $globalrow['title'], $mail_template);
	$mail_template=str_replace('#ticket_category#', $catrow['name'], $mail_template);
	$mail_template=str_replace('#ticket_subcat#', $subcatrow['name'], $mail_template);
	if($globalrow['u_group']) {$mail_template=str_replace('#ticket_user#', $groupuser['name'], $mail_template);} else {$mail_template=str_replace('#ticket_user#', $userrow['firstname'].' '.strtoupper($userrow['lastname']), $mail_template);}
	if($globalrow['t_group']) {$mail_template=str_replace('#ticket_technician#', $grouptech['name'], $mail_template);} else {$mail_template=str_replace('#ticket_technician#', $techrow['firstname'].' '.strtoupper($techrow['lastname']), $mail_template);}
	$mail_template=str_replace('#ticket_state#', T_($staterow['name']), $mail_template);
	$mail_template=str_replace('#ticket_priority#', $priorityrow['name'], $mail_template);
	$mail_template=str_replace('#ticket_criticality#', $criticalityrow['name'], $mail_template);
	$mail_template=str_replace('#ticket_place#', $placerow['name'], $mail_template);
	$mail_template=str_replace('#ticket_date_create#', $date_create, $mail_template);
	$mail_template=str_replace('#ticket_description#', $description, $mail_template);
	$mail_template=str_replace('#ticket_resolution#', $resolution, $mail_template);
	$mail_template=str_replace('#ticket_date_hope#', $date_hope, $mail_template);
	$mail_template=str_replace('#ticket_date_res#', $date_res, $mail_template);
	if(isset($companyrow['name'])) {$mail_template=str_replace('#ticket_company#', $companyrow['name'], $mail_template);}
	$mail_template=str_replace('#company_logo#', "$rparameters[server_url]/upload/logo/$rparameters[logo]", $mail_template);
	$mail_template=str_replace('#ticket_technician_phone#', $techrow['phone'], $mail_template);
	$mail_template=str_replace('#ticket_technician_mobile#', $techrow['mobile'], $mail_template);
	$mail_template=str_replace('#ticket_technician_custom1#', $techrow['custom1'], $mail_template);
	$mail_template=str_replace('#ticket_technician_custom2#', $techrow['custom2'], $mail_template);
	$mail_template=str_replace('#ticket_technician_mail#', $techrow['mail'], $mail_template);
	$mail_template=str_replace('#ticket_technician_function#', $techrow['function'], $mail_template);
	$mail_template=str_replace('#ticket_technician_services#', $technician_services, $mail_template);
	$mail_template=str_replace('#ticket_technician_service#', $technician_service, $mail_template);
	if($rparameters['asset'] && $globalrow['asset_id'])
	{
		//get name of asset 
		$qry=$db->prepare("SELECT `netbios` FROM `tassets` WHERE id=:id");
		$qry->execute(array('id' => $globalrow['asset_id']));
		$asset=$qry->fetch();
		$qry->closeCursor();
		$mail_template=str_replace('#ticket_asset#', $asset['netbios'], $mail_template);
	}
	//replace ticket_last_comment tag
	if(preg_match('/#ticket_last_comment#/',$mail_template))
	{
		//get last text comment for this ticket
		$qry=$db->prepare("SELECT `tthreads`.`text`,`tthreads`.`date`, `tusers`.`firstname`, `tusers`.`lastname`
		FROM `tthreads`,`tusers`
		WHERE 
		`tthreads`.`author`=`tusers`.`id` AND
		`tthreads`.`id`= (SELECT MAX(`id`) FROM `tthreads` WHERE `ticket`=:ticket AND `tthreads`.`type`='0' AND `tthreads`.`text`!='' )");
		$qry->execute(array('ticket' => $globalrow['id']));
		$last_comment=$qry->fetch();
		$qry->closeCursor();

		if(!empty($last_comment['text'])){
			$date_thread=date_convert($last_comment['date']);
			$last_comment='
			<div style="text-align:center;">
				Le '.$date_thread.' '.$last_comment['firstname'].' '.$last_comment['lastname'].' :
			</div>
			'.$last_comment['text'];
			$last_comment='
			<p style="padding-top:5px;">
				<div style="text-align:center; color:red; font-weight: bold;">'.T_('ÉTAPE DE RÉSOLUTION VOUS CONCERNANT').'</div>
				<table style="border: 4px solid red; padding:5px;"><tr><td width="580">'.$last_comment.'</td></tr></table>
			</p>
			<br />
			<hr />';
		} else {$last_comment='';}

		//replace value
		$mail_template=str_replace('#ticket_last_comment#', $last_comment , $mail_template);
	}
	//replace ticket_last_comment_text_only tag
	if(preg_match('/#ticket_last_comment_text_only#/',$mail_template))
	{
		//get last text comment for this ticket
		$qry=$db->prepare("SELECT `tthreads`.`text`,`tthreads`.`date`, `tusers`.`firstname`, `tusers`.`lastname`
		FROM `tthreads`,`tusers`
		WHERE 
		`tthreads`.`author`=`tusers`.`id` AND
		`tthreads`.`id`= (SELECT MAX(`id`) FROM `tthreads` WHERE `ticket`=:ticket AND `tthreads`.`type`='0' AND `tthreads`.`text`!='' )");
		$qry->execute(array('ticket' => $globalrow['id']));
		$last_comment=$qry->fetch();
		$qry->closeCursor();

		if(!empty($last_comment['text'])){
			//replace value
			$mail_template=str_replace('#ticket_last_comment_text_only#', $last_comment['text'] , $mail_template);
		} else {
			$mail_template=str_replace('#ticket_last_comment_text_only#', '' , $mail_template);
		}
	}
	$msg.=$mail_template;
} else {
	echo 'ERROR : unable to find mail template, check your /template/mail directory';
}

//add tag in mail to split fonction of imap connector
if($rparameters['imap']==1 && $rparameters['imap_reply']==1) {$msg.='---- '.T_('Repondre au dessus du ticket').' ----';} else {$msg.='';}

if($send==1)
{
	if($rparameters['debug']) {echo '<b>SMTP SERVER :</b><br />';}
	if($rparameters['mail_auth_type']=='oauth_google' || $rparameters['mail_auth_type']=='oauth_microsoft' || $rparameters['mail_auth_type']=='oauth_azure') {require_once (__DIR__.'/../vendor/autoload.php');}
	require_once(__DIR__.'/../vendor/phpmailer/phpmailer/src/PHPMailer.php');
	if($rparameters['mail_auth_type']=='oauth_google' || $rparameters['mail_auth_type']=='oauth_microsoft' || $rparameters['mail_auth_type']=='oauth_azure') { require_once(__DIR__.'/../vendor/phpmailer/phpmailer/src/OAuth.php');}
	require_once(__DIR__.'/../vendor/phpmailer/phpmailer/src/SMTP.php');
	require_once(__DIR__.'/../vendor/phpmailer/phpmailer/src/Exception.php');
	if($rparameters['mail_auth_type']=='oauth_google' || $rparameters['mail_auth_type']=='oauth_microsoft' || $rparameters['mail_auth_type']=='oauth_azure') {require_once(__DIR__.'/../vendor/phpmailer/phpmailer/src/OAuthTokenProvider.php');}

	$mail = new PHPMailer;
	try {
		//detect and convert image in mail
		if(preg_match_all('/<img.*?>/', $msg, $matches))
		{
			//for each images detected
			$i = 1;
			foreach ($matches[0] as $img)
			{
				//generate cid
				$cid = 'img'.($i++);
				if(strpos($img, 'base64') !== false)
				{
					if($rparameters['debug']) {echo 'DEBUG : Images base64 detected conversion ('.$img.')<br />';}
					//remove originalsrc
                    $img = preg_replace('/originalsrc="(.*?)"/', '', $img);
					//keep data of current image
					preg_match('/src="(.*?)"/', $img, $m);
					//extract image parameters
					$image_data=explode(',',$m[1]);
					$image_encoding=explode(';',$image_data[0]);
					$image_type=explode(':',$image_encoding[0]);
					//keep image size
					preg_match('/style="(.*?)"/', $img, $style);
					if(!isset($style[0])) {$style[0]='';}
					//replace img
					$msg = str_replace($img, '<img alt="" src="cid:'.$cid.'" '.$style[0].' />', $msg); 
					//add image to mail
					if(!empty($image_data[1])){$mail->AddStringEmbeddedImage(base64_decode($image_data[1]), $cid, $cid, $image_encoding[1], $image_type[1]);} 
				} else {
					if($rparameters['debug']) {echo 'DEBUG : Images no base64 detected add EmbeddedImage('.$img.')<br />';}
					if(!preg_match('#/logo/#',$img))
					{
						$orig_filename=explode('src="',$img);
						if(!empty($orig_filename[1]))
						{
							$orig_filename=explode('"',$orig_filename[1]);
							$orig_filename=$orig_filename[0];
							if($orig_filename!='content-blocker://') 
							{
								if(file_exists($orig_filename))
								{
									$msg=str_replace($orig_filename, 'cid:'.$cid,$msg);
									$mail->AddEmbeddedImage($orig_filename,$cid,$orig_filename,'base64', 'image/png');
								}
							}
						}
					}
				}
			}
		} 
		
		if($rparameters['user_agency'] && !empty($_POST['u_agency']) && $_GET['page']!='preview_mail') {
			//get agency mail
			$qry=$db->prepare("SELECT `mail` FROM `tagencies` WHERE id=:id");
			$qry->execute(array('id' => $_POST['u_agency']));
			$row=$qry->fetch();
			$qry->closeCursor();
			if($row['mail']) 
			{
				if($userrow['mail']){$mail->AddCC("$row[mail]"); $dest_mail=1;} else {$mail->AddAddress("$row[mail]"); $dest_mail=1;}
			}
		}

		//add user agency mail if user have no mail and agency parameter is enable
		if($rparameters['user_agency']) {
			//send mail to agency on agency field if exist
			if(!empty($_POST['u_agency']) && $_GET['page']!='preview_mail')
			{
				//get agency mail
				$qry=$db->prepare("SELECT `mail` FROM `tagencies` WHERE id=:id");
				$qry->execute(array('id' => $_POST['u_agency']));
				$row=$qry->fetch();
				$qry->closeCursor();
				if($row['mail']) 
				{
					if($userrow['mail']){$mail->AddCC($row['mail']); $dest_mail=1;} else {$mail->AddAddress($row['mail']); $dest_mail=1;}
				}
			} else {
				//get agency mail of user
				$qry=$db->prepare("SELECT `mail` FROM `tagencies` WHERE id IN (SELECT agency_id FROM tusers_agencies WHERE user_id=:user_id)");
				$qry->execute(array('user_id' => $userrow['id']));
				$row=$qry->fetch();
				$qry->closeCursor();
				if(!empty($row['mail'])) 
				{
					if($userrow['mail']){$mail->AddCC("$row[mail]"); $dest_mail=1;} else {$mail->AddAddress("$row[mail]"); $dest_mail=1;}
				}
			}
		}
		
		$mail->CharSet = 'UTF-8'; //ISO-8859-1 possible if string problems
		if($rparameters['mail_smtp_class']=='IsSendMail()') {$mail->IsSendMail();}
		if($rparameters['mail_smtp_class']=='IsSMTP()') {$mail->IsSMTP();} 
		if($rparameters['mail_secure']=='SSL') {$mail->Host = "ssl://$rparameters[mail_smtp]";}
		elseif($rparameters['mail_secure']=='TLS') {$mail->Host = "tls://$rparameters[mail_smtp]";} 
		else {$mail->Host = "$rparameters[mail_smtp]";}
		$mail->SMTPAuth = $rparameters['mail_auth'];
		if($rparameters['debug']) {$mail->SMTPDebug = 4;}
		if($rparameters['mail_auth_type']=='login') {$mail->AuthType = 'LOGIN';}
		elseif($rparameters['mail_auth_type']=='oauth_google') {$mail->AuthType = 'XOAUTH2';}
		elseif($rparameters['mail_auth_type']=='oauth_microsoft') {$mail->AuthType = 'XOAUTH2';}
		elseif($rparameters['mail_auth_type']=='oauth_azure') {$mail->AuthType = 'XOAUTH2';}
		if($rparameters['mail_auth'] && ($rparameters['mail_auth_type']=='oauth_google' || $rparameters['mail_auth_type']=='oauth_microsoft' || $rparameters['mail_auth_type']=='oauth_azure'))
		{
			if(preg_match('/gs_en/',$rparameters['mail_oauth_client_secret'])) {$rparameters['mail_oauth_client_secret']=gs_crypt($rparameters['mail_oauth_client_secret'], 'd' , $rparameters['server_private_key']);}
			if($rparameters['mail_auth_type']=='oauth_google')
			{
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
				$provider = new Google(  //Create a new OAuth2 provider instance
					[
						'clientId' => $rparameters['mail_oauth_client_id'],
						'clientSecret' => $rparameters['mail_oauth_client_secret'],
					]
				);
			}
			if($rparameters['mail_auth_type']=='oauth_microsoft')
			{
				$mail->SMTPSecure = 'tls';
				$provider = new Microsoft( //Create a new OAuth2 provider instance
					[
						'clientId' => $rparameters['mail_oauth_client_id'],
						'clientSecret' => $rparameters['mail_oauth_client_secret'],
					]
				);
			}
			if($rparameters['mail_auth_type']=='oauth_azure')
			{
				$mail->SMTPSecure = 'tls';
				$provider = new Azure( //Create a new OAuth2 provider instance
					[
						'clientId' => $rparameters['mail_oauth_client_id'],
						'clientSecret' => $rparameters['mail_oauth_client_secret'],
						'tenantId' => $rparameters['mail_oauth_tenant_id'],
					]
				);
			}
			$mail->setOAuth( //Pass the OAuth provider instance to PHPMailer
				new OAuth(
					[
						'provider' => $provider,
						'clientId' => $rparameters['mail_oauth_client_id'],
						'clientSecret' => $rparameters['mail_oauth_client_secret'],
						'refreshToken' => $rparameters['mail_oauth_refresh_token'],
						'userName' => $rparameters['mail_username'],
					]
				)
			);
		} else {
			if($rparameters['mail_secure']!=0) {$mail->SMTPSecure = $rparameters['mail_secure'];} #6121

			//if($rparameters['mail_secure']!=0) {$mail->SMTPSecure = $rparameters['mail_secure'];} else {$mail->SMTPAutoTLS = false;}
		}
		if($rparameters['mail_port']!=25) {$mail->Port = $rparameters['mail_port'];} else {$mail->SMTPAutoTLS = false;} #6121
		$mail->Username = "$rparameters[mail_username]";
		if(preg_match('/gs_en/',$rparameters['mail_password'])) {$rparameters['mail_password']=gs_crypt($rparameters['mail_password'], 'd' , $rparameters['server_private_key']);}
		$mail->Password = "$rparameters[mail_password]";
		$mail->IsHTML(true); 
		$mail->Timeout = 30;
		$mail->From = "$sender";
		$mail->AddReplyTo($mail_reply);
		$mail->FromName = htmlspecialchars_decode($rparameters['mail_from_name']);
		$mail->XMailer = '_';

		//generate adresse list
		if($_POST['receiver']!='none') {
			
			if($globalrow['u_group']!=0)
			{
				$qry2=$db->prepare("SELECT `mail` FROM `tusers`, `tgroups_assoc` WHERE tgroups_assoc.user=tusers.id AND tgroups_assoc.group=:group AND tusers.disable='0'");
				$qry2->execute(array('group' => $globalrow['u_group']));
				while($row=$qry2->fetch())
				{
					if($row['mail'])
					{
						$mail->AddAddress($row['mail']);
						$dest_mail=1;
					}
				}
				$qry2->closeCursor();
			}elseif($to && $rparameters['ticket_type'] && $rparameters['mail_auto_type']) { //case add dest for ticket with type adresses
				if(preg_match('/;/',$to))
				{
					$to=explode(';',$to);
					foreach ($to as &$email) {$mail->AddAddress($email);}
					$dest_mail=1;
				} else {
					$mail->AddAddress($to);
					//add user if mail_auto
					if($userrow['mail'] && $rparameters['mail_auto']) {$mail->AddAddress($userrow['mail']);}
				}	$dest_mail=1;
			}elseif($userrow['mail']) {
				$mail->AddAddress($userrow['mail']); $dest_mail=1;
			}
			
		}
		if($rparameters['mail_cc']!='') {
			$addresses = explode(";",$rparameters['mail_cc']);
			foreach($addresses as $mailCC){
				$mail->AddCC("$mailCC");
				$dest_mail=1;
			}
		}
		if($_POST['usercopy']!='')
		{ 
			if(substr($_POST['usercopy'], 0, 1) =='G') 
			{
				$groupid= explode("_", $_POST['usercopy']);
				$qry2=$db->prepare("SELECT `mail` FROM `tusers`, `tgroups_assoc` WHERE tgroups_assoc.user=tusers.id AND tgroups_assoc.group=:group AND tusers.disable='0'");
				$qry2->execute(array('group' => $groupid[1]));
				while($row=$qry2->fetch()){$mail->AddCC("$row[0]"); $dest_mail=1;}
				$qry2->closeCursor();
			} else {$mail->AddCC("$_POST[usercopy]"); $dest_mail=1;}
		}
		if($_POST['usercopy2']!='')
		{ 
			if(substr($_POST['usercopy2'], 0, 1) =='G') 
			{
				$groupid= explode("_", $_POST['usercopy2']);
				$qry2=$db->prepare("SELECT `mail` FROM `tusers`, `tgroups_assoc` WHERE tgroups_assoc.user=tusers.id AND tgroups_assoc.group=:group AND tusers.disable='0'");
				$qry2->execute(array('group' => $groupid[1]));
				while($row=$qry2->fetch()){$mail->AddCC("$row[0]"); $dest_mail=1;}
				$qry2->closeCursor();
			} else {$mail->AddCC("$_POST[usercopy2]");$dest_mail=1;}
		} 
		if($_POST['usercopy3']!='')
		{ 
			if(substr($_POST['usercopy3'], 0, 1) =='G') 
			{
				$groupid= explode("_", $_POST['usercopy3']);
				$qry2=$db->prepare("SELECT `mail` FROM `tusers`, `tgroups_assoc` WHERE tgroups_assoc.user=tusers.id AND tgroups_assoc.group=:group AND tusers.disable='0'");
				$qry2->execute(array('group' => $groupid[1]));
				while($row=$qry2->fetch()){$mail->AddCC("$row[0]"); $dest_mail=1;}
				$qry2->closeCursor();
			} else {$mail->AddCC("$_POST[usercopy3]"); $dest_mail=1;}
		}
		if($_POST['usercopy4']!='')
		{ 
			if(substr($_POST['usercopy4'], 0, 1) =='G') 
			{
				$groupid= explode("_", $_POST['usercopy4']);
				$qry2=$db->prepare("SELECT `mail` FROM `tusers`, `tgroups_assoc` WHERE tgroups_assoc.user=tusers.id AND tgroups_assoc.group=:group AND tusers.disable='0'");
				$qry2->execute(array('group' => $groupid[1]));
				while($row=$qry2->fetch()){$mail->AddCC("$row[0]"); $dest_mail=1;}
				$qry2->closeCursor();
			} else {$mail->AddCC("$_POST[usercopy4]"); $dest_mail=1;}
		}
		if($_POST['usercopy5']!='')
		{ 
			if(substr($_POST['usercopy5'], 0, 1) =='G') 
			{
				$groupid= explode("_", $_POST['usercopy5']);
				$qry2=$db->prepare("SELECT `mail` FROM `tusers`, `tgroups_assoc` WHERE tgroups_assoc.user=tusers.id AND tgroups_assoc.group=:group AND tusers.disable='0'");
				$qry2->execute(array('group' => $groupid[1]));
				while($row=$qry2->fetch()){$mail->AddCC("$row[0]"); $dest_mail=1;}
				$qry2->closeCursor();
			} else {$mail->AddCC("$_POST[usercopy5]"); $dest_mail=1;}
		}
		if($_POST['usercopy6']!='')
		{ 
			if(substr($_POST['usercopy6'], 0, 1) =='G') 
			{
				$groupid= explode("_", $_POST['usercopy6']);
				$qry2=$db->prepare("SELECT `mail` FROM `tusers`, `tgroups_assoc` WHERE tgroups_assoc.user=tusers.id AND tgroups_assoc.group=:group AND tusers.disable='0'");
				$qry2->execute(array('group' => $groupid[1]));
				while($row=$qry2->fetch()){$mail->AddCC("$row[0]"); $dest_mail=1;}
				$qry2->closeCursor();
			} else {$mail->AddCC("$_POST[usercopy6]"); $dest_mail=1;}
		}
		if($_POST['manual_address'])
		{
			$dest_mail=1;
			$_POST['manual_address']=str_replace(',',';',$_POST['manual_address']);
			if(strpos($_POST['manual_address'],';'))
			{
				$_POST['manual_address']=explode(';',$_POST['manual_address']);
				foreach ($_POST['manual_address'] as &$email) {$mail->AddCC($email);}
			} else {$mail->AddCC($_POST['manual_address']);}
		}
		
		//add cci adresses
		if($rparameters['mail_cci'])
		{
			if($_POST['usercopy_cci'])
			{ 
				if(substr($_POST['usercopy_cci'], 0, 1) =='G') 
				{
					$groupid= explode("_", $_POST['usercopy_cci']);
					$qry2=$db->prepare("SELECT `mail` FROM `tusers`, `tgroups_assoc` WHERE tgroups_assoc.user=tusers.id AND tgroups_assoc.group=:group AND tusers.disable='0'");
					$qry2->execute(array('group' => $groupid[1]));
					while($row=$qry2->fetch()){$mail->AddBCC($row['mail']); $dest_mail=1;}
					$qry2->closeCursor();
				} else {$mail->AddBCC($_POST['usercopy_cci']); $dest_mail=1;}
			}
			if($_POST['usercopy2_cci'])
			{ 
				if(substr($_POST['usercopy2_cci'], 0, 1) =='G') 
				{
					$groupid= explode("_", $_POST['usercopy2_cci']);
					$qry2=$db->prepare("SELECT `mail` FROM `tusers`, `tgroups_assoc` WHERE tgroups_assoc.user=tusers.id AND tgroups_assoc.group=:group AND tusers.disable='0'");
					$qry2->execute(array('group' => $groupid[1]));
					while($row=$qry2->fetch()){$mail->AddBCC($row['mail']); $dest_mail=1;}
					$qry2->closeCursor();
				} else {$mail->AddBCC($_POST['usercopy2_cci']); $dest_mail=1;}
			}
			if($_POST['usercopy3_cci'])
			{ 
				if(substr($_POST['usercopy3_cci'], 0, 1) =='G') 
				{
					$groupid= explode("_", $_POST['usercopy3_cci']);
					$qry2=$db->prepare("SELECT `mail` FROM `tusers`, `tgroups_assoc` WHERE tgroups_assoc.user=tusers.id AND tgroups_assoc.group=:group AND tusers.disable='0'");
					$qry2->execute(array('group' => $groupid[1]));
					while($row=$qry2->fetch()){$mail->AddBCC($row['mail']); $dest_mail=1;}
					$qry2->closeCursor();
				} else {$mail->AddBCC($_POST['usercopy3_cci']); $dest_mail=1;}
			}
			if($_POST['usercopy4_cci'])
			{ 
				if(substr($_POST['usercopy4_cci'], 0, 1) =='G') 
				{
					$groupid= explode("_", $_POST['usercopy4_cci']);
					$qry2=$db->prepare("SELECT `mail` FROM `tusers`, `tgroups_assoc` WHERE tgroups_assoc.user=tusers.id AND tgroups_assoc.group=:group AND tusers.disable='0'");
					$qry2->execute(array('group' => $groupid[1]));
					while($row=$qry2->fetch()){$mail->AddBCC($row['mail']); $dest_mail=1;}
					$qry2->closeCursor();
				} else {$mail->AddBCC($_POST['usercopy4_cci']); $dest_mail=1;}
			}
			if($_POST['usercopy5_cci'])
			{ 
				if(substr($_POST['usercopy5_cci'], 0, 1) =='G') 
				{
					$groupid= explode("_", $_POST['usercopy5_cci']);
					$qry2=$db->prepare("SELECT `mail` FROM `tusers`, `tgroups_assoc` WHERE tgroups_assoc.user=tusers.id AND tgroups_assoc.group=:group AND tusers.disable='0'");
					$qry2->execute(array('group' => $groupid[1]));
					while($row=$qry2->fetch()){$mail->AddBCC($row['mail']); $dest_mail=1;}
					$qry2->closeCursor();
				} else {$mail->AddBCC($_POST['usercopy5_cci']); $dest_mail=1;}
			}
			if($_POST['usercopy6_cci'])
			{ 
				if(substr($_POST['usercopy6_cci'], 0, 1) =='G') 
				{
					$groupid= explode("_", $_POST['usercopy6_cci']);
					$qry2=$db->prepare("SELECT `mail` FROM `tusers`, `tgroups_assoc` WHERE tgroups_assoc.user=tusers.id AND tgroups_assoc.group=:group AND tusers.disable='0'");
					$qry2->execute(array('group' => $groupid[1]));
					while($row=$qry2->fetch()){$mail->AddBCC($row['mail']); $dest_mail=1;}
					$qry2->closeCursor();
				} else {$mail->AddBCC($_POST['usercopy6_cci']); $dest_mail=1;}
			}
			if($_POST['manual_address_cci'])
			{
				$dest_mail=1;
				$_POST['manual_address_cci']=str_replace(',',';',$_POST['manual_address_cci']);
				if(strpos($_POST['manual_address_cci'],';'))
				{
					$_POST['manual_address_cci']=explode(';',$_POST['manual_address_cci']);
					foreach ($_POST['manual_address_cci'] as &$email) {$mail->AddBCC($email);}
				} else {$mail->AddBCC($_POST['manual_address_cci']);}
			}
		}
		
		if($_POST['withattachment'])
		{
			//display all attachments of this ticket
			$qry=$db->prepare("SELECT `uid`,`storage_filename`,`real_filename` FROM `tattachments` WHERE `ticket_id`=:ticket_id");
			$qry->execute(array('ticket_id' => $_GET['id']));
			while($attachment=$qry->fetch()) 
			{
				if(!$attachment['uid']) //old upload file case
				{
					$mail->AddAttachment('./upload/'.$_GET['id'].'/'.$attachment['real_filename']);
				} else {
					$mail->AddAttachment('./upload/ticket/'.$attachment['storage_filename'], $attachment['real_filename']);
				}
			}
			$qry->closeCursor();
		}
		$mail->Subject = "$object";
		
		if(!$rparameters['mail_ssl_check'])
		{
			//bug fix 3292 & 3427
			$mail->smtpConnect([
			'ssl' => [
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
				]
			]);
		}
		$mail->Body = "$msg";
		
		if($dest_mail)
		{
			//send mail
			$mail->send();
			//generate recipient list to trace
			$recipient_mail='';
			if($mail_auto==true) {
				$author=0;
				if($to)
				{
					$recipient_mail=$to;
				}elseif(!empty($usermail['mail'])) {
					$recipient_mail=$usermail['mail'];
				} else {
					$recipient_mail='';
				}
			} else {
				$author=$_SESSION['user_id'];
				//get dest mail to trace in thread from manual send
				if($_POST['receiver']!='none') {$recipient_mail.=$_POST['receiver'].', ';}
				if($_POST['usercopy']) {$recipient_mail.=$_POST['usercopy'].', ';}
				if($_POST['usercopy2']) {$recipient_mail.=$_POST['usercopy2'].', ';}
				if($_POST['usercopy3']) {$recipient_mail.=$_POST['usercopy3'].', ';}
				if($_POST['usercopy4']) {$recipient_mail.=$_POST['usercopy4'].', ';}
				if($_POST['usercopy5']) {$recipient_mail.=$_POST['usercopy5'].', ';}
				if($_POST['usercopy6']) {$recipient_mail.=$_POST['usercopy6'].', ';}
				if($_POST['manual_address']) {
					if(is_array($_POST['manual_address']))
					{
						foreach ($_POST['manual_address'] as &$email) {$recipient_mail.=', '.$email;}
					} else {$recipient_mail.=', '.$_POST['manual_address'];}
				}
			} 

			//recipient array cases
			if(is_array($recipient_mail))
			{
				$array_recipient_mail='';
				foreach ($recipient_mail as &$email) {$array_recipient_mail.=' '.$email;}
				$recipient_mail=$array_recipient_mail;
			}

			//trace mail in thread
			$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`text`,`type`,`dest_mail`) VALUES (:ticket,:date,:author,'','3',:dest_mail)");
			$qry->execute(array('ticket' => $_GET['id'],'date' => $datetime,'author' => $author,'dest_mail' => $recipient_mail));
			
			if(isset($_SESSION['user_id'])) {
				echo DisplayMessage('success',T_('Message envoyé'));
				
				//redirect
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				<!--
				function redirect()
				{
					window.location='./index.php?page=dashboard&&state=$_GET[state]&userid=$_GET[userid]&view=$_GET[view]&date_start=$_GET[date_start]&date_end=$_GET[date_end]&companyview=$_GET[companyview]'
				}
				setTimeout('redirect()',$rparameters[time_display_msg]);
				-->
				</SCRIPT>
				";
			}
		} else {
			echo DisplayMessage('error',T_("Aucune adresse mail en destinataire renseignée"));
		}
		$mail->SmtpClose();

	} catch (Exception $e) {
		echo DisplayMessage('error',T_('Message non envoyé, vérifier les paramètres de votre connecteur SMTP').' ('.$e->getMessage().')');
		//log
		if($rparameters['log']) {LogIt('error','ERROR 29 : SMTP, '.$e->getMessage(),$_SESSION['user_id']);}
	}
}
?>