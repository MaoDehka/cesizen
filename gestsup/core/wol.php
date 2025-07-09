<?php
################################################################################
# @Name : wol.php
# @Description : wake on lan ip asset
# @Call : ./core/asset.php
# @Parameters : $_GET[id]
# @Author : Flox
# @Create : 19/12/2015
# @Update : 02/10/2024
# @Version : 3.2.53
################################################################################

//secure direct access
if(!$_SESSION['user_id']) {echo 'ERROR : invalid access'; exit;}

//get mac address
$qry=$db->prepare("SELECT MAX(`id`),`mac` FROM `tassets_iface` WHERE `asset_id`=:asset_id");
$qry->execute(array('asset_id' => $_GET['id']));
$asset=$qry->fetch();
$qry->closeCursor();

if(ctype_xdigit($asset['mac'])) 
{
	$mac=str_split($asset['mac'], 2);
	$mac="$mac[0]:$mac[1]:$mac[2]:$mac[3]:$mac[4]:$mac[5]";
	$result=exec("wakeonlan $mac");
} else {
	$result=" No hexadecimal digit detected";
}

//test result
if($result=="Wake-up packet sent successfully.")
{
	echo DisplayMessage('success',T_('Allumage de').' <b>'.$globalrow['netbios'].'</b> : OK <span style="font-size: x-small;">('.$result.')</span>');
} else {
	echo DisplayMessage('error',T_('Vérifier le wake on lan est bien installé (LINUX: apt-get install wakeonlan)').''.$result);
}
?>