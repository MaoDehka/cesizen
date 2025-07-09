
<?php
################################################################################
# @Name : user.php 
# @Description : admin user
# @Call : admin.php
# @Author : Flox
# @Create : 12/01/2011
# @Update : 11/05/2023
# @Version : 3.2.36
################################################################################

//initialize variables 
if(!isset($_SERVER['QUERY_URI'])) $_SERVER['QUERY_URI'] = '';
if(!isset($_POST['password'])) $_POST['password'] = '';
if(!isset($_POST['password2'])) $_POST['password2'] = '';
if(!isset($user1['company'])) $user1['company'] = '';
if(!isset($password)) $password = '';
if(!isset($password2)) $password2 = '';
if(!isset($addeview)) $addview = '';
if(!isset($category)) $category = '%';
if(!isset($maxline)) $maxline = '';
if(!isset($error)) $error = '';

//defaults values
if(!$_GET['tab']) $_GET['tab'] = 'infos';
if($_GET['disable']=='') $_GET['disable'] = '0';
if($_GET['cursor']=='') $_GET['cursor'] = '0';
if($_GET['order']=='') $_GET['order'] = 'lastname';
if($_GET['way']=='') $_GET['way'] = 'ASC';
if($maxline=='') $maxline = $rparameters['maxline'];
if($_POST['userkeywords']=='') $userkeywords='%'; else $userkeywords=$_POST['userkeywords'];
$db_order=$_GET['order'];
if($_GET['way']=='ASC' || $_GET['way']=='DESC') {$db_way=$_GET['way'];} else {$db_way='DESC';}
if(is_numeric($_GET['cursor'])) {$db_cursor=$_GET['cursor'];} else {$db_cursor=0;}

//delete association user > service
if($_GET['delete_assoc_service'] && ($rright['admin'] || $rright['user_profil_service']))
{
	$qry=$db->prepare("DELETE FROM `tusers_services` WHERE id=:id");
	$qry->execute(array('id' => $_GET['delete_assoc_service']));
}

//delete assoc user > agency
if($_GET['delete_assoc_agency'] && $rright['admin'])
{
	$qry=$db->prepare("DELETE FROM `tusers_agencies` WHERE id=:id");
	$qry->execute(array('id' => $_GET['delete_assoc_agency']));
}

//log
if($rparameters['log'] && $_GET['userid'] && $_GET['userid']!='%')
{
	$qry=$db->prepare("SELECT `login` FROM `tusers` WHERE id=:id");
	$qry->execute(array('id' => $_GET['userid']));
	$user_login=$qry->fetch();
	$qry->closeCursor();
	if($_GET['action']=='disable'){LogIt('security', 'User '.$user_login['login'].' disabled',$_SESSION['user_id']);}
	if($_GET['action']=='delete'){LogIt('security', 'User '.$user_login['login'].' deleted',$_SESSION['user_id']);}
	if($_GET['action']=='enable'){LogIt('security', 'User '.$user_login['login'].' enabled',$_SESSION['user_id']);}
}

//cancel
if($_POST['cancel'])
{
	//redirect
	if($rright['admin'] && $_GET['subpage']=='user')
	{
		$www = './index.php?page=admin&subpage=user&disable='.$_GET['disable'].'&lastname='.$_GET['lastname'].'&login='.$_GET['login'].'&company='.$_GET['company'].'&agency='.$_GET['agency'].'&service='.$_GET['service'].'&mail='.$_GET['mail'].'&phone='.$_GET['phone'].'&profile='.$_GET['profile'].'&connexion='.$_GET['connexion'].'&cursor='.$_GET['cursor'];
	} else {
		$www = "./index.php?page=dashboard&userid=$uid&state=%25";
	}
	echo '<script language="Javascript">
	<!--
	document.location.replace("'.$www.'");
	// -->
	</script>';
}

//view Part
if($_GET['deleteview'] && $rright['side_view'] && $rright['admin_user_view'])
{
	$qry=$db->prepare("DELETE FROM `tviews` WHERE id=:id");
	$qry->execute(array('id' => $_GET['viewid']));
	//redirect
	$url = "./index.php?page=admin/user&subpage=user&action=edit&tab=parameters&userid=$_GET[userid]";
	$url=preg_replace('/%/','%25',$url);
	echo '<script language="Javascript">
	<!--
	document.location.replace("'.$url.'");
	// -->
	</script>';
}

//delete tech attachement
if($_GET['attachmentdelete'] && ($_SESSION['profile_id']==0 || $_SESSION['profile_id']==4))
{
	$qry=$db->prepare("DELETE FROM `tusers_tech` WHERE id=:id");
	$qry->execute(array('id' => $_GET['attachmentdelete']));
}

//display head page
if($rright['admin_user_profile'] || $rright['admin'])
{
	if(!$_GET['ldap'])
	{
		//count users
		$qry = $db->prepare("SELECT COUNT(*) FROM `tusers` WHERE disable='0'");
		$qry->execute();
		$active_users=$qry->fetch();
		$qry->closeCursor();
		
		$qry = $db->prepare("SELECT COUNT(`id`) FROM `tusers` WHERE `disable`='1' AND `id`!='0' AND `login`!='delete_user_gs'");
		$qry->execute();
		$inactive_users=$qry->fetch();
		$qry->closeCursor();
		echo '
		<div class="page-header position-relative">
			<h1 class="page-title text-primary-m2" >
				<i class="fa fa-user"><!----><!----></i>  '.T_('Gestion des utilisateurs').'
			</h1>
		</div>';
	}
}

if($_GET['action']=="disable" && $rright['admin'])
{
	$qry=$db->prepare("UPDATE `tusers` SET `disable`='1' WHERE `id`=:id");
	$qry->execute(array('id' => $_GET['userid']));
	
	if($rparameters['log'])
	{
		if(is_numeric($_GET['userid']))
		{
			$qry=$db->prepare("SELECT `login` FROM `tusers` WHERE id=:id");
			$qry->execute(array('id' => $_GET['userid']));
			$row=$qry->fetch();
			$qry->closeCursor();
			
			require_once('core/functions.php');
			LogIt('security', 'User '.$row['login'].' disabled',$_SESSION['user_id']);
		}
	}
	
	//home page redirection
	$www = "./index.php?page=admin&subpage=user";
			echo '<script language="Javascript">
			<!--
			document.location.replace("'.$www.'");
			// -->
			</script>';
}elseif($_GET['action']=="delete" && $rright['admin'] && $_GET['userid'])
{
	DeleteUser($_GET['userid']);
	//home page redirection
	$www = "index.php?page=admin&subpage=user&disable=$_GET[disable]";
	echo '<script language="Javascript">
	<!--
	document.location.replace("'.$www.'");
	// -->
	</script>';
}
elseif($_GET['action']=="enable" && $rright['admin'])
{
	$qry=$db->prepare("UPDATE `tusers` SET `disable`='0' WHERE `id`=:id");
	$qry->execute(array('id' => $_GET['userid']));
	//home page redirection
	$www = "./index.php?page=admin&amp;subpage=user";
			echo '<script language="Javascript">
			<!--
			document.location.replace("'.$www.'");
			// -->
			</script>';
}
elseif($_GET['ldap']=="1"){include('./core/ldap.php');}
elseif($_GET['ldap']=="agencies"){include('./core/ldap_agencies.php');} 
elseif($_GET['ldap']=="services"){include('./core/ldap_services.php');}
elseif($_GET['azure_ad']=="1"){include('./core/azure_ad.php');}

//display security warning for user who want access to edit another user profile
elseif(($_GET['action']=='edit') && ($_GET['userid']!=$_SESSION['user_id']) && !$rright['admin'])
{
   echo '
	<div role="alert" class="alert alert-lg bgc-danger-l3 border-0 border-l-4 brc-danger-m1 mt-4 mb-3 pr-3 d-flex">
		<div class="flex-grow-1">
			<i class="fas fa-times mr-1 text-120 text-danger-m1"><!----></i>
			<strong class="text-danger">'.T_('Erreur').' : '.T_("Vous n'avez pas le droit d'accéder au profil d'un autre utilisateur").'.</strong>
		</div>
		<button type="button" class="close align-self-start" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true"><i class="fa fa-times text-80"><!----></i></span>
		</button>
	</div>
';
}
//display security warning for user who want access to add another user profile
elseif(($_GET['action']=='add') && !$rright['admin'] && ($_GET['userid']!=$_SESSION['user_id']))
{
	echo '
	<div role="alert" class="alert alert-lg bgc-danger-l3 border-0 border-l-4 brc-danger-m1 mt-4 mb-3 pr-3 d-flex">
		<div class="flex-grow-1">
			<i class="fas fa-times mr-1 text-120 text-danger-m1"><!----></i>
			<strong class="text-danger">'.T_('Erreur').' : '.T_("Vous n'avez pas le droit d'ajouter des utilisateurs").'.</strong>
		</div>
		<button type="button" class="close align-self-start" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true"><i class="fa fa-times text-80"><!----></i></span>
		</button>
	</div>
	';
} else {

	//include plugin
	$section='user_list';
	include('./plugin.php');

	//include display pages
	require('admin/users/add.php');
	require('admin/users/edit.php');
	require('admin/users/list.php');
}

?>
<!-- admin user scripts  -->
<script type="text/javascript" src="js/admin_user.js"></script>