<?php
################################################################################
# @Name : ./core/functions.php
# @Description : define all functions
# @Call :
# @Parameters : 
# @Author : Flox
# @Create : 06/02/2018  
# @Update : 13/12/2023
# @Version : 3.2.45
################################################################################

if(!function_exists("date_cnv")){
	//date conversion to fr format
	function date_cnv($date) 
	{
		return substr($date,8,2) . "/" . substr($date,5,2) . "/" . substr($date,0,4);
	}
}

if(!function_exists("DatetimeToDB")){
	//date conversion to fr format
	function DatetimeToDB($DatetimeToDB) 
	{
		//check sec format
		$hour_format=substr_count($DatetimeToDB, ':');
		if($hour_format==1){$DatetimeToDB=DateTime::createFromFormat('d/m/Y H:i', $DatetimeToDB);} 
		else {$DatetimeToDB=DateTime::createFromFormat('d/m/Y H:i:s', $DatetimeToDB);}
		
		$DatetimeToDB=$DatetimeToDB->format('Y-m-d H:i:s');
		return $DatetimeToDB;
	}
}

if(!function_exists("DatetimeToDisplay")){
	function DatetimeToDisplay($DatetimeToDisplay) 
	{
		if($DatetimeToDisplay!='0000-00-00 00:00:00' && $DatetimeToDisplay!='0000-00-00')
		{
			$DatetimeToDisplay=DateTime::createFromFormat('Y-m-d H:i:s', $DatetimeToDisplay);
			$DatetimeToDisplay=$DatetimeToDisplay->format('d/m/Y H:i:s');
			return $DatetimeToDisplay;
		}
	}
}

if(!function_exists("DateToDB")){
	//date conversion to fr format
	function DateToDB($DateToDB) 
	{
		$DateToDB=DateTime::createFromFormat('d/m/Y', $DateToDB);
		$DateToDB=$DateToDB->format('Y-m-d');
		return $DateToDB;
	}
}

if(!function_exists("DateToDisplay")){
	function DateToDisplay($DateToDisplay) 
	{
		if($DateToDisplay!='0000-00-00')
		{
			$DateToDisplay=DateTime::createFromFormat('Y-m-d', $DateToDisplay);
			$DateToDisplay=$DateToDisplay->format('d/m/Y');
			return $DateToDisplay;
		}
	}
}
if(!function_exists("DatetimeToDate")){
	function DatetimeToDate($DatetimeToDate) 
	{
		return  substr($DatetimeToDate,8,2) . '/' . substr($DatetimeToDate,5,2) . '/' . substr($DatetimeToDate,0,4);
	}
}
if(!function_exists("CheckFileExtension")){
	function CheckFileExtension($filename) 
	{
		$blacklist = array('php', 'php1', 'php2','php3' ,'php4' ,'php5', 'php6', 'php7', 'php8', 'php9', 'php10', 'js', 'htm', 'html', 'phtml', 'exe', 'jsp' ,'pht', 'shtml', 'asa', 'cer', 'asax', 'swf', 'xap', 'phphp', 'inc', 'htaccess', 'sh', 'py', 'pl', 'jsp', 'asp', 'cgi', 'json', 'svn', 'git', 'lock', 'yaml', 'com', 'bat', 'ps1', 'cmd', 'vb', 'hta', 'reg', 'ade', 'adp', 'app', 'asp', 'bas', 'bat', 'cer', 'chm', 'cmd', 'com', 'cpl', 'crt', 'csh', 'der', 'exe', 'fxp', 'gadget', 'hlp', 'hta', 'inf', 'ins', 'isp', 'its', 'js', 'jse', 'ksh', 'lnk', 'mad', 'maf', 'mag', 'mam', 'maq', 'mar', 'mas', 'mat', 'mau', 'mav', 'maw', 'mda', 'mdb', 'mde', 'mdt', 'mdw', 'mdz', 'msc', 'msh', 'msh1', 'msh2', 'mshxml', 'msh1xml', 'msh2xml', 'msi', 'msp', 'mst', 'ops', 'pcd', 'pif', 'plg', 'prf', 'prg', 'pst', 'reg', 'scf', 'scr', 'sct', 'shb', 'shs', 'ps1', 'ps1xml', 'ps2', 'ps2xml', 'psc1', 'psc2', 'tmp', 'url', 'vb', 'vbe', 'vbs', 'vsmacros', 'vsw', 'ws', 'wsc', 'wsf', 'wsh', 'xnk', 'payload', 'shell', 'phar', 'phpt', 'pht', 'pgif');
		$extension=new SplFileInfo($filename);
		$extension=$extension->getExtension();
		if(in_array(strtolower($extension),$blacklist)) {$result=false;} else {$result=true;}
		return $result;
	}
}
if(!function_exists("logit")){
	function logit($type,$message,$user_id) 
	{
		//db connect
		require(__DIR__."/../connect.php");

		//switch SQL MODE to allow empty values
		$db->exec('SET sql_mode = ""');

		//load parameters table
		$qry=$db->prepare("SELECT `log` FROM `tparameters`");
		$qry->execute();
		$rparameters=$qry->fetch();
		$qry->closeCursor();

		if(!isset($_SERVER['REMOTE_ADDR'])) {$_SERVER['REMOTE_ADDR']='php_cli';}

		if($rparameters['log'])
		{
			$qry=$db->prepare("INSERT INTO `tlogs` (`type`,`date`,`message`,user,ip) VALUES (:type,:date,:message,:user,:ip)");
			$qry->execute(array('type' => $type,'date' => date('Y-m-d H:i:s'),'message' => $message, 'user' => $user_id,'ip' => $_SERVER['REMOTE_ADDR']));
		}
	}
}

//date conversion
function date_convert($date) 
{return  substr($date,8,2) . '/' . substr($date,5,2) . '/' . substr($date,0,4) . ' '.T_('à').' ' . substr($date,11,2	) . 'h' . substr($date,14,2	);}

//date conversion
function MinToHour($min) 
{
	if($min>=60){$time=round($min/60,1).'h';} else {$time=$min.'m';}
	return $time;
}

function gs_crypt($string, $action, $key) 
{
    $secret_key = $key;
    $secret_iv = 'G€$|$ùP!';
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    if($action=='e') {
        $output='gs_en_'.base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
    }
    elseif($action=='d'){
        $output=openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}

function DisplayMessage($type, $message)
{
	if($type=='error')
	{
		return '
		<div role="alert" class="alert alert-lg bgc-danger-l3 border-0 border-l-4 brc-danger-m1 mt-4 mb-3 pr-3 d-flex">
			<div class="flex-grow-1">
				<i class="fas fa-times mr-1 text-120 text-danger-m1"><!----></i>
				<strong class="text-danger">'.T_('Erreur').' : '.$message.'. </strong>
			</div>
			<button type="button" class="close align-self-start" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true"><i class="fa fa-times text-80"><!----></i></span>
			</button>
		</div>
	';
	} 
	if($type=='warning')
	{
		return '
		<div role="alert" class="alert alert-lg bgc-warning-l3 border-0 border-l-4 brc-warning-m1 mt-4 mb-3 pr-3 d-flex">
			<div class="flex-grow-1">
				<i class="fas fa-exclamation-triangle mr-1 text-120 text-warning-m1"><!----></i>
				<strong class="text-warning">'.T_('Avertissement').' : '.$message.'. </strong>
			</div>
			<button type="button" class="close align-self-start" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true"><i class="fa fa-times text-80"><!----></i></span>
			</button>
		</div>
	';
	} 
	if($type=='success')
	{
		return '
		<div role="alert" class="alert alert-lg bgc-success-l3 border-0 border-l-4 brc-success-m1 mt-4 mb-3 pr-3 d-flex">
				<div class="flex-grow-1">
					<i class="fas fa-check mr-1 text-120 text-success-m1"><!----></i>
					<strong class="text-success">'.$message.'</strong>
				</div>
				<button type="button" class="close align-self-start" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true"><i class="fa fa-times text-80"><!----></i></span>
				</button>
			</div>
			';
	}
	if($type=='info')
	{
		return '
		<div role="alert" class="alert alert-lg bgc-info-l3 border-0 border-l-4 brc-info-m1 mt-4 mb-3 pr-3 d-flex">
			<div class="flex-grow-1">
				<i class="fas fa-info-circle mr-1 text-120 text-info-m1"><!----></i>
				<strong class="text-info">'.$message.'</strong>
			</div>
			<button type="button" class="close align-self-start" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true"><i class="fa fa-times text-80"><!----></i></span>
			</button>
		</div>
		';
	}
	if($type=='progress')
	{
		return '
		<div id="ProgressMessage" role="alert" class="alert alert-lg bgc-info-l3 border-0 border-l-4 brc-info-m1 mt-4 mb-3 pr-3 d-flex">
			<div class="flex-grow-1">
				<i class="fa fa-spinner fa-spin text-info-m1 text-120"><!----></i>
				<strong class="text-info">'.$message.'</strong>
			</div>
			<button type="button" class="close align-self-start" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true"><i class="fa fa-times text-80"><!----></i></span>
			</button>
		</div>
		';
	}
}

//check image file
if(!function_exists("IsImage")){
	function IsImage($path)
	{
		if(@is_array(getimagesize($path)))
		{
			return true;
		} else {
			return false;
		}
	}
}

//compress image file
if(!function_exists("CompressImage")){
	function CompressImage($source, $destination, $quality) { 
		//get image info 
		$imginfo = getimagesize($source); 
		$mime = $imginfo['mime']; 
		//create a new image from file 
		switch($mime){ 
			case 'image/jpeg': 
				$image = imagecreatefromjpeg($source); 
				break; 
			case 'image/png': 
				$image = imagecreatefrompng($source); 
				break; 
			case 'image/gif': 
				$image = imagecreatefromgif($source); 
				break; 
			default: 
				$image = imagecreatefromjpeg($source); 
		} 
		//save image 
		imagejpeg($image, $destination, $quality); 
		//return compressed image 
		return $destination; 
	} 
}

if(!function_exists("DeleteTicket")){
	function DeleteTicket($ticket_id) 
	{
		//db connect
		require(__DIR__."/../connect.php");
		$db->exec('SET sql_mode = ""');

		//load rights table
		$qry=$db->prepare("SELECT * FROM `trights` WHERE profile=:profile");
		$qry->execute(array('profile' => $_SESSION['profile_id']));
		$rright=$qry->fetch();
		$qry->closeCursor();

		if(!$rright['ticket_delete'] && !$rright['ticket_fusion']) {echo DisplayMessage('error',T_('Vous ne disposez pas des droits nécessaire')); exit;}
		if(!$ticket_id) {echo DisplayMessage('error',T_('Paramètre manquant')); exit;}
		
		$qry=$db->prepare("DELETE FROM `tincidents` WHERE id=:id"); //delete ticket
		$qry->execute(array('id' => $ticket_id));
		$qry=$db->prepare("DELETE FROM `tevents` WHERE incident=:incident"); //delete associate events
		$qry->execute(array('incident' => $ticket_id));
		$qry=$db->prepare("DELETE FROM `tthreads` WHERE ticket=:ticket"); //delete threads
		$qry->execute(array('ticket' => $ticket_id));
		$qry=$db->prepare("DELETE FROM `tmails` WHERE incident=:incident"); //delete mails
		$qry->execute(array('incident' => $ticket_id));
		$qry=$db->prepare("DELETE FROM `tsurvey_answers` WHERE ticket_id=:ticket_id"); //delete survey
		$qry->execute(array('ticket_id' => $ticket_id));
		$qry=$db->prepare("DELETE FROM `ttemplates` WHERE incident=:incident"); //delete template
		$qry->execute(array('incident' => $ticket_id));
		$qry=$db->prepare("DELETE FROM `ttoken` WHERE ticket_id=:ticket_id"); //delete token
		$qry->execute(array('ticket_id' => $ticket_id));
		
		//remove old upload files and folder if exist
		$upload_dir_to_remove='upload/'.$ticket_id.'/';
		if(is_numeric($ticket_id) && is_dir($upload_dir_to_remove)) 
		{
			//remove files before delete directory
			$files_to_remove = array_diff(scandir($upload_dir_to_remove), array('.','..'));
			foreach ($files_to_remove as $file_to_remove) {
				if(file_exists($upload_dir_to_remove.$file_to_remove)) {unlink($upload_dir_to_remove.$file_to_remove);}
			}
			rmdir($upload_dir_to_remove); //remove empty dir
		}
		
		//remove new upload files
		$qry=$db->prepare("SELECT COUNT(`id`) FROM `tattachments` WHERE ticket_id=:ticket_id");
		$qry->execute(array('ticket_id' => $ticket_id));
		$row=$qry->fetch();
		$qry->closeCursor();
		if($row[0]>0)
		{
			//remove files
			$qry=$db->prepare("SELECT `storage_filename` FROM `tattachments` WHERE ticket_id=:ticket_id");
			$qry->execute(array('ticket_id' => $ticket_id));
			while($attachment=$qry->fetch()) 
			{
				if(file_exists('upload/ticket/'.$attachment['storage_filename'])) {unlink('upload/ticket/'.$attachment['storage_filename']);}
			}
			$qry->closeCursor();
			//delete in db
			$qry=$db->prepare("DELETE FROM `tattachments` WHERE ticket_id=:ticket_id");
			$qry->execute(array('ticket_id' => $ticket_id));
		}

		//remove image attachment from IMAP connector
		$pattern='upload/ticket/'.$ticket_id.'_*';
		$ticket_files = glob($pattern);
		foreach ($ticket_files as $file_to_delete) {
			if(file_exists($file_to_delete)){unlink($file_to_delete); }
		}

		//logit
		logit('ticket', 'Ticket '.$ticket_id.' deleted ',$_SESSION['user_id']);
	}
}

if(!function_exists("DeleteUser")){
	function DeleteUser($user_id) 
	{
		//db connect
		require(__DIR__."/../connect.php");
		$db->exec('SET sql_mode = ""');

		//load rights table
		$qry=$db->prepare("SELECT * FROM `trights` WHERE profile=:profile");
		$qry->execute(array('profile' => $_SESSION['profile_id']));
		$rright=$qry->fetch();
		$qry->closeCursor();

		if(!$rright['admin']) {echo DisplayMessage('error',T_('Vous ne disposez pas des droits nécessaire')); exit;}
		if(!$user_id) {echo DisplayMessage('error',T_('Paramètre manquant')); exit;}
		
		//get id of delete_user
		$qry=$db->prepare("SELECT `id` FROM `tusers` WHERE login=:login");
		$qry->execute(array('login' => 'delete_user_gs'));
		$delete_user=$qry->fetch();
		$qry->closeCursor();
		$delete_user=$delete_user[0];

		//update ticket
		$qry=$db->prepare("UPDATE `tincidents` SET `user`=:delete_user WHERE `user`=:user");
		$qry->execute(array('delete_user' => $delete_user,'user' => $user_id));
		$qry=$db->prepare("UPDATE `tincidents` SET `technician`=:delete_user WHERE `technician`=:technician");
		$qry->execute(array('delete_user' => $delete_user,'technician' => $user_id));
		$qry=$db->prepare("UPDATE `tincidents` SET `creator`=:delete_user WHERE `creator`=:creator");
		$qry->execute(array('delete_user' => $delete_user,'creator' => $user_id));
		$qry=$db->prepare("UPDATE `tincidents` SET `observer1`=:delete_user WHERE `observer1`=:observer1");
		$qry->execute(array('delete_user' => $delete_user,'observer1' => $user_id));
		$qry=$db->prepare("UPDATE `tincidents` SET `observer2`=:delete_user WHERE `observer2`=:observer2");
		$qry->execute(array('delete_user' => $delete_user,'observer2' => $user_id));
		$qry=$db->prepare("UPDATE `tincidents` SET `observer3`=:delete_user WHERE `observer1`=:observer3");
		$qry->execute(array('delete_user' => $delete_user,'observer3' => $user_id));
		//update threads
		$qry=$db->prepare("UPDATE `tthreads` SET `author`=:delete_user WHERE `author`=:author");
		$qry->execute(array('delete_user' => $delete_user,'author' => $user_id));
		$qry=$db->prepare("UPDATE `tthreads` SET `tech1`=:delete_user WHERE `tech1`=:tech1");
		$qry->execute(array('delete_user' => $delete_user,'tech1' => $user_id));
		$qry=$db->prepare("UPDATE `tthreads` SET `tech2`=:delete_user WHERE `tech2`=:tech2");
		$qry->execute(array('delete_user' => $delete_user,'tech2' => $user_id));
		$qry=$db->prepare("UPDATE `tthreads` SET `user`=:delete_user WHERE `user`=:user");
		$qry->execute(array('delete_user' => $delete_user,'user' => $user_id));
		//update asset
		$qry=$db->prepare("UPDATE `tassets` SET `user`=:delete_user WHERE `user`=:user");
		$qry->execute(array('delete_user' => $delete_user,'user' => $user_id));
		$qry=$db->prepare("UPDATE `tassets` SET `technician`=:delete_user WHERE `technician`=:technician");
		$qry->execute(array('delete_user' => $delete_user,'technician' => $user_id));
		//update log
		$qry=$db->prepare("UPDATE `tlogs` SET `user`=:delete_user WHERE `user`=:user");
		$qry->execute(array('delete_user' => $delete_user,'user' => $user_id));
		//remove calendar
		$qry=$db->prepare("DELETE FROM `tevents` WHERE `technician`=:technician");
		$qry->execute(array('technician' => $user_id));
		//remove groups
		$qry=$db->prepare("DELETE FROM `tgroups_assoc` WHERE `user`=:user");
		$qry->execute(array('user' => $user_id));
		//remove token
		$qry=$db->prepare("DELETE FROM `ttoken` WHERE `user_id`=:user_id");
		$qry->execute(array('user_id' => $user_id));
		//remove agencies
		$qry=$db->prepare("DELETE FROM `tusers_agencies` WHERE `user_id`=:user_id");
		$qry->execute(array('user_id' => $user_id));
		//remove services
		$qry=$db->prepare("DELETE FROM `tusers_services` WHERE `user_id`=:user_id");
		$qry->execute(array('user_id' => $user_id));
		//remove tech assoc
		$qry=$db->prepare("DELETE FROM `tusers_tech` WHERE `user`=:user");
		$qry->execute(array('user' => $user_id));
		$qry=$db->prepare("DELETE FROM `tusers_tech` WHERE `tech`=:tech");
		$qry->execute(array('tech' => $user_id));
		//remove view 
		$qry=$db->prepare("DELETE FROM `tviews` WHERE `uid`=:uid");
		$qry->execute(array('uid' => $user_id));
		//remove user 
		$qry=$db->prepare("DELETE FROM `tusers` WHERE `id`=:id");
		$qry->execute(array('id' => $user_id));

		//logit
		logit('ticket', 'User '.$user_id.' deleted ',$_SESSION['user_id']);
	}
}

//upload file
if(!function_exists("UploadFile")){
	function UploadFile($file_name,$file_tmp_name,$target_folder,$type,$id,$user_id)
	{
		$error=0;
		//db connect
		require(__DIR__."/../connect.php");
		$db->exec('SET sql_mode = ""');

		//create directory
		if(!is_dir($target_folder))  {mkdir($target_folder, 0777, true);}

		//sanitize filename
		$real_filename=preg_replace("/[^A-Za-z0-9\_\-\.\s+]/", '', $file_name);

		//check blacklist extension
		if(CheckFileExtension($real_filename)==true) {
			//generate storage filename
			$storage_filename=$id.'_'.md5(uniqid());
			//extract file extension 
			$extension=new SplFileInfo($file_name);
			$extension=$extension->getExtension();
			//if jpeg image file compressed it
			if(($extension=='jpg' || $extension=='jpeg') && extension_loaded('gd') && IsImage($file_tmp_name))
			{
				if(CompressImage($file_tmp_name, $target_folder.$storage_filename, 70)) 
				{
					//check if file exist
					if(file_exists($target_folder.$storage_filename))
					{
						//content check
						$file_content = file_get_contents($target_folder.$storage_filename, true);
						if(preg_match('{\<\?php}',$file_content) || preg_match('/system\(/',$file_content)) {
							unlink($target_folder.$storage_filename); //remove file
							$error=T_("Fichier interdit");
							if($rparameters['log'] && is_numeric($_GET['id'])) {logit('security','Blacklisted file "'.$real_filename.'" blocked on ticket '.$id,$user_id);}
						} else {
							$uid=md5(uniqid());
							if($type=='ticket')
							{
								$qry=$db->prepare("INSERT INTO `tattachments` (`uid`,`ticket_id`,`storage_filename`,`real_filename`) VALUES (:uid,:ticket_id,:storage_filename,:real_filename)");
								$qry->execute(array('uid' => $uid,'ticket_id' => $id,'storage_filename' => $storage_filename,'real_filename' => $real_filename));
							} elseif($type=='procedure')
							{
								$qry=$db->prepare("INSERT INTO `tattachments` (`uid`,`procedure_id`,`storage_filename`,`real_filename`) VALUES (:uid,:procedure_id,:storage_filename,:real_filename)");
								$qry->execute(array('uid' => $uid,'procedure_id' => $id,'storage_filename' => $storage_filename,'real_filename' => $real_filename));
							}
						} 
					} else {
						$error=T_("Erreur durant le transfert de fichier");
					}
				} else {
					$error=T_("Compression image impossible");
				}
			} else { //not image file
				
				if(move_uploaded_file($file_tmp_name, $target_folder.$storage_filename))
				{
					//check if file exist
					if(file_exists($target_folder.$storage_filename))
					{
						//content check
						$file_content = file_get_contents($target_folder.$storage_filename, true);
						if(preg_match('{\<\?php}',$file_content) || preg_match('/system\(/',$file_content)) {
							unlink($target_folder.$storage_filename); //remove file
							$error=T_("Fichier interdit");
							if($rparameters['log'] && is_numeric($_GET['id'])) {logit('security','File upload blocked on ticket '.$_GET['id'],$user_id);}
						} else {
							$uid=md5(uniqid());
							if($type=='ticket')
							{
								$qry=$db->prepare("INSERT INTO `tattachments` (`uid`,`ticket_id`,`storage_filename`,`real_filename`) VALUES (:uid,:ticket_id,:storage_filename,:real_filename)");
								$qry->execute(array('uid' => $uid,'ticket_id' => $_GET['id'],'storage_filename' => $storage_filename,'real_filename' => $real_filename));
							} elseif($type=='procedure')
							{
								$qry=$db->prepare("INSERT INTO `tattachments` (`uid`,`procedure_id`,`storage_filename`,`real_filename`) VALUES (:uid,:procedure_id,:storage_filename,:real_filename)");
								$qry->execute(array('uid' => $uid,'procedure_id' => $id,'storage_filename' => $storage_filename,'real_filename' => $real_filename));
							}
						} 
					} else {
						$error=T_("Erreur durant le transfert de fichier");
					}
				} else {
					$error=T_("Transfert fichier impossible");
				}
			}
			return array(
				'uid' => $uid,
				'error' => $error,
			);
		} else {
			return array(
				'uid' => 0,
				'error' => T_("Fichier interdit"),
			);
			if($rparameters['log'] && is_numeric($_GET['id'])) {logit('security','Blacklisted file "'.$real_filename.'" blocked on ticket '.$_GET['id'],$user_id);}
		}
	}
}

//check app update
if(!function_exists("CheckUpdate")){
	function CheckUpdate()
	{
		if(CheckConnection('gestsup.fr',443))
		{
			//db connect
			require(__DIR__."/../connect.php");
			$db->exec('SET sql_mode = ""');	

			//load parameters table
			$qry=$db->prepare("SELECT * FROM `tparameters`");
			$qry->execute();
			$rparameters=$qry->fetch();
			$qry->closeCursor();

				//define URL to check
			$url='https://gestsup.fr/lastest_patch_'.$rparameters['update_channel'].'.php';
			
			//get lastest version
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			if($rparameters['server_proxy_url']) {curl_setopt($curl, CURLOPT_PROXY, $rparameters['server_proxy_url']);}
			$lastest_version = curl_exec($curl);
			if(curl_error($curl)) die(curl_error($curl));
			$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			curl_close($curl);
			if($status!='200' || !$lastest_version)
			{
				LogIt('error', 'ERROR 33 : CHECK GESTSUP UPDATE, unable to get '.$url.' error '.$status,0);
				return array(
					'result' => 'error',
					'message' => 'unable to get '.$url.' error '.$status
				);
			} 
			
			//update last version parameter
			$qry=$db->prepare("UPDATE `tparameters` SET `last_version`=:last_version");
			$qry->execute(array('last_version' => $lastest_version));

			//compare last patch
			$need_update=0;
			$local_version_array=explode('.',$rparameters['version']);
			$local_version1=$local_version_array[0];
			$local_version2=$local_version_array[1];
			$local_version3=$local_version_array[2];
			$lastest_version_array=explode('.',$lastest_version);
			$lastest_version1=$lastest_version_array[0];
			$lastest_version2=$lastest_version_array[1];
			$lastest_version3=$lastest_version_array[2];
			if($lastest_version1>$local_version1) {$need_update=1;} 
			elseif($lastest_version2>$local_version2){$need_update=1;}
			elseif($lastest_version3>$local_version3+2){$need_update=1;} 

			//add system error
			if($need_update)
			{
				$qry=$db->prepare("UPDATE `tparameters` SET `system_error`='1'");
				$qry->execute();
			}
			return array(
				'result' => 'success',
				'message' => 'lastest version parameters updated '.$lastest_version.' need_update='.$need_update
			);
			
		} else {
			LogIt('error', 'ERROR 16 : CHECK GESTSUP UPDATE, unable to access on gestsup.fr:443',0);
			return array(
				'result' => 'error',
				'message' => 'unable to access on gestsup.fr:443'
			);
		}
	}
}

//check connection 
if(!function_exists("CheckConnection")){
	function CheckConnection($host,$port)
	{
		$context = stream_context_create([
			'ssl' => [
				'verify_peer' => false,
				'verify_peer_name' => false
			]
		]);
		$fp = stream_socket_client("tcp://$host:$port", $errno, $errstr, 2, STREAM_CLIENT_CONNECT, $context);
		if ($fp) {
			fclose($fp);
			return 1;
		} else {
			LogIt('error', 'ERROR 36 : CHECK CONNEXION, unable to access to '.$host.' on '.$port.' '.$errstr.' ('.$errno.')',0);
			return 0;
		}
	}
}

//function convert for LDAP
if(!function_exists("SIDtoString")){
	function SIDtoString($ADsid)
	{
		$sid = "S-";
		//$ADguid = $info[0]['objectguid'][0];
		$sidinhex = str_split(bin2hex($ADsid), 2);
		// Byte 0 = Revision Level
		$sid = $sid.hexdec($sidinhex[0])."-";
		// Byte 1-7 = 48 Bit Authority
		$sid = $sid.hexdec($sidinhex[6].$sidinhex[5].$sidinhex[4].$sidinhex[3].$sidinhex[2].$sidinhex[1]);
		// Byte 8 count of sub authorities - Get number of sub-authorities
		$subauths = hexdec($sidinhex[7]);
		//Loop through Sub Authorities
		for($i = 0; $i < $subauths; $i++) {
			$start = 8 + (4 * $i);
			// X amount of 32Bit (4 Byte) Sub Authorities
			$sid = $sid."-".hexdec($sidinhex[$start+3].$sidinhex[$start+2].$sidinhex[$start+1].$sidinhex[$start]);
		}
		return $sid;
	}
}

//function update telemetry
if(!function_exists("Telemetry")){
	function Telemetry()
	{
		if(CheckConnection('gestsup.fr',443))
		{
			//db connection
			require('connect.php');
			
			//load parameters table
			$qry=$db->prepare("SELECT * FROM `tparameters`");
			$qry->execute();
			$rparameters=$qry->fetch();
			$qry->closeCursor();

			//get db informations
			$qry=$db->prepare("SHOW VARIABLES");
			$qry->execute();
			while($row=$qry->fetch()) 
			{
				if($row[0]=="version") {$sql=$row[1];}
			}
			$qry->closeCursor();

			//get number of tickets
			$qry=$db->prepare("SELECT COUNT(`id`) FROM `tincidents` WHERE `disable`='0'");
			$qry->execute();
			$row=$qry->fetch();
			$qry->closeCursor();
			$ticket=$row[0];
			
			//get number of users
			$qry=$db->prepare("SELECT COUNT(`id`) FROM `tusers` WHERE `disable`='0'");
			$qry->execute();
			$row=$qry->fetch();
			$qry->closeCursor();
			$user=$row[0];

			//get number of assets
			$qry=$db->prepare("SELECT COUNT(`id`) FROM `tassets` WHERE `disable`='0'");
			$qry->execute();
			$row=$qry->fetch();
			$qry->closeCursor();
			$asset=$row[0];

			//crypt data
			$secret_key = 'zmMNBo@Q*Ebqa0HJIt^5gcuWRnx#UJNdwEXPA9QlPqf1g4VplE';
			$secret_iv = 'G€$|$ùP!';
			$output = false;
			$encrypt_method = "AES-256-CBC";
			$key = hash('sha256', $secret_key);
			$iv = substr(hash('sha256', $secret_iv), 0, 16);
			$company=base64_encode(openssl_encrypt(htmlspecialchars_decode($rparameters['company']), $encrypt_method, $key, 0, $iv));
			$version=base64_encode(openssl_encrypt($rparameters['version'], $encrypt_method, $key, 0, $iv));
			$server_url=base64_encode(openssl_encrypt($rparameters['server_url'], $encrypt_method, $key, 0, $iv));
			$os=base64_encode(openssl_encrypt(php_uname(), $encrypt_method, $key, 0, $iv));
			$php=base64_encode(openssl_encrypt(phpversion(), $encrypt_method, $key, 0, $iv));
			$sql=base64_encode(openssl_encrypt($sql, $encrypt_method, $key, 0, $iv));
			$apache=base64_encode(openssl_encrypt($_SERVER['SERVER_SOFTWARE'], $encrypt_method, $key, 0, $iv));
			$ticket=base64_encode(openssl_encrypt($ticket, $encrypt_method, $key, 0, $iv));
			$asset=base64_encode(openssl_encrypt($asset, $encrypt_method, $key, 0, $iv));
			$user=base64_encode(openssl_encrypt($user, $encrypt_method, $key, 0, $iv));
			$date_install=base64_encode(openssl_encrypt($rparameters['server_date_install'], $encrypt_method, $key, 0, $iv));

			//url generation
			$url='https://gestsup.fr/telemetry.php';
			$url.='?install_id='.hash('md5',$rparameters['server_private_key']);
			$url.='&key=LChWr2fcmyBy3q23PzAcRnMrrgsAHs88xtyYcUd3U6YLLj59qrJNsXFD7PXxAnkw';
			$url.='&company='.$company;
			$url.='&version='.$version;
			$url.='&os='.$os;
			$url.='&php='.$php;
			$url.='&sql='.$sql;
			$url.='&apache='.$apache;
			$url.='&ticket='.$ticket;
			$url.='&asset='.$asset;
			$url.='&user='.$user;
			$url.='&url='.$server_url;
			$url.='&date_install='.$date_install;

			//send data
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			if($rparameters['server_proxy_url']) {curl_setopt($curl, CURLOPT_PROXY, $rparameters['server_proxy_url']);}
			curl_exec($curl);
			if(curl_error($curl)) die(curl_error($c));
			curl_close($curl);
		}
	}
}

//function to generate random password
if(!function_exists("RandomPassword")){
	function RandomPassword() {
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$pass = array();
		$alphaLength = strlen($alphabet) - 1;
		for ($i = 0; $i < 8; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass);
	}
}

function GetSizeName($octet)
{
    //units array
    $unite = array('o','Ko','Mo','Go');
    
    if ($octet < 1000) // octet
    {
        return $octet.$unite[0];
    }
    else 
    {
        if ($octet < 1000000) // ko
        {
            $ko = round($octet/1024,2);
            return $ko.$unite[1];
        }
        else // Mo ou Go 
        {
            if ($octet < 1000000000) // Mo 
            {
                $mo = round($octet/(1024*1024),2);
                return $mo.$unite[2];
            }
            else // Go 
            {
                $go = round($octet/(1024*1024*1024),2);
                return $go.$unite[3];    
            }
        }
    }
}

?>