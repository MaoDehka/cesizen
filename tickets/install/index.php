<?php
################################################################################
# @Name : ./install/index.php 
# @Description : Installation application page
# @Call : /
# @Author : Flox
# @Version : 3.2.35
# @Create : 10/11/2007
# @Update : 17/04/2023
################################################################################

//initialize variables 
if(!isset($_POST['refresh'])) $_POST['refresh'] = '';
if(!isset($_POST['step'])) $_POST['step'] = '';
if(!isset($_POST['server'])) $_POST['server'] = '';
if(!isset($_POST['port'])) $_POST['port'] = '';
if(!isset($_POST['dbname'])) $_POST['dbname'] = '';
if(!isset($_POST['user'])) $_POST['user'] = '';
if(!isset($_POST['password'])) $_POST['password'] = '';
if(!isset($_POST['telemetry'])) $_POST['telemetry'] = '';
if(!isset($requetes)) $requetes= '';
if(!isset($valid)) $valid = '';
if(!isset($vphp)) $vphp = '';
if(!isset($i)) $i = '';
if(!isset($textension[$i])) $textension[$i] = '';
if(!isset($openssl)) $openssl = '';
if(!isset($phpinfo)) $phpinfo = '';
if(!isset($match)) $match = '';
if(!isset($ldap)) $ldap = '';
if(!isset($zip)) $zip = '';
if(!isset($imap)) $imap = '';
if(!isset($error)) $error = '';
if(!isset($e)) $e= '';

//secure input
$_POST['server']=htmlspecialchars($_POST['server'], ENT_QUOTES, 'UTF-8');
$_POST['port']=htmlspecialchars($_POST['port'], ENT_QUOTES, 'UTF-8');
$_POST['dbname']=htmlspecialchars($_POST['dbname'], ENT_QUOTES, 'UTF-8');
$_POST['user']=htmlspecialchars($_POST['user'], ENT_QUOTES, 'UTF-8');
$_POST['password']=htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');
$_POST['telemetry']=htmlspecialchars($_POST['telemetry'], ENT_QUOTES, 'UTF-8');
$_POST['password']=str_replace("';","",$_POST['password']);
$_POST['password']=str_replace('";','',$_POST['password']);
$_POST['password']=str_replace("'",'',$_POST['password']);
$_POST['password']=str_replace(".",'',$_POST['password']);
$_POST['password']=str_replace('system(','',$_POST['password']);
$_POST['password']=str_replace('$_GET','',$_POST['password']);
$_POST['password']=str_replace(';//','',$_POST['password']);
$_POST['password']=str_replace('passthru','',$_POST['password']);
$_POST['password']=str_replace('_POST','',$_POST['password']);

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

//default value
if(!isset($step)) $step=1;

//detect https connection
if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {$http='https';} else {$http='http';}

//extract parameters from phpinfo
ob_start();
phpinfo();
$phpinfo = array('phpinfo' => array());
if(preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER))
foreach($matches as $match)
	if(strlen($match[1]))
		$phpinfo[$match[1]] = array();
	elseif(isset($match[3])) {
		$ak=array_keys($phpinfo);
		$phpinfo[end($ak)][$match[2]] = isset($match[4]) ? array($match[3], $match[4]) : $match[3];
	}
	else {
		$ak=array_keys($phpinfo);
		$phpinfo[end($ak)][] = $match[2];
	}
//case for old version php, php info tab is PHP CORE 			
if(isset($phpinfo['Core'])!='') $vphp='Core'; else $vphp='HTTP Headers Information';

//initialize variables 
if(!isset($phpinfo[$vphp]['register_globals'][0])) $phpinfo[$vphp]['register_globals'][0] = '';
if(!isset($phpinfo[$vphp]['magic_quotes_gpc'][0])) $phpinfo[$vphp]['magic_quotes_gpc'][0] = '';
if(!isset($phpinfo[$vphp]['file_uploads'][0])) $phpinfo[$vphp]['file_uploads'][0] = '';
if(!isset($phpinfo[$vphp]['memory_limit'][0])) $phpinfo[$vphp]['memory_limit'][0] = '';
if(!isset($phpinfo[$vphp]['upload_max_filesize'][0])) $phpinfo[$vphp]['upload_max_filesize'][0] = '';

////actions on submit

//step1
if($_POST['step']==1 && $_POST['server'])
{	
	//write connect.php file with parameter
	$fichier = fopen('../connect.php','w+');
	fputs($fichier,"<?php\r\n");
	fputs($fichier,"################################################################################\r\n");
	fputs($fichier,"# @Name : connect.php\r\n");
	fputs($fichier,"# @Description : database connection parameters\r\n");
	fputs($fichier,"# @Call : \r\n");
	fputs($fichier,"# @Parameters : \r\n");
	fputs($fichier,"# @Author : Flox\r\n");
	fputs($fichier,"# @Create : 07/03/2007\r\n");
	fputs($fichier,"# @Update : 17/04/2023\r\n");
	fputs($fichier,"# @Version : 3.2.35\r\n");
	fputs($fichier,"################################################################################\r\n");
	fputs($fichier,"\r\n");
	fputs($fichier,"//database connection parameters\r\n");
	fputs($fichier,"\$host='$_POST[server]'; //SQL server name\r\n");
	fputs($fichier,"\$port='$_POST[port]'; //SQL server port\r\n");
	fputs($fichier,"\$db_name='$_POST[dbname]'; //database name\r\n");
	fputs($fichier,"\$charset='utf8'; //database charset default utf8\r\n");
	fputs($fichier,"\$user='$_POST[user]'; //database user name\r\n");
	fputs($fichier,"\$password='$_POST[password]'; //database password\r\n");
	fputs($fichier,"\r\n");
	fputs($fichier,"//database connection\r\n");
	fputs($fichier,'try {$db = new PDO("mysql:host=$host;port=$port;dbname=$db_name;charset=$charset", "$user", "$password" , array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));}'."\r\n");
	fputs($fichier,'catch (Exception $e)'."\r\n");
	fputs($fichier,'{die(\'Error : \' . $e->getMessage());}'."\r\n");
	fputs($fichier,"?>");
	fclose($fichier);
	
	//db connect
	$host="$_POST[server]"; //SQL server name
	$port="$_POST[port]"; //SQL server port
	$db_name="$_POST[dbname]"; //database name
	$charset="utf8"; //database charset default utf8
	$user="$_POST[user]"; //database user name
	$password="$_POST[password]"; //database password
	
	//create and connect to database
	try 
	{
		$db = new PDO("mysql:host=$host;port=$port;charset=$charset", $user, $password , array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
		$db->exec("CREATE DATABASE IF NOT EXISTS `$_POST[dbname]`");
		$db->query("use `$_POST[dbname]`");
		//import sql skeleton
		$sql_file=file_get_contents('../_SQL/skeleton.sql');
		$sql_file=explode(";", $sql_file);
		foreach ($sql_file as $value) {
			if($value!='') $db->exec($value);
		}
		//update default server_url
		if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {$http='https';} else {$http='http';}
		$current_url="$http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$current_url=str_replace('/install/','',$current_url);
		$qry=$db->prepare("UPDATE `tparameters` SET `server_url`=:server_url");
		$qry->execute(array('server_url' => $current_url));

		//update telemetry
		if($_POST['telemetry']=='1')
		{
			$qry=$db->prepare("UPDATE `tparameters` SET `telemetry`='1'");
			$qry->execute();
		}
		//update date install
		$qry=$db->prepare("UPDATE `tparameters` SET `server_date_install`=:date_install");
		$qry->execute(array('date_install' => date('Y-m-d')));
		
		$step=2;
	} catch (Exception $e) {
		$e->getMessage();
		$error=T_('Vérifier vos paramètres de connexion à la base de donnée').'<br />'.$e; 
	}
}
//step2
if($_POST['step']==2)
{
	if($_POST['refresh']) $step=2; else $step=3;
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<title>GestSup | <?php echo T_('Installation'); ?></title>
		<link rel="shortcut icon" type="image/png" href="../images/favicon_ticket.png" />
		<meta name="description" content="gestsup" />
		<meta name="robots" content="noindex, nofollow">
		<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1">
		<!-- bootstrap styles -->
		<link rel="stylesheet" href="../vendor/components/bootstrap/dist/css/bootstrap.min.css" />
		<!-- fontawesome styles -->
		<link rel="stylesheet" type="text/css" href="../vendor/fortawesome/font-awesome/css/fontawesome.min.css">
		<link rel="stylesheet" type="text/css" href="../vendor/fortawesome/font-awesome/css/regular.min.css">
		<link rel="stylesheet" type="text/css" href="../vendor/fortawesome/font-awesome/css/brands.min.css">
		<link rel="stylesheet" type="text/css" href="../vendor/fortawesome/font-awesome/css/solid.min.css">
		<!-- ace styles -->
		<link rel="stylesheet" type="text/css" href="../template/ace/dist/css/ace-font.min.css">
		<link rel="stylesheet" type="text/css" href="../template/ace/dist/css/ace.min.css">
		<link rel="stylesheet" type="text/css" href="../template/ace/dist/css/ace-themes.min.css">
	</head>
	<body class="bgc-white">
		<div style="body-container" >
			<nav class="navbar navbar-expand-lg navbar-fixed navbar-skyblue">
				<div class="navbar-inner">
					<div class="navbar-content">
						<a href="#" class="navbar-brand text-white">
							<img title="logo" width="34" src="../images/logo_gestsup_white.svg" />
							GestSup
						</a><!--/.brand-->
					</div>
				</div><!--/.navbar-inner-->
			</div>
			<div class="main-container p-4" id="main-container">
				<div role="main" class="main-content">
					<div class="">
						<div class="card bcard shadow" id="card-1" draggable="false">
							<div class="card-header">
								<h5 class="card-title text-primary-m2">
									<i class="fa fa-download"></i>
									<?php echo T_("Installation de l'application"); ?>
								</h5>
							</div>
							<div class="card-body p-0">
								<div class="p-3">
									<div id="smartwizard-1" class="sw-main sw-theme-circles pb-4">
										<ul class="mx-auto nav nav-tabs step-anchor">
											<li data-target="#step1" class="nav-item <?php if($step==1) {echo 'active';} if($step==2 || $step==3) {echo 'done';} ?>" >
												<a class="nav-link" >
													<span class="step-title">1</span>
													<span class="step-title-done"><i class="fa fa-check text-success-m1"></i></span>
												</a>
												<span class="step-description"><?php echo T_('Base de données'); ?></span>
											</li>
											<li data-target="#step2" class="nav-item <?php if($step==2) {echo 'active';} if($step==3) {echo 'done';}?>" >
												<a class="nav-link" >
													<span class="step-title">2</span>
													<span class="step-title-done"><i class="fa fa-check text-success-m1"></i></span>
												</a>
												<span class="step-description"><?php echo T_('Vérification de la configuration serveur'); ?></span>
											</li>
											<li data-target="#step3" class="nav-item <?php if($step==3) echo 'done'; ?>"" >
												<a class="nav-link" >
													<span class="step-title">3</span>
													<span class="step-title-done"><i class="fa fa-check text-success-m1"></i></span>
												</a>
												<span class="step-description"><?php echo T_('Fin'); ?></span>
											</li>
										</ul>
									</div>
									<form method="post" id="form" action="" class="form-horizontal" id="sample-form" >
										<div class="step-content row-fluid position-relative" id="step-container">
											<div class="step-pane active" id="step1">
												<?php
													//display error box
													if($error) {
														echo '
															<div role="alert" class="alert alert-lg bgc-danger-l3 border-0 border-l-4 brc-danger-m1 mt-4 mb-3 pr-3 d-flex">
																<div class="flex-grow-1">
																	<i class="fas fa-times mr-1 text-120 text-danger-m1"></i>
																	<strong class="text-danger">'.T_('Erreur').' : '.$error.'.</strong>
																</div>
																<button type="button" class="close align-self-start" data-dismiss="alert" aria-label="Close">
																	<span aria-hidden="true"><i class="fa fa-times text-80"></i></span>
																</button>
															</div>
														';
													}
													//display STEP 1 form
													if($step=='1')
													{
														//check if connect.php is writable
														if(!is_writable('./../connect.php')) {
															echo '
															<div role="alert" class="alert alert-lg bgc-danger-l3 border-0 border-l-4 brc-danger-m1 mt-4 mb-3 pr-3 d-flex">
																<div class="flex-grow-1">
																	<i class="fas fa-times mr-1 text-120 text-danger-m1"></i>
																	<strong class="text-danger">'.T_('Erreur').' : '.T_("L'application n'a pas les droits d'écriture sur le fichier connect.php").'.</strong>
																</div>
																<button type="button" class="close align-self-start" data-dismiss="alert" aria-label="Close">
																	<span aria-hidden="true"><i class="fa fa-times text-80"></i></span>
																</button>
															</div>
															';
															exit;
														}
														echo '
															<h4 class="text-blue">'.T_('Entrer les paramètres de connexion à votre base de données').' :</h4>
															<hr class="mb-3 border-dotted">
															<input type="hidden" name="step" value="1">
															<div class="form-group row">
																<div class="col-sm-2 col-form-label text-sm-right pr-0">
																	<label class="mb-0" for="server">'.T_('Serveur de base données').' :</label>
																</div>
																<div class="col-sm-5">
																	<input class="form-control col-4 d-inline-block" type="text" name="server" value="localhost" required="required" >
																	<i class="fa fa-info-circle text-primary-m2 pl-1 pr-1"></i><span class="small">'.T_('Nom netbios ou adresse IP du serveur de base de données').'</span>
																</div>
															</div>
															<div class="form-group row">	
																<div class="col-sm-2 col-form-label text-sm-right pr-0">
																	<label class="mb-0" for="dbname">'.T_('Nom de la base données').' :</label>
																</div>
																<div class="col-sm-5">
																	<input class="form-control col-4 d-inline-block" type="text" name="dbname" value="bsup" required="required">
																</div>
															</div>
															<div class="form-group row">
																<div class="col-sm-2 col-form-label text-sm-right pr-0">
																	<label class="mb-0" for="port">'.T_('Port de la base données').' :</label>
																</div>
																<div class="col-sm-5">
																	<input class="form-control col-4 d-inline-block" type="text" name="port" value="3306" required="required">
																	<i class="fa fa-info-circle text-primary-m2 pl-1 pr-1"></i><span class="small">'.T_('Pour MySQL ou MariaDB par défaut 3306').'</span>
																</div>
															</div>
															<div class="form-group row">	
																<div class="col-sm-2 col-form-label text-sm-right pr-0">
																	<label for="user" class="mb-0">'.T_('Utilisateur de la base données').' :</label>
																</div>
																<div class="col-sm-5">
																	<input class="form-control col-4 d-inline-block" type="text" name="user" value="root" required="required">
																</div>
															</div>
															<div class="form-group row">
																<div class="col-sm-2 col-form-label text-sm-right pr-0">
																	<label for="password" class="mb-0">'.T_('Mot de passe de la base données').' :</label>
																</div>
																<div class="col-sm-5">
																	<input class="form-control col-4 d-inline-block" type="password" name="password" value="">
																</div>
															</div>
															<div class="form-group row">
																<div class="col-sm-2 col-form-label text-sm-right pr-0">
																	<label for="password" class="mb-0">'.T_("J'autorise la télémétrie").' :</label>
																</div>
																<div class="col-sm-5 mt-2">
																	<input type="checkbox" checked name="telemetry" id="telemetry" value="1">
																	<i title="'.T_("Autorise la transmission de certaines informations concernant votre serveur à l'éditeur du logiciel uniquement à des fins statistiques et d'amélioration de service cf. doc.gestsup.fr").'" class="fa fa-info-circle text-primary-m2 pl-1 pr-1"></i>
																</div>
															</div>
														';
													}
													if($step=='2')
													{	
														echo '
														<input type="hidden" name="step" value="2">
														<h4 class="text-blue">'.T_('Vérification de la configuration serveur').' :</h4>
														';
														include('../system.php');
													}
													if($step=='3')
													{	
														//db connection 
														require('./../connect.php');
														require('./../core/functions.php');

														//find server url
														if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {$http='https';} else {$http='http';}
														$url=$_SERVER['HTTP_REFERER'];
														$url=(parse_url($url));
														$path=$url['path'];
														$path=explode("/",$path);
														$path=$path[1];
														if($path=='install') {$path='';}
														$url=$http.'://'.$url['host'].'/'.$path;

														//update default users password
														$admin_password=randomPassword();
														$admin_password_hash=password_hash($admin_password, PASSWORD_DEFAULT);
														$qry=$db->prepare("UPDATE `tusers` SET `password`=:password,`last_pwd_chg`=:date WHERE `login`='admin'");
														$qry->execute(array('password' => $admin_password_hash,'date' => date('Y-m-d')));
														$qry=$db->prepare("UPDATE `tusers` SET `password`=:password,`last_pwd_chg`=:date WHERE `login`='user'");
														$qry->execute(array('password' => randomPassword(),'date' => date('Y-m-d')));
														$qry=$db->prepare("UPDATE `tusers` SET `password`=:password,`last_pwd_chg`=:date WHERE `login`='poweruser'");
														$qry->execute(array('password' => randomPassword(),'date' => date('Y-m-d')));
														$qry=$db->prepare("UPDATE `tusers` SET `password`=:password,`last_pwd_chg`=:date WHERE `login`='super'");
														$qry->execute(array('password' => randomPassword(),'date' => date('Y-m-d')));
														$qry=$db->prepare("UPDATE `tusers` SET `password`=:password,`last_pwd_chg`=:date WHERE `login`='tech'");
														$qry->execute(array('password' => randomPassword(),'date' => date('Y-m-d')));
														
														echo '
															<input type="hidden" name="step" value="3">
															<h4 class="text-blue">'.T_('Installation terminée').' :</h4>
															<hr class="mb-3 border-dotted">
															<input type="hidden" name="step" value="1">
															<div class="m-4" >
																<i class="fa fa-check-circle text-success mr-1"></i> '.T_("L'application à été installée avec succès").'.<br />
																<i class="fa fa-sign-in-alt text-primary-m2 mr-1"></i> '.T_('Les identifiants par défaut sont').' <b>admin</b> / <b>'.$admin_password.'</b> <br />
																<i class="fa fa-ticket text-primary-m2 mr-1"></i> '.T_("Vous pouvez vous connecter via l'url").' : <a href="'.$url.'">'.$url.'</a>. <br />
																<div role="alert" class="alert alert-lg bgc-warning-l3 border-0 border-l-4 brc-warning-m1 mt-4 mb-3 pr-3 d-flex">
																	<div class="flex-grow-1">
																		<i class="fas fa-exclamation-triangle mr-1 text-120 text-warning-m1"></i>
																		<strong class="text-warning">'.T_("Pour finaliser l'installation, réaliser les étapes suivantes").' : </strong>
																		<div class="pt-2"></div>
																		1 - '.T_("Supprimer le répertoire /install de votre serveur pour éviter toute ré-installation").'.<br />
																		2 - '.T_("Restreindre les droits d'accès aux fichiers serveur").' (<a target="_blank" href="https://doc.gestsup.fr/install/">'.T_('Documentation').'</a>).<br />
																		3 - '.T_("Verifier les mises à jour de l'application (Administration > Mises à jour)").' <br />
																		4 - '.T_("Sécuriser votre serveur").' (<a target="_blank" href="https://doc.gestsup.fr/install/#securisation">'.T_('Documentation').'</a>).<br />
																	</div>
																	<button type="button" class="close align-self-start" data-dismiss="alert" aria-label="Close">
																		<span aria-hidden="true"><i class="fa fa-times text-80"></i></span>
																	</button>
																</div>
															</div>
														';
													}
												?>
											</div>
											<div class="border-t-1 brc-secondary-l1 bgc-secondary-l3 py-3 text-center">
												<?php
													if($step==2)
													{
														echo '
														<button type="submit" name="refresh" id="refresh" value="refresh" class="btn btn-primary">
															<i class="fa fa-sync"></i>
															'.T_('Actualiser').'
														</button>';
													}
													if($step!=3)
													{
														echo '
														<button type="submit" class="btn btn-success">
															'.T_('Suivant').'
															<i class="fa fa-arrow-right pl-1"></i>
														</button>';
													}
												?>
											</div>
										</div>
									</form>
								</div><!-- /widget-main -->
							</div><!-- /widget-body -->
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
	<script type="text/javascript" src="../vendor/components/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="../vendor/components/popper-js/dist/umd/popper.min.js"></script>
	<script type="text/javascript" src="../vendor/components/bootstrap/dist/js/bootstrap.min.js"></script>

	<!-- include ace scripts -->
	<script type="text/javascript" src="../template/ace/dist/js/ace.js"></script>
	<script type="text/javascript" src="../template/ace/assets/js/demo.js"></script>
</html>