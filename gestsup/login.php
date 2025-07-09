<?php
################################################################################
# @Name : login.php
# @Description : Login page for enter credentials and redirect to register page
# @Call : index.php
# @Parameters : 
# @Author : Flox
# @Create : 07/03/2010
# @Update : 26/02/2024
# @Version : 3.2.49
################################################################################

//initialize variables 
if(!isset($state)) $state = ''; 
if(!isset($userid)) $userid = ''; 
if(!isset($techread)) $techread = '';
if(!isset($find_login)) $find_login = '';
if(!isset($profile)) $profile = '';
if(!isset($newpassword)) $newpassword = '';
if(!isset($salt)) $salt= '';
if(!isset($dcgen)) $dcgen= '';
if(!isset($ldap_type)) $ldap_type= '';
if(!isset($error)) $error= '';
if(!isset($_SESSION['user_id'])) $_SESSION['user_id'] = '';
if(!isset($_SESSION['login'])) $_SESSION['login'] = ''; 
if(!isset($_SESSION['code'])) $_SESSION['code'] = ''; 
if(!isset($_POST['captcha'])) $_POST['captcha'] = '';

if(!isset($db)) {header('HTTP/1.0 403 Forbidden'); exit;}

//check for captcha display
$qry=$db->prepare("SELECT `attempts` FROM `tauth_attempts` WHERE ip=:ip");
$qry->execute(array('ip' => $_SERVER['REMOTE_ADDR']));
$ip_attempts=$qry->fetch();
$qry->closeCursor();
if(empty($ip_attempts['attempts'])) {$ip_attempts=array(); $ip_attempts['attempts']=0;} 

if($_GET['state']=='') $_GET['state'] = '%';


//hide inputs fields in azure sso multi tenant
if($rparameters['azure_ad_sso'] && $rparameters['azure_ad_tenant_number']==2 && $_GET['local_auth']!=1){$hide_login='d-none';} else {$hide_login='';}

//actions on submit
if($_POST['submit'])
{
	//include plugin
	$section='login_post';
	include('./plugin.php');

	//display login sso azure multi tenant
	if($_POST['submit']=='local_auth') {
		$www="./index.php?local_auth=1"; 
		echo "<SCRIPT LANGUAGE='JavaScript'>
			<!--
			function redirect()
			{
			window.location='$www'
			}
			setTimeout('redirect()');
			-->
			</SCRIPT>";
			exit;
	}

	$login=(isset($_POST['login'])) ? $_POST['login'] : '';
	$pass=(isset($_POST['pass'])) ? $_POST['pass']  : '';

	if($ip_attempts['attempts']>10 && $_POST['captcha']!=$_SESSION['code']){$error=T_('Captcha invalide');}
	
	$qry=$db->prepare("SELECT `id`,`login`,`password`,`salt`,`profile`,`ldap_guid`,`disable` FROM `tusers`");
	$qry->execute();
	while ($row = $qry->fetch()) 
	{
		//uppercase login converter to compare
		$login=strtoupper($login);
		$db_login=strtoupper($row['login']);
		$find_ldap_guid='';

		if($login && $pass && ($db_login == $login) && $row['password']!='' && $row['disable']==0 && !$error) //check existing login
		{
			if(strlen($row['password'])>33) //hash detection
			{
				if(password_verify($pass, $row['password'])) {
					$find_login=$row['login'];
					if($rparameters['ldap_auth'] && $rparameters['ldap']) {$find_ldap_guid=$row['ldap_guid'];}
					$user_id=$row['id'];
					$profile=$row['profile'];
					break;
				}
			}elseif($row['password']==md5($row['salt'] . md5($pass))) //md5 has detect allow and convert hash, using for hash transition
			{ 
				$find_login=$row['login'];
				if($rparameters['ldap_auth'] && $rparameters['ldap']) {$find_ldap_guid=$row['ldap_guid'];}
				$user_id=$row['id'];
				$profile=$row['profile'];
				//update hash
				$hash=password_hash($pass, PASSWORD_DEFAULT);
				$qry2=$db->prepare("UPDATE `tusers` SET `password`=:password WHERE `id`=:id");
				$qry2->execute(array('password' => $hash,'id' => $row['id']));
				break;
			}
		}	
	}
	$qry->closeCursor();
	if($find_login && !$find_ldap_guid && !$error) 
	{	
		$_SESSION['login']=$find_login;
		$_SESSION['user_id']=$user_id;
		
		//reset attempt counter
		if($rparameters['user_disable_attempt'])
		{
			$qry=$db->prepare("UPDATE `tusers` SET `auth_attempt`=0 WHERE `id`=:id");
			$qry->execute(array('id' => $_SESSION['user_id']));
		}
		//check new ip connexion for admin
		if($rparameters['user_admin_ip'] && $rparameters['mail'])
		{
			//check profile
			$qry = $db->prepare("SELECT `profile`,`mail` FROM `tusers` WHERE id=:id");
			$qry->execute(array('id' => $_SESSION['user_id']));
			$user_login=$qry->fetch();
			$qry->closeCursor();
			if($user_login['profile']==4 && $user_login['mail'])
			{
				//check ipv6 or ipv4
				if(preg_match('/:/',$_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']!='::1')
				{
					//keep prefix only
					$ip_to_check=explode(':',$_SERVER['REMOTE_ADDR']);
					$ip_to_check=$ip_to_check[0].':'.$ip_to_check[1].':'.$ip_to_check[2].':'.$ip_to_check[3].'%';
				
					$qry=$db->prepare("SELECT `id` FROM `tusers_ip` WHERE `ip` LIKE :ip AND `user_id`=:user_id");
					$qry->execute(array('ip' => $ip_to_check,'user_id' => $_SESSION['user_id']));
					$ip=$qry->fetch();
					$qry->closeCursor();
				} else {
					//check ip exist
					$qry=$db->prepare("SELECT `id` FROM `tusers_ip` WHERE `ip`=:ip AND `user_id`=:user_id");
					$qry->execute(array('ip' => $_SERVER['REMOTE_ADDR'],'user_id' => $_SESSION['user_id']));
					$ip=$qry->fetch();
					$qry->closeCursor();
				}
				
				if(!isset($ip[0]))
				{
					//add ip 
					$qry=$db->prepare("INSERT INTO `tusers_ip` (`user_id`,`ip`) VALUES (:user_id,:ip)");
					$qry->execute(array('user_id' => $_SESSION['user_id'],'ip' => $_SERVER['REMOTE_ADDR']));

					//notify
					if($rparameters['mail'])
					{
						//mail definition
						$from=$rparameters['mail_from_adr'];
						$to=$user_login['mail'];
						$object=T_('Nouvelle connexion sur votre compte administrateur GestSup');
						$message = '
						'.T_('Bonjour,').' <br /><br /><br />
						'.T_("Une nouvelle connexion a été détectée sur votre compte administrateur GestSup, sur l'IP").' '.$_SERVER['REMOTE_ADDR'].'.<br /><br />
						'.T_("Si ce n'était pas vous merci de changer votre mot de passe. ").'<br /><br />
						'.T_('Cordialement').'.';
						require('./core/message.php');
					}
				} 
			}
		}
		//delete ip attempt
		$qry=$db->prepare("DELETE FROM `tauth_attempts` WHERE ip=:ip");
		$qry->execute(array('ip' => $_SERVER['REMOTE_ADDR']));
		//update last time connection
		$qry=$db->prepare("UPDATE `tusers` SET `last_login`=:last_login,`ip`=:ip WHERE `id`=:id");
		$qry->execute(array('last_login' => $datetime,'ip' => $_SERVER['REMOTE_ADDR'],'id' => $user_id));
		//display loading 
		echo '<i class="fa fa-spinner fa-spin text-info text-120"></i>&nbsp;Chargement...';
		//user pref default redirection state
		$qry = $db->prepare("SELECT * FROM `tusers` WHERE id=:id");
		$qry->execute(array('id' => $_SESSION['user_id']));
		$ruser=$qry->fetch();
		$qry->closeCursor();
		if(!isset($ruser['default_ticket_state'])) {$ruser=array(); $ruser['default_ticket_state']='';}
		if($ruser['default_ticket_state']) {$redirectstate=$ruser['default_ticket_state'];} else {$redirectstate=$rparameters['login_state'];}
		if(!isset($redirectstate)) {$redirectstate='all';}
		//check right side all
		$qry=$db->prepare("SELECT `side_all` FROM `trights` WHERE `profile`=(SELECT `profile` FROM `tusers` WHERE id=:id);");
		$qry->execute(array('id' => $_SESSION['user_id']));
		$rright=$qry->fetch();
		$qry->closeCursor();
		//select page to redirect
		if($_GET['id']) { //email case
			$www='./index.php?page=ticket&id='.$_GET['id'];
		} else { //parameters case
			if($redirectstate=='activity')
			{
				$www='./index.php?page=dashboard&userid=%25&view=activity&date_start='.date('d/m/Y').'&date_end='.date('d/m/Y');
			}elseif($redirectstate=='meta_all')
			{
				$www="./index.php?page=dashboard&userid=%25&state=meta";
			}elseif($redirectstate=='all' && $rright['side_all'])
			{
				$www="./index.php?page=dashboard&userid=%25&state=%25";
			}elseif($redirectstate=='all' && !$rright['side_all'])
			{
				$www="./index.php?page=dashboard&userid=$user_id&state=%25";
			} else {
				$www="./index.php?page=dashboard&userid=$user_id&state=$redirectstate";
			}
		}
		
		//web redirection
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
	elseif($rparameters['ldap'] && $rparameters['ldap_auth'] && !$error) // if gestsup user is not found and LDAP is enable, search in LDAP
	{
		
		//check ldap extension
		if (!extension_loaded('ldap')) {
			echo DisplayMessage('error',T_("L'extension PHP LDAP n'est pas activée sur votre serveur, modifier le fichier php.ini"));
			LogIt('error','ERROR 2 : LDAP, extension not loaded (login.php)',0);
			exit;
		}
		
		//LDAP connect
		if($rparameters['ldap_port']==636) {$hostname='ldaps://'.$rparameters['ldap_server'];} else {$hostname=$rparameters['ldap_server'];}
		if(phpversion()>'8.3.0')
		{
			if($rparameters['ldap_port']=='389')
			{
				$ldap = ldap_connect('ldap://'.$rparameters['ldap_server'].':'.$rparameters['ldap_port']) or die("Unable to connect to LDAP server"); 
			} else {
				$ldap = ldap_connect('ldaps://'.$rparameters['ldap_server'].'/') or die("Unable to connect to LDAP server");
			}
		} else {
			$ldap = ldap_connect($hostname,$rparameters['ldap_port']) or die("Unable to connect to LDAP server");
		}

		ldap_set_option($ldap, LDAP_OPT_NETWORK_TIMEOUT, 1);
		ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
		$domain=$rparameters['ldap_domain'];
		if($rparameters['ldap_type']==0 || $rparameters['ldap_type']==3) //AD and Samba4 
		{
			//if UPN not add domain suffix
			if($rparameters['ldap_login_field']=='UserPrincipalName')
			{
				$ldapbind = @ldap_bind($ldap, $login, $pass);
			} else { 
				$ldapbind = @ldap_bind($ldap, "$login@$domain", $pass);
			}
		} elseif($rparameters['ldap_type']==4) //Kwartz
		{
			$ldapbind = @ldap_bind($ldap, "uid=$user,ou=Users$domain", $pass);
		} else { //OpenLDAP
			//generate DC chain from domain parameter
			$dcpart=explode(".",$domain);
			$i=0;
			while($i<count($dcpart)) {
				$dcgen="$dcgen,dc=$dcpart[$i]";
				$i++;
			}
			if(preg_match('/gs_en/',$rparameters['ldap_password'])) {$rparameters['ldap_password']=gs_crypt($rparameters['ldap_password'], 'd' , $rparameters['server_private_key']);}
			
			$dn='uid='.$login.','.$rparameters['ldap_url'].$dcgen;
			$ldapbind = @ldap_bind($ldap, $dn, $pass);
			if(!$ldapbind)
			{
				//if user not in base dn search it in sub ou to get user dn
				$basedn=$rparameters['ldap_url'].$dcgen;
				$filter="(uid=$login)";
				$res = ldap_search($ldap, $basedn, $filter);
				$first = ldap_first_entry($ldap, $res);
				if($first)
				{
					$dn = ldap_get_dn($ldap, $first);
					$ldapbind = @ldap_bind($ldap, $dn, $pass);
					if(!$ldapbind)
					{
						if($rparameters['debug']) {echo DisplayMessage('error',"Unable to bind on OpenLDAP Server (dn: $dn filter: uid=$login basedn: $basedn)");}
						$ldapbind=0;
					}
				} else {
					if($rparameters['debug']) {echo DisplayMessage('error',"Unable to bind on OpenLDAP Server, user not found (dn: $dn filter: uid=$login basedn: $basedn)");}
					$ldapbind=0;
				}
			}
		}
		
		if($ldapbind && $pass!='') 
		{
			$_SESSION['login'] = $login;
			//LogIt('login_access','ldap connect with login '.$login,'0');
			$qry = $db->prepare("SELECT `id`,`password` FROM `tusers` WHERE `login`=:login AND `disable`='0'");
			$qry->execute(array('login' => $login));
			$r=$qry->fetch();
			$qry->closeCursor();
			if(empty($r['id'])) {$r['id']='';}
			$_SESSION['user_id'] = $r['id'];
			if(empty($r['id'])) //to check
			{
				//if error with login or password 
				echo DisplayMessage('error', 'Utilisateur non synchronisé dans base utilisateurs GestSup');
				session_destroy();
				//web redirection to login page
				echo "<SCRIPT LANGUAGE='JavaScript'>
						<!--
						function redirect()
						{
						window.location='./index.php'
						}
						setTimeout('redirect()',$rparameters[time_display_msg]+1000);
						-->
					</SCRIPT>";
			} else {
				//update last time connection
				$qry=$db->prepare("UPDATE `tusers` SET `last_login`=:last_login,`ip`=:ip WHERE `id`=:id");
				$qry->execute(array('last_login' => $datetime,'ip' => $_SERVER['REMOTE_ADDR'],'id' => $r['id']));
				
				//update GS db pwd
				$newpassword = password_hash($pass, PASSWORD_DEFAULT);
				//update password
				$qry=$db->prepare("UPDATE `tusers` SET `password`=:password WHERE `id`=:id");
				$qry->execute(array('password' => $newpassword,	'id' => $r['id']));
				
				//user pref default redirection state
				$qry=$db->prepare("SELECT * FROM `tusers` WHERE `id`=:id");
				$qry->execute(array('id' => $_SESSION['user_id']));
				$ruser=$qry->fetch();
				$qry->closeCursor();
				
				//modify redirection state to personal user state if it's define else using admin parameter
				if($ruser['default_ticket_state']) {$redirectstate=$ruser['default_ticket_state'];} else {$redirectstate=$rparameters['login_state'];}
		
				//select page to redirect for email link case
				if($_GET['id']) {
					$www = './index.php?page=ticket&id='.$_GET['id'].'&userid='.$_SESSION['user_id'];
				} else {
					if($redirectstate=='activity')
					{
						$www='./index.php?page=dashboard&userid=%25&view=activity&date_start='.date('d/m/Y').'&date_end='.date('d/m/Y');
					}
					elseif($redirectstate=='meta_all')
					{
						$www = "./index.php?page=dashboard&userid=%25&state=meta";
					} elseif($redirectstate=='all')
					{
						$www = "./index.php?page=dashboard&userid=%25&state=%25";
					} else {
						$www = './index.php?page=dashboard&userid='.$_SESSION['user_id'].'&state='.$redirectstate;
					}
				}
				//web redirection
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
		} else {
			//log
			if($rparameters['log'] && $rparameters['debug'] && ldap_error($ldap)){LogIt('error', "ERROR 3 : LDAP, connexion failed on login page ".ldap_error($ldap),$_SESSION['user_id']);}
			// if error with login or password 
			$error=T_('Identifiant ou mot de passe invalide');
			if($rparameters['debug']) {$error.=' ('.T_('erreur 1').')';}
			if($_SESSION['user_id']) {session_destroy();}
			//web redirection to login page
			echo "<SCRIPT LANGUAGE='JavaScript'>
					<!--
					function redirect()
					{
					window.location='./index.php'
					}
					setTimeout('redirect()',$rparameters[time_display_msg]+1500);
					-->
				</SCRIPT>";
		}
	}
	else
	{
		//secure check user attempt
		if($rparameters['user_disable_attempt'] && $_POST['login'] && $_POST['pass'])
		{
			//check if user exist
			$qry=$db->prepare("SELECT `id`,`auth_attempt`,`disable` FROM `tusers` WHERE login=:login");
			$qry->execute(array('login' => $_POST['login']));
			$row=$qry->fetch();
			$qry->closeCursor();
			if($row)
			{
				$attempt=$row['auth_attempt']+1;
				$qry=$db->prepare("UPDATE `tusers` SET `auth_attempt`=:auth_attempt WHERE `id`=:id");
				$qry->execute(array('auth_attempt' => $attempt,'id' => $row['id']));
				$attempt_remaing=$rparameters['user_disable_attempt_number']-$attempt;
				if($attempt_remaing>0)
				{
					$attempt_remaing=T_('Il reste').' '.$attempt_remaing.' '.T_('tentatives avant la désactivation de votre compte');
				} else {
					if($row['disable'])
					{
						$attempt_remaing=T_('Votre compte est désactivé, contactez votre administrateur');
					} else {
						$qry=$db->prepare("UPDATE `tusers` SET `disable`=1 WHERE `id`=:id");
						$qry->execute(array('id' => $row['id']));
						$attempt_remaing=T_('Votre compte a été désactivé, suite à').' '.$rparameters['user_disable_attempt_number'].' '.T_('tentatives de connexion infructueuses');
						if($rparameters['log'])
						{
							LogIt('security', 'User '.$_POST['login'].' disable after authentication failures',$row['id']);
						}
					}
				}
			} else {$attempt_remaing='';}
		} else {$attempt_remaing='';}

		//log ip attempt to display captcha
		if($ip_attempts['attempts']!=0)
		{
			//increment counter for this ip
			$qry=$db->prepare("UPDATE `tauth_attempts` SET `date`=:date,`attempts`=:attempts WHERE `ip`=:ip");
			$qry->execute(array('date' => date('Y-m-d H:i:s'),'ip' => $_SERVER['REMOTE_ADDR'],'attempts' => $ip_attempts['attempts']+1));
		} else {
			//create entry for this ip
			$qry=$db->prepare("INSERT INTO `tauth_attempts` (`date`,`ip`,`attempts`) VALUES (:date,:ip,:attempts)");
			$qry->execute(array('date' => date('Y-m-d H:i:s'),'ip' => $_SERVER['REMOTE_ADDR'],'attempts' => '1'));
		}

		// if error with login or password 
		if(!$error) {$error=T_('Identifiant ou mot de passe invalide');}
		if($rparameters['debug']) {$error.=' ('.T_('erreur 2').')';}
		if($attempt_remaing) {$error.='<br />'.$attempt_remaing;}
		session_destroy();
		//web redirection to login page
		echo "<SCRIPT LANGUAGE='JavaScript'>
					<!--
					function redirect()
					{
					window.location='./index.php'
					}
					setTimeout('redirect()',$rparameters[time_display_msg]+1500);
					-->
				</SCRIPT>";
	}
}; 
// if user isn't connected then display authentication else display dashboard
if(!$_SESSION['login']) 
{
	//case mail link with specific URL parameters
	if($rparameters['mail_link_redirect_url'] && $_GET['page']=='ticket' && $_GET['id'])
	{
		echo T_('Redirection en cours, veillez patienter...');
		echo '<script language="Javascript">document.location.replace("'.$rparameters['mail_link_redirect_url'].'");</script>';
	} else {
		//display background
		if($rparameters['login_background'])
		{
			echo '<div class="body-container" style=" background:url(upload/login_background/'.$rparameters['login_background'].') no-repeat fixed center;" >	';
		} else {
			echo '<div class="body-container" style=" background-image: linear-gradient(#6baace, #264783); background-attachment: fixed; background-repeat: no-repeat;" >';
		}
		echo '
			<div class="main-container container bgc-transparent">
				<div role="main" class="main-content">
					<div class="justify-content-center pb-2">
						';
							if($error){echo DisplayMessage('error',$error);}
							echo '
						<div class="d-flex flex-column align-items-center justify-content-start">
							<h1 class="mt-5">
								<a style="text-decoration: none;"  target="_blank" href="https://gestsup.fr">
									<img title="'.T_('Ouvre un nouvel onglet vers le site gestsup.fr').'" width="45" src="images/logo_gestsup_white.svg" />
								</a>
								<span class="text-90 text-white">GestSup</span>
							</h1>
						</div>
						<div class="d-flex flex-column align-items-center justify-content-start">
							
							<h5 class="text-white">
								';if(isset($rparameters['company'])) echo $rparameters['company']; echo' 
							</h5>
						</div>
						<div class="d-flex flex-column align-items-center justify-content-start">
							';
							if($rparameters['logo'] && file_exists("./upload/logo/$rparameters[logo]"))
							{
								$size=getimagesize('./upload/logo/'.$rparameters['logo']);
								if(!empty($size)){
									if($size[0]>150) {$logo_width='width="150"';} else {$logo_width='';}
								} else {$logo_width='';}
								echo '<img style="border-style: none" alt="logo" '.$logo_width.' src="./upload/logo/'.$rparameters['logo'].'" />';
							} else {
								echo '<span style="font-size: 3em; color: white;"><i class="fa fa-dice-d6"><!----></i></span>';
							}
							echo '
						</div>
					</div>
					<div class="p-4 p-md-4 mh-2 ">
						<div class="row justify-content-center ">
							<div class="shadow radius-1 overflow-hidden bg-white col-12 col-lg-4 ">
								<div class="row ">
									<div class="col-12 bgc-white px-0 pt-5 pb-4">
										<div class="" data-swipe="center">
											<div class="active show px-3 px-lg-0 pb-0" id="id-tab-login">
												<div class="d-lg-block col-md-8 offset-md-2 mt-lg-4 px-0">
													<h4 class="text-dark-tp4 border-b-1 brc-grey-l1 pb-1 text-130">
														<i class="fa fa-lock text-success-m2 mr-1"><!----></i>
														'.T_('Identification').'
													</h4>
												</div>
												<form id="conn" method="post" action="" class="form-row mt-4"> 
													<div class="form-group col-md-8 offset-md-2 '.$hide_login.'">
														<div class="d-flex align-items-center input-floating-label text-blue-m1 brc-blue-m2">
															<input type="text" class="form-control form-control-lg pr-4 shadow-none" id="login" name="login" autocomplete="off" />
															<i class="fa fa-user text-grey-m2 ml-n4"><!----></i>
															<label class="floating-label text-grey-l1 text-100 ml-n3" for="login">'.T_("Nom d'utilisateur").'</label>
														</div>
													</div>
													<div class="form-group col-md-8 offset-md-2 mt-2 mt-md-1 '.$hide_login.'">
														<div class="d-flex align-items-center input-floating-label text-blue-m1 brc-blue-m2">
															<input type="password" class="form-control form-control-lg pr-4 shadow-none" id="pass" name="pass" autocomplete="off" />
															<i class="fa fa-key text-grey-m2 ml-n4"><!----></i>
															<label class="floating-label text-grey-l1 text-100 ml-n3" for="pass">'.T_('Mot de passe').'</label>
														</div>
													</div>
													';
													if($rparameters['user_forgot_pwd'] && !$rparameters['ldap'] && $rparameters['mail'])
													{
														echo '
														<div class="form-group col-md-8 offset-md-2 mt-0 pt-0 text-right '.$hide_login.' ">
															<a href="index.php?page=forgot_pwd" class="text-primary-m2 text-95">
																'.T_('Mot de passe oublié').' ?
															</a>
														</div>
														';
													}
													//display captcha after 10 failed login
													if($ip_attempts['attempts']>=10)
													{
														if(!empty($_POST['login'])) {$failed_login=htmlspecialchars($_POST['login'], ENT_QUOTES, 'UTF-8');} else {$failed_login='';}
														if(substr($ip_attempts['attempts'],-1)=='0') {LogIt('security',$ip_attempts['attempts'].' connexion attempts for ip '.$_SERVER['REMOTE_ADDR'].' with login '.$failed_login,0);}
														
														echo '
														<div class="form-group col-md-8 offset-md-2">
																<img class="mb-2" src="core/captcha.php" alt="captcha" style="cursor:pointer;">
															<div class="d-flex align-items-center input-floating-label text-blue-m1 brc-blue-m2">
																<input type="text" class="form-control form-control-lg pr-4 shadow-none" id="captcha" name="captcha" autocomplete="off" value="" />
																<i class="fa fa-image text-grey-m2 ml-n4"></i>
																<label class="floating-label text-grey-l1 text-100 ml-n3" for="captcha">'.T_('Captcha').'</label>
															</div>
														</div>
														';
													}
													echo '
													<div class="form-group col-md-6 offset-md-3">
														';
														//redirect local auth
														if($hide_login) {
															echo '
															<button type="submit" name="submit" id="submit" value="local_auth" class="btn btn-primary btn-block px-4 btn-bold mt-2 mb-4">
																<i class="fa fa-sign-in-alt"><!----></i>
																'.T_('Connexion').'
															</button>
															';
														} else {
															echo '
															<button type="submit" name="submit" id="submit" value="submit" class="btn btn-primary btn-block px-4 btn-bold mt-2 mb-4">
																<i class="fa fa-sign-in-alt"><!----></i>
																'.T_('Connexion').'
															</button>
															';
														}
														//display Azure SSO buttons for multi tenant
														if($rparameters['azure_ad_sso'] && $rparameters['azure_ad'] && !$rparameters['azure_ad_sso_hide_login']) {
															if($rparameters['azure_ad_tenant_number']==2) //case TDV
															{
																//get tenant info
																$qry=$db->prepare("SELECT `tenant_name` FROM `tentra_tenant` WHERE id='1'");
																$qry->execute();
																$tenant1=$qry->fetch();
																$qry->closeCursor();

																echo '
																<hr />
																<button type="button" name="sso1" id="sso1" value="sso1" onclick="document.location.href=\'./azure_ad_auth.php\'" class="btn btn-success btn-block px-4 btn-bold mt-2 mb-4">
																	<i class="fa fa-sign-in-alt"><!----></i>
																	';
																	if($tenant1['tenant_name'])
																	{
																		echo T_('Connexion SSO ').$tenant1['tenant_name'];
																	} else {
																		echo T_('Connexion SSO');
																	}
																	echo '
																</button>
																';
																if($rparameters['azure_ad_tenant_number']==2)
																{
																	//get tenant info
																	$qry=$db->prepare("SELECT `tenant_name` FROM `tentra_tenant` WHERE id='2'");
																	$qry->execute();
																	$tenant2=$qry->fetch();
																	$qry->closeCursor();

																	echo '
																	<button type="button" name="sso2" id="sso2" value="sso2" onclick="document.location.href=\'./azure_ad_auth2.php\'" class="btn btn-success btn-block px-4 btn-bold mt-2 mb-4">
																		<i class="fa fa-sign-in-alt"><!----></i>
																		';
																		if($tenant2['tenant_name'])
																		{
																			echo T_('Connexion SSO ').$tenant2['tenant_name'];
																		} else {
																			echo T_('Connexion SSO locataire 2');
																		}
																		echo '
																	</button>
																	';
																}
															} else {
																echo '
																<hr />
																<button type="button" name="sso1" id="sso1" value="sso1" onclick="document.location.href=\'./azure_ad_auth.php\'" class="btn btn-success btn-block px-4 btn-bold mt-2 mb-4">
																	<i class="fa fa-sign-in-alt"><!----></i>
																	'.T_('Connexion SSO').'
																</button>
																';
															}
															
														} 
														//auto redirect MS login
														if($rparameters['azure_ad_sso'] && $rparameters['azure_ad'] && $rparameters['azure_ad_sso_hide_login'] && $_GET['local_auth']!=1) {
															echo '<script language="Javascript">document.location.replace("'.$rparameters['server_url'].'/azure_ad_auth.php");</script>';
														}
														echo '
													</div>
												</form>
												';
													//include plugin
													$section='login';
													include('./plugin.php');
													echo '
												';
												if($rparameters['user_register'])
												{
													echo '
													<div class="form-row">
														<div class="col-12 col-md-6 offset-md-3 d-flex flex-column align-items-center justify-content-center">
															<hr class="brc-default-m4 mt-0 mb-2 w-100" />
															<div class="p-0 px-md-2 text-dark-tp3 my-3">
																<a class="text-success-m2 text-600 mx-1" href="index.php?page=register">
																	'.T_("S'enregistrer").'
																</a>
															</div>
														</div>
													</div>
													';
												}
												echo '
											</div>
										</div><!-- .tab-content -->
									</div>
								</div>
							</div>
						</div>
					</div>
					';
					//display information message
					if($rparameters['login_message'])
					{
						if($rparameters['login_message_alert'] && $rparameters['login_message_alert']!='<br>' && $rparameters['login_message_alert']!='<br />')
						{
							echo '
								<div class="alert bgc-red-l4 border-none border-l-4 brc-red-tp1 radius-0 text-dark-tp2">
									<h5 class="alert-heading text-danger-m1 font-bolder">
										<i class="fas fa-exclamation-circle mr-1 mb-1"></i>
										'.T_('Alerte').'
									</h5>
									'.$rparameters['login_message_alert'].'
								</div>
							';
						}
						if($rparameters['login_message_warning'] && $rparameters['login_message_warning']!='<br>' && $rparameters['login_message_warning']!='<br />')
						{
							echo '
								<div class="alert bgc-warning-l4 border-none border-l-4 brc-warning-tp1 radius-0 text-dark-tp2">
									<h5 class="alert-heading text-warning-m1 font-bolder">
										<i class="fas fa-exclamation-triangle mr-1 mb-1"></i>
										'.T_('Warning').'
									</h5>
									'.$rparameters['login_message_warning'].'
								</div>
							';
						}
						if($rparameters['login_message_info'] && $rparameters['login_message_info']!='<br>' && $rparameters['login_message_info']!='<br />')
						{
							echo '
								<div class="alert bgc-info-l4 border-none border-l-4 brc-info-tp1 radius-0 text-dark-tp2 mt-4">
									<h5 class="alert-heading text-info-m1 font-bolder">
										<i class="fas fa-info-circle mr-1 mb-1"></i>
										'.T_('Information').'
									</h5>
									'.$rparameters['login_message_info'].'
								</div>
							';
						}
					}
					echo '
				</div><!-- /main -->
			</div><!-- /.main-container -->
		</div><!-- /.body-container -->
		<!-- DO NOT DELETE OR MODIFY THIS LINE THANKS -->
			<span style="position: fixed; bottom: 0px; right: 0px;"><a title="'.T_('Ouvre un nouvel onglet vers le site gestsup.fr').'" target="_blank" href="https://gestsup.fr">GestSup.fr</a></span>
		<!-- DO NOT DELETE OR MODIFY THIS LINE THANKS -->
		<script type="text/JavaScript">
			document.getElementById("login").focus();
		</script>
		';
	}
}
?>