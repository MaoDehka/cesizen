<?php
################################################################################
# @Name : ticket_calendar.php
# @Description : add event and plan ticket
# @Call : ./core/ticket.php
# @Parameters :  
# @Author : Flox
# @Create : 16/02/2020
# @Update : 17/01/2024
# @Version : 3.2.47
################################################################################

//init and secure var
require_once(__DIR__.'/../core/init_post.php');
require_once(__DIR__.'/../core/init_get.php');

//prevent direct access
if(!isset($_SESSION['user_id'])) {header('HTTP/1.0 403 Forbidden'); exit;}

//add right control
if(!$rright['planning'] || !$rparameters['planning'] || !$rright['ticket_calendar']) {echo 'ERROR : no calendar rights'; exit;}

if($_POST['planification'] && $_POST['add_calendar_start'] && $_POST['add_calendar_end']){
	$_POST['add_calendar_start']=htmlspecialchars($_POST['add_calendar_start'], ENT_QUOTES, 'UTF-8');
	$_POST['add_calendar_end']=htmlspecialchars($_POST['add_calendar_end'], ENT_QUOTES, 'UTF-8');
	$qry=$db->prepare("INSERT INTO `tevents` (`technician`,`incident`,`date_start`,`date_end`,`type`,`title`,`classname`) VALUES (:technician,:incident,:date_start,:date_end,'2',:title,'badge-success')");
	$qry->execute(array(
		'technician' => $globalrow['technician'],
		'incident' => $_GET['id'],
		'date_start' => DatetimeToDB($_POST['add_calendar_start']),
		'date_end' => DatetimeToDB($_POST['add_calendar_end']),
		'title' => "Ticket $_GET[id] : $globalrow[title]"
	));
	
	//redirect
	$www = "./index.php?page=ticket&id=$_GET[id]&userid=$_GET[userid]";
	echo '<script language="Javascript">
	<!--
	document.location.replace("'.$www.'");
	// -->
	</script>';
}

if($_POST['reminder'] && $_POST['add_reminder'])
{
	$_POST['add_reminder']=htmlspecialchars($_POST['add_reminder'], ENT_QUOTES, 'UTF-8');

	if($_POST['end']='0000-00-00 00:00:00' && $_POST['add_reminder']) 
	{
		$_POST['end']=DatetimeToDB($_POST['add_reminder']);
		$_POST['end'] = date('Y-m-d H:i:s', strtotime($_POST['end']. ' + 1 hours'));
	}

	$qry=$db->prepare("INSERT INTO `tevents` (`technician`,`incident`,`date_start`,`date_end`,`type`,`title`,`classname`) VALUES (:technician,:incident,:date_start,:date_end,'1',:title,'badge-warning')");
	$qry->execute(array(
		'technician' => $_SESSION['user_id'],
		'incident' => $_GET['id'],
		'date_start' => DatetimeToDB($_POST['add_reminder']),
		'date_end' => $_POST['end'],
		'title' => "Rappel ticket $_GET[id] : $globalrow[title]"
	));
	//redirect
	$www = "./index.php?page=ticket&id=$_GET[id]&userid=$_GET[userid]";
	echo '<script language="Javascript">
	<!--
	document.location.replace("'.$www.'");
	// -->
	</script>';
	
}

//form add planification
echo '
<div class="modal fade" id="add_planification" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="ModalLabel"><i class="fa fa-calendar text-info pr-2"><!----></i>'.T_("Planifier une intervention").'</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form name="form5" method="POST" action="" id="form5">
					<input name="planification" type="hidden" value="1">
					<div class="form-group row p-0 m-0">
						<div class="col-sm-4 col-form-label text-sm-right pt-1">
							<label for="add_calendar_start">'.T_('Début').' :</label>
						</div>
						<div class="col-sm-5">
							<input autocomplete="off" class="form-control form-control-sm datetimepicker-input" data-toggle="datetimepicker" data-target="#add_calendar_start" type="text" id="add_calendar_start" name="add_calendar_start" />
						</div>
					</div>
					<div class="form-group row p-0 m-0">
						<div class="col-sm-4 col-form-label text-sm-right pt-1">
							<label for="add_calendar_end">'.T_('Fin').' :</label>
						</div>
						<div class="col-sm-5">
							<input autocomplete="off" class="form-control form-control-sm datetimepicker-input" data-toggle="datetimepicker" data-target="#add_calendar_end" type="text" name="add_calendar_end" id="add_calendar_end" />
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="$(\'form#form5\').submit();" ><i class="fa fa-check pr-2"><!----></i>'.T_('Ajouter').'</button>
				<button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times pr-2"><!----></i>'.T_('Annuler').'</button>
			</div>
		</div>
	</div>
</div>
';

//form add reminder
echo '
<div class="modal fade" id="add_event" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="ModalLabel"><i class="fa fa-bell text-warning pr-2"><!----></i>'.T_("Ajouter un rappel").'</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form name="form6" method="POST" action="" id="form6">
					<input name="reminder" type="hidden" value="1">
					<div class="form-group row p-0 m-0">
						<div class="col-sm-4 col-form-label text-sm-right pt-1">
							<label for="add_reminder">'.T_('Date et heure').' :</label>
						</div>
						<div class="col-sm-5 ">
							<input autocomplete="off" class="form-control form-control-sm datetimepicker-input" data-toggle="datetimepicker" data-target="#add_reminder" type="text" id="add_reminder" name="add_reminder" />
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="$(\'form#form6\').submit();" ><i class="fa fa-check pr-2"><!----></i>'.T_('Ajouter').'</button>
				<button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times pr-2"><!----></i>'.T_('Annuler').'</button>
			</div>
		</div>
	</div>
</div>
';
?>