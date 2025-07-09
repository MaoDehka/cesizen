<?php
################################################################################
# @Name : ticket_user.php
# @Description : add and modify user
# @Call : ./core/ticket.php
# @Parameters :  
# @Author : Flox
# @Create : 16/01/2020
# @Update : 01/12/2022
# @Update : 17/01/2024
# @Version : 3.2.47
################################################################################

//init and secure var
require_once(__DIR__.'/../core/init_post.php');
require_once(__DIR__.'/../core/init_get.php');

//prevent direct access
if(!isset($_SESSION['user_id'])) {header('HTTP/1.0 403 Forbidden'); exit;}

if($_POST['user']) {$selecteduser=$_POST['user'];} else {$selecteduser=$globalrow['user'];} 

//user add form
echo '
<div class="modal fade" id="user_add_modal" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="ModalLabel"><i class="fa fa-user text-info pr-2"><!----></i>'.T_('Ajouter un nouvel utilisateur').'</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form name="user_add_form" method="POST" action="" id="user_add_form">
				<div class="modal-body">
					<input name="add" type="hidden" value="1">
					<div class="form-group row p-0 m-0">
						<div class="col-sm-4 col-form-label text-sm-right pt-1">
							<label for="firstname">'.T_('Prénom').' :</label> 
						</div>
						<div class="col-sm-7 ">
							<input class="form-control form-control-sm d-inline-block" name="firstname" id="firstname" type="text" >
						</div>
					</div>
					<div class="form-group row p-0 m-0">
						<div class="col-sm-4 col-form-label text-sm-right pt-1">
							<label for="lastname">'.T_('Nom').' :</label> 
						</div>
						<div class="col-sm-7 ">
							<input class="form-control form-control-sm d-inline-block" name="lastname" id="lastname" type="text" >
						</div>
					</div>
					<div class="form-group row p-0 m-0">
						<div class="col-sm-4 col-form-label text-sm-right pt-1">
							<label for="phone">'.T_('Tél. fixe').' :</label> 
						</div>
						<div class="col-sm-7 ">
							<input class="form-control form-control-sm d-inline-block" name="phone" id="phone" type="text" autocomplete="On" >
						</div>
					</div>
					<div class="form-group row p-0 m-0">
						<div class="col-sm-4 col-form-label text-sm-right pt-1">
							<label for="mobile">'.T_('Tél. portable').' :</label> 
						</div>
						<div class="col-sm-7 ">
							<input class="form-control form-control-sm d-inline-block" name="mobile" id="mobile" type="text" >
						</div>
					</div>
					<div class="form-group row p-0 m-0">
						<div class="col-sm-4 col-form-label text-sm-right pt-1">
							<label for="usermail">'.T_('Mail').' :</label> 
						</div>
						<div class="col-sm-7 ">
							<input class="form-control form-control-sm d-inline-block" name="usermail" id="usermail" type="email" >
						</div>
					</div>
					';
					//display advanced user informations
					if($rparameters['user_advanced'])
					{
						echo '
						<div class="form-group row p-0 m-0">
							<div class="col-sm-4 col-form-label text-sm-right pt-1">
								<label for="company">'.T_('Société').' :</label><br />
							</div>
							<div class="col-sm-7">
								<select style="width:210px;" id="company" name="company" autocomplete="On">
									<option value=""></option>
									';
									$qry=$db->prepare("SELECT `id`,`name` FROM `tcompany` ORDER BY `name`ASC");
									$qry->execute();
									while($row=$qry->fetch()) {echo '<option value="'.$row['id'].'">'.T_($row['name']).'</option>';}
									$qry->closeCursor();
									echo '
								</select>
								<span class="d-inline-block">
								<a class="d-inline-block" target="blank" href="./index.php?page=admin&amp;subpage=list&amp;table=tcompany&amp;action=disp_add"><i class="fa fa-plus-circle text-success text-130 pl-1" title="'.T_('Ajouter une société').'" ><!----></i></a>
								</span>
							</div>
						</div>
						';
					}
					echo '
					<a target="blank" href="./index.php?page=admin&amp;subpage=user&amp;action=add">'.T_('Plus de champs').'...</a>
				</div>
				<div class="modal-footer">
					<button type="button" id="user_add_button" class="btn btn-success" ><i class="fa fa-check pr-2"><!----></i>'.T_('Ajouter').'</button>
					<button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times pr-2"><!----></i>'.T_('Annuler').'</button>
				</div>
			</form>
		</div>
	</div>
</div>
';

//user edit form
$qry=$db->prepare("SELECT `firstname`,`lastname`,`phone`,`mobile`,`mail`,`company` FROM `tusers` WHERE id=:id");
$qry->execute(array('id' => $selecteduser));
$userform=$qry->fetch();
$qry->closeCursor();

echo '
<div class="modal fade" id="user_modify_modal" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="ModalLabel"><i class="fa fa-user text-info pr-2"><!----></i>'.T_('Modifier un utilisateur').'</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form name="user_modify_form" method="POST" action="" id="user_modify_form">
				<div class="modal-body">
						<input name="modifyuser" type="hidden" value="1">
						<input name="user_id_edit" id="user_id_edit" type="hidden" value="'.$selecteduser.'">
						<div class="form-group row p-0 m-0">
							<div class="col-sm-4 col-form-label text-sm-right pt-1">
								<label for="firstname">'.T_('Prénom').' :</label> 
							</div>
							<div class="col-sm-5 ">
								<input class="form-control form-control-sm d-inline-block" name="firstname" id="firstname_edit" type="text" value="'.$userform['firstname'].'" >
							</div>
						</div>
						<div class="form-group row p-0 m-0">
							<div class="col-sm-4 col-form-label text-sm-right pt-1">
								<label for="lastname">'.T_('Nom').' :</label> 
							</div>
							<div class="col-sm-5 ">
								<input class="form-control form-control-sm d-inline-block" name="lastname" id="lastname_edit" type="text" value="'.$userform['lastname'].'" >
							</div>
						</div>
						<div class="form-group row p-0 m-0">
							<div class="col-sm-4 col-form-label text-sm-right pt-1">
								<label for="phone">'.T_('Tél. fixe').' :</label> 
							</div>
							<div class="col-sm-5 ">
								<input class="form-control form-control-sm d-inline-block" name="phone" id="phone_edit" type="text" value="'.$userform['phone'].'" autocomplete="On">
							</div>
						</div>
						<div class="form-group row p-0 m-0">
							<div class="col-sm-4 col-form-label text-sm-right pt-1">
								<label for="mobile">'.T_('Tél. portable').' :</label> 
							</div>
							<div class="col-sm-5 ">
								<input class="form-control form-control-sm d-inline-block" name="mobile" id="mobile_edit" type="text" value="'.$userform['mobile'].'" >
							</div>
						</div>
						<div class="form-group row p-0 m-0">
							<div class="col-sm-4 col-form-label text-sm-right pt-1">
								<label for="usermail">'.T_('Mail').' :</label> 
							</div>
							<div class="col-sm-5 ">
								<input class="form-control form-control-sm d-inline-block" name="usermail" id="usermail_edit" type="email" value="'.$userform['mail'].'" >
							</div>
						</div>
						';
						//display advanced user informations
						if($rparameters['user_advanced'])
						{
							echo '
							<div class="form-group row p-0 m-0">
								<div class="col-sm-4 col-form-label text-sm-right pt-1">
									<label for="company">'.T_('Société').' :</label><br />
								</div>
								<div class="col-sm-5">
									<select class="form-control col-9 d-inline-block" id="company_edit" name="company" autocomplete="On">';
										$qry=$db->prepare("SELECT `id`,`name` FROM `tcompany` ORDER BY `name`ASC");
										$qry->execute();
										while($row=$qry->fetch()) {
											if($row['id']==$userform['company'])
											{
												echo '<option selected value="'.$row['id'].'">'.T_($row['name']).'</option>';
											} else {
												echo '<option value="'.$row['id'].'">'.T_($row['name']).'</option>';
											}
										}
										$qry->closeCursor();
										echo '
									</select>
									<a target="blank" href="./index.php?page=admin&amp;subpage=list&amp;table=tcompany&amp;action=disp_add"><i class="fa fa-plus-circle text-success text-130 pl-1" title="'.T_('Ajouter une société').'" ><!----></i></a>
								</div>
							</div>
							';
						}
						echo '
						<a target="blank" href="./index.php?page=admin&amp;subpage=user&amp;action=edit&amp;userid='.$selecteduser.'">'.T_('Plus de champs').'...</a>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-success" id="user_modify_button"  ><i class="fa fa-check pr-2"><!----></i>'.T_('Modifier').'</button>
					<button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times pr-2"><!----></i>'.T_('Annuler').'</button>
				</div>
			</form>
		</div>
	</div>
</div>
';
?>
<script>
	//////////////////////////////////////////////////////////////////////////////// AJAX ADD USER

	//variable to hold request
	var request;
	// Bind to the submit event of our form
	$("#user_add_button").click(function () {
		//prevent default posting of form - put here to work in case of errors
		event.preventDefault();
		//abort any pending request
		if (request) {request.abort();}
		//get form
		var $form = $("#user_add_form");
		//select and cache all the fields
		var $inputs = $form.find("input, select, button, textarea");
		//serialize the data in the form
		var serializedData = $form.serialize();
		//init var
		var cancel = 0;

		//check if user exist
		request = $.ajax({
			url: "ajax/ticket_user_check.php?token=<?php echo $token; ?>",
			type: "post",
			data: serializedData
		});
		//callback handler that will be called on success
		request.done(function (response, textStatus, jqXHR){
			//log a message to the console
			<?php if($rparameters['debug']) {echo 'console.log("ticket_user_check.php"+response);';}?>
			var response = JSON.parse(response);
			if(response.find_user && response.find_user == 'mail')
			{
				if (confirm("<?php echo T_("Un autre utilisateur possède déjà cette adresse mail, poursuivre ?"); ?>") == true) {cancel = 0;} else {cancel = 1;}
			}
			if(response.find_user && response.find_user == 'mobile')
			{
				if (confirm("<?php echo T_("Un autre utilisateur possède déjà ce numéro de portable, poursuivre ?"); ?>") == true) {cancel = 0;} else {cancel = 1;}
			}
			
			//user add
			if(!cancel)
			{
				request = $.ajax({
					url: "ajax/ticket_user.php?token=<?php echo $token; ?>",
					type: "post",
					data: serializedData
				});
				//callback handler that will be called on success
				request.done(function (response, textStatus, jqXHR){
					//log a message to the console
					<?php if($rparameters['debug']) {echo 'console.log("ticket_user.php"+response);';}?>
					var response = JSON.parse(response);
					//modal close
					$("#user_add_modal").modal("hide");
					//update user field
					var $select = $("#user").selectize();
					var selectize = $select[0].selectize;
					selectize.addOption({value:response.user_id,text:response.lastname+' '+response.firstname});
					selectize.setValue(response.user_id);
				});
				//callback handler that will be called on failure
				request.fail(function (jqXHR, textStatus, errorThrown){
					//log the error to the console
					console.error(
						"The following error occurred: "+
						textStatus, errorThrown
					);
				});
			}
		});
	});

	//////////////////////////////////////////////////////////////////////////////// AJAX UPDATE USER

	//Variable to hold request
	var request;
	// Bind to the submit event of our form
	$("#user_modify_button").click(function () {
		//prevent default posting of form - put here to work in case of errors
		event.preventDefault();
		//abort any pending request
		if (request) {
			request.abort();
		}
		//get form
		var $form = $("#user_modify_form");
		//select and cache all the fields
		var $inputs = $form.find("input, select, button, textarea");
		//serialize the data in the form
		var serializedData = $form.serialize();
		//get input user_id
		user_id=document.getElementById('user_id_edit').value;
		//fire off the request to db
		if(user_id!=0)
		{
			request = $.ajax({
				url: "ajax/ticket_user.php?user_id="+user_id+"&token=<?php echo $token; ?>",
				type: "post",
				data: serializedData
			});
		} else {
			request = $.ajax({
				url: "ajax/ticket_user.php?user_id=<?php echo $selecteduser; ?>&token=<?php echo $token; ?>",
				type: "post",
				data: serializedData
			});
		}
		
		//callback handler that will be called on success
		request.done(function (response, textStatus, jqXHR){
			//log a message to the console
			<?php if($rparameters['debug']) {echo 'console.log(response);';}?>
			var response = JSON.parse(response);
			//modal close
			$("#user_modify_modal").modal("hide");
			//update user field
			var $select = $("#user").selectize();
			var selectize = $select[0].selectize;
			selectize.removeOption(response.user_id);
			selectize.addOption({value:response.user_id,text:response.lastname+' '+response.firstname});
			selectize.setValue(response.user_id);
		});
		//callback handler that will be called on failure
		request.fail(function (jqXHR, textStatus, errorThrown){
			//log the error to the console
			console.error(
				"The following error occurred: "+
				textStatus, errorThrown
			);
		});
	});
</script>