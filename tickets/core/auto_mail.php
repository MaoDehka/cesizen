<?php
################################################################################
# @Name : /core/auto_mail.php
# @Description : page to send automatic mail
# @Call : ./core/ticket.php
# @Parameters : ticket id 
# @Author : Flox
# @Update : 05/06/2023
# @Update : 19/03/2024
# @Version : 3.2.50 p1
################################################################################

//prevent direct access
if(!isset($_SESSION['user_id'])) {echo 'ERROR 403 : /core/auto_mail.php'; header('HTTP/1.0 403 Forbidden'); exit;}

//secure string
$db_id=strip_tags($db->quote($_GET['id']));

//check if mail is already sent
$qry = $db->prepare("SELECT `open`,`close` FROM `tmails` WHERE incident=:id");
$qry->execute(array('id' => $_GET['id']));
$mail_sended=$qry->fetch();
$qry->closeCursor();
if(empty($mail_sended)) {$mail_sended=array();}

//initialize variables 
if(!isset($send)) $send = ''; 
if(!isset($t_group)) $t_group = ''; 
if(!isset($usermail['mail'])) $usermail['mail'] = ''; 
if(!isset($_POST['type'])) $_POST['type'] = ''; 
if(!isset($_POST['resolution'])) $_POST['resolution'] = ''; 
if(!isset($_POST['private'])) $_POST['private'] = ''; 
if(!isset($_POST['modify'])) $_POST['modify']= ''; 
if(!isset($_POST['quit'])) $_POST['quit'] = ''; 
if(!isset($_POST['send'])) $_POST['send'] = ''; 
if(!isset($_POST['close'])) $_POST['close'] = ''; 
if(!isset($autoclose)) $autoclose = 0; //call from cron job
if(!isset($mail_sended['open'])) $mail_sended['open'] = ''; 
if(!isset($mail_sended['close'])) $mail_sended['close'] = ''; 
if(!isset($mail_u_group_members)) $mail_u_group_members = ''; 
if(!isset($globalrow['observer1'])) {$globalrow['observer1']='';}
if(!isset($globalrow['observer2'])) {$globalrow['observer2']='';}
if(!isset($globalrow['observer3'])) {$globalrow['observer3']='';}

//check user group defined as user on ticket 
$qry = $db->prepare("SELECT u_group FROM `tincidents` WHERE tincidents.id=:id");
$qry->execute(array('id' => $_GET['id']));
$mail_u_group=$qry->fetch();
$qry->closeCursor();
if(empty($mail_u_group['u_group'])) {$mail_u_group['u_group']=0;}
if($mail_u_group['u_group']!=0)
{
	//check if group members have mail
	$qry = $db->prepare("SELECT `tusers`.mail FROM `tusers`,`tgroups_assoc` WHERE `tusers`.id=`tgroups_assoc`.user AND `tgroups_assoc`.group=:group AND `tusers`.disable='0'");
	$qry->execute(array('group' => $mail_u_group['u_group']));
	$mail_u_group_members=$qry->fetch();
	$qry->closeCursor();
	if($mail_u_group_members)
	{
		$usermail['mail']=1;
		if($rparameters['debug']) {echo "<b>AUTO MAIL USER:</b> group detected with mail adresses <br />";}
	} else {
		if($rparameters['debug']) {echo "<b>AUTO MAIL USER:</b> group detected without mail adresses <br />";}
	}
} else {
	//check if user have mail
	$qry = $db->prepare("SELECT tusers.mail FROM `tusers`,`tincidents` WHERE tincidents.user=tusers.id AND tincidents.id=:id");
	$qry->execute(array('id' => $_GET['id']));
	$usermail=$qry->fetch();
	$qry->closeCursor();
	if($usermail && $rparameters['debug']) {echo "<b>AUTO MAIL SENDER:</b> user detected <br />";}
}

//check if connected user is member of group of technician
if($t_group)
{
	//check if user have mail
	$qry = $db->prepare("SELECT COUNT(`id`) FROM `tgroups_assoc` WHERE `group`=:group_id AND `user`=:user_id");
	$qry->execute(array('group_id' => $t_group,'user_id' => $_SESSION['user_id']));
	$current_user_member_of_tech_group=$qry->fetch();
	$qry->closeCursor();
	$current_user_member_of_tech_group=$current_user_member_of_tech_group[0];
} else {$current_user_member_of_tech_group=0;}

//debug
if($rparameters['debug']) {echo "<b>AUTO MAIL VAR:</b> SESSION[profile_id]=$_SESSION[profile_id] mail_auto_user_modify=$rparameters[mail_auto_user_modify] _POST[resolution]=$_POST[resolution] _POST[private]=$_POST[private] <br />";}

//case send auto mail to tech when technician attribution
if(($rparameters['mail_auto_tech_attribution']==1) && (($_POST['send'] || $_POST['modify'] || $_POST['quit']) && (($globalrow['technician']!=$_POST['technician']) || ($t_group && $t_group!=$globalrow['t_group'])) && ($_POST['technician']!=$_SESSION['user_id'])))
{
	//debug
	if($rparameters['debug']) {echo "<b>AUTO MAIL DETECT:</b>  FROM system TO tech  (Reason: mail_auto_tech_attribution ticket technician attribution is detected)<br> ";}
	
	if($rparameters['mail_from_adr']){$from=$rparameters['mail_from_adr'];} else {$from=$ruser['mail'];}
	
	//technician group detection
	if($t_group) 
	{
		$to='';
		//check group change or attribution
		if($t_group!=$globalrow['t_group'])
		{
			$qry2=$db->prepare("SELECT `tusers`.mail FROM `tusers`,`tgroups_assoc` WHERE `tusers`.id=`tgroups_assoc`.user AND `tgroups_assoc`.group=:group AND `tusers`.disable='0' ");
			$qry2->execute(array('group' => $t_group));
			while($row2=$qry2->fetch()) {if($row2['mail']){$to.=$row2['mail'].';';}}
			$qry2->closeCursor();
		} 
	} else {
		//get tech mail 
		$qry = $db->prepare("SELECT `mail` FROM tusers WHERE id=:id");
		$qry->execute(array('id' => $_POST['technician']));
		$techrow=$qry->fetch();
		$qry->closeCursor();
		if(!isset($techrow['mail'])) {$techrow=array(); $techrow['mail']='';}
		$to=$techrow['mail'];
	}
	$dest_mail=$to;
	
	//check if tech have mail
	if($to) 
	{
		$object=T_('Le ticket').' n°'.$_GET['id'].' : '.htmlspecialchars_decode($_POST['title']).' '.T_('vous a été attribué');
		//get user name 
		$qry = $db->prepare("SELECT `firstname`,`lastname` FROM tusers WHERE id=:id AND `disable`=0");
		$qry->execute(array('id' => $_POST['user']));
		$userrow=$qry->fetch();
		$qry->closeCursor();
		if(!isset($userrow)) {$userrow=array();}
		if(!isset($userrow['firstname'])) {$userrow['firstname'];}
		if(!isset($userrow['lastname'])) {$userrow['lastname'];}
		$message = '
		'.T_('Le ticket').' n°'.$_GET['id'].' '.T_('vous a été attribué').' <br />
		<br />
		<u>'.T_('Demandeur').' :</u><br />
		'.$userrow['firstname'].' '.$userrow['lastname'].'<br />		
		<br />	
		<u>'.T_('Objet').' :</u><br />
		'.$_POST['title'].'<br />		
		<br />	
		<u>'.T_('Description').' :</u><br />
		'.$_POST['description'].'<br />
		<br />
		'.T_("Pour plus d'informations vous pouvez consulter le ticket sur").' <a href="'.$rparameters['server_url'].'/index.php?page=ticket&id='.$_GET['id'].'">'.$rparameters['server_url'].'/index.php?page=ticket&id='.$_GET['id'].'</a>.
		';
		$mail_auto=true;
		require('./core/message.php');
		if($mail_send) { //insert thread
			$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`text`,`type`,`dest_mail`) VALUES (:ticket,:date,'0','','3',:dest_mail)");
			$qry->execute(array('ticket' => $_GET['id'],'date' => date('Y-m-d H:i:s'),'dest_mail' => $dest_mail));
		}
	} else {if($rparameters['debug']) {echo "technician mail is empty or no technician associated or tech group no change on this ticket";}}
}

//case send mail to user where ticket open by technician.
if($rparameters['mail_auto'] && ((($mail_sended['open']=='') && ($_POST['modify'] || $_POST['quit']) && ($_SESSION['profile_id']!=2 && $_SESSION['profile_id']!=3 && $_SESSION['profile_id']!=1))) && !$_POST['private']) 
{
	if($usermail['mail'] || $rparameters['mail_cc'])
	{
		//debug
		if($rparameters['debug']) {echo "<b>AUTO MAIL DETECT:</b> FROM tech TO user (Reason: mail_auto enable, and open detect by technician.) <br />";}

		//add observer if parameter is enable
		if($rparameters['ticket_observer'])
		{
			if($_POST['observer'])
			{
				//check observer mail
				$qry=$db->prepare("SELECT `mail` FROM `tusers` WHERE id=:id");
				$qry->execute(array('id' => $_POST['observer']));
				$row=$qry->fetch();
				$qry->closeCursor();
				if(!empty($row['mail'])) {$_POST['usercopy']=$row['mail'];}
			}
			if($globalrow['observer1'])
			{
				//check observer mail
				$qry=$db->prepare("SELECT `mail` FROM `tusers` WHERE id=:id");
				$qry->execute(array('id' => $globalrow['observer1']));
				$row=$qry->fetch();
				$qry->closeCursor();
				if(!empty($row['mail'])) {$_POST['usercopy']=$row['mail'];}
				//echo 'case'.$globalrow['observer1'].; exit;
			}
			if($globalrow['observer2'])
			{
				//check observer mail
				$qry=$db->prepare("SELECT `mail` FROM `tusers` WHERE id=:id");
				$qry->execute(array('id' => $globalrow['observer2']));
				$row=$qry->fetch();
				$qry->closeCursor();
				if(!empty($row['mail'])) {$_POST['usercopy2']=$row['mail'];}
			}
			if($globalrow['observer3'])
			{
				//check observer mail
				$qry=$db->prepare("SELECT `mail` FROM `tusers` WHERE id=:id");
				$qry->execute(array('id' => $globalrow['observer3']));
				$row=$qry->fetch();
				$qry->closeCursor();
				if(!empty($row['mail'])) {$_POST['usercopy3']=$row['mail'];}
			}
		}

		//auto send open notification mail
		$to=''; //auto mail technician group attribution bug
		$send=1;
		$mail_auto=true;
		include('./core/mail.php');
		//insert mail table
		$qry=$db->prepare("INSERT INTO `tmails` (`incident`,`open`,`close`) VALUES (:incident,'1','0')");
		$qry->execute(array('incident' => $_GET['id']));
	} else {
		//debug
		if($rparameters['debug']) {echo "<b>AUTO MAIL DETECT:</b> FROM tech TO user (Reason: mail_auto enable, and open detect by technician.) but user have no mail and mail_cc empty message not sent<br />";}
	}
//case send mail to user where ticket close by technician.
} elseif($rparameters['mail_auto'] && ($_POST['state']=='3' || $_POST['state']=='4') && ($_POST['modify'] || $_POST['quit'] || $_POST['close']) && ($_SESSION['profile_id']==0 || $_SESSION['profile_id']==4) || $autoclose==1) 
{
	if($usermail['mail'] || $rparameters['mail_cc'])
	{
		//debug
		if($rparameters['debug']) {echo "<b>AUTO MAIL DETECT:</b> FROM tech TO user (Reason: mail_auto enable, and close detect by technician.)<br />";}
		
		if ($mail_sended['open']=='1')
		{
			//check if is the first close mail
			if ($mail_sended['close']=='0')
			{
				$send=1;
				$mail_auto=true;

				//add observer if parameter is enable
				if($rparameters['ticket_observer'])
				{
					//get observer
					$qry=$db->prepare("SELECT `observer1`,`observer2`,`observer3` FROM `tincidents` WHERE id=:id");
					$qry->execute(array('id' => $_GET['id']));
					$observer=$qry->fetch();
					$qry->closeCursor();

					if($_POST['observer'])
					{
						//check observer mail
						$qry=$db->prepare("SELECT `mail` FROM `tusers` WHERE id=:id");
						$qry->execute(array('id' => $_POST['observer']));
						$row=$qry->fetch();
						$qry->closeCursor();
						if(!empty($row['mail'])) {$_POST['usercopy']=$row['mail'];}
					}
					if($observer['observer1'])
					{
						//check observer mail
						$qry=$db->prepare("SELECT `mail` FROM `tusers` WHERE id=:id");
						$qry->execute(array('id' => $observer['observer1']));
						$row=$qry->fetch();
						$qry->closeCursor();
						if(!empty($row['mail'])) {$_POST['usercopy']=$row['mail'];}
					}
					if($observer['observer2'])
					{
						//check observer mail
						$qry=$db->prepare("SELECT `mail` FROM `tusers` WHERE id=:id");
						$qry->execute(array('id' => $observer['observer2']));
						$row=$qry->fetch();
						$qry->closeCursor();
						if(!empty($row['mail'])) {$_POST['usercopy2']=$row['mail'];}
					}
					if($observer['observer3'])
					{
						//check observer mail
						$qry=$db->prepare("SELECT `mail` FROM `tusers` WHERE id=:id");
						$qry->execute(array('id' => $observer['observer3']));
						$row=$qry->fetch();
						$qry->closeCursor();
						if(!empty($row['mail'])) {$_POST['usercopy3']=$row['mail'];}
					}
				}
				//auto send close notification mail
				include('./core/mail.php');
				//update mail table
				$qry=$db->prepare("UPDATE tmails SET close='1' WHERE incident=:incident");
				$qry->execute(array('incident' => $_GET['id']));
			} else {
				//close mail already sent
			}
		} else {
			//close not sent because no open mail was sent
		}
	} else {
		//debug
		if($rparameters['debug']) {echo "<b>AUTO MAIL DETECT :</b> FROM tech TO user (Reason: mail_auto enable, and close detect by technician.) but user have no mail and mail_cc empty message not sent<br />";}
	}
//case send mail to user where technician add thread in ticket
} elseif ($rparameters['mail_auto_user_modify'] && ($_POST['resolution']!='') && ($_POST['resolution']!='\'\'') && !$_POST['private'] && ($_SESSION['user_id']==$_POST['technician'] || ($t_group && $current_user_member_of_tech_group) || ($_SESSION['profile_id']==0 || $_SESSION['profile_id']==4)) && (!empty($globalrow['user']) || $mail_u_group_members)) #6510
{
	if($usermail['mail'] || $rparameters['mail_cc'])
	{
		//debug
		if($rparameters['debug']) {echo "<b>AUTO MAIL DETECT :</b> FROM tech TO user (Reason: mail_auto_user_modify enable and technician add thread.<br> ";}
		//check if user is the technician and not user
		if($globalrow['user']!=$_SESSION['user_id'] || $rparameters['ticket_observer'])
		{
			//add observer if parameter is enable
			if($rparameters['ticket_observer'])
			{
				
				if($_POST['observer'])
				{
					//check observer mail
					$qry=$db->prepare("SELECT `mail` FROM `tusers` WHERE id=:id");
					$qry->execute(array('id' => $_POST['observer']));
					$row=$qry->fetch();
					$qry->closeCursor();
					if(!empty($row['mail'])) {$_POST['usercopy']=$row['mail'];}
				}
				if($globalrow['observer1'])
				{
					//check observer mail
					$qry=$db->prepare("SELECT `mail` FROM `tusers` WHERE id=:id");
					$qry->execute(array('id' => $globalrow['observer1']));
					$row=$qry->fetch();
					$qry->closeCursor();
					if(!empty($row['mail'])) {$_POST['usercopy']=$row['mail'];}
				}
				if($globalrow['observer2'])
				{
					//check observer mail
					$qry=$db->prepare("SELECT `mail` FROM `tusers` WHERE id=:id");
					$qry->execute(array('id' => $globalrow['observer2']));
					$row=$qry->fetch();
					$qry->closeCursor();
					if(!empty($row['mail'])) {$_POST['usercopy2']=$row['mail'];}
				}
				if($globalrow['observer3'])
				{
					//check observer mail
					$qry=$db->prepare("SELECT `mail` FROM `tusers` WHERE id=:id");
					$qry->execute(array('id' => $globalrow['observer3']));
					$row=$qry->fetch();
					$qry->closeCursor();
					if(!empty($row['mail'])) {$_POST['usercopy3']=$row['mail'];}
				}
			}
			$_POST['withattachment']=1;
			$send=1;
			$mail_auto=true;
			include('./core/mail.php');
		}
	} else {
		//debug
		if($rparameters['debug']) {echo "<b>AUTO MAIL DETECT :</b> FROM tech TO user (Reason: mail_auto_user_modify enable and technician add thread) but user have no mail and mail_cc empty, message not sent.<br> ";}
	}
	
//send mail to admin where user open new ticket
} elseif($rparameters['mail_newticket'] && ($_POST['send'] || ($_POST['upload'] && $_GET['action']=='new')) && ($_SESSION['profile_id']!=0 && $_SESSION['profile_id']!=4) && $_SESSION['user_id']) 
{
	//find user name
	$qry = $db->prepare("SELECT tusers.`firstname` AS firstname,tusers.`lastname` AS lastname,tusers.`mail` AS mail,tcompany.`name` AS company FROM tusers,tcompany WHERE tusers.company=tcompany.id AND tusers.id=:id");
	$qry->execute(array('id' => $_SESSION['user_id']));
	$userrow=$qry->fetch();
	$qry->closeCursor();
	
	//mail parameters
	if(!$rparameters['mail_from_adr'])
	{
		if($userrow['mail']) {$from=$userrow['mail'];} else {$from=$rparameters['mail_cc'];}
	} else {
		$from=$rparameters['mail_from_adr'];
	}
	
	if($rparameters['mail_newticket_address'])
	{
		if($rparameters['debug']) {echo "<b>AUTO MAIL DETECT:</b>  FROM user TO tech OR parameter_cc (Reason: mail_newticket enable and user open ticket.<br> ";} //debug
		if($rparameters['ticket_places'] && $_POST['ticket_places']) {
			//find place name
			$qry = $db->prepare("SELECT `name` FROM tplaces WHERE id=:id");
			$qry->execute(array('id' => $_POST['ticket_places']));
			$place=$qry->fetch();
			$qry->closeCursor();
			$place='
			<br />
			<u>'.T_('Lieu').' :</u><br />
			'.$place['name'].'<br />		
			<br />';
		} else {$place='';}
		if($userrow['company']!="Aucune"){$company_name=' '.T_('de la société').' '.$userrow['company'];} else {$company_name='';}
		$to=$rparameters['mail_newticket_address'];
		$object=T_('Un nouveau ticket a été déclaré par ').$userrow['firstname'].' '.$userrow['lastname'].$company_name.' : '.htmlspecialchars_decode($_POST['title']);
		$message = '
		'.T_('Le ticket').' n°'.$_GET['id'].' '.T_("a été déclaré par l'utilisateur").' '.$userrow['firstname'].' '.$userrow['lastname'].$company_name.'.<br />
		<br />
		<u>'.T_('Objet').' :</u><br />
		'.$_POST['title'].'<br />	
		'.$place.'	
		<br />	
		<u>'.T_('Description').' :</u><br />
		'.$_POST['text'].'<br />
		<br />
		'.T_("Pour plus d'informations vous pouvez consulter le ticket sur").' <a href="'.$rparameters['server_url'].'/index.php?page=ticket&id='.$_GET['id'].'">'.$rparameters['server_url'].'/index.php?page=ticket&id='.$_GET['id'].'</a>.
		';
		$mail_auto=true;
		require('./core/message.php');
		if($mail_send) { //insert thread
			$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`text`,`type`,`dest_mail`) VALUES (:ticket,:date,'0','','3',:dest_mail)");
			$qry->execute(array('ticket' => $_GET['id'],'date' => date('Y-m-d H:i:s'),'dest_mail' => $rparameters['mail_newticket_address']));
		}
	} else {
		//debug
		if($rparameters['debug']) {echo "<b>AUTO MAIL DETECT:</b> FROM user TO tech OR parameter_cc (Reason: mail_newticket enable and user open ticket, message not sent no administrator mail specified<br> ";}
	}
	
//send mail to technician where an user add thread in ticket
} elseif ($rparameters['mail_auto_tech_modify'] && ($_POST['modify'] || $_POST['quit'] || $_POST['upload'] || $_POST['close']) && ((($_POST['resolution']!='') && ($_POST['resolution']!='\'\'')) || $_FILES['file']['name'] || $_POST['close']) && $_SESSION['user_id'])
{
	//debug
	if($rparameters['debug']) {echo "<b>AUTO MAIL DETECT:</b> FROM user TO tech (Reason: mail_auto_tech_modify enable and user add thread in ticket.)<br> ";}
	//find user name
	$qry = $db->prepare("SELECT `mail` FROM tusers WHERE id=:id");
	$qry->execute(array('id' => $_SESSION['user_id']));
	$userrow=$qry->fetch();
	$qry->closeCursor();
	
	//define from mail
	if(!$rparameters['mail_from_adr'])
	{
		if($userrow['mail']) {$from=$userrow['mail'];} else {$from=$rparameters['mail_cc'];}
	} else {
		$from=$rparameters['mail_from_adr'];
	}
	//check if it's technician or technician group
	$send_it=0;
	$to='';
	if($t_group)
	{
		$qry=$db->prepare("SELECT `tusers`.mail FROM `tusers`,`tgroups_assoc` WHERE `tusers`.id=`tgroups_assoc`.user AND `tgroups_assoc`.group=:group AND `tusers`.disable='0' ");
		$qry->execute(array('group' => $t_group));
		while($row=$qry->fetch()) {if($row['mail']){$to.=$row['mail'].';';}}
		$qry->closeCursor();
		$send_it=1;
	} else {
		//get tech mail 
		$qry = $db->prepare("SELECT `id`,`mail` FROM tusers WHERE id=:id");
		$qry->execute(array('id' => $globalrow['technician']));
		$techrow=$qry->fetch();
		$qry->closeCursor();
		if($techrow['mail']) {$to=$techrow['mail'];}
		if($techrow['id']!=$_SESSION['user_id']) {$send_it=1;}
	}

	//check if tech have mail
	if($to && $send_it) 
	{
		$modif='';
		$title=htmlspecialchars_decode($_POST['title']);
		$object=T_('Votre ticket').' n°'.$_GET['id'].' : '.$title.' '.T_('a été modifié');
		//remove single quote in post data
		$resolution = str_replace("'", "", $_POST['resolution']);
		$title = str_replace("'", "", $_POST['title']);
		//generate modification part
		if($resolution) {$modif='<u>'.T_('Ajout du commentaire').' :</u><br />'.$resolution.'<br />';}
		if($_FILES['file']['name']) {$modif='<u>'.T_("Ajout d'un fichier joint").' :</u><br />'.$_FILES['file']['name'].'<br />';}
		if($_POST['close']) {$modif=''.T_("Fermeture du ticket").'<br />';}
		$message = '
		'.T_('Le ticket').' n°'.$_GET['id'].' '.T_('a été modifié ').' <br />
		<br />
		<u>'.T_('Objet').' :</u><br />
		'.$title.'<br />		
		<br />	
		'.$modif.'
		<br />
		'.T_("Pour plus d'informations vous pouvez consulter le ticket sur").' <a href="'.$rparameters['server_url'].'/index.php?page=ticket&id='.$_GET['id'].'#down">'.$rparameters['server_url'].'/index.php?page=ticket&id='.$_GET['id'].'#down</a>.
		';
		$mail_auto=true;
		require('./core/message.php');
		if($mail_send) { //insert thread
			if(is_array($to)) {$to=implode(' ',$to);}
			$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`text`,`type`,`dest_mail`) VALUES (:ticket,:date,'0','','3',:dest_mail)");
			$qry->execute(array('ticket' => $_GET['id'],'date' => date('Y-m-d H:i:s'),'dest_mail' => $to));
		}
	} else {if($rparameters['debug']) {echo "technician mail is empty or no technician associated with this ticket";}}
}

//case send auto mail to user where user open ticket
if($rparameters['mail_auto_user_newticket'] && ($mail_sended['open']=='') && $_GET['action']=='new' && ($_POST['send'] || $_POST['modify'] || $_POST['quit']|| $_POST['upload']) && ($_SESSION['profile_id']==2 || $_SESSION['profile_id']==3 || $_SESSION['profile_id']==1))
{
	if($usermail['mail'] || $rparameters['mail_cc'])
	{
		//debug
		if($rparameters['debug']) {echo "<b>AUTO MAIL DETECT:</b> FROM user TO user (Reason: mail_auto_user_newticket enable, and open detect by user.) <br />";}
		//auto send open notification mail
		$to='';
		$send=1;
		$mail_auto=true;
		include('./core/mail.php');
		//insert mail table
		$qry=$db->prepare("INSERT INTO `tmails` (`incident`,`open`,`close`) VALUES (:incident,'1','0')");
		$qry->execute(array('incident' => $_GET['id']));
	} else {
		//debug
		if($rparameters['debug']) {echo "<b>AUTO MAIL DETECT:</b> FROM user TO user (Reason: mail_auto_user_newticket enable, and open detect by user.) but user have no mail and mail_cc empty message not sent<br />";}
	}
}

//send mail to user from tech for survey where ticket is in survey parameter state
if($rparameters['survey'] && ($_POST['modify'] || $_POST['quit'] || $_POST['close'] || $_POST['mail']) && ($_POST['state']==$rparameters['survey_ticket_state']))
{
	
	if($usermail['mail'])
	{
		//debug
		if($rparameters['debug']) {echo "<b>AUTO MAIL DETECT:</b> FROM tech TO user (Reason: survey enable and technician switch ticket in state $rparameters[survey_ticket_state].)<br> ";}
		//check if survey answer already exist for this ticket
		$qry2 = $db->prepare("SELECT ticket_id FROM tsurvey_answers WHERE ticket_id=:ticket_id");
		$qry2->execute(array('ticket_id' => $_GET['id']));
		$row2=$qry2->fetch();
		$qry2->closeCursor();
		if(!$row2)
		{
			//insert a token
			$token=bin2hex(random_bytes(32)); 
			$qry=$db->prepare("INSERT INTO ttoken (`date`,`token`,`action`,`ticket_id`) VALUES (NOW(),:token,'survey',:ticket_id)");
			$qry->execute(array('token' => $token,'ticket_id' => $_GET['id']));
			//select sender address
			if($rparameters['mail_from_adr']=='')
			{
				//get tech mail 
				$qry = $db->prepare("SELECT mail FROM tusers WHERE id=(SELECT technician FROM tincidents WHERE id=:id)");
				$qry->execute(array('id' => $_GET['id']));
				$from_adr=$qry->fetch();
				$from_adr=$from_adr['mail'];
				$qry->closeCursor();
				
			} else {
				$from_adr=$rparameters['mail_from_adr'];
			}
			//add ticket link if ticket tag detected
			$rparameters['survey_mail_text']=str_replace("[ticket_link]","<a target=\"_blank\" href=\"$rparameters[server_url]/index.php?page=ticket&id=$_GET[id]\">$_GET[id]</a>", $rparameters['survey_mail_text']);
			
			if($rparameters['mail_from_adr']!='') {$from=$rparameters['mail_from_adr'];} else {$from=$from_adr;}
			$to=$usermail['mail'];
			$object=T_("Sondage concernant votre ticket n°").$_GET['id'];
			$message=$rparameters['survey_mail_text'].'
			<br />
			<a href="'.$rparameters['server_url'].'/survey.php?token='.$token.'">'.T_('Répondre au sondage').'</a>
			';
			$mail_auto=true;
			require('./core/message.php');
		}
	} else {
		//debug
		if($rparameters['debug']) {echo "<b>AUTO MAIL DETECT:</b> FROM tech TO user (Reason: survey enable and technician switch ticket in state $rparameters[survey_ticket_state].) but user have no mail, message not sent.<br> ";}
	}
}

//send mail to mail addresses on ticket type where ticket is open
if($rparameters['ticket_type'] && $rparameters['mail_auto_type'] && $rparameters['mail_from_adr'] && $mail_sended['open']=='' && $_POST['type'] && ($_POST['send'] || $_POST['modify'] || $_POST['quit'] || $_POST['close']))
{
	//check if type have mail adresses
	$qry=$db->prepare("SELECT `mail` FROM `ttypes` WHERE id=:id");
	$qry->execute(array('id' => $_POST['type']));
	$type=$qry->fetch();
	$qry->closeCursor();
	
	if(!empty($type['mail']))
	{
		//debug
		if($rparameters['debug']) {echo "<b>AUTO MAIL DETECT:</b> FROM system TO ticket type adresses (Reason: mail_auto_type enable and ticket is open.)<br> ";}
		$to=$type['mail'];
		//auto send open notification mail
		$send=1;
		$mail_auto=true;
		include('./core/mail.php');
		//insert mail table
		$qry=$db->prepare("INSERT INTO `tmails` (`incident`,`open`,`close`) VALUES (:ticket,'1','0')");
		$qry->execute(array('ticket' => $_GET['id']));
	} else {
		//debug
		if($rparameters['debug']) {echo "<b>AUTO MAIL DETECT:</b> FROM tech TO user (Reason: survey enable and technician switch ticket in state $rparameters[survey_ticket_state].) but user have no mail, message not sent.<br> ";}
	}
}
?>