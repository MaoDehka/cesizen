<?php 
################################################################################
# @Name : attachment.php
# @Description : attach file to ticket
# @Call : /ticket.php
# @Parameters : 
# @Author : Flox
# @Create : 06/03/2013
# @Update : 30/01/2024
# @Version : 3.2.47
################################################################################

//secure direct access
if(!isset($_GET['page'])) {echo 'ERROR : invalid access'; exit;}

//delete attachment
if($rright['ticket_attachment_delete'] && $_GET['delete_file'])
{
	//get file properties
	$qry=$db->prepare("SELECT `id`,`uid`,`ticket_id`,`storage_filename` FROM `tattachments` WHERE id=:id");
	$qry->execute(array('id' => $_GET['delete_file']));
	$attachment=$qry->fetch();
	$qry->closeCursor();
	
	if(!empty($attachment))
	{
		if(is_numeric($_GET['id']))
		{
			if($_GET['id']==$attachment['ticket_id'])
			{
				if($attachment['uid']) //new storage
				{
					if(file_exists('./upload/ticket/'.$attachment['storage_filename'])) 
					{
						unlink('./upload/ticket/'.$attachment['storage_filename']);
					}
					if(!file_exists('./upload/ticket/'.$attachment['storage_filename']))
					{
						$qry=$db->prepare("DELETE FROM `tattachments` WHERE id=:id");
						$qry->execute(array('id' => $_GET['delete_file']));
					}
				} else { //old storage
					if(file_exists('./upload/'.$_GET['id'].'/'.$attachment['storage_filename'])) 
					{
						unlink('./upload/'.$_GET['id'].'/'.$attachment['storage_filename']);
					}
					if(!file_exists('./upload/'.$_GET['id'].'/'.$attachment['storage_filename']))
					{
						$qry=$db->prepare("DELETE FROM `tattachments` WHERE id=:id");
						$qry->execute(array('id' => $_GET['delete_file']));
					}
				}
			} else {
				echo 'ERROR : Delete failed, file associated to other ticket.';
			}
		} else {
			echo 'ERROR : Delete failed, incorrect ticket id.';
		}
	} 
}

//display upload field
if($globalrow['state']!=3 && $rright['ticket_attachment'])
{
	//limit by ticket
	$hide_attachment=0;
	if($_GET['action']!='new' && $_GET['id'])
	{
		$qry=$db->prepare("SELECT COUNT(`id`) FROM `tattachments` WHERE ticket_id=:ticket_id");
		$qry->execute(array('ticket_id' => $_GET['id']));
		$row=$qry->fetch();
		$qry->closeCursor();
		if($row[0]>100) {$hide_attachment=1;}
		if($ticket_observer) {$hide_attachment=1;}
	} 
	if(!$hide_attachment)
	{
		echo '
		<input type="hidden" name="MAX_FILE_SIZE" value="100000000" />
		<input '; if($mobile) {echo ' style="color:transparent; width:160px;" ';} echo ' id="file" type="file" name="file" />&nbsp;
		<button class="btn btn-sm btn-success" title="'.T_('Enregistrer le ticket et charger le fichier').'" name="upload" value="upload" type="submit" id="upload"><i class="fa fa-upload"><!----></i></button>
		';
	}
	
	//check size
	$upload_max_size_error=T_('Le fichier joint est trop volumineux, la taille maximum est ').ini_get('post_max_size');
	//get php value parameters
	if(preg_match('~[0-9]~', ini_get('post_max_size'))){$upload_max_size=(preg_replace('/[^0-9.]+/', '', ini_get('post_max_size')))*1024*1024;} else {$upload_max_size=8000000;}
	//display alert
	if($_GET['action']!='adduser' && $_GET['action']!='edituser'  && $_GET['action']!='addcat' && $_GET['action']!='editcat' && $_GET['action']!='template')
	{
		echo '
		<script type="text/javascript">
			$(function() {
				$("input:file").change(function (){
					if(!($("#file")[0].files[0].size < '.$upload_max_size.' )) { 
						//reset field and display error
						$("#file").val("")
						alert("'.$upload_max_size_error.'");
					}
				});
			});
		</script>
		';
	}
}

//display exiting attachments
$qry=$db->prepare("SELECT `id`,`uid`,`storage_filename`,`real_filename` FROM `tattachments` WHERE `ticket_id`=:ticket_id ORDER BY `id`");
$qry->execute(array('ticket_id' => $_GET['id']));
while($attachment=$qry->fetch()) 
{
	echo '<div class="p-1"></div>';	
	if(!$attachment['uid']) //old upload file case
	{
		echo '<a target="_blank" title="'.T_('Télécharger le fichier').' '.$attachment['real_filename'].'" href="./upload/'.$_GET['id'].'/'.$attachment['real_filename'].'" style="text-decoration:none"><i style="vertical-align: middle;" class="fa fa-file text-info" ></i>&nbsp;</a>&nbsp;<a target="_blank" title="'.T_('Télécharger le fichier').' '.$attachment['real_filename'].'" href="./upload/'.$_GET['id'].'/'.$attachment['real_filename'].'" >'.$attachment['real_filename'].'</a>';
		if($rright['ticket_attachment_delete']) {echo '&nbsp;<a title="'.T_('Supprimer ce fichier').'" onClick="javascript: return confirm(\''.T_('Êtes-vous sur de vouloir supprimer ce fichier ?').'\');" href="./index.php?page=ticket&amp;id='.$_GET['id'].'&amp;'.$url_get_parameters.'&amp;delete_file='.$attachment['id'].'" style="text-decoration: none;" > <i class="fa fa-trash text-danger"><!----></i></a>';}
		if(is_dir('upload/'.$_GET['id'])) {
			if(file_exists('upload/'.$_GET['id'].'/'.$attachment['real_filename']))
			{
				$file_size=filesize('upload/'.$_GET['id'].'/'.$attachment['real_filename']);
				if($file_size> 1000000) //display in MB
				{
					$file_size=round($file_size/1024/1024,1);
					echo '&nbsp;&nbsp;('.$file_size.' Mo)';
				} else { //display in KB
					$file_size=round($file_size/1024,0);
					echo '&nbsp;&nbsp;('.$file_size.' Ko)';
				}
			} else {
				echo ' ('.T_('Le fichier a été supprimé du serveur').')';
			}
		} else {echo ' ('.T_('Le repertoire de ce ticket a été supprimé du serveur').')';}
	} else {
		echo '
		<a target="_blank" title="'.T_('Télécharger le fichier').' '.$attachment['real_filename'].'" href="index.php?page=ticket&download='.$attachment['uid'].'" style="text-decoration:none">
			<i class="fa fa-file text-info text-120"><!----></i>
			&nbsp;'.$attachment['real_filename'].'
		</a>';
		if($rright['ticket_attachment_delete']) {echo '&nbsp;<a title="'.T_('Supprimer ce fichier').'" onClick="javascript: return confirm(\''.T_('Êtes-vous sur de vouloir supprimer ce fichier ?').'\');" href="./index.php?page=ticket&amp;id='.$_GET['id'].'&amp;'.$url_get_parameters.'&amp;delete_file='.$attachment['id'].'" style="text-decoration: none;"> <i class="fa fa-trash text-danger"><!----></i></a>';}
		if(file_exists('upload/ticket/'.$attachment['storage_filename']))
		{
			$file_size=filesize('upload/ticket/'.$attachment['storage_filename']);
			if($file_size> 1000000) //display in MB
			{
				$file_size=round($file_size/1024/1024,1);
				echo '&nbsp;&nbsp;('.$file_size.' Mo)';
			} else { //display in KB
				$file_size=round($file_size/1024,0);
				echo '&nbsp;&nbsp;('.$file_size.' Ko)';
			}
		} else {
			echo ' ('.T_('Le fichier a été supprimé du serveur').')';
		}
	}
}
$qry->closeCursor();
?>