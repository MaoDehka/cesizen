<?php
################################################################################
# @Name : ./admin/list.php
# @Description : administration of tables
# @Call : /admin.php
# @Parameters : 
# @Author : Flox
# @Create : 15/03/2011
# @Update : 24/11/2023
# @Version : 3.2.45
################################################################################

//initialize variables 
if(!isset($nbchamp)) $nbchamp = 0;
if(!isset($champ0)) $champ0 = '';
if(!isset($champ1)) $champ1 = '';
if(!isset($champ2)) $champ2 = '';
if(!isset($champ3)) $champ3 = '';
if(!isset($champ4)) $champ4 = '';
if(!isset($champ5)) $champ5 = '';
if(!isset($champ6)) $champ6 = '';
if(!isset($reqchamp)) $reqchamp = '';
if(!isset($set)) $set = '';
if(!isset($i)) $i = '';
if(!isset($extensionFichier)) $extensionFichier = '';
if(!isset($nomorigine)) $nomorigine = '';
if(!isset($number)) $number = '';
if(!isset($_FILES['file1']['name'])) $_FILES['file1']['name'] = '';
if(!isset($_POST['limit_ticket_number'])) $_POST['limit_ticket_number'] = '';
if(!isset($_POST['limit_ticket_date_start'])) $_POST['limit_ticket_date_start'] = '';
if(!isset($_POST['limit_ticket_days'])) $_POST['limit_ticket_days'] = '';
if(!isset($_POST['limit_hour_days'])) $_POST['limit_hour_days'] = '';
if(!isset($_POST['limit_hour_date_start'])) $_POST['limit_hour_date_start'] = '';

//default table
if($_GET['table']=='') $_GET['table']='tcategory';

//default page
if($_GET['action']=='') $_GET['action']='disp_list';

//escape special char and secure string before database insert
$champ0=strip_tags($db->quote($champ0));
$champ1=strip_tags($db->quote($champ1));
$champ2=strip_tags($db->quote($champ2));
$champ3=strip_tags($db->quote($champ3));
$champ4=strip_tags($db->quote($champ4));
$champ5=strip_tags($db->quote($champ5));
$champ6=strip_tags($db->quote($champ6));
$db_id=strip_tags($db->quote($_GET['id']));
$db_table=strip_tags($db->quote($_GET['table']));
$db_table=str_replace("'","`",$db_table);

//display debug informations
if($rparameters['debug']) {
	echo '<u><b>DEBUG MODE:</b></u><br /> <b>VAR:</b> cnt_service='.$cnt_service;
	if($user_services) {echo ' user_services=';foreach($user_services as $value) {echo $value.' ';}}
}

//retrieve selected table description
$qry = $db->prepare("DESC $db_table");
$qry->execute();
while($row=$qry->fetch()) {
	${'champ' . $nbchamp} =$row[0];
	$nbchamp++;
}
$qry->closeCursor();
$nbchamp1=$nbchamp;
$nbchamp=$nbchamp-1;
?>
<div class="page-header position-relative">
	<h1 class="page-title text-primary-m2">
		<i class="fa fa-list text-primary-m2"><!----></i> 
		<?php 
		echo T_('Administration des listes');
		?>
	</h1>
</div>
<div class="card bcard bgc-transparent shadow">
	<div class="card-body tabs-left p-0">
		<!-- list tables -->
		<?php require('admin/lists/list.php'); ?>
		<div class="tab-content" style="background-color:#FFF;">
			<!-- edit table -->
			<?php require('admin/lists/edit.php'); ?>
			<!-- add table -->
			<?php require('admin/lists/add.php'); ?>
			<!-- display table data -->
			<?php require('admin/lists/display.php'); ?>
		</div>
	</div>
</div>

<!-- admin list script  -->
<script type="text/javascript" src="js/admin_list.js"></script>