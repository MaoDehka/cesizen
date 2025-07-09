<?php
################################################################################
# @Name : download.php
# @Description : download file
# @Call : index.php, ticket.php
# @Parameters : download uid
# @Author : Flox
# @Create : 17/12/2019
# @Update : 01/11/2023
# @Version : 3.2.42
################################################################################

//prevent direct access
if(!isset($_SESSION['user_id'])) {echo 'ERROR 403 : /core/download.php'; header('HTTP/1.0 403 Forbidden'); exit;}

if(isset($_GET['download']))
{
	//get download properties
	$qry=$db->prepare("SELECT `real_filename`,`storage_filename`,`ticket_id`,`procedure_id`,`asset_id` FROM `tattachments` WHERE `uid`=:uid");
	$qry->execute(array('uid' => $_GET['download']));
	$attachment=$qry->fetch();
	$qry->closeCursor();
	
	if(!empty($attachment))
	{
		//define file path
		if($attachment['ticket_id'])
		{
			$filepath='upload/ticket/'.$attachment['storage_filename'];
		} elseif($attachment['procedure_id']) {
			$filepath='upload/procedure/'.$attachment['storage_filename'];
		} elseif($attachment['asset_id']) {
			$filepath='upload/asset/'.$attachment['storage_filename'];
		} else {
			echo 'ERROR : file type unknown'; exit;
		}
		
		if(file_exists($filepath))
		{
			//define image extension array
			$image_extension=array('png','jpg','jpeg','gif','tif','tiff','svg','bmp');
			$file_extension=substr($attachment['real_filename'],-3);
			if(in_array($file_extension,$image_extension)) //open image file else download
			{
				//echo '<img alt="img" src="'.$filepath.'" />';
				header('Content-Type: image/jpeg');
				echo file_get_contents($filepath);
			} else {
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="'.$attachment['real_filename'].'"');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . filesize($filepath));
				flush();
				readfile($filepath);
				die();
			}
		} else {
			echo 'ERROR : File not exist';
			die;
		}
	} else {
		echo 'ERROR : invalid file';
		die;
	}
} else {
	echo 'ERROR : Download failed';
	die;
}
?>