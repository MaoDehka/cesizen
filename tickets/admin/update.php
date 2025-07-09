<?php
################################################################################
# @Name : update.php
# @Description : page to update GestSup
# @Call : admin.php
# @Parameters : 
# @Author : Flox
# @Create : 20/01/2011
# @Update : 16/05/2023
# @Version : 3.2.36
################################################################################

//initialize variables 
if(!isset($contents[0])) $contents[0] = '';
if(!isset($_POST['update_channel'])) $_POST['update_channel'] = '';
if(!isset($_POST['check'])) $_POST['check'] = '';
if(!isset($_POST['download'])) $_POST['download'] = '';
if(!isset($_POST['install'])) $_POST['install'] = '';
if(!isset($_POST['install_update'])) $_POST['install_update'] = '';
if(!isset($argv[1])) $argv[1] = '';
if(!isset($argv[2])) $argv[2] = '';
if(!isset($findpatch)) $findpatch = '';
if(!isset($message)) $message = '';
if(!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) $_SERVER['HTTP_ACCEPT_LANGUAGE']='';
$hide_install_button=0;

//update server
$update_server='gestsup.fr';

//check if script is executed from command line
if(php_sapi_name()=='cli') {$cmd_update=1;} else {$cmd_update=0;}

//display error if missing arg
if($cmd_update) {
	if(!$argv[1]) {echo 'ERROR : you must specify autoinstall in first argument from command line'; exit;}
	if(!$argv[2]) {echo 'ERROR : you must specify server key in second argument from command line'; exit;}
}

//db connection
require_once(__DIR__ . '/../connect.php');

//call functions
require_once(__DIR__ . '/../core/functions.php');

//check update
CheckUpdate();

//load parameters table
$qry = $db->prepare("SELECT * FROM `tparameters`");
$qry->execute();
$rparameters=$qry->fetch();
$qry->closeCursor();

//check autoinstall for command line options
if ($argv[1]=='autoinstall') {
	//locales
	$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	if ($lang=='fr') {$_GET['lang'] = 'fr_FR';}
	else {$_GET['lang'] = 'en_US';}
	define('PROJECT_DIR', realpath('../'));
	define('LOCALE_DIR', PROJECT_DIR .'/locale');
	define('DEFAULT_LOCALE', '($_GET[lang]');
	require_once(__DIR__.'/../vendor/components/php-gettext/gettext.inc');
	$encoding = 'UTF-8';
	$locale = (isset($_GET['lang']))? $_GET['lang'] : DEFAULT_LOCALE;
	T_setlocale(LC_MESSAGES, $locale);
	T_bindtextdomain($_GET['lang'], LOCALE_DIR);
	T_bind_textdomain_codeset($_GET['lang'], $encoding);
	T_textdomain($_GET['lang']);
	
	if($argv[2]==$rparameters['server_private_key'])
	{
		echo "SERVER KEY VALIDATION : OK".PHP_EOL;
		$autoinstall=1;
	} else {
		echo "ERROR : Wrong server key, go to admin system panel";
		exit;
		$autoinstall=0;
	}
} else {
	$autoinstall=0;
}
 
//check dedicated version
if(substr_count($rparameters['version'], '.')==3) {$dedicated=1;} else {$dedicated=0;}
 
//display title
if(!$cmd_update)
{
	echo '
	<div class="page-header position-relative">
		<h1 class="page-title text-primary-m2">
			<i class="fa fa-cloud-upload-alt text-primary-m2"><!----></i>  '.T_('Mise à jour de GestSup').'
		</h1>
	</div>
	';
}

//check rights permission on files
if(!$cmd_update && (!is_writable('./core/ticket.php') || !is_writable('./index.php') || !is_writable('./admin/parameters.php') || !is_writable('./download/readme.txt')))
{
	echo DisplayMessage('warning',T_("Les fichiers serveur ne sont pas accessible en écriture, l'installation semi-automatique ne fonctionnera pas, modifier les droits d'écriture temporairement pour l'installation puis remettre les droits par défaut"));
	$hide_install_button=1;	
}

//check network access to update_server
if(!CheckConnection($update_server,443))
{
	LogIt('error', 'ERROR 30 : CHECK GESTSUP UPDATE, unable to access on '.$update_server.':443',0);
	echo DisplayMessage('error',T_("Votre serveur GestSup n'autorise pas accès au site $update_server sur le port 443"));
	$hide_install_button=1;	
}

//update update channel parameter
if($_POST['update_channel']) 
{
	$qry=$db->prepare("UPDATE `tparameters` SET `update_channel`=:update_channel");
	$qry->execute(array('update_channel' => $_POST['update_channel']));
}

//get local channel 
if($_POST['update_channel'])
{
	$update_channel=$_POST['update_channel'];
} else {
	$update_channel=$rparameters['update_channel'];
}


//get local version
$local_version=$rparameters['version'];

//display debug informations
if($rparameters['debug']) {echo "<b><u>DEBUG MODE:</u></b><br />UPDATE CHANNEL : $update_channel<br />LOCAL VERSION : $local_version <br />";}

//no dedicated version check
if(!$dedicated)
{
	//find current version
	$local_version_array= explode('.',$local_version);
	
	//find number of current patch
	$local_patch=$local_version_array[2];

	//get all patch available
	$url='https://'.$update_server.'/available_patch_'.$rparameters['update_channel'].'.php';
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	if($rparameters['server_proxy_url']) {curl_setopt($curl, CURLOPT_PROXY, $rparameters['server_proxy_url']);}
	$remote_patch_list=json_decode(curl_exec($curl),true);
	if(curl_error($curl)) die(curl_error($curl));
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);

	//check lastest version
	if($status!='200' || !$remote_patch_list)
	{
		LogIt('error', 'ERROR 34 : GET ALL PATCHS, unable to get '.$url.' error '.$status,0);
		echo DisplayMessage('error',T_("Impossible de récupérer la liste des derniers  patch via $update_server sur le port 443"));
		$hide_install_button=1;	
	} 

	//get patch only
	$patch_list = preg_grep("/patch_$local_version_array[0].$local_version_array[1]/", $remote_patch_list);

	//put in array only last number of patch
	$patch_list_array = array();
	foreach($patch_list as $patch){
		$patch=explode("_",$patch);
		$patch=explode(".zip",$patch[1]);
		$patch=explode(".",$patch[0]);
		array_push($patch_list_array, $patch[2]);
	}

	//order patch number
	asort($patch_list_array);

	//get last patch
	$last_remote_patch=end($patch_list_array);
	
	//display debug information
	if($last_remote_patch) 
	{
		if($rparameters['debug']) {echo "LAST AVAILABLE PATCH : $local_version_array[0].$local_version_array[1].$last_remote_patch<br />";}
	} else {
		if($rparameters['debug']) {echo "LAST AVAILABLE PATCH : No patch available on update server for version $local_version_array[0].$local_version_array[1]<br />";}
	}

	//get version only
	$url='https://'.$update_server.'/available_version.php';
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	if($rparameters['server_proxy_url']) {curl_setopt($curl, CURLOPT_PROXY, $rparameters['server_proxy_url']);}
	$remote_version_list=json_decode(curl_exec($curl),true);
	if(curl_error($curl)) die(curl_error($curl));
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);

	//check lastest version
	if($status!='200' || !$remote_version_list)
	{
		LogIt('error', 'ERROR 35 : GET ALL PATCHS, unable to get '.$url.' error '.$status,0);
		echo DisplayMessage('error',T_("Impossible de récupérer la liste des dernières versions via $update_server sur le port 443"));
		$hide_install_button=1;	
	} 

	//put version in array
	$version_remote_array = array();
	foreach($remote_version_list as $version){
		$version=explode("_",$version);
		$version=explode(".zip",$version[1]);
		$version=explode(".",$version[0]);
		array_push($version_remote_array, $version);
	}

	//sort array
	asort($version_remote_array);

	//get last version
	$last_remote_version_array=end($version_remote_array);
	$last_remote_version="$last_remote_version_array[0].$last_remote_version_array[1].$last_remote_version_array[2]";
	if($last_remote_version) 
	{
		if($rparameters['debug']) {echo "LAST VERSION AVAILABLE : $last_remote_version<br />";}
	} else {
		if($rparameters['debug']) {echo "LAST VERSION AVAILABLE : no version is available on update server.<br />"; }
	}

	//check update server
	if($last_remote_version){
		$serverstate='<i class="fa fa-check-circle text-success"><!----></i> <span class="text-success">'.T_('Serveur de mises à jour disponible').'</span>';
		$findversion=0;
		$findpatch=0;

		//compare versions check two first number of version name
		if (($local_version_array[0]==$last_remote_version_array[0]) && ($local_version_array[1]==$last_remote_version_array[1]))
		{
			if($rparameters['debug']) {echo "COMPARE VERSIONS : local version $local_version_array[0].$local_version_array[1] is the same as update server $last_remote_version_array[0].$last_remote_version_array[1] <br />"; }
			//compare patchs
			if ($local_patch==$last_remote_patch)
			{
				if($rparameters['debug']) {echo "COMPARE PATCH : local patch $local_patch is the same that update server $last_remote_patch <br />"; }
				$message=T_('Votre version est à jour');
				if($cmd_update){echo 'OK : CURRENT VERSION IS ALREADY UP TO DATE'; exit;}
			} 
			elseif ($local_patch>$last_remote_patch)
			{
				if($rparameters['debug']) {echo "COMPARE PATCH : local patch $local_patch is superior than update server $last_remote_patch <br />"; }
				$message=T_('Votre version').' '.$local_version_array[0].'.'.$local_version_array[1].'.'.$local_patch.' '.T_('est supérieur à la dernière version disponible, vous devez avoir changé de canal de mises à jour');
				if($cmd_update){echo 'OK : CURRENT VERSION SUPERIOR THAN LATEST AVAILABLE VERSION'; exit;}
			}
			elseif ($local_patch<$last_remote_patch)
			{
				$findpatch=1;
				//generate n+1 name if more than one patch is available
				if (($last_remote_patch-$local_patch)>1) {$next_remote_patch=$local_patch+1;} else {$next_remote_patch=$last_remote_patch;}
				if($rparameters['debug']) {echo "COMPARE PATCH : local patch $local_patch is inferior than update Server $last_remote_patch <br />"; }
				$message=T_('Un nouveau patch').' '.$local_version_array[0].'.'.$local_version_array[1].'.'.$next_remote_patch.' '.T_('est disponible en téléchargement');
				$cmd_msg='NEW PATCH '.$local_version_array[0].'.'.$local_version_array[1].'.'.$next_remote_patch.' AVAILABLE'.PHP_EOL;
			}
		}
		elseif(($local_version_array[0]<$last_remote_version_array[0]) || ($local_version_array[1]<$last_remote_version_array[1]))
		{
			if($rparameters['debug']) {echo "COMPARE VERSIONS : local version $local_version_array[0].$local_version_array[1] is inferior than update server $last_remote_version_array[0].$last_remote_version_array[1]<br />"; }
			$message=T_('La version').' '.$last_remote_version.' '.T_('est disponible au téléchargement');
			$findversion=1;
		}
		elseif(($local_version_array[0]>$last_remote_version_array[0]) || (($local_version_array[0]>$last_remote_version_array[0])&&($local_version_array[1]>$last_remote_version_array[1])))
		{
			if($rparameters['debug']) {echo "COMPARE VERSIONS : local version $local_version_array[0].$local_version_array[1] is superior than update server GestSup $last_remote_version_array[0].$last_remote_version_array[1], you are maybe a developer.<br />"; }
		}

		//display check message
		if($cmd_update){echo $cmd_msg;} else {if($_POST['check']) {echo DisplayMessage('success',$message);}}

		//downloads
		if($_POST['download'] || $autoinstall)
		{
			if($findversion) // download version
			{
				$url = "https://gestsup.fr/downloads/versions/current/version/gestsup_$last_remote_version.zip";
				$file_local_url = __DIR__ ."/../download/gestsup_$last_remote_version.zip";
				$zipResource = fopen($file_local_url, "w");
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_FAILONERROR, true);
				curl_setopt($curl, CURLOPT_HEADER, 0);
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($curl, CURLOPT_AUTOREFERER, true);
				curl_setopt($curl, CURLOPT_BINARYTRANSFER,true);
				curl_setopt($curl, CURLOPT_TIMEOUT, 10);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); 
				curl_setopt($curl, CURLOPT_FILE, $zipResource);
				if($rparameters['server_proxy_url']) {curl_setopt($curl, CURLOPT_PROXY, $rparameters['server_proxy_url']);}
				$download = curl_exec($curl);
				if(!$download) {echo "Error :- ".curl_error($curl);}
				curl_close($curl);

				//update download counter on gestsup.fr
				$url='https://gestsup.fr/downloaded.php?type=version&version='.$last_remote_version.'&channel='.$rparameters['update_channel'];
				$curl = curl_init($url);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
				if($rparameters['server_proxy_url']) {curl_setopt($curl, CURLOPT_PROXY, $rparameters['server_proxy_url']);}
				curl_exec($curl);
				if(curl_error($curl)) die(curl_error($curl));
				curl_close($curl);

				//check if file exist
				if(file_exists($file_local_url)) 
				{
					if($cmd_update){echo 'DOWNLOAD GESTSUP VERSION COMPLETED'.PHP_EOL;} else {
						echo DisplayMessage('success',T_('La version').' '.$last_remote_version.' '.T_('à été téléchargée dans le répertoire "./download" du serveur web'));
					}
				}else{
					if($cmd_update){echo 'ERROR DURING DOWNLOAD THE LATEST VERSION'.PHP_EOL;exit;
					} else {
						echo DisplayMessage('error',T_('Le téléchargement de la dernière version à échoué'));
						LogIt('error', "ERROR 6 : Download lastest version failed",$_SESSION['user_id']);
					}
				}
			}
			elseif($findpatch) //download patch
			{
				$url = "https://$update_server/downloads/versions/current/$update_channel/patch_$local_version_array[0].$local_version_array[1].$next_remote_patch.zip";
				$file_local_url = __DIR__ ."/../download/patch_$local_version_array[0].$local_version_array[1].$next_remote_patch.zip";
				$zipResource = fopen($file_local_url, "w");
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_FAILONERROR, true);
				curl_setopt($curl, CURLOPT_HEADER, 0);
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($curl, CURLOPT_AUTOREFERER, true);
				curl_setopt($curl, CURLOPT_BINARYTRANSFER,true);
				curl_setopt($curl, CURLOPT_TIMEOUT, 10);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); 
				curl_setopt($curl, CURLOPT_FILE, $zipResource);
				if($rparameters['server_proxy_url']) {curl_setopt($curl, CURLOPT_PROXY, $rparameters['server_proxy_url']);}
				$download = curl_exec($curl);
				if(!$download) {echo "Error :- ".curl_error($curl);}
				curl_close($curl);

				//update download counter on gestsup.fr
				$url='https://gestsup.fr/downloaded.php?type=patch&version='.$local_version_array[0].'.'.$local_version_array[1].'.'.$next_remote_patch.'&channel='.$rparameters['update_channel'];
				$curl = curl_init($url);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
				if($rparameters['server_proxy_url']) {curl_setopt($curl, CURLOPT_PROXY, $rparameters['server_proxy_url']);}
				curl_exec($curl);
				if(curl_error($curl)) die(curl_error($curl));
				curl_close($curl);

				//check if file exist
				if(file_exists($file_local_url)) 
				{
					if($cmd_update){echo 'DOWNLOAD GESTSUP PATCH COMPLETED'.PHP_EOL;} else {
						echo DisplayMessage('success',T_('Le patch').' '.$local_version_array[0].'.'.$local_version_array[1].'.'.$next_remote_patch.' '.T_('à été téléchargé dans le répertoire "./download" du serveur web'));
					}
				}else{
					if($cmd_update){echo 'ERROR DURING DOWNLOAD THE LATEST PATCH'.PHP_EOL;exit;
					} else {
						echo DisplayMessage('error',T_('Le téléchargement du patch à échoué'));
						LogIt('error', "ERROR 6 : Download patch '.$local_version_array[0].'.'.$local_version_array[1].'.'.$next_remote_patch.' failed",$_SESSION['user_id']);
					}
				}
			} else {
				if($cmd_update)
				{
					echo 'CURRENT VERSION ALREADY UP TO DATE NO DOWNLOAD AVAILABLE'.PHP_EOL;
				} else {
					echo DisplayMessage('success',T_('Votre version').' '.$local_version.' '.T_('est à jour, pas de téléchargement nécessaire'));
				}
			}
		}
		
		//install version
		if($_POST['install'] || $autoinstall)
		{
			if(!$findpatch && !$findversion)
			{
				if($cmd_update)
				{
					echo 'UNABLE TO INSTALL UPDATE'.PHP_EOL;
				} else {
					echo DisplayMessage('success',T_('Installation impossible votre version est à jour'));
				}
			} 
			if($findversion) 
			{
				if(file_exists(__DIR__ ."/../download/gestsup_$last_remote_version.zip"))
				{
					$installfile="gestsup_$last_remote_version.zip";
					if($cmd_update)
					{
						echo 'INSTALLING UPDATE...'.PHP_EOL;
					} else {
						echo DisplayMessage('success',T_('Installation du fichier').' '.$installfile.' '.T_('en cours...'));
					}
					$type="version";
					include(__DIR__ ."/../core/install_update.php");
				} else {
					if($cmd_update)
					{
						echo 'ERROR YOU MUST DOWNLOAD LATEST VERSION FIRST'.PHP_EOL;
					} else {
						echo DisplayMessage('error',T_("Vous devez d'abord télécharger la dernière version").' '.$last_remote_version);
					}
				}
			}
			if($findpatch)
			{
				if(file_exists(__DIR__ ."/../download/patch_$local_version_array[0].$local_version_array[1].$next_remote_patch.zip"))
				{
					$installfile="patch_$local_version_array[0].$local_version_array[1].$next_remote_patch.zip";
					if($cmd_update)
					{
						echo 'INSTALLING UPDATE...'.PHP_EOL;
					} else {
						echo DisplayMessage('success',T_('Installation du fichier').' '.$installfile.' '.T_('en cours...'));
					}
					$type="patch";
					include(__DIR__ ."/../core/install_update.php");
				} else {
					if($cmd_update)
					{
						echo 'ERROR : YOU MUST DOWNLOAD THE LATEST PATCH BEFORE'.PHP_EOL;
					} else {
						echo DisplayMessage('error',T_("Vous devez d'abord télécharger le dernier patch").' '.$next_remote_patch);
					}
				}
			}
		}
		
	} else {
		$serverstate='<i class="fa fa-times text-danger"><!----></i> <span class="text-danger">'.T_("Serveur de mise à jour GestSup indisponible, ou vous avez un problème de connection internet ou vous n'avez pas autorisé le port 443 vers gestsup.fr sur votre firewall").'.</span>';
	}

	//display informations
	if(!$cmd_update)
	{
		echo'
			<div class="mt-4 mt-lg-0 card bcard h-auto overflow-hidden shadow border-0">
				<div class="card-body p-0 table-responsive-xl">
					<table class="table text-dark-m1 brc-black-tp10 mb-0">
						<tbody>
							<tr>
								<td style="width: 160px;" class="text-95 text-default-d3 bgc-secondary-l4"><i class="fa fa-tag text-blue-m3 pr-1"><!----></i>'.T_('Version actuelle').'</td>
								<td class="text-95 text-default-d3">'.$rparameters['version'].'</td>
							</tr>
							<tr>
								<td style="width: 160px;" class="text-95 text-default-d3 bgc-secondary-l4"><i class="fa fa-tag text-blue-m3 pr-1"><!----></i>'.T_('Dernière version').'</td>
								<td class="text-95 text-default-d3">'.$rparameters['last_version'].' </td>
							</tr>
							<tr>
								<td style="width: 160px;" class="text-95 text-default-d3 bgc-secondary-l4"><i class="fa fa-code-branch text-blue-m3 pr-1"><!----></i>'.T_('Canal').'</td>
								<td class="text-95 text-default-d3">
									<form method="POST" name="form">
										<select style="width:auto;" class="form-control form-control-sm " name="update_channel" onchange="submit()">
											<option value="stable" '; if($update_channel=='stable') {echo 'selected';} echo '>'.T_('Stable').'</option>
											<option value="beta" '; if($update_channel=='beta') {echo 'selected';} echo '>'.T_('Bêta').'</option>
										</select>
									</form>
								</td>
							</tr>
							<tr>
								<td style="width:220px;" class="text-95 text-default-d3 bgc-secondary-l4"><i class="fa fa-server text-blue-m3 pr-1"><!----></i>'.T_('Serveur de mise à jour').'</td>
								<td class="text-95 text-default-d3">'.$serverstate.'</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		
			<div class=" brc-secondary-l1 bgc-secondary-l4 py-3 text-center mt-5">
				<form method="POST" action="">
					<button  title="'.T_('Vérifie sur le serveur FTP de GestSup si une version plus récente existe').'." name="check" value="check" type="submit" class="btn btn-primary m-1">
						<i class="fa fa-check-circle pr-1"><!----></i>
						1 - '.T_('Vérifier').' 
					</button>
					<button  title="'.T_("Redirige vers la section sauvegarde de l'application").'" onclick=\'window.open("./index.php?page=admin&amp;subpage=backup")\'  type="submit" class="btn btn-primary m-1">
						<i class="fa fa-save pr-1"><!----></i>
						2 - '.T_('Réaliser une sauvegarde').'
					</button>
					<button title="'.T_('Lance le téléchargement depuis le serveur FTP de GestSup si une version plus récente existe').'." name="download" value="download" type="submit" class="btn btn-primary m-1">
						<i class="fa fa-download pr-1"><!----></i>
						3 - '.T_('Télécharger').'
					</button>
					';
					if(!$hide_install_button)
					{
						echo '
						<button title="'.T_("Lance l'installation de la version téléchargée").'" name="install" value="install" type="submit" class="btn btn-primary m-1">
							<i class="fa fa-hdd pr-1"><!----></i>
							4 - '.T_('Installation semi-automatique').'
						</button>
						';
					}
					echo '
				</form>
				<br />
				<button title="'.T_('Lance le site web dans la section documentation').'" onclick=\'window.open("https://doc.gestsup.fr/update/#manuelle")\' type="submit" class="btn btn-grey m-1">
					<i class="fa fa-book pr-1"><!----></i>
					'.T_('Installation manuelle').'
				</button>
				<button title="'.T_('Lance le site web dans la section documentation').'" onclick=\'window.open("https://doc.gestsup.fr/update/#automatique")\' type="submit" class="btn btn-grey m-1">
					<i class="fa fa-book pr-1"><!----></i>
					'.T_('Installation automatique').'
				</button>
			</div>
		';
	}
} else { //dedicated version
	//find current version
	$current_version=$rparameters['version'];
	$local_version_array= explode('.',$current_version);
	
	//find number of current patch
	$local_patch=$local_version_array[3];

	//get all patch available
	$url='https://'.$update_server.'/available_patch.php?key='.$rparameters['server_private_key'];
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	if($rparameters['server_proxy_url']) {curl_setopt($curl, CURLOPT_PROXY, $rparameters['server_proxy_url']);}
	$remote_patch_list=json_decode(curl_exec($curl),true);
	if(curl_error($curl)) die(curl_error($curl));
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
	
	//check lastest version
	if($status!='200' || !$remote_patch_list)
	{
		LogIt('error', 'ERROR 34 : GET ALL PATCHS, unable to get '.$url.' error '.$status,0);
		echo DisplayMessage('error',T_("Impossible de récupérer la liste des derniers  patch via $update_server sur le port 443"));
		$hide_install_button=1;	
	} 

	//get patch only
	$remote_patch_list = preg_grep("/patch_$local_version_array[0].$local_version_array[1].$local_version_array[2]/", $remote_patch_list);
	$patch_list_array = array();
	foreach($remote_patch_list as $patch){
		$patch=explode("_",$patch);
		$patch=explode(".zip",$patch[1]);
		$patch=explode(".",$patch[0]);
		array_push($patch_list_array, $patch[3]);
	}
	
	//order patch number
	asort($patch_list_array);

	//get last patch
	$last_remote_patch=end($patch_list_array);

	//display debug information
	if ($last_remote_patch) 
	{
		if($rparameters['debug']) {echo "LAST AVAILABLE PATCH : $local_version_array[0].$local_version_array[1].$local_version_array[2].$last_remote_patch  <br />";}
	} else {
		if($rparameters['debug']) {echo "LAST AVAILABLE PATCH :  No patch available for version $local_version_array[0].$local_version_array[1].$local_version_array[2]  <br />";}
	}

	if(!$last_remote_patch) {
		$serverstate='<i class="fa fa-times text-danger"><!----></i> <span class="text-danger">'.T_("Serveur de mise à jour indisponible, ou vous avez un problème de connection internet ou vous n'avez pas autorisé le port 443 sur votre firewall").'.</span>';
	} else {
		$serverstate='<i class="fa fa-check-circle text-success"><!----></i> <span class="text-success">'.T_('Serveur de mise à jour').' <b>'.$update_server.'</b> '.T_('est disponible').'.</span>';
	}
	
	//check update server
	$findversion=0;
	$findpatch=0;

	if(!$last_remote_patch)
	{
		if($cmd_update)
		{
			echo 'NO NEW PATCH AVAILABLE FOR YOUR VERSION'.PHP_EOL;
			exit;
		} else {
			$message=T_('Aucun nouveau patch pour votre version').' '.$local_version_array[0].'.'.$local_version_array[1].'.'.$local_version_array[2].' Patch '.$local_patch.' '.T_("n'est encore disponible");
		}
	}elseif($local_patch==$last_remote_patch)
	{
		if($rparameters['debug']) {echo "COMPARE PATCH : Local server patch $local_patch is the same that FTP server $last_remote_patch <br />"; }
		$message=T_('Votre version').' '.$local_version_array[0].'.'.$local_version_array[1].'.'.$local_version_array[2].' Patch '.$local_patch.' '.T_('est à jour');
	} 
	elseif ($local_patch>$last_remote_patch)
	{
		if($rparameters['debug']) {echo "COMPARE PATCH : Local server patch $local_patch is superior than FTP server $last_remote_patch <br />"; }
		if($cmd_update)
		{
			echo 'CURRENT VERSION IS SUPERIOR THAN AVAILABLE VERSION'.PHP_EOL;
			exit;
		} else {
			$message=T_('Votre version').' '.$local_version_array[0].'.'.$local_version_array[1].'.'.$local_version_array[2].' Patch '.$local_patch.' '.T_('est supérieur à la dernière version disponible, vous devez avoir changé de canal de mises à jour');
		}
	}
	elseif ($local_patch<$last_remote_patch)
	{
		$findpatch=1;
		//generate n+1 name if more than one patch is available
		if (($last_remote_patch-$local_patch)>1) {$next_remote_patch=$local_patch+1;} else {$next_remote_patch=$last_remote_patch;}
		if($rparameters['debug']) {echo "COMPARE PATCH Local server patch $local_patch is inferior than FTP Server $last_remote_patch <br />"; }
		if($cmd_update)
		{
			echo 'A NEW PATCH IS AVAILABLE FOR YOUR VERSION'.PHP_EOL;
		} else {
			$message=T_('Un nouveau patch').' '.$local_version_array[0].'.'.$local_version_array[1].'.'.$local_version_array[2].'.'.$next_remote_patch.' '.T_('est disponible en téléchargement');
		}
	}
	//display check message
	if($cmd_update)
	{
		echo 'A NEW PATCH IS AVAILABLE FOR YOUR VERSION'.PHP_EOL;
	} else {
		if($_POST['check']) {echo DisplayMessage('success',$message);}
	}
	
	//downloads
	if($_POST['download'] || ($autoinstall==1))
	{
		if($findpatch) // download patch
		{
			$url = "https://$update_server/ftp/versions/dedicated/$rparameters[server_private_key]/patch_$local_version_array[0].$local_version_array[1].$local_version_array[2].$next_remote_patch.zip";
			$file_local_url=__DIR__ ."/../download/patch_$local_version_array[0].$local_version_array[1].$local_version_array[2].$next_remote_patch.zip";
			$zipResource = fopen($file_local_url, "w");
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_FAILONERROR, true);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_AUTOREFERER, true);
			curl_setopt($curl, CURLOPT_BINARYTRANSFER,true);
			curl_setopt($curl, CURLOPT_TIMEOUT, 10);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); 
			curl_setopt($curl, CURLOPT_FILE, $zipResource);
			if($rparameters['server_proxy_url']) {curl_setopt($curl, CURLOPT_PROXY, $rparameters['server_proxy_url']);}
			$download = curl_exec($curl);
			if(!$download) {echo "Error :- ".curl_error($curl);}
			curl_close($curl);

			//update download counter on gestsup.fr
			$url='https://gestsup.fr/downloaded.php?type=patch&version='.$local_version_array[0].'.'.$local_version_array[1].'.'.$local_version_array[2].'.'.$next_remote_patch.'&channel='.$rparameters['update_channel'];
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			if($rparameters['server_proxy_url']) {curl_setopt($curl, CURLOPT_PROXY, $rparameters['server_proxy_url']);}
			curl_exec($curl);
			if(curl_error($curl)) die(curl_error($curl));
			curl_close($curl);

			//check if file exist
			if(file_exists($file_local_url)) 
			{
				if($cmd_update){echo 'DOWNLOAD GESTSUP PATCH COMPLETED'.PHP_EOL;} else {
					echo DisplayMessage('success',T_('Le patch').' '.$local_version_array[0].'.'.$local_version_array[1].'.'.$local_version_array[2].'.'.$next_remote_patch.' '.T_('à été téléchargé dans le répertoire "./download" du serveur web'));
				}
			}else{
				if($cmd_update){echo 'ERROR DURING DOWNLOAD THE LATEST PATCH'.PHP_EOL;exit;
				} else {
					echo DisplayMessage('error',T_('Le téléchargement du patch à échoué'));
					LogIt('error', "ERROR 6 : Download patch '.$local_version_array[0].'.'.$local_version_array[1].'.'.$local_version_array[2].'.'.$next_remote_patch.' failed",$_SESSION['user_id']);
				}
			}
		} else {
			if($cmd_update)
			{
				echo 'YOUR VERSION IS UP TO DATE'.PHP_EOL;
			} else {
				echo DisplayMessage('success',T_('Votre version').' '.$current_version.' '.T_('est à jour, pas de téléchargement nécessaire'));
			}
		}
	}
	//install patch
	if($_POST['install'] || $autoinstall)
	{
		if (!$findpatch)
		{
			if($cmd_update)
			{
				echo 'UNABLE TO INSTALL YOUR VERSION IS UP TO DATE'.PHP_EOL;
				exit;
			} else {
				echo DisplayMessage('success',T_('Installation impossible votre version est à jour'));
			}
		} 
		if($findpatch)
		{
			if(file_exists(__DIR__ ."/../download/patch_$local_version_array[0].$local_version_array[1].$local_version_array[2].$next_remote_patch.zip"))
			{
				$installfile="patch_$local_version_array[0].$local_version_array[1].$local_version_array[2].$next_remote_patch.zip";
				if($cmd_update)
				{
					echo 'INSTALLING PATCH...'.PHP_EOL;
				} else {
					echo DisplayMessage('success',T_('Installation du fichier').' '.$installfile.' '.T_('en cours...'));
				}
				$type="patch";
				include(__DIR__ ."/../core/install_update.php");
			} else {
				if($cmd_update)
				{
					echo 'YOU MUST DOWNLOAD THE LATEST PATCH BEFORE'.PHP_EOL;
					exit;
				} else {
					echo DisplayMessage('error',T_("Vous devez d'abord télécharger le dernier patch").' '.$next_remote_patch);
				}
			}
		}
	}

	//display informations
	if(!$cmd_update)
	{
		echo'
		<div class="mt-4 mt-lg-0 card bcard h-auto overflow-hidden shadow border-0">
			<div class="card-body p-0 table-responsive-xl">
				<table class="table text-dark-m1 brc-black-tp10 mb-0">
					<tbody>
						<tr>
							<td style="width: 220px;" class="text-95 text-default-d3 bgc-secondary-l4"><i class="fa fa-tag text-blue-m3 pr-1"><!----></i>'.T_('Version actuelle').'</td>
							<td class="text-95 text-default-d3"><a href="./index.php?page=changelog">'.$rparameters['version'].' </td>
						</tr>
						<tr>
							<td style="width: 220px;" class="text-95 text-default-d3 bgc-secondary-l4"><i class="fa fa-server text-blue-m3 pr-1"><!----></i>'.T_('Serveur de mises à jour').'</td>
							<td class="text-95 text-default-d3">'.$serverstate.'</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="border-t-1 brc-secondary-l1 bgc-secondary-l4 py-3 text-center mt-5">
			<form method="POST" action="">
				<button  title="'.T_('Vérifie sur le serveur FTP de GestSup si une version plus récente existe').'." name="check" value="check" type="submit" class="btn btn-primary mr-1">
					<i class="fa fa-check-circle "><!----></i>
					1- '.T_('Vérifier').' 
				</button>
				<button title="'.T_("Redirige vers la section sauvegarde de l'application").'" onclick=\'window.open("./index.php?page=admin&subpage=backup")\' type="submit" class="btn btn-primary mr-1">
					<i class="fa fa-save "><!----></i>
					2- '.T_('Réaliser une sauvegarde').'
				</button>
				<button title="'.T_('Lance le téléchargement depuis le serveur FTP de GestSup si une version plus récente existe').'." name="download" value="download" type="submit" class="btn btn-primary mr-1">
					<i class="fa fa-download "><!----></i>
					3- '.T_('Télécharger').'
				</button>
				<button title="'.T_("Lance l'installation de la version téléchargée").'" name="install" value="install" type="submit" class="btn btn-primary">
					<i class="fa fa-hdd "><!----></i>
					4- '.T_('Installation semi-automatique').'
				</button>
			</form>
				<br />
				<button title="'.T_('Lance le site web dans la section documentation').'" onclick=\'window.open("https://doc.gestsup.fr/update/#manuelle")\' type="submit" class="btn btn-grey mr-1">
					<i class="fa fa-book "><!----></i>
					'.T_('Installation manuelle').'
				</button>
				<button title="'.T_('Lance le site web dans la section documentation').'" onclick=\'window.open("https://doc.gestsup.fr/update/#automatique")\' type="submit" class="btn btn-grey">
					<i class="fa fa-book "><!----></i>
					'.T_('Installation automatique').'
				</button>
		</div>
		';
	}
}
?>