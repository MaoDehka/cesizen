<?php
################################################################################
# @Name : ./admin/users/add.php
# @Description : add user
# @Call : /admin/user.php
# @Parameters : 
# @Author : Flox
# @Create : 17/09/2021
# @Update : 31/08/2023
# @Version : 3.2.38
################################################################################

if(($_POST['add_btn1'] || $_POST['add_btn2']) && $rright['admin'])
{
	if(!$_POST['password']) {$error=T_('Vous devez spécifier un mot de passe');}
	if(!$_POST['login']) {$error=T_('Vous devez spécifier un identifiant');}
	if($_POST['password']!=$_POST['password2']) {$error=T_('Les mots de passe ne sont pas identiques');}
	if($_POST['mail'])
	{
		if(!filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL)) {$error=T_("L'adresse mail est incorrecte");}
	}
	if($_POST['login'])
	{
		$qry=$db->prepare("SELECT `id` FROM `tusers` WHERE `login`=:login AND `disable`='0'");
		$qry->execute(array('login' => $_POST['login']));
		$row=$qry->fetch();
		$qry->closeCursor();
		
		if($row) {$error=T_('Un autre compte utilise déjà cet identifiant');}
	}
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
			$error=T_('Le mot de passe doit au moins une lettre majuscule et une minuscule');
		}
	}
	
	if(!$error)
	{
		//hash password
		$hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
		
		$qry=$db->prepare("
		INSERT INTO tusers (
		`firstname`,
		`lastname`,
		`password`,
		`mail`,
		`phone`,
		`mobile`,
		`fax`,
		`company`,
		`address1`,
		`address2`,
		`zip`,
		`city`,
		`custom1`,
		`custom2`,
		`profile`,
		`login`,
		`chgpwd`,
		`last_pwd_chg`,
		`skin`,
		`function`,
		`planning_color`
		) VALUES (
		:firstname,
		:lastname,
		:password,
		:mail,
		:phone,
		:mobile,
		:fax,
		:company,
		:address1,
		:address2,
		:zip,
		:city,
		:custom1,
		:custom2,
		:profile,
		:login,
		:chgpwd,
		:last_pwd_chg,
		:skin,
		:function,
		:planning_color
		)");
		$qry->execute(array(
			'firstname' => $_POST['firstname'],
			'lastname' => $_POST['lastname'],
			'password' => $hash,
			'mail' => $_POST['mail'],
			'phone' => $_POST['phone'],
			'mobile' => $_POST['mobile'],
			'fax' => $_POST['fax'],
			'company' => $_POST['company'],
			'address1' => $_POST['address1'],
			'address2' => $_POST['address2'],
			'zip' => $_POST['zip'],
			'city' => $_POST['city'],
			'custom1' => $_POST['custom1'],
			'custom2' => $_POST['custom2'],
			'profile' => $_POST['profile'],
			'login' => $_POST['login'],
			'chgpwd' => $_POST['chgpwd'],
			'last_pwd_chg' => date('Y-m-d'),
			'skin' => $_POST['skin'],
			'function' => $_POST['function'],
			'planning_color' => $_POST['planning_color']
			));
		$last_user_id=$db->lastInsertId();
		//if post service insert new assoc
		if($_POST['service'])
		{
			$qry=$db->prepare("INSERT INTO `tusers_services` (`user_id`,`service_id`) VALUES (:user_id,:service_id)");
			$qry->execute(array('user_id' => $last_user_id,'service_id' => $_POST['service']));
		}
		if($rparameters['user_agency'])
		{
			if($_POST['agency']) //if post agency insert new assoc
			{
				$qry=$db->prepare("INSERT INTO `tusers_agencies` (`user_id`,`agency_id`) VALUES (:user_id,:agency_id)");
				$qry->execute(array('user_id' => $last_user_id,'agency_id' => $_POST['agency']));
			}
		}
		if($rparameters['log'] && $_POST['profile']==4)
		{
			require_once('core/functions.php');
			logit('security', 'Admin account has been added '.$_POST['login'],$_SESSION['user_id']);
		}
		//redirect
		$www = "./index.php?page=admin&subpage=user";
		echo '<script language="Javascript">
		<!--
		document.location.replace("'.$www.'");
		// -->
		</script>';
	} else {
        echo '
            <div role="alert" class="alert alert-lg bgc-danger-l3 border-0 border-l-4 brc-danger-m1 mt-4 mb-3 pr-3 d-flex">
                <div class="flex-grow-1">
                    <i class="fas fa-times mr-1 text-120 text-danger-m1"><!----></i>
                    <strong class="text-danger">'.T_('Erreur').' : '.$error.'.</strong>
                </div>
                <button type="button" class="close align-self-start" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times text-80"><!----></i></span>
                </button>
            </div>
        ';
    }
}


if($_GET['action']=="add" && (($_SESSION['user_id']==$_GET['userid']) || $rright['admin']))
{
	//remove '' to display
	$_POST['firstname']=str_replace("'","",$_POST['firstname']);
	$_POST['lastname']=str_replace("'","",$_POST['lastname']);
	$_POST['login']=str_replace("'","",$_POST['login']);
	$_POST['password']=str_replace("'","",$_POST['password']);
	$_POST['mail']=str_replace("'","",$_POST['mail']);
	$_POST['phone']=str_replace("'","",$_POST['phone']);
	$_POST['mobile']=str_replace("'","",$_POST['mobile']);
	$_POST['fax']=str_replace("'","",$_POST['fax']);
	$_POST['function']=str_replace("'","",$_POST['function']);
	
	/////////////////////////////////////////////////////////////////////display add form///////////////////////////////////////////////////
	echo '
		<div class="col-12 cards-container">
			<div class="card bcard shadow">
				<div class="card-header">
					<h5 class="card-title">'.T_("Nouvel utilisateur").' :</h5>
					<span class="card-toolbar">
						<button title="'.T_('Ajouter un utilisateur').'" value="add" id="add_btn1" name="add_btn1" type="submit" form="add_user" class="btn btn-xs btn-success ml-1">
							<i class="fa fa-save text-110"><!----></i>
						</button>
					</span>
				</div>
				<div class="card-body">
					<div class="card-main no-padding">
						<form id="add_user" name="add_user" method="POST" action="">
							<div class="form-group">
								<div class="form-group mt-3 mb-1 row mx-0">
									<label for="firstname" class="col-sm-3 col-form-label text-sm-right">'.T_('Prénom').'</label>
									<div class="col-sm-3">
										<input type="text" class="form-control brc-on-focus brc-success-m2" id="firstname" name="firstname" value="'.$_POST['firstname'].'" autocomplete="off">
									</div>
								</div>
								<div class="form-group mt-1 mb-1 row mx-0">
									<label for="lastname" class="col-sm-3 col-form-label text-sm-right">'.T_('Nom').'</label>
									<div class="col-sm-3 ">
										<input type="text" class="form-control brc-on-focus brc-success-m2" id="lastname" name="lastname" value="'.$_POST['lastname'].'" autocomplete="off">
									</div>
								</div>
								<div class="form-group mt-1 mb-1 row mx-0 '; if(!$rright['admin']) {echo 'd-none';} echo'">
									<label for="login" class="col-sm-3 col-form-label text-sm-right">'.T_('Identifiant').'</label>
									<div class="col-sm-3">
										<input type="text" class="form-control brc-on-focus brc-success-m2" id="login" name="login" value="'.$_POST['login'].'" autocomplete="off">
									</div>
								</div>
								<div class="form-group mt-1 mb-1 row mx-0">
									<label for="password" class="col-sm-3 col-form-label text-sm-right">'.T_('Mot de passe').'</label>
									<div class="col-sm-3">
										<input type="password" class="form-control brc-on-focus brc-success-m2" id="password" name="password" autocomplete="new-password" value="" autocomplete="off">
									</div>
								</div>
								<div class="form-group mt-1 mb-1 row mx-0">
									<label for="password2" class="col-sm-3 col-form-label text-sm-right">'.T_('Confirmation mot de passe').'</label>
									<div class="col-sm-3">
										<input type="password" class="form-control brc-on-focus brc-success-m2" id="password2" name="password2" autocomplete="new-password" value="" autocomplete="off">
									</div>
								</div>
								<div class="form-group mt-1 mb-1 row mx-0">
									<label for="mail" class="col-sm-3 col-form-label text-sm-right">'.T_('Adresse mail').'</label>
									<div class="col-sm-3">
										<input type="email" class="form-control brc-on-focus brc-success-m2" id="mail" name="mail" value="'.$_POST['mail'].'" autocomplete="off">
									</div>
								</div>
								<div class="form-group mt-1 mb-1 row mx-0">
									<label for="phone" class="col-sm-3 col-form-label text-sm-right">'.T_('Téléphone fixe').'</label>
									<div class="col-sm-3">
										<input type="text" class="form-control brc-on-focus brc-success-m2" id="phone" name="phone" value="'.$_POST['phone'].'" autocomplete="off">
									</div>
								</div>
								<div class="form-group mt-1 mb-1 row mx-0">
									<label for="mobile" class="col-sm-3 col-form-label text-sm-right">'.T_('Téléphone mobile').'</label>
									<div class="col-sm-3">
										<input type="text" class="form-control brc-on-focus brc-success-m2" id="mobile" name="mobile" value="'.$_POST['mobile'].'" autocomplete="off">
									</div>
								</div>
								<div class="form-group mt-1 mb-1 row mx-0">
									<label for="fax" class="col-sm-3 col-form-label text-sm-right">'.T_('Fax').'</label>
									<div class="col-sm-3">
										<input type="text" class="form-control brc-on-focus brc-success-m2" id="fax" name="fax" value="'.$_POST['fax'].'" autocomplete="off">
									</div>
								</div>
								<div class="form-group mt-1 mb-1 row mx-0">
									<label for="service" class="col-sm-3 col-form-label text-sm-right">'.T_('Service').'</label>
									<div class="col-sm-3">
										<select class="form-control" name="service" id="service" '; if($rright['user_profil_service']==0) {echo 'disabled="disabled"';} echo '>
											<option value=""></option>';
											$qry = $db->prepare("SELECT `id`,`name` FROM `tservices` ORDER BY `name`");
											$qry->execute();
											while ($row = $qry->fetch()) 
											{
												if($_POST['service']) {echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';} else {echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';}
											} 
											$qry->closeCursor();
											echo '
										</select>
									</div>
								</div>
								';
								if($rparameters['user_agency'])
								{
									echo '
									<div class="form-group mt-1 mb-1 row mx-0">
										<label for="agency" class="col-sm-3 col-form-label text-sm-right">'.T_('Agence').'</label>
										<div class="col-sm-3">
											<select class="form-control"  name="agency" '; if($rright['user_profil_agency']==0) {echo 'disabled="disabled"';} echo '>
												<option value=""></option>';
												$qry = $db->prepare("SELECT `id`,`name` FROM `tagencies` ORDER BY `name`");
												$qry->execute();
												while ($row = $qry->fetch()) 
												{
													if($_POST['agency']) {echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';} else {echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';}
												} 
												$qry->closeCursor();
												echo '
											</select>
										</div>
									</div>
									';
								}
								echo '
								<div class="form-group mt-1 mb-1 row mx-0">
									<label for="function" class="col-sm-3 col-form-label text-sm-right">'.T_('Fonction').'</label>
									<div class="col-sm-3">
										<input type="text" class="form-control brc-on-focus brc-success-m2" id="function" name="function" value="'.$_POST['function'].'" autocomplete="off">
									</div>
								</div>
								';
								//display advanced user informations
								if($rparameters['user_advanced'])
								{
								echo '
									<div class="form-group mt-1 mb-1 row mx-0">
										<label for="company" class="col-sm-3 col-form-label text-sm-right">'.T_('Société').'</label>
										<div class="col-sm-3">
											<select style="width:100%" id="company" name="company" '; if($rright['user_profil_company']==0) {echo 'disabled="disabled"';} echo ' autocomplete="off">
												<option value=""></option>';
												$qry = $db->prepare("SELECT `id`,`name` FROM `tcompany` ORDER BY `name`");
												$qry->execute();
												while ($row = $qry->fetch()) 
												{
													if($user1['company']==$row['id']) echo "<option selected value=\"$row[id]\">$row[name]</option>"; else echo "<option value=\"$row[id]\">$row[name]</option>";
												} 
												$qry->closeCursor(); 
												echo '
											</select>
										</div>
									</div>
									<div class="form-group mt-1 mb-1 row mx-0">
										<label for="address1" class="col-sm-3 col-form-label text-sm-right">'.T_('Adresse').' 1</label>
										<div class="col-sm-3">
											<input type="text" class="form-control brc-on-focus brc-success-m2" id="address1" name="address1" value="'.$_POST['address1'].'" autocomplete="off">
										</div>
									</div>
									<div class="form-group mt-1 mb-1 row mx-0">
										<label for="address2" class="col-sm-3 col-form-label text-sm-right">'.T_('Adresse').' 2</label>
										<div class="col-sm-3">
											<input type="text" class="form-control brc-on-focus brc-success-m2" id="address2" name="address2" value="'.$_POST['address2'].'" autocomplete="off">
										</div>
									</div>
									<div class="form-group mt-1 mb-1 row mx-0">
										<label for="city" class="col-sm-3 col-form-label text-sm-right">'.T_('Ville').'</label>
										<div class="col-sm-3">
											<input type="text" class="form-control brc-on-focus brc-success-m2" id="city" name="city" value="'.$_POST['city'].'" autocomplete="off">
										</div>
									</div>
									<div class="form-group mt-1 mb-1 row mx-0">
										<label for="zip" class="col-sm-3 col-form-label text-sm-right">'.T_('Code postal').'</label>
										<div class="col-sm-3">
											<input type="text" class="form-control brc-on-focus brc-success-m2" id="zip" name="zip" value="'.$_POST['zip'].'" autocomplete="off">
										</div>
									</div>
									<div class="form-group mt-1 mb-1 row mx-0">
										<label for="custom1" class="col-sm-3 col-form-label text-sm-right">'.T_('Champ personnalisé').' 1</label>
										<div class="col-sm-3">
											<input type="text" class="form-control brc-on-focus brc-success-m2" id="custom1" name="custom1" value="'.$_POST['custom1'].'" autocomplete="off">
										</div>
									</div>
									<div class="form-group mt-1 mb-1 row mx-0">
										<label for="custom2" class="col-sm-3 col-form-label text-sm-right">'.T_('Champ personnalisé').' 2</label>
										<div class="col-sm-3">
											<input type="text" class="form-control brc-on-focus brc-success-m2" id="custom2" name="custom2" value="'.$_POST['custom2'].'" autocomplete="off">
										</div>
									</div>
								';
								}
								echo '
								<hr />
								<div class="form-group mt-1 mb-1 row mx-0">
									<label for="skin" class="col-sm-3 col-form-label text-sm-right">'.T_('Thème').'</label>
									<div class="col-sm-3">
									<select class="form-control" id="skin" name="skin">
										<option '; if($rparameters['default_skin']==''){echo "selected";} echo ' value="">'.T_('Bleu (Défaut)').'</option>
										<option '; if($rparameters['default_skin']=='skin-3'){echo "selected";} echo ' value="skin-3">'.T_('Gris').'</option>
										<option '; if($rparameters['default_skin']=='skin-1'){echo "selected";} echo ' value="skin-1">'.T_('Noir').'</option>
										<option '; if($rparameters['default_skin']=='skin-2'){echo "selected";} echo ' value="skin-2">'.T_('Violet').'</option>
										<option '; if($rparameters['default_skin']=='skin-5'){echo "selected";} echo ' value="skin-5">'.T_('Vert').'</option>
										<option '; if($rparameters['default_skin']=='skin-7'){echo "selected";} echo ' value="skin-7">'.T_('Vert et violet').'</option>
										<option '; if($rparameters['default_skin']=='skin-6'){echo "selected";} echo ' value="skin-6">'.T_('Orange').'</option>
										<option '; if($rparameters['default_skin']=='skin-8'){echo "selected";} echo ' value="skin-8">'.T_('Orange et violet').'</option>
										<option '; if($rparameters['default_skin']=='skin-4'){echo "selected";} echo ' value="skin-4">'.T_('Sombre').'</option>
									</select>
									</div>
								</div>
								';
								//display planning color 
								if($rright['planning_tech_color'] && ($rright['ticket_tech_admin'] || $rright['ticket_tech_super'] || $_SESSION['profile_id']=='0'))
								{
									echo '
									<div class="form-group mt-1 mb-1 row mx-0">
										<label for="planning_color" class="col-sm-3 col-form-label text-sm-right">'.T_('Couleur dans le calendrier').'</label>
										<div class="col-sm-3">
											<input type="text" class="form-control brc-on-focus brc-success-m2" id="planning_color" name="planning_color" autocomplete="off" value="'.$_POST['planning_color'].'" autocomplete="off">
										</div>
									</div>
									';
								}
								// Display profile list
								if($rright['admin_user_profile']!='0')
								{
									echo '
									<hr />
									<div class="form-group mt-1 mb-1 row mx-0">
										<span for="profile" class="col-sm-3 col-form-label text-sm-right">'.T_('Profil').'</span>
										<div class="col-sm-3">
											<div class="controls mt-2">
												<div class="radio">
													<label>
														<input type="radio" class="ace" name="profile" value="4"> <span class="lbl"> '.T_('Administrateur').' </span>
													</label>
												</div>
												<div class="radio">
													<label>
														<input type="radio" class="ace" name="profile" value="0"> <span class="lbl"> '.T_('Technicien').' </span>
													</label>
												</div>
												<div class="radio">
													<label>
														<input type="radio" class="ace" name="profile" value="3"> <span class="lbl"> '.T_('Superviseur').' </span>
													</label>
												</div>
												<div class="radio">
													<label>
														<input type="radio" class="ace" name="profile" value="1"> <span class="lbl"> '.T_('Utilisateur avec pouvoir').' </span>
													</label>
												</div>
												<div class="radio">
													<label>
														<input type="radio" class="ace" name="profile" checked value="2"> <span class="lbl"> '.T_('Utilisateur').' </span>
													</label>
												</div>
											</div>
										</div>
									</div>
									<hr />
									<div class="form-group mt-1 mb-1 row mx-0">
										<span for="chgpwd" class="col-sm-3 col-form-label text-sm-right">'.T_('Forcer le changement du mot de passe').'</span>
										<div class="col-sm-3">
											<div class="controls">
												<label class="mt-2">
													<input type="radio" class="ace" name="chgpwd" checked value="1"> <span class="lbl"> '.T_('Oui').' </span>
													<input type="radio" class="ace" name="chgpwd" value="0"> <span class="lbl"> '.T_('Non').' </span>
												</label>
											</div>
										</div>
									</div>
									';
								}
								else
								{
									echo '<input type="hidden" name="profile" value="">';
								}
								echo '
							</div>
							<div class="border-t-1 brc-secondary-l1 bgc-secondary-l3 py-3 text-center mt-5">
								<button title="'.T_('CTRL+S').'" value="add" id="add_btn2" name="add_btn2" type="submit" class="btn btn-success mr-2">
									<i class="fa fa-check"><!----></i>
									'.T_('Ajouter').'
								</button>
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