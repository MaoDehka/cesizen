<?php
################################################################################
# @Name : project.php
# @Description : display project
# @Call : /index.php
# @Parameters : 
# @Author : Flox
# @Create : 26/01/2019
# @Update : 30/01/2024
# @Version : 3.2.47
################################################################################

//secure direct access
if(!isset($_GET['page'])) {echo 'ERROR : invalid access'; exit;}

//initialize variables 
if(!isset($_POST['name'])) $_POST['name'] = '';
if(!isset($_POST['save'])) $_POST['save'] = '';
if(!isset($_POST['return'])) $_POST['return'] = '';
if(!isset($_POST['task_add'])) $_POST['task_add'] = '';

if($rright['project'])
{
	if($_POST['return'])
	{
		//redirect
		$www = "./index.php?page=project";
		echo "<SCRIPT LANGUAGE='JavaScript'>
			<!--
			function redirect()
			{
			window.location='$www'
			}
			setTimeout('redirect()');
			-->
			</SCRIPT>";
	}
	//delete project
	if($_GET['action']=='delete' && $_GET['id']) 
	{
		$qry=$db->prepare("UPDATE `tprojects` SET `disable`=1 WHERE `id`=:id");
		$qry->execute(array('id' => $_GET['id']));
	}
	//delete task
	if($_GET['task_action']=='delete' && $_GET['task_id']) 
	{
		$qry=$db->prepare("DELETE FROM `tprojects_task` WHERE `id`=:id");
		$qry->execute(array('id' => $_GET['task_id']));
	}
	//add task
	if($_POST['task_add'] && $_GET['id'] && $_POST['add_task_number'] && $_POST['add_ticket_number'])
	{
		//secure string
		$_POST['add_task_number']=strip_tags($_POST['add_task_number']);
		$_POST['add_ticket_number']=strip_tags($_POST['add_ticket_number']);
			
		$qry=$db->prepare("INSERT INTO `tprojects_task` (`number`,`project_id`,`ticket_id`) VALUES (:number,:project_id,:ticket_id)");
		$qry->execute(array('number' => $_POST['add_task_number'],'project_id' => $_GET['id'],'ticket_id' => $_POST['add_ticket_number']));
	}
	//////////////////////////////////////////////////////////// add project
	if($_GET['action']=='add')
	{
		if($_POST['save'])
		{
			//secure string
			$_POST['name']=strip_tags($_POST['name']);
			
			$qry=$db->prepare("INSERT INTO `tprojects` (`name`) VALUES (:name)");
			$qry->execute(array('name' => $_POST['name']));
			$project_id=$db->lastInsertId();
			
			//display action message
			echo DisplayMessage('success',T_('Le projet a été crée'));
			//redirect
			$www = "./index.php?page=project&id=$project_id&action=edit";
			echo "<SCRIPT LANGUAGE='JavaScript'>
				<!--
				function redirect()
				{
				window.location='$www'
				}
				setTimeout('redirect()',$rparameters[time_display_msg]);
				-->
				</SCRIPT>";
		}
		///////////////////////////add project
		echo '
		<div class="card bcard shadow" id="card-1">
			<div class="card-header">
				<h5 class="card-title">
					<i class="fa fa-tasks text-primary-m2"><!----></i> '.T_("Ajout d'un projet").'
				</h5>
			</div><!-- /.card-header -->
			<div class="card-body p-0">
				<!-- to have smooth .card toggling, it should have zero padding -->
				<div class="p-3">
					<form method="POST" enctype="multipart/form-data" name="myform" id="myform" action="" >
						<label for="name">'.T_('Nom du projet').' :</label>
						<input type="text" style="width:auto;" class="form-control form-control-sm d-inline-block" name="name" id="name" maxlength="50" value="'; echo $_POST['name']; echo '" autocomplete="off">
						<br />
						<br />
						<div class="border-t-1 brc-secondary-l1 bgc-secondary-l3 py-3 text-center mt-5">
							<button name="save" value="save" id="save" type="submit" class="btn btn-success mr-2">
								<i class="fa fa-save bigger-110"><!----></i>
								'.T_('Sauvegarder').'
							</button>
							<button name="return" value="return" id="return" type="submit" class="btn btn-danger">
								<i class="fa fa-undo bigger-110"><!----></i>
								'.T_('Retour').'
							</button>
						</div>
					</form>
				</div>
			</div><!-- /.card-body -->
		</div>
		';
		
	} elseif($_GET['action']=='edit') //////////////////////////////////////////////////////////// edit project
	{
		
		if($_POST['save'] && $_GET['id'])
		{
			//secure string
			$_POST['name']=strip_tags($_POST['name']);
			
			$qry=$db->prepare("UPDATE `tprojects` SET `name`=:name WHERE `id`=:id");
			$qry->execute(array('id' => $_GET['id'], 'name' => $_POST['name']));
			
			//display action message
			echo DisplayMessage('success',T_('Le projet a été mis à jour'));
			//redirect
			$www = "./index.php?page=project&id=$_GET[id]&action=$_GET[action]";
			echo "<SCRIPT LANGUAGE='JavaScript'>
				<!--
				function redirect()
				{
				window.location='$www'
				}
				setTimeout('redirect()',$rparameters[time_display_msg]);
				-->
				</SCRIPT>";
		}
		
		//get project data
		$qry=$db->prepare("SELECT `name` FROM `tprojects` WHERE id=:id");
		$qry->execute(array('id' => $_GET['id']));
		$project=$qry->fetch();
		$qry->closeCursor();
		
		echo '
		<div class="card bcard shadow" id="card-2">
			<div class="card-header">
				<h5 class="card-title">
				<i class="fa fa-tasks text-primary-m2"><!----></i> '.T_("Modification du projet").' : '.$project['name'].'
				</h5>
			</div><!-- /.card-header -->
			<div class="card-body p-0">
				<!-- to have smooth .card toggling, it should have zero padding -->
				<div class="p-3">
					<form method="POST" enctype="multipart/form-data" name="myform" id="myform" action="" >
						<label for="name">'.T_('Nom du projet').' :</label>
						<input type="text" style="width:auto;" class="form-control form-control-sm d-inline-block" maxlength="50" name="name" id="name" value="'; echo $project['name']; echo '" autocomplete="off">
						<div class="pt-3"></div>
						<span for="tasks">'.T_('Liste des tâches de ce projet').' :</span>
						<br />
						';
						//list tasks
						$qry=$db->prepare("SELECT `id`,`number`,`project_id`,`ticket_id` FROM `tprojects_task` WHERE `project_id`=:project_id ORDER BY `number`");
						$qry->execute(array('project_id' => $_GET['id']));
						while($task=$qry->fetch()) 
						{
							//get ticket data
							$qry2=$db->prepare("SELECT `title` FROM `tincidents` WHERE id=:id");
							$qry2->execute(array('id' => $task['ticket_id']));
							$ticket=$qry2->fetch();
							$qry2->closeCursor();
							if(empty($ticket['title'])) {$ticket['title']='';}
							
							echo '<i class="fa fa-circle text-success pl-2"><!----></i> <b>'.T_('Tâche n°').' '.$task['number'].' :</b> '.T_('Ticket').' '.$task['ticket_id'].' ('.$ticket['title'].')';
							echo '<a href="./index.php?page=project&amp;id='.$_GET['id'].'&amp;action=edit&amp;task_id='.$task['id'].'&amp;task_action=delete" onClick="javascript: return confirm(\''.T_('Êtes-vous sur de vouloir supprimer cette tâche ?').'\');" ><i title="'.T_('Supprimer cette tâche').'" class="fa fa-trash text-danger pl-1"><!----></i></a>';
							echo '<br />';
						}
						$qry->closeCursor();
						//add task
						echo '<div class="pt-2"></div>';
						echo '<i class="fa fa-plus text-success pl-2"><!----></i> '.T_('Tâche n°').'&nbsp;<input type="text" style="width:auto;" class="form-control form-control-sm d-inline-block" size="2" name="add_task_number" value="">';
						echo '&nbsp;'.T_('ticket n°').' <input type="text" style="width:auto;" class="form-control form-control-sm d-inline-block" size="4" name="add_ticket_number" value="">';
						echo '&nbsp;&nbsp;<button class="btn btn-xs btn-success" title="'.T_('Ajouter').'" id="task_add" name="task_add" value="task_add" type="submit" ><i class="fa fa-check"><!----></i></button>';
						echo '
						<div class="border-t-1 brc-secondary-l1 bgc-secondary-l3 py-3 text-center mt-5">
							<button name="save" value="save" id="save" type="submit" class="btn btn-success mr-2">
								<i class="fa fa-save bigger-110"><!----></i>
								'.T_('Sauvegarder').'
							</button>
							<button name="return" value="return" id="return" type="submit" class="btn btn-danger">
								<i class="fa fa-undo bigger-110"><!----></i>
								'.T_('Retour').'
							</button>
						</div>
					</form>
				</div>
			</div><!-- /.card-body -->
		</div>
		';
	} else { //////////////////////////////////////////////////////////// list projects
		$qry=$db->prepare("SELECT COUNT(id) FROM `tprojects` WHERE `disable`=0");
		$qry->execute();
		$row=$qry->fetch();
		$qry->closeCursor();
		echo '
			<div class="page-header position-relative">
				<h1 class="page-title text-primary-m2">
					<i class="fa fa-tasks text-primary-m2"><!----></i> '.T_('Liste des projets').'
					<small class="page-info text-secondary-d2">
						<i class="fa fa-angle-double-right text-80"><!----></i>
						&nbsp;'.T_('Nombre').' : '.$row[0].' &nbsp;&nbsp;
					</small>
				</h1>
			</div>
		';
		
		//for each project
		$qry=$db->prepare("SELECT `id`,`name` FROM `tprojects` WHERE `disable`=0 ORDER BY `id` DESC");
		$qry->execute();
		while($project=$qry->fetch()) 
		{
			//check finish project
			$finish=1;
			$qry2=$db->prepare("SELECT `tincidents`.state FROM `tprojects_task`,`tincidents` WHERE `tprojects_task`.`ticket_id`=`tincidents`.`id` AND `tincidents`.`disable`=0 AND `tprojects_task`.project_id=:project_id");
			$qry2->execute(array('project_id' => $project['id']));
			while($row2=$qry2->fetch()) 
			{
				if($row2['state']!=3) {$finish=0;}
			}
			$qry2->closeCursor();
			if($finish==1) {$flag='text-success';} else {$flag='text-warning';}
			
			echo '
			<div class="cards-container" id="card-container-'.$project['id'].'">
				<div class="card bcard shadow" id="card-'.$project['id'].'">
					<div class="card-header">
						<h5 class="card-title">
							<i class="fa fa-flag '.$flag.'"><!----></i> '.T_("Projet").' n°'.$project['id'].' : '.$project['name'].'
						</h5>
						<div class="card-toolbar">
							<a href="./index.php?page=project&amp;id='.$project['id'].'&amp;action=delete" onClick="javascript: return confirm(\''.T_('Êtes-vous sur de vouloir supprimer ce projet ?').'\');" >
								<i style="float:right; margin:5px;" title="'.T_('Supprimer ce projet').'" class="fa fa-trash text-danger "><!----></i>
							</a>
							<a href="./index.php?page=project&amp;id='.$project['id'].'&amp;action=edit" >
								<i style="float:right; margin:5px;" title="'.T_('Modifier ce projet').'" class="fa fa-pencil-alt text-warning "><!----></i>
							</a>
						</div>
					</div><!-- /.card-header -->
					<div class="card-body p-0">
						<div class="pt-3 pb-3">
							<div class="px-1 px-md-2 px-lg-3 ml-4" id="profile-tab-timeline">
								<div class="px-1 px-lg-0 text-grey-m1 text-95">
									<div class="mt-1 pl-1 pos-rel">
										<div class="position-tl h-100 border-l-2 brc-secondary-l1 ml-2 ml-lg-25"></div>
											';
											$qry2=$db->prepare("SELECT `number`,`ticket_id` FROM `tprojects_task` WHERE `project_id`=:project_id ORDER by `number`");
											$qry2->execute(array('project_id' => $project['id']));
											while($task=$qry2->fetch()) 
											{
												//get ticket data
												$qry3=$db->prepare("SELECT `id`,`title`,`state`,`date_hope`,`date_res` FROM `tincidents` WHERE id=:id AND disable='0'");
												$qry3->execute(array('id' => $task['ticket_id']));
												$ticket=$qry3->fetch();
												$qry3->closeCursor();

												//check if ticket exist
												if(empty($ticket['id'])){echo DisplayMessage('error',T_("Le ticket").' '.$task['ticket_id'].' '.T_("sélectionné pour ce projet n'existe pas")); break;}
												
												//check if event exist with this ticket
												$qry3=$db->prepare("SELECT `date_start`,`date_end` FROM `tevents` WHERE incident=:incident AND type=2");
												$qry3->execute(array('incident' => $task['ticket_id']));
												$event=$qry3->fetch();
												$qry3->closeCursor();
												
												if($event)
												{
													//convert datetime
													if($event['date_start'] && $event['date_start']!='0000-00-00 00:00:00')
													{
														$date_start=DateTime::createFromFormat('Y-m-d H:i:s',$event['date_start']);
														$date_start=$date_start->format('d/m/Y');
													}  else {$date_start='';}
													
													if($event['date_end'] && $event['date_end']!='0000-00-00 00:00:00')
													{
														$date_end=DateTime::createFromFormat('Y-m-d H:i:s',$event['date_end']);
														$date_end=$date_end->format('d/m/Y');
													} else {$date_end='';}
													
													if($ticket['state']=='3') {
														$class='success';
														$date=T_('Résolu le').' : '.$date_end;
														$date_label=T_('Date de résolution du ticket');
													} else {
														$class='warning';
														$date=T_('Planifié le').' : '.$date_start;
														$date_label=T_('Date de résolution estimée du ticket');
													}
												} else {
													//convert datetime
													if(!empty($ticket['date_res']) && $ticket['date_res']!='0000-00-00 00:00:00')
													{
														$date_res=DateTime::createFromFormat('Y-m-d H:i:s',$ticket['date_res']);
														$date_res=$date_res->format('d/m/Y');
													}  else {$date_res='';}
													
													if(!empty($ticket['date_hope']) && $ticket['date_hope']!='0000-00-00')
													{
														$date_hope=DateTime::createFromFormat('Y-m-d',$ticket['date_hope']);
														$date_hope=$date_hope->format('d/m/Y');
													} else {$date_hope='';}
													
													if($ticket['state']=='3') {
														$class='success';
														$date=T_('Résolu le').' : '.$date_res;
														$date_label=T_('Date de résolution du ticket');
													} else {
														$class='warning';
														$date=T_('Date estimée le').' : '.$date_hope;
														$date_label=T_('Date de résolution estimée du ticket');
													}
												}
												echo '
													<div class="ml-n3 mt-2">
														<button type="button" class="btn btn-lighter-'.$class.' btn-h-'.$class.'  w-5 radius-round px-0" '; if($class!='success'){echo ' title="'.T_('Tâche n°').$task['number'].'"';} echo '>
															';
															if($class=='success'){echo '<i title="'.T_('Tâche n°').$task['number'].'" class="fa fa-check text-success-m1"><!----></i>';} else {echo $task['number'];}
															echo '
														</button>
														<div class="d-inline-block ml-2 text-secondary-m1">
															<a title="'.T_('Ouvrir le ticket associé à cette tâche').'" target="_blank " href="index.php?page=ticket&&amp;id='.$ticket['id'].'">
																<i class="fa fa-ticket text-'.$class.'"><!----></i> '.$ticket['id'].' : '.$ticket['title'].'
															</a> | 
															<i title="'.$date_label.'" class="fa fa-calendar text-'.$class.'"><!----></i> <span class="text-secondary-d2">'.$date.'</span>
														</div>
													</div>
												';
											}
											$qry2->closeCursor();
											echo '
										</div>	
									<div class="mb-4"></div>
								</div>
							</div>
						</div>
					</div><!-- /.card-body -->
				</div>
			</div>
			<br />
			';
		}
		$qry->closeCursor();
	}
} else {
 echo '<div class="alert alert-danger"><i class="fa fa-remove"><!----></i> <strong>'.T_('Erreur').':</strong> '.T_("Vous n'avez pas les droits d'accès à la fonction projet").' </div>';
}
?>