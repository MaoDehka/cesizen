<?php
################################################################################
# @Name : ./core/ticket.php 
# @Description : actions page for tickets
# @Call : ./ticket.php
# @Author : Flox
# @Create : 28/10/2013
# @Update : 21/02/2024
# @Version : 3.2.50 p2
################################################################################

//initialize variable
if(!isset($_POST['close'])) $_POST['close'] = '';
if(!isset($_POST['reopen'])) $_POST['reopen'] = '';
if(!isset($_POST['text'])) $_POST['text'] = '';
if(!isset($_POST['send'])) $_POST['send'] = '';
if(!isset($_POST['action'])) $_POST['action'] = '';
if(!isset($_POST['edituser'])) $_POST['edituser'] = '';
if(!isset($_POST['editcat'])) $_POST['editcat'] = '';
if(!isset($_POST['u_agency'])) $_POST['u_agency'] = '';
if(!isset($_POST['observer'])) $_POST['observer'] = '';
if(!isset($_POST['observer1'])) $_POST['observer1'] = '';
if(!isset($_POST['observer2'])) $_POST['observer2'] = '';
if(!isset($_POST['observer3'])) $_POST['observer3'] = '';
if(!isset($_POST['delete_observer'])) $_POST['delete_observer'] = '';
if(!isset($_POST['delete_observer1'])) $_POST['delete_observer1'] = '';
if(!isset($_POST['delete_observer2'])) $_POST['delete_observer2'] = '';
if(!isset($_POST['delete_observer3'])) $_POST['delete_observer3'] = '';
if(!isset($error)) $error="0";
if(!isset($user_auto_switch_state)) $user_auto_switch_state='0';

//fix different bug variable
$_POST['title']=str_replace('≠','&#8800;',$_POST['title']);
$_POST['description']=str_replace('≠','&#8800;',$_POST['description']);
$_POST['text']=str_replace('≠','&#8800;',$_POST['text']);
$_POST['text2']=str_replace('≠','&#8800;',$_POST['text2']);

//find next incident number for new ticket
if($_GET['action']=='new')
{
	//get database server name
	$qry=$db->prepare("SELECT VERSION();");
	$qry->execute();
	$database_server=$qry->fetch();
	$qry->closeCursor();
	if(!preg_match('/MariaDB/',$database_server[0])) //MySQL 
	{
		if(substr($database_server[0],0,1)!=5)
		{
			//update auto-increment MySQL default
			$qry=$db->prepare("SET @@SESSION.`information_schema_stats_expiry` = 0;");
			$qry->execute();
		}
	}

	//get max auto-increment from db on table tincidents
	$qry=$db->prepare("SELECT MAX(`auto_increment`) FROM `INFORMATION_SCHEMA`.`TABLES` WHERE `TABLE_SCHEMA`=:database AND `table_name`='tincidents';");
	$qry->execute(array('database' => $db_name));
	$max_auto_increment=$qry->fetch();
	$qry->closeCursor();
	$max_auto_increment=$max_auto_increment[0];
	if(empty($max_auto_increment)) {$max_auto_increment='';}

	//get max ticket number
	$qry=$db->prepare("SELECT MAX(`id`) FROM `tincidents`");
	$qry->execute();
	$ticket_max_number=$qry->fetch();
	$qry->closeCursor();
	$ticket_max_number=$ticket_max_number[0];
	if(empty($ticket_max_number)) {$ticket_max_number='';}

	if($max_auto_increment && $ticket_max_number)
	{
		if($max_auto_increment>$ticket_max_number)
		{
			$_GET['id']=$max_auto_increment;
			$db_id=$max_auto_increment;
		} else {
			$_GET['id']=$ticket_max_number+1;
			$db_id=$ticket_max_number+1;
		}
	} elseif($ticket_max_number) {
		$_GET['id']=$ticket_max_number+1;
		$db_id=$ticket_max_number+1;
	} elseif($max_auto_increment) {
		$_GET['id']=$max_auto_increment;
		$db_id=$max_auto_increment;
	} else {
		$_GET['id']=1;
		$db_id=1;
	}
}

//generate ticket token access
$token = bin2hex(random_bytes(32));
$qry=$db->prepare("INSERT INTO `ttoken` (`date`,`token`,`action`,`ticket_id`,`user_id`,`ip`) VALUES (NOW(),:token,'ticket_access',:ticket_id,:user_id,:ip)");
$qry->execute(array('token' => $token,'ticket_id' => $_GET['id'],'user_id' => $_SESSION['user_id'],'ip' => $_SERVER['REMOTE_ADDR']));
echo '<input type="hidden" value="'.$token.'" id="token" />';
//action delete ticket
if(($_GET['action']=="delete") && $rright['ticket_delete'] && $_GET['id'])
{
	//call delete function
	DeleteTicket($_GET['id']);

	//display delete message
	echo DisplayMessage('success',T_('Ticket supprimé'));
	
	//redirect
	$url="./index.php?page=dashboard&state=$_GET[state]&userid=$_GET[userid]";
	$url=preg_replace('/%/','%25',$url);
	$url=preg_replace('/%2525/','%25',$url);
	echo "
	<SCRIPT LANGUAGE='JavaScript'>
		function redirect(){window.location='$url'}
		setTimeout('redirect()',$rparameters[time_display_msg]);
	</SCRIPT>
	";
}

//action to lock thread
if($_GET['lock_thread'] && $rright['ticket_thread_private']!=0) 
{
	$qry=$db->prepare("UPDATE `tthreads` SET `private`='1' WHERE `id`=:id");
	$qry->execute(array('id' => $_GET['lock_thread']));
}

//action to unlock thread
if($_GET['unlock_thread'] && $rright['ticket_thread_private']!=0) 
{
	$qry=$db->prepare("UPDATE `tthreads` SET `private`='0' WHERE `id`=:id");
	$qry->execute(array('id' => $_GET['unlock_thread']));
}

//master query
$qry=$db->prepare("SELECT * FROM `tincidents` WHERE id=:id");
$qry->execute(array('id' => $_GET['id']));
$globalrow=$qry->fetch();
$qry->closeCursor();
if(empty($globalrow)) {$globalrow=array();}

if(empty($globalrow['user'])) {$globalrow['user']=0;}
if(empty($globalrow['u_group'])) {$globalrow['u_group']=0;}
if(empty($globalrow['t_group'])) {$globalrow['t_group']=0;}
if(empty($globalrow['technician'])) {$globalrow['technician']=0;}
if(empty($globalrow['u_service'])) {$globalrow['u_service']=0;}
if(empty($globalrow['u_agency'])) {$globalrow['u_agency']=0;}
if(empty($globalrow['asset_id'])) {$globalrow['asset_id']=0;}
if(empty($globalrow['sender_service'])) {$globalrow['sender_service']=0;}
if(empty($globalrow['techread'])) {$globalrow['techread']=0;}
if(empty($globalrow['userread'])) {$globalrow['userread']=0;}
if(empty($globalrow['state'])) {$globalrow['state']=1;}
if(empty($globalrow['time_hope'])) {$globalrow['time_hope']=5;}
if(empty($globalrow['time'])) {$globalrow['time']=0;}
if(empty($globalrow['title'])) {$globalrow['title']='';}
if(empty($globalrow['creator'])) {$globalrow['creator']=0;}
if(empty($globalrow['user_validation'])) {$globalrow['user_validation']=0;}
if(empty($globalrow['user_validation_date'])) {$globalrow['user_validation_date']='0000-00-00';}

//include modalbox
if($rright['planning'] && $rparameters['planning'] && $rright['ticket_calendar']) {include('includes/ticket_calendar.php');}
if($rright['ticket_user_actions']) {include('includes/ticket_user.php');}
if($rright['ticket_cat_actions']) {include('includes/ticket_category.php');}
if($rright['ticket_template'] && $_GET['action']=='new') {include('includes/ticket_template.php');}
if($rright['ticket_fusion'] && $_GET['action']!='new') {include('includes/ticket_fusion.php');}

//user group detection switch values
if(substr($_POST['user'], 0, 1) =='G') 
{
 	$u_group=explode("_", $_POST['user']);
	$u_group=$u_group[1];
	$_POST['user']='';
} elseif($globalrow['u_group']!=0 && $_POST['user']=='')
{
	$u_group=$globalrow['u_group'];
	$_POST['user']='';
}
//technician group detection switch values
if(substr($_POST['technician'], 0, 1) =='G') 
{
 	$t_group=explode("_", $_POST['technician']);
	$t_group=$t_group[1];
	$_POST['technician']='';
} elseif($globalrow['t_group']!=0 && $_POST['technician']=='')
{
	$t_group=$globalrow['t_group'];
	$_POST['technician']='';
} 

//action to user validation with type selection
if($rparameters['user_validation'] && $rparameters['user_validation_perimeter']=='mark' && $rright['ticket_user_validation']) 
{
	if($_POST['type'])
	{
		//check if selected type need validation
		$qry=$db->prepare("SELECT `user_validation` FROM `ttypes` WHERE id=:id");
		$qry->execute(array('id' => $_POST['type']));
		$type=$qry->fetch();
		$qry->closeCursor();
		//replace current value if type is switched
		if($type['user_validation'] && $_POST['user_validation']==0){$globalrow['user_validation']=1;}
		if(!$type['user_validation'] && $_POST['user_validation']==1){$globalrow['user_validation']=0;}
	}
}

//define default state
if($_GET['action']=='new' && !$_POST['state']) 
{
	if($_SESSION['profile_id']==0 || $_SESSION['profile_id']==4)
	{
		if($rparameters['ticket_default_state']==5)
		{
			if($rparameters['debug']) {echo "<b>AUTO CHANGE STATE</b> to wait PEC (id=1) parameter state, technician is creator <br />";}
			$_POST['state']=1;
		} else {
			if($rparameters['debug']) {echo "<b>AUTO CHANGE STATE</b> to default parameter state $rparameters[ticket_default_state] ticket_default_state !=5<br />";}
			$_POST['state']=$rparameters['ticket_default_state'];
		}
	}else {
		if($rparameters['debug']) {echo "<b>AUTO CHANGE STATE</b> to default parameter state $rparameters[ticket_default_state]<br />";}
		$_POST['state']=$rparameters['ticket_default_state'];
	}
}

//database inputs if submit
if($rparameters['debug']){ echo "<b><u>DEBUG MODE :</u></b> (To disable go to Administration > Parameters > General, and uncheck debug)<br /> <b>VAR:</b>  post_observer=$_POST[observer] post_observer1=$_POST[observer1] post_observer2=$_POST[observer2] post_observer3=$_POST[observer3]  ticket_observer=$ticket_observer save=$save post_modify=$_POST[modify] post_quit=$_POST[quit] post_mail=$_POST[mail] post_upload=$_POST[upload] post_send=$_POST[send] post_action=$_POST[action] get_action=$_GET[action] post_category=$_POST[category] post_subcat=$_POST[subcat] post_technician=$_POST[technician] globalrow_technician=$globalrow[technician] post_u_service=$_POST[u_service] globalrow_u_service=$globalrow[u_service] post_u_agency=$_POST[u_agency] globalrow_u_agency=$globalrow[u_agency] post_asset_id=$_POST[asset_id] globalrow[asset_id]=$globalrow[asset_id] post_sender_service=$_POST[sender_service] globalrow_sender_service=$globalrow[sender_service] post_priority=$_POST[priority] post_title=$_POST[title] post_date_hope=$_POST[date_hope] post_type=$_POST[type]<br />";}
if($_POST['addcalendar']||$_POST['addevent']||$_POST['modify']||$_POST['quit']||$_POST['close']||$_POST['reopen']||$_POST['mail']||$_POST['upload']||$save=="1"||$_POST['send']||$_POST['action']) 
{
	//hide button during save
	echo '<script>if(typeof HideSaveButton === "function") {HideSaveButton();}</script>';
	//check mandatory fields
    if($rright['ticket_priority_mandatory'] && !$_POST['priority']) {$error=T_('Merci de renseigner la priorité');}
    if($rright['ticket_criticality_mandatory'] && !$_POST['criticality']) {$error=T_('Merci de renseigner la criticité');}
	if($rright['ticket_description_mandatory'] && ((ctype_space($_POST['text']) || $_POST['text']=='' || ctype_space(strip_tags($_POST['text']))==1 ) || strip_tags($_POST['text'])=='')) {$error=T_('Merci de renseigner la description de ce ticket');} 
	if($rright['ticket_place_mandatory'] && $rparameters['ticket_places'] && !$_POST['ticket_places']) {$error=T_('Merci de renseigner le lieu');}
	if($rright['ticket_cat_mandatory'] && (!$_POST['category'] || !$_POST['subcat'])) {$error=T_("Merci de renseigner le champ catégorie et sous-catégorie");}
    if($rright['ticket_asset_mandatory'] && $rparameters['asset'] && !$_POST['asset_id']) {$error=T_("Merci de renseigner l'équipement");}
    if($rright['ticket_type_mandatory'] && $rparameters['ticket_type'] && !$_POST['type']) {$error=T_("Merci de renseigner le champ type");}
    if($rright['ticket_agency_mandatory'] && $rparameters['user_agency'] && !$_POST['u_agency']) {
		//check if current user have multiple agencies to display empty mandatory alert
		$qry2=$db->prepare("SELECT COUNT(*) FROM `tusers_agencies` WHERE user_id=:user_id");
		$qry2->execute(array('user_id' => $_SESSION['user_id']));
		$row2=$qry2->fetch();
		$qry2->closeCursor();
		if($row2[0]>1 && $_SESSION['profile_id']!=4) {$error=T_("Merci de renseigner l'agence");}
	}
	if($rright['ticket_tech_mandatory'] && !$_POST['technician'] && !$t_group) {$error=T_('Merci de renseigner le technicien associé à ce ticket');}
	if($rright['ticket_user_mandatory'] && !$_POST['user'] && !$u_group) {$error=T_('Merci de renseigner le demandeur associé à ce ticket');}
	//check user ticket limit 
	if($rparameters['user_limit_ticket'] && $ruser['limit_ticket_number'] && $ruser['limit_ticket_days'] && $ruser['limit_ticket_date_start']!='0000-00-00' && ($_SESSION['profile_id']==1 || $_SESSION['profile_id']==2))
	{
		$date_start_conv = date_create($ruser['limit_ticket_date_start']);
		date_add($date_start_conv, date_interval_create_from_date_string("$ruser[limit_ticket_days] days"));
		$date_end=date_format($date_start_conv, 'Y-m-d');
	
		//count number of ticket remaining in period
		$qry=$db->prepare("SELECT COUNT(*) FROM `tincidents` WHERE user=:user AND date_create BETWEEN :date_create AND :date_end AND disable='0'");
		$qry->execute(array('user' => $_SESSION['user_id'],'date_create' => $ruser['limit_ticket_date_start'],'date_end' => $date_end));
		$nbticketused=$qry->fetch();
		$qry->closeCursor();
		
		//check number of tickets in current range date
		if(date('Y-m-d')>$date_end || date('Y-m-d')<$ruser['limit_ticket_date_start'])
		{$nbticketremaining=0;} 
		else 
		{$nbticketremaining=$ruser['limit_ticket_number']-$nbticketused[0];}
		if($nbticketremaining<=0) {$error=T_('Votre limite de ticket est atteinte, prenez contact avec votre administrateur pour créditer votre compte').'.';}
	}
	//check company limit ticket
	if($rparameters['company_limit_ticket'] && ($_SESSION['profile_id']==1 || $_SESSION['profile_id']==2))
	{
		//get company limit ticket parameters
		$qry=$db->prepare("SELECT * FROM `tcompany` WHERE id=:id");
		$qry->execute(array('id' => $ruser['company']));
		$rcompany=$qry->fetch();
		$qry->closeCursor();
		
		if($rcompany['limit_ticket_number'] && $rcompany['limit_ticket_days']&& $rcompany['limit_ticket_date_start']!='0000-00-00' )
		{
			//calculate end date	
			$date_start_conv = date_create($rcompany['limit_ticket_date_start']);
			date_add($date_start_conv, date_interval_create_from_date_string("$rcompany[limit_ticket_days] days"));
			$date_end=date_format($date_start_conv, 'Y-m-d');
		
			//count number of ticket remaining in period
			$qry=$db->prepare("SELECT COUNT(*) FROM `tincidents`,`tusers` WHERE tusers.id=tincidents.user AND tusers.company=:company AND date_create BETWEEN :date_start AND :date_end AND tincidents.disable='0'");
			$qry->execute(array('company' => $rcompany['id'],'date_start' => $rcompany['limit_ticket_date_start'],'date_end' => $date_end));
			$nbticketused=$qry->fetch();
			$qry->closeCursor();
			
			//check number of tickets in current range date
			if(date('Y-m-d')>$date_end || date('Y-m-d')<$rcompany['limit_ticket_date_start'])
			{$nbticketremaining=0;} 
			else 
			{$nbticketremaining=$rcompany['limit_ticket_number']-$nbticketused[0];}
			if($nbticketremaining<=0) {$error=T_('La limite de ticket attribuée pour votre société est atteinte, prenez contact avec votre administrateur pour créditer votre compte.');}
		}
	}
	//check company limit hour
	if($rparameters['company_limit_hour'] && ($_SESSION['profile_id']==1 || $_SESSION['profile_id']==2))
	{
		//get company limit ticket parameters
		$qry=$db->prepare("SELECT * FROM `tcompany` WHERE id=:id");
		$qry->execute(array('id' => $ruser['company']));
		$rcompany=$qry->fetch();
		$qry->closeCursor();
		
		if($rcompany['limit_hour_number'] && $rcompany['limit_hour_days']&& $rcompany['limit_hour_date_start']!='0000-00-00' )
		{
			//calculate end date	
			$date_start_conv = date_create($rcompany['limit_hour_date_start']);
			date_add($date_start_conv, date_interval_create_from_date_string("$rcompany[limit_hour_days] days"));
			$date_end=date_format($date_start_conv, 'Y-m-d');
		
			//count number of hour remaining in period
			$qry=$db->prepare("SELECT SUM(tincidents.time)/60 FROM `tincidents`,`tusers` WHERE tusers.id=tincidents.user AND tusers.company=:company AND date_create BETWEEN :date_start AND :date_end AND tincidents.disable='0'");
			$qry->execute(array('company' => $rcompany['id'],'date_start' => $rcompany['limit_hour_date_start'],'date_end' => $date_end));
			$nbhourused=$qry->fetch();
			$qry->closeCursor();
			
			//check number of tickets in current range date
			if(date('Y-m-d')>$date_end || date('Y-m-d')<$rcompany['limit_hour_date_start'])
			{$nbticketremaining=0;} 
			else 
			{$nbhourremaining=$rcompany['limit_hour_number']-$nbhourused[0];}
			if($nbhourremaining<=0) {$error=T_("La limite d'heures attribuée pour votre société est atteinte, prenez contact avec votre administrateur pour créditer votre compte.");}
		}
	}
	//automatic technician category attribution
	if($rparameters['ticket_cat_auto_attribute'])
	{
		//only if technician field not display
		if($_GET['action']=='new' && !$rright['ticket_new_tech_disp'])
		{
			$auto_tech_attribute=0;
			if($_POST['subcat'])
			{
				//check if association between technician and category exist
				$qry=$db->prepare("SELECT `id`,`technician`,`technician_group` FROM `tsubcat` WHERE (technician!=0 OR technician_group!=0) AND id=:id");
				$qry->execute(array('id' => $_POST['subcat']));
				$auto_tech=$qry->fetch();
				$qry->closeCursor();
				if($auto_tech)
				{
					if($auto_tech['technician']){$_POST['technician']=$auto_tech['technician']; $auto_tech_attribute=1;}
					elseif($auto_tech['technician_group']){$t_group=$auto_tech['technician_group']; $auto_tech_attribute=1;}
				}
			}
			if($_POST['category'] && !$auto_tech_attribute)
			{
				//check if association between technician and category exist
				$qry=$db->prepare("SELECT `id`,`technician`,`technician_group` FROM `tcategory` WHERE (technician!=0 OR technician_group!=0) AND id=:id");
				$qry->execute(array('id' => $_POST['category']));
				$auto_tech=$qry->fetch();
				$qry->closeCursor();
				if($auto_tech)
				{
					if($auto_tech['technician']){$_POST['technician']=$auto_tech['technician'];}
					elseif($auto_tech['technician_group']){$t_group=$auto_tech['technician_group'];}
				}
			}
			
		}
	}
	//update calendar technician on technicien switch
	if($rparameters['planning'] && $rright['ticket_calendar'] && $globalrow['technician'] && $_POST['technician'] && $globalrow['technician']!=$_POST['technician'] && $_GET['id'])
	{
		$qry=$db->prepare("UPDATE `tevents` SET `technician`=:technician WHERE `incident`=:incident");
		$qry->execute(array('technician' => $_POST['technician'],'incident' => $_GET['id']));
	}
	//escape special char and secure string before database insert
	$_POST['description']=$_POST['text'];
	$_POST['resolution']=$_POST['text2'];
	//remove <br> generate by IE browser
	$_POST['description']=str_replace('<br><br><br>','',$_POST['description']);
	$_POST['resolution']=str_replace('<br><br><br>','',$_POST['resolution']);
	if($_POST['description']=='<br>'){$_POST['description']='';}
	if($_POST['resolution']=='<br>'){$_POST['resolution']='';}
	
	//thread generation when no error detected
	if(!$error)
	{
		//detect transfert tech group change to group
		if($t_group!=$globalrow['t_group'] && $globalrow['technician']==0 && $t_group!='' && $globalrow['t_group']!=0 ) {
			$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`text`,`type`,`group1`,`group2`) VALUES (:ticket,:date,:author,'','2',:group1,:group2)");
			$qry->execute(array('ticket' => $_GET['id'],'date' => $datetime,'author' => $_SESSION['user_id'],'group1' => $globalrow['t_group'],'group2' => $t_group));
			//update modification date
			$qry=$db->prepare("UPDATE `tincidents` SET `date_modif`=:date_modif WHERE `id`=:id");
			$qry->execute(array('id' => $_GET['id'],'date_modif' => $datetime));
		}
		//detect transfert tech change to tech
		if($rright['ticket_tech'] && $_POST['technician']!=$globalrow['technician'] && $globalrow['technician']!=0 && $_POST['technician']!='') {
			$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`text`,`type`,`tech1`,`tech2`) VALUES (:ticket,:date,:author,'','2',:tech1,:tech2)");
			$qry->execute(array('ticket' => $_GET['id'],'date' => $datetime,'author' => $_SESSION['user_id'],'tech1' => $globalrow['technician'],'tech2' => $_POST['technician']));
			//update modification date
			$qry=$db->prepare("UPDATE `tincidents` SET `date_modif`=:date_modif WHERE `id`=:id");
			$qry->execute(array('id' => $_GET['id'],'date_modif' => $datetime));
		}
		//detect transfert techgroup change to tech
		if($globalrow['t_group']!=0 && $_POST['technician']) {
			$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`text`,`type`,`group1`,`tech2`) VALUES (:ticket,:date,:author,'','2',:group1,:tech1)");
			$qry->execute(array('ticket' => $_GET['id'],'date' => $datetime,'author' => $_SESSION['user_id'],'group1' => $globalrow['t_group'],'tech1' => $_POST['technician']));
			//update modification date
			$qry=$db->prepare("UPDATE `tincidents` SET `date_modif`=:date_modif WHERE `id`=:id");
			$qry->execute(array('id' => $_GET['id'],'date_modif' => $datetime));
		}
		//detect transfert tech change to techgroup
		if($globalrow['technician']!=0 && $t_group) {
			$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`text`,`type`,`tech1`,`group2`) VALUES (:ticket,:date,:author,'','2',:tech1,:group2)");
			$qry->execute(array('ticket' => $_GET['id'],'date' => $datetime,'author' => $_SESSION['user_id'],'tech1' => $globalrow['technician'],'group2' => $t_group));
			//update modification date
			$qry=$db->prepare("UPDATE `tincidents` SET `date_modif`=:date_modif WHERE `id`=:id");
			$qry->execute(array('id' => $_GET['id'],'date_modif' => $datetime));
		}
		//detect technician attribution
		if(($rright['ticket_tech'] || $rparameters['ticket_cat_auto_attribute']) && $globalrow['technician']==0 && $_POST['technician']!='' && $_POST['technician']!='0' && $globalrow['t_group']==0)
		{
			$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`type`,`tech1`) VALUES (:ticket,:date,:author,'1',:tech1)");
			$qry->execute(array('ticket' => $_GET['id'],'date' => $datetime,'author' => $_SESSION['user_id'],'tech1' => $_POST['technician']));
			//update modification date
			$qry=$db->prepare("UPDATE `tincidents` SET `date_modif`=:date_modif WHERE `id`=:id");
			$qry->execute(array('id' => $_GET['id'],'date_modif' => $datetime));
		}
		//detect tech group attribution
		if($globalrow['t_group']==0 && $t_group!='' && $globalrow['technician']==0)
		{
			$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`type`,`group1`) VALUES (:ticket,:date,:author,'1',:group1)");
			$qry->execute(array('ticket' => $_GET['id'],'date' => $datetime,'author' => $_SESSION['user_id'],'group1' => $t_group));
			//update modification date
			$qry=$db->prepare("UPDATE `tincidents` SET `date_modif`=:date_modif WHERE `id`=:id");
			$qry->execute(array('id' => $_GET['id'],'date_modif' => $datetime));
		}
		//generate thread for switch state
		if($rright['ticket_state'] && $globalrow['state']!=$_POST['state'] && $_POST['state']!=3 && $_POST['technician']!='')
		{
			$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`type`,`state`) VALUES (:ticket,:date,:author,'5',:state)");
			$qry->execute(array('ticket' => $_GET['id'],'date' => $datetime,'author' => $_SESSION['user_id'],'state' => $_POST['state']));
			//update modification date
			$qry=$db->prepare("UPDATE `tincidents` SET `date_modif`=:date_modif WHERE `id`=:id");
			$qry->execute(array('id' => $_GET['id'],'date_modif' => $datetime));
		}
		//auto modify state from 5 to 1 if technician change (not attribute to wait tech)
		if(!$globalrow['technician'] && $_POST['technician'] && $globalrow['state']=='5' && $_POST['state']==$globalrow['state'] && $t_group=='') 
		{
			if($rparameters['debug']) {echo "<b>AUTO CHANGE STATE:</b> from 5 to 1 reason technician change detected (globalrow[state]=$globalrow[state] POST[state]=$_POST[state])<br />";}
			$_POST['state']='1';
			$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`type`,`state`) VALUES (:ticket,:date,:author,'5',:state)");
			$qry->execute(array('ticket' => $_GET['id'],'date' => $datetime,'author' => $_SESSION['user_id'],'state' => $_POST['state']));
		}
		//auto modify state from 5 to 1 if technician group change (not attribute to wait tech)
		if($globalrow['t_group']==0 && $globalrow['state']=='5' && $_POST['state']==$globalrow['state'] && $t_group!='') 
		{
			if($rparameters['debug']) {echo "<b>AUTO CHANGE STATE:</b> from 5 to 1 reason technician group change detected (globalrow[state]=$globalrow[state] POST[state]=$_POST[state] t_group=$t_group)<br />";}
			$_POST['state']='1';
			$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`type`,`state`) VALUES (:ticket,:date,:author,'5',:state)");
			$qry->execute(array('ticket' => $_GET['id'],'date' => $datetime,'author' => $_SESSION['user_id'],'state' => $_POST['state']));
		}
		//auto modify state from 5 to 2 if technician add resolution thread (wait tech to current)
		if((($_POST['resolution']!='') && ($_POST['resolution']!='\'\'')) && ($globalrow['technician']==$_SESSION['user_id']) && ($_POST['state']=='1')) 
		{
			if($rparameters['debug']) {echo "<b>AUTO CHANGE STATE</b> from 5 to 2 reason technician add resolution thread detected<br />";}
			$_POST['state']='2';		
			$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`type`,`state`) VALUES (:ticket,:date,:author,'5',:state)");
			$qry->execute(array('ticket' => $_GET['id'],'date' => $datetime,'author' => $_SESSION['user_id'],'state' => $_POST['state']));
		}
		//auto modify state from 5 to 2 if technician add resolution thread on new ticket(wait tech to current)
		if(($_POST['resolution']!='') && ($_POST['resolution']!='\'\'') && ($_POST['technician']==$_SESSION['user_id']) && ($_POST['state']=='1') && ($_GET['action']=='new')) 
		{
			if($rparameters['debug']) {echo "<b>AUTO CHANGE STATE</b> from 5 to 2 reason technician add resolution thread on new ticket detected<br />";}
			$_POST['state']='2';
			$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`type`,`state`) VALUES (:ticket,:date,:author,'5',:state)");
			$qry->execute(array('ticket' => $_GET['id'],'date' => $datetime,'author' => $_SESSION['user_id'],'state' => $_POST['state']));
		}
		
		/*
		//auto modify state from 3 to 2 when user add comment on closed ticket
		if($_SESSION['user_id']==$_POST['user'] && $_POST['user']!=$_POST['technician'] && $_POST['resolution']!='' && $_POST['state']==3 && $_GET['action']!='new') 
		{
			if($rparameters['debug']) {echo "<b>AUTO CHANGE STATE</b> to current state reason user add comment on closed ticket<br />";}
			$_POST['state']='2';
			$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`type`,`state`) VALUES (:ticket,:date,:author,'5',:state)");
			$qry->execute(array('ticket' => $_GET['id'],'date' => $datetime,'author' => $_SESSION['user_id'],'state' => $_POST['state']));
			$user_auto_switch_state=1;
		} 
		*/
		//auto modify state from 6 to 2 when user add comment on wait user ticket
		if($_SESSION['user_id']==$_POST['user'] && $_POST['user']!=$_POST['technician'] && $_POST['resolution']!='' && $_POST['state']==6 && $_GET['action']!='new') 
		{
			if($rparameters['debug']) {echo "<b>AUTO CHANGE STATE</b> to current state reason user add comment on wait user ticket<br />";}
			$_POST['state']='2';
			$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`type`,`state`) VALUES (:ticket,:date,:author,'5',:state)");
			$qry->execute(array('ticket' => $_GET['id'],'date' => $datetime,'author' => $_SESSION['user_id'],'state' => $_POST['state']));
			$user_auto_switch_state=1;
		} 
	}
	
	//insert resolution date if state is change to resolve (3)
	if($_POST['state']=='3' && $globalrow['state']!='3' && ($_POST['date_res']=='' || $_POST['date_res']=='0000-00-00 00:00:00')) {$_POST['date_res']=date("Y-m-d H:i:s");}
	
	//remove resolution date if state change from 3 to other state
	if($globalrow['state']=='3' && $_POST['state']!='3') {$_POST['date_res']='';}
	
	//unread ticket if another technician add thread
	if($_POST['resolution'] && ($globalrow['technician']!=$_SESSION['user_id'])) {$techread=0;}
	
	//auto-attribute ticket to technician if user attachment is detected
	if($_POST['user'])
	{
		//find associate tech
		$qry=$db->prepare("SELECT `tech` FROM `tusers_tech` WHERE user=:user");
		$qry->execute(array('user' => $_POST['user']));
		$row=$qry->fetch();
		$qry->closeCursor();
        if(!empty($row['tech'])) { //associate to single user
			if($rparameters['debug']) {echo '<br /><b>AUTO TECH CHANGE:</b> Auto assignment of this ticket, because technician attachment is detected on user.<br />';}
			$_POST['technician']=$row['tech'];
		} else { //check associate to  user group
			//check post user is member of group
			$qry=$db->prepare("SELECT `group` FROM `tgroups_assoc` WHERE user=:user");
			$qry->execute(array('user' => $_POST['user']));
			$member_of_group=$qry->fetch();
			$qry->closeCursor();

			if(!empty($member_of_group['group']))
			{
				//find associate tech for user group
				$qry=$db->prepare("SELECT `tech` FROM `tusers_tech` WHERE user_group=:user_group");
				$qry->execute(array('user_group' => $member_of_group['group']));
				$row=$qry->fetch();
				$qry->closeCursor();
				if(!empty($row['tech'])) {
					if($rparameters['debug']) {echo '<br /><b>AUTO TECH CHANGE:</b> Auto assignment of this ticket, because technician attachment is detected on user group.<br />';}
					$_POST['technician']=$row['tech'];
				}
			}
		}
	}
	
	//get user service to insert in tincidents table, or get selected service from field if ticket_service right
	if($_POST['user'] && !$_POST['u_service'] && !$rright['ticket_service_disp'])
	{
		$qry=$db->prepare("SELECT `service_id` FROM `tusers_services` WHERE user_id=:user_id");
		$qry->execute(array('user_id' => $_POST['user']));
		$row=$qry->fetch();
		$qry->closeCursor();  
		if(empty($row)) {$row=array();}
		if(empty($row['service_id'])) {$row['service_id']='';}
        if($_POST['state']!=3) {$u_service=$row['service_id'];} 
		elseif($_POST['state']==3 && $_GET['action']=='new') {$u_service=$row['service_id'];}
		else {$u_service=$globalrow['u_service'];} 
		if($rparameters['debug']) {echo ' post_u_service='.$u_service.'<br />'; }
	} elseif($_POST['u_service'] || $rright['ticket_service']!=0)
	{
		$u_service=$_POST['u_service'];
	} else {$u_service=$globalrow['u_service'];}
	
	if(!isset($u_service)) $u_service=0;

	if(!$error)
	{
		//convert posted datetime to SQL format, if yyyy-mm-dd is detected
		if($_POST['date_create'] && !strpos($_POST['date_create'], "-") && preg_match('~[0-9]~', $_POST['date_create']))
		{
			if(strpos($_POST['date_create'], ":"))
			{$_POST['date_create'] = DateTime::createFromFormat('d/m/Y H:i:s', $_POST['date_create']);}
			else 
			{$_POST['date_create'] = DateTime::createFromFormat('d/m/Y', $_POST['date_create']);}
			$_POST['date_create']=$_POST['date_create']->format('Y-m-d H:i:s');
		} elseif(!$_POST['date_create'] && isset($globalrow['date_create'])) {$_POST['date_create'] = $globalrow['date_create'];}
		elseif(!$_POST['date_create'] && !isset($globalrow['date_create'])) {$_POST['date_create'] = date('Y-m-d H:i:s');}
		
		if($_POST['date_hope'] && !strpos($_POST['date_hope'], "-") && preg_match('~[0-9]~', $_POST['date_hope']))
		{
			if(strpos($_POST['date_hope'], ":"))
			{$_POST['date_hope'] = DateTime::createFromFormat('d/m/Y H:i:s', $_POST['date_hope']);}
			else 
			{$_POST['date_hope'] = DateTime::createFromFormat('d/m/Y', $_POST['date_hope']);}
			$_POST['date_hope']=$_POST['date_hope']->format('Y-m-d');
		}
		if($_POST['date_res'] && !strpos($_POST['date_res'], "-") && preg_match('~[0-9]~', $_POST['date_res']))
		{
			if(strpos($_POST['date_res'], ":"))
			{$_POST['date_res'] = DateTime::createFromFormat('d/m/Y H:i:s', $_POST['date_res']);}
			else 
			{$_POST['date_res'] = DateTime::createFromFormat('d/m/Y', $_POST['date_res']);}
			$_POST['date_res']=$_POST['date_res']->format('Y-m-d H:i:s');
		}
		if($_POST['user_validation_date'] && !strpos($_POST['user_validation_date'], "-") && preg_match('~[0-9]~', $_POST['user_validation_date']))
		{
			if(strpos($_POST['user_validation_date'], ":"))
			{$_POST['user_validation_date'] = DateTime::createFromFormat('d/m/Y H:i:s', $_POST['user_validation_date']);}
			else 
			{$_POST['user_validation_date'] = DateTime::createFromFormat('d/m/Y', $_POST['user_validation_date']);}
			$_POST['user_validation_date']=$_POST['user_validation_date']->format('Y-m-d');
		}
	}

	//add selected observer
	if(!$_POST['observer1'] && $_POST['observer']) {
		$_POST['observer1']=$_POST['observer'];
	} elseif(!$_POST['observer2'] && $_POST['observer']) {
		$_POST['observer2']=$_POST['observer'];
	}elseif(!$_POST['observer3'] && $_POST['observer']) {
		$_POST['observer3']=$_POST['observer'];
	}

	//update time by element
	if(!$error && $_POST['time_response_element'] && $rparameters['ticket_time_response_element']) {$_POST['time']=$_POST['time']+$_POST['time_response_element'];}
	
	//SQL queries
	if(!$error && ($_GET['action']=='new'))
	{
		//modify read state
		if($globalrow['technician']!=$_SESSION['user_id']) {$techread=0;} //unread ticket case when creator is not technician  
		if($_POST['technician']==$_SESSION['user_id']) {$techread=1; $techread_date=date("Y-m-d H:i:s"); $userread=0;} //read ticket  
		if($_POST['user']==$_SESSION['user_id']) {$userread=1;}

		//check existing ticket avoid duplicate
		$qry=$db->prepare("SELECT `id` FROM `tincidents` WHERE `date_create`=:date_create AND `title`=:title AND `description`=:description");
		$qry->execute(array('date_create' => $_POST['date_create'],'title' => $_POST['title'],'description' => $_POST['description'] ));
		$check_ticket=$qry->fetch();
		$qry->closeCursor();
		if(isset($check_ticket['id']))
		{
			echo DisplayMessage('error',T_('Un autre ticket identique à deja été crée ').$check_ticket['id']);
			LogIt('error', 'Duplicate ticket '.$check_ticket['id'],$_SESSION['user_id']);
			exit;
		}
		
		//insert ticket
		$qry=$db->prepare("INSERT INTO `tincidents` 
		(`user`,`observer1`,`observer2`,`observer3`,`type`,`type_answer`,`u_group`,`u_service`,`u_agency`,`sender_service`,`technician`,`t_group`,`title`,`description`,`date_create`,`date_modif`,`date_hope`,`date_res`,`priority`,`criticality`,`billable`,`state`,`user_validation`,`user_validation_date`,`creator`,`time`,`time_hope`,`category`,`subcat`,`techread`,`techread_date`,`userread`,`place`,`asset_id`)
		VALUES 
		(:user,:observer1,:observer2,:observer3,:type,:type_answer,:u_group,:u_service,:u_agency,:sender_service,:technician,:t_group,:title,:description,:date_create,:date_modif,:date_hope,:date_res,:priority,:criticality,:billable,:state,:user_validation,:user_validation_date,:creator,:time,:time_hope,:category,:subcat,:techread,:techread_date,:userread,:place,:asset_id)");
		$qry->execute(array(
			'user' => $_POST['user'],
			'observer1' => $_POST['observer1'],
			'observer2' => $_POST['observer2'],
			'observer3' => $_POST['observer3'],
			'type' => $_POST['type'],
			'type_answer' => $_POST['type_answer'],
			'u_group' => $u_group,
			'u_service' => $u_service,
			'u_agency' => $_POST['u_agency'],
			'sender_service' => $_POST['sender_service'],
			'technician' => $_POST['technician'],
			't_group' => $t_group,
			'title' => $_POST['title'],
			'description' => $_POST['description'],
			'date_create' => $_POST['date_create'],
			'date_modif' => $_POST['date_create'],
			'date_hope' => $_POST['date_hope'],
			'date_res' => $_POST['date_res'],
			'priority' => $_POST['priority'],
			'criticality' => $_POST['criticality'],
			'billable' => $_POST['billable'],
			'state' => $_POST['state'],
			'user_validation' => $_POST['user_validation'],
			'user_validation_date' => $_POST['user_validation_date'],
			'creator' => $_SESSION['user_id'],
			'time' => $_POST['time'],
			'time_hope' => $_POST['time_hope'],
			'category' => $_POST['category'],
			'subcat' => $_POST['subcat'],
			'techread' => $techread,
			'techread_date' => $techread_date,
			'userread' => $userread,
			'place' => $_POST['ticket_places'],
			'asset_id' => $_POST['asset_id']
			));
	}elseif(!$error) {
		//modify read state
		if($_POST['technician']==$_SESSION['user_id']) {$techread=1; $techread_date=date("Y-m-d H:i:s");} //read ticket  
		
		//check previous change, before update, concomitant user change #4471
		$qry=$db->prepare("SELECT `technician`,`state` FROM `tincidents` WHERE id=:id");
		$qry->execute(array('id' => $_GET['id']));
		$row=$qry->fetch();
		$qry->closeCursor();
		if(!$rright['ticket_tech']) {if($row['technician']!=$_POST['technician']) {$_POST['technician']=$row['technician'];}}
		if(!$rright['ticket_state'] && !$user_auto_switch_state) {if($row['state']!=$_POST['state']) {$_POST['state']=$row['state'];}}

		//update observers
		if($rparameters['ticket_observer'] && $rright['ticket_observer'] && $_GET['id'])
		{
			$qry=$db->prepare("UPDATE `tincidents` SET `observer1`=:observer1 WHERE `id`=:id");
			$qry->execute(array('observer1' => $_POST['observer1'],'id' => $_GET['id']));
			$qry=$db->prepare("UPDATE `tincidents` SET `observer2`=:observer2 WHERE `id`=:id");
			$qry->execute(array('observer2' => $_POST['observer2'],'id' => $_GET['id']));
			$qry=$db->prepare("UPDATE `tincidents` SET `observer3`=:observer3 WHERE `id`=:id");
			$qry->execute(array('observer3' => $_POST['observer3'],'id' => $_GET['id']));
		}
		
		//update ticket
		$query = "UPDATE tincidents SET 
		user='$_POST[user]',
		type='$_POST[type]',
		type_answer='$_POST[type_answer]',
		u_group='$u_group',
		u_service='$u_service',
		u_agency='$_POST[u_agency]',
		sender_service='$_POST[sender_service]',
		technician='$_POST[technician]',
		t_group='$t_group',
		title=$_POST[title],
		description=$_POST[description],
		date_create='$_POST[date_create]',
		date_hope='$_POST[date_hope]',
		date_res='$_POST[date_res]',
		priority='$_POST[priority]',
		criticality='$_POST[criticality]',
		billable='$_POST[billable]',
		state='$_POST[state]',
		user_validation='$_POST[user_validation]',
		user_validation_date='$_POST[user_validation_date]',
		time='$_POST[time]',
		time_hope='$_POST[time_hope]',
		category='$_POST[category]',
		subcat='$_POST[subcat]',
		techread='$techread',
		techread_date='$techread_date',
		place='$_POST[ticket_places]',
		asset_id='$_POST[asset_id]'
		WHERE
		id LIKE $db_id";
		if($rparameters['debug']) {echo "<br /><b>QUERY:</b><br /> $query<br />";}
		
		$qry=$db->prepare("UPDATE `tincidents` SET
		`user`=:user,
		`type`=:type,
		`type_answer`=:type_answer,
		`u_group`=:u_group,
		`u_service`=:u_service,
		`u_agency`=:u_agency,
		`sender_service`=:sender_service,
		`technician`=:technician,
		`t_group`=:t_group,
		`title`=:title,
		`description`=:description,
		`date_create`=:date_create,
		`date_hope`=:date_hope,
		`date_res`=:date_res,
		`priority`=:priority,
		`criticality`=:criticality,
		`billable`=:billable,
		`state`=:state,
		`user_validation`=:user_validation,
		`user_validation_date`=:user_validation_date,
		`time`=:time,
		`time_hope`=:time_hope,
		`category`=:category,
		`subcat`=:subcat,
		`techread`=:techread,
		`techread_date`=:techread_date,
		`place`=:place,
		`asset_id`=:asset_id
		WHERE `id`=:id");
		$qry->execute(array(
			'user' => $_POST['user'],
			'type' => $_POST['type'],
			'type_answer' => $_POST['type_answer'],
			'u_group' => $u_group,
			'u_service' => $u_service,
			'u_agency' => $_POST['u_agency'],
			'sender_service' => $_POST['sender_service'],
			'technician' => $_POST['technician'],
			't_group' => $t_group,
			'title' => $_POST['title'],
			'description' => $_POST['description'],
			'date_create' => $_POST['date_create'],
			'date_hope' => $_POST['date_hope'],
			'date_res' => $_POST['date_res'],
			'priority' => $_POST['priority'],
			'criticality' => $_POST['criticality'],
			'billable' => $_POST['billable'],
			'state' => $_POST['state'],
			'user_validation' => $_POST['user_validation'],
			'user_validation_date' => $_POST['user_validation_date'],
			'time' => $_POST['time'],
			'time_hope' => $_POST['time_hope'],
			'category' => $_POST['category'],
			'subcat' => $_POST['subcat'],
			'techread' => $techread,
			'techread_date' => $techread_date,
			'place' => $_POST['ticket_places'],
			'asset_id' => $_POST['asset_id'],
			'id' => $_GET['id']
			));
	}

	//plugins inclusion 
	$section='ticket_core';
	include('plugin.php');

	//threads text generation
	if(!$error && $_POST['resolution'] && ($_POST['resolution']!="'<br>'") && ($_POST['resolution']!='\'\''))
	{
		if($_GET['threadedit'])
		{
			//get author from thread
			$qry=$db->prepare("SELECT `author` FROM `tthreads` WHERE id=:id");
			$qry->execute(array('id' => $_GET['threadedit']));
			$row=$qry->fetch();
			$qry->closeCursor();

			//update modification date
			$qry=$db->prepare("UPDATE `tincidents` SET `date_modif`=:date_modif WHERE `id`=:id");
			$qry->execute(array('id' => $_GET['id'],'date_modif' => $datetime));
			
			//check your own ticket for update thread right
			if($row['author']==$_SESSION['user_id']) 
			{
				if($rright['ticket_thread_edit']) {
					$qry=$db->prepare("UPDATE `tthreads` SET `text`=:text,`time`=:time WHERE `id`=:id");
					$qry->execute(array('text' => $_POST['resolution'],'time' => $_POST['time_response_element'],'id' => $_GET['threadedit']));
				}
			} else {
				if($rright['ticket_thread_edit_all']) {
					$qry=$db->prepare("UPDATE `tthreads` SET `text`=:text,`time`=:time WHERE `id`=:id");
					$qry->execute(array('text' => $_POST['resolution'],'time' => $_POST['time_response_element'],'id' => $_GET['threadedit']));
				}
			}
		}elseif($_POST['resolution']) {
			//keep old ticket time when enable time by response
			if($rparameters['ticket_time_response_element'] && $globalrow['time']!=0 && $_POST['time_response_element']!=0)
			{
				//check if it's the first time element
				$qry=$db->prepare("SELECT COUNT(`id`) FROM `tthreads` WHERE ticket=:ticket AND `time`!=0");
				$qry->execute(array('ticket' => $_GET['id']));
				$row=$qry->fetch();
				$qry->closeCursor();
				//add thread with time
				if($row[0]==0)
				{
					$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`text`,`type`,`time`) VALUES (:ticket,:date,0,:text,'0',:time)");
					$qry->execute(array('ticket' => $_GET['id'],'date' => $datetime,'text' => T_('Report du temps passé, précédemment enregistré'),'time' => $globalrow['time']));
				}
			}
			//generate new thread for this ticket
			$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`text`,`type`,`private`,`time`) VALUES (:ticket,:date,:author,:text,'0',:private,:time)");
			$qry->execute(array('ticket' => $_GET['id'],'date' => $datetime,'author' => $_SESSION['user_id'],'text' => $_POST['resolution'],'private' => $_POST['private'],'time' => $_POST['time_response_element']));
			//update modification date
			$qry=$db->prepare("UPDATE `tincidents` SET `date_modif`=:date_modif WHERE `id`=:id");
			$qry->execute(array('id' => $_GET['id'],'date_modif' => $datetime));
			//unread for user if technician add comment
			if($_POST['technician']==$_SESSION['user_id'])
			{
				$qry=$db->prepare("UPDATE `tincidents` SET `userread`='0' WHERE `id`=:id");
				$qry->execute(array('id' => $_GET['id']));
			}
		}
	}

	//threads insert close state
	if(!$error && $_POST['state']=='3' && $globalrow['state']!='3')
	{
		$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`type`) VALUES (:ticket,:date,:author,'4')");
		$qry->execute(array('ticket' => $_GET['id'],'date' => $datetime,'author' => $_SESSION['user_id']));
		//update modification date
		$qry=$db->prepare("UPDATE `tincidents` SET `date_modif`=:date_modif WHERE `id`=:id");
		$qry->execute(array('id' => $_GET['id'],'date_modif' => $datetime));
	}
	
	if(!$error)
	{
		//uploading files
		include "./core/upload.php";
	}

	//auto send mail
	if(!$error)
	{
		if($rparameters['mail_auto_type'] || $rparameters['mail_auto_user_newticket'] || $rparameters['survey'] || $rparameters['mail_auto'] || $rparameters['mail_auto_user_modify'] || $rparameters['mail_auto_tech_modify'] || $rparameters['mail_auto_tech_attribution'] || $rparameters['mail_auto_tech_modify'] || $rparameters['mail_newticket']){
			if($rparameters['mail'] && $rparameters['mail_smtp'])
			{
				include('./core/auto_mail.php');
			} else {
				echo DisplayMessage('error', T_("Le connecteur SMTP n'est pas configuré, vérifier dans Administration > Paramètres > Connecteurs"));
				exit;
			}
		}
	}
	
	//display message
	if(!$error)
	{
		echo DisplayMessage('success',T_('Ticket sauvegardé'));
		$hide_button=1; //case press save button during saving ticket
	} else {
		echo DisplayMessage('error',$error);
	}

	//redirect to ticket list for quit or send button
	if(!$error && ($_POST['quit'] || $_POST['send']))
	{
		$url_get_parameters=str_replace('&amp;','&',$url_get_parameters);
		echo '<script language="Javascript">document.location.replace("./index.php?page=dashboard&'.$url_get_parameters.'");</script>';
	}
	
	//send mail
	if(!$error && $_POST['mail'])
	{
	
		//redirect to preview mail page
		$url="./index.php?page=preview_mail&id=$_GET[id]&userid=$_GET[userid]&state=$_GET[state]&category=$_GET[category]&subcat=$_GET[subcat]&viewid=$_GET[viewid]&view=$_GET[view]&date_start=$_GET[date_start]&date_end=$_GET[date_end]&companyview=$_GET[companyview]";
		$url=preg_replace('/%/','%25',$url);
		$url=preg_replace('/%2525/','%25',$url);
		echo '<script language="Javascript">document.location.replace("'.$url.'");</script>';
	}
	
    if(!$error && !$_POST['addcalendar'] && !$_POST['addevent'])
    {
		$url_get_parameters=str_replace('&amp;','&',$url_get_parameters);
		//global redirect on current ticket
		$url="./index.php?page=ticket&id=$_GET[id]&action=$_POST[action]&edituser=$_POST[edituser]&cat=$_POST[category]&editcat=$_POST[subcat]&$url_get_parameters$down";
		$url=preg_replace('/%/','%25',$url);
		$url=preg_replace('/%2525/','%25',$url);
		echo '
		<script language="JavaScript">
			function redirect(){window.location="'.$url.'"}
			setTimeout("redirect()",'.$rparameters['time_display_msg'].');
		</script>
		';
    }
	
	//modify ticket state on close ticket button
	if(!$error && $_POST['close'] && $rright['ticket_close']) 
	{
		//update tincidents
		$qry=$db->prepare("UPDATE `tincidents` SET `state`='3',`date_res`=:date_res WHERE `id`=:id");
		$qry->execute(array('date_res' => $datetime,'id' => $_GET['id']));
			
		//auto send mail
		$_POST['state']=3;
		if($rparameters['mail_auto'] && !$_POST['upload']){include('core/auto_mail.php');}
		
		if($_SESSION['profile_id']!=0 && $_SESSION['profile_id']!=4)
		{
			//unread ticket for technician only if user close ticket
			$qry=$db->prepare("UPDATE `tincidents` SET `techread`='0' WHERE `id`=:id");
			$qry->execute(array('id' => $_GET['id']));
		}
		//update thread
		$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`type`,`author`) VALUES (:ticket,:date,'4',:author)");
		$qry->execute(array('ticket' => $_GET['id'],'date' => $datetime,'author' => $_SESSION['user_id']));
		//update modification date
		$qry=$db->prepare("UPDATE `tincidents` SET `date_modif`=:date_modif WHERE `id`=:id");
		$qry->execute(array('id' => $_GET['id'],'date_modif' => $datetime));
		//redirect to tickets list
		$url_get_parameters=str_replace('&amp;','&',$url_get_parameters);
		echo '
		<script language="JavaScript">
			function redirect(){window.location="./index.php?page=dashboard&'.$url_get_parameters.'"}
			setTimeout("redirect()",'.$rparameters['time_display_msg'].');
		</script>
		';
	}	
	//modify ticket state on reopen ticket button
	if(!$error && $_POST['reopen'] && $rright['ticket_reopen']) 
	{
		//update tincidents
		$qry=$db->prepare("UPDATE `tincidents` SET `state`='2',`date_res`='',`user_validation_date`='',`user_validation`='0' WHERE `id`=:id");
		$qry->execute(array('id' => $_GET['id']));
			
		//switch state
		$_POST['state']=2;
				
		if($_SESSION['profile_id']!=0 && $_SESSION['profile_id']!=4)
		{
			//unread ticket for technician only if user reopen ticket
			$qry=$db->prepare("UPDATE `tincidents` SET `techread`='0' WHERE `id`=:id");
			$qry->execute(array('id' => $_GET['id']));
		}
		$url_get_parameters=str_replace('&amp;','&',$url_get_parameters);
		//global redirect on current ticket
		$url="./index.php?page=ticket&id=$_GET[id]&action=$_POST[action]&edituser=$_POST[edituser]&cat=$_POST[category]&editcat=$_POST[subcat]&$url_get_parameters&down=1";
		$url=preg_replace('/%/','%25',$url);
		$url=preg_replace('/%2525/','%25',$url);
		echo '
		<script language="JavaScript">
			function redirect(){window.location="'.$url.'"}
			setTimeout("redirect()",'.$rparameters['time_display_msg'].');
		</script>
		';
	}	
}
//redirect to tickets list
if($_POST['cancel']) 
{
	echo DisplayMessage('success',T_('Annulation pas de modification'));
	$url_get_parameters=str_replace('&amp;','&',$url_get_parameters);
	echo '
	<script language="JavaScript">
		function redirect(){window.location="./index.php?page=dashboard&'.$url_get_parameters.'"}
		setTimeout("redirect()",'.$rparameters['time_display_msg'].');
	</script>
	';
}
  
//read ticket technician
if(($globalrow['techread']=="0")&&($globalrow['technician']==$_SESSION['user_id'])) 
{
	$current_date_hour=date("Y-m-d H:i:s");
	$qry=$db->prepare("UPDATE `tincidents` SET `techread`='1',`techread_date`=:techread_date WHERE `id`=:id");
	$qry->execute(array('techread_date' => $current_date_hour,'id' => $_GET['id']));
}

//read ticket user
if(($globalrow['userread']=="0")&&($globalrow['user']==$_SESSION['user_id'])) 
{
	$qry=$db->prepare("UPDATE `tincidents` SET `userread`='1' WHERE `id`=:id");
	$qry->execute(array('id' => $_GET['id']));
}

if($rright['ticket_next']!=0 && !$mobile && $_GET['action']!='new')
{
	//find next ticket
	$qry=$db->prepare("SELECT MIN(id) FROM `tincidents` WHERE id > :id AND id IN (SELECT id FROM tincidents WHERE technician=:technician AND state=:state AND id NOT LIKE :id) AND disable='0'");
	$qry->execute(array('id' => $_GET['id'],'technician' => $_SESSION['user_id'],'state' => $globalrow['state']));
	$next=$qry->fetch();
	$qry->closeCursor();
	//find previous ticket
	$qry=$db->prepare("SELECT MIN(id) FROM `tincidents` WHERE id < :id AND id IN (SELECT id FROM tincidents WHERE technician=:technician AND state=:state AND id NOT LIKE :id) AND disable='0'");
	$qry->execute(array('id' => $_GET['id'],'technician' => $_SESSION['user_id'],'state' => $globalrow['state']));
	$previous=$qry->fetch();
	$qry->closeCursor();
}

//calculate percentage of ticket resolution
if($globalrow['time_hope']!=0 && ($rright['ticket_time_disp']!=0 && $rright['ticket_time_hope_disp']!=0))
{
	$percentage=($globalrow['time']*100)/$globalrow['time_hope'];
	$percentage=round($percentage);
	if(($globalrow['time']!='1') && ($globalrow['time_hope']!='1') && ($globalrow['time_hope']>=$globalrow['time'])) {$percentage=' <span title="'.T_("Pourcentage d'avancement du ticket basé sur le temps passé et estimé").'">('.$percentage.'%)</span> ';} else {$percentage='';}
}

//cut title for long case
$nbtitle=strlen($globalrow['title']);
if($nbtitle>50)
{
	$title=mb_substr($globalrow['title'], 0, 50);
	$title=$title.'...';
} else {$title=$globalrow['title'];}

//redirect down page
if($_GET['down']){echo '<script>$(\'html,body\').animate({scrollTop: document.body.scrollHeight},"fast");</script>';}
?>