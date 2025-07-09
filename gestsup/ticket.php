<?php
################################################################################
# @Name : ticket.php
# @Description : page to display create and edit ticket
# @call : dashboard
# @parameters : 
# @Author : Flox
# @Create : 07/01/2007
# @Update : 21/03/2024
# @Version : 3.2.49
################################################################################

//secure direct access
if(!isset($_GET['page'])) {echo 'ERROR : invalid access'; exit;}

//initialize variables 
if(!isset($userreg)) $userreg = ''; 
if(!isset($category)) $category = ''; 
if(!isset($subcat)) $subcat = ''; 
if(!isset($title)) $title = ''; 
if(!isset($date_hope)) $date_hope = ''; 
if(!isset($date_create)) $date_create = ''; 
if(!isset($state)) $state = ''; 
if(!isset($description)) $description = ''; 
if(!isset($resolution)) $resolution = ''; 
if(!isset($priority)) $priority = '';
if(!isset($percentage)) $percentage = '';
if(!isset($id)) $id = '';
if(!isset($id_in)) $id_in = '';
if(!isset($save)) $save = '';
if(!isset($techread)) $techread = '';
if(!isset($techread_date)) $techread_date = '';
if(!isset($userread)) $userread = '';
if(!isset($next)) $next = '';
if(!isset($previous)) $previous = '';
if(!isset($user)) $user = '';
if(!isset($down)) $down = '';
if(!isset($u_group)) $u_group = '';
if(!isset($t_group)) $t_group = '';
if(!isset($userid)) $userid = '';
if(!isset($u_service)) $u_service = '';
if(!isset($date_hope_error)) $date_hope_error = '';
if(!isset($selected_time)) $selected_time = '';

if(!isset($_POST['mail'])) $_POST['mail'] = '';
if(!isset($_POST['upload'])) $_POST['upload'] = '';
if(!isset($_POST['title'])) $_POST['title'] = '';
if(!isset($_POST['description'])) $_POST['description'] = '';
if(!isset($_POST['resolution'])) $_POST['resolution'] = '';
if(!isset($_POST['Submit'])) $_POST['Submit'] = '';
if(!isset($_POST['subcat'])) $_POST['subcat'] = '';
if(!isset($_POST['user'])) $_POST['user'] = '';
if(!isset($_POST['type'])) $_POST['type'] = '';
if(!isset($_POST['type_answer'])) $_POST['type_answer'] = '';
if(!isset($_POST['modify'])) $_POST['modify'] = '';
if(!isset($_POST['quit'])) $_POST['quit'] = '';
if(!isset($_POST['date_create'])) $_POST['date_create'] = '';
if(!isset($_POST['date_hope'])) $_POST['date_hope'] = '';
if(!isset($_POST['date_res'])) $_POST['date_res'] = '';
if(!isset($_POST['priority'])) $_POST['priority'] = '';
if(!isset($_POST['criticality'])) $_POST['criticality'] = '';
if(!isset($_POST['category'])) $_POST['category'] = '';
if(!isset($_POST['time'])) $_POST['time'] = '';
if(!isset($_POST['time_hope'])) $_POST['time_hope'] = '';
if(!isset($_POST['billable'])) $_POST['billable'] = '';
if(!isset($_POST['state'])) $_POST['state'] = '';
if(!isset($_POST['cancel'])) $_POST['cancel'] = '';
if(!isset($_POST['technician'])) $_POST['technician'] = '';
if(!isset($_POST['ticket_places'])) $_POST['ticket_places'] = '';
if(!isset($_POST['text2'])) $_POST['text2'] = '';
if(!isset($_POST['u_service'])) $_POST['u_service'] = '';
if(!isset($_POST['asset_id'])) $_POST['asset_id'] = '';
if(!isset($_POST['asset'])) $_POST['asset'] = '';
if(!isset($_POST['u_agency]'])) $_POST['u_agency]'] = '';
if(!isset($_POST['sender_service'])) $_POST['sender_service'] = '';
if(!isset($_POST['addcalendar'])) $_POST['addcalendar'] = '';
if(!isset($_POST['addevent'])) $_POST['addevent'] = '';
if(!isset($_POST['user_validation'])) $_POST['user_validation'] = '';
if(!isset($_POST['user_validation_date'])) $_POST['user_validation_date'] = '';

//secure excluded var init_post.php
$_POST['description']=htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');

$db_id=strip_tags($db->quote($_GET['id']));
$db_lock_thread=strip_tags($db->quote($_GET['lock_thread']));
$db_unlock_thread=strip_tags($db->quote($_GET['unlock_thread']));
$db_threadedit=strip_tags($db->quote($_GET['threadedit']));

$hide_button=0;

if(!isset($globalrow['technician'])) $globalrow['technician'] = '';
if(!isset($globalrow['time'])) $globalrow['time'] = '';

//core ticket actions
include('./core/ticket.php');

//defaults values for new tickets
if(!isset($globalrow['creator'])) $globalrow['creator'] = '0';
if(!isset($globalrow['t_group'])) $globalrow['t_group'] = '';
if(!isset($globalrow['u_group'])) $globalrow['u_group'] = '';
if(!isset($globalrow['category'])) $globalrow['category'] = '';
if(!isset($globalrow['subcat'])) $globalrow['subcat'] = '';
if(!isset($globalrow['title'])) $globalrow['title'] = '';
if(!isset($globalrow['description'])) $globalrow['description'] = '';
if(!isset($globalrow['date_create'])) $globalrow['date_create'] = date("Y-m-d").' '.date("H:i:s");
if(!isset($globalrow['date_hope'])) $globalrow['date_hope'] = '';
if(!isset($globalrow['date_res'])) $globalrow['date_res'] = '';
if(!isset($globalrow['time_hope'])) $globalrow['time_hope'] = '5';
if(!isset($globalrow['time'])) $globalrow['time'] = '';
if(!isset($globalrow['priority'])) $globalrow['priority'] = ''; 
if(!isset($globalrow['state'])) $globalrow['state'] = '1';
if(!isset($globalrow['type'])) $globalrow['type'] = '0';
if(!isset($globalrow['type_answer'])) $globalrow['type_answer'] = '0';
if(!isset($globalrow['place'])) $globalrow['place'] = '0';
if(!isset($globalrow['criticality'])) $globalrow['criticality'] = '0';
if(!isset($globalrow['u_service'])) $globalrow['u_service'] = '0';
if(!isset($globalrow['asset_id'])) $globalrow['asset_id'] = '';
if(!isset($globalrow['u_agency'])) $globalrow['u_agency'] = '0';
if(!isset($globalrow['sender_service'])) $globalrow['sender_service'] = '0';
if(!isset($globalrow['user_validation_date'])) $globalrow['user_validation_date'] = '';
if(!isset($globalrow['observer1'])) $globalrow['observer1'] = '0';
if(!isset($globalrow['observer2'])) $globalrow['observer2'] = '0';
if(!isset($globalrow['observer3'])) $globalrow['observer3'] = '0';
if(!isset($globalrow['billable'])) $globalrow['billable'] = '0';

//default values for tech and admin and super
if($_SESSION['profile_id']==4 || $_SESSION['profile_id']==0 || $_SESSION['profile_id']==3)
{
	if($globalrow['technician']==0 && $_GET['action']=='new') {$globalrow['technician']=$_SESSION['user_id'];} //auto select current technician on new tickets
	if(!isset($globalrow['user'])) $globalrow['user']=0;
} else {
	if(!isset($globalrow['technician'])) {$globalrow['technician']='';}
	if($globalrow['user']==0) {$globalrow['user']=$_SESSION['user_id'];}
}

//display ticket open message
if($rparameters['ticket_open_message'] && $rparameters['ticket_open_message_text'] && $_GET['action']=='new')
{
	echo '
	<div class="alert bgc-info-l4 border-none border-l-4 brc-info-tp1 radius-0 text-dark-tp2">
		<h5 class="alert-heading text-info-m1 font-bolder">
			<i class="fas fa-info-circle mr-1 mb-1"></i>
			Information
		</h5>
		'.nl2br($rparameters['ticket_open_message_text']).'
	</div>
	';
}

?>
<div style="overflow-x: auto;" class="card bcard shadow mt-2" id="card-1" draggable="false">
	<form class="form-horizontal" name="myform" id="myform" enctype="multipart/form-data" method="post" action="" onsubmit="loadVal();" >
		<div class="card-header">
			<h5 class="card-title">
				<i class="fa fa-ticket"><!----></i>
				<?php
					//display widget title
					if($_GET['action']=='new') {
						if($mobile){echo 'n°'.$_GET['id'].' ';} 
						else {echo T_('Ouverture du ticket').' n° '.$_GET['id'];}
					} else {
						if($mobile){echo 'n°'.$_GET['id'].' ';} 
						else {echo T_('Édition du ticket').' n° '.$_GET['id'].' '.$percentage.': '.$title;}
					}
					//display clock if alarm 
					$qry=$db->prepare("SELECT `date_start` FROM `tevents` WHERE incident=:incident AND disable='0' AND type='1'");
					$qry->execute(array('incident' => $_GET['id']));
					$alarm=$qry->fetch();
					$qry->closeCursor();
					if($alarm) {echo ' <i class="fa fa-bell text-warning" title="'.T_('Alarme activée le').' '.$alarm['date_start'].'" /><!----></i>';}
					//display calendar if planned
					$qry=$db->prepare("SELECT `date_start` FROM `tevents` WHERE incident=:incident AND disable='0' AND type='2'");
					$qry->execute(array('incident' => $_GET['id']));
					$plan=$qry->fetch();
					$qry->closeCursor();
					if($plan && !$mobile) echo '&nbsp;<a target="_blank" href="./index.php?page=calendar"><i class="fa fa-calendar text-info" title="'.T_('Ticket planifié dans le calendrier le').' '.$plan['date_start'].'" /><!----></i></a>';
					//display member of project
					if($rparameters['project']==1 && $rright['project'] && !$mobile)
					{
						//check if current ticket is a task of project
						$qry=$db->prepare("SELECT `tprojects`.`name`,`tprojects_task`.`project_id` FROM `tprojects_task`,`tprojects` WHERE `tprojects_task`.project_id=`tprojects`.id AND `tprojects_task`.ticket_id=:ticket_id");
						$qry->execute(array('ticket_id' => $_GET['id']));
						$row=$qry->fetch();
						$qry->closeCursor();
						if($row) echo '&nbsp;<a target="_blank" href="./index.php?page=project"><i class="fa fa-tasks text-purple" title="'.T_('Le ticket est une tâche du projet').' '.$row['name'].'" /><!----></i></a>';
					}
				?>
			</h5>
			<span class="card-toolbar">
				<?php 
					if($rparameters['asset']==1 && $rparameters['asset_vnc_link']==1 && $_POST['user'] ){
						//check if user have asset with IP
						$qry=$db->prepare("SELECT `tassets_iface`.`ip` FROM `tassets_iface`,`tassets` WHERE tassets_iface.asset_id=tassets.id AND user=:user");
						$qry->execute(array('user' => $_POST['user']));
						$row=$qry->fetch();
						$qry->closeCursor();
						if($row) {echo '<a target="_blank" href="http://'.$row['ip'].':5800"><img title="'.T_('Ouvre un nouvel onglet sur le prise de contrôle distant web VNC').'" src="./images/remote.png" /></a>&nbsp;&nbsp;';}
					}
					if($rright['ticket_next']!=0 && !$mobile && $_GET['action']!='new')
					{
						if($previous[0]) echo'<a style="vertical-align:middle;" href="./index.php?page=ticket&amp;id='.$previous[0].'&amp;state='.$globalrow['state'].'&amp;userid='.$_GET['userid'].'"><i title="'.T_('Ticket précédent de cet état').'" class="fa fa-arrow-circle-left text-130 text-primary-m2 mr-1"><!----></i></a>'; 
						if($next[0]) echo'<a style="vertical-align:middle;" href="./index.php?page=ticket&amp;id='.$next[0].'&amp;state='.$globalrow['state'].'&amp;userid='.$_GET['userid'].' "><i title="'.T_('Ticket suivant de cet état').'" class="fa fa-arrow-circle-right text-130 text-primary-m2 mr-1"><!----></i></a>';
					}
					if($rright['ticket_print'] && $_GET['action']!='new')
					{
						echo '
						<a style="width:31px; height:27px; padding-left:6px;" class="btn btn-xs btn-default" target="_blank" onClick="parentNode.submit();" href="ticket_print.php?id='.$_GET['id'].'&amp;user_id='.$_SESSION['user_id'].'&amp;token='.$token.'">
							<i title="'.T_('Imprimer ce ticket').'" class="fa fa-print text-130"><!----></i>
						</a>&nbsp;';
					}
					if($rright['ticket_template']!=0 && $_GET['action']=='new')
					{
						//check if template exist
						$qry=$db->prepare("SELECT `id` FROM `ttemplates`");
						$qry->execute();
						$template=$qry->fetch();
						$qry->closeCursor();
						if(!empty($template['id']))
						{
							echo '<button type="button" style="width:31px; height:27px; padding:6px;" class="btn btn-xs btn-pink" title="'.T_('Modèle de tickets').'" data-toggle="modal" data-target="#template" ><i class="fa fa-tags text-110"><!----></i></button>&nbsp;';
						}
					}
					if($rright['planning'] && $rparameters['planning'] && $rright['ticket_event'] && $_GET['action']!='new')
					{
						echo '<button type="button" style="width:31px; height:27px; padding-top:5px; margin-right:1px;" class="btn btn-xs btn-warning" title="'.T_('Créer un rappel pour ce ticket').'" data-toggle="modal" data-target="#add_event" ><i style="color: #fff;" class="fa fa-bell text-120"><!----></i></button>&nbsp;';
					}
					if($rright['planning'] && $rparameters['planning'] && $rright['ticket_calendar'] && $_GET['action']!='new') 
					{
						echo '<button type="button" style="width:31px; height:27px; padding-left:6px;  padding-top:5px;" class="btn btn-xs btn-info" title="'.T_('Planifier une intervention dans le calendrier').'" data-toggle="modal" data-target="#add_planification" ><i class="fa fa-calendar text-120"><!----></i></button>&nbsp;';
					}
					if($rright['ticket_fusion'] && $_GET['action']!='new')
					{
						echo '<button type="button" style="width:31px; height:27px; padding:5px; " class="btn btn-xs btn-grey" title="'.T_('Fusionner ce ticket').'" data-toggle="modal" data-target="#fusion" ><i class="fa fa-sitemap text-110"><!----></i></button>&nbsp;';
					}
					if($rright['ticket_delete'] && $_GET['action']!='new')
					{
						//replace % value in URL by %25
						if($_GET['userid']=='%') {$get_user_id='%25';} else {$get_user_id=$_GET['userid'];}
						if($_GET['state']=='%') {$get_state='%25';} else {$get_state=$_GET['state'];}
						echo '<a style="width:31px; height:27px; padding-top:5px;" class="btn btn-xs btn-danger" onClick="javascript: return confirm(\''.T_('Êtes-vous sur de vouloir supprimer ce ticket ? les données et les pièces jointes seront définitivement supprimées').'\');" href="./index.php?page=ticket&amp;id='.$_GET['id'].'&amp;userid='.$get_user_id.'&amp;state='.$get_state.'&amp;action=delete"  title="'.T_('Supprimer ce ticket').'" ><i class="fa fa-trash text-120"><!----></i></a>&nbsp;';
					}
					if($rright['ticket_save'] && !$hide_button)
					{
						echo '
						<div id="header_save_button">
							<button style="width:31px; height:27px;" class="btn btn-xs btn-success" title="'.T_('Enregistrer').'" name="modify" value="submit" type="submit" id="modify_btn"><i class="fa fa-save text-130"><!----></i></button>
							<button style="width:31px; height:27px;" class="btn btn-xs btn-purple" title="'.T_('Enregistrer et quitter').'" name="quit" value="quit" type="submit" id="quit_btn"><i class="fa fa-save text-130"><!----></i></button>
						</div>
						';
					}
					?>
			</span>
		</div>
		<div class="card-body p-0">
			<div class="p-3">
				<!-- START sender part -->	
				<div class="form-group row <?php if((!$rright['ticket_user_disp'] && $_GET['action']!='new') || (!$rright['ticket_new_user_disp'] && $_GET['action']=='new')) {echo 'd-none';} ?>" >
					<div class="col-sm-2 col-form-label text-sm-right pr-0">
						<label class="mb-0" for="user">
							<?php 
								if(($_POST['user']==0) && ($globalrow['user']==0) && ($u_group=='')) echo '<i id="user_warning" title="'.T_('Sélectionner un demandeur').'" class="fa fa-exclamation-triangle text-danger-m2 text-130"><!----></i>&nbsp;'; 
								echo T_('Demandeur').' :'; 
							?>
						</label>
					</div>
					<div class="col-sm-9">
						<!-- START sender list part -->
						<select autofocus onchange="loadVal(); <?php if($rright['ticket_user_mandatory']) {echo 'CheckMandatory();';} ?>" id="user" name="user" <?php if(($rright['ticket_user']==0 && $_GET['action']!='new') || ($rright['ticket_new_user']==0 && $_GET['action']=='new') || $ticket_observer) {echo ' disabled="disabled" ';} if($rright['ticket_user_mandatory']) {echo ' required ';} ?>>
							<?php
							//define order of user list in case with company prefix
							if($rright['ticket_user_company']!=0)
							{
								$qry=$db->prepare("SELECT tusers.id,tusers.company,tusers.firstname,tusers.lastname,tusers.mail,tusers.mobile,tusers.disable,tcompany.name AS user_company FROM `tusers`, `tcompany` WHERE tusers.company=tcompany.id AND (tusers.lastname!='' OR tusers.firstname!='') ORDER BY tcompany.name, tusers.lastname");
							} else {
								$qry=$db->prepare("SELECT id,company,firstname,lastname,mail,mobile,disable FROM `tusers` WHERE (lastname!='' OR firstname!='')  ORDER BY lastname ASC, firstname ASC");
							}
							//display user list and keep selected an disable user
							$qry->execute();
							while($row=$qry->fetch())
							{	
								$user_mail_mobile='';
								if($rright['ticket_user_company']!=0 && $row['company']!=0){$user_company='['.$row['user_company'].'] ';} else {$user_company='';}
								if($rright['ticket_user_mail'] && $row['mail'] && !$rright['ticket_user_mobile']){$user_mail_mobile=' ('.$row['mail'].')';} else {$user_mail_mobile.='';}
								if($rright['ticket_user_mobile'] && $row['mobile'] && !$rright['ticket_user_mail']){$user_mail_mobile=' ('.$row['mobile'].')';} else {$user_mail_mobile.='';}
								if($rright['ticket_user_mobile'] && $rright['ticket_user_mail'] && $row['mobile'] && $row['mail']){$user_mail_mobile=' ('.$row['mobile'].' - '.$row['mail'].')';} else {$user_mail_mobile.='';}
								if($rright['ticket_user_mobile'] && $rright['ticket_user_mail'] && $row['mobile'] && !$row['mail']){$user_mail_mobile=' ('.$row['mobile'].')';} else {$user_mail_mobile.='';}
								if($rright['ticket_user_mobile'] && $rright['ticket_user_mail'] && !$row['mobile'] && $row['mail']){$user_mail_mobile=' ('.$row['mail'].')';} else {$user_mail_mobile.='';}
								if($_POST['user']==$row['id']) {$selected='selected';} elseif(($_POST['user']=='') && ($globalrow['user']==$row['id'])) {$selected='selected';} else {$selected='';} 
								if($row['id']==0) {echo '<option '.$selected.' value="">'.T_("$row[lastname]").'</option>';} //case no user
								if($row['disable']==0) {echo '<option '.$selected.' value="'.$row['id'].'">'.$user_company.$row['lastname'].' '.$row['firstname'].$user_mail_mobile.'</option>';} //all enable users and technician
								if(($row['disable']==1) && ($selected=='selected') && $row['id']!=0) {echo '<option '.$selected.' value="'.$row['id'].'">'.$row['lastname'].' '.$row['firstname'].'</option>';} //case disable user always attached to this ticket
							}
							$qry->closeCursor(); 
							//display group list and keep selected an disable group
							$qry=$db->prepare("SELECT `id`,`name`,`disable` FROM `tgroups` WHERE `type`='0' ORDER BY `name`");
							$qry->execute();
							while($row=$qry->fetch()) 
							{
								if($row['id']==$u_group) {$selected='selected';} else {$selected='';}
								if($row['disable']==0) {echo '<option '.$selected.' value="G_'.$row['id'].'">[G] '.T_(" $row[name]").'</option>';}
								if(($row['disable']==1) && ($selected=='selected')) {echo '<option '.$selected.' value="G_'.$row['id'].'">[G] '.$row['name'].'</option>';}
							}
							$qry->closeCursor();
							?>
						</select>
						
						<?php 
							//send data in disabled case
							if((!$rright['ticket_user'] && $_GET['action']!='new') || (!$rright['ticket_new_user'] && $_GET['action']=='new')) {echo ' <input type="hidden" name="user" value='.$globalrow['user'].' /> ';} 
						?>
						<!-- END sender list part -->
						<!-- START sender actions part -->
						<?php
						if($rright['ticket_user_actions'])
						{
							echo '<span class="d-inline-block">';
								echo'<input type="hidden" name="action" value="">';
								echo'<input type="hidden" name="edituser" value="">';
								echo '
								<button title="'.T_('Ajouter un utilisateur').'" style="background: none; padding: 0px; border: none;" type="button" id="user_add_modal" name="user_add_modal" data-toggle="modal" data-target="#user_add_modal" >
									<i class="fa fa-plus-circle text-success text-130 mr-2 ml-1"><!----></i>
								</button>
								';
								if(!$u_group)
								{
									if($_POST['user']) {$selecteduser=$_POST['user'];} else {$selecteduser=$globalrow['user'];}
									echo '
									<button title="'.T_('Modifier un utilisateur').'" style="background: none; padding: 0px; border: none;" type="button" name="edit_user_btn" id="edit_user_btn" data-toggle="modal" data-target="#user_modify_modal"">
										<i class="fa fa-pencil-alt text-warning text-130 mr-2 ml-1"><!----></i>
									</button>
									';
								}
							echo '</span>';
						}	
						?>
						<!-- END sender actions part -->
						<!-- START user info part -->
						<?php
							if(!$mobile || $rright['ticket_user_info_mobile'])
							{
								//data get by ajax script refer /includes/
								echo '
								<span style="font-size:15px;">
									<span id="user_phone"></span>
									<span id="user_mobile"></span>
									<span id="user_mail"></span>
									<span id="user_function"></span>
									<span id="user_service"></span>
									<span id="user_agency"></span>
									<span id="user_company_name"></span>
									<span id="user_company_comment"></span>
									<span id="user_other_ticket"></span>
									<span id="user_asset"></span>
									<span id="user_ticket_remaining"></span>
									<span id="user_hour_remaining"></span>
									<span id="user_custom1"></span>
									<span id="user_custom2"></span>
								</span>
								';
							}
						?>
						<!-- START user info part -->
					</div>
				</div>
				<!-- END sender part -->
				
				<!-- START destination service part -->
				<?php
					if($rright['ticket_service_disp']!=0)
					{
						echo'
						<div class="form-group row '; if($rright['ticket_new_service_disp']==0 && $_GET['action']=='new') {echo 'd-none';} echo '" >
							<div class="col-sm-2 col-form-label text-sm-right pr-0">
								<label class="mb-0" for="u_service">'.T_('Service').' :</label>
							</div>
							<div class="col-sm-5">
								<select style="min-width:269px;" class="form-control col-5" id="u_service" name="u_service" '; if(($rright['ticket_service']==0 && $_GET['action']!='new') || ($rright['ticket_new_service']==0 && $_GET['action']=='new') || $ticket_observer) {echo ' disabled="disabled" ';} if($rright['ticket_service_mandatory']!=0) {echo ' required="required" ';}  echo' onchange="loadVal(); submit();">
									';
									if($_POST['u_service'])
									{
										echo '<option value="">Aucun</option>';
										$qry2=$db->prepare("SELECT `id`,`name` FROM `tservices` WHERE id=:id");
										$qry2->execute(array('id' => $_POST['u_service']));
										$row2=$qry2->fetch();
										$qry2->closeCursor();
										echo '<option value="'.$_POST['u_service'].'" selected >'.T_($row2['name']).'</option>';
										$qry2=$db->prepare("SELECT `id`,`name` FROM `tservices` WHERE id!=:id AND disable='0' ORDER BY id!=0, name");
										$qry2->execute(array('id' => $_POST['u_service']));
										while($row2=$qry2->fetch()) {echo '<option value="'.$row2['id'].'">'.T_($row2['name']).'</option>';}
										$qry2->closeCursor();
									}
									else
									{
										echo '<option value=""></option>';
										$qry2=$db->prepare("SELECT `id`,`name` FROM `tservices` WHERE id=:id AND id!='0' ORDER BY id");
										$qry2->execute(array('id' => $globalrow['u_service']));
										$row2=$qry2->fetch();
										$qry2->closeCursor();
										if(!empty($row2)) {echo '<option value="'.$globalrow['u_service'].'" selected >'.T_($row2['name']).'</option>';}
										$qry2=$db->prepare("SELECT `id`,`name` FROM `tservices` WHERE id!=:id AND disable='0' AND id!='0' ORDER BY name");
										$qry2->execute(array('id' => $globalrow['u_service']));
										while($row2=$qry2->fetch()) {echo '<option value="'.$row2['id'].'">'.T_($row2['name']).'</option>';}
										$qry2->closeCursor();
									}
									echo'			
								</select>
								';
								//send data in disabled case
								if($rright['ticket_service']==0 && $_POST['u_service']==0 && $globalrow['u_service']!=0) echo '<input type="hidden" name="u_service" value="'.$globalrow['u_service'].'" />'; 
								echo '
							</div>
						</div>
						';
					} else {
						//send data not disp case
						echo '<input type="hidden" name="u_service" value="'.$globalrow['u_service'].'" />';
					}
				?>
				<!-- END destination service part -->

				<!-- START type part -->
			   <?php
					if($rparameters['ticket_type']==1)
					{
						echo'
						<div class="form-group row '; if((!$rright['ticket_type_disp'] && $_GET['action']!='new') || (!$rright['ticket_new_type_disp'] && $_GET['action']=='new')) {echo 'd-none';} echo'">
							<div class="col-sm-2 col-form-label text-sm-right pr-0">
								<label class="mb-0" for="type">
									'.T_('Type').' :
								</label>
							</div>
							<div class="col-sm-5">
								<select style="min-width:269px;" id="type" name="type" class="form-control col-5" ';if($rparameters['user_validation'] && $rparameters['user_validation_perimeter']=='mark' && $rright['ticket_user_validation']) { echo ' onchange="loadVal(); submit();" ';} if($rright['ticket_type_mandatory']) { echo ' onchange="CheckMandatory();" ';} if(($rright['ticket_type']==0 && $_GET['action']!='new') || ($rright['ticket_new_type']==0 && $_GET['action']=='new') || $ticket_observer) {echo 'disabled="disabled"';} echo' >';
									//limit service type
									if($rparameters['user_limit_service']==1 && $rright['ticket_type_service_limit']!=0)
									{
										if($rright['ticket_service_disp'] || $rright['ticket_new_service_disp']) //case service field display
										{
											if($_POST['u_service']) 
											{$where=' service=\''.$_POST['u_service'].'\' ';} else {$where=' service=\''.$globalrow['u_service'].'\' ';}
										} else { //case no service field display
											if($cnt_service==1){$where=' service='.$user_services['0'].' OR service=0 OR id=0 ';}
											elseif($cnt_service>1) //multi services case
											{
												$where='';
												foreach($user_services as $user_service) {$where.=" service='$user_service' OR ";}
												$where.='service=0';
											} else {$where='1=1';}
										}
										$old_type=1;
										$query2 = $db->query("SELECT `id`,`name` FROM `ttypes` WHERE $where OR `id`=0 ORDER BY `id`=0 DESC,`name`");
										while ($row2 = $query2->fetch()) {
											//select entry
											$selected='';
											if($_POST['type']!='' && $row2['id']==$_POST['type']) 
											{$selected='selected';}
											elseif($globalrow['type'] && $row2['id']==$globalrow['type'] && $_POST['type']=='') 
											{$selected='selected';}
											if($globalrow['type']==$row2['id']) {$old_type=0;}
											echo '<option '.$selected.' value="'.$row2['id'].'">'.T_($row2['name']).'</option>';
										}
										$query2->closeCursor(); 
										//keep old data
										if($old_type==1 && $_GET['action']!='new') {
											$qry2=$db->prepare("SELECT `id`,`name` FROM `ttypes` WHERE `id`=:id");
											$qry2->execute(array('id' => $globalrow['type']));
											$row2=$qry2->fetch();
											$qry2->closeCursor();
											echo '<option selected value="'.$row2['id'].'">'.T_($row2['name']).'</option>';
										}
									} else {
										if($_POST['type']!='')
										{
											$qry2=$db->prepare("SELECT `id`,`name` FROM `ttypes` WHERE `id`=:id");
											$qry2->execute(array('id' => $_POST['type']));
											$row2=$qry2->fetch();
											$qry2->closeCursor();
											echo '<option value="'.$_POST['type'].'" selected >'.T_($row2['name']).'</option>';
											$qry2=$db->prepare("SELECT `id`,`name` FROM `ttypes` WHERE `id`!=:id ORDER BY `name`");
											$qry2->execute(array('id' => $_POST['type']));
											while($row2=$qry2->fetch()) {echo '<option value="'.$row2['id'].'">'.T_($row2['name']).'</option>';}
											$qry2->closeCursor();
										}
										else
										{
											$qry2=$db->prepare("SELECT `id`,`name` FROM `ttypes` WHERE `id`=:id ");
											$qry2->execute(array('id' => $globalrow['type']));
											$row2=$qry2->fetch();
											$qry2->closeCursor();
											if(!empty($row2['name'])) {echo '<option value="'.$globalrow['type'].'" selected >'.T_($row2['name']).'</option>';}
											$qry2=$db->prepare("SELECT `id`,`name` FROM `ttypes` WHERE  `id`!=:id ORDER BY `name`");
											$qry2->execute(array('id' => $globalrow['type']));
											while($row2=$qry2->fetch()) {echo '<option value="'.$row2['id'].'">'.T_($row2['name']).'</option>';}
											$qry2->closeCursor();
										}
									}
									echo'			
								</select>
								';
								//send data in disabled case
								if($rright['ticket_type']==0 && $_GET['action']!='new') echo '<input type="hidden" name="type" value="'.$globalrow['type'].'" />'; 
								echo '
							</div>
						</div>
						';
					}
				?>
				<!-- END type part -->	

				<!-- START type answer part -->
				<?php
					if($rright['ticket_type_answer_disp'])
					{
						echo'
						<div class="form-group row">
							<div class="col-sm-2 col-form-label text-sm-right pr-0">
								<label class="mb-0" for="type_answer">'.T_('Type de réponse').' :</label>
							</div>
							<div class="col-sm-5">
								<select class="form-control col-5" name="type_answer">';
									if($_POST['type_answer'])
									{
										$qry2=$db->prepare("SELECT `id`,`name` FROM `ttypes_answer` WHERE id=:id");
										$qry2->execute(array('id' => $_POST['type_answer']));
										$row2=$qry2->fetch();
										$qry2->closeCursor();
										if(empty($row2['name'])) {$row2['name']='';}
										echo '<option value="'.$_POST['type_answer'].'" selected >'.T_($row2['name']).'</option>';
										$qry2=$db->prepare("SELECT `id`,`name` FROM `ttypes_answer` WHERE id!=:id ORDER BY name");
										$qry2->execute(array('id' => $_POST['type_answer']));
										while($row2=$qry2->fetch()) {echo '<option value="'.$row2['id'].'">'.T_($row2['name']).'</option>';}
										$qry2->closeCursor();
									}
									else
									{
										$qry2=$db->prepare("SELECT `id`,`name` FROM `ttypes_answer` WHERE id=:id ORDER BY name");
										$qry2->execute(array('id' => $globalrow['type_answer']));
										$row2=$qry2->fetch();
										$qry2->closeCursor();
										if(empty($row2['name'])) {$row2['name']='';}
										echo '<option value="'.$globalrow['type_answer'].'" selected >'.T_($row2['name']).'</option>';
										$qry2=$db->prepare("SELECT `id`,`name` FROM `ttypes_answer` WHERE  id!=:id ORDER BY name");
										$qry2->execute(array('id' => $globalrow['type_answer']));
										while($row2=$qry2->fetch()) {echo '<option value="'.$row2['id'].'">'.T_($row2['name']).'</option>';}
										$qry2->closeCursor();
									}
									echo'			
								</select>
							</div>
						</div>
						';
					} else {
						echo '<input type="hidden" name="type_answer" value="'.$globalrow['type_answer'].'" />';
					}
				?>
				<!-- END type answer part -->

				<!-- START observer part -->
				<?php
					if($rparameters['ticket_observer'] && $rright['ticket_observer_disp'])
					{
						if($rright['ticket_observer_disp'] && !$rright['ticket_observer'] && $_GET['action']=='new')
						{
							//hide field when open ticket
						} else {
							echo '
							<div class="form-group row">
								<div class="col-sm-2 col-form-label text-sm-right pr-0">
									<label class="mb-0" for="observer">'.T_('Observateur').' :</label>
								</div>
								<div class="col-sm-5">
									';
									//fetch current data from database
									if($_POST['observer1']=='' && $globalrow['observer1']) {$_POST['observer1']=$globalrow['observer1'];}
									if($_POST['observer2']=='' && $globalrow['observer2']) {$_POST['observer2']=$globalrow['observer2'];}
									if($_POST['observer3']=='' && $globalrow['observer3']) {$_POST['observer3']=$globalrow['observer3'];}

									if($rright['ticket_observer'])
									{
										//delete observer
										if($_POST['delete_observer1']) {$_POST['observer1']=0;}
										if($_POST['delete_observer2']) {$_POST['observer2']=0;}
										if($_POST['delete_observer3']) {$_POST['observer3']=0;}

										//find observer free slot
										if($_POST['observer'])
										{
											if(!$_POST['observer1']) {$_POST['observer1']=$_POST['observer'];}
											elseif(!$_POST['observer2']) {$_POST['observer2']=$_POST['observer'];}
											elseif(!$_POST['observer3']) {$_POST['observer3']=$_POST['observer'];}
										}

										if($_POST['observer1'] && $_POST['observer2'] && $_POST['observer3'])
										{
											//hide select if all slot are full
										} else {
											echo '
											<select style="width:269px;" id="observer" name="observer" >
												';
												$qry="SELECT `id`,`lastname`,`firstname` FROM `tusers` WHERE `disable`='0' OR `id`='0' ORDER BY `id`!=0, `lastname` ASC, `firstname` ASC";
												$qry=$db->prepare($qry);
												$qry->execute();
												while($user=$qry->fetch()) 
												{
													if(($globalrow['observer1']!=$user['id']) || $user['id']=='0')
													{
														if($user['id']==0) {echo '<option '.$selected.' value="">'.T_("$user[lastname]").'</option>';} //case no user

														echo '<option value="'.$user['id'].'">'.T_($user['lastname']).' '.$user['firstname'].'</option>';
													}
												}
												$qry->closeCursor();
												echo '
											</select>
											';
											//display add button
											echo '
											<button style="background: none; padding: 0px; border: none;" type="submit" id="add_observer" name="add_observer">
												<i class="fa fa-plus-circle text-success text-130 mr-2 ml-1" title="Ajouter un observateur"><!----></i>
											</button>';
										}
									}
									if($_POST['observer1'] && $_POST['observer2'] && $_POST['observer3']) {echo'<div class="mt-2" >';}

									//hidden observer field
									echo '<input type="hidden" name="observer1" value="'.$_POST['observer1'].'" />';
									echo '<input type="hidden" name="observer2" value="'.$_POST['observer2'].'" />';
									echo '<input type="hidden" name="observer3" value="'.$_POST['observer3'].'" />';

									//list current observer
									if($_POST['observer1'])
									{
										$qry=$db->prepare("SELECT `firstname`,`lastname` FROM `tusers` WHERE id=:id");
										$qry->execute(array('id' => $_POST['observer1']));
										$observer=$qry->fetch();
										$qry->closeCursor();
										echo $observer['firstname'].' '.$observer['lastname'].' ';
										if($rright['ticket_observer']) {echo '<button style="background:none; padding:0px; border:none;" type="submit" value="delete_observer1" id="delete_observer1" name="delete_observer1"><a title="'.T_('Supprimer cet observateur').'" <i class="fa fa-trash text-danger text-130 ml-1 mr-1" title="'.T_('Supprimer cet observateur').'"><!----></i></a></button>';}
									}
									if($_POST['observer2'])
									{
										$qry=$db->prepare("SELECT `firstname`,`lastname` FROM `tusers` WHERE id=:id");
										$qry->execute(array('id' => $_POST['observer2']));
										$observer=$qry->fetch();
										$qry->closeCursor();
										echo $observer['firstname'].' '.$observer['lastname'].' ';;
										if($rright['ticket_observer']) {echo '<button style="background:none; padding:0px; border:none;" type="submit" value="delete_observer2" id="delete_observer2" name="delete_observer2"><a title="'.T_('Supprimer cet observateur').'" <i class="fa fa-trash text-danger text-130 ml-1 mr-1" title="'.T_('Supprimer cet observateur').'"><!----></i></a></button>';}
									}
									if($_POST['observer3'])
									{
										$qry=$db->prepare("SELECT `firstname`,`lastname` FROM `tusers` WHERE id=:id");
										$qry->execute(array('id' => $_POST['observer3']));
										$observer=$qry->fetch();
										$qry->closeCursor();
										echo $observer['firstname'].' '.$observer['lastname'];
										if($rright['ticket_observer']) {echo '<button style="background:none; padding:0px; border:none;" type="submit" value="delete_observer3" id="delete_observer3" name="delete_observer3"><a title="'.T_('Supprimer cet observateur').'" <i class="fa fa-trash text-danger text-130 ml-1 mr-1" title="'.T_('Supprimer cet observateur').'"><!----></i></a></button>';}
									}
									if($_POST['observer1'] && $_POST['observer2'] && $_POST['observer3']) {echo'</div>';}
									echo '
								</div>
							</div>
							';
						}
					}
				?>
				<!-- END observer part -->	

				<!-- START technician part -->
				<?php
				//lock technician field if technician open ticket for another service and limit service is enable
				if($rparameters['user_limit_service']==1 && $rright['ticket_tech_service_lock']!=0)
				{
					if($_SESSION['profile_id']==0 || $_SESSION['profile_id']==3) //for technician and supervisor
					{
						//check if current technician or supervisor is member of selected service
						if(($_POST['u_service'] && $_POST['u_service']!=0 && $_GET['action']=='new') || ($_GET['action']!='new' && $globalrow['u_service']!=0) && $user_services)
						{
							if($_GET['action']=='new') {$chk_svc=$_POST['u_service'];} else {$chk_svc=$globalrow['u_service'];}
							$check_tech_svc=0;
							foreach($user_services as $value) {if($chk_svc==$value){$check_tech_svc=1;}}
							if($check_tech_svc==0) {$lock_tech=1;} else {$lock_tech=0;}
						} else {$lock_tech=0;}
					} else {$lock_tech=0;}
				} else {$lock_tech=0;}
				?>
				<div class="form-group row <?php if(($rright['ticket_tech_disp']==0 && $_GET['action']!='new') || ($rright['ticket_new_tech_disp']==0 && $_GET['action']=='new')) {echo 'd-none';} ?>">
					<div class="col-sm-2 col-form-label text-sm-right pr-0">
						<label class="mb-0" for="technician">
						<?php 
							if($lock_tech==0) //case lock field 
							{
								if(($_POST['technician']==0 && $_POST['technician']!='') || ($_POST['technician']=='' && $globalrow['technician']==0) && $globalrow['t_group']==0) 
								{
									echo '<i id="technician_warning" title="'.T_('Aucun technicien associé à ce ticket').'" class="fa fa-exclamation-triangle text-danger-m2 text-130"><!----></i>&nbsp;';
								}
							}
							echo T_('Technicien').' :'; 
						?>
						</label>
					</div>
					<div class="col-sm-5">
						<select style="min-width:269px;" class="form-control col-5 d-inline-block" id="technician" name="technician" onchange="loadVal();  <?php if($rright['ticket_tech_mandatory']) {echo ' CheckMandatory(); ';}?>" <?php if($rright['ticket_tech']==0 || $lock_tech==1 ) {echo ' disabled="disabled" ';}?> >
							<?php
							//add service filter to technician list
							if($rparameters['user_limit_service'] && $rright['dashboard_service_only']) //case for user who open ticket to auto-select categories of the service
							{
								if($_POST['u_service']) {$where_service=$_POST['u_service'];} else {$where_service=$globalrow['u_service'];}
								
								if($rright['ticket_tech_super'] && $rright['ticket_tech_admin']) { //display supervisor and admin in technician list
									$query="SELECT `id`,`lastname`,`firstname`,`disable` FROM `tusers` WHERE (`profile`='0' || `profile`='4' || `profile`='3') AND ( `id` IN (SELECT `user_id` FROM `tusers_services` WHERE `service_id`=$where_service)) OR `id`='0' ORDER BY `lastname` ASC, `firstname` ASC";
								} elseif($rright['ticket_tech_super']) //display supervisor in technician list
								{
									$query="SELECT `id`,`lastname`,`firstname`,`disable` FROM `tusers` WHERE (`profile`='0' || `profile`='3') AND ( `id` IN (SELECT `user_id` FROM `tusers_services` WHERE `service_id`=$where_service)) OR `id`='0' ORDER BY `id`!=0, `lastname` ASC, `firstname` ASC";
								} elseif($rright['ticket_tech_admin'])  { //display technician and admin in technician list
									$query="SELECT `id`,`lastname`,`firstname`,`disable` FROM `tusers` WHERE (`profile`='0' || `profile`='4') AND ( `id` IN (SELECT `user_id` FROM `tusers_services` WHERE `service_id`=$where_service)) OR `id`='0' ORDER BY `id`!=0, `lastname` ASC, `firstname` ASC";
								} else { //display only technician in technician list
									#6325
									$query="SELECT `id`,`lastname`,`firstname`,`disable` FROM `tusers` WHERE `id`=$globalrow[technician]";
									/*
									//check selected service is connected user service #6325
									$qry=$db->prepare("SELECT `id` FROM `tusers_services` WHERE user_id=:user_id AND service_id=:service_id");
									$qry->execute(array('user_id' => $_SESSION['user_id'],'service_id' => $globalrow['u_service']));
									$user_service=$qry->fetch();
									$qry->closeCursor();
									if($rright['side_all_service_edit'] && $globalrow['technician'] && empty($user_service[0])) #case supervisor modify ticket with technicien of another service #6325
									{
										$query="SELECT `id`,`lastname`,`firstname`,`disable` FROM `tusers` WHERE `id`=$globalrow[technician]";
									}else {
										$query="SELECT `id`,`lastname`,`firstname`,`disable` FROM `tusers` WHERE `profile`='0' AND ( `id` IN (SELECT `user_id` FROM `tusers_services` WHERE `service_id`=$where_service)) OR `id`='0' ORDER BY `id`!=0, `lastname` ASC, `firstname` ASC";
									}
									*/
								}
								//display technician groups
								$query2="SELECT `id`,`name`,`disable` FROM `tgroups` WHERE `type`='1' AND `service`=$where_service ORDER BY `name`";
							} else {
								//display technician and admin in technician list
								 if($rright['ticket_tech_super']!=0 && $rright['ticket_tech_admin']!=0) { // supervisor, admin, technician
									$query = "SELECT `id`,`lastname`,`firstname`,`disable` FROM `tusers` WHERE (`profile`='0' || `profile`='4' || `profile`='3') OR `id`=0 ORDER BY `lastname` ASC, `firstname` ASC" ;
								} elseif($rright['ticket_tech_super']!=0)
								{
									$query = "SELECT `id`,`lastname`,`firstname`,`disable` FROM `tusers` WHERE (`profile`='0' || `profile`='3') OR id=0 ORDER BY `id`!=0, `lastname` ASC, `firstname` ASC" ;
								} elseif($rright['ticket_tech_admin']!=0)
								{
									$query = "SELECT `id`,`lastname`,`firstname`,`disable` FROM `tusers` WHERE (`profile`='0' || `profile`='4') OR `id`=0 ORDER BY `id`!=0, `lastname` ASC, `firstname` ASC" ;
								} else {
									$query="SELECT `id`,`lastname`,`firstname`,`disable` FROM `tusers` WHERE `profile`='0' OR `id`='0' ORDER BY `id`!=0, `lastname` ASC, `firstname` ASC";
								}
								//display technician groups
								$query2="SELECT `id`,`name`,`disable` FROM `tgroups` WHERE `type`='1' ORDER BY `name`";
							}
							
							//display technician list
							if($rparameters['debug']) {echo $query;}
							$query = $db->query($query);
							$tech_selected='0';
							while ($row = $query->fetch()) 
							{
								//select technician
								if($_POST['technician']==$row['id']) {
									$selected="selected";
									$tech_selected=$row['id'];
								} elseif(($_POST['technician']=='') && ($globalrow['technician']==$row['id']) && $selected=='') {
									$selected="selected";
									$tech_selected=$row['id'];
								} else {
									$selected='';
								}
								//display each entry
								if($row['id']==0) {echo '<option '.$selected.' value="'.$row['id'].'">'.T_($row['lastname']).' '.$row['firstname'].'</option>';} //case no technician TEMP 3.1.20 && (($_POST['technician']==0) && ($globalrow['technician']!=$row['id']))
								if($row['disable']==0) {echo '<option '.$selected.' value="'.$row['id'].'">'.$row['lastname'].' '.$row['firstname'].'</option>';} //all enable technician
								if(($row['disable']==1) && ($selected=='selected') && $row['id']!=0) {echo '<option '.$selected.' value="'.$row['id'].'">'.$row['lastname'].' '.$row['firstname'].'</option>';} //case disable technician always attached to this ticket
							} 
							$query->closeCursor();
							
							//display technician group list
							$query2 = $db->query($query2);
							while ($row = $query2->fetch()) {
								if($row['id']==$t_group) {$selected='selected';} else {$selected='';}
								if(!$row['disable']) {echo '<option '.$selected.' value="G_'.$row['id'].'">[G] '.T_($row['name']).'</option>';}
								if(($row['disable']) && ($selected=='selected')) {echo '<option '.$selected.' value="G_'.$row['id'].'">[G] '.$row['name'].'</option>';}
							}
							$query2->closeCursor(); 
							?>
						</select>
						<?php 
						//send data in disabled case
						if($rright['ticket_tech']==0) {
							if($globalrow['t_group'])
							{
								echo '<input type="hidden" name="technician" value="G_'.$globalrow['t_group'].'" />';
							} else {
								echo '<input type="hidden" name="technician" value="'.$globalrow['technician'].'" />';
							}
						}									
						if($lock_tech==1) echo '<input type="hidden" name="technician" value="'.$tech_selected.'" />'; 
						?>
					</div>
				</div>
				<!-- END technician part -->

				<!-- START asset part -->
				<?php
					if($rparameters['asset'])
					{
						if($rright['ticket_new_asset_disp'])
						{
							echo'
							<div class="form-group row '; if(($rright['ticket_new_asset_disp']==0 && $_GET['action']=='new') || ($rright['ticket_asset_disp']==0 && $_GET['action']!='new')) {echo 'd-none';} echo '" >
								<div class="col-sm-2 col-form-label text-sm-right pr-0">
									<label class="mb-0" for="asset_id">
										'.T_('Équipement').' :
									</label>
								</div>
								<div class="col-sm-5">
									';
									//display asset type field
									if($rright['ticket_asset_type'])
									{
										echo '
										<select title="'.T_("Type d'équipement").'" id="asset_type" name="asset_type" style="width:auto;" class="form-control d-inline-block mb-1 mb-md-0" >
											<option value="%">'.T_('Aucun').'</option>
											';
											$qry=$db->prepare("SELECT `id`,`name` FROM `tassets_type`");
											$qry->execute();
											while($asset_type=$qry->fetch()) 
											{
												//select type if asset_id exist
												if($globalrow['asset_id'])
												{
													//get type name of this asset
													$qry2=$db->prepare("SELECT `type` FROM `tassets` WHERE `id`=:id");
													$qry2->execute(array('id' => $globalrow['asset_id']));
													$asset_type_find=$qry2->fetch();
													$qry2->closeCursor();

													if($asset_type_find['type']==$asset_type['id'])
													{
														echo '<option selected value="'.$asset_type['id'].'">'.$asset_type['name'].'</option>';
													} else {
														echo '<option value="'.$asset_type['id'].'">'.$asset_type['name'].'</option>';
													}
												} else {
													echo '<option value="'.$asset_type['id'].'">'.$asset_type['name'].'</option>';
												}
											}
											$qry->closeCursor();
											echo '
										</select>
										';
									}
									//display asset model field
									if($rright['ticket_asset_model'])
									{
										echo '
										<select title="'.T_("Modèle d'équipement").'" id="asset_model" name="asset_model" style="width:auto;" class="form-control d-inline-block mb-1 mb-md-0" >
											<option value="%">'.T_('Aucun').'</option>
											';
											$qry=$db->prepare("SELECT `id`,`name` FROM `tassets_model`");
											$qry->execute();
											while($asset_model=$qry->fetch()) 
											{
												//select type if asset_id exist
												if($globalrow['asset_id'])
												{
													//get model name of this asset
													$qry2=$db->prepare("SELECT `model` FROM `tassets` WHERE `id`=:id");
													$qry2->execute(array('id' => $globalrow['asset_id']));
													$asset_model_find=$qry2->fetch();
													$qry2->closeCursor();

													if($asset_model_find['model']==$asset_model['id'])
													{
														echo '<option selected value="'.$asset_model['id'].'">'.$asset_model['name'].'</option>';
													} else {
														echo '<option value="'.$asset_model['id'].'">'.$asset_model['name'].'</option>';
													}
												} else {
													echo '<option value="'.$asset_model['id'].'">'.$asset_model['name'].'</option>';
												}
											}
											$qry->closeCursor();
											echo '
										</select>
										';
									}
									//display asset field
									echo '
									<select style="min-width:269px;" class="form-control col-5 d-inline-block" id="asset_id" name="asset_id" '; if(($rright['ticket_asset']==0 && $_GET['action']!='new') || ($rright['ticket_new_asset_disp']==0 && $_GET['action']=='new') || $ticket_observer) {echo 'disabled="disabled"';} echo' onchange="loadVal(); '; if($rright['ticket_asset_mandatory']) {echo 'CheckMandatory();';} echo '">
										';
										if($_POST['asset_id'])
										{
											$qry2=$db->prepare("SELECT `id`,`netbios` FROM `tassets` WHERE id=:id");
											$qry2->execute(array('id' => $_POST['asset_id']));
											$row2=$qry2->fetch();
											$qry2->closeCursor();
											echo '<option value="'.$row2['id'].'" selected >'.T_($row2['netbios']).'</option>';
											if(($globalrow['asset_id'] && $globalrow['user']) || ($_SESSION['profile_id']==3 || $_SESSION['profile_id']==2) || $rright['ticket_asset_user_only'])
											{
												$qry2=$db->prepare("SELECT `id`,`netbios` FROM `tassets` WHERE netbios!='' AND disable='0' AND user=:user ORDER BY id!=0, netbios");
												$qry2->execute(array('user' => $globalrow['user']));
												while($row2=$qry2->fetch()) {echo '<option value="'.$row2['id'].'">'.T_($row2['netbios']).'</option>';}
												$qry2->closeCursor();
											} else {
												$qry2=$db->prepare("SELECT `id`,`netbios` FROM `tassets` WHERE netbios!='' AND disable='0' ORDER BY id!=0, netbios");
												$qry2->execute();
												while($row2=$qry2->fetch()) {echo '<option value="'.$row2['id'].'">'.T_($row2['netbios']).'</option>';}
												$qry2->closeCursor();
											}
										}
										else
										{
											//find existing value
											$qry2=$db->prepare("SELECT `id`,`netbios` FROM `tassets` WHERE id=:id ORDER BY id");
											$qry2->execute(array('id' => $globalrow['asset_id']));
											$row2=$qry2->fetch();
											$qry2->closeCursor();

											//select none if new ticket 
											if($_GET['action']=='new'){echo '<option value="0">'.T_('Aucun').'</option>';} 
											else {echo '<option value="'.$row2['id'].'" selected>'.$row2['netbios'].'</option>';}
											
											//user restricted asset list
											$qry2=$db->prepare("SELECT `id`,`netbios` FROM `tassets` WHERE `id`!=:id AND `netbios`!='' AND `disable`='0' AND `user` LIKE :user ORDER BY `id`!='0', `netbios`");
											
											if($_SESSION['profile_id']==3 || $_SESSION['profile_id']==2 || $rright['ticket_asset_user_only']){
												$qry2->execute(array('id' => $globalrow['asset_id'],'user' => $_SESSION['user_id']));
											}elseif($_POST['user']){
												$qry2->execute(array('id' => $globalrow['asset_id'],'user' => $_POST['user']));
											}elseif($globalrow['user'] && $rright['ticket_asset_user_only']){
												$qry2->execute(array('id' => $globalrow['asset_id'],'user' => $globalrow['user']));
											}else{
												$qry2->execute(array('id' => $globalrow['asset_id'],'user' => '%'));
											}
											while($row2=$qry2->fetch()){
												echo '<option value="'.$row2['id'].'">'.T_($row2['netbios']).'</option>';
											}
											$qry2->closeCursor();
										}
										echo'			
									</select>
									';
									//send data in disabled case
									if(!$rright['ticket_asset'] && $_GET['action']!='new') echo '<input type="hidden" name="asset_id" value="'.$globalrow['asset_id'].'" />'; 
									echo '
								</div>
							</div>
							';
						}
					}
				?>
				<!-- END asset part -->
				
				<!-- START category part -->
				<div class="form-group row <?php if(($rright['ticket_cat_disp']==0 && $_GET['action']!='new') || ($rright['ticket_new_cat_disp']==0 && $_GET['action']=='new')) echo 'd-none';?>">
					<div class="col-sm-2 col-form-label text-sm-right pr-0">
						<label class="mb-0" for="category">
							<?php if(($globalrow['category']==0) && ($_POST['category']==0)) {echo '<i id="warning_empty_category" title="'.T_('Aucune catégorie associée').'" class="fa fa-exclamation-triangle text-danger-m2 text-130"><!----></i>&nbsp;';} ?>
							<?php echo T_('Catégorie').' :'; ?>
						</label>
					</div>
					<div class="col-sm-5">
						<select id="category" name="category" <?php if($mobile) {echo 'style="max-width:105px;"';}else{echo 'style="width:auto;"';}?>  class="form-control d-inline-block mb-1 mb-md-0" title="<?php echo T_('Catégorie'); ?>" <?php if(($rright['ticket_cat']==0 && $_GET['action']!='new') || ($rright['ticket_new_cat']==0 && $_GET['action']=='new') || $ticket_observer) echo ' disabled="disabled" ';?>>
						<?php
							//if user limit service restrict category to associated service
							if($rparameters['user_limit_service'] && $rright['dashboard_service_only'] && $rright['ticket_cat_service_only']) //case for user who open ticket to auto-select categories of the service
							{
								//case service field is display
								if($rright['ticket_service_disp'] || $rright['ticket_new_service_disp'])
								{
									if($_POST['u_service']) {$where='WHERE service='.$_POST['u_service'].' OR id=0 ';} else {$where='WHERE service='.$globalrow['u_service'].' OR id=0 ';}
								} else { //case service field not display, using service associated to current user
									//one service case
									if($cnt_service==1){$where='WHERE service='.$user_services['0'].' OR service=0 OR id=0 ';}
									elseif($cnt_service>1) //multi services case
									{
										$where='WHERE ';
										foreach($user_services as $user_service) {$where.="service='$user_service' OR ";}
										$where.='service=0';
									} else {$where='';}
								}
							}else{$where='';}
							$query= $db->query("SELECT `id`,`name` FROM `tcategory` $where ORDER BY `id`!='0',`number`,`name`"); //order to display none in first
							while ($row = $query->fetch()) 
							{
								if($row['id']==0) {$row['name']=T_($row['name']);} //translate only none
								if($_POST['category']!=''){if($_POST['category']==$row['id']) echo '<option value="'.$row['id'].'" selected>'.T_($row['name']).'</option>'; else echo '<option value="'.$row['id'].'">'.T_($row['name']).'</option>';}
								else
								{if($globalrow['category']==$row['id']) echo '<option value="'.$row['id'].'" selected>'.T_($row['name']).'</option>'; else echo '<option value="'.$row['id'].'">'.T_($row['name']).'</option>';}
							}
							$query->closeCursor();
						?>
						</select>
						<?php if(($rright['ticket_cat']==0 && $_GET['action']!='new') || ($rright['ticket_new_cat']==0 && $_GET['action']=='new'))  echo '<input type="hidden" name="category" value="'.$globalrow['category'].'" />'; //send data in disabled case ?>
						
						<?php
						//display subcat
						if($rright['ticket_subcat_disp'])
						{
							echo '
							<select 
								id="subcat" 
								name="subcat" 
								hidden="hidden"
								'; if($mobile) {echo 'style="max-width:105px;"';}else{echo 'style="width:auto;"';} echo '
								class="form-control d-inline-block mb-1 mb-md-0 " 
								title="'.T_('Sous-catégorie').'" 
								onchange="loadVal(); '; if($rright['ticket_cat_mandatory']) {echo ' CheckMandatory(); ';} echo '" 
								'; if(($rright['ticket_cat']==0 && $_GET['action']!='new') || ($rright['ticket_new_cat']==0 && $_GET['action']=='new') || $ticket_observer) echo ' disabled="disabled" '; 
								echo '
							>
								';
								
									$qry=$db->prepare("SELECT `id`,`name` FROM `tsubcat` WHERE `cat` LIKE :cat OR `id`='0' ORDER BY `name` ASC");
									if($_POST['category']) {$qry->execute(array('cat' => $_POST['category']));}
									else{$qry->execute(array('cat' => $globalrow['category']));}
									while($row=$qry->fetch()) 
									{
										if(!$row['id']) {$row['name']=T_($row['name']);}
										if($_POST['subcat'])
										{
											if($_POST['subcat']==$row['id']) {echo '<option value="'.$row['id'].'" selected>'.T_($row['name']).'</option>';}
											else {echo '<option value="'.$row['id'].'">'.T_($row['name']).'</option>';}
										}
										else
										{
											if($globalrow['subcat']==$row['id']) {echo '<option value="'.$row['id'].'" selected>'.T_($row['name']).'</option>';}
											else {echo '<option value="'.$row['id'].'">'.T_($row['name']).'</option>';}
										}
									}
									$qry->closeCursor();
									if($globalrow['subcat']==0 && $_POST['subcat']==0) echo "<option value=\"\" selected></option>";
								echo '
							</select>
							';
						}
						//send data in disabled case
						if(!$rright['ticket_subcat_disp'] || ($rright['ticket_cat']==0 && $_GET['action']!='new') || ($rright['ticket_new_cat']==0 && $_GET['action']=='new'))  echo '<input type="hidden" name="subcat" value="'.$globalrow['subcat'].'" />'; 
						//cat action buttons
						if($rright['ticket_cat_actions']!=0 && $rright['ticket_subcat_disp'])
						{
							echo '
							<button title="'.T_('Ajouter une sous-catégorie').'" style="background: none; padding: 0px; border: none;" type="button" id="add_cat" name="add_cat" data-toggle="modal" data-target="#add_cat" >
								<i class="fa fa-plus-circle text-success text-130 mr-2 ml-1"><!----></i>
							</button>
							<button title="'.T_('Modifier une sous-catégorie').'" style="background: none; padding: 0px; border: none;" type="button" id="edit_cat" name="edit_cat" data-toggle="modal" data-target="#edit_cat" >
								<i class="fa fa-pencil-alt text-warning text-130 mr-2 ml-1"><!----></i>
							</button>
							';
						}
						if(!$rright['ticket_cat_mandatory']){echo '<span id="category_field_mandatory"></span><span id="cat_label_mandatory"></span>'; } // avoid error on ticket.js
						?>
					</div>
				</div>
				<!-- END category part -->

				<!-- START agency part -->
				<?php
				if($rparameters['user_agency'])
				{
					//check if current user have multiple agencies to display select, else no display select and get value of agency
					$qry2=$db->prepare("SELECT COUNT(id) FROM `tusers_agencies` WHERE user_id=:user_id");
					$qry2->execute(array('user_id' => $_SESSION['user_id']));
					$row2=$qry2->fetch();
					$qry2->closeCursor();
					if(($row2[0]==0 && ($_SESSION['profile_id']==1 || $_SESSION['profile_id']==2) || $rright['ticket_agency']==0)) //case no agency for current user
					{
						echo '<input type="hidden" name="u_agency" value="'.$globalrow['u_agency'].'" />'; //send data without display
					}
					elseif($row2[0]==1 && ($_SESSION['profile_id']==1 || $_SESSION['profile_id']==2)) //case one agency for current user hide field and transmit data
					{
						$qry3=$db->prepare("SELECT `agency_id` FROM `tusers_agencies` WHERE user_id=:user_id");
						$qry3->execute(array('user_id' => $_SESSION['user_id']));
						$row3=$qry3->fetch();
						$qry3->closeCursor();
						echo '<input type="hidden" name="u_agency" value="'.$row3['agency_id'].'" />'; //send data without display
					} else //else display field to select agency
					{
						//display select agency field
						echo'
						<div class="form-group row">
							<div class="col-sm-2 col-form-label text-sm-right pr-0">
								<label class="mb-0" for="u_agency">
									'.T_('Agence').' :
								</label>
							</div>
							<div class="col-sm-5">
								<select id="u_agency" name="u_agency" class="form-control col-5"  '; if($rright['ticket_agency_mandatory']) {echo ' onchange="CheckMandatory();" ';}echo' >
									';
									//display list of agency of current user if it's a user or poweruser
									$find_agency_id=0;
									if($_SESSION['profile_id']==1 || $_SESSION['profile_id']==2) {
										$qry3=$db->prepare("SELECT DISTINCT(`agency_id`) FROM `tusers_agencies` WHERE `user_id` LIKE :user_id AND `agency_id` IN (SELECT `id` AS `agency_id` FROM `tagencies` WHERE `disable`='0')");
										$qry3->execute(array('user_id' => $_SESSION['user_id']));
									}
									elseif(($_SESSION['profile_id']==0 || $_SESSION['profile_id']==3) && $_POST['user']) {
										$qry3=$db->prepare("SELECT DISTINCT(`agency_id`) FROM `tusers_agencies` WHERE `user_id` LIKE :user_id AND `agency_id` IN (SELECT `id` AS `agency_id` FROM `tagencies` WHERE `disable`='0')");
										$qry3->execute(array('user_id' => $_POST['user']));
									}
									elseif(($_SESSION['profile_id']==0 || $_SESSION['profile_id']==3) && $globalrow['user']) {
										$qry3=$db->prepare("SELECT DISTINCT(`agency_id`) FROM `tusers_agencies` WHERE `user_id` LIKE :user_id AND `agency_id` IN (SELECT `id` AS `agency_id` FROM `tagencies` WHERE `disable`='0')");
										$qry3->execute(array('user_id' => $globalrow['user']));
									}
									else{
										$qry3=$db->prepare("SELECT `id` AS agency_id FROM `tagencies` WHERE disable='0' ORDER BY name");
										$qry3->execute();
									}
									$count = $qry3->rowCount();
									while($row3=$qry3->fetch()) 
									{
										//get agency name
										$qry4=$db->prepare("SELECT `id`,`name` FROM `tagencies` WHERE id=:id");
										$qry4->execute(array('id' => $row3['agency_id']));
										$row4=$qry4->fetch();
										$qry4->closeCursor();
										if($globalrow['u_agency']==$row4['id']) {$selected='selected';} else {$selected='';}
										if($count==1 && $_GET['action']=='new') {$selected='selected'; $find_agency_id=1;}
										echo '<option value="'.$row4['id'].'" '.$selected.' >'.T_($row4['name']).'</option>';
									}
									$qry3->closeCursor();
									//case for no agency selected
									if($globalrow['u_agency']==0 && $find_agency_id==0 && $_POST['u_agency']==0) {echo '<option value="0" selected >'.T_("Aucune").'</option>';}
									echo'			
								</select>
							</div>
						</div>
						';
						if($_GET['action']!='new' && $_POST['u_agency']==$globalrow['u_agency'] && $_POST['u_agency']!=0) {echo '<input type="hidden" name="u_agency" value="'.$globalrow['u_agency'].'" />';} //send data in disabled case
					}
				}
				?>
				<!-- END agency part -->
				
				<!-- START sender service part -->
				<?php
					if($rright['ticket_sender_service_disp']!=0)
					{
						echo'
							<div class="form-group row">
								<div class="col-sm-2 col-form-label text-sm-right pr-0">
									<label class="mb-0" for="sender_service">
										'.T_('Service du demandeur').' :
									</label>
								</div>
								<div class="col-sm-5">
									<select class="form-control col-5" id="sender_service" name="sender_service" onchange="loadVal();">
										<option value="0">'.T_('Aucun').'</option>
										';
											$qry=$db->prepare("SELECT `id`,`name` FROM `tservices` WHERE `id` IN (SELECT `service_id` FROM `tusers_services` WHERE `user_id`=:user_id) ORDER BY `name`");
											$qry->execute(array('user_id' => $globalrow['user']));
											while($row=$qry->fetch()) 
											{
												if($globalrow['sender_service']==$row['id'])
												{
													echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
												} else {
													echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
												}
											}
											$qry->closeCursor();
										echo'			
									</select>
								</div>
							</div>
							';
					} else { //hidden case
						if($globalrow['sender_service']!=0) //if value exist keep value
						{
							//disable or hide case to keep value
							echo '<input type="hidden" id="sender_service" name="sender_service" value="'.$globalrow['sender_service'].'" />';
						} elseif($globalrow['user']) {
							//get sender service id to put in database
							$qry2=$db->prepare("SELECT MAX(id) FROM tservices WHERE id IN (SELECT service_id FROM tusers_services WHERE user_id=:user_id)");
							$qry2->execute(array('user_id' => $globalrow['user']));
							$sender_svc_id=$qry2->fetch();
							$qry2->closeCursor();
							if($sender_svc_id[0]) {echo '<input type="hidden" id="sender_service" name="sender_service" value="'.$sender_svc_id[0].'" />';}
						} else {
							//populate field with ajax, use for ticket creation #24638
							echo '<input type="hidden" id="sender_service" name="sender_service" value="" />';
						}
					}
				?>
				<!-- END sender service part -->

				<!-- START place part if parameter is on -->
				<?php
				if($rparameters['ticket_places']==1)
				{
					echo '
					<div class="form-group row">
						<div class="col-sm-2 col-form-label text-sm-right pr-0">
							<label class="mb-0" for="ticket_places">'.T_('Lieu').' :</label>
						</div>
						<div class="col-sm-5">
							<select class="form-control col-5" id="ticket_places" name="ticket_places" '; if((!$rright['ticket_place'] && $_GET['action']!='new') || $ticket_observer) {echo 'disabled="disabled"';} if($rright['ticket_place_mandatory']) { echo ' onchange="CheckMandatory();" ';} echo' > 
								';
								if($_POST['ticket_places'])
								{
									$qry=$db->prepare("SELECT `id`,`name` FROM `tplaces` ORDER BY name ASC");
									$qry->execute();
									while($row=$qry->fetch()) {if($_POST['ticket_places']==$row['id']) {echo '<option selected value="'.$row['id'].'">'.T_($row['name']).'</option>';} else {echo '<option value="'.$row['id'].'">'.T_($row['name']).'</option>';}}
									$qry->closeCursor();
								} else {
									$qry=$db->prepare("SELECT `id`,`name` FROM `tplaces` ORDER BY name ASC");
									$qry->execute();
									while($row=$qry->fetch()){if($globalrow['place']==$row['id']) {echo '<option selected value="'.$row['id'].'">'.T_($row['name']).'</option>';} else {echo '<option value="'.$row['id'].'">'.T_($row['name']).'</option>';}}
									$qry->closeCursor();
								}
							echo '
							</select>
							';
							if($rright['ticket_place']==0 && $_GET['action']!='new')  echo '<input type="hidden" name="ticket_places" value="'.$globalrow['place'].'" />'; //send data in disabled case
							echo '
						</div>
					</div>
					';
				}
				?>
				<!-- END place part -->

				<!-- START title part -->
				<div class="form-group row <?php if($rright['ticket_title_disp']==0) {echo 'd-none';} ?>">
					<div class="col-sm-2 col-form-label text-sm-right pr-0">
						<label class="mb-0" for="title"><?php echo T_('Titre');?> :</label>
					</div>
					<div class="col-sm-5">
						<input class="form-control col-10" name="title" id="title" type="text" maxlength="100"
							size="<?php if(!$mobile) {echo '50';} else {echo '30';}?>"  
							value="<?php if($_POST['title']) {echo $_POST['title'];} else {echo $globalrow['title'];} ?>" 
							<?php 
							if(($rright['ticket_title']==0 && $_GET['action']!='new') || $ticket_observer) {echo ' readonly="readonly" ';} 
							if($rright['ticket_title_mandatory']) {echo ' required="required" onchange="CheckMandatory();" ';}
							?> 
						/>
					</div>
				</div>
				<!-- END title part -->

				<!-- START description part -->
				<div class="form-group row <?php if($rright['ticket_description_disp']==0) {echo 'd-none';} ?>">
					<div class="col-sm-2 col-form-label text-sm-right pr-0">
						<span class="mb-0"><?php echo T_('Description'); ?> :</span>
					</div>
					<div class="col-sm-5">
						<table id="description" border="1" width="<?php if(!$mobile) {echo '780';} else {echo '100%';}?>" style=" min-height: 40px; border: 1px solid #D8D8D8;" >
							<tr>
								<td <?php if(!$rright['ticket_description']) {echo 'style="padding:5px"';} ?>>
									<?php
									if(($rright['ticket_description'] || $_GET['action']=='new') && !$ticket_observer)
									{	
										//display editor
										echo '
										<div id="editor" '; if($rright['ticket_description_mandatory']) {echo 'onchange="CheckMandatory();"';} echo' class="bootstrap-wysiwyg-editor pl-2 pt-1" style="min-height:100px; max-width:775px">';
											if($_POST['text'] && $_POST['text']!='') {echo "$_POST[text]";} else {echo $globalrow['description'];}
											if($_GET['action']=='new' && !$_POST['user']) {echo '';}	 echo'
										</div>
										<input type="hidden" id="text" name="text" />
										';
									} else {
										echo $globalrow['description'];
										echo '<input type="hidden" id="text" name="text" value="'.htmlentities($globalrow['description']).'" />';
									}
									?>
								</td>
							</tr>
						</table>
					</div>
				</div>
				<!-- END description part -->

				<!-- START resolution part -->
				<div class="form-group row <?php if(($rright['ticket_resolution_disp']==0 && $_GET['action']!='new') || ($rright['ticket_new_resolution_disp']==0 && $_GET['action']=='new')) echo 'd-none';?>" >
					<div class="col-sm-2 col-form-label text-sm-right pr-0">
						<span class="mb-0"><?php echo T_('Résolution'); ?> :</span>
					</div>
					<div class="col-sm-5">	
						<?php include "./thread.php"; ?>	
					</div>
				</div>
				<a id="down"></a>
				<!-- END resolution part -->

				<!-- START attachement part -->
				<?php
				if($rright['ticket_attachment'])
				{
					//check existing attachments
					$qry=$db->prepare("SELECT `id` FROM `tattachments` WHERE `ticket_id`=:ticket_id");
					$qry->execute(array('ticket_id' => $_GET['id']));
					$row=$qry->fetch();
					$qry->closeCursor();
					if($globalrow['state']==3 && empty($row[0]))
					{
						//hide field
					} else {
						echo '
						<div class="form-group row">
							<div class="col-sm-2 col-form-label text-sm-right pr-0">
								<label class="mb-0" for="file">'.T_('Fichiers joints').' :</label>
							</div>
							<div class="col-sm-5">
								<table border="1" style="border:1px solid #D8D8D8; min-width:265px;" >
									<tr>
										<td style="padding:15px;">';
											include "./attachment.php";
											echo '
										</td>
									</tr>
								</table>
							</div>
						</div>';
					}
				}
				?>
				<!-- END attachement part -->

				<!-- START create date part -->
				<div class="form-group row  <?php if(!$rright['ticket_date_create_disp']) {echo 'd-none';}?>">
					<div class="col-sm-2 col-form-label text-sm-right pr-0">
						<label class="mb-0" for="date_create"><?php echo T_('Date de création'); ?> :</label>
					</div>
					<div class="col-sm-5">
						<input type="hidden" name="hide" id="hide" value="1"/>
						<input type="text" class="form-control <?php if($mobile){echo 'col-7';} else {echo 'col-5';} ?> datetimepicker-input" id="date_create" data-toggle="datetimepicker" data-target="#date_create" name="date_create" autocomplete="off" 
						value="<?php if($_POST['date_create'] && strpos($_POST['date_create'],'-')) {echo DatetimeToDisplay($_POST['date_create']);}elseif($_POST['date_create']) {echo $_POST['date_create'];} elseif($globalrow['date_create']) {echo DatetimeToDisplay($globalrow['date_create']);} ?>" 
						<?php if(!$rright['ticket_date_create'] || $ticket_observer) {echo ' disabled ';}?> />
					</div> 
				</div>
				<!-- END create date part -->

				<!-- START hope date part -->
				<div class="form-group row <?php echo $date_hope_error; if(!$rright['ticket_date_hope_disp']) {echo ' d-none';}?>">
					<div class="col-sm-2 col-form-label text-sm-right pr-0">
						<label class="mb-0" for="date_hope">
							<?php 
								//display warning if hope date is passed
								$qry=$db->prepare("SELECT DATEDIFF(NOW(), :date_hope)");
								$qry->execute(array('date_hope' => $globalrow['date_hope']));
								$row=$qry->fetch();
								$qry->closeCursor();
								if($row[0]>0 && ($globalrow['state']!=3 && $globalrow['state']!=4)) echo '<i title="'.T_('Date de résolution dépassée de').' '.$row[0].' '.T_('jours').'" class="fa fa-exclamation-triangle text-warning text-130"><!----></i>&nbsp;';
								echo T_('Date de résolution estimée'); 
							?> :
						</label>
					</div>
					<div class="col-sm-5">
						<input type="text" class="form-control <?php if($mobile){echo 'col-7';} else {echo 'col-5';} ?> datetimepicker-input" <?php if($rright['ticket_date_hope_mandatory']) {echo 'onchange="CheckMandatory();"'; } ?> id="date_hope" data-toggle="datetimepicker" data-target="#date_hope" autocomplete="off" name="date_hope"  
						value="<?php if($_POST['date_hope'] && strpos($_POST['date_hope'],'-')) {echo DateToDisplay($_POST['date_hope']);}elseif($_POST['date_hope']) {echo $_POST['date_hope'];} elseif($globalrow['date_hope']) {echo DateToDisplay($globalrow['date_hope']);} ?>" 
							<?php if(!$rright['ticket_date_hope'] || $ticket_observer) {echo ' disabled ';} if($rright['ticket_date_hope_mandatory']) {echo ' required="required" ';}?>
						/>
					</div>
				</div>
				<!-- END hope date part -->
				
				<!-- START resolution date part -->
				<div class="form-group row <?php if(!$rright['ticket_date_res_disp']) {echo 'd-none';}?>">
					<div class="col-sm-2 col-form-label text-sm-right pr-0">
						<label class="mb-0" for="date_res"><?php echo T_('Date de résolution'); ?> :</label>
					</div>
					<div class="col-sm-5">
						<input type="text" class="form-control <?php if($mobile){echo 'col-7';} else {echo 'col-5';} ?> datetimepicker-input" id="date_res" data-toggle="datetimepicker" data-target="#date_res" autocomplete="off" name="date_res" 
						value="<?php if($_POST['date_res'] && strpos($_POST['date_res'],'-')) {echo DatetimeToDisplay($_POST['date_res']);}elseif($_POST['date_res']) {echo $_POST['date_res'];} elseif($globalrow['date_res']) {echo DatetimeToDisplay($globalrow['date_res']);} ?>" 
							<?php if(!$rright['ticket_date_res'] || $ticket_observer) {echo ' disabled ';}?>
						/>
					</div>
				</div>
				<!-- END resolution date part -->
				<!-- START time part -->
				<div class="form-group row <?php if(!$rright['ticket_time_disp']) {echo 'd-none';} ?>">
					<div class="col-sm-2 col-form-label text-sm-right pr-0">
						<label class="mb-0" for="time"><?php echo T_('Temps passé'); ?> :</label>
					</div>
					<div class="col-sm-5">
						<?php
							//time by resolution element
							if($rparameters['ticket_time_response_element'])
							{
								//sum element time
								$qry=$db->prepare("SELECT SUM(`time`) as `total_time` FROM `tthreads` WHERE `ticket`=:ticket");
								$qry->execute(array('ticket' => $_GET['id']));
								$row=$qry->fetch();
								$qry->closeCursor();

								if($row['total_time']!=0)
								{
									echo '<div class="mt-2" >'.MinToHour($row['total_time']).'</div><input type="hidden" name="time" id="time" value="'.$row['total_time'].'" />';
								} elseif($row['total_time']==0 && $globalrow['time']!=0)
								{
									echo '<div class="mt-2" >'.MinToHour($globalrow['time']).'</div><input type="hidden" name="time" id="time" value="'.$globalrow['time'].'" />';
								} else {
									echo '<div class="mt-2" >0h</div><input type="hidden" name="time" id="time" value="0" />';
								}
							} else {
								echo '
								<select class="form-control '; if($mobile){echo 'col-7';} else {echo 'col-5';} echo '" id="time" name="time" '; if(!$rright['ticket_time'] || $ticket_observer) {echo 'disabled';}echo ' >
									';
									$qry=$db->prepare("SELECT `min`,`name` FROM `ttime` ORDER BY min ASC");
									$qry->execute();
									while($row=$qry->fetch()) 
									{
										if(($_POST['time']==$row['min'])||($globalrow['time']==$row['min']))
										{
											echo '<option selected value="'.$row['min'].'">'.$row['name'].'</option>';
											$selected_time=$row['min'];
										} else {
											echo '<option value="'.$row['min'].'">'.$row['name'].'</option>';
										}
									}
									$qry->closeCursor();
									//special case when time entry was modify or delete from admin time list
									$qry=$db->prepare("SELECT `id` FROM `ttime` WHERE min=:min");
									$qry->execute(array('min' => $globalrow['time']));
									$row=$qry->fetch();
									$qry->closeCursor();
									if(!$row && $_GET['action']!='new') { echo '<option selected value="'.$globalrow['time'].'">'.$globalrow['time'].'m</option>';}
									echo '
								</select>
								';
								//send value in lock select case 
								if(!$rright['ticket_time']) {echo '<input type="hidden" name="time" value="'.$selected_time.'" />';}
							}
						?>
					</div>
				</div>
				<!-- END time part -->
				<!-- START time hope part -->
				<div class="form-group row <?php if(!$rright['ticket_time_hope_disp']) {echo 'd-none';}?>">
					<div class="col-sm-2 col-form-label text-sm-right pr-0">
						<label class="mb-0" for="time_hope">
						<?php 
							//display error if time hope < time pass
							if(($globalrow['time_hope']<$globalrow['time']) && $globalrow['state']!='3') {echo '<i class="pr-1 fa fa-exclamation-triangle text-danger-m2 text-130" title="'.T_('Le temps est sous-estimé').'"><!----></i>';}
							echo T_('Temps estimé'); 
						?> :
						</label>
					</div>
					<div class="col-sm-5">
						<select class="form-control <?php if($mobile){echo 'col-7';} else {echo 'col-5';} ?>" id="time_hope" name="time_hope" <?php if(!$rright['ticket_time_hope'] || $ticket_observer) echo 'disabled';?> >
							<?php
							$qry=$db->prepare("SELECT `min`,`name` FROM `ttime` ORDER BY min ASC");
							$qry->execute();
							while($row=$qry->fetch()) 
							{
								if(($_POST['time_hope']==$row['min']) || ($globalrow['time_hope']==$row['min']))
								{
									echo '<option selected value="'.$row['min'].'">'.$row['name'].'</option>'; 
									$selected_time_hope=$row['min'];
								} else {
									echo '<option value="'.$row['min'].'">'.$row['name'].'</option>';
									$selected_time_hope=$row['min'];
								}
							}
							$qry->closeCursor();
							//special case when time entry was modify or delete from admin time list
							$qry=$db->prepare("SELECT `id` FROM `ttime` WHERE min=:min");
							$qry->execute(array('min' => $globalrow['time_hope']));
							$row=$qry->fetch();
							$qry->closeCursor();
							if(!$row) { echo '<option selected value="'.$globalrow['time_hope'].'">'.$globalrow['time_hope'].'m</option>';}
							?>
						</select>
						<?php
						//send value in lock or hide case
						if($rright['ticket_time_hope']==0 || $rright['ticket_time_hope_disp']==0) {
							echo '<input type="hidden" name="time_hope" value="'.$globalrow['time_hope'].'" />';
						}
						?>
					</div>
				</div>
				<!-- END time hope part -->
				<!-- START priority part -->
				<div class="form-group row <?php if(!$rright['ticket_priority_disp']) {echo 'd-none';} ?>">
					<div class="col-sm-2 col-form-label text-sm-right pr-0">
						<label class="mb-0" for="priority">
							<?php echo T_('Priorité'); ?> :
						</label>
					</div>
					<div class="col-sm-5">
						<select class="form-control <?php if($mobile){echo 'col-7';} else {echo 'col-5';} ?> d-inline-block mb-1 mb-md-0" id="priority" name="priority" <?php if(!$rright['ticket_priority'] || $ticket_observer) {echo ' disabled ';} if($rright['ticket_priority_mandatory']) {echo ' required onchange="CheckMandatory();" ';} ?>>
							<?php
							if($rright['ticket_priority_mandatory']) {echo '<option value=""></option>';}
							//if user limit service restrict priority to associated service
							if($rparameters['user_limit_service'] && $rright['dashboard_service_only'] && $rright['ticket_priority_service_limit'])
							{
								if($_POST['u_service']) {$where=' service='.$_POST['u_service'].' ';} else {$where=' service='.$globalrow['u_service'].' ';}
								$old_priority=1;
								$query2 = $db->query("SELECT `id`,`name` FROM `tpriority` WHERE $where OR `id`=0 ORDER BY `number` DESC");
								while ($row2 = $query2->fetch()) {
									//select entry
									$selected='';
									if($_POST['priority'] && $row2['id']==$_POST['priority']) 
									{$selected='selected';}
									elseif($globalrow['priority'] && $row2['id']==$globalrow['priority']) 
									{$selected='selected';}
									if($globalrow['priority']==$row2['id']) {$old_priority=0;}
									echo '<option '.$selected.' value="'.$row2['id'].'">'.T_($row2['name']).'</option>';
								}
								$query2->closeCursor(); 
								//keep old data
								if($old_priority==1 && $_GET['action']!='new') {
									$qry2=$db->prepare("SELECT `id`,`name` FROM `tpriority` WHERE id=:id");
									$qry2->execute(array('id' => $globalrow['priority']));
									$row2=$qry2->fetch();
									$qry2->closeCursor();
									if(!empty($row2['name'])) {echo '<option selected value="'.$row2['id'].'">'.T_($row2['name']).'</option>';}
								}
							} else { //case no limit service
								if($_POST['priority'])
								{
									//find row to select
									$qry=$db->prepare("SELECT `name` FROM `tpriority` WHERE id=:id");
									$qry->execute(array('id' => $_POST['priority']));
									$row=$qry->fetch();
									$qry->closeCursor();
									if(!isset($row['name'])) {$row=array(); $row['name']='';}
									echo '<option value="'.$_POST['priority'].'" selected >'.T_($row['name']).'</option>';
									//display all entries without selected
									$selected_priority=$_POST['priority'];
									$qry=$db->prepare("SELECT DISTINCT(id),`name` FROM `tpriority` WHERE `id`!=:id ORDER BY `number` DESC");
									$qry->execute(array('id' => $_POST['priority']));
									while($row=$qry->fetch()) {echo '<option value="'.$row['id'].'">'.T_($row['name']).'</option>';}
									$qry->closeCursor();
								} else {
									if($globalrow['priority'])
									{
										//find row to select
										$qry=$db->prepare("SELECT DISTINCT(id),`name` FROM `tpriority` WHERE `id`=:id");
										$qry->execute(array('id' => $globalrow['priority']));
										$row=$qry->fetch();
										$qry->closeCursor();
										if(!empty($row['name'])) {echo '<option value="'.$globalrow['priority'].'" selected >'.T_($row['name']).'</option>';}
										$selected_priority=$globalrow['priority'];
									} else {$selected_priority='';}
									$qry=$db->prepare("SELECT `id`,`name` FROM `tpriority` WHERE id!=:id ORDER BY `number` DESC");
									$qry->execute(array('id' => $globalrow['priority']));
									while($row=$qry->fetch()) {echo '<option value="'.$row['id'].'" >'.T_($row['name']).'</option>';}
									$qry->closeCursor();
								}
							}
							?>			
						</select>
						<?php
						//send value in lock select case 
						if(!$rright['ticket_priority'] || !$rright['ticket_priority_disp']) {echo '<input type="hidden" name="priority" value="'.$globalrow['priority'].'" />';}
						
						//display priority icon
						if($_POST['priority']) {$check_id=$_POST['priority'];} elseif($globalrow['priority']) {$check_id=$globalrow['priority'];} else {$check_id=6;}
						$qry=$db->prepare("SELECT `name`,`color` FROM `tpriority` WHERE id=:id");
						$qry->execute(array('id' => $check_id));
						$row=$qry->fetch();
						$qry->closeCursor();
						if(empty($row['name'])) {$row=array(); $row['name']='';}
						if(empty($row['color'])) {$row['color']='';}
						if($row['name']) {echo '<i title="'.T_($row['name']).'" class="fa fa-exclamation-triangle text-130 pl-1" style=" color:'.T_($row['color']).'"><!----></i>';}
						?>
					</div>
				</div>
				<!-- END priority part -->
				<!-- START criticality part -->
				<div class="form-group row <?php if(!$rright['ticket_criticality_disp']) {echo 'd-none';} ?>">
					<div class="col-sm-2 col-form-label text-sm-right pr-0">
						<label class="mb-0" for="criticality" >
							<?php echo T_('Criticité'); ?> :
						</label>
					</div>
					<div class="col-sm-5">
						<select class="form-control <?php if($mobile){echo 'col-7';} else {echo 'col-5';} ?> d-inline-block mb-1 mb-md-0" id="criticality" name="criticality" <?php if(!$rright['ticket_criticality'] || $ticket_observer) {echo ' disabled ';} if($rright['ticket_criticality_mandatory']) {echo ' required onchange="CheckMandatory();"';}?>>
							<?php
							if($rright['ticket_criticality_mandatory']) {echo '<option value=""></option>';}
							//if user limit service restrict criticality to associated service
							if($rparameters['user_limit_service'] && $rright['dashboard_service_only'] && $rright['ticket_criticality_service_limit'])
							{
								if($_POST['u_service']) {$where=' service='.$_POST['u_service'].' ';} else {$where=' service='.$globalrow['u_service'].' ';}
								$old_criticality=1;
								$query2 = $db->query("SELECT `id`,`name` FROM `tcriticality` WHERE $where OR `id`=0 ORDER BY `number` DESC");
								while ($row2 = $query2->fetch()) {
									//select entry
									$selected='';
									if($_POST['criticality'] && $row2['id']==$_POST['criticality']) 
									{$selected='selected';}
									elseif($globalrow['criticality'] && $row2['id']==$globalrow['criticality']) 
									{$selected='selected';}
									if($globalrow['criticality']==$row2['id']) {$old_criticality=0;}
									if(!empty($row2['name'])) {echo '<option '.$selected.' value="'.$row2['id'].'">'.T_($row2['name']).'</option>';}
								}
								$query2->closeCursor(); 
								
								//keep old data
								if($old_criticality==1 && $_GET['action']!='new') {
									$qry2=$db->prepare("SELECT `id`,`name` FROM `tcriticality` WHERE id=:id");
									$qry2->execute(array('id' => $globalrow['criticality']));
									$row2=$qry2->fetch();
									$qry2->closeCursor();
									if(!empty($row2['name'])) {echo '<option selected value="'.$row2['id'].'">'.T_($row2['name']).'</option>';}
								}
								$selected_criticality=''; //init var
							} else { //case no service limit
								if($_POST['criticality'])
								{
									//find row to select
									$qry=$db->prepare("SELECT `id`,`name` FROM `tcriticality` WHERE id=:id");
									$qry->execute(array('id' => $_POST['criticality']));
									$row=$qry->fetch();
									$qry->closeCursor();
									if(!empty($row['name'])) {echo '<option value="'.$_POST['criticality'].'" selected >'.T_($row['name']).'</option>';}
									//display all entries without selected
									$selected_criticality=$_POST['criticality'];
									$qry=$db->prepare("SELECT DISTINCT(id),name FROM `tcriticality` WHERE id!=:id ORDER BY number DESC");
									$qry->execute(array('id' => $_POST['criticality']));
									while($row=$qry->fetch()) {echo '<option value="'.$row['id'].'">'.T_($row['name']).'</option>';}
									$qry->closeCursor();
								}
								else
								{
									if($globalrow['criticality'])
									{
										//find row to select
										$qry=$db->prepare("SELECT DISTINCT(id),name FROM `tcriticality` WHERE id=:id");
										$qry->execute(array('id' => $globalrow['criticality']));
										$row=$qry->fetch();
										$qry->closeCursor();
										if(!empty($row['name'])) {echo '<option value="'.$globalrow['criticality'].'" selected >'.T_($row['name']).'</option>';}
										$selected_criticality=$globalrow['criticality'];
									} else {$selected_criticality='';}
									//display all entries without selected
									$qry=$db->prepare("SELECT `id`,`name` FROM `tcriticality` WHERE id!=:id ORDER BY number DESC");
									$qry->execute(array('id' => $globalrow['criticality']));
									while($row=$qry->fetch()) {echo '<option value="'.$row['id'].'">'.T_($row['name']).'</option>';}
									$qry->closeCursor();
								}			
							}
							?>			
						</select>
						<?php
						//send value in lock select case 
						if($rright['ticket_criticality']==0) {echo '<input type="hidden" name="criticality" value="'.$selected_criticality.'" />';}
						
						//display criticality icon
						if($_POST['criticality']) {$check_id=$_POST['criticality'];} else {$check_id=$globalrow['criticality'];}
						$qry=$db->prepare("SELECT `color`,`name` FROM `tcriticality` WHERE `id`=:id");
						$qry->execute(array('id' => $check_id));
						$row=$qry->fetch();
						$qry->closeCursor();
						if(!empty($row['name'])) {echo '&nbsp;<i title="'.T_($row['name']).'" class="fa fa-bullhorn text-130" style="color:'.$row['color'].'"><!----></i>';}
						?>
					</div>
				</div>
				<!-- END criticality part -->
				<!-- START billable part -->
				<?php
					if($rright['ticket_billable'])
					{
						echo '
						<div class="form-group row">
							<div class="col-sm-2 col-form-label text-sm-right pr-0">
								<label class="mb-0" for="billable">'.T_('Facturable').' :</label>
							</div>
							<div class="col-sm-5">
								<label class="lbl">
									<input class="mt-2" type="checkbox" '; if($globalrow['billable']) {echo 'checked';} echo ' name="billable" value="1">
								</label>
							</div>
						</div>
						';
					} else {echo '<input type="hidden" name="billable" value="'.$globalrow['billable'].'" />';}
				?>
				<!-- END billable part -->
				<!-- START state part -->
				<div class="form-group row <?php if($rright['ticket_state_disp']==0) echo 'd-none';?>">
					<div class="col-sm-2 col-form-label text-sm-right pr-0">
						<label class="mb-0" for="state"><?php echo T_('État'); ?> :</label>
					</div>
					<div class="col-sm-5">
						<select class="form-control <?php if($mobile){echo 'col-7';} else {echo 'col-5';} ?> d-inline-block" id="state"  name="state" <?php if(!$rright['ticket_state'] || $lock_tech==1 || $ticket_observer) echo 'disabled';?> >	
							<?php
							//selected value
							if($_POST['state'])
							{
								$qry=$db->prepare("SELECT `name` FROM `tstates` WHERE `id`=:id");
								$qry->execute(array('id' => $_POST['state']));
								$row=$qry->fetch();
								$qry->closeCursor();
								echo '<option value="'.$_POST['state'].'" selected >'.T_($row['name']).'</option>';
								$selected_state=$_POST['state'];
							} else {
								$qry=$db->prepare("SELECT `name` FROM `tstates` WHERE `id`=:id");
								$qry->execute(array('id' => $globalrow['state']));
								$row=$qry->fetch();
								$qry->closeCursor();
								echo '<option value="'.$globalrow['state'].'" selected >'.T_($row['name']).'</option>';
								$selected_state=$globalrow['state'];
							}

							//case super with state modification, display wait pec #6517
							if($_GET['action']=='new' && $_SESSION['profile_id']==3 && $_POST['state']==5)
							{
								$globalrow['state']=$_POST['state'];
							}

							$qry=$db->prepare("SELECT `id`,`name` FROM `tstates` WHERE `id`!=:id1 AND `id`!=:id2 ORDER BY `number`");
							$qry->execute(array('id1' => $_POST['state'],'id2' => $globalrow['state']));
							while($row=$qry->fetch()) 
							{
								if($_SESSION['profile_id']==2 && $row['id']==3){}  //special case to hide resolve state for user only
								else {echo '<option value="'.$row['id'].'">'.T_($row['name']).'</option>';}
							}
							$qry->closeCursor();
							?>
						</select>
						<?php
						//send value in lock select case 
						if($rright['ticket_state']==0 || $lock_tech==1) {echo '<input type="hidden" name="state" value="'.$selected_state.'" />';}
						
						//display state icon
						$qry=$db->prepare("SELECT `display`,`description`,`icon` FROM `tstates` WHERE `id`=:id");
						$qry->execute(array('id' => $globalrow['state']));
						$row=$qry->fetch();
						$qry->closeCursor();
						echo '&nbsp;<span class="'.$row['display'].'" title="'.T_($row['description']).'">';
						if($row['icon']) {echo '<i class="fa '.$row['icon'].'"><!-- --></i>';} else {echo '&nbsp;';}
						echo '</span>';
						?>
					</div>
				</div>
				<!-- END state part -->
				
				<?php
				//if user validation is enable display field
				if($rparameters['user_validation'] && $rparameters['user_validation_perimeter']=='mark' && $rright['ticket_user_validation'])
				{
					echo '
					<!-- START user validation part -->
					<input type="hidden" id="user_validation_delay_parameters" value="'.$rparameters['user_validation_delay'].'" />
					<div id="user_validation_section" class="form-group row">
						<div class="col-sm-2 col-form-label text-sm-right pr-0">
							<label class="mb-0" for="user_validation">'.T_('Validation demandeur').' :</label>
						</div>
						<div class="col-sm-5">
							<label>
								<input type="radio" class="ace mt-2" name="user_validation" value="1" '; if($globalrow['user_validation']) echo 'checked'; echo '> <span class="lbl"> Oui </span>
								<input type="radio" class="ace mt-2" name="user_validation" value="0" '; if(!$globalrow['user_validation']) echo 'checked'; echo '> <span class="lbl"> Non </span>
							</label>
							<input type="text" title="'.T_('Date à laquelle, la notification par mail sera émise au demandeur').'" class="form-control '; if($mobile){echo 'col-7';} else {echo 'col-5';} echo ' datetimepicker-input" id="user_validation_date" data-toggle="datetimepicker" data-target="#user_validation_date" autocomplete="off" name="user_validation_date"  
							value="'; if($_POST['user_validation_date']) {echo $_POST['user_validation_date'];} elseif($globalrow['user_validation_date'] && $globalrow['user_validation_date']!='0000-00-00') {echo DateToDisplay($globalrow['user_validation_date']);} echo '" 
							/>
						</div>
					</div>
					<!-- END user validation part -->
					';
				} else {
					echo '<input type="hidden" id="user_validation_date" name="user_validation_date" value="'.$globalrow['user_validation_date'].'" /> ';
				}
				?>
				<!-- START plugins part --> 
				<?php
				$section='ticket_form';
				include('plugin.php');
				?>
				<!-- START plugins part --> 
				
				<!-- START buttons -->
				<div id="bottom_button" class="border-t-1 brc-secondary-l1 bgc-secondary-l3 py-3 text-center">
					<?php
					if(!$hide_button)
					{
						if(($rright['ticket_save']!=0 && $_GET['action']!='new') || ($rright['ticket_new_save']!=0 && $_GET['action']=='new'))
						{
							echo '
							<button title="CTRL+S" accesskey="s" name="modify" id="modify" value="modify" type="submit" class="btn btn-secondary btn-success">
								<i class="fa fa-save"><!----></i> 
								'; 
								if(!$mobile) {echo '&nbsp;'.T_('Enregistrer');}
								echo '
							</button>
							&nbsp;
							';
						}
						if($rright['ticket_save_close']!=0)
						{
							echo '
							<button title="ALT+SHIFT+f" accesskey="f" name="quit" id="quit" value="quit" type="submit" class="btn btn-secondary btn-purple">
								<i class="fa fa-save"><!----></i> 
								'; 
								if(!$mobile) {echo '&nbsp;'.T_('Enregistrer et Fermer');}
								echo '
							</button>
							&nbsp;
							';
						}
						if($rright['ticket_new_send']!=0 && $_GET['action']=='new')
						{
							echo '
							<button name="send" id="send" value="send" type="submit" class="btn btn-secondary btn-success">
								'; 
								if(!$mobile) {echo T_('Envoyer').'&nbsp;';}
								echo '
								<i class="fa fa-arrow-right"><!----></i> 
							</button>
							&nbsp;
							';
						}
						if($rright['ticket_close'] && $_POST['state']!='3' && $globalrow['state']!='3' && $globalrow['state']!='4' && $_GET['action']!='new' && $lock_tech==0 && !$ticket_observer)
						{
							echo '
							<button name="close" id="close" value="close" type="submit" class="btn btn-secondary btn-grey">
								<i class="fa fa-check"><!----></i> 
								'; 
								if(!$mobile) {echo '&nbsp;'.T_('Clôturer le ticket');}
								echo '
							</button>
							&nbsp;
							';
						}
						if($rright['ticket_reopen']!=0 && ($globalrow['state']=='3' || $globalrow['state']=='4') && $_GET['action']!='new' && $lock_tech==0)
						{
							echo '
							<button name="reopen" id="reopen" value="reopen" type="submit" class="btn btn-secondary btn-grey">
								<i class="fa fa-redo-alt"><!----></i> 
								'; 
								if(!$mobile) {echo '&nbsp;'.T_('Rouvrir le ticket');}
								echo '
							</button>
							&nbsp;
							';
						}
						if($rright['ticket_send_mail'])
						{
							echo '
							<button title="ALT+SHIFT+m" accesskey="m" name="mail" id="mail" value="mail" type="submit" class="btn btn-secondary btn-info">
								<i class="fa fa-envelope"><!----></i> 
								'; 
								if(!$mobile) {echo '&nbsp;'.T_('Envoyer un mail');}
								echo '
							</button>
							&nbsp;
							';
						}
						if($rright['ticket_cancel'])
						{
							echo '
							<button title="ALT+SHIFT+c" accesskey="c" name="cancel" id="cancel" value="cancel" type="submit" class="btn btn-secondary btn-danger" formnovalidate>
								<i class="fa fa-times"><!----></i> 
								'; 
								if(!$mobile) {echo '&nbsp;'.T_('Annuler');}
								echo '
							</button>
							';
						}
					}
					?>
				</div>
				<!-- END buttons -->
			</div> <!-- div end p-3 -->
		</div> <!-- div end body card -->
	</form>
</div> <!-- div end card -->


<!-- datetime picker scripts  -->
<script type="text/javascript" src="./vendor/moment/moment/min/moment.min.js"></script>
<?php 
	if($ruser['language']=='fr_FR') {echo '<script src="./vendor/moment/moment/locale/fr.js" charset="UTF-8"></script>';} 
	if($ruser['language']=='de_DE') {echo '<script src="./vendor/moment/moment/locale/de.js" charset="UTF-8"></script>';} 
	if($ruser['language']=='es_ES') {echo '<script src="./vendor/moment/moment/locale/es.js" charset="UTF-8"></script>';} 
	if($ruser['language']=='it_IT') {echo '<script src="./vendor/moment/moment/locale/it.js" charset="UTF-8"></script>';} 
?>
<script src="./vendor/components/tempusdominus/bootstrap-4/build/js/tempusdominus-bootstrap-4.min.js" charset="UTF-8"></script> 

<?php
	//call mandatory script to update fields color
	if(
		$rright['ticket_user_mandatory'] ||
		$rright['ticket_tech_mandatory'] ||
		$rright['ticket_criticality_mandatory'] ||
		$rright['ticket_priority_mandatory'] ||
		$rright['ticket_date_hope_mandatory'] ||
		$rright['ticket_description_mandatory'] ||
		$rright['ticket_title_mandatory'] ||
		$rright['ticket_agency_mandatory'] || 
		$rright['ticket_asset_mandatory'] ||
		$rright['ticket_cat_mandatory'] ||
		$rright['ticket_tech_mandatory'] || 
		$rright['ticket_service_mandatory'] || 
		$rright['ticket_place_mandatory'] || 
		$rright['ticket_type_mandatory'] 
	) {require('includes/ticket_mandatory.php');}
?>

<!-- tickets scripts  -->
<script type="text/javascript" src="js/ticket.js"></script>

<!-- START plugins part --> 
<?php
$section='ticket_js';
include('plugin.php');
?>
<!-- END plugins part --> 
<script type="text/javascript">
	jQuery(function($) {
		//get and display user informations
			//read informations of current field
			var e = document.getElementById("user");
			var user = e.options[e.selectedIndex].value;
			var edit_user_btn_exist = document.getElementById("edit_user_btn");
			var token="<?php echo $token; ?>";

			//get user information if exist
			if(user!=0){
				if(edit_user_btn_exist)
				{
					edit_user_btn.classList.remove("invisible");
					edit_user_btn.classList.add("visible");
				}
				GetUserInfos(user);
			} else {
				if(edit_user_btn_exist)
				{
					edit_user_btn.classList.remove("visible");
					edit_user_btn.classList.add("invisible");
				}
			}
			//case switch by user
			$('#user').change(function(){
				//console.info('user switch detected');
				var user = $(this).val(); 
				if(user!=0){
					if(edit_user_btn_exist)
					{
						edit_user_btn.classList.remove("invisible");
						edit_user_btn.classList.add("visible");
					}
					GetUserInfos(user);
				} else {
					if(edit_user_btn_exist)
					{
						edit_user_btn.classList.remove("visible");
						edit_user_btn.classList.add("invisible");
					}
				}
			});	
			//function to add user information to current page
			function GetUserInfos(user) {
				var dataString = "user="+user; 
				$.ajax({ 
					type: "POST", 
					url: "ajax/ticket_userinfos.php?token=<?php echo $token; ?>&user_id=<?php echo $_SESSION['user_id']; ?>&ticket=<?php echo $_GET['id']; ?>", 
					data: dataString, 
					success: function(result){
						//console.log('JSON received :', result)
						var data = JSON.parse(result);
						//display user information
						if(data.phone) {$("#user_phone").html('&nbsp;&nbsp;<a href="tel:'+data.phone+'"><i title="<?php echo T_('Téléphoner au'); ?> '+data.phone+'" class="fa fa-phone text-info"><!----></i></a> '+data.phone);} else {$("#user_phone").html('');}
						if(data.mobile) {$("#user_mobile").html('&nbsp;&nbsp;<a href="tel:'+data.mobile+'"><i title="<?php echo T_('Téléphoner au'); ?> '+data.mobile+'" class="fa fa-mobile text-info"><!----></i></a> '+data.mobile);} else {$("#user_mobile").html('');}
						if(data.mail) {$("#user_mail").html('&nbsp;&nbsp;<a href="mailto:'+data.mail+'"><i title="<?php echo T_('Envoyer un mail sur'); ?> '+data.mail+'" class="fa fa-envelope text-info"><!----></i></a>');} else {$("#user_mail").html('');}
						if(data.function) {$("#user_function").html('&nbsp;&nbsp;<a href=""><i title="<?php echo T_('Fonction'); ?> '+data.function+'" class="fa fa-user text-info"><!----></i></a> '+data.function);} else {$("#user_function").html('');}
						if(data.service) {$("#user_service").html('&nbsp;&nbsp;<a href=""><i title="<?php echo T_('Service'); ?> '+data.service+'" class="fa fa-users text-info"><!----></i></a> '+data.service);} else {$("#user_service").html('');}
						if(data.agency) {$("#user_agency").html('&nbsp;&nbsp;<a href=""><i title="<?php echo T_('Agence'); ?> '+data.agency+'" class="fa fa-globe text-info"><!----></i></a> '+data.agency);} else {$("#user_agency").html('');}
						if(data.company) {$("#user_company_name").html('&nbsp;&nbsp;<a href=""><i title="<?php echo T_('Société'); ?> '+data.company+'" class="fa fa-building text-info"><!----></i></a> '+data.company);} else {$("#user_company_name").html('');}
						if(data.company_comment) {$("#user_company_comment").html('&nbsp;&nbsp;<a href=""><i title="<?php echo T_('Commentaire société'); ?> : '+data.company_comment+'" class="fa fa-comment text-info"><!----></i></a> '+data.company_comment);} else {$("#user_company_comment").html('');}
						if(data.other_ticket) {$("#user_other_ticket").html('&nbsp;&nbsp;<a href=""><i title="<?php echo T_('Autres tickets de cet utilisateur'); ?>" class="fa fa-ticket text-info"><!----></i></a>'+data.other_ticket);} else {$("#user_other_ticket").html('');}
						if(data.asset_id) {$("#user_asset").html('&nbsp;&nbsp;&nbsp;<a target="_blank" href="./index.php?page=asset&id='+data.asset_id+'"><i title="<?php echo T_('Équipement associé'); ?>" class="fa fa-desktop text-info"><!----></i></a> '+data.asset_netbios);} else {$("#user_asset").html('');}
						if(data.ticket_remaining) {$("#user_ticket_remaining").html('&nbsp;&nbsp;&nbsp;<a href=""><i title="<?php echo T_('Tickets restants'); ?>" class="fa fa-tachometer-alt text-info"><!----></i></a> '+data.ticket_remaining);} else {$("#user_ticket_remaining").html('');}
						if(data.hour_remaining) {$("#user_hour_remaining").html('&nbsp;&nbsp;&nbsp;<a href=""><i title="<?php echo T_('Heures restantes'); ?>" class="fa fa-tachometer-alt text-info"><!----></i></a> '+data.hour_remaining+'h');} else {$("#user_hour_remaining").html('');}
						if(data.custom1) {$("#user_custom1").html('&nbsp;&nbsp;&nbsp;<a href=""><i title="<?php echo T_('Champ personnalisé 1'); ?>" class="fa fa-info-circle text-info"><!----></i></a> '+data.custom1);} else {$("#user_custom1").html('');}
						if(data.custom2) {$("#user_custom2").html('&nbsp;&nbsp;&nbsp;<a href=""><i title="<?php echo T_('Champ personnalisé 2'); ?>" class="fa fa-info-circle text-info"><!----></i></a> '+data.custom2);} else {$("#user_custom2").html('');}
						$('#user_warning').css('display', 'none');
						//get sender service for ticket creation
						if(data.service_id) {document.getElementById('sender_service').value = data.service_id;}
						//complete edit user form
						if(document.getElementById("user_id_edit") != null)
						{
							if(data.user_id) {document.getElementById('user_id_edit').value = data.user_id;}
						}
						if(document.getElementById("firstname_edit") != null)
						{
							if(data.firstname) {document.getElementById('firstname_edit').value = data.firstname;} else {document.getElementById('firstname_edit').value = '';}
						}
						if(document.getElementById("lastname_edit") != null)
						{
							if(data.lastname) {document.getElementById('lastname_edit').value = data.lastname;} else {document.getElementById('lastname_edit').value = '';}
						}
						if(document.getElementById("phone_edit") != null)
						{
							if(data.phone) {document.getElementById('phone_edit').value = data.phone;} else {document.getElementById('phone_edit').value = '';}
						}
						if(document.getElementById("mobile_edit") != null)
						{
							if(data.mobile) {document.getElementById('mobile_edit').value = data.mobile;} else {document.getElementById('mobile_edit').value = '';}
						}
						if(document.getElementById("usermail_edit") != null)
						{
							if(data.mail) {document.getElementById('usermail_edit').value = data.mail;} else {document.getElementById('usermail_edit').value = '';}
						}
						if(document.getElementById("company_edit") != null)
						{
							if(data.company_id) {document.getElementById('company_edit').value=data.company_id;}
						}
					}
				});
			}
		
		//update asset list on user change
		if (myform.asset_id != undefined) {
			$('#user').change(function(){ 
				var UserSelected = $(this).val(); //get user id value
				//console.log('case switch user '+UserSelected);
				$("#asset_id").empty();
				//replace asset field data with associated data
				$.ajax({
					<?php
					//filter asset by user only
					if($rright['ticket_asset_user_only']){echo 'url:"ajax/ticket_asset_user_only.php",';} else {echo 'url:"ajax/ticket_asset.php",';}
					?>
					type:"post",
					data: {UserId: UserSelected, token:token},
					async:true,
					success: function(result) {
						var data = JSON.parse(result);
						if($.trim(data)) //if data
						{
							//reset and populate asset field
							$("#asset_id").empty();
							jQuery.each(data, function(index, value){
								if(value['netbios'])
								{
									$("#asset_id").append("<option value='"+value['id']+"' "+value['selected']+">"+value['netbios']+"</option>");
								}
							});
						}
					},
					error: function() {
						console.log('ERROR : unable to get asset for selected user '+UserSelected)
					}
				});
			})
		}

		//update asset name list on asset type change
		if (myform.asset_type != undefined) {
			$('#asset_type').change(function(){ 
				var TypeSelected = $(this).val(); //get type id value
				var UserSelected = $("#user").val(); 
				$("#asset_id").empty();
				//replace asset field data with associated data
				$.ajax({
					<?php
					//filter asset by user only
					if($rright['ticket_asset_user_only']){echo 'url:"ajax/ticket_asset_type_user_only.php",';} else {echo 'url:"ajax/ticket_asset_type.php",';}
					?>
					type:"post",
					data: {TypeId: TypeSelected,UserId: UserSelected,token:token},
					async:true,
					success: function(result) {
						var data = JSON.parse(result);
						if($.trim(data)) //if data
						{
							//reset and populate asset field
							$("#asset_id").empty();
							jQuery.each(data, function(index, value){
								if(value['netbios'])
								{
									$("#asset_id").append("<option value='"+value['id']+"' "+value['selected']+">"+value['netbios']+"</option>");
								}
							});
						}
					},
					error: function() {
						console.log('ERROR : unable to get asset for selected type '+TypeSelected)
					}
				});

				//replace model list with filter values
				if (myform.asset_model != undefined) {
					$.ajax({
						url:"ajax/ticket_asset_type_model.php",
						type:"post",
						data: {TypeId: TypeSelected,token:token},
						async:true,
						success: function(result) {
							var data = JSON.parse(result);
							//console.log(data)
							if($.trim(data)) //if data
							{
								//reset and populate asset field
								$("#asset_model").empty();
								jQuery.each(data, function(index, value){
									if(value['name'])
									{
										$("#asset_model").append("<option value='"+value['id']+"' "+value['selected']+">"+value['name']+"</option>");
									}
								});
							}
						},
						error: function() {
							console.log('ERROR : unable to get model for selected type '+TypeSelected)
						}
					});
				}
			})
		}

		//update asset name on model switch
		if (myform.asset_type != undefined) {
			$('#asset_model').change(function(){ 
				var ModelSelected = $(this).val(); //get type id value
				$("#asset_id").empty();
				//replace asset field data with associated data
				$.ajax({
					url:"ajax/ticket_asset_model.php",
					type:"post",
					data: {ModelId: ModelSelected,token:token},
					async:true,
					success: function(result) {
						var data = JSON.parse(result);
						if($.trim(data)) //if data
						{
							//reset and populate asset field
							$("#asset_id").empty();
							jQuery.each(data, function(index, value){
								if(value['netbios'])
								{
									$("#asset_id").append("<option value='"+value['id']+"' "+value['selected']+">"+value['netbios']+"</option>");
								}
							});
						}
					},
					error: function() {
						console.log('ERROR : unable to get asset for selected model '+TypeSelected)
					}
				});

				
			})
		}

	});
</script>		