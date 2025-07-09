<?php
################################################################################
# @Name : procedure.php
# @Description : display, edit and add procedure
# @Call : /index.php
# @Parameters : 
# @Author : Flox
# @Create : 03/09/2013
# @Update : 30/01/2024
# @Version : 3.2.47
################################################################################

//secure direct access
if(!isset($_GET['page'])) {echo 'ERROR : invalid access'; exit;}

//init var
if($_GET['order']=='') {$_GET['order']='id';}
if($_GET['way']=='') {$_GET['way']='DESC';}

//secure way
if($_GET['way']!='DESC' && $_GET['way']!='ASC') {$_GET['way']='DESC';}

if(!$rright['procedure']) {echo DisplayMessage('error',T_('La fonction procédure est désactivée')); exit;}

//generate procedure token access for ajax call
$token = bin2hex(random_bytes(32));
$qry=$db->prepare("INSERT INTO `ttoken` (`date`,`token`,`action`,`procedure_id`,`user_id`,`ip`) VALUES (NOW(),:token,'procedure_access',:procedure_id,:user_id,:ip)");
$qry->execute(array('token' => $token,'procedure_id' => $_GET['id'],'user_id' => $_SESSION['user_id'],'ip' => $_SERVER['REMOTE_ADDR']));
echo '<input type="hidden" id="token" value="'.$token.'" />';

//delete procedure
if($_GET['action']=='delete' && $rright['procedure_delete'])
{
	//disable procedure
	$qry=$db->prepare("UPDATE `tprocedures` SET `disable`='1' WHERE `id`=:id");
	$qry->execute(array('id' => $_GET['id']));
	//display delete message
	echo DisplayMessage('success',T_('Procédure supprimée'));
	//redirect
	$www = "./index.php?page=procedure";
	echo "<SCRIPT LANGUAGE='JavaScript'>
		<!--
		function redirect()
		{
		window.location='$www'
		}
		setTimeout('redirect()',$rparameters[time_display_msg]);
		-->
	</SCRIPT>";
}

//if delete attachment file
if($_GET['delete_file'] && $rright['procedure_modify'])
{
	//get file information to delete in database and file
	$qry=$db->prepare("SELECT `storage_filename` FROM `tattachments` WHERE uid=:uid");
	$qry->execute(array('uid' => $_GET['delete_file']));
	$file=$qry->fetch();
	$qry->closeCursor();

	//remove file
	if(file_exists('upload/procedure/'.$file['storage_filename'])) {unlink('upload/procedure/'.$file['storage_filename']);}

	//update database
	$qry=$db->prepare("DELETE FROM `tattachments` WHERE uid=:uid");
	$qry->execute(array('uid' => $_GET['delete_file']));
	
	//display delete message
	echo DisplayMessage('success',T_('Fichier supprimé'));

	//redirect
	$www = './index.php?page=procedure&action=edit&id='.$_GET['id'];
	echo "<SCRIPT LANGUAGE='JavaScript'>
		<!--
		function redirect()
		{
		window.location='$www'
		}
		setTimeout('redirect()',$rparameters[time_display_msg]);
		-->
	</SCRIPT>";
}

//start procedure form
if($_GET['action']=='add' || ($_GET['action']=='edit' && $_GET['id']))
{
	if($_GET['action']=='add')
	{
		$page_title=T_("Ajout d'une procédure");
		$procedure['name']='';
		$procedure['company_id']='';
		$procedure['category']='';
		$procedure['subcat']='';
		$procedure['text']='';
	} elseif($_GET['action']=='edit' && $_GET['id'])
	{
		//get data of current selected procedure
		$qry=$db->prepare("SELECT * FROM `tprocedures` WHERE id=:id");
		$qry->execute(array('id' => $_GET['id']));
		$procedure=$qry->fetch();
		$qry->closeCursor();

		$page_title=T_('Procédure').' n°'.$procedure['id'].' : '.$procedure['name'];

		//detect <br> for wysiwyg transition from 2.9 to 3.0
		$findbr=stripos($procedure['text'], '<br>');
		if($findbr === false) {$procedure['text']=nl2br($procedure['text']);} else {$procedure['text']=$procedure['text'];}
	} 
	//ACL check 
	if($rright['procedure_list_company_only'] && $procedure['company_id']!=$ruser['company']) {echo DisplayMessage('error',T_("Vous n'avez pas accès à cette procédure")); exit;}
	if(!$rright['procedure_add'] && $_GET['action']=='add') {echo DisplayMessage('error',T_("Vous n'avez pas le droit d'ajouter une procédure")); exit;}

	echo '
	<div class="card bcard mt-2" id="card-1">
		<div class="card-header">
			<h5 class="card-title">
				<i class="fa fa-book"><!----></i> '.$page_title.'
			</h5>
		</div><!-- /.card-header -->
		<div class="card-body p-0">
			<!-- to have smooth .card toggling, it should have zero padding -->
			<div class="p-3">
				<form name="myform" id="myform" method="POST" enctype="multipart/form-data"  action="" onsubmit="loadVal();" >
					<label for="name">'.T_('Nom').' :</label>
					<input name="name" id="name" style="width:auto;" class="form-control form-control-sm d-inline-block mb-2" type="text" value="'.$procedure['name'].'" '; if(!$rright['procedure_modify']) {echo 'readonly="readonly"';} echo ' autocomplete="off">
					<br />
					';
					//company field
					if($rright['procedure_company'])
					{
						echo '
						<label for="company">'.T_('Société').' :</label>
						<select id="company" name="company" style="width:auto;" class="form-control form-control-sm d-inline-block mb-2" autocomplete="off">
							';
							$qry=$db->prepare("SELECT `id`,`name` FROM `tcompany` WHERE `disable`='0' ORDER BY `name`");
							$qry->execute();
							while($row=$qry->fetch()) 
							{
								if($procedure['company_id']==$row['id']) {echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';} else {echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';}
							}
							$qry->closeCursor();
							echo '
						</select>
						<br />
						';
					}

					//category field
					echo '
					<label for="category">'.T_('Catégorie').' :</label>
					<select title="'.T_('Catégorie').'"  name="category" id="category" style="width:auto;" class="form-control form-control-sm d-inline-block mb-2" '; if(!$rright['procedure_modify']) {echo 'disabled="disabled"';} echo ' >
						';
						$qry=$db->prepare("SELECT `id`,`name` FROM `tcategory` ORDER BY `name`");
						$qry->execute();
						while($row=$qry->fetch()) 
						{
							if($procedure['category']==$row['id']){echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';} else {echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';} 
						}
						$qry->closeCursor(); 
						echo '
					</select>
					';

					//subcat field
					echo '
					<select title="'.T_('Sous-catégorie').'" name="subcat" id="subcat" style="width:auto;" class="form-control form-control-sm d-inline-block mb-2" '; if(!$rright['procedure_modify']) {echo 'disabled="disabled"';} echo '>
						<option value="0">'.T_('Aucune').'</option>
						';
						$qry= $db->prepare("SELECT `id`,`name` FROM `tsubcat` WHERE `cat` LIKE :cat ORDER BY `name` ASC");
						$qry->execute(array('cat' => $procedure['category']));
						while($row=$qry->fetch()) 
						{
							if($procedure['subcat']==$row['id']) {echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';} else {echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';}
						}
						$qry->closeCursor();	
						echo '
					</select>
					<br />
					';

					//text field
					if(!$rright['procedure_modify']) 
					{echo '<label for="procedure">'.T_('Procédure').' :</label><br /><div class="ml-2 mb-2" >'.$procedure['text'].'</div>';} 
					else
					{
						echo '
						<table class="mb-2" border="1" style="border: 1px solid #D8D8D8;" >
							<tr>
								<td>
									<div id="editor" class="bootstrap-wysiwyg-editor px-3 py-2" style="min-height:100px;">'.$procedure['text'].'</div>
								</td>
							</tr>
						</table>
						';
					}

					//attachment
					echo '<label class="mb-2" for="procedure_file">'.T_('Pièce jointe').' :&nbsp;</label>';
					if($rright['procedure_modify']) {
						echo '<input id="procedure_file" name="procedure_file" type="file" style="display:inline" />';
					}
					echo '<div id="uploaded_file"></div>';
					if($_GET['id'])
					{
						//listing of attach file
						$qry=$db->prepare("SELECT `uid`,`storage_filename`,`real_filename` FROM `tattachments` WHERE procedure_id=:procedure_id");
						$qry->execute(array('procedure_id' => $_GET['id']));
						while($row=$qry->fetch()) 
						{
							if(file_exists('upload/procedure/'.$row['storage_filename']))
							{
								echo '
								<i class="fa fa-paperclip text-primary-m2 ml-2"><!----></i> 
								<a target="_blank" title="'.T_('Télécharger le fichier').' '.$row['real_filename'].'" href="index.php?page=procedure&download='.$row['uid'].'">'.$row['real_filename'].'</a>
								';
								if($rright['procedure_modify']) {echo '<a href="./index.php?page=procedure&id='.$_GET['id'].'&action=edit&delete_file='.$row['uid'].'" title="'.T_('Supprimer').'"<i class="fa fa-trash text-danger"><!----></i></a>';}
								echo '
								<br />
								';
							} else {
								echo DisplayMessage('error',T_('Le fichier').' '.$row['storage_filename'].' '.T_('à été supprimé du serveur'));
							}
						}
						$qry->closeCursor();
					}

					//send data to ajax
					echo '
					<input type="hidden" name="text" />
					<input type="hidden" name="text2" />
					<input type="hidden" name="language" value="'.$ruser['language'].'"  />
					<input type="hidden" name="token" value="'.$token.'" />
					<input type="hidden" name="action" value="'.$_GET['action'].'"  />
					';

					//footer form
					echo '
					<div class="border-t-1 brc-secondary-l1 bgc-secondary-l3 py-3 text-center mt-5">
						';
						if($rright['procedure_modify']) {
							echo '<button name="edit" id="edit" type="submit" class="btn btn-success mr-2"><i class="fa fa-save bigger-110 mr-2"><!----></i>'.T_('Enregistrer').'</button>';
						}
						echo '
						<a href="index.php?page=procedure" class="btn btn-danger"><i class="fa fa-reply bigger-110 mr-2"><!----></i>'.T_('Retour').'</a>
					</div>
				</form>
			</div>
		</div>
	</div>
	';

} else {
	//////////////////////////////////////////////////////////////// START PROCEDURE LIST ///////////////////////////////////////////////////////////
	
	if(!$procedurekeywords) {$procedurekeywords='%';} else {$procedurekeywords='%'.$procedurekeywords.'%';}
	if($rright['procedure_list_company_only'])
	{
		//get name of company of current user
		$qry=$db->prepare("SELECT `name` FROM `tcompany` WHERE id=:id AND disable='0'");
		$qry->execute(array('id' => $ruser['company']));
		$company=$qry->fetch();
		$qry->closeCursor();
		$company=T_(' de la société ').$company['name'];
		
		//count procedure
		$qry=$db->prepare("SELECT COUNT(*) FROM `tprocedures` WHERE `company_id`=:company_id AND (`text` LIKE :text OR `name` LIKE :text) AND `disable`=0");
		$qry->execute(array('company_id' => $ruser['company'], 'text' => $procedurekeywords));
		$row=$qry->fetch();
		$qry->closeCursor();
	} else {
		$company='';
		$qry=$db->prepare("SELECT COUNT(*) FROM `tprocedures` WHERE (`text` LIKE :text OR `name` LIKE :text) AND `disable`='0'");
		$qry->execute(array('text' => $procedurekeywords));
		$row=$qry->fetch();
		$qry->closeCursor();
	}
	if($_GET['way']=='ASC') $arrow_way='DESC'; else $arrow_way='ASC';
	echo '
		<div class="page-header position-relative">
			<h1 class="page-title text-primary-m2">
				<i class="fa fa-book text-primary-m2"><!----></i> 
				'.T_('Liste des procédures').$company.'
				<small class="page-info text-secondary-d2">
					<i class="fa fa-angle-double-right text-80"><!----></i>
					&nbsp;'.T_('Nombre').' : '.$row[0].' &nbsp;&nbsp;
				</small>
			</h1>
		</div>
		<div class="mt-4 mt-lg-0 card bcard h-auto overflow-hidden shadow border-0">
			<div class="card-body p-0 table-responsive-xl">
				<table id="sample-table-1" class="table table-bordered table-bordered table-striped table-hover text-dark-m2">
					<thead>
						<tr>
							<th class="'; if($_GET['order']=='id') {echo 'active';} echo '" >
								<a class="text-primary-m2" title="'.T_('Numéro').'" href="./index.php?page=procedure&amp;order=id&amp;way='.$arrow_way.'">
									<i class="fa fa-tag"><!----></i> '.T_('Numéro');
									//Display arrows
									if($_GET['order']=='id'){
										if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
										if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
									}
									echo '
								</a>
							</th>';
							if($rright['procedure_company']) {
								echo '
								<th class="'; if($_GET['order']=='company_id') {echo 'active';} echo '" >
									<a class="text-primary-m2" title="'.T_('Société').'" href="./index.php?page=procedure&amp;order=company_id&amp;way='.$arrow_way.'">
										<i class="fa fa-building"><!----></i> '.T_('Société');
										//Display arrows
										if($_GET['order']=='company_id'){
											if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
											if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
										}
										echo '
									</a>
								</th>';
							}
							echo '
							<th class="'; if($_GET['order']=='category') {echo 'active';} echo '" >
								<a class="text-primary-m2" title="'.T_('Catégorie').'" href="./index.php?page=procedure&amp;order=category&amp;way='.$arrow_way.'">
									<i class="fa fa-square"><!----></i> '.T_('Catégorie');
									//Display arrows
									if($_GET['order']=='category'){
										if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
										if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
									}
									echo '
								</a>
							</th>
							<th class="'; if($_GET['order']=='subcat') {echo 'active';} echo '" >
								<a class="text-primary-m2" title="'.T_('Sous-catégorie').'" href="./index.php?page=procedure&amp;order=subcat&amp;way='.$arrow_way.'">
									<i class="fa fa-sitemap"><!----></i> '.T_('Sous-catégorie');
									//Display arrows
									if($_GET['order']=='subcat'){
										if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
										if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
									}
									echo '
								</a>
							</th>
							<th class="'; if($_GET['order']=='name') {echo 'active';} echo '" >
								<a class="text-primary-m2" title="'.T_('Nom de la procédure').'" href="./index.php?page=procedure&amp;order=name&amp;way='.$arrow_way.'">
									<i class="fa fa-file-alt"><!----></i> '.T_('Nom');
									//Display arrows
									if($_GET['order']=='name'){
										if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
										if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
									}
									echo '
								</a>
							</th>
							<th><i class="fa fa-play"><!----></i> '.T_('Actions').'</th>
						</tr>
						<!-- 
						<tr class="bgc-white text-secondary-d3 text-95">
							<td class="text-center" style="max-width:115px" >
								<input form="filter" class="form-control" name="ticket" onchange="submit();" type="text" value="" />
							</td>		
							<td class="text-center" style="max-width:115px" >
								<select class="form-control" name="ticket" />
								</select>
							</td>		
							<td class="text-center" style="max-width:115px" >
								<select class="form-control" name="ticket" />
								</select>
							</td>		
							<td class="text-center" style="max-width:115px" >
								<select class="form-control" name="ticket" />
								</select>
							</td>		
							<td class="text-center" style="max-width:115px" >
								<input form="filter" class="form-control" name="ticket" onchange="submit();" type="text" value="" />
							</td>		
								
						</tr>
						-->
					</thead>
					<tbody>
						';
							//order by company name
							if($_GET['order']=='company_id') {$_GET['order']='tcompany.name';}

							//limit result to procedure of company of current connected user
							if($rright['procedure_list_company_only'])
							{
								$masterquery=$db->prepare("SELECT `tprocedures`.* 
								FROM `tprocedures`,`tcompany` 
								WHERE 
								`tprocedures`.`company_id`=`tcompany`.`id` AND
								`company_id`=:company_id AND
								(
									`tprocedures`.`text` LIKE :procedurekeywords OR 
									`tprocedures`.`name` LIKE :procedurekeywords
								) AND 
								`tprocedures`.`disable`='0' 
								ORDER BY $_GET[order] $_GET[way]");
								$masterquery->execute(array('procedurekeywords' => $procedurekeywords,'company_id' => $ruser['company']));

							} else {
								$masterquery=$db->prepare("SELECT `tprocedures`.* 
								FROM `tprocedures`,`tcompany` 
								WHERE 
								`tprocedures`.`company_id`=`tcompany`.`id` AND
								(
									`tprocedures`.`text` LIKE :procedurekeywords OR 
									`tprocedures`.`name` LIKE :procedurekeywords
								) AND 
								`tprocedures`.`disable`='0' 
								ORDER BY $_GET[order] $_GET[way]");
								$masterquery->execute(array('procedurekeywords' => $procedurekeywords));

							}
							while($procedure=$masterquery->fetch()) 
							{
								//get category name
								$qry=$db->prepare("SELECT `name` FROM `tcategory` WHERE id=:id");
								$qry->execute(array('id' => $procedure['category']));
								$rcat=$qry->fetch();
								$qry->closeCursor();
								
								//get sub-category name
								$qry=$db->prepare("SELECT `name` FROM `tsubcat` WHERE id=:id");
								$qry->execute(array('id' => $procedure['subcat']));
								$rscat=$qry->fetch();
								$qry->closeCursor();

								//get company name
								$qry=$db->prepare("SELECT `name` FROM `tcompany` WHERE id=:id");
								$qry->execute(array('id' => $procedure['company_id']));
								$rcompany=$qry->fetch();
								$qry->closeCursor();

								if(empty($rcat['name'])) {$rcat=array(); $rcat['name']='';}
								if(empty($rscat['name'])) {$rscat=array(); $rscat['name']='';}

								echo '
								<tr class="bgc-h-orange-l4" >	
									<td onclick="document.location=\'./index.php?page=procedure&amp;id='.$procedure['id'].'&amp;action=edit\'" >'.$procedure['id'].'</td>
									';
									if($rright['procedure_company']) {echo '<td onclick="document.location=\'./index.php?page=procedure&amp;id='.$procedure['id'].'&amp;action=edit\'" >'.$rcompany['name'].'</td>';}
									echo '
									<td onclick="document.location=\'./index.php?page=procedure&amp;id='.$procedure['id'].'&amp;action=edit\'" >'.$rcat['name'].'</td>
									<td onclick="document.location=\'./index.php?page=procedure&amp;id='.$procedure['id'].'&amp;action=edit\'" >'.$rscat['name'].'</td>
									<td onclick="document.location=\'./index.php?page=procedure&amp;id='.$procedure['id'].'&amp;action=edit\'" >'.$procedure['name'].'</td>
									<td>
										';
										//display actions buttons
										if($rright['procedure_modify']) {echo'<a class="action-btn btn btn-sm btn-warning mr-1" href="./index.php?page=procedure&amp;id='.$procedure['id'].'&amp;action=edit" title="'.T_('Modifier cette procédure').'" ><span style="color:#FFF;#"><i class="fa fa-pencil-alt"><!----></i></span></a>';}
										if($rright['procedure_delete']) {echo'<a class="action-btn btn btn-sm btn-danger" onClick="javascript: return confirm(\''.T_('Êtes-vous sur de vouloir supprimer cette procédure ?').'\');" href="./index.php?page=procedure&amp;id='.$procedure['id'].'&amp;action=delete" title="'.T_('Supprimer cette procédure').'" ><i class="fa fa-trash"><!----></i></a>';}
										echo '
									</td>
								</tr>
								';
							}
							$masterquery->closeCursor();
						echo '
					</tbody>
				</table>
			</div>	
		</div>
	';
	//////////////////////////////////////////////////////////////// END PROCEDURE LIST ///////////////////////////////////////////////////////////
}
?>
<!-- procedure scripts  -->
<script type="text/javascript" src="js/procedure.js"></script>