<?php
################################################################################
# @Name : ./admin/users/edit.php
# @Description : modify user
# @Call : /admin/user.php
# @Parameters : 
# @Author : Flox
# @Create : 17/09/2021
# @Update : 26/12/2023
# @Version : 3.2.46
################################################################################

//bloc none user 
if($_GET['userid']==0) {echo DisplayMessage('error',T_('Accès interdit')); exit;}

if($_POST['modify'])
{
	//check token
	$qry=$db->prepare("SELECT `token` FROM ttoken WHERE `action`='profile_update' AND ip=:ip AND user_id=:user_id AND token=:token");
	$qry->execute(array('ip' => $_SERVER['REMOTE_ADDR'],'user_id' => $_SESSION['user_id'],'token' => $_POST['token']));
	$token=$qry->fetch();
	$qry->closeCursor();
	if(empty($token['token'])) {echo DisplayMessage('error',T_("Vous n'avez pas accès à cette page")); exit;}

	//case user sync from AD without pwd and not already connected
	if($rparameters['ldap'] && $rparameters['ldap_auth'] && $_POST['password']=='')
	{
		$qry = $db->prepare("SELECT `password`,`salt` FROM `tusers` WHERE id=:id");
		$qry->execute(array('id' => $_GET['userid']));
		$row=$qry->fetch();
		$qry->closeCursor();
		if($row['password']=='')
		{
			$pwd = substr(md5(uniqid(rand(), true)), 0, 5); //generate a random password
			$hash = password_hash($pwd, PASSWORD_DEFAULT);
			//update pwd
			$qry=$db->prepare("UPDATE `tusers` SET `password`=:password WHERE `id`=:id");
			$qry->execute(array('password' => $hash,'id' => $_GET['userid']));
			$_POST['password']=$pwd;
		}
	}
	if($_POST['mail']) //mail control
	{
		if(!filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL)) {$error=T_("L'adresse mail est incorrecte");}
	}
	if($_POST['login']) //existing account control
	{
		$qry=$db->prepare("SELECT `id`,`firstname`,`lastname` FROM `tusers` WHERE `login`=:login AND `disable`=0 AND `id`!=:id");
		$qry->execute(array('login' => $_POST['login'],'id' => $_GET['userid']));
		$row=$qry->fetch();
		$qry->closeCursor();
		if($row) {$error=T_('Un autre compte utilise déjà cet identifiant ('.$row['lastname'].' '.$row['firstname'].')');}
	}
	if($_POST['password']!=$_POST['password2']) {$error=T_('Les mots de passe ne sont pas identiques');}
	if(!$_POST['login']) {$error=T_("L'identifiant ne peut être vide");}
	
	//password policy
	if($rparameters['user_password_policy'] && $_POST['password'])
	{
		if(strlen($_POST['password'])<$rparameters['user_password_policy_min_lenght'])
		{
			$error=T_('Le mot de passe doit faire').' '.$rparameters['user_password_policy_min_lenght'].' '.T_('caractères minimum');
		}elseif($rparameters['user_password_policy_special_char'] && !preg_match('/[^a-zA-Z\d]/', $_POST['password']))
		{
			$error=T_('Le mot de passe doit contenir un caractère spécial');
		}elseif($rparameters['user_password_policy_min_maj'] && (!preg_match('/[A-Z]/', $_POST['password']) || !preg_match('/[a-z]/', $_POST['password'])))
		{
			$error=T_('Le mot de passe doit posséder au moins une lettre majuscule et une minuscule');
		}
	}

	//security check for move profile to admin to another
	$qry=$db->prepare("SELECT `profile`,`login` FROM `tusers` WHERE id=:id");
	$qry->execute(array('id' => $_GET['userid']));
	$current_user=$qry->fetch();
	$qry->closeCursor();
	if($current_user['profile']==4 && $_POST['profile']!=4)
	{
		logit('security', 'Admin profile remove for user '.$current_user['login'],$_SESSION['user_id']);
	}

	if(!$rright['user_profil_company'])
	{
		//check right modify company
		$qry=$db->prepare("SELECT `company` FROM `tusers` WHERE id=:id");
		$qry->execute(array('id' => $_GET['userid']));
		$current_user=$qry->fetch();
		$qry->closeCursor();
		if($current_user['company']!=$_POST['company'])
		{
			echo DisplayMessage('error',T_("Vous n'avez pas les droits de changer votre société"));
			exit;
		}
	}
	
	if(!$error)
	{
		if($_POST['password'])
		{
			$hash=password_hash($_POST['password'], PASSWORD_DEFAULT);
			$last_pwd_chg=date('Y-m-d');
		} else {
			$qry=$db->prepare("SELECT `password`,`last_pwd_chg` FROM `tusers` WHERE id=:id AND disable=0 ");
			$qry->execute(array('id' => $_GET['userid']));
			$row=$qry->fetch();
			$qry->closeCursor();
			if(empty($row['password'])) {$row['password']='';}
			if(empty($row['last_pwd_chg'])) {$row['last_pwd_chg']='';}
			$hash=$row['password'];
			$last_pwd_chg=$row['last_pwd_chg'];
		}
		 
		$qry=$db->prepare("
		UPDATE tusers SET
		`firstname`=:firstname,
		`lastname`=:lastname,
		`password`=:password,
		`mail`=:mail,
		`phone`=:phone,
		`mobile`=:mobile,
		`fax`=:fax,
		`function`=:function,
		`company`=:company,
		`address1`=:address1,
		`address2`=:address2,
		`zip`=:zip,
		`city`=:city,
		`custom1`=:custom1,
		`custom2`=:custom2,
		`limit_ticket_number`=:limit_ticket_number,
		`limit_ticket_days`=:limit_ticket_days,
		`limit_ticket_date_start`=:limit_ticket_date_start,
		`skin`=:skin,
		`dashboard_ticket_order`=:dashboard_ticket_order,
		`default_ticket_state`=:default_ticket_state,
		`chgpwd`=:chgpwd,
		`last_pwd_chg`=:last_pwd_chg,
		`language`=:language,
		`planning_color`=:planning_color
		WHERE `id`=:id
		");
		$qry->execute(array(
			'firstname' => $_POST['firstname'],
			'lastname' => $_POST['lastname'],
			'password' => $hash,
			'mail' => $_POST['mail'],
			'phone' => $_POST['phone'],
			'mobile' => $_POST['mobile'],
			'fax' => $_POST['fax'],
			'function' => $_POST['function'],
			'company' => $_POST['company'],
			'address1' => $_POST['address1'],
			'address2' => $_POST['address2'],
			'zip' => $_POST['zip'],
			'city' => $_POST['city'],
			'custom1' => $_POST['custom1'],
			'custom2' => $_POST['custom2'],
			'limit_ticket_number' => $_POST['limit_ticket_number'],
			'limit_ticket_days' => $_POST['limit_ticket_days'],
			'limit_ticket_date_start' => $_POST['limit_ticket_date_start'],
			'skin' => $_POST['skin'],
			'dashboard_ticket_order' => $_POST['dashboard_ticket_order'],
			'default_ticket_state' => $_POST['default_ticket_state'],
			'chgpwd' => $_POST['chgpwd'],
			'last_pwd_chg' => $last_pwd_chg,
			'language' => $_POST['language'],
			'planning_color' => $_POST['planning_color'],
			'id' => $_GET['userid']
			));
		$qry->closeCursor();
		
		//log
		if($rparameters['log'])
		{
			require_once('core/functions.php');
			if($_POST['profile']==4)
			{
				if($_POST['password']){logit('security', 'Password change for and admin account '.$_POST['login'],$_SESSION['user_id']);}
				
				//get current profil of updated user
				$qry=$db->prepare("SELECT `profile` FROM `tusers` WHERE id=:id");
				$qry->execute(array('id' => $_GET['userid']));
				$user_profile=$qry->fetch();
				$qry->closeCursor();
				
				if($user_profile['profile']!=4){logit('security', 'Profile change to admin for account '.$_POST['login'],$_SESSION['user_id']);}
			}
		}
		
		//special case profil update check admin right
		if($rright['admin'])
		{
			$qry=$db->prepare("UPDATE tusers SET profile=:profile WHERE id=:id ");
			$qry->execute(array('profile' => $_POST['profile'],'id' => $_GET['userid']));
			$qry->closeCursor();
			$qry=$db->prepare("UPDATE tusers SET login=:login WHERE id=:id ");
			$qry->execute(array('login' => $_POST['login'],'id' => $_GET['userid']));
			$qry->closeCursor();
		}

		
		//add service association to this user
		if($_POST['service'] && $rright['user_profil_service']) {
			$qry=$db->prepare("INSERT INTO `tusers_services` (`user_id`,`service_id`) VALUES (:user_id,:service_id)");
			$qry->execute(array('user_id' => $_GET['userid'],'service_id' => $_POST['service']));
		}
		//add agency association to this user
		if($rparameters['user_agency'])
		{
			if($_POST['agency'] && $rright['user_profil_agency']) {
				$qry=$db->prepare("INSERT INTO `tusers_agencies` (`user_id`,`agency_id`) VALUES (:user_id,:agency_id)");
				$qry->execute(array('user_id' => $_GET['userid'],'agency_id' => $_POST['agency']));
			}
		}
		//add view
		if($_POST['viewname'] && $rright['admin_user_view'] && $_POST['category']!='%')
		{
			$qry=$db->prepare("INSERT INTO `tviews` (`uid`,`name`,`category`,`subcat`) VALUES (:uid,:name,:category,:subcat)");
			$qry->execute(array('uid' => $_GET['userid'],'name' => $_POST['viewname'],'category' => $_POST['category'],'subcat' => $_POST['subcat']));
		}
		if($_POST['technician'] && $rright['admin_user_view'] && $_POST['technician']!='%' && is_numeric($_POST['technician']))
		{
			$qry=$db->prepare("INSERT INTO `tviews` (`uid`,`technician`) VALUES (:uid,:technician)");
			$qry->execute(array('uid' => $_GET['userid'],'technician' => $_POST['technician']));
		}
		//tech attachement insert
		if($_POST['attachment'])
		{
			if(preg_match('/G_/',$_POST['attachment']))
			{
				$group_id=explode('G_',$_POST['attachment']);
				$group_id=$group_id['1'];
				$qry=$db->prepare("INSERT INTO `tusers_tech` (`user_group`,`tech`) VALUES (:user,:tech)");
				$qry->execute(array('user' => $group_id,'tech' => $_GET['userid']));
			} else {	
				$qry=$db->prepare("INSERT INTO `tusers_tech` (`user`,`tech`) VALUES (:user,:tech)");
				$qry->execute(array('user' => $_POST['attachment'],'tech' => $_GET['userid']));
			}
		}
	} 
	//redirect
	$url=$_SERVER['QUERY_URI'];
	$url=preg_replace('/%/','%25',$url);
	if($error)
	{
		echo DisplayMessage('error',$error);
	} else {
		echo '<script language="Javascript">
		<!--
		document.location.replace("'.$url.'");
		// -->
		</script>';
	}
}

//generate token
$qry=$db->prepare("DELETE FROM `ttoken` WHERE `action`='profile_update' AND user_id=:user_id AND ip=:ip ");
$qry->execute(array('user_id' => $_SESSION['user_id'],'ip' => $_SERVER['REMOTE_ADDR']));
$token = bin2hex(random_bytes(32));
$qry=$db->prepare("INSERT INTO `ttoken` (`date`,`token`,`action`,`user_id`,`ip`) VALUES (NOW(),:token,'profile_update',:user_id,:ip)");
$qry->execute(array('token' => $token,'user_id' => $_SESSION['user_id'],'ip' => $_SERVER['REMOTE_ADDR']));

/////////////////////////////////////////////////// display edit user page  /////////////////////////////////////////////////////////////
if(($_GET['action']=='edit') && (($_SESSION['user_id']==$_GET['userid']) || $rright['admin'])) 
{
	//get user data
	$qry = $db->prepare("SELECT * FROM `tusers` WHERE id=:id");
	$qry->execute(array('id' => $_GET['userid']));
	$user1=$qry->fetch();
	$qry->closeCursor();

	//get user rights
	$qry=$db->prepare("SELECT * FROM `trights` WHERE profile=:profile");
	$qry->execute(array('profile' => $user1['profile']));
	$user_right=$qry->fetch();
	$qry->closeCursor();

	//first letters
	$firstname_letter=strtoupper(substr($user1['firstname'],0,1));
	if(empty($firstname_letter)) {$firstname_letter='';}
	$lastname_letter=strtoupper(substr($user1['lastname'],0,1));
	if(empty($lastname_letter)) {$lastname_letter='';}

	if($user1['profile']==0)$lastname_letter_color='bgc-grey';
	if($user1['profile']==1)$lastname_letter_color='bgc-green';
	if($user1['profile']==2)$lastname_letter_color='bgc-primary';
	if($user1['profile']==3)$lastname_letter_color='bgc-orange';
	if($user1['profile']==4)$lastname_letter_color='bgc-dark';

	//keep date if posted on error
	if($error)
	{
		if($_POST['firstname']){$user1['firstname']=$_POST['firstname'];}
		if($_POST['lastname']){$user1['lastname']=$_POST['lastname'];}
		if($_POST['login']){$user1['login']=$_POST['login'];}
		if($_POST['mail']){$user1['mail']=$_POST['mail'];}
		if($_POST['phone']){$user1['phone']=$_POST['phone'];}
		if($_POST['mobile']){$user1['mobile']=$_POST['mobile'];}
		if($_POST['fax']){$user1['fax']=$_POST['fax'];}
		if($_POST['service']){$user1['service']=$_POST['service'];}
		if($_POST['agency']){$user1['agency']=$_POST['agency'];}
		if($_POST['function']){$user1['function']=$_POST['function'];}
		if($_POST['company']){$user1['company']=$_POST['company'];}
		if($_POST['address1']){$user1['address1']=$_POST['address1'];}
		if($_POST['address2']){$user1['address2']=$_POST['address2'];}
		if($_POST['city']){$user1['city']=$_POST['city'];}
		if($_POST['zip']){$user1['zip']=$_POST['zip'];}
		if($_POST['custom1']){$user1['custom1']=$_POST['custom1'];}
		if($_POST['custom2']){$user1['custom2']=$_POST['custom2'];}
	}
	
	//display edit form.
	echo '
		<div class="col-12 cards-container">
			<div class="card bcard shadow">
				<div class="card-header">
					<h5 class="card-title">
						<span style="padding-top:5px;" class="d-inline-block text-center mr-2 pt-2 w-5 h-5 radius-round '.$lastname_letter_color.' text-white font-bolder text-90">'.$firstname_letter.$lastname_letter.'</span>
						'.$user1['firstname'].' '.$user1['lastname'].'
					</h5>
					<span class="card-toolbar">
						';
						//display toolbar buttons for administrators
						if($rright['admin'])
						{
							//display previous and next buttons
							$qry=$db->prepare("SELECT `id` FROM `tusers` WHERE `lastname`!='' AND `disable`=:disable AND `id` < :current_user_id ORDER BY `id` DESC LIMIT 1;");
							$qry->execute(array('disable' => $_GET['disable'],'current_user_id' => $_GET['userid']));
							$previous_user=$qry->fetch();
							$qry->closeCursor();
							if(!empty($previous_user['id']))
							{
								echo '
								<a href="index.php?page=admin&amp;subpage=user&amp;action=edit&amp;userid='.$previous_user['id'].'&amp;tab='.$_GET['tab'].'&amp;disable='.$_GET['disable'].'" >
									<button class="btn btn-info ml-1">
										<i title="'.T_('Utilisateur précédent').'" class="fa fa-arrow-left text-120"><!----></i>
									</button>
								</a>
								';
							}
							$qry=$db->prepare("SELECT `id` FROM `tusers` WHERE `lastname`!='' AND `disable`=:disable AND `id` > :current_user_id ORDER BY `id` ASC LIMIT 1;");
							$qry->execute(array('disable' => $_GET['disable'],'current_user_id' => $_GET['userid']));
							$next_user=$qry->fetch();
							$qry->closeCursor();
							if(!empty($next_user['id']))
							{
								echo '
								<a href="index.php?page=admin&amp;subpage=user&amp;action=edit&amp;userid='.$next_user['id'].'&amp;tab='.$_GET['tab'].'&amp;disable='.$_GET['disable'].'">
									<button class="btn btn-info ml-1"><i title="'.T_('Utilisateur suivant').'" class="fa fa-arrow-right text-120"><!----></i></button>
								</a>
								';
							}
							//display enable disable and delete buttons
							if(!$_GET['disable'])
							{
								echo '
								<a class="btn btn-danger ml-1" onclick=\'javascript: return confirm("'.T_('Êtes-vous sur de vouloir désactiver cet utilisateur, il ne pourra plus se connecter ni apparaître sur un nouveau ticket').'"); \' href="index.php?page=admin&amp;subpage=user&amp;action=disable&amp;userid='.$_GET['userid'].'&amp;disable='.$_GET['disable'].'">
									<i title="'.T_('Désactiver l\'utilisateur').'" class="fa fa-ban text-120"><!----></i>
								</a>';
							} else {
								echo '
								<a class="btn btn-success ml-1"  href="index.php?page=admin&amp;subpage=user&amp;action=enable&userid='.$_GET['userid'].'&amp;tab='.$_GET['tab'].'&amp;disable='.$_GET['disable'].'">
									<i title="'.T_('Activer l\'utilisateur').'" class="fa fa-check text-120"><!----></i>
								</a>';
									echo '<a class="btn btn-danger ml-1" onclick=\'javascript: return confirm("'.T_('Êtes-vous sur de vouloir supprimer définitivement cet utilisateur ? information également supprimée sur tous les tickets et dans tous le logiciel').'"); \' href="index.php?page=admin&amp;subpage=user&amp;action=delete&userid='.$_GET['userid'].'&amp;tab='.$_GET['tab'].'&amp;disable='.$_GET['disable'].'">
									<i title="'.T_('Supprimer l\'utilisateur').'" class="fa fa-trash text-120"><!----></i>
								</a>';
							}
						}	
						echo '
						<button value="modify" id="modify2" name="modify" type="submit" form="edit_user" class="btn btn-success ml-1">
							<i title="'.T_('Enregistrer').'" class="fa fa-save text-130"><!----></i>
						</button>
					</span>
				</div>
				<div class="card-body">
					<div class="card-main no-padding">
						<form id="edit_user" name="edit_user" method="POST" action="" class="form-horizontal" autocomplete="off">
							<input type="hidden" name="token" value="'.$token.'" />
                                <fieldset>
                                <div class="col-sm-12">
                            		<div class="tabs-above">
                            			<ul class="nav nav-tabs nav-justified" id="myTab">
                            				<li class="nav-item mr-1px">
                            					<a class="nav-link '; if($_GET['tab']=='infos') {echo 'active';} echo '" href="./index.php?page=admin/user&amp;subpage=user&amp;action=edit&amp;userid='.$_GET['userid'].'&amp;tab=infos">
                            						<i class="fa fa-info-circle text-primary-m2"><!----></i>
                            						'.T_('Informations').'
                            					</a>
                            				</li>
                            				<li class="nav-item mr-1px">
                            					<a class="nav-link '; if($_GET['tab']=='parameters') {echo 'active';} echo '" href="./index.php?page=admin/user&amp;subpage=user&amp;action=edit&amp;userid='.$_GET['userid'].'&amp;tab=parameters">
                            						<i class="fa fa-cog text-warning"><!----></i>
                            						'.T_('Paramètres').'
                            					</a>
                            				</li>';
                                            //display attachment tab if current user is technician or admin
                            				if($rright['admin'] && ($user1['profile']==0 || $user1['profile']==4))
                            				{
												echo '
												<li class="nav-item mr-1px">
                                					<a class="nav-link '; if($_GET['tab']=='attachment') {echo 'active';} echo '" href="./index.php?page=admin/user&amp;subpage=user&amp;action=edit&amp;userid='.$_GET['userid'].'&amp;tab=attachment">
                                						<i class="fa fa-user text-success"><!----></i>
                                						'.T_('Rattachement à des utilisateurs').'
                                						<i title="'.T_("Permet d'attribuer automatiquement un technicien lors de la création de ticket par un utilisateur").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                					</a>
                                				</li>';
                            				}
                            				echo'
                            			</ul>
                            			<div class="tab-content">
										';
											if($rright['admin'])
											{
												echo '
												<div id="attachment_tab" class="tab-pane'; if($_GET['tab']=='attachment' || $_GET['tab']=='') echo 'active'; echo '">
													<label class="control-label bolder text-primary-m2" for="attachment">'.T_('Associer des utilisateurs à ce technicien').' :</label>
													<div class="space-4"></div>
													<select style="width:auto;" class="form-control form-control-sm d-inline-block" name="attachment" id="attachment">
														';
														//display list of user for attachment
														$qry = $db->prepare("SELECT tusers.* FROM `tusers` WHERE (tusers.profile!=0 AND tusers.profile!='4' AND tusers.disable='0' AND tusers.id NOT IN (SELECT user FROM tusers_tech)) OR id=0 ORDER BY tusers.id!=0,tusers.lastname");
														$qry->execute();
														while ($row = $qry->fetch())
														{
															echo '<option value="'.$row['id'].'">'.$row['lastname'].' '.$row['firstname'].'</option>';
														}
														$qry->closeCursor();
														//display group of user for attachment
														$qry = $db->prepare("SELECT `id`,`name` FROM `tgroups` WHERE `type`='0' AND `disable`='0'");
														$qry->execute();
														while ($row = $qry->fetch())
														{
															echo '<option value="G_'.$row['id'].'">[G] '.$row['name'].'</option>';
														}
														$qry->closeCursor();
														echo '
													</select>
													<hr />
													<span class="control-label bolder text-primary-m2">'.T_('Liste des utilisateurs associés à ce technicien').' :</span>
													<div class="space-4"></div>
													';
														//user list
														$qry = $db->prepare("SELECT `id`,`user` FROM `tusers_tech` WHERE tech=:tech AND `user`!=0");
														$qry->execute(array('tech' => $_GET['userid']));
														while ($row = $qry->fetch())
														{
															//find tech name
															$qry2 = $db->prepare("SELECT `lastname`,`firstname` FROM `tusers` WHERE id=:id");
															$qry2->execute(array('id' => $row['user']));
															$row2=$qry2->fetch();
															$qry2->closeCursor();
															echo'<i class="fa fa-caret-right text-primary-m2"><!----></i> '.$row2['lastname'].' '.$row2['firstname'].'';
															echo '<a title="Supprimer" href="./index.php?page=admin/user&amp;subpage=user&amp;action=edit&amp;userid='.$_GET['userid'].'&amp;tab=attachment&attachmentdelete='.$row['id'].'"> <i class="fa fa-trash text-danger"><!----></i></a>';
															echo '<br />';
														}
														$qry->closeCursor();
														//group list
														$qry = $db->prepare("SELECT  `id`,`user_group` FROM `tusers_tech` WHERE tech=:tech AND user_group!='0'");
														$qry->execute(array('tech' => $_GET['userid']));
														while ($row = $qry->fetch())
														{
															//find tech name
															$qry2 = $db->prepare("SELECT `name` FROM `tgroups` WHERE id=:id");
															$qry2->execute(array('id' => $row['user_group']));
															$row2=$qry2->fetch();
															$qry2->closeCursor();
															echo'<i class="fa fa-caret-right text-primary-m2"><!----></i> [G] '.$row2['name'];
															echo '<a title="Supprimer" href="./index.php?page=admin/user&amp;subpage=user&amp;action=edit&amp;userid='.$_GET['userid'].'&amp;tab=attachment&attachmentdelete='.$row['id'].'"> <i class="fa fa-trash text-danger"><!----></i></a>';
															echo '<br />';
														}
														$qry->closeCursor();
														echo '
												</div>
												';
											}
											echo '
                                           	 <div id="parameters" class="tab-pane'; if($_GET['tab']=='parameters' || $_GET['tab']=='') echo 'active'; echo '">
													<label class="control-label bolder text-primary-m2" for="language">'.T_('Langue').' :</label>
                    								<select style="width:auto;" class="form-control form-control-sm d-inline-block" name="language" id="language">
                    									<option '; if($user1['language']=='fr_FR'){echo "selected";} echo ' value="fr_FR">'.T_('Français (France)').'</option>
                    									<option '; if($user1['language']=='en_US'){echo "selected";} echo ' value="en_US">'.T_('Anglais (États-Unis)').'</option>
                    									<option '; if($user1['language']=='de_DE'){echo "selected";} echo ' value="de_DE">'.T_('Allemand (Allemagne)').'</option>
                    									<option '; if($user1['language']=='es_ES'){echo "selected";} echo ' value="es_ES">'.T_('Espagnol (Espagne)').'</option>
                    									<option '; if($user1['language']=='it_IT'){echo "selected";} echo ' value="it_IT">'.T_('Italien (Italie)').'</option>
                    								</select>
                    								<div class="mb-3"></div>
                                                    <label class="control-label bolder text-primary-m2" for="skin">'.T_('Thème').' :</label>
                    								<select style="width:auto;" class="form-control form-control-sm d-inline-block" name="skin" id="skin">
                    									<option '; if($user1['skin']==''){echo "selected";} echo ' value="">'.T_('Bleu (Défaut)').'</option>
														<option '; if($user1['skin']=='skin-3'){echo "selected";} echo ' value="skin-3">'.T_('Gris').'</option>
                    									<option '; if($user1['skin']=='skin-1'){echo "selected";} echo ' value="skin-1">'.T_('Noir').'</option>
                    									<option '; if($user1['skin']=='skin-2'){echo "selected";} echo ' value="skin-2">'.T_('Violet').'</option>
                    									<option '; if($user1['skin']=='skin-5'){echo "selected";} echo ' value="skin-5">'.T_('Vert').'</option>
														<option '; if($user1['skin']=='skin-7'){echo "selected";} echo ' value="skin-7">'.T_('Vert et violet').'</option>
                    									<option '; if($user1['skin']=='skin-6'){echo "selected";} echo ' value="skin-6">'.T_('Orange').'</option>
														<option '; if($user1['skin']=='skin-8'){echo "selected";} echo ' value="skin-8">'.T_('Orange et violet').'</option>
														<option '; if($user1['skin']=='skin-4'){echo "selected";} echo ' value="skin-4">'.T_('Sombre').'</option>
                    								</select>
                    								';
													//display planning color 
													if($user_right['planning_tech_color'] && $rright['planning_tech_color'] && ($rright['ticket_tech_admin'] || $rright['ticket_tech_super'] || $_SESSION['profile_id']=='4'))
													{
														echo '
														<div class="mb-3"></div>
														<label class="control-label bolder text-primary-m2" for="planning_color">'.T_('Couleur dans le calendrier').' :</label>
														<input name="planning_color" id="planning_color" type="text" style="width:auto;" class="form-control form-control-sm d-inline-block" autocomplete="off" value="'.$user1['planning_color'].'"  />
														';
													} else {
														echo '
														<input name="planning_color" id="planning_color" type="hidden" value="'.$user1['planning_color'].'"  />
														';
													}
                    								//display group attachment if exist
													$qry = $db->prepare("SELECT count(*) FROM `tgroups`, `tgroups_assoc` WHERE tgroups.id=tgroups_assoc.group AND tgroups_assoc.user=:user AND tgroups.disable='0'");
													$qry->execute(array('user' => $_GET['userid']));
													$row=$qry->fetch();
													$qry->closeCursor();
                    								if($row[0]!=0)
                    								{
                    									echo '<div class="mb-3"></div>';
                    									echo '<span class="control-label bolder text-primary-m2" for="group">'.T_('Membre des groupes').' :</span>';
														$qry = $db->prepare("SELECT tgroups.id AS id, tgroups.name AS name FROM tgroups, tgroups_assoc WHERE tgroups.id=tgroups_assoc.group AND tgroups_assoc.user=:user AND tgroups.disable='0'");
														$qry->execute(array('user' => $_GET['userid']));
                    									while ($row = $qry->fetch())
                    									{
                    										echo '<div></div><i class="fa fa-caret-right text-primary-m2 ml-3"><!----></i> <a href="./index.php?page=admin&amp;subpage=group&amp;action=edit&amp;id='.$row['id'].'">'.$row['name'].'</a>';
                    									}
														$qry->closeCursor();														
                    								}
                    								// Display profile list
                    								if($rright['admin_user_profile']!='0')
                    								{
                    									echo '
                    									<div class="mb-3"></div>
                    									<span class="control-label bolder text-primary-m2">'.T_('Profil').' :</span>
                    									<div class="controls ml-3">
                    										<div class="radio">
                    											<label>
                    												<input type="radio" class="ace" name="profile" value="4" '; if($user1['profile']=='4')echo "checked"; echo '> <span class="lbl"> '.T_('Administrateur').' </span>
                    											</label>
                    										</div>
                    										<div class="radio">
                    											<label>
                    												<input type="radio" class="ace" name="profile" value="0" '; if($user1['profile']=='0')echo "checked"; echo '> <span class="lbl"> '.T_('Technicien').' </span>
                    											</label>
                    										</div>
                    										<div class="radio">
                    											<label>
                    												<input type="radio" class="ace" name="profile" value="3" '; if($user1['profile']=='3')echo "checked"; echo '> <span class="lbl"> '.T_('Superviseur').' </span>
                    											</label>
                    										</div>
                    										<div class="radio">
                    											<label>
                    												<input type="radio" class="ace" name="profile" value="1" '; if($user1['profile']=='1')echo "checked"; echo '> <span class="lbl"> '.T_('Utilisateur avec pouvoir').' </span>
                    											</label>
                    										</div>
                    										<div class="radio">
                    											<label>
                    												<input type="radio" class="ace" name="profile" value="2" '; if($user1['profile']=='2')echo "checked"; echo '> <span class="lbl"> '.T_('Utilisateur').' </span>
                    											</label>
                    										</div>
                    									</div>
                    									<div class="mb-3"></div>
                    									<span class="control-label bolder text-primary-m2" for="chgpwd">'.T_('Forcer le changement du mot de passe').' :</span>
                    									<label>
                    											<input type="radio" class="ace" name="chgpwd" value="1" '; if($user1['chgpwd']=='1')echo "checked"; echo '> <span class="lbl"> '.T_('Oui').' </span>
                    											<input type="radio" class="ace" name="chgpwd" value="0" '; if($user1['chgpwd']=='0')echo "checked"; echo '> <span class="lbl"> '.T_('Non').' </span>
                    									</label>
                    									';
                    								}
                    								else
                    								{
                    									echo '<input type="hidden" name="profile" value="'.$user1['profile'].'" '; if($user1['profile']=='2')echo "checked"; echo '>';
                    								}
                    								//display personal view
                    								if($rright['admin_user_view'])
                    								{
                    									echo '
															<div class="mb-3"></div>
                    										<span class="control-label bolder text-primary-m2" >'.T_('Vues personnelles').' : </span>
                        									<i title="'.T_("Associe une catégorie ou un technicien à l'utilisateur, afin d'avoir un accès direct au tickets rattachés, via un nouveau menu").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                    										<div>';
                    											//check if selected user have view
																$qry = $db->prepare("SELECT `id` FROM `tviews` WHERE uid=:uid");
																$qry->execute(array('uid' => $_GET['userid']));
																$row=$qry->fetch();
																$qry->closeCursor();
																if(empty($row['id'])) {$row=array(); $row['id']='';}
                    											if($row['id'])
                    											{
                    												//display current user views
																	$qry = $db->prepare("SELECT `id`,`name`,`category`,`subcat`,`technician` FROM `tviews` WHERE `uid`=:uid ORDER BY `uid`");
																	$qry->execute(array('uid' => $_GET['userid']));
                    												while ($view = $qry->fetch())
                    												{
																		if($view['technician']==0) //category view
																		{
																			//get category name
																			$qry2 = $db->prepare("SELECT `name` FROM `tcategory` WHERE id=:id");
																			$qry2->execute(array('id' => $view['category']));
																			$category=$qry2->fetch();
																			$qry2->closeCursor();
																			
																			if($view['subcat'])
																			{
																				$qry2 = $db->prepare("SELECT `name` FROM `tsubcat` WHERE id=:id");
																				$qry2->execute(array('id' => $view['subcat']));
																				$subcat=$qry2->fetch();
																				$qry2->closeCursor();
																				$subcat['name']=' > '.$subcat['name'];
																			} else {$subcat=array(); $subcat['name']='';}
																			$view_name=$view['name'].' : '.$category['name'].$subcat['name'];
																			
																		} else { //technician view
																			//get technician name
																			$qry2 = $db->prepare("SELECT `firstname`,`lastname` FROM `tusers` WHERE id=:id");
																			$qry2->execute(array('id' => $view['technician']));
																			$technician=$qry2->fetch();
																			$qry2->closeCursor();
																			$view_name=T_('Technicien').' : '.$technician['firstname'].' '.$technician['lastname'];
																		}
																		//display view
																		echo '
																		<div class="ml-3">
																			<i class="fa fa-eye text-primary-m2"><!----></i> 
																			'.$view_name.'
																			<a title="'.T_('Supprimer cette vue').'" href="index.php?page=admin/user&amp;subpage=user&amp;action=edit&amp;userid='.$_GET['userid'].'&viewid='.$view['id'].'&deleteview=1"><i class="fa fa-trash text-danger"><!----></i></a>
																		</div>
																		';
                    												}
																	$qry->closeCursor();
                    											}
                    											//display add view form
                    											echo '
																	<i class="fa fa-caret-right text-primary-m2 ml-3"><!----></i>
																 	'.T_('Ajouter une vue par catégorie').' : <input placeholder="'.T_('Nom de la vue').'" style="width:auto;" class="form-control form-control-sm d-inline-block mt-2" autocomplete="off" name="viewname" type="text" value="'.$_POST['viewname'].'" size="20" />
                    												<select style="width:auto;" class="form-control form-control-sm d-inline-block mt-2" name="category" id="category" onchange="submit()" >
                    													<option value="%"></option>';
																		//case to limit service parameters is enable
																		if($rparameters['user_limit_service'] && !$rright['admin'] && $rright['ticket_cat_service_only'])
																		{
																			$qry = $db->prepare("SELECT `id`,`name` FROM `tcategory` WHERE `service` IN (SELECT `service_id` FROM `tusers_services` WHERE `user_id`=:user_id) ORDER BY `name`");
																			$qry->execute(array('user_id' => $_SESSION['user_id']));
																		} else {
																			$qry = $db->prepare("SELECT `id`,`name` FROM `tcategory` ORDER BY `name`");
																			$qry->execute();
																		}
                    													while ($row=$qry->fetch())
                    													{
                    														echo "<option value=\"$row[id]\">$row[name]</option>";
                    														if($_POST['category']==$row['id']) echo "<option selected value=\"$row[id]\">$row[name]</option>";
                    													} 
																		$qry->closeCursor();
                    													echo '
                    												</select>
																	';
																	if($_POST['category']!='%')
																	{
																		echo '
																		<select style="width:auto;" class="form-control form-control-sm d-inline-block mt-2" name="subcat" id="subcat">
																			<option value="%"></option>';
																			$qry = $db->prepare("SELECT `id`,`name` FROM `tsubcat` WHERE `cat`=:cat ORDER BY `name`");
																			$qry->execute(array('cat' => $_POST['category']));
																			while ($row = $qry->fetch())
																			{
																				echo "<option value=\"$row[id]\">$row[name]</option>";
																				if($_POST['subcat']==$row['id']) echo "<option selected value=\"$row[id]\">$row[name]</option>";
																			} 
																			$qry->closeCursor();
																			echo '
																		</select>';
																	}

																	echo '
                    												<div class="space-4"></div>
																	<i class="fa fa-caret-right text-primary-m2 ml-3"><!----></i>
																	'.T_('Ajouter une vue par technicien').' :
                    												<select style="width:auto;" class="form-control form-control-sm d-inline-block mt-2" name="technician" id="technician">
                    													<option value="%"></option>';
																		$qry = $db->prepare("SELECT `id`,`firstname`,`lastname` FROM `tusers` WHERE `disable`=0 AND (`profile`='0' OR `profile`='4') ORDER BY `lastname`,`firstname`");
																		$qry->execute();
                    													while ($row=$qry->fetch())
                    													{
                    														echo '<option value="'.$row['id'].'">'.$row['lastname'].' '.$row['firstname'].'</option>';
                    														if($_POST['technician']==$row['id']) {echo '<option selected value="'.$row['id'].'">'.$row['lastname'].' '.$row['firstname'].'</option>';}
                    													} 
																		$qry->closeCursor();
                    													echo '
                    												</select>
																	
                    												
                    											</div>';
                    											//display default ticket state
                    										    echo '
																	<div class="mb-3"></div>
                        										    <label class="control-label bolder text-primary-m2" for="default_ticket_state">'.T_('Page par défaut à la connexion').' :</label>
                        										    <select style="width:auto; max-width:235px;" class="form-control form-control-sm d-inline-block" name="default_ticket_state" id="default_ticket_state">
                                    									<option '; if($user1['default_ticket_state']==''){echo "selected";} echo ' value="">'.T_("Aucun (Géré par l'administrateur)").'</option>';
																		if($rparameters['meta_state']==1) 
																		{
																			echo '<option '; if($user1['default_ticket_state']=='meta'){echo "selected";} echo ' value="meta">'.T_('Vos tickets à traiter').'</option>';
																			echo '<option '; if($user1['default_ticket_state']=='meta_all'){echo 'selected';} echo ' value="meta_all">'.T_('Tous les tickets à traiter').'</option>';
																		}
                                                                        $qry = $db->prepare("SELECT `id`,`name` FROM `tstates` ORDER BY `number`");
																		$qry->execute();
                    													while ($row = $qry->fetch())
                    													{
                    														if($user1['default_ticket_state']==$row['id']) {echo '<option selected value="'.$row['id'].'">'.T_('Vos tickets').' '.$row['name'].'</option>';} else {echo '<option value="'.$row['id'].'">'.T_('Vos tickets').' '.$row['name'].'</option>';}
                    													}
																		$qry->closeCursor();
                    													echo '
																		<option '; if($user1['default_ticket_state']=='all'){echo 'selected';} echo ' value="all">'.T_('Tous les tickets').'</option>
																		<option '; if($user1['default_ticket_state']=='activity'){echo 'selected';} echo ' value="activity">'.T_('Activité').'</option>
                                    								</select>
																	<i title="'.T_("État qui est directement affiché, lors de la connexion à l'application, si ce paramètre n'est pas renseigné, alors l'état par défaut est celui définit par l'administrateur").'." class="fa fa-question-circle text-primary-m2"><!----></i>
                    										    ';
                    										    //display default ticket order
                    										    echo '
																	<div class="mb-3"></div>
                        										    <label class="control-label bolder text-primary-m2" for="dashboard_ticket_order">'.T_('Ordre de tri personnel par défaut').' :</label>
                        										   
                        										    <select style="width:auto; max-width:235px;" class="form-control form-control-sm d-inline-block" name="dashboard_ticket_order" id="dashboard_ticket_order">
                        										        <option '; if($user1['default_ticket_state']==''){echo "selected";} echo ' value="">'.T_("Aucun (Géré par l'administrateur)").'</option>
                                    									<option '; if($user1['dashboard_ticket_order']=='tstates.number, tincidents.priority, tincidents.criticality, tincidents.date_create'){echo "selected";} echo ' value="tstates.number, tincidents.priority, tincidents.criticality, tincidents.date_create">'.T_('État  > Priorité > Criticité > Date de création').'</option>
                                    									<option '; if($user1['dashboard_ticket_order']=='tstates.number, tincidents.priority, tincidents.criticality, tincidents.date_hope'){echo "selected";} echo ' value="tstates.number, tincidents.priority, tincidents.criticality, tincidents.date_hope">'.T_('État > Priorité > Criticité > Date de résolution estimée').'</option>
                                    									<option '; if($user1['dashboard_ticket_order']=='tincidents.date_hope'){echo "selected";} echo ' value="tincidents.date_hope"> '.T_('Date de résolution estimée').'</option>
                                    									<option '; if($user1['dashboard_ticket_order']=='tincidents.priority'){echo "selected";} echo ' value="tincidents.priority"> '.T_('Priorité').'</option>
                                    									<option '; if($user1['dashboard_ticket_order']=='tincidents.criticality'){echo "selected";} echo ' value="tincidents.criticality"> '.T_('Criticité').'</option>
                                    									<option '; if($user1['dashboard_ticket_order']=='id'){echo "selected";} echo ' value="id">'.T_('Numéro de ticket').'</option>
                                    									<option '; if($user1['dashboard_ticket_order']=='date_modif'){echo "selected";} echo ' value="date_modif">'.T_('Date de dernière modification').'</option>
                                    								</select>
																	<i title="'.T_("Modifie l'ordre de tri des tickets dans la liste des tickets, si ce paramètre n'est pas renseigné, c'est le réglage par défaut dans la section administration qui est prit en compte").'." class="fa fa-question-circle text-primary-m2"><!----></i>
                    										    ';	
                    								    }
														//display ticket limit parameters
														if($rparameters['user_limit_ticket']==1 )
														{
															if($_SESSION['profile_id']==1 || $_SESSION['profile_id']==2) $readonly='readonly'; else $readonly='';
															echo '
																<hr />
																<label class="control-label bolder text-primary-m2" for="limit_ticket_number">'.T_('Limite de tickets').' :</label>
																<i title="'.T_("Permet de limiter un utilisateur a un nombre de ticket définit, passer la limite l'ouverture de nouveau ticket n'est plus possible").'." class="fa fa-question-circle text-primary-m2"><!----></i>
																<div class="space-4"></div>
																<label for="limit_ticket_number">'.T_('Nombre limite de ticket').' :</label>
																<input '.$readonly.' size="3" name="limit_ticket_number" type="text" value="'; if($user1['limit_ticket_number']) echo "$user1[limit_ticket_number]"; else echo ""; echo'" />
																<div class="space-4"></div>
																<label for="limit_ticket_days">'.T_('Durée de validé jours').' :</label>
																<input '.$readonly.' size="4" name="limit_ticket_days" type="text" value="'; if($user1['limit_ticket_days']) echo "$user1[limit_ticket_days]"; else echo ""; echo'" />
																<div class="space-4"></div>
																<label for="limit_ticket_date_start">'.T_('Date de début de validité (YYYY-MM-DD)').' :</label>
																<input '.$readonly.' size="10" name="limit_ticket_date_start" type="text" value="'; if($user1['limit_ticket_date_start']) echo "$user1[limit_ticket_date_start]"; else echo ""; echo'" />
															';
														}
                    								echo'
                                            </div>
											<div id="infos" class="tab-pane'; if($_GET['tab']=='infos' || $_GET['tab']=='') echo 'active'; echo '">
												<div class="form-group">
													<div class="form-group mt-3 mb-1 row mx-0">
														<label for="firstname" class="col-sm-2 col-form-label text-sm-right">'.T_('Prénom').'</label>
														<div class="col-sm-3">
															<input type="text" class="form-control brc-on-focus brc-success-m2" id="firstname" name="firstname" value="'.$user1['firstname'].'" autocomplete="off">
														</div>
													</div>
													<div class="form-group mt-1 mb-1 row mx-0">
														<label for="lastname" class="col-sm-2 col-form-label text-sm-right">'.T_('Nom').'</label>
														<div class="col-sm-3 ">
															<input type="text" class="form-control brc-on-focus brc-success-m2" id="lastname" name="lastname" value="'.$user1['lastname'].'" autocomplete="off">
														</div>
													</div>
													<div class="form-group mt-1 mb-1 row mx-0 '; if(!$rright['admin']) {echo 'd-none';} echo'">
														<label for="login" class="col-sm-2 col-form-label text-sm-right">'.T_('Identifiant').'</label>
														<div class="col-sm-3">
															<input type="text" class="form-control brc-on-focus brc-success-m2" id="login" name="login" value="'.$user1['login'].'" autocomplete="off">
														</div>
													</div>
													<div class="form-group mt-1 mb-1 row mx-0">
														<label for="password" class="col-sm-2 col-form-label text-sm-right">'.T_('Mot de passe').'</label>
														<div class="col-sm-3">
															<input type="password" class="form-control brc-on-focus brc-success-m2" id="password" name="password" autocomplete="new-password" value="" autocomplete="off">
														</div>
													</div>
													<div class="form-group mt-1 mb-1 row mx-0">
														<label for="password2" class="col-sm-2 col-form-label text-sm-right">'.T_('Confirmation mot de passe').'</label>
														<div class="col-sm-3">
															<input type="password" class="form-control brc-on-focus brc-success-m2" id="password2" name="password2" autocomplete="new-password" value="" autocomplete="off">
														</div>
													</div>
													<div class="form-group mt-1 mb-1 row mx-0">
														<label for="mail" class="col-sm-2 col-form-label text-sm-right">'.T_('Adresse mail').'</label>
														<div class="col-sm-3">
															<input type="email" class="form-control brc-on-focus brc-success-m2" id="mail" name="mail" value="'.$user1['mail'].'" autocomplete="off">
														</div>
													</div>
													<div class="form-group mt-1 mb-1 row mx-0">
														<label for="phone" class="col-sm-2 col-form-label text-sm-right">'.T_('Téléphone fixe').'</label>
														<div class="col-sm-3">
															<input type="text" class="form-control brc-on-focus brc-success-m2" id="phone" name="phone" value="'.$user1['phone'].'" autocomplete="off">
														</div>
													</div>
													<div class="form-group mt-1 mb-1 row mx-0">
														<label for="mobile" class="col-sm-2 col-form-label text-sm-right">'.T_('Téléphone mobile').'</label>
														<div class="col-sm-3">
															<input type="text" class="form-control brc-on-focus brc-success-m2" id="mobile" name="mobile" value="'.$user1['mobile'].'" autocomplete="off">
														</div>
													</div>
													<div class="form-group mt-1 mb-1 row mx-0">
														<label for="fax" class="col-sm-2 col-form-label text-sm-right">'.T_('Fax').'</label>
														<div class="col-sm-3">
															<input type="text" class="form-control brc-on-focus brc-success-m2" id="fax" name="fax" value="'.$user1['fax'].'" autocomplete="off">
														</div>
													</div>
													<div class="form-group mt-1 mb-1 row mx-0">
														<label for="fax" class="col-sm-2 col-form-label text-sm-right">'.T_('Service').'</label>
														<div class="col-sm-3">
															';
															if($rright['user_profil_service'])
															{
																echo '<select class="form-control" name="service">';
																	echo '<option value=""></option>';
																	$qry = $db->prepare("SELECT `id`,`name` FROM `tservices` WHERE `id`!=0 AND `id` NOT IN (SELECT DISTINCT(`service_id`) FROM `tusers_services` WHERE `user_id`=:user_id)");
																	$qry->execute(array('user_id' => $_GET['userid']));
																	while ($service=$qry->fetch())
																	{
																		echo '<option value="'.$service['id'].'">'.$service['name'].'</option>';
																	}
																echo '</select>';
															}

															//display current service associations
															$qry=$db->prepare("SELECT COUNT(`id`) FROM `tusers_services` WHERE user_id=:user_id");
															$qry->execute(array('user_id' => $_GET['userid']));
															$assoc=$qry->fetch();
															$qry->closeCursor();
															if($assoc[0]>0)
															{
																echo '
																<ul class="ml-3 pl-3 mt-1 pb-0 mb-0">
																';
																	$qry = $db->prepare("SELECT `tservices`.`id`,`tservices`.`name`, `tusers_services`.`id` AS assoc_id  FROM `tservices`,`tusers_services` WHERE `tservices`.`id`=`tusers_services`.`service_id` AND `tusers_services`.`user_id`=:user_id");
																	$qry->execute(array('user_id' => $_GET['userid']));
																	while ($service=$qry->fetch())
																	{
																		echo '<li>';
																		echo $service['name'];
																		if($rright['user_profil_service']) {echo '&nbsp;<a href="./index.php?page=admin/user&amp;action=edit&amp;userid='.$_GET['userid'].'&amp;tab=infos&amp;delete_assoc_service='.$service['assoc_id'].'" onClick="javascript: return confirm(\''.T_('Êtes-vous sur de vouloir supprimer cette association ?').'\');"><i title="'.T_("Supprimer l'association de ce service avec cet utilisateur").'" class="fa fa-trash text-danger"><!----></i></a>';}
																		echo '</li>';
																	}
																	echo '
																</ul>
																';
															}
															echo '
														</div>
													</div>
													';
													//agency field
													if($rparameters['user_agency'])
													{
														echo '
														<div class="form-group mt-1 mb-1 row mx-0">
															<label for="agency" class="col-sm-2 col-form-label text-sm-right">'.T_('Agence').'</label>
															<div class="col-sm-3">
																';
																//agency add field
																if($rright['user_profil_agency'])
																{
																	echo '<select class="form-control" name="agency">';
																	echo '<option value=""></option>';
																		$qry = $db->prepare("SELECT `id`,`name` FROM `tagencies` WHERE `id`!=0 AND `id` NOT IN (SELECT DISTINCT(`agency_id`) FROM `tusers_agencies` WHERE `user_id`=:user_id)");
																		$qry->execute(array('user_id' => $_GET['userid']));
																		while ($agency=$qry->fetch())
																		{
																			echo '<option value="'.$agency['id'].'">'.$agency['name'].'</option>';
																		}
																	echo '</select>';
																}
																//current agency associations
																$qry=$db->prepare("SELECT COUNT(`id`) FROM `tusers_agencies` WHERE user_id=:user_id");
																$qry->execute(array('user_id' => $_GET['userid']));
																$assoc=$qry->fetch();
																$qry->closeCursor();
																if($assoc[0]>0)
																{
																	echo '
																		<ul class="ml-3 pl-3 mt-1 pb-0 mb-0">
																		';
																			$qry = $db->prepare("SELECT `tagencies`.`id`,`tagencies`.`name`, `tusers_agencies`.`id` AS assoc_id  FROM `tagencies`,`tusers_agencies` WHERE `tagencies`.`id`=`tusers_agencies`.`agency_id` AND `tusers_agencies`.`user_id`=:user_id");
																			$qry->execute(array('user_id' => $_GET['userid']));
																			while ($agency=$qry->fetch())
																			{
																				echo '<li>';
																				echo $agency['name'];
																				if($rright['user_profil_agency']) {echo '&nbsp;<a href="./index.php?page=admin&amp;subpage=user&amp;action=edit&amp;userid='.$_GET['userid'].'&amp;tab=infos&amp;delete_assoc_agency='.$agency['assoc_id'].'" onClick="javascript: return confirm(\''.T_('Êtes-vous sur de vouloir supprimer cette association ?').'\');"><i title="'.T_("Supprimer l'association de cette agence avec cet utilisateur").'" class="fa fa-trash text-danger"><!----></i></a>';}
																				echo '</li>';
																			}
																			echo '
																		</ul>
																	';
																} 
																echo '
															</div>
														</div>
														';
													}
													echo '
													<div class="form-group mt-1 mb-1 row mx-0">
														<label for="function" class="col-sm-2 col-form-label text-sm-right">'.T_('Fonction').'</label>
														<div class="col-sm-3">
															<input type="text" class="form-control brc-on-focus brc-success-m2" id="function" name="function" value="'.$user1['function'].'" autocomplete="off">
														</div>
													</div>
													';
													//display advanced user informations
													if($rparameters['user_advanced'])
													{
													echo '
														<div class="form-group mt-1 mb-1 row mx-0">
															<label for="company" class="col-sm-2 col-form-label text-sm-right">'.T_('Société').'</label>
															<div class="col-sm-3">
																<select style="width:100%" id="company" name="company" '; if($rright['user_profil_company']==0) {echo 'disabled="disabled"';} echo ' autocomplete="off">
																	';
																	$qry = $db->prepare("SELECT `id`,`name` FROM `tcompany` ORDER BY `name`");
																	$qry->execute();
																	while ($row = $qry->fetch())
																	{
																		if($user1['company']==$row['id']) {echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';} else {echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';}
																	} 
																	$qry->closeCursor();
																	echo '
																</select>
																';
																//send company value in disabled state
																if($rright['user_profil_company']==0)
																{
																	echo '<input type="hidden" name="company" value="'.$user1['company'].'" />';
																}
																echo '
															</div>
														</div>
														<div class="form-group mt-1 mb-1 row mx-0">
															<label for="address1" class="col-sm-2 col-form-label text-sm-right">'.T_('Adresse').' 1</label>
															<div class="col-sm-3">
															<input type="text" class="form-control brc-on-focus brc-success-m2" id="address1" name="address1" value="'.$user1['address1'].'" autocomplete="off">
															</div>
														</div>
														<div class="form-group mt-1 mb-1 row mx-0">
															<label for="address2" class="col-sm-2 col-form-label text-sm-right">'.T_('Adresse').' 2</label>
															<div class="col-sm-3">
															<input type="text" class="form-control brc-on-focus brc-success-m2" id="address2" name="address2" value="'.$user1['address2'].'" autocomplete="off">
															</div>
														</div>
														<div class="form-group mt-1 mb-1 row mx-0">
															<label for="city" class="col-sm-2 col-form-label text-sm-right">'.T_('Ville').'</label>
															<div class="col-sm-3">
																<input type="text" class="form-control brc-on-focus brc-success-m2" id="city" name="city" value="'.$user1['city'].'" autocomplete="off">
															</div>
														</div>
														<div class="form-group mt-1 mb-1 row mx-0">
															<label for="zip" class="col-sm-2 col-form-label text-sm-right">'.T_('Code postal').'</label>
															<div class="col-sm-3">
																<input type="text" class="form-control brc-on-focus brc-success-m2" id="zip" name="zip" value="'.$user1['zip'].'" autocomplete="off">
															</div>
														</div>
														<div class="form-group mt-1 mb-1 row mx-0">
															<label for="custom1" class="col-sm-2 col-form-label text-sm-right">'.T_('Champ personnalisé').' 1</label>
															<div class="col-sm-3">
																<input type="text" class="form-control brc-on-focus brc-success-m2" id="custom1" name="custom1" value="'.$user1['custom1'].'" autocomplete="off">
															</div>
														</div>
														<div class="form-group mt-1 mb-1 row mx-0">
															<label for="custom2" class="col-sm-2 col-form-label text-sm-right">'.T_('Champ personnalisé').' 2</label>
															<div class="col-sm-3">
																<input type="text" class="form-control brc-on-focus brc-success-m2" id="custom2" name="custom2" value="'.$user1['custom2'].'" autocomplete="off">
															</div>
														</div>
													';
													}
													//START plugin fields part
														$section='admin_user_infos';
														include('plugin.php');
													//END plugin fields part	
													echo '		
												</div>
                            			    </div>
                            			</div>
                            		</div>
                            	</div>
							</fieldset>
							<div class="border-t-1 brc-secondary-l1 bgc-secondary-l3 py-3 text-center mt-5">
								<button title="'.T_('CTRL+S').'" value="modify" id="modify" name="modify" type="submit" class="btn btn-success">
									<i class="fa fa-save"><!----></i>
									'.T_('Modifier').'
								</button>
								&nbsp;&nbsp;&nbsp;&nbsp;
								<button name="cancel" value="cancel" type="submit" class="btn btn-danger" >
									<i class="fa fa-reply"><!----></i>
									'.T_('Retour').'
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	';
}

?>