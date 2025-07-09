<?php
################################################################################
# @Name : ./includes/ticket_fusion.php
# @Description : fusion of 2 tickets
# @Call : /core/ticket.php
# @Author : Flox
# @Update : 10/08/2021
# @Update : 17/01/2024
# @Version : 3.2.47
################################################################################

//init and secure var
require_once(__DIR__.'/../core/init_post.php');
require_once(__DIR__.'/../core/init_get.php');

//prevent direct access
if(!isset($_SESSION['user_id'])) {header('HTTP/1.0 403 Forbidden'); exit;}

if($_POST['target_ticket'] && $rright['ticket_fusion'])
{
    $source_ticket=$_GET['id'];
    $target_ticket=$_POST['target_ticket'];
    
    //check ticket id formation
    if(!is_numeric($source_ticket) || !is_numeric($target_ticket)) {echo DisplayMessage('error',T_('Numéro de ticket invalide')); exit;}

    //check target_ticket
    $qry=$db->prepare("SELECT `id` FROM `tincidents` WHERE `id`=:id AND `disable`='0'");
    $qry->execute(array('id' => $target_ticket));
    $row=$qry->fetch();
    $qry->closeCursor();
    if(empty($row)) {echo DisplayMessage('error',T_('Ticket de destination inexistant')); exit;}

    //check same ticket
    if($source_ticket==$target_ticket){echo DisplayMessage('error',T_('Le ticket de destination doit être différent du ticket source')); exit;}

    //add thread type for fusion
    /* TODO */ 

    //get current user name
    $qry=$db->prepare("SELECT `firstname`,`lastname` FROM `tusers` WHERE id=:id");
    $qry->execute(array('id' => $_SESSION['user_id']));
    $user=$qry->fetch();
    $qry->closeCursor();

    //copy threads from source ticket to target ticket
    $qry=$db->prepare("SELECT `date`,`text`,`author`,`private` FROM `tthreads` WHERE `ticket`=:ticket AND `type`='0'");
    $qry->execute(array('ticket' => $source_ticket));
    while($row=$qry->fetch()) 
    {
        //adding fusion tag
        $row['text']='<i>'.T_('Commentaire fusionné du ticket n°').$source_ticket.' '.T_('vers le ticket n°').$target_ticket.', '.T_('le').' '.date('d/m/Y H:i:s').', '.T_('par').' '.$user['firstname'].' '.$user['lastname'].' : </i><br />'.$row['text'];
        //insert in target ticket
        $qry2=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`text`,`author`,`private`) VALUES (:ticket,:date,:text,:author,:private)");
        $qry2->execute(array('ticket' => $target_ticket,'date' => $row['date'],'text' => $row['text'],'author' => $row['author'],'private' => $row['private']));
    }
    $qry->closeCursor();

    //update events 
    $qry=$db->prepare("UPDATE `tevents` SET `incident`=:target WHERE `incident`=:source");
    $qry->execute(array('target' => $target_ticket,'source' => $source_ticket));

    //update project task 
    $qry=$db->prepare("UPDATE `tprojects_task` SET `ticket_id`=:target WHERE `ticket_id`=:source");
    $qry->execute(array('target' => $target_ticket,'source' => $source_ticket));

    //update attachements
    $qry=$db->prepare("SELECT `id`,`storage_filename`,`real_filename` FROM `tattachments` WHERE ticket_id=:ticket_id");
    $qry->execute(array('ticket_id' => $source_ticket));
    while($attachment=$qry->fetch()) 
    {
        //security check
        if(preg_match('/\.php/', strtolower($attachment['storage_filename']))){ echo 'ERROR : invalid filename'; exit;} 

        //rename storage filename
        $new_storage_filename=explode('_',$attachment['storage_filename']);
        $new_storage_filename=$target_ticket.'_'.$new_storage_filename[1];
        rename("upload/ticket/$attachment[storage_filename]", "upload/ticket/$new_storage_filename");
    
        //update ticket_id and storage filename
        $qry2=$db->prepare("UPDATE `tattachments` SET `ticket_id`=:ticket_id,`storage_filename`=:storage_filename WHERE `id`=:id");
        $qry2->execute(array('ticket_id' => $target_ticket,'storage_filename' => $new_storage_filename,'id' => $attachment['id']));
    }
    $qry->closeCursor();

    //delete source ticket
    DeleteTicket($source_ticket);

	echo DisplayMessage('success',T_('Ticket fusionné'));
	echo "<SCRIPT LANGUAGE='JavaScript'>
			<!--
			function redirect()
			{
			window.location='./index.php?page=ticket&id=$target_ticket&userid=$_GET[userid]&state=$_GET[state]'
			}
			setTimeout('redirect()',$rparameters[time_display_msg]);
			-->
	</SCRIPT>";
} elseif($rright['ticket_fusion']) {
	//form fusion
	echo '
	<div class="modal fade" id="fusion" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="ModalLabel"><i class="fa fa-sitemap text-grey pr-2"><!----></i>'.T_("Fusion de ticket").'</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form name="form8" method="POST" action="" id="form8">
                        <label for="target_ticket">'.T_('Fusionner ce ticket dans le ticket n° ').' :</label>
                        <input type="text" style="width:100px" class="form form-control d-inline" name="target_ticket" id="target_ticket" >
					</form>
                    <div class="alert bgc-danger-l2 text-dark-m2 border-none border-l-4 brc-danger radius-1 d-inline-block mt-4">
                       <i class="fas fa-exclamation-triangle mr-1 text-danger-m1 align-middle"></i>
                       '.T_('Seules les annotations de la section résolution et les pièces jointes seront transférées sur le ticket de destination, ce ticket sera supprimé.').'
                    </div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-success" onclick="if(confirm(\''.T_('Êtes-vous sur de vouloir fusionner ce ticket ? les données autres que les commentaires et les pièces jointes seront définitivement supprimées de ce ticket').'\')) $(\'form#form8\').submit();"><i class="fa fa-check"><!----></i> '.T_('Fusionner').'</button>
					<button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times pr-2"><!----></i>'.T_('Annuler').'</button>
				</div>
			</div>
		</div>
	</div>
	';
}
?>