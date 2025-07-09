<?php
################################################################################
# @Name : ticket_category.php
# @Description : add and modify categories
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

//initialize variables 
if(!isset($globalrow['category'])) $globalrow['category'] = '';
if(!isset($globalrow['subcat'])) $globalrow['subcat'] = '';
 
if(!isset($subcat)) $subcat = '';
if(!isset($subcatname)) $subcatname = '';
if(!isset($name)) $name = '';

$_GET['cat']=strip_tags($_GET['cat']);
$_GET['editcat']=strip_tags($_GET['editcat']);
$_POST['subcatname']=strip_tags($_POST['subcatname']);
$_POST['name']=strip_tags($_POST['name']);

$db_cat=strip_tags($db->quote($_GET['cat']));
$db_editcat=strip_tags($db->quote($_GET['editcat']));

if($_POST['cat']) {$selectedcat=$_POST['cat'];} else {$selectedcat=$globalrow['category'];} 
if($_POST['subcat']) {$selectedscat=$_POST['subcat'];} else {$selectedscat=$globalrow['subcat'];} 

if($rright['ticket_cat'])
{
	if($_POST['addsubcat'] && $_POST['subcatname']){
		$qry=$db->prepare("INSERT INTO `tsubcat` (`cat`,`name`) VALUES (:cat,:name)");
		$qry->execute(array('cat' => $selectedcat,'name' => $_POST['subcatname']));
		$_POST['category']=$selectedcat;
	}
	
	if($_POST['modifysubcat'] && $_POST['subcatname']){
		$qry=$db->prepare("UPDATE `tsubcat` SET name=:name,cat=:cat WHERE id=:id");
		$qry->execute(array('name' => $_POST['subcatname'],'cat' => $_POST['cat'],'id' => $selectedscat));
	}
}

//cat add form
echo '
<div class="modal fade" id="add_cat" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="ModalLabel"><i class="fa fa-sitemap text-info pr-2"><!----></i>'.T_("Ajout d'une sous-catégorie").'</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form name="form3" method="POST" action="" id="form3">
					<input name="addsubcat" type="hidden" value="1">
					<div class="form-group row p-0 m-0">
						<div class="col-sm-4 col-form-label text-sm-right pt-1">
							<label for="cat">'.T_('Catégorie').' :</label>
						</div>
						<div class="col-sm-5 ">
							<select class="form-control form-control-sm" id="cat" name="cat">
								';
								$qry=$db->prepare("SELECT `id`,`name` FROM `tcategory` WHERE id!=0 ORDER BY `name` ASC");
								$qry->execute();
								while($row=$qry->fetch()) {echo '<option value="'.$row['id'].'">'.T_($row['name']).'</option>';} 
								$qry->closeCursor(); 
								echo '
							</select>
						</div>
					</div>
					<div class="form-group row p-0 m-0">
						<div class="col-sm-4 col-form-label text-sm-right pt-1">
							<label for="subcat"> '.T_('Sous-catégorie').' :</label>
						</div>
						<div class="col-sm-5 ">
							<input class="form-control form-control-sm" name="subcatname" type="text" value="'.$subcatname.'" size="26">
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="$(\'form#form3\').submit();" ><i class="fa fa-check pr-2"><!----></i>'.T_('Ajouter').'</button>
				<button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times pr-2"><!----></i>'.T_('Annuler').'</button>
			</div>
		</div>
	</div>
</div>
';

//cat edit form
$qry=$db->prepare("SELECT `name` FROM `tsubcat` WHERE id=:id");
$qry->execute(array('id' => $selectedscat));
$subcat=$qry->fetch();
$qry->closeCursor();
if(!isset($subcat['name'])) {$subcat['name']='';}
echo '
<div class="modal fade" id="edit_cat" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="ModalLabel"><i class="fa fa-sitemap text-info pr-2"><!----></i>'.T_("Modification d'une sous-catégorie").'</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form name="form4" method="POST" action="" id="form4">
					<input name="modifysubcat" type="hidden" value="1">
					<div class="form-group row p-0 m-0">
						<div class="col-sm-4 col-form-label text-sm-right pt-1">
							<label for="cat">'.T_('Catégorie').':</label>
						</div>
						<div class="col-sm-5 ">
							<select class="form-control form-control-sm" id="cat" name="cat">
								';
								$qry=$db->prepare("SELECT `id`,`name` FROM `tcategory` ORDER BY name ASC");
								$qry->execute();
								while($row=$qry->fetch()) 
								{
									if($row['id']==$selectedcat) //special case to translate none value
									{echo '<option selected value="'.$row['id'].'">'.T_($row['name']).'</option>';}
									else
									{echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';}
								} 
								$qry->closeCursor(); 
								echo '
							</select>
						</div>
					</div>
					<div class="form-group row p-0 m-0">
						<div class="col-sm-4 col-form-label text-sm-right pt-1">
							<label for="subcat"> '.T_('Sous-catégorie').':</label>
						</div>
						<div class="col-sm-5 ">
							<input class="form-control form-control-sm" '; if($subcat['name']=='Aucune') {echo 'readonly';} echo ' name="subcatname" type="text" value="'.$subcat['name'].'" size="26">
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="$(\'form#form4\').submit();" ><i class="fa fa-check pr-2"><!----></i>'.T_('Modifier').'</button>
				<button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times pr-2"><!----></i>'.T_('Annuler').'</button>
			</div>
		</div>
	</div>
</div>
';
?>