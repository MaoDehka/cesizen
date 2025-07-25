<?php
################################################################################
# @Name : thread.php
# @Call : ticket.php
# @Description : display tickets thread
# @Author : Flox
# @Create : 27/01/2013
# @Update : 30/01/2024
# @Version : 3.2.47
################################################################################

//secure direct access
if(!isset($_GET['page'])) {echo 'ERROR : invalid access'; exit;}

//initialize variables 
if(!isset($rcreator['firstname'])) $rcreator['firstname']= ''; 
if(!isset($rcreator['lastname'])) $rcreator['lastname']= ''; 

//thread delete
if(is_numeric($_GET['threaddelete']) && $rright['ticket_thread_delete_all'])
{
	$qry=$db->prepare("DELETE FROM `tthreads` WHERE id=:id");
	$qry->execute(array('id' => $_GET['threaddelete']));
}elseif(is_numeric($_GET['threaddelete']) && $rright['ticket_thread_delete'])
{
	//check own thread before delete
	$qry=$db->prepare("DELETE FROM `tthreads` WHERE id=:id AND author=:author");
	$qry->execute(array('id' => $_GET['threaddelete'],'author' => $_SESSION['user_id']));
} 

//call date conversion function from index
if($mobile){$date_start=DatetimeToDate($globalrow['date_create']);} else {$date_start=date_convert($globalrow['date_create']);}

//display time line
if($_GET['action']!='new') //case for edit ticket not new ticket
{
	echo '
    <table '; if(!$mobile) {echo 'style="border: 1px solid #D8D8D8; min-width:780px;"';} echo '>
		<tr>
			<td class="p-2">
            <div class="px-1 px-md-2 px-lg-3" id="profile-tab-timeline">
                <div class="px-1 px-lg-0 text-grey-m1 text-95">
                    <div class="mt-1 pl-1 pos-rel">
                        <div class="position-tl h-100 border-l-2 brc-secondary-l1 ml-2 ml-lg-25 mt-2"></div>
                            <div class="ml-n3">
								<button title="'.T_('Création du ticket').'" type="button" class="btn btn-lighter-info btn-h-info  w-5 radius-round px-0">
                                    <i class="fa fa-calendar-plus"><!----></i>
                                </button>
                                <div class="d-inline-block ml-2 text-secondary-m1">
									'.$date_start.' :
                                    <span class="text-blue-d1 text-600">'.T_('Ouverture').'</span>
									';
										if(!$mobile) {
											//check if ticket is open by mail2ticket connector
											$qry=$db->prepare("SELECT `id` FROM `tthreads` WHERE `type`='6' AND `ticket`=:ticket_id");
											$qry->execute(array('ticket_id' => $_GET['id']));
											$mail_create=$qry->fetch();
											$qry->closeCursor();
											if(!empty($mail_create))
											{
												echo ' '.T_('du ticket').' <span style="font-size: x-small;">('.T_('Effectué par mail').')</span>';
											} else {
												$qry=$db->prepare("SELECT `firstname`,`lastname` FROM `tusers` WHERE `id`=:id");
												$qry->execute(array('id' => $globalrow['creator']));
												$rcreator=$qry->fetch();
												$qry->closeCursor();
												echo ' '.T_('du ticket').' <span class="ml-1" style="font-size: x-small;">('.T_('réalisé par').' '; if($rcreator['firstname']) {echo $rcreator['firstname'];} if($rcreator['lastname']) {echo ' '.$rcreator['lastname'];} echo ')</span>';
											}
										}
									echo '
                                </div>
                            </div>
							';
							//for each type of thread display line
							$qry=$db->prepare("SELECT * FROM `tthreads` WHERE ticket=:ticket ORDER BY date");
							$qry->execute(array('ticket' => $_GET['id']));
							while($row=$qry->fetch()) 
							{
								//call date conversion function
								if($mobile){$date_thread=DatetimeToDate($row['date']);} else {$date_thread=date_convert($row['date']);}
								
								//get author name
								$qry2=$db->prepare("SELECT `id`,`firstname`,`lastname`,`profile` FROM `tusers` WHERE id=:id");
								$qry2->execute(array('id' => $row['author']));
								$author=$qry2->fetch();
								$qry2->closeCursor();
								if(empty($author['id'])) {$author['id']=0;}
								if(empty($author['firstname'])) {$author['firstname']='';}
								if(empty($author['lastname'])) {$author['lastname']='';}
								if(empty($author['profile'])) {$author['profile']='';}
								
								//case system modification, remplace name
								if($author['id']==0) {$author['firstname']='GestSup'; $author['lastname']='';}
								
								//state name
								$qry2=$db->prepare("SELECT `name` FROM `tstates` WHERE id=:id");
								$qry2->execute(array('id' => $row['state']));
								$rstate=$qry2->fetch();
								$qry2->closeCursor();
								if(empty($rstate)) {$rstate=array();}
								if(empty($rstate['name'])) {$rstate['name']='';}
								
								//find author profile avatar
								$qry2=$db->prepare("SELECT `tprofiles`.`img` FROM `tprofiles`,`tusers` WHERE `tusers`.`profile`=`tprofiles`.`level` AND `tusers`.`id`=:id");
								$qry2->execute(array('id' => $row['author']));
								$ruserprofile=$qry2->fetch();
								$qry2->closeCursor();
								
								//text thread
								if($row['type']==0)
								{
									//check if user have right to read thread case of private message
									if($rright['ticket_thread_private'] || !$row['private'])
									{
										//define thread color
										if($ruser['skin']!='skin-4') 
										{
											if($author['profile']=='1') {$thread_color='primary';}
											elseif($author['profile']=='2') {$thread_color='primary';}
											else {$thread_color='success';}
										} else {$thread_color='primary';}
										echo '
										<div class="row pos-rel my-0">
											<div class="pt-2 d-flex '; if(!$mobile) {echo 'flex-grow-1';} echo '">
												<div class="mr-3 align-self-start align-self-sm-center mt-2 mt-sm-0 pos-rel">
													<img src="images/avatar/'.$ruserprofile['img'].'" class="radius-round border-2 p-1px brc-primary-m1 bgc-white w-5 h-5">
												</div>
												<div class="media-body py-2 px-3 radius-1 flex-grow-1 bgc-'.$thread_color.'-l5 brc-'.$thread_color.'-l2 border-1">
													<div class="d-flex justify-content-between">
														<span class="pb-2 text-blue-d1 text-95">
															<i class="fa fa-user text-info mr-1"><!----></i><b>'.$author['firstname'].' '.$author['lastname'].'</b>&nbsp;
															';
															if(!$mobile){echo '<i title="'.T_("Date de création de l'élément").'" class="fa fa-calendar text-grey-m2 mr-1"><!----></i><span class="text-grey-m2 text-90">'.$date_thread.'</span>';}
															//display time by response element
															if(!$mobile && $rparameters['ticket_time_response_element'] && $row['time']!=0) {
																//get name of time
																$qry2=$db->prepare("SELECT `name` FROM `ttime` WHERE `min`=:min");
																$qry2->execute(array('min' => $row['time']));
																$time=$qry2->fetch();
																$qry2->closeCursor();
																if(!isset($time['name'])) {$time=array(); $time['name']='';}
																if($time['name']) {$time_element=$time['name'];} else {$time_element=$row['time'].'m';}

																echo '<i title="'.T_('Temps passé pour cet élément').'" class="fa fa-clock text-grey-m2 ml-2 mr-1"><!----></i><span class="text-grey-m2 text-90">'.$time_element.'</span>';
															}
															echo '
														</span>
														<span class="text-90">
															';
															//private thread button
															if($rright['ticket_thread_private']) {
																if($row['private'])
																{
																	echo '<a href="./index.php?page=ticket&id='.$_GET['id'].'&userid='.$_GET['userid'].'&unlock_thread='.$row['id'].'#down"><i title="'.T_('Message non visible pour le demandeur').'" class="mr-1 fa fa-eye-slash text-130 text-danger"><!----></i></a>&nbsp;';
																}else{
																	echo '<a href="./index.php?page=ticket&id='.$_GET['id'].'&userid='.$_GET['userid'].'&lock_thread='.$row['id'].'#down"><i title="'.T_('Message visible pour le demandeur').'" class="mr-1 fa fa-eye text-130 text-success"><!----></i></a>&nbsp;';																			
																}
															} 
															//edit thread button
															if($row['author']==$_SESSION['user_id']) 
															{
																if($rright['ticket_thread_edit']) {echo '<a href="./index.php?page=ticket&id='.$_GET['id'].'&threadedit='.$row['id'].'&userid='.$_GET['userid'].'&state='.$_GET['state'].'&category='.$_GET['category'].'&subcat='.$_GET['subcat'].'&view='.$_GET['view'].'&viewid='.$_GET['viewid'].'#down"><i title="'.T_('Modifier').'" class="mr-1 fa fa-pencil-alt text-warning text-120"><!----></i></a>&nbsp;';}
															}else{
																if($rright['ticket_thread_edit_all']) {echo '<a href="./index.php?page=ticket&id='.$_GET['id'].'&threadedit='.$row['id'].'&userid='.$_GET['userid'].'&state='.$_GET['state'].'&category='.$_GET['category'].'&subcat='.$_GET['subcat'].'&view='.$_GET['view'].'&viewid='.$_GET['viewid'].'#down"><i title="'.T_('Modifier').'" class="mr-1 fa fa-pencil-alt text-warning text-120"><!----></i></a>&nbsp;';}
															}
															//delete thread button
															if($rright['ticket_thread_delete_all'] || ($rright['ticket_thread_delete'] && $row['author']==$_SESSION['user_id'])) {echo '<a onClick="javascript: return confirm(\''.T_('Êtes-vous sur de vouloir supprimer ce texte ?').'\');" href="./index.php?page=ticket&id='.$_GET['id'].'&threaddelete='.$row['id'].'&userid='.$_GET['userid'].'&state='.$_GET['state'].'&category='.$_GET['category'].'&subcat='.$_GET['subcat'].'&view='.$_GET['view'].'&viewid='.$_GET['viewid'].'#down"><i title="'.T_('Supprimer').'" class="fa fa-trash text-danger text-120"><!----></i></a>';}
															echo '
														</span>
													</div>
													<div '; if($mobile) {echo 'style="width:210px"';} else {echo 'style="max-width:1200px"';} echo ' class="text-dark-m2 text-90">
														';
														//insert html link if http is detected in text
														if((preg_match('#http://#',$row['text']) || preg_match('#https://#',$row['text'])) && !preg_match('#href#',$row['text']) && !preg_match('#xmlns:v="urn:schemas-microsoft-com:vml"#',$row['text']))
														{
															$url = '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i'; 
															$row['text'] = preg_replace($url, '<a href="$0" target="_blank" title="$0">$0</a>', $row['text']);
														}
														//set max image width, if not already resize
														if(!preg_match('/style="width/',$row['text'])){$row['text']=str_replace('<img ','<img style="max-width:1200px;" ',$row['text']);}
														
														echo $row['text'];
														echo '
													</div>
												</div>
											</div>
										</div>
										';
									}
								}
								//attribution thread
								if($row['type']==1 && !$rright['ticket_resolution_text_only'])
								{
									if($row['group1'])
									{
										//find group name 
										$qry2=$db->prepare("SELECT `name` FROM `tgroups` WHERE id=:id");
										$qry2->execute(array('id' => $row['group1']));
										$rgroup=$qry2->fetch();
										$qry2->closeCursor();
										
										$name=T_('au groupe').' <b>'.$rgroup['name'].'</b>';
									} else {
										//find technician name 
										$qry2=$db->prepare("SELECT `firstname`,`lastname` FROM `tusers` WHERE id=:id");
										$qry2->execute(array('id' => $row['tech1']));
										$rtech=$qry2->fetch();
										$qry2->closeCursor();

										if(empty($rtech['firstname'])) {$rtech['firstname']='';}
										if(empty($rtech['lastname'])) {$rtech['lastname']='';}
										
										$name=T_('au technicien ').' <b>'.$rtech['firstname'].' '.$rtech['lastname'].'</b>';
									}
									echo '
									<div class="ml-n3 mt-2">
										<button title="'.T_('Attribution du ticket à un technicien').'" type="button" class="btn btn-lighter-purple btn-h-purple  w-5 radius-round px-0">
											<i class="fa fa-user"><!----></i>
										</button>
										<div class="d-inline-block ml-2 text-secondary-m1">
											'.$date_thread.' : <span class="text-blue-d1 text-600">'.T_('Attribution').'</span>';
											if(!$mobile) {echo '&nbsp;'.T_($name).' <span class="ml-1" style="font-size: x-small;">('.T_('réalisé par').' '; if($author['firstname']) {echo $author['firstname'];} if($author['lastname']) {echo ' '.$author['lastname'];} echo ')</span>';}
											echo '
										</div>
									</div>
									';
								}
								//transfert thread
								if($row['type']==2 && !$rright['ticket_resolution_text_only'])
								{
									//find technician group name 
									$qry2=$db->prepare("SELECT `name` FROM `tgroups` WHERE id=:id");
									$qry2->execute(array('id' => $row['group1']));
									$rgroup1=$qry2->fetch();
									$qry2->closeCursor();
									if(empty($rgroup1)) {$rgroup1=array();} 
									if(empty($rgroup1['name'])) {$rgroup1['name']='';}
									
									$qry2=$db->prepare("SELECT `name` FROM `tgroups` WHERE id=:id");
									$qry2->execute(array('id' => $row['group2']));
									$rgroup2=$qry2->fetch();
									$qry2->closeCursor();
									if(empty($rgroup2)) {$rgroup2=array();} 
									if(empty($rgroup2['name'])) {$rgroup2['name']='';}
									
									//find technicians name
									$qry2=$db->prepare("SELECT CONCAT_WS('. ', left(tusers.firstname, 1),  tusers.lastname) AS name FROM tusers WHERE id=:id AND id!='0'");
									$qry2->execute(array('id' => $row['tech1']));
									$rtech1=$qry2->fetch();
									$qry2->closeCursor();
									if(empty($rtech1)) {$rtech1=array();} 
									if(empty($rtech1['name'])) {$rtech1['name']='';}
									
									$qry2=$db->prepare("SELECT CONCAT_WS('. ', left(tusers.firstname, 1),  tusers.lastname) AS name FROM tusers WHERE id=:id AND id!='0'");
									$qry2->execute(array('id' => $row['tech2']));
									$rtech2=$qry2->fetch();
									$qry2->closeCursor();
									if(empty($rtech2)) {$rtech2=array();} 
									if(empty($rtech2['name'])) {$rtech2['name']='';}
									
									if($rtech1['name']) {$source=$rtech1['name'];} else {$source=$rgroup1['name'];}
									if($rtech2['name']) {$destination=$rtech2['name'];} else {$destination=$rgroup2['name'];}
									
									$dispname=T_('de').' <b>'.$source.'</b> '.T_('vers').' <b>'.$destination.'</b>';
									echo '
									<div class="ml-n3 mt-2">
										<button title="'.T_("Transfert du ticket entre deux techniciens").'" type="button" class="btn btn-lighter-warning btn-h-warning  w-5 radius-round px-0">
											<i class="fa fa-exchange-alt"><!----></i>
										</button>
										<div class="d-inline-block ml-2 text-secondary-m1">
											'.$date_thread.' : <span class="text-blue-d1 text-600">'.T_('Transfert').'</span>';
											if(!$mobile) {echo '&nbsp;'.T_('du ticket').' '.$dispname.' <span class="ml-1" style="font-size: x-small;">('.T_('réalisé par').' '; if($author['firstname']) {echo $author['firstname'];} if($author['lastname']) {echo ' '.$author['lastname'];} echo ')</span>';}
											echo '
										</div>
									</div>
									';
								}
								//mail thread
								if($row['type']==3 && !$rright['ticket_resolution_text_only'])
								{
									echo '
									<div class="ml-n3 mt-2">
										<button title="'.T_("Ticket envoyé par mail à l'adresse").' '.$row['dest_mail'].'"  type="button" class="btn btn-lighter-grey btn-h-grey  w-5 radius-round px-0">
											<i class="fa fa-envelope"><!----></i>
										</button>
										<div class="d-inline-block ml-2 text-secondary-m1">
											'.$date_thread.' : <span class="text-blue-d1 text-600">'.T_('Envoi Mail').'</span>';
											if(!$mobile) {echo '<span class="ml-1" style="font-size: x-small;">('.T_('réalisé par').' '; if($author['firstname']) {echo $author['firstname'];} if($author['lastname']) {echo ' '.$author['lastname'];} echo ')</span>';}
											echo '
										</div>
									</div>
									';
								}
								//close ticket
								if($row['type']==4)
								{
									echo '
									<div class="ml-n3 mt-2">
										<button title="'.T_("Le ticket est résolu").'"  type="button" class="btn btn-lighter-success btn-h-success w-5 radius-round px-0">
											<i class="fa fa-check"><!----></i>
										</button>
										<div class="d-inline-block ml-2 text-secondary-m1">
											'.$date_thread.' : <span class="text-blue-d1 text-600">'.T_('Résolu').'</span>';
											if(!$mobile) {echo '<span class="ml-1" style="font-size: x-small;">('.T_('réalisé par').' '; if($author['firstname']) {echo $author['firstname'];} if($author['lastname']) {echo ' '.$author['lastname'];} echo ')</span>';}
											echo '
										</div>
									</div>
									';
								}
								//switch state thread
								if($row['type']==5 && !$rright['ticket_resolution_text_only'])
								{
									echo '
									<div class="ml-n3 mt-2">
										<button title="'.T_("Modification de l'état du ticket").'" type="button" class="btn btn-lighter-pink btn-h-pink  w-5 radius-round px-0">
											<i class="fa fa-adjust"><!----></i>
										</button>
										<div class="d-inline-block ml-2 text-secondary-m1">
											'.$date_thread.' : <span class="text-blue-d1 text-600">';
											if(!$mobile) {echo T_("Changement d'état").'</span> '.T_($rstate['name']).'<span class="ml-1" style="font-size: x-small;">('.T_('réalisé par').' '; if($author['firstname']) {echo $author['firstname'];} if($author['lastname']) {echo ' '.$author['lastname'];} echo ')</span>';}
											else {echo T_("Modif. état").'</span>';}
											echo '
										</div>
									</div>
									';
								}
							}
							$qry->closeCursor();
							echo'
                    </div>
                </div>           
			</div>';			
}

if($rright['ticket_thread_add'])
{
	//display text input
	if($_GET['action']!='new') //query only in edit ticket mode to display new ticket faster
	{
		$qry=$db->prepare("SELECT `text` FROM `tthreads` WHERE `id`=:id AND `type`='0'");
		$qry->execute(array('id' => $_GET['threadedit']));
		$row=$qry->fetch();
		$qry->closeCursor();
		if(empty($row['text'])) {$thread_text='';} else {$thread_text=$row['text'];}
	} else {$thread_text='';}
	//button name
	if(!$mobile){
		if(empty($_GET['threadedit']))
		{
			$button_text=T_('Ajouter');
			$button_icon='fa-plus';
		} else {
			$button_text=T_('Modifier');
			$button_icon='fa-pencil-alt';
		}
	} else 	{
		$button_text='';
		if(empty($_GET['threadedit']))
		{
			$button_icon='fa-plus';
		} else {
			$button_icon='fa-pencil-alt';
		}
	}
	//detect <br> for wysiwyg transition from 2.9 to 3.0
	$findbr=stripos($thread_text, '<br>');
	if($findbr===false) {$text=nl2br($thread_text);} else {$text=$row[0];}
	echo '
	<table border="0" width="';if($mobile) {echo '310';} else {echo '732';} echo '" >
		<tr>
			<td class="pt-2 mr-0">
				<table border="0" style="border: 1px solid #D8D8D8;" >
					<tr>
						<td>
							<div id="editor2" class="pl-2 pt-1 bootstrap-wysiwyg-editor" style="min-height:80px; min-width:250px; max-width:575px;" >';
								if($_POST['text2']!='') {echo $_POST['text2'];} elseif($text) {echo "	$text";} else {echo "";}
							echo '</div>
							<input type="hidden" name="text2" />
						</td>
					</tr>
				</table>
			</td>
			<td >	
				';
				//add button for private message, case to send auto mail when technician add resolution
				if($rright['ticket_thread_private_button']!=0 && !$_GET['threadedit'])
				{
					echo '<label><input class="ml-2" type="checkbox" id="private" name="private" value="1"><span title="'.T_('Permet de masquer cet élément pour le demandeur, sur le ticket et dans les mails').'" class="lbl">&nbsp;&nbsp;'.T_('Privé').'</span></label>';
					if($rparameters['mail_auto_user_modify']!=0) {echo '<i class="pl-1 fa fa-question-circle text-info" title="'.T_('Le demandeur ne recevra pas de mail concernant ce message').'."><!----></i>';}
					echo '<br><br>';
				}
				//add time by response element list for tech
				if($rparameters['ticket_time_response_element'] && ($_SESSION['profile_id']==0 || $_SESSION['profile_id']==3 || $_SESSION['profile_id']==4))
				{
					echo T_('Temps passé'). ' :
					<select class="form-control form-control-sm mb-2" id="time_response_element" name="time_response_element">
						';
						$qry=$db->prepare("SELECT `min`,`name` FROM `ttime` ORDER BY `min` ");
						$qry->execute();
						while($row=$qry->fetch()) 
						{
							//select value
							if($_GET['threadedit'])
							{
								$qry2=$db->prepare("SELECT `time` FROM `tthreads` WHERE id=:id");
								$qry2->execute(array('id' => $_GET['threadedit']));
								$thread_time=$qry2->fetch();
								$qry2->closeCursor();

								if($thread_time['time']==$row['min'])
								{
									echo '<option selected value="'.$row['min'].'">'.$row['name'].'</option>';
								} else {
									echo '<option value="'.$row['min'].'">'.$row['name'].'</option>';
								}
							} else {
								echo '<option value="'.$row['min'].'">'.$row['name'].'</option>';
							}
						}
						$qry->closeCursor();
						echo '
					</select>
					';
				}
				echo '
				<span style="text-align:center;">
					<button class="btn btn-sm btn-success" title="'.$button_text.'" name="modify" value="modify" type="submit" id="add_thread_btn"><i class="fa '.$button_icon.'"><!----></i> '.$button_text.'</button>
				</span>
			</td>
		</tr>
	</table>
	';
}

if($_GET['action']!='new') 
{
			echo '
			</td>
		</tr>
	</table>
	';	
}

?>