<?php
################################################################################
# @Name : system.php
# @Description :  admin system
# @Call : ./admin.php, install/index.php
# @Parameters : 
# @Author : Flox
# @Create : 10/11/2013
# @Update : 17/04/2024
# @Version : 3.2.50 p1
################################################################################

//check access
if(!is_dir('./../install') && $_GET['page']!='admin') {echo 'ERROR : invalid access'; exit;}

//require files
require_once("core/init_get.php");
require_once("core/functions.php");

//initialize variables 
$system_error=0;
$system_warning=0;

if($_GET['page']=='admin')
{
	//create /upload/logo/ directory if not exist
	if(!is_dir('upload/logo') && is_writeable('upload')) {mkdir('upload/logo');}
}

//clean old files
if(file_exists('ajax/sendmail.php') && is_writable('ajax/sendmail.php')){unlink('ajax/sendmail.php');}
if(file_exists('ajax/ticket_asset_db.php') && is_writable('ajax/ticket_asset_db.php')){unlink('ajax/ticket_asset_db.php');}
if(file_exists('ajax/ticket_sender_service_db.php') && is_writable('ajax/ticket_sender_service_db.php')){unlink('ajax/ticket_sender_service_db.php');}
if(file_exists('ajax/ticket_subcat_db.php') && is_writable('ajax/ticket_subcat_db.php')){unlink('ajax/ticket_subcat_db.php');}
if(file_exists('ajax/ticket_user_db.php') && is_writable('ajax/ticket_user_db.php')){unlink('ajax/ticket_user_db.php');}
if(file_exists('js/parameters.js') && is_writable('js/parameters.js')){unlink('js/parameters.js');}
if(file_exists('fileupload.php') && is_writable('fileupload.php')){unlink('fileupload.php');}
if(file_exists('attachement.php') && is_writable('attachement.php')){unlink('attachement.php');}
if(file_exists('vendor/components/bootstrap-wysiwyg/examples/php/upload.php') && is_writable('vendor/components/bootstrap-wysiwyg/examples/php/upload.php')){unlink('vendor/components/bootstrap-wysiwyg/examples/php/upload.php');}
if(file_exists('composer.json') && is_writable('composer.json') && $rparameters['server_private_key']!='5a237a1a7c078b09e819f7e3a5825065'){unlink('composer.json');}
if(file_exists('composer.lock') && is_writable('composer.lock') && $rparameters['server_private_key']!='5a237a1a7c078b09e819f7e3a5825065'){unlink('composer.lock');}
if(file_exists('core/crypt.php') && is_writable('core/crypt.php')){unlink('core/crypt.php');}

//remove old components
if(is_dir('components') && is_writable('components/wol/wol.exe'))
{
	function removeDirectory($path) {
		$files = glob($path . '/*');
		foreach ($files as $file) {
			is_dir($file) ? removeDirectory($file) : unlink($file);
		}
		rmdir($path);
		return;
	}
	removeDirectory('components');
}

//remove old logo
if(file_exists('upload/logo/logo.png') && filesize('upload/logo/logo.png')=='2182' && $rparameters['logo']=='logo.png')
{
	$qry=$db->prepare("UPDATE `tparameters` SET `logo`=''");
	$qry->execute();
}

//check user_id 0
if($_GET['page']=='admin') 
{
	$qry=$db->prepare("SELECT `id` FROM `tusers` WHERE `id`=0");
	$qry->execute();
	$user=$qry->fetch();
	$qry->closeCursor();

	if(!isset($user['id']))
	{
		$qry=$db->prepare("SELECT `id` FROM `tusers` WHERE `login`='aucun'");
		$qry->execute();
		$user=$qry->fetch();
		$qry->closeCursor();

		if(!isset($user['id']))
		{
			$qry=$db->prepare("DELETE FROM `tusers` WHERE `login`='aucun'");
			$qry->execute();
			$qry=$db->prepare("INSERT INTO `tusers` (`id`,`login`,`lastname`,`profile`,`disable`) VALUES ('0','aucun','Aucun','2','1')");
			$qry->execute();
		} else { 
			$qry=$db->prepare("DELETE FROM `tusers` WHERE `login`='aucun'");
			$qry->execute();
		}
	} 
}


//for install call
if($_GET['page']=='admin') 
{
	require ('./connect.php');
	CheckUpdate();
	$install=0;

	//update install date if not defined
	if($rparameters['server_date_install']=='0000-00-00' || $rparameters['server_date_install']=='0001-11-30' || $rparameters['server_date_install']<'2007-01-01' )
	{
		$qry=$db->prepare("SELECT MIN(date) FROM `tthreads`;");
		$qry->execute();
		$min_date=$qry->fetch();
		$qry->closeCursor();

		if($min_date[0] && $min_date[0]!='0000-00-00' && $min_date[0]!='0001-11-30')
		{
			$qry=$db->prepare("UPDATE `tparameters` SET `server_date_install`=:server_date_install");
			$qry->execute(array('server_date_install' => $min_date[0]));
		}
	}
} else {
	require ('../connect.php');
	
	//load parameters table
	$qry=$db->prepare("SELECT * FROM `tparameters`");
	$qry->execute();
	$rparameters=$qry->fetch();
	$qry->closeCursor();
	
	//mobile detection
	if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$_SERVER['HTTP_USER_AGENT'])||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($_SERVER['HTTP_USER_AGENT'],0,4)))
	{$mobile=1;} else {$mobile=0;}
	$install=1;
}

//create private server key if not exist used to auto-installation URL
if($rparameters['server_private_key']=='') 
{
	$key=md5(uniqid());
	$qry=$db->prepare("UPDATE `tparameters` SET `server_private_key`=:server_private_key WHERE `id`=1");
	$qry->execute(array('server_private_key' => $key));
}

//detect https connection
if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {$http='https';} else {$http='http';}

//extract php info
ob_start();
phpinfo();
$phpinfo = array('phpinfo' => array());
if(preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER))
    foreach($matches as $match)
        if(strlen($match[1]))
            $phpinfo[$match[1]] = array();
        elseif(isset($match[3])){
			$ak=array_keys($phpinfo);
            $phpinfo[end($ak)][$match[2]] = isset($match[4]) ? array($match[3], $match[4]) : $match[3];
			}
        else
            {
			$ak=array_keys($phpinfo);
            $phpinfo[end($ak)][] = $match[2];
		}

//find PHP table informations, depends of PHP versions			
if(isset($phpinfo['Core'])!='') $vphp='Core';
elseif(isset($phpinfo['PHP Core'])!='') $vphp='PHP Core';
elseif(isset($phpinfo['HTTP Headers Information'])!='') $vphp='HTTP Headers Information'; 

//initialize variables 
if(!isset($_POST['Modifier'])) $_POST['Modifier'] = '';
if(!isset($phpinfo[$vphp]['file_uploads'][0])) $phpinfo[$vphp]['file_uploads'][0] = '';
if(!isset($phpinfo[$vphp]['memory_limit'][0])) $phpinfo[$vphp]['memory_limit'][0] = '';
if(!isset($phpinfo[$vphp]['upload_max_filesize'][0])) $phpinfo[$vphp]['upload_max_filesize'][0] = '';
if(!isset($phpinfo[$vphp]['post_max_size'][0])) $phpinfo[$vphp]['post_max_size'][0] = '';
if(!isset($phpinfo[$vphp]['max_execution_time'][0])) $phpinfo[$vphp]['max_execution_time'][0] = '';
if(!isset($phpinfo['date']['date.timezone'][0])) $phpinfo['date']['date.timezone'][0] = '';
if(!isset($i)) $i = '';
if(!isset($php_error)) $php_error = '';
if(!isset($php_warning)) $php_warning = '';

//SQL db connect
if($_GET['page']!='admin') {require('../connect.php');}

//get rdb database version 
$qry=$db->prepare("SHOW VARIABLES");
$qry->execute();
while($row=$qry->fetch()) 
{
	if($row[0]=="version") {
		$rdb_version=$row[1];
		if(strpos($rdb_version, 'MariaDB')) {
			$rdb_name='MariaDB';
			$rdb_icon=explode('-',$rdb_version);
			$rdb_icon=explode('.',$rdb_icon[0]);
			if($rdb_icon[0]>=10 && $rdb_icon[1]>=1) {$rdb_icon='ok';} else {$rdb_icon='ko';}
		} else {
			$rdb_name='MySQL';
			$rdb_icon='ko';
		}
	}
}
$qry->closeCursor();

//check OS
$os=$phpinfo['phpinfo']['System'];
$os= explode(" ",$os);
$os=$os[0];

//check and convert current ram value in MB value to check prerequisites
$ram=$phpinfo[$vphp]['memory_limit'][0];
if(preg_match("/M/",$ram)) {$ram_mb=explode('M',$ram);$ram_mb=$ram_mb[0];}
if(preg_match("/m/",$ram)) {$ram_mb=explode('m',$ram);$ram_mb=$ram_mb[0];}
if(preg_match("/G/",$ram)) {$ram_mb=explode('G',$ram);$ram_mb=$ram_mb[0]*1024;}
if(preg_match("/g/",$ram)) {$ram_mb=explode('g',$ram);$ram_mb=$ram_mb[0]*1024;}
if(!$ram_mb) {$ram_mb=$phpinfo[$vphp]['memory_limit'][0];}

$max_filesize=$phpinfo[$vphp]['upload_max_filesize'][0];
if(preg_match("/M/",$max_filesize)) {$max_filesize_mb=explode('M',$max_filesize);$max_filesize_mb=$max_filesize_mb[0];}
if(preg_match("/m/",$max_filesize)) {$max_filesize_mb=explode('m',$max_filesize);$max_filesize_mb=$max_filesize_mb[0];}
if(preg_match("/G/",$max_filesize)) {$max_filesize_mb=explode('G',$max_filesize);$max_filesize_mb=$max_filesize_mb[0]*1024;}
if(preg_match("/g/",$max_filesize)) {$max_filesize_mb=explode('g',$max_filesize);$max_filesize_mb=$max_filesize_mb[0]*1024;}
if(!$max_filesize_mb) {$max_filesize_mb=$phpinfo[$vphp]['upload_max_filesize'][0];}

$post_max_size=$phpinfo[$vphp]['post_max_size'][0];
if(preg_match("/M/",$post_max_size)) {$post_max_size_mb=explode('M',$post_max_size);$post_max_size_mb=$post_max_size_mb[0];}
if(preg_match("/m/",$post_max_size)) {$post_max_size_mb=explode('m',$post_max_size);$post_max_size_mb=$post_max_size_mb[0];}
if(preg_match("/G/",$post_max_size)) {$post_max_size_mb=explode('G',$post_max_size);$post_max_size_mb=$post_max_size_mb[0]*1024;}
if(preg_match("/g/",$post_max_size)) {$post_max_size_mb=explode('g',$post_max_size);$post_max_size_mb=$post_max_size_mb[0]*1024;}
if(!$post_max_size_mb) {$post_max_size_mb=$phpinfo[$vphp]['upload_post_max_size'][0];}

//get mariadb parameters
$qry=$db->prepare("SHOW VARIABLES LIKE 'innodb_buffer_pool_size';");
$qry->execute();
$innodb_buffer_pool_size=$qry->fetch();
$qry->closeCursor();
$innodb_buffer_pool_size=$innodb_buffer_pool_size[1];
$innodb_buffer_pool_size=$innodb_buffer_pool_size;

//get web server name
$web_server=$_SERVER['SERVER_SOFTWARE'];
$web_server=explode('/',$web_server);
$web_server_name=strtolower($web_server[0]);
if(isset($web_server[1])) {
	$web_server_version=$web_server[1];
	$web_server_version=explode(' ',$web_server_version);
	$web_server_version=$web_server_version[0];
} else {
	$web_server_version=T_('Non disponible');
}

if($web_server_name!='nginx')
{
	//get apache version
	$apache=$_SERVER['SERVER_SOFTWARE'];
	$apache=preg_split('[ ]', $apache); 
	$apache=preg_split('[/]', $apache[0]);
	if(isset($apache[1])) {
		$apache_version=$apache[1]; 
		$apache_display_version=1;
		$apache_icon=explode(".",$apache[1]);
		if($apache_icon[0]>=2 && $apache_icon[1]>=4){$web_server_icon='apache_ok.png';} else {$web_server_icon='apache_ko.png';}
	} else {
		$apache_version=T_('Version non disponible, serveur sécurisé');
		$apache_display_version=0;
		$web_server_icon='apache_ok.png';
	}
} else {
	$web_server_icon='nginx_ok.png';
}

//get components versions
if($_GET['page']!='admin')
{
	$phpmailer_phpmailer = file_get_contents('../vendor/phpmailer/phpmailer/VERSION');
	$inetsys_phpgettext = file_get_contents('../vendor/components/php-gettext/VERSION');
	$barbushin_phpimap = file_get_contents('../vendor/php-imap/php-imap/VERSION');
	$highcharts_highcharts = file_get_contents('../vendor/components/Highcharts/VERSION');
	$ifsnop_mysqldumpphp = file_get_contents('../vendor/ifsnop/mysqldump-php/VERSION');
	$fullcalendar_fullcalendar = file_get_contents('../vendor/components/fullcalendar/VERSION');
	$jquery_jquery = file_get_contents('../vendor/components/jquery/VERSION');
	$twbs_bootstrap = file_get_contents('../vendor/components/bootstrap/VERSION');
	$itsjavi_bootstrapcolorpicker = file_get_contents('../vendor/components/bootstrap-colorpicker/VERSION');
	$steveathon_bootstrapwysiwyg = file_get_contents('../vendor/components/bootstrap-wysiwyg/VERSION');
	$tempusdominus_bootstrap4 = file_get_contents('../vendor/components/tempusdominus/bootstrap-4/VERSION');
	$moment_moment = file_get_contents('../vendor/moment/moment/VERSION');
	$microsoftgraph_msgraph_sdk_php = file_get_contents('../vendor/microsoft/microsoft-graph/VERSION');
	$selectize_selectizejs= file_get_contents('../vendor/components/selectize/VERSION');
	$swagger_api_swagger_ui= file_get_contents('../vendor/components/swagger-ui/VERSION');
	$jeresig_jqueryhotkeys = file_get_contents('../vendor/components/jquery-hotkeys/VERSION');
	$FezVrasta_popperjs = file_get_contents('../vendor/components/popper-js/VERSION');
	$fortawesome_fontawesome = file_get_contents('../vendor/fortawesome/font-awesome/VERSION');
	$makeusabrew_bootbox = file_get_contents('../vendor/components/bootbox/VERSION');
	$ace = file_get_contents('../template/ace/VERSION');
	$thephpleague_oauth2client = file_get_contents('../vendor/league/oauth2-client/VERSION');
	$thephpleague_oauth2google = file_get_contents('../vendor/league/oauth2-google/VERSION');
	$stevenmaguire_oauth2microsoft = file_get_contents('../vendor/stevenmaguire/oauth2-microsoft/VERSION');
	$greew_oauth2azureprovider = file_get_contents('../vendor/greew/oauth2-azure-provider/VERSION');
	$ezyang_htmlpurifier = file_get_contents('../vendor/ezyang/htmlpurifier/VERSION');
	$webklex_phpimap = file_get_contents('../vendor/webklex/php-imap/VERSION');
	$thenetworg_oauth2_azure = file_get_contents('../vendor/thenetworg/oauth2-azure/VERSION');
	
	//get log file to check version db and file version
	$changelog = file_get_contents('../changelog.php');
} else {
	$phpmailer_phpmailer = file_get_contents('./vendor/phpmailer/phpmailer/VERSION');
	$inetsys_phpgettext = file_get_contents('./vendor/components/php-gettext/VERSION');
	$barbushin_phpimap = file_get_contents('./vendor/php-imap/php-imap/VERSION');
	$highcharts_highcharts = file_get_contents('./vendor/components/Highcharts/VERSION');
	$ifsnop_mysqldumpphp = file_get_contents('./vendor/ifsnop/mysqldump-php/VERSION');	
	$fullcalendar_fullcalendar = file_get_contents('./vendor/components/fullcalendar/VERSION');	
	$jquery_jquery = file_get_contents('./vendor/components/jquery/VERSION');	
	$twbs_bootstrap = file_get_contents('./vendor/components/bootstrap/VERSION');	
	$itsjavi_bootstrapcolorpicker = file_get_contents('./vendor/components/bootstrap-colorpicker/VERSION');
	$steveathon_bootstrapwysiwyg = file_get_contents('./vendor/components/bootstrap-wysiwyg/VERSION');	
	$tempusdominus_bootstrap4 = file_get_contents('./vendor/components/tempusdominus/bootstrap-4/VERSION');
	$microsoftgraph_msgraph_sdk_php = file_get_contents('./vendor/microsoft/microsoft-graph/VERSION');	
	$moment_moment = file_get_contents('./vendor/moment/moment/VERSION');	
	$selectize_selectizejs= file_get_contents('./vendor/components/selectize/VERSION');	
	$swagger_api_swagger_ui= file_get_contents('./vendor/components/swagger-ui/VERSION');
	$jeresig_jqueryhotkeys = file_get_contents('./vendor/components/jquery-hotkeys/VERSION');	
	$FezVrasta_popperjs = file_get_contents('./vendor/components/popper-js/VERSION');	
	$fortawesome_fontawesome = file_get_contents('./vendor/fortawesome/font-awesome/VERSION');	
	$makeusabrew_bootbox = file_get_contents('./vendor/components/bootbox/VERSION');	
	$ace = file_get_contents('./template/ace/VERSION');	
	$thephpleague_oauth2client = file_get_contents('./vendor/league/oauth2-client/VERSION');
	$thephpleague_oauth2google = file_get_contents('./vendor/league/oauth2-google/VERSION');
	$stevenmaguire_oauth2microsoft = file_get_contents('./vendor/stevenmaguire/oauth2-microsoft/VERSION');
	$greew_oauth2azureprovider = file_get_contents('./vendor/greew/oauth2-azure-provider/VERSION');
	$ezyang_htmlpurifier = file_get_contents('./vendor/ezyang/htmlpurifier/VERSION');
	$webklex_phpimap = file_get_contents('./vendor/webklex/php-imap/VERSION');
	$thenetworg_oauth2_azure = file_get_contents('./vendor/thenetworg/oauth2-azure/VERSION');

	//get log file to check version db and file version
	$changelog = file_get_contents('changelog.php');
}

//get php session max lifetime parameter
$maxlifetime = ini_get("session.gc_maxlifetime");

//get db size
function formatfilesize($data) {
    if($data < 1024) {return $data . " bytes";}
    else if($data < 1024000) {return round(($data / 1024 ), 1) . "k";}
    else {return round(($data / 1024000), 1);}
}
$db_size=0;
$qry=$db->prepare("SHOW TABLE STATUS");
$qry->execute();
while($row=$qry->fetch()){$db_size += $row["Data_length"] + $row["Index_length"];}
$qry->closeCursor();
$db_size=formatfilesize($db_size);

if(!isset($gestsup_version))
{
	//get file version from changelog.php
	$matches = array(); 
	preg_match('/^.*\Version\b.*$/m',$changelog,$matches);
	$file_version=explode(':',$matches[0]);
	$file_version=$file_version[1];
	$file_version=str_replace('<br />','',$file_version);
	$file_version=str_replace(' ','',$file_version);
	$file_version = str_replace("\t", '', $file_version); // remove tabs
	$file_version = str_replace("\r", '', $file_version); // remove carriage returns
	
	if($rparameters['version']==$file_version)
	{
		//check if lastest version is installed
		$need_update=0;
		$local_version_array=explode('.',$rparameters['version']);
		$local_version1=$local_version_array[0];
		$local_version2=$local_version_array[1];
		$local_version3=$local_version_array[2];

		if($rparameters['last_version'])
		{
			$remote_version_array=explode('.',$rparameters['last_version']);
			$remote_version1=$remote_version_array[0];
			$remote_version2=$remote_version_array[1];
			$remote_version3=$remote_version_array[2];

			if($remote_version1>$local_version1) {$need_update=1;} 
			elseif($remote_version2>$local_version2){$need_update=1;}
			elseif($remote_version3>$local_version3+2){$need_update=1;} 
		} else {
			$need_update=0;
		}
		//dedicated no check
		if(substr_count($rparameters['version'], '.')==3){$need_update=0;}

		if($need_update)
		{
			if($_GET['subpage']=='system')
			{
				$gestsup_version='<i style="width:20px;" title="'.T_("Votre application est obsolète, merci de la mettre à jour en ").' '.$rparameters['last_version'].'" class="fa fa-exclamation-triangle text-danger"><!----></i>';
				$gestsup_version_text=' <i>('.T_("Votre application est obsolète merci d'installer la dernière version").' '.$rparameters['last_version'].')<!----></i>';
				$system_error=1;
			} else {
				$gestsup_version='<i style="width:20px;" class="fa fa-ticket text-success"><!----></i>';
			}
		} else {
			$gestsup_version='<i style="width:20px;" class="fa fa-ticket text-success"><!----></i>';
			if($_GET['subpage']=='system')
			{
				//count number of ticket
				$qry=$db->prepare("SELECT COUNT(`id`) AS counter FROM `tincidents` WHERE `disable`='0'");
				$qry->execute();
				$ticket=$qry->fetch();
				$qry->closeCursor();
				//count number of asset
				$qry=$db->prepare("SELECT COUNT(`id`) AS counter FROM `tassets` WHERE `disable`='0'");
				$qry->execute();
				$asset=$qry->fetch();
				$qry->closeCursor();
				//count number of user
				$qry=$db->prepare("SELECT COUNT(`id`) AS counter FROM `tusers` WHERE `disable`='0'");
				$qry->execute();
				$user=$qry->fetch();
				$qry->closeCursor();
				$gestsup_version_text=' <span class="text-sm"><i>('.$ticket['counter'].' '.T_('tickets').' / '.$user['counter'].' '.T_('utilisateurs').' / '.$asset['counter'].' '.T_('équipements').')<!----></i></span>';
			} else {
				$gestsup_version_text='';
			}
		}
	} else {
		$gestsup_version='<i style="width:20px;" title="'.T_("Une incohérence de version de l'application à été détectée, entre votre base de données et vos fichiers").'" class="fa fa-exclamation-triangle text-danger"><!----></i>';
		$gestsup_version_text=' <i>('.T_("Une incohérence de version de l'application à été détectée, entre votre base de données").' v'.$rparameters['version'].' '.T_("et vos fichiers").' v'.$file_version.', '.T_("vérifier votre méthode d'installation des mises à jours").')<!----></i>';
		$system_error=1;
	}
}

function folderSize ($dir)
{
    $size = 0;
    foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
        $size += is_file($each) ? filesize($each) : folderSize($each);
    }
    return $size;
}
$upload_size=round(((folderSize('upload')/1024)/1024),2).'Mo';
$total_size=$db_size+round(((folderSize('upload')/1024)/1024),2);
?>
<div class="mt-4 mt-lg-0 card bcard h-auto overflow-hidden shadow border-0">
    <div class="card-body p-0 table-responsive-xl">
		<table class="table text-dark-m1 brc-black-tp10 mb-0">
			<tbody>
				<tr>
					<td style="width: 150px;" class="text-95 text-default-d3 bgc-secondary-l4"><i class="fa fa-server text-blue-m3 pr-1"><!----></i><?php echo T_('Serveur'); ?></td>
					<td class="text-95 text-default-d3">
						<span id="server">
							<?php
								$os_comment='';
								$os_img=strtolower($os).'_ok.png';
								//check OS
								if($os=='Windows') {
									$system_warning=1;
									$os_comment=' <span class="text-sm"><i>('.T_('les environnements Windows ne sont pas recommandés pour les serveurs de production').')<!----></i></span>';
								} 
								if(preg_match('/Debian 5./',$phpinfo['phpinfo']['System']))  {$system_warning=1; $os_comment=' <span class="text-sm"><i>('.T_("Votre système d'exploitation est obsolète").')<!----></i></span>'; $os_img=strtolower($os).'_ko.png';} 
								if(preg_match('/Debian 4./',$phpinfo['phpinfo']['System']))  {$system_warning=1; $os_comment=' <span class="text-sm"><i>('.T_("Votre système d'exploitation est obsolète").')<!----></i></span>'; $os_img=strtolower($os).'_ko.png';} 
								if(preg_match('/Debian 3./',$phpinfo['phpinfo']['System']))  {$system_warning=1; $os_comment=' <span class="text-sm"><i>('.T_("Votre système d'exploitation est obsolète").')<!----></i></span>'; $os_img=strtolower($os).'_ko.png';} 
								if(preg_match('/Debian 2./',$phpinfo['phpinfo']['System']))  {$system_warning=1; $os_comment=' <span class="text-sm"><i>('.T_("Votre système d'exploitation est obsolète").')<!----></i></span>'; $os_img=strtolower($os).'_ko.png';} 
								if(preg_match('/Debian 1./',$phpinfo['phpinfo']['System']))  {$system_warning=1; $os_comment=' <span class="text-sm"><i>('.T_("Votre système d'exploitation est obsolète").')<!----></i></span>'; $os_img=strtolower($os).'_ko.png';} 
								if(preg_match('/Debian/',$phpinfo['phpinfo']['System']))  {
									//check date
									$debian_date=explode('(',$phpinfo['phpinfo']['System']);
									$debian_date=explode(')',$debian_date[1]);
									$debian_date=$debian_date[0];

									$origin = new DateTime($debian_date);
									$target = new DateTime(date('Y-m-d'));
									$interval = $origin->diff($target);
									$release_day_old=$interval->format('%a');

									if($release_day_old>365) {
										$system_warning=1;
										$os_comment=' <i>('.T_("Votre système d'exploitation est obsolète, dernière mise à jour").' '.$release_day_old.' '.T_('jours').')</i>'; $os_img=strtolower($os).'_ko.png';
									}
								} 
								//check distrib 
								if($os=='Linux')
								{
									if(!preg_match('/Debian/',$phpinfo['phpinfo']['System']))
									{
										$system_warning=1;
										$os_comment=' <i>('.T_("Votre distribution n'est pas compatible, migrer vers Debian").')</i>';
										$os_img=strtolower($os).'_ko.png';
									}
								}
							?>
							<img src="./images/<?php echo $os_img; ?>" style="border-style: none; margin-right:2px;" alt="img" /> 
							<?php echo "<b>OS :</b> {$phpinfo['phpinfo']['System']}".$os_comment; ?>
							<br />
							<?php
								//display total RAM
								$total_memory='';
								if($os=='Windows')
								{
									exec('wmic memorychip get capacity', $total_memory);
									if(isset($total_memory[1])){$total_memory=$total_memory[1];}
								} else {
									if(file_exists('/proc/meminfo'))
									{
										$fh = fopen('/proc/meminfo','r');
										$total_memory = 0;
										while ($line = fgets($fh)) {
											$pieces = array();
											if (preg_match('/^MemTotal:\s+(\d+)\skB$/', $line, $pieces)) {
												$total_memory = $pieces[1];
												break;
											}
										}
										fclose($fh);
										if(isset($total_memory))
										{
											$total_memory=$total_memory*1024;
											$total_memory=$total_memory;
										}
									} else {$total_memory=0;}
								}
								if($total_memory && $rparameters['debug'])
								{
									if($total_memory>=4294967296)
									{
										echo '<i style="width:19px;" class="fa fa-memory text-success"><!----></i>  <b>'.T_('RAM').' :</b> '.GetSizeName($total_memory).' '.T_('total');;
										echo '<br />';
									} else {
										$system_error=1;
										echo '<i style="width:19px;" class="fa fa-memory text-danger"><!----></i>  <b>'.T_('RAM').' :</b> '.GetSizeName($total_memory);
										echo ' <i>('.T_('votre serveur ne dispose pas de suffisamment de RAM minimum 4Go').')</i>';
										echo '<br />';
									}
								}
								
								//display disk free space
								$disk_free_space='';
								if($os=='Windows'){$disk_free_space = disk_free_space("C:");} else {$disk_free_space = disk_free_space("/");}
								if($disk_free_space)
								{
									if($disk_free_space<=1073741824)
									{
										$system_error=1;
										echo '<i style="width:19px;" class="fa fa-hard-drive text-danger"><!----></i>  <b>'.T_('HDD').' :</b> '.GetSizeName($disk_free_space);
										echo ' <i>('.T_("l'espace disque restant sur votre serveur est inférieur à 1GB ajouter de l'espace disque").')</i>';
										echo '<br />';
									}
								}
								
								//display public ip
								if(CheckConnection('ipv4.lafibre.info','443')) {
									$url = 'https://ipv4.lafibre.info/ip.php';
        							$curl = curl_init($url);
									curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
									curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
									curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
									if($rparameters['server_proxy_url']) {curl_setopt($curl, CURLOPT_PROXY, $rparameters['server_proxy_url']);}
									$public_ip_v4 = curl_exec($curl);
									if(curl_error($curl)) {$public_ip_v4 = "";}
									curl_close($curl);
								} else {$public_ip_v4 ='';}

								if(CheckConnection('ipv6.lafibre.info','443')) {
									$url = 'https://ipv6.lafibre.info/ip.php';
        							$curl = curl_init($url);
									curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
									curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
									curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
									if($rparameters['server_proxy_url']) {curl_setopt($curl, CURLOPT_PROXY, $rparameters['server_proxy_url']);}
									$public_ip_v6 = curl_exec($curl);
									if(curl_error($curl)) {$public_ip_v6 = "";}
									curl_close($curl);
								} else {$public_ip_v6 ='';}
								
								if($public_ip_v4 || $public_ip_v6)
								{
									echo '<i style="width:19px;" class="fa fa-globe text-success"><!----></i>  <b>'.T_('IP publique').' :</b> '.$public_ip_v4;
									if($public_ip_v6) {echo ' / '.$public_ip_v6;}
									echo '<br />';
								}
								
								//display apache version
								echo '<img src="./images/'.$web_server_icon.'" style="border-style: none; margin-right:2px;" alt="img" /> <b>'.ucfirst($web_server_name).' :</b> '.$web_server_version.' <span style="font-size:12px"><i>('.T_('en').' '.$_SERVER['SERVER_PROTOCOL'].' '.T_('sur').' '.$_SERVER['HTTP_HOST'].')<!----></i></span><br />';
							?>
							<img style="width:20px;" src="./images/<?php echo strtolower($rdb_name).'_'.$rdb_icon.'.png'; ?>" alt="img" /> 
							<?php 
								echo '<b>'.$rdb_name.' :</b> '.$rdb_version;
								echo '<span class="text-sm font-italic"> (';
								if(strtolower($rdb_name)=='mysql') {echo T_("Il est recommandé de migrer sur MariaDB, ");}
								echo T_('base').' : '.$db_name.' '.$db_size.'Mo)<!----></i><br /></span>'; 
					
								//check php version https://www.php.net/supported-versions.php
								$php_version=phpversion();
								if($php_version<'7.4.0' && date('Y-m-d')>='2021-12-06') {
								echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>PHP :</b>  '.T_('Votre version de PHP ').phpversion().T_(' est obsolète et incompatible consulter la documentation').' (<a target="about_blank" href="https://www.php.net/supported-versions.php">https://www.php.net/supported-versions.php</a>)';
									$system_error=1;
								}elseif(($php_version<'8.0.2') && date('Y-m-d')>='2022-11-28') { 
									echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>PHP :</b>  '.T_('Votre version de PHP ').phpversion().T_(' est obsolète et incompatible consulter la documentation').' (<a target="about_blank" href="https://www.php.net/supported-versions.php">https://www.php.net/supported-versions.php</a>)';
									$system_error=1;
								}elseif($php_version<'8.1.0' && date('Y-m-d')>='2023-11-26') {
									echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>PHP :</b>  '.T_('Votre version de PHP ').phpversion().T_(' est obsolète consulter la documentation').' (<a target="about_blank" href="https://www.php.net/supported-versions.php">https://www.php.net/supported-versions.php</a>)';
									$system_error=1;
								}elseif($php_version<'8.2.0' && date('Y-m-d')>='2024-11-25') {
									echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>PHP :</b>  '.T_('Votre version de PHP ').phpversion().T_(' est obsolète consulter la documentation').' (<a target="about_blank" href="https://www.php.net/supported-versions.php">https://www.php.net/supported-versions.php</a>)';
									$system_error=1;
								}else{
									echo '<img class="pr-1" src="./images/php_ok.png" alt="img" /> <b>PHP :</b> '.phpversion().' <span class="text-sm font-italic">('.php_ini_loaded_file().')</span>';
								}
							?>  
							<br />
							<?php echo $gestsup_version; ?> <b><?php echo T_('GestSup'); ?> :</b> <?php echo $rparameters['version'].$gestsup_version_text; ?><br />
							<i style="width:16px;" class="fa fa-clock text-success"><!----></i> &nbsp;<b><?php echo T_('Horloge'); ?> :</b> <?php echo date('Y-m-d H:i:s').' <span class="text-sm"><i>('.date_default_timezone_get().')<!----></i></span>'; ?> <br />
							<i style="width:16px;" class="fa fa-hdd text-success"><!----></i> &nbsp;<b><?php echo T_('Fichiers chargés'); ?> :</b> <?php echo $upload_size; ?> <span class="text-sm"><i><?php echo '('.T_('total').' '.$total_size.'Mo)'; ?></i></span><br />
							<i style="width:19px;" class="fa fa-key text-success"><!----></i> 
						</span>
						<b><?php echo T_('Clé privée'); ?> :</b> 
						<button id="btn_display_key" class="btn btn-xs btn-info"><?php echo T_('Afficher');?> </button>
						<button id="btn_hide_key" class="btn btn-xs btn-info"><?php echo T_('Masquer');?> </button>
						<span id="private_key"><?php echo $rparameters['server_private_key']; ?> <i><?php echo T_("(Clé à ne pas divulguer)"); ?><!----></i></span>
						<script>
							//default hide
							$("#btn_hide_key").addClass("d-none");
							$("#private_key").addClass("d-none");
							//display key
							$("#btn_display_key").on("click", function(e, m) {
								$("#btn_hide_key").removeClass("d-none");
								$("#private_key").removeClass("d-none");
								$("#btn_display_key").addClass("d-none");
							});
							//hide key
							$("#btn_hide_key").on("click", function(e, m) {
								$("#btn_hide_key").addClass("d-none");
								$("#private_key").addClass("d-none");
								$("#btn_display_key").removeClass("d-none");
							});
						</script>
					</td>
				</tr>
				<tr>
					<td style="width: 150px;" class="text-95 text-default-d3 bgc-secondary-l4"><i class="fa fa-desktop text-blue-m3 pr-1"><!----></i><?php echo T_('Client'); ?> </td>
					<td class="text-95 text-default-d3">
						<span id="client">
							<i class="fa fa-mobile text-success mr-1"><!----></i> <b><?php echo T_('Mobile'); ?> :</b> <?php if($mobile) {echo T_('Oui');} else {echo T_('Non');} ?><br />
							<i class="fa fa-window-maximize text-success"><!----></i> <b><?php echo T_('Navigateur'); ?> :</b> <?php echo $_SERVER['HTTP_USER_AGENT']; ?><br />
							
							<i class="fa fa-globe text-success"><!----></i> <!----></i> <b><?php if(strstr($_SERVER['REMOTE_ADDR'],':')) {echo 'IPv6';} else {echo 'IPv4';}  ?> :</b> <?php echo $_SERVER['REMOTE_ADDR']; ?><br />
						</span>
					</td>
				</tr>
				<tr>
					<td style="width: 150px;" class="text-95 text-default-d3 bgc-secondary-l4"><i class="fa fa-exchange-alt text-blue-m3 pr-1"><!----></i><?php echo T_('Réseau'); ?> </td>
					<td class="text-95 text-default-d3">
						<span id="network">
						<?php
							if($rparameters['mail'] && $rparameters['mail_smtp'] && $rparameters['mail_port'])
							{
								if(CheckConnection($rparameters['mail_smtp'],$rparameters['mail_port']))
								{
									echo '<i class="fa fa-check-circle text-success"><!----></i> <b>'.T_('SMTP').' :</b> '.T_('Ouvert').' ('.$rparameters['mail_smtp'].':'.$rparameters['mail_port'].')  <br />';
								} else {
									$system_error=1;
									echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>'.T_('SMTP').' :</b> '.T_('Fermé').' ('.$rparameters['mail_smtp'].':'.$rparameters['mail_port'].')  <br />';
								}
							}
							if($rparameters['imap'] && $rparameters['imap_server'] && $rparameters['imap_port'])
							{
								if($rparameters['imap_port']=='993/imap/ssl') {$rparameters['imap_port']='993';};
								if(CheckConnection($rparameters['imap_server'],$rparameters['imap_port']))
								{
									echo '<i class="fa fa-check-circle text-success"><!----></i> <b>'.T_('IMAP').' :</b> '.T_('Ouvert').' ('.$rparameters['imap_server'].':'.$rparameters['imap_port'].')  <br />';
								} else {
									$system_error=1;
									echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>'.T_('IMAP').' :</b> '.T_('Fermé').' ('.$rparameters['imap_server'].':'.$rparameters['imap_port'].')  <br />';
								}
							}
							if($rparameters['ldap'] && $rparameters['ldap_server'] && $rparameters['ldap_port'])
							{
								if(CheckConnection($rparameters['ldap_server'],$rparameters['ldap_port']))
								{
									echo '<i class="fa fa-check-circle text-success"><!----></i> <b>'.T_('LDAP').' :</b> '.T_('Ouvert').' ('.$rparameters['ldap_server'].':'.$rparameters['ldap_port'].')  <br />';
								} else {
									$system_error=1;
									echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>'.T_('LDAP').' :</b> '.T_('Fermé').' ('.$rparameters['ldap_server'].':'.$rparameters['ldap_port'].')  <br />';
								}
							}
							if($rparameters['mail'] && $rparameters['mail_auth_type']=='oauth_azure')
							{
								if(CheckConnection('login.microsoftonline.com','443'))
								{
									echo '<i class="fa fa-check-circle text-success"><!----></i> <b>'.T_('OAUTH2 Azure').' :</b> '.T_('Ouvert').' (login.microsoftonline.com:443)  <br />';
								} else {
									$system_error=1;
									echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>'.T_('OAUTH2 Azure').' :</b> '.T_('Fermé').' (login.microsoftonline.com:443)  <br />';
								}
							}
							if(CheckConnection('gestsup.fr',443))
							{
								echo '<i class="fa fa-check-circle text-success"><!----></i> <b>'.T_('HTTPS').' :</b> '.T_('Ouvert').' (gestsup.fr:443)  <br />';
							} else {
								$system_danger=1;
								echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>'.T_('HTTPS').' :</b> '.T_('Fermé').' (gestsup.fr:443)  <br />';
							}
							
						?>
						</span>
					</td>
				</tr>
				<?php 
					//check configuration
					$conf_error='';

					//check write
					if(!is_writable('./upload/ticket/index.htm') && is_dir('./upload/ticket')){$system_error=1; $conf_error.='<i class="fa fa-times-circle text-danger"><!----></i> <b>'.T_("Droit d'écriture").' : </b> '.T_('Répertoire').' "upload/ticket" '.T_('verrouillé').' <i>('.T_("Ajouter les droits d'écriture sur ce repertoire, afin de pouvoir charger des pièces jointes à un ticket").').<!----></i><br />';}
					if(!is_writable('./upload/logo/index.htm') && is_dir('./upload/logo')){$system_error=1; $conf_error.='<i class="fa fa-times-circle text-danger"><!----></i> <b>'.T_("Droit d'écriture").' : </b> '.T_('Répertoire').' "upload/logo" '.T_('verrouillé').' <i>('.T_("Ajouter les droits d'écriture sur ce repertoire, afin de pouvoir charger un logo").').<!----></i><br />';}
					if(!is_writable('./upload/procedure/index.htm') && is_dir('./upload/procedure')){$system_error=1; $conf_error.='<i class="fa fa-times-circle text-danger"><!----></i> <b>'.T_("Droit d'écriture").' : </b> '.T_('Répertoire').' "upload/procedure" '.T_('verrouillé').' <i>('.T_("Ajouter les droits d'écriture sur ce repertoire, afin de pouvoir charger des pièces jointes à une procédure").').<!----></i><br />';}
					if(!is_writable('./upload/asset/index.htm') && is_dir('./upload/asset')){$system_error=1; $conf_error.='<i class="fa fa-times-circle text-danger"><!----></i> <b>'.T_("Droit d'écriture").' : </b> '.T_('Répertoire').' "upload/asset" '.T_('verrouillé').' <i>('.T_("Ajouter les droits d'écriture sur ce repertoire, afin de pouvoir charger une image de modèle d'équipement").').<!----></i><br />';}
					if(isset($rright))
					{
						if($rright['admin_backup'] && !is_writable('./_SQL/index.htm') && is_dir('./_SQL')){$system_error=1; $conf_error.='<i class="fa fa-times-circle text-danger"><!----></i> <b>'.T_("Droit d'écriture").' : </b> '.T_('Répertoire').' "_SQL" '.T_('verrouillé').' <i>('.T_("Ajouter les droits d'écriture sur ce répertoire, afin de pouvoir utiliser la fonction sauvegarde").').<!----></i><br />';}
						if($rright['admin_backup'] && !is_writable('./backup/index.htm') && is_dir('./backup')){$system_error=1; $conf_error.='<i class="fa fa-times-circle text-danger"><!----></i> <b>'.T_("Droit d'écriture").' : </b> '.T_('Répertoire').' "backup" '.T_('verrouillé').' <i>('.T_("Ajouter les droits d'écriture sur ce répertoire, afin de pouvoir utiliser la fonction sauvegarde").').<!----></i><br />';}
					}
					
					//check SMTP configuration
					if($rparameters['mail'] && $rparameters['mail_smtp'] && $rparameters['mail_username'] && $rparameters['mail_username']!=$rparameters['mail_from_adr']){$system_warning=1; $conf_error.='<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>'.T_("Connecteur SMTP").' : </b> '.T_("L'adresse mail de l'émetteur configurée dans les paramètres généraux, est différente de l'adresse mail configurée sur votre connecteur SMTP, vos mails peuvent être considérés en tant que SPAM.").'<br />';}
					if(!$rparameters['mail'] && ($rparameters['mail_auto'] || $rparameters['mail_auto_user_newticket'] ||$rparameters['mail_auto_user_modify'] ||$rparameters['mail_auto_tech_modify'] ||$rparameters['mail_auto_tech_attribution'] ||$rparameters['mail_newticket'])){$system_warning=1; $conf_error.='<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>'.T_("Connecteur SMTP").' : </b> '.T_("Des envois de mails automatiques sont configurés, alors que votre connecteur SMTP est désactivé").'<br />';}
					
					//check LDAP configuration
					if($rparameters['ldap'] && (preg_match('/DC=/',$rparameters['ldap_url']) || preg_match('/dc=/',$rparameters['ldap_url']))){$system_warning=1; $conf_error.='<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>'.T_("Connecteur LDAP").' : </b> '.T_("L'emplacement des utilisateurs ne doit pas contenir dc=, cf documentation.").'<br />';}

					//check number of lines per page parameter
					if($rparameters['maxline']>50){$system_warning=1; $conf_error.='<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>'.T_("Nombre de lignes par page").' : </b> '.T_("Le nombre de lignes par page est élevé (").$rparameters['maxline'].'), '.T_("cela peut entraîner une baisse des performances de l'application, réduisez le paramètre général \"Nombre de lignes par page\" à une valeur inférieur à 50").' <br />';}
					
					//check server url parameter
					if($_GET['page']=='admin')
					{
						$current_url="$http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
						$current_url=str_replace('/index.php?page=admin&subpage=system','',$current_url);
						if($rparameters['server_url']!=$current_url){$system_warning=1; $conf_error.='<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>'.T_("URL serveur").' : </b> '.T_("Il semble que l'URL du serveur définie dans les paramètres généraux soit erronée").' ('.$rparameters['server_url'].')<br />';}
					}

					//check company name
					if($_GET['page']=='admin')
					{
						if($rparameters['company']=='Societe'){$system_warning=1; $conf_error.='<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>'.T_("Nom de l'entreprise").' : </b> '.T_("Le nom de votre entreprise n'ai pas encore été changé, modifier le paramètre Administration > Paramètres > > Général > Société > Nom de l'entreprise").' <br />';}
					}
					
					//check telemetry
					if(!$rparameters['telemetry'] && !$install){$system_warning=1; $conf_error.='<i class="fa fa-info-circle text-warning"><!----></i> <b>'.T_("Télémétrie").' : </b> '.T_("Désactivée, activer la télémétrie dans Administration > Paramètres > Général > Serveur").'<br />';}
	
					if($conf_error)
					{
						echo '
						<tr>
							<td style="width: 150px;" class="text-95 text-default-d3 bgc-secondary-l4"><i class="fa fa-cog text-blue-m3 pr-1"><!----></i>'.T_('Configuration').'</td>
							<td class="text-95 text-default-d3">
								<span id="conf_error">
									'.$conf_error.'
								</span>
							</td>
						</tr>
						';
					}
				?>
				<tr>
					<td style="width:180px;" class="text-95 text-default-d3 bgc-secondary-l4"><i class="fa fa-cog text-blue-m3 pr-1"><!----></i><?php echo T_('Paramètres PHP'); ?> </td>
					<td class="text-95 text-default-d3">
						<span id="php_parameters">
							<?php
							if($phpinfo[$vphp]['file_uploads'][0]=="On") {
								echo '<i class="fa fa-check-circle text-success"><!----></i> <b>file_uploads</b> : '.T_('Activé').'<br />';
							} else {
								$system_error=1;
								echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>file_uploads :</b> '.T_('Désactivé').' <i>('.T_('Le chargement de fichiers sera impossible').')<!----></i><br />';
							}
							if($ram_mb>=512) {
								echo '<i class="fa fa-check-circle text-success"><!----></i> <b>memory_limit :</b> '.$phpinfo[$vphp]['memory_limit'][0].'<br />';
							} else {
								$system_warning=1;
								echo '<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>memory_limit :</b> '.$phpinfo[$vphp]['memory_limit'][0].' <i>('.T_("Il est préconisé d'allouer plus de mémoire pour PHP valeur minimum 512M éditer votre fichier php.ini").')<!----></i>.<br />';
							}
							if($max_filesize_mb>=5) {
								echo '<i class="fa fa-check-circle text-success"><!----></i> <b>upload_max_filesize :</b> '.$max_filesize_mb.'M<br />'; 
							} else {
								$system_warning=1;
								echo '<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>upload_max_filesize : </b>'.$max_filesize_mb.'M <i> ('.T_("Il est préconisé d'avoir une valeur supérieur ou égale à 5Mo, afin d'attacher des pièces jointes volumineuses").')<!----></i>.<br />';
							}
							if($post_max_size_mb>=5) {
								echo '<i class="fa fa-check-circle text-success"><!----></i> <b>post_max_size :</b> '.$post_max_size_mb.'M <br />';
							} else {
								$system_warning=1;
								echo '<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>post_max_size : </b>'.$post_max_size_mb.'M <i> ('.T_("Il est préconisé d'avoir une valeur supérieur ou égale à 5Mo, afin d'attacher des pièces jointes volumineuses").')<!----></i>.<br />';
							}
							if($phpinfo[$vphp]['max_execution_time'][0]>="480") {
								echo '<i class="fa fa-check-circle text-success"><!----></i> <b>max_execution_time :</b> '.$phpinfo[$vphp]['max_execution_time'][0].'s<br />'; 
							} else {
								$system_warning=1;
								echo '<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>max_execution_time : </b>'.$phpinfo[$vphp]['max_execution_time'][0].'s <i>('.T_('Valeur conseillé 480s, modifier votre php.ini relancer apache et actualiser cette page').'.)<!----></i><br />';
							}
							if($phpinfo['date']['date.timezone'][0]!='UTC' || $rparameters['server_timezone']) {
								echo '<i class="fa fa-check-circle text-success"><!----></i> <b>date.timezone :</b> '.date_default_timezone_get().'<br />';
							} else {
								$system_warning=1;
								echo '<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>date.timezone :</b> '.date_default_timezone_get().' <i>('.T_("Il est préconisé de modifier la valeur date.timezone du fichier php.ini, et mettre Europe/Paris afin de ne pas avoir de problème d'horloge").'.)<!----></i><br />';
							}
							?>
						</span>
					</td>
				</tr>
				<tr>
					<td style="width:180px;" class="text-95 text-default-d3 bgc-secondary-l4"><i class="fa fa-cog text-blue-m3 pr-1"><!----></i><?php echo T_('Paramètres MariaDB'); ?> </td>
					<td class="text-95 text-default-d3">
						<span id="mariadb_parameters">
							<?php
							if($innodb_buffer_pool_size>=($total_memory/4)) {
								echo '<i class="fa fa-check-circle text-success"><!----></i> <b>innodb_buffer_pool_size :</b> '.GetSizeName($innodb_buffer_pool_size).'<br />';
							} else {
								$system_warning=1;
								echo '<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>innodb_buffer_pool_size :</b> '.GetSizeName($innodb_buffer_pool_size).' <i>('.T_("Il est préconisé d'allouer au moins ").(GetSizeName($total_memory/2)).T_(", modifier le fichier de configuration MariaDB /etc/mysql/mariadb.conf.d/50-server.cnf").')<!----></i>.<br />';
							}
							?>
						</span>
					</td>
				</tr>
				<tr>
					<td style="width:180px;" class="text-95 text-default-d3 bgc-secondary-l4"><i class="fa fa-puzzle-piece text-blue-m3 pr-1"><!----></i><?php echo T_('Extensions PHP'); ?> </td>
					<td class="text-95 text-default-d3">
						<span id="php_extensions">
							<?php
							if(extension_loaded('curl')) {echo '<i class="fa fa-check-circle text-success"><!----></i> <b>php_curl :</b> '.T_('Activée');} else {$system_error=1; echo '<i class="fa fa-exclamation-triangle text-danger"><!----></i> <b>php_curl</b> : '.T_("Désactivée").' <i>('.T_("le contrôle de sécurité sur le listing des répertoires ainsi que le contrôle de version de l'application ne fonctionneront pas. apt-get install php-curl").')<!----></i>';}
							echo "<br />";
							if(extension_loaded('fileinfo')) {echo '<i class="fa fa-check-circle text-success"><!----></i> <b>php_fileinfo :</b> '.T_('Activée');} else {$system_error=1; echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>php_fileinfo</b> : '.T_("Désactivée").' <i>('.T_("le connecteur IMAP ne fonctionnera pas. apt install php-fileinfo").')<!----></i>';}
							echo "<br />";
							if(extension_loaded('gd')) {echo '<i class="fa fa-check-circle text-success"><!----></i> <b>php_gd :</b> '.T_('Activée');} else {$system_error=1; echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>php_gd</b> : '.T_("Désactivée").' <i>('.T_("la confirmation visuelle lors de l'enregistrement d'un utilisateur ne fonctionnera pas. apt install php-gd").')<!----></i>';}
							echo "<br />";
							if(extension_loaded('iconv')) {echo '<i class="fa fa-check-circle text-success"><!----></i> <b>php_iconv :</b> '.T_('Activée');} else {$system_error=1; echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>php_iconv</b> : '.T_("Désactivée").' <i>('.T_("le connecteur IMAP ne fonctionnera pas. apt install php-iconv").')<!----></i>';}
							echo "<br />";
							if(extension_loaded('imap')) {echo '<i class="fa fa-check-circle text-success"><!----></i> <b>php_imap :</b> '.T_('Activée');} else {$system_error=1; echo '<i class="fa fa-exclamation-triangle text-danger"><!----></i> <b>php_imap</b> : '.T_("Désactivée").' <i>('.T_("la fonction Mail2Ticket ne fonctionnera pas. apt-get install php-imap").')<!----></i>';}
							echo "<br />";
							if(extension_loaded('intl')) {echo '<i class="fa fa-check-circle text-success"><!----></i> <b>php_intl :</b> '.T_('Activée');} else {$system_error=1; echo '<i class="fa fa-exclamation-triangle text-danger"><!----></i> <b>php_intl</b> : '.T_("Désactivée").' <i>('.T_("apt-get install php-intl").')<!----></i>';}
							echo "<br />";	
							if(extension_loaded('json')) {echo '<i class="fa fa-check-circle text-success"><!----></i> <b>php_json :</b> '.T_('Activée');} else {$system_error=1; echo '<i class="fa fa-exclamation-triangle text-danger"><!----></i> <b>php_json</b> : '.T_("Désactivée").' <i>('.T_("la fonction Mail2Ticket ne fonctionnera pas. apt-get install php-json").')<!----></i>';}
							echo "<br />";
							if(extension_loaded('ldap')) {echo '<i class="fa fa-check-circle text-success"><!----></i> <b>php_ldap :</b> '.T_('Activée');} else {echo '<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>php_ldap</b> : '.T_("Désactivée").' <i>('.T_("aucune synchronisation ni authentification via un serveur LDAP ne sera possible. apt-get install php-ldap").')<!----></i>';}
							echo "<br />";
							if(extension_loaded('mbstring')) {echo '<i class="fa fa-check-circle text-success"><!----></i> <b>php_mbstring :</b> '.T_('Activée');} else {$system_error=1; echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>php_mbstring</b> : '.T_("Désactivée").' <i>('.T_("des erreurs sont possibles dans la liste des tickets et sur le connecteur IMAP. apt install php-mbstring").')<!----></i>';}
							echo "<br />";
							if(extension_loaded('openssl')) {echo '<i class="fa fa-check-circle text-success"><!----></i> <b>php_openssl :</b> '.T_('Activée');} else {$system_error=1; echo '<i class="fa fa-exclamation-triangle text-danger"><!----></i> <b>php_openssl</b> : '.T_("Désactivée").' <i>('.T_("si vous utilisez un serveur SMTP sécurisé les mails ne seront pas envoyés. apt-get install openssl").')<!----></i>';}
							echo "<br />";
							if(extension_loaded('pdo_mysql')) {echo '<i class="fa fa-check-circle text-success"><!----></i> <b>php_pdo_mysql :</b> '.T_('Activée');} else {$system_error=1; echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>php_pdo_mysql</b> : '.T_("Désactivée").' <i>('.T_("l'interconnexion de base de données ne pourra être disponible").')<!----></i>';}
							echo "<br />";
							if(extension_loaded('xml')) {echo '<i class="fa fa-check-circle text-success"><!----></i> <b>php_xml :</b> '.T_('Activée');} else {$system_error=1; echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>php_xml</b> : '.T_("Désactivée").' <i>('.T_("le connecteur LDAP ne fonctionnera pas. apt-get install php-xml").')<!----></i>';}
							echo "<br />";
							if(extension_loaded('zip')) {echo '<i class="fa fa-check-circle text-success"><!----></i> <b>php_zip :</b> '.T_('Activée');} else {$system_error=1; echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>php_zip</b> : '.T_("Désactivée").' <i>('.T_("la fonction de mise à jour automatique ne sera pas possible").')<!----></i>';}
							?>
						</span>
					</td>
				</tr>
				<?php
					//check existing plugin before display section
					$qry=$db->prepare("SELECT COUNT(`id`) FROM `tplugins` WHERE `enable`='1'");
					$qry->execute();
					$row=$qry->fetch();
					$qry->closeCursor();

					if($row['0']>0)
					{
						echo '
						<tr>
							<td style="width:180px;" class="text-95 text-default-d3 bgc-secondary-l4"><i class="fa fa-puzzle-piece text-blue-m3 pr-1"><!----></i>'.T_('Plugins').'</td>
							<td class="text-95 text-default-d3">
								<span id="plugins">
								';
									$qry=$db->prepare("SELECT `label`,`version` FROM `tplugins` WHERE `enable`=1 ORDER BY `name`");
									$qry->execute();
									while($plugin=$qry->fetch()) 
									{
										echo '<i class="fa fa-check-circle text-success"><!----></i> <b>'.$plugin['label'].' :</b> '.$plugin['version'].'<br />';
									}
									$qry->closeCursor();
								
									echo '
								</span>
							</td>
						</tr>
						';
					} 
				?>
				

				<tr>
					<td style="width:180px;" class="text-95 text-default-d3 bgc-secondary-l4"><i class="fa fa-shield-alt text-blue-m3 pr-1"><!----></i><?php echo T_('Sécurité'); ?> </td>
					<td class="text-95 text-default-d3">
						<span id="security">
							<?php
							if($http=="https") 
							{
								echo '<i class="fa fa-check-circle text-success"><!----></i> <b>HTTPS : </b>'.T_('Activée');
							} else {
								$system_error=1;
								echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>HTTPS : </b>'.T_("Désactivé").' <span class="text-sm"><i>('.T_("les connexions vers le serveur ne sont pas chiffrées, installer un certificat SSL").' <a target="_blank" href="https://certbot.eff.org"> '.T_("installer un certificat SSL").'</a>.)<!----></i></span>';
							}
							echo "<br />";
							if($web_server_name=='apache') 
							{
								if($apache_display_version==0)
								{
									echo '<i class="fa fa-check-circle text-success"><!----></i> <b>'.T_('Version Apache').' : </b>'.T_('Non affichée');
								} else {
									$system_warning=1;
									echo '<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>'.T_('Version Apache').' : </b>'.T_("Affichée").' <span class="text-sm"><i>('.T_("pour plus de sécurité, masquez la version d'apache que vous utilisez. Passer \"ServerTokens\" à \"Prod\" dans security.conf").'.)<!----></i></span>';
								}	
								echo "<br />";
							}
							
							if($phpinfo[$vphp]['expose_php'][0]=='Off')
							{
								echo '<i class="fa fa-check-circle text-success"><!----></i> <b>'.T_('Version PHP').' : </b>'.T_('Non affichée');
							} else {
								$system_warning=1;
								echo '<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>'.T_('Version PHP').' : </b>'.T_("Affichée").' <span class="text-sm"><i>('.T_("pour plus de sécurité, masquez la version de PHP que vous utilisez. Passer le paramètre \"expose_php\" à  \"Off\" dans le php.ini").'.)<!----></i></span>';
							}	
							echo "<br />";
							
							if($maxlifetime<=1440 || $rparameters['timeout']<=24) 
							{
								echo '<i class="fa fa-check-circle text-success"><!----></i> <b>'.T_('Durée de la session').' : </b> PHP='.$maxlifetime.'s GestSup='.$rparameters['timeout'].'m';
							} else {
								$system_warning=1;
								echo '<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>'.T_('Durée de la session').' : </b> PHP='.$maxlifetime.'s GestSup='.$rparameters['timeout'].'m <span class="text-sm"><i>('.T_("pour plus de sécurité, diminuez la durée à 24m minimum, paramètre \"session.gc_maxlifetime\" du php.ini et paramètre GestSup.").')<!----></i></span>';
							}
							echo "<br />";
							if($_GET['subpage']=='system') //not display on installation page
							{
								if(!is_writable('./index.php'))
								{
									echo '<i class="fa fa-check-circle text-success"><!----></i> <b>'.T_("Droits d'écriture").' : </b>'.T_('Verrouillés').'<br />';
								} else {
									$system_warning=1;
									echo '<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>'.T_("Droits d'écriture").' : </b>'.T_('Non verrouillés').' <span class="text-sm"><i>(<a target="_blank" href="https://doc.gestsup.fr/install/">'.T_('cf. documentation').'</a>).<!----></i></span><br />';
								}  
								$test_install_file=file_exists('./install/index.php' );
								if(!$test_install_file) 
								{
									echo '<i class="fa fa-check-circle text-success"><!----></i> <b>'.T_("Répertoire installation").' : </b>'.T_('Non présent').'<br />';
								} elseif($rparameters['server_private_key']!='5a237a1a7c078b09e819f7e3a5825065') 
								{
									$system_error=1; 
									echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>'.T_("Répertoire installation").' : </b>'.T_('Présent').' <span class="text-sm"><i>('.T_("supprimer le répertoire \"./install\" de votre serveur").').<!----></i></span><br />';
								}
						
								//check secure SMTP
								if($rparameters['mail'])
								{
									if($rparameters['mail_port']=='587' || $rparameters['mail_port']=='465')
									{
										echo '<i class="fa fa-check-circle text-success"><!----></i> <b>SMTP : </b>'.T_('Sécurisé').'<br />';
									} else {
										$system_warning=1;
										echo '<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>SMTP : </b>'.T_('Non sécurisé').' <span class="text-sm"><i>('.T_('régler le port 465 ou 587, dans la configuration du connecteur').').<!----></i><br /></span>';
									}
								}
								//check secure IMAP
								if($rparameters['imap'])
								{
									if($rparameters['imap_port']=='993/imap/ssl' || $rparameters['imap_port']=='993')
									{
										echo '<i class="fa fa-check-circle text-success"><!----></i> <b>IMAP : </b>'.T_('Sécurisé').'<br />';
									} else {
										$system_warning=1;
										echo '<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>IMAP : </b>'.T_('Non sécurisé').' <span class="text-sm"><i>('.T_('régler le port 993, dans la configuration du connecteur').').<!----></i></span><br />';
									}
								}
								//check secure LDAP
								if($rparameters['ldap'])
								{
									if($rparameters['ldap_port']=='636')
									{
										echo '<i class="fa fa-check-circle text-success"><!----></i> <b>LDAP : </b>'.T_('Port sécurisé').'<br />';
									} else {
										$system_warning=1;
										echo '<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>LDAP : </b>'.T_('Port non sécurisé').' <span class="text-sm"><i>('.T_('régler le port 636, dans la configuration du connecteur').').<!----></i></span><br />';
									}
									//check LDAP user admin 
									if(strtoupper($rparameters['ldap_user'])=='ADMINISTRATEUR')
									{
										$system_warning=1;
										echo '<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>'.T_('LDAP').' : </b>'.T_('Utilisateur administrateur').' <span class="text-sm"><i>('.T_("l'utilisateur administrateur est spécifié sur les paramètres du connecteur LDAP, l'application n'a pas besoins de ces privilèges, renseigner un utilisateur du domaine").').<!----></i></span><br />';
									}
								}
								
								//check password policy
								if($rparameters['ldap_auth'])
								{
									echo '<i class="fa fa-check-circle text-success"><!----></i> <b>'.T_('Mots de passe').' : </b>'.T_('Géré par le serveur LDAP').'<br />';
								} elseif($rparameters['user_password_policy'])
								{
									if($rparameters['user_password_policy_min_lenght']>=8)
									{
										echo '<i class="fa fa-check-circle text-success"><!----></i> <b>'.T_('Mots de passe').' : </b>'.T_('Sécurisés').'<br />';
									} else {
										$system_warning=1;
										echo '<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>'.T_('Mots de passe').' : </b>'.T_('Longueur de mot de passe trop faible').' <span class="text-sm"><i>('.T_('définir la longueur minimale à 8 caractères').').<!----></i></span><br />';
									}
								} else {
									$system_warning=1;
									echo '<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>'.T_('Mots de passe').' : </b>'.T_('Aucune politique définie').' <span class="text-sm"><i>('.T_('définissez une politique de mot de passe dans Administration > Paramètres > Général > Utilisateur').').<!----></i></span><br />';
								}

								//check default user password							
								$qry=$db->prepare("SELECT `last_pwd_chg` FROM `tusers` WHERE `login`='user' AND `disable`='0'");
								$qry->execute();
								$user_pwd=$qry->fetch();
								$qry->closeCursor();
								if(!empty($user_pwd['last_pwd_chg']))
								{
									if($user_pwd['last_pwd_chg']=='0000-00-00') {
										echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>'.T_('Mot de passe user').' : </b>'.T_('Pas encore modifié, ou modifié il y a trop longtemps').' <span class="text-sm"><i>('.T_("changer le mot de passe du compte ayant l'identifiant user").').<!----></i></span><br />';
										$system_error=1;
									}
								}
								$qry=$db->prepare("SELECT `last_pwd_chg` FROM `tusers` WHERE `login`='power' AND `disable`='0'");
								$qry->execute();
								$power_pwd=$qry->fetch();
								$qry->closeCursor();
								if(!empty($power_pwd['last_pwd_chg']))
								{
									if($power_pwd['last_pwd_chg']=='0000-00-00') {
										echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>'.T_('Mot de passe power').' : </b>'.T_('Pas encore modifié, ou modifié il y a trop longtemps').' <span class="text-sm"><i>('.T_("changer le mot de passe du compte ayant l'identifiant power").').<!----></i></span><br />';
										$system_error=1;
									}
								}
								$qry=$db->prepare("SELECT `last_pwd_chg` FROM `tusers` WHERE `login`='super' AND `disable`='0'");
								$qry->execute();
								$super_pwd=$qry->fetch();
								$qry->closeCursor();
								if(!empty($super_pwd['last_pwd_chg']))
								{
									if($super_pwd['last_pwd_chg']=='0000-00-00') {
										echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>'.T_('Mot de passe super').' : </b>'.T_('Pas encore modifié, ou modifié il y a trop longtemps').' <span class="text-sm"><i>('.T_("changer le mot de passe du compte ayant l'identifiant super").').<!----></i></span><br />';
										$system_error=1;
									}
								}
								$qry=$db->prepare("SELECT `last_pwd_chg` FROM `tusers` WHERE `login`='tech' AND `disable`='0'");
								$qry->execute();
								$tech_pwd=$qry->fetch();
								$qry->closeCursor();
								if(!empty($tech_pwd['last_pwd_chg']))
								{
									if($tech_pwd['last_pwd_chg']=='0000-00-00') {
										echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>'.T_('Mot de passe tech').' : </b>'.T_('Pas encore modifié, ou modifié il y a trop longtemps').' <span class="text-sm"><i>('.T_("changer le mot de passe du compte ayant l'identifiant tech").').<!----></i></span><br />';
										$system_error=1;
									}
								}
								$qry=$db->prepare("SELECT `last_pwd_chg` FROM `tusers` WHERE `login`='admin' AND `disable`='0'");
								$qry->execute();
								$admin_pwd=$qry->fetch();
								$qry->closeCursor();
								if(!empty($admin_pwd['last_pwd_chg']))
								{
									if($admin_pwd['last_pwd_chg']=='0000-00-00')
									{
										$system_error=1;
										echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>'.T_('Mot de passe admin').' : </b>'.T_('Pas encore modifié, ou modifié il y a trop longtemps').' <span class="text-sm"><i>('.T_("changer le mot de passe du compte ayant l'identifiant admin").').<!----></i></span><br />';
									} else {
										echo '<i class="fa fa-check-circle text-success"><!----></i> <b>'.T_('Mot de passe admin').' : </b>'.T_('Modifié').'<br />';
									}
								}

								//check enable log 
								if($rparameters['log'])
								{
									echo '<i class="fa fa-check-circle text-success"><!----></i> <b>'.T_('Logs').' : </b>'.T_('Activés').'<br />';
								} else {
									$system_warning=1;
									echo '<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>'.T_('Logs').' : </b>'.T_('Désactivés').' <span class="text-sm"><i>('.T_('pour plus de sécurité vous pouvez activer les logs de sécurité dans Administration > Paramètres > Général > Serveur').').<!----></i></span><br />';
								}
								//check limit IP
								if($rparameters['restrict_ip'])
								{
									echo '<i class="fa fa-check-circle text-success"><!----></i> <b>'.T_('Restriction IP').' : </b>'.T_('Activé').'<br />';
								} else {
									echo '<i class="fa fa-info-circle text-info"><!----></i> <b>'.T_('Restriction IP').' : </b>'.T_('Désactivé').' <span class="text-sm"><i>('.T_("pour plus de sécurité, il est possible de restreindre l'accès des clients à certaines adresses IP, cf Administration > Paramètres > Général > Serveur").').<!----></i></span><br />';
								}
								//check system update
								if(preg_match('/Debian/',$phpinfo['phpinfo']['System']))
								{
									$current_year=date('Y');
									$previous_year=date('Y')-1;
									if(preg_match('/'.$current_year.'/',$phpinfo['phpinfo']['System']) || preg_match('/'.$previous_year.'/',$phpinfo['phpinfo']['System']))
									{} else {
										$system_warning=1;
										echo '<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>'.T_('Mise à jour système').' : </b>'.T_('Obsolète').' <span class="text-sm"><i>('.T_("le système d'exploitation serveur n'est pas à jour. Exécuter la commande : apt update && apt upgrade").').<!----></i></span><br />';
									}
								}
								//check user_admin_ip 
								if(!$rparameters['user_admin_ip'])
								{
									$system_warning=1;
									echo '<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>'.T_("Notification de connexion d'un administrateur sur une nouvelle IP").' : </b>'.T_('Désactivée').' <span class="text-sm"><i>('.T_("activer cette option dans Administration > Paramètres > Général > Utilisateurs").').<!----></i></span><br />';
								}

								//check user admin  
								$qry=$db->prepare("SELECT `id` FROM `tusers` WHERE `login`='admin' AND `disable`='0'");
								$qry->execute();
								$admin_login=$qry->fetch();
								$qry->closeCursor();
								if(isset($admin_login['id']))
								{
									$system_warning=1;
									echo '<i class="fa fa-exclamation-triangle text-warning"><!----></i> <b>'.T_("Login admin").' : </b>'.T_('Actif').' <span class="text-sm"><i>('.T_("créer un nouvel utilisateur ayant le profil administrateur, puis désactiver l'utilisateur ayant le login admin").').<!----></i></span><br />';
								}
							}
							//if curl extension is installed
							if(extension_loaded('curl'))
							{
								//test directory listing
								$url=$http.'://'.$_SERVER['SERVER_NAME'].'/vendor/components/';
								$c = curl_init($url);
								curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
								curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
								curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
								if($rparameters['server_proxy_url']) {curl_setopt($c, CURLOPT_PROXY, $rparameters['server_proxy_url']);}
								$html = curl_exec($c);
								if(curl_error($c)) die(curl_error($c));
								$status = curl_getinfo($c, CURLINFO_HTTP_CODE);
								curl_close($c);
								if($status=='200')
								{
									$system_error=1;
									echo '<i class="fa fa-times-circle text-danger"><!----></i> <b>'.T_("Listing des répertoires").' : </b>'.T_("Activé, vérifier l'option 'Indexes' de votre serveur Apache").'.<br />';
								} else {
									echo '<i class="fa fa-check-circle text-success"><!----></i> <b>'.T_("Listing des répertoires").' : </b>'.T_('Désactivé').'<br />';
								}
							}
							?>
						</span>
					</td>
				</tr>
				<tr>
					<td style="width: 150px;" class="text-95 text-default-d3 bgc-secondary-l4"><i class="fa fa-cubes text-blue-m3 pr-1"><!----></i><?php echo T_('Composants'); ?> </td>
					<td class="text-95 text-default-d3">
						<span id="components">
							<i class="fa fa-check-circle text-success"><!----></i> <b>Ace :</b> <?php echo $ace; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/makeusabrew/bootbox">makeusabrew/bootbox</a> :</b> <?php echo $makeusabrew_bootbox; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/twbs/bootstrap">twbs/bootstrap</a> :</b> <?php echo $twbs_bootstrap; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/itsjavi/bootstrap-colorpicker">itsjavi/bootstrap-colorpicker</a> :</b> <?php echo $itsjavi_bootstrapcolorpicker; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/steveathon/bootstrap-wysiwyg">steveathon/bootstrap-wysiwyg</a> :</b> <?php echo $steveathon_bootstrapwysiwyg; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/selectize/selectize.js/">selectize/selectize.js</a> :</b> <?php echo $selectize_selectizejs; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/swagger-api/swagger-ui">swagger-api/swagger-ui</a> :</b> <?php echo $swagger_api_swagger_ui; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/FortAwesome/Font-Awesome">FortAwesome/Font-Awesome</a> :</b> <?php echo $fortawesome_fontawesome; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/fullcalendar/fullcalendar">fullcalendar/fullcalendar</a> :</b> <?php echo $fullcalendar_fullcalendar; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/highcharts/highcharts">highcharts/highcharts</a> :</b> <?php echo $highcharts_highcharts; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/jquery/jquery">jquery/jquery</a> :</b> <?php echo $jquery_jquery; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/jeresig/jquery.hotkeys">jeresig/jquery.hotkeys</a> :</b> <?php echo $jeresig_jqueryhotkeys; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/thephpleague/oauth2-client">thephpleague/oauth2-client</a> :</b> <?php echo $thephpleague_oauth2client; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/thephpleague/oauth2-google">thephpleague/oauth2-google</a> :</b> <?php echo $thephpleague_oauth2google; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/stevenmaguire/oauth2-microsoft">stevenmaguire/oauth2-microsoft</a> :</b> <?php echo $stevenmaguire_oauth2microsoft; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/greew/oauth2-azure-provider">greew/oauth2-azure-provider</a> :</b> <?php echo $greew_oauth2azureprovider; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/ezyang/htmlpurifier">ezyang/htmlpurifier</a> :</b> <?php echo $ezyang_htmlpurifier; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/microsoftgraph/msgraph-sdk-php">microsoftgraph/msgraph-sdk-php</a> :</b> <?php echo $microsoftgraph_msgraph_sdk_php; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/moment/moment">moment/moment</a> :</b> <?php echo $moment_moment; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/PHPMailer/PHPMailer">PHPMailer/PHPMailer</a> :</b> <?php echo $phpmailer_phpmailer; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/barbushin/php-imap">barbushin/php-imap</a> :</b> <?php echo $barbushin_phpimap; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/inetsys/phpgettext">inetsys/phpgettext</a> :</b> <?php echo $inetsys_phpgettext; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/ifsnop/mysqldump-php">ifsnop/mysqldump-php</a> :</b> <?php echo $ifsnop_mysqldumpphp; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/FezVrasta/popper.js">FezVrasta/popper.js<a> :</b> <?php echo $FezVrasta_popperjs; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/tempusdominus/bootstrap-4">tempusdominus/bootstrap-4</a> :</b> <?php echo $tempusdominus_bootstrap4; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/TheNetworg/oauth2-azure">thenetworg/oauth2-azure</a> :</b> <?php echo $thenetworg_oauth2_azure; ?><br />
							<i class="fa fa-check-circle text-success"><!----></i> <b><a target="_blank" href="https://github.com/Webklex/php-imap">Webklex/php-imap</a> :</b> <?php echo $webklex_phpimap; ?><br />
						</span>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<?php
if($_GET['subpage']=='system')
{
	//update system error and warning detection
	$qry=$db->prepare("UPDATE `tparameters` SET `system_error`=:system_error,`system_warning`=:system_warning");
	$qry->execute(array('system_error' => $system_error,'system_warning' => $system_warning));
}

//fix SQL none values on tables
$qry=$db->prepare("SELECT `id`,`name` FROM `tcategory` WHERE id=0");
$qry->execute();
$qry->closeCursor();
if($qry->rowCount()==0)
{
	$db->exec('SET sql_mode = ""');
	$qry=$db->prepare("INSERT INTO `tcategory` (`name`) VALUES ('Aucune')");
	$qry->execute();
	$qry=$db->prepare("UPDATE `tcategory` SET `id`=0 WHERE `name`='Aucune'");
	$qry->execute();
}
$qry=$db->prepare("SELECT `id`,`login` FROM `tusers` WHERE id=0");
$qry->execute();
$qry->closeCursor();
if($qry->rowCount()==0)
{
	$db->exec('SET sql_mode = ""');
	$qry=$db->prepare("INSERT INTO `tusers` (`login`,`lastname`,`profile`,`disable`) VALUES ('aucun','Aucun','2','1')");
	$qry->execute();
	$qry=$db->prepare("UPDATE `tusers` SET `id`=0 WHERE `login`='aucun'");
	$qry->execute();
}
?>
