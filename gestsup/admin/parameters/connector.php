<?php
################################################################################
# @Name : connector.php
# @Description : admin connector parameters
# @Call : /admin/parameters.php
# @Parameters : 
# @Author : Flox
# @Create : 22/09/2020
# @Update : 06/05/2024
# @Version : 3.2.50 p1
################################################################################

require_once('vendor/autoload.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;
use Greew\OAuth2\Client\Provider\Azure;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

//initialize variables 
if(!isset($_POST['mail_password'])) $_POST['mail_password'] = '';
if(!isset($_POST['ldap_password'])) $_POST['ldap_password']= '';
if(!isset($_POST['imap_password'])) $_POST['imap_password']= '';
if(!isset($provider)) $provider= '';

//default variable
if(empty($_GET['subtab'])) {$_GET['subtab']='smtp';}

//remove space from mail adresse
$_POST['mail_username']=str_replace(' ','',$_POST['mail_username']);
$_POST['imap_user']=str_replace(' ','',$_POST['imap_user']);

//delete imap mailbox service association
if($rparameters['imap_mailbox_service'] && $_GET['delete_imap_service'] && $rright['admin'])
{
	$qry=$db->prepare("DELETE FROM tparameters_imap_multi_mailbox WHERE id=:id");
	$qry->execute(array('id' => $_GET['delete_imap_service']));
}

//delete tenant for entra id connector
if($rparameters['azure_ad'] && $_GET['delete'] && $_GET['subtab']=='azure'&& $rright['admin'])
{
    //delete tenant
	$qry=$db->prepare("DELETE FROM `tentra_tenant` WHERE `id`=:id");
	$qry->execute(array('id' => $_GET['delete']));

    //update tenant number
    $rparameters['azure_ad_tenant_number']=$rparameters['azure_ad_tenant_number']-1;
    $qry=$db->prepare("UPDATE `tparameters` SET `azure_ad_tenant_number`=:azure_ad_tenant_number");
    $qry->execute(array('azure_ad_tenant_number' => $rparameters['azure_ad_tenant_number']));

    //update tenant id
    $qry=$db->prepare("SELECT `id` FROM `tentra_tenant` WHERE `id`>:id");
    $qry->execute(array('id' => $_GET['delete']));
    while($tenant=$qry->fetch()) 
    {
        $new_tenant_id=$tenant['id']-1;
        $qry2=$db->prepare("UPDATE `tentra_tenant` SET `id`=:new_id WHERE `id`=:old_id");
        $qry2->execute(array('new_id' => $new_tenant_id,'old_id' => $tenant['id']));
    }
    $qry->closeCursor();
}

if($rright['admin'] && ($_POST['submit_connector'] || $_POST['test_ldap'] || $_POST['test_smtp'] || $_POST['test_imap']))
{
	//remove incorrect char
	$_POST['mail_smtp']=str_replace('|','',$_POST['mail_smtp']);
	$_POST['ldap_server']=str_replace('|','',$_POST['ldap_server']);
	$_POST['imap_server']=str_replace('|','',$_POST['imap_server']);

    //init API parameters
    if($_POST['api'] && !$rparameters['api_key']) {
        $_POST['api_key'] = bin2hex(random_bytes(1024));
    } else { $_POST['api_key']=$rparameters['api_key'];}
	
	$qry=$db->prepare("
	UPDATE `tparameters` SET 
	`mail`=:mail,
	`mail_smtp`=:mail_smtp, 
	`mail_smtp_class`=:mail_smtp_class, 
	`mail_port`=:mail_port, 
	`mail_ssl_check`=:mail_ssl_check, 
	`mail_secure`=:mail_secure, 
	`mail_auth`=:mail_auth, 
	`mail_auth_type`=:mail_auth_type, 
	`mail_oauth_client_id`=:mail_oauth_client_id, 
	`mail_oauth_tenant_id`=:mail_oauth_tenant_id, 
	`mail_oauth_client_secret`=:mail_oauth_client_secret, 
	`mail_username`=:mail_username, 
	`ldap`=:ldap, 
	`ldap_auth`=:ldap_auth, 
	`ldap_sso`=:ldap_sso, 
	`ldap_type`=:ldap_type, 
	`ldap_service`=:ldap_service, 
	`ldap_service_url`=:ldap_service_url, 
	`ldap_login_field`=:ldap_login_field, 
	`ldap_agency`=:ldap_agency, 
	`ldap_agency_url`=:ldap_agency_url, 
	`ldap_server`=:ldap_server, 
	`ldap_port`=:ldap_port, 
	`ldap_user`=:ldap_user, 
	`ldap_domain`=:ldap_domain, 
	`ldap_url`=:ldap_url, 
	`ldap_disable_user`=:ldap_disable_user, 
	`azure_ad`=:azure_ad, 
	`azure_ad_tenant_number`=:azure_ad_tenant_number, 
	`azure_ad_login_field`=:azure_ad_login_field, 
	`azure_ad_disable_user`=:azure_ad_disable_user, 
	`azure_ad_sso`=:azure_ad_sso, 
	`azure_ad_sso_hide_login`=:azure_ad_sso_hide_login, 
	`ocs`=:ocs, 
	`ocs_server_url`=:ocs_server_url, 
	`api`=:api, 
	`api_key`=:api_key, 
	`api_client_ip`=:api_client_ip, 
	`imap`=:imap, 
	`imap_server`=:imap_server, 
	`imap_auth_type`=:imap_auth_type, 
	`imap_oauth_client_id`=:imap_oauth_client_id, 
	`imap_oauth_tenant_id`=:imap_oauth_tenant_id, 
	`imap_oauth_client_secret`=:imap_oauth_client_secret, 
	`imap_port`=:imap_port, 
	`imap_ssl_check`=:imap_ssl_check, 
	`imap_user`=:imap_user, 
	`imap_reply`=:imap_reply, 
	`imap_auto_create_user`=:imap_auto_create_user, 
	`imap_blacklist`=:imap_blacklist, 
	`imap_post_treatment`=:imap_post_treatment, 
	`imap_post_treatment_folder`=:imap_post_treatment_folder, 
	`imap_mailbox_service`=:imap_mailbox_service, 
	`imap_from_adr_service`=:imap_from_adr_service, 
	`imap_inbox`=:imap_inbox,
	`imap_date_create`=:imap_date_create
	WHERE `id`=:id
	");
	$qry->execute(array(
		'mail' => $_POST['mail'],
		'mail_smtp' => $_POST['mail_smtp'],
		'mail_smtp_class' => $_POST['mail_smtp_class'],
		'mail_port' => $_POST['mail_port'],
		'mail_ssl_check' => $_POST['mail_ssl_check'],
		'mail_secure' => $_POST['mail_secure'],
		'mail_auth' => $_POST['mail_auth'],
		'mail_auth_type' => $_POST['mail_auth_type'],
		'mail_oauth_client_id' => $_POST['mail_oauth_client_id'],
		'mail_oauth_tenant_id' => $_POST['mail_oauth_tenant_id'],
		'mail_oauth_client_secret' => $_POST['mail_oauth_client_secret'],
		'mail_username' => $_POST['mail_username'],
		'ldap' => $_POST['ldap'],
		'ldap_auth' => $_POST['ldap_auth'],
		'ldap_sso' => $_POST['ldap_sso'],
		'ldap_type' => $_POST['ldap_type'],
		'ldap_service' => $_POST['ldap_service'],
		'ldap_service_url' => $_POST['ldap_service_url'],
		'ldap_login_field' => $_POST['ldap_login_field'],
		'ldap_agency' => $_POST['ldap_agency'],
		'ldap_agency_url' => $_POST['ldap_agency_url'],
		'ldap_server' => $_POST['ldap_server'],
		'ldap_port' => $_POST['ldap_port'],
		'ldap_user' => $_POST['ldap_user'],
		'ldap_domain' => $_POST['ldap_domain'],
		'ldap_url' => $_POST['ldap_url'],
		'ldap_disable_user' => $_POST['ldap_disable_user'],
		'azure_ad' => $_POST['azure_ad'],
		'azure_ad_tenant_number' => $_POST['azure_ad_tenant_number'],
		'azure_ad_login_field' => $_POST['azure_ad_login_field'],
		'azure_ad_disable_user' => $_POST['azure_ad_disable_user'],
		'azure_ad_sso' => $_POST['azure_ad_sso'],
		'azure_ad_sso_hide_login' => $_POST['azure_ad_sso_hide_login'],
		'ocs' => $_POST['ocs'],
		'ocs_server_url' => $_POST['ocs_server_url'],
		'api' => $_POST['api'],
		'api_key' => $_POST['api_key'],
		'api_client_ip' => $_POST['api_client_ip'],
		'imap' => $_POST['imap'],
		'imap_server' => $_POST['imap_server'],
		'imap_auth_type' => $_POST['imap_auth_type'],
		'imap_oauth_client_id' => $_POST['imap_oauth_client_id'],
		'imap_oauth_tenant_id' => $_POST['imap_oauth_tenant_id'],
		'imap_oauth_client_secret' => $_POST['imap_oauth_client_secret'],
		'imap_port' => $_POST['imap_port'],
		'imap_ssl_check' => $_POST['imap_ssl_check'],
		'imap_user' => $_POST['imap_user'],
		'imap_reply' => $_POST['imap_reply'],
		'imap_auto_create_user' => $_POST['imap_auto_create_user'],
		'imap_blacklist' => $_POST['imap_blacklist'],
		'imap_post_treatment' => $_POST['imap_post_treatment'],
		'imap_post_treatment_folder' => $_POST['imap_post_treatment_folder'],
		'imap_mailbox_service' => $_POST['imap_mailbox_service'],
		'imap_from_adr_service' => $_POST['imap_from_adr_service'],
		'imap_inbox' => $_POST['imap_inbox'],
		'imap_date_create' => $_POST['imap_date_create'],
		'id' => '1'
		));
	
	//move ticket from agency to another if detected
	if($rparameters['user_agency'] && $_POST['from_agency'] && $_POST['dest_agency'])
	{
		$qry=$db->prepare("UPDATE `tincidents` SET `u_agency`=:u_agency1 WHERE `u_agency`=:u_agency2");
		$qry->execute(array('u_agency1' => $_POST['dest_agency'],'u_agency2' => $_POST['from_agency']));
	}
	
	//update imap multi mailbox service parameters
	if($rparameters['imap_mailbox_service'])
	{
		//add new association
		if($_POST['mailbox_service'] && $_POST['mailbox_service_id'])
		{
            if($_POST['mailbox_service_oauth_client_secret'] && !preg_match('/gs_en/',$_POST['mailbox_service_oauth_client_secret']))
            {
                $_POST['mailbox_service_oauth_client_secret'] = gs_crypt($_POST['mailbox_service_oauth_client_secret'], 'e', $rparameters['server_private_key']);
            }

			$qry=$db->prepare("INSERT INTO `tparameters_imap_multi_mailbox` (
                `mail`,
                `password`,
                `service_id`,
                `mailbox_service_auth_type`,
                `mailbox_service_oauth_tenant_id`,
                `mailbox_service_oauth_client_id`,
                `mailbox_service_oauth_client_secret`
                ) VALUES (
                    :mail,
                    :password,
                    :service_id,
                    :mailbox_service_auth_type,
                    :mailbox_service_oauth_tenant_id,
                    :mailbox_service_oauth_client_id,
                    :mailbox_service_oauth_client_secret
                    )");
			$qry->execute(array(
                'mail' => $_POST['mailbox_service'],
                'password' => $_POST['mailbox_password'],
                'service_id' => $_POST['mailbox_service_id'],
                'mailbox_service_auth_type' => $_POST['mailbox_service_auth_type'],
                'mailbox_service_oauth_tenant_id' => $_POST['mailbox_service_oauth_tenant_id'],
                'mailbox_service_oauth_client_id' => $_POST['mailbox_service_oauth_client_id'],
                'mailbox_service_oauth_client_secret' => $_POST['mailbox_service_oauth_client_secret']
            ));
		}
		//crypt password
		$qry=$db->prepare("SELECT `id`,`password` FROM `tparameters_imap_multi_mailbox` WHERE `password` NOT LIKE '%gs_en%'");
		$qry->execute();
		while($row=$qry->fetch()) 
		{
			//crypt password
			$enc_mailbox_password = gs_crypt($row['password'], 'e', $rparameters['server_private_key']);
			//update tparameters
			$qry2=$db->prepare("UPDATE `tparameters_imap_multi_mailbox` SET `password`=:mail_password WHERE `id`=:id");
			$qry2->execute(array('mail_password' => $enc_mailbox_password,'id' => $row['id']));
		}
		$qry->closeCursor();
	}
	
	//crypt connector password
	if($_POST['mail_password'] && !preg_match('/gs_en/',$_POST['mail_password']))
	{
		$enc_mail_password = gs_crypt($_POST['mail_password'], 'e', $rparameters['server_private_key']);
		$qry=$db->prepare("UPDATE `tparameters` SET `mail_password`=:mail_password WHERE `id`='1'");
		$qry->execute(array('mail_password' => $enc_mail_password));
	}
	if($_POST['ldap_password'] && !preg_match('/gs_en/',$_POST['ldap_password']))
	{
		$enc_ldap_password = gs_crypt($_POST['ldap_password'], 'e', $rparameters['server_private_key']);
		$qry=$db->prepare("UPDATE `tparameters` SET `ldap_password`=:ldap_password WHERE `id`='1'");
		$qry->execute(array('ldap_password' => $enc_ldap_password));
	}
	if($_POST['imap_password'] && !preg_match('/gs_en/',$_POST['imap_password']))
	{
		$enc_imap_password = gs_crypt($_POST['imap_password'], 'e', $rparameters['server_private_key']);
		$qry=$db->prepare("UPDATE `tparameters` SET `imap_password`=:imap_password WHERE `id`='1'");
		$qry->execute(array('imap_password' => $enc_imap_password));
	}
	if($_POST['mail_oauth_client_secret'] && !preg_match('/gs_en/',$_POST['mail_oauth_client_secret']))
	{
		$enc_mail_oauth_client_secret = gs_crypt($_POST['mail_oauth_client_secret'], 'e', $rparameters['server_private_key']);
		$qry=$db->prepare("UPDATE `tparameters` SET `mail_oauth_client_secret`=:mail_oauth_client_secret WHERE `id`='1'");
		$qry->execute(array('mail_oauth_client_secret' => $enc_mail_oauth_client_secret));
	}
	if($_POST['imap_oauth_client_secret'] && !preg_match('/gs_en/',$_POST['imap_oauth_client_secret']))
	{
		$enc_imap_oauth_client_secret = gs_crypt($_POST['imap_oauth_client_secret'], 'e', $rparameters['server_private_key']);
		$qry=$db->prepare("UPDATE `tparameters` SET `imap_oauth_client_secret`=:imap_oauth_client_secret WHERE `id`='1'");
		$qry->execute(array('imap_oauth_client_secret' => $enc_imap_oauth_client_secret));
	}

    //update entra connector
    if($rparameters['azure_ad'])
    {
        //for each tenant
        for($id = 1; $id <= $rparameters['azure_ad_tenant_number']; $id++) {

            //define field name
            $field_tenant_name='entra_tenant_name_'.$id;
            $field_client_id='entra_client_id_'.$id;
            $field_tenant_id='entra_tenant_id_'.$id;
            $field_client_secret='entra_client_secret_'.$id;
            $field_group_filter='entra_group_filter_'.$id;

            //init var
            if(!isset($_POST[$field_tenant_name])){$_POST[$field_tenant_name]='';}
            if(!isset($_POST[$field_client_id])){$_POST[$field_client_id]='';}
            if(!isset($_POST[$field_tenant_id])){$_POST[$field_tenant_id]='';}
            if(!isset($_POST[$field_client_secret])){$_POST[$field_client_secret]='';}
            if(!isset($_POST[$field_group_filter])){$_POST[$field_group_filter]='';}

            //secure var
            $_POST[$field_tenant_name]=htmlspecialchars($_POST[$field_tenant_name], ENT_QUOTES, 'UTF-8');
            $_POST[$field_client_id]=htmlspecialchars($_POST[$field_client_id], ENT_QUOTES, 'UTF-8');
            $_POST[$field_tenant_id]=htmlspecialchars($_POST[$field_tenant_id], ENT_QUOTES, 'UTF-8');
            $_POST[$field_client_secret]=htmlspecialchars($_POST[$field_client_secret], ENT_QUOTES, 'UTF-8');
            $_POST[$field_group_filter]=htmlspecialchars($_POST[$field_group_filter], ENT_QUOTES, 'UTF-8');

            //check tenant parameters exist
            $qry=$db->prepare("SELECT `id` FROM `tentra_tenant` WHERE `id`=:id");
            $qry->execute(array('id' => $id));
            $tenant=$qry->fetch();
            $qry->closeCursor();
            if(!isset($tenant['id'])) {$tenant=array(); $tenant['id']='';}

            if($tenant['id']==$id) //update tenant
            {
             
                $qry=$db->prepare("UPDATE `tentra_tenant` 
                    SET 
                    `tenant_name`=:tenant_name, 
                    `tenant_id`=:tenant_id, 
                    `client_id`=:client_id, 
                    `group_filter`=:group_filter 
                    WHERE `id`=:id
                ");
                $qry->execute(array(
                    'tenant_name' =>  $_POST[$field_tenant_name],
                    'tenant_id' =>  $_POST[$field_tenant_id],
                    'client_id' =>  $_POST[$field_client_id],
                    'group_filter' =>  $_POST[$field_group_filter],
                    'id' => $tenant['id']
                ));

                //update secret
                if($_POST[$field_client_secret])
                {
                    //crypt secret
                    $_POST[$field_client_secret] = gs_crypt($_POST[$field_client_secret], 'e', $rparameters['server_private_key']);
                    //update
                    $qry=$db->prepare("UPDATE `tentra_tenant` SET `client_secret`=:client_secret WHERE `id`=:id");
                    $qry->execute(array('client_secret' =>  $_POST[$field_client_secret], 'id' => $tenant['id']));
                }
                
            } elseif($_POST[$field_tenant_name]) {  //create tenant
                $qry=$db->prepare("INSERT INTO `tentra_tenant` 
                (
                    `id`,
                    `tenant_name`,
                    `tenant_id`,
                    `client_id`,
                    `client_secret`,
                    `group_filter`
                ) VALUES (
                    :id,
                    :tenant_name,
                    :tenant_id,
                    :client_id,
                    :client_secret,
                    :group_filter
                )");
                $qry->execute(array(
                    'id' => $id,
                    'tenant_name' => $_POST[$field_tenant_name],
                    'tenant_id' => $_POST[$field_tenant_id],
                    'client_id' => $_POST[$field_client_id],
                    'client_secret' => $_POST[$field_client_secret],
                    'group_filter' => $_POST[$field_group_filter]
                ));
            }
           
        }  
    }

	if(!$_POST['plugin_connector'])
    {
        //redirect
        $www = './index.php?page=admin&subpage=parameters&tab=connector&subtab='.$_GET['subtab'].'&ldaptest='.$_POST['test_ldap'].'&smtptest='.$_POST['test_smtp'];
        echo '<script language="Javascript">
        <!--
        document.location.replace("'.$www.'");
        // -->
        </script>'; 
    }
}
?>
<!-- /////////////////////////////////////////////////////////////// connectors part /////////////////////////////////////////////////////////////// -->
<input type="hidden" name="tab" id="tab" value="connector" />
<div id="connector" class="tab-pane <?php if($_GET['tab']=='connector') echo 'active'; ?>">
    <form name="connector_form" id="connector_form" enctype="multipart/form-data" method="post" action="">
        <div class="table-responsive">

            <div class="tab-content" style="background-color:#FFF;">
                <div class="card bcard bgc-transparent shadow-none">
                    <div class="card-body tabs-left p-0">
                        <ul class="nav nav-tabs align-self-start" role="tablist">
                            <li class="nav-item brc-primary shadow-sm">
                                <a class="nav-link text-left py-3 <?php if($_GET['subtab']=='smtp') {echo 'active';}?>" href="index.php?page=admin&subpage=parameters&tab=connector&subtab=smtp">
                                    <i class="fa fa-envelope text-blue-m3 pr-1"><!----></i><?php echo T_('SMTP'); ?> 
                                </a>
                            </li>
                            <li class="nav-item brc-primary shadow-sm">
                                <a class="nav-link text-left py-3 <?php if($_GET['subtab']=='imap') {echo 'active';}?>" href="index.php?page=admin&subpage=parameters&tab=connector&subtab=imap">
                                    <i class="fa fa-download text-blue-m3 pr-1"><!----></i><?php echo T_('IMAP'); ?> 
                                </a>
                            </li>
                            <li class="nav-item brc-primary shadow-sm">
                                <a class="nav-link text-left py-3 <?php if($_GET['subtab']=='ldap') {echo 'active';}?>" href="index.php?page=admin&subpage=parameters&tab=connector&subtab=ldap">
                                    <i class="fa fa-book text-blue-m3 pr-1"><!----></i><?php echo T_('LDAP'); ?> 
                                </a>
                            </li>
                            <li class="nav-item brc-primary shadow-sm">
                                <a class="nav-link text-left py-3 <?php if($_GET['subtab']=='azure') {echo 'active';}?>" href="index.php?page=admin&subpage=parameters&tab=connector&subtab=azure">
                                    <i class="fa fa-book text-blue-m3 pr-1"><!----></i><?php echo T_('Entra ID'); ?> 
                                </a>
                            </li>
                            <li class="nav-item brc-primary shadow-sm">
                                <a class="nav-link text-left py-3 <?php if($_GET['subtab']=='ocs') {echo 'active';}?>" href="index.php?page=admin&subpage=parameters&tab=connector&subtab=ocs">
                                    <i class="fa fa-desktop text-blue-m3 pr-1"><!----></i><?php echo T_('OCS'); ?> 
                                </a>
                            </li>
                            <li class="nav-item brc-primary shadow-sm">
                                <a class="nav-link text-left py-3 <?php if($_GET['subtab']=='api') {echo 'active';}?>" href="index.php?page=admin&subpage=parameters&tab=connector&subtab=api">
                                    <i class="fa fa-exchange text-blue-m3 pr-1"><!----></i><?php echo T_('API'); ?> 
                                </a>
                            </li>
                        </ul>
                        <!-- tab content -->
                        <div class="tab-content p-35 border-1 brc-grey-l1 shadow-sm bgc-white">
                            <!-- tab smtp -->
                            <div class="tab-pane fade text-95 <?php if($_GET['subtab']=='smtp') {echo 'show active';}?> ">
                                <div class="text-primary-d3 text-110 mb-2">
                                    <input id="mail" name="mail" type="checkbox" <?php if($rparameters['mail']) {echo "checked";} ?> value="1">
                                    <label for="mail" ><?php echo T_('Activer la liaison SMTP'); ?></label>
                                    <i data-toggle="tooltip" id="tooltip1" data-placement="auto" data-original-title="<?php echo T_("Connecteur permettant l'envoi de mails depuis GestSup vers un serveur de messagerie, afin que les mails puissent être envoyés"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    <div id="smtp_parameters">
                                        <?php
                                        echo '
                                        <div class="pt-1"></div>
                                        <label class="lbl" for="mail_smtp"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Serveur').' :</label>
                                        <input style="width:auto" class="form-control  d-inline-block" name="mail_smtp" id="mail_smtp" type="text" value="'.$rparameters['mail_smtp'].'" size="20" />
                                        <i data-toggle="tooltip" id="tooltip2" data-placement="auto" data-original-title="'.T_("Adresse IP ou nom de votre serveur de messagerie (Exemple: smtp.office365.com ou smtp.gmail.com ou ssl0.ovh.net ou 192.168.0.1 ou SRVMSG ou smtp.free.fr ou auth.smtp.1and1.fr)").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        ';
                                        //add control
                                        if($rparameters['mail_auth_type']=='oauth_azure' && $rparameters['mail_smtp'] && $rparameters['mail_smtp']!='smtp.office365.com')
                                        {
                                            echo'<i data-toggle="tooltip" id="tooltip3" data-placement="auto" data-original-title="'.T_("Le serveur SMTP pour l'authentification XOAUTH2 Entra ID (Azure AD) est smtp.office365.com").'" class="fa fa-exclamation-triangle text-warning"><!----></i>';
                                        }
                                        echo '
                                        <div class="pt-1"></div>
                                        <label class="lbl" for="mail_port"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Port').' :</label>
                                        <select style="width:auto" class="form-control  d-inline-block" id="mail_port" name="mail_port" >
                                            <option ';if($rparameters['mail_port']==587) echo "selected "; echo' value="587">587 (TLS)</option>
                                            <option ';if($rparameters['mail_port']==465) echo "selected "; echo' value="465">465 (SSL)</option>
                                            <option ';if($rparameters['mail_port']==25) echo "selected "; echo' value="25">25</option>
                                            <option ';if($rparameters['mail_port']==225) echo "selected "; echo' value="225">225</option>
                                        </select>
                                        <i data-toggle="tooltip" id="tooltip4" data-placement="auto" data-original-title="'.T_("Port du serveur de messagerie par défaut le port 25 est utilisé, pour les connexions sécurisées les ports 465 et 587 sont utilisés. (exemple: OVH port 587, 1&1 port 587, Office 365 port 587, Gmail port 587, Gmail Oauth 465, Office 365 Oauth 587)").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        ';
                                        //add control
                                        if($rparameters['mail_auth_type']=='oauth_azure' && $rparameters['mail_port']!='587')
                                        {
                                            echo'<i data-toggle="tooltip" id="tooltip5" data-placement="auto" data-original-title="'.T_("Le port l'authentification XOAUTH2 Entra ID (Azure AD) est 587").'" class="fa fa-exclamation-triangle text-warning"><!----></i>';
                                        }
                                        echo '
                                        <div class="pt-1"></div>
                                        <label class="lbl" for="mail_ssl_check"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Vérification SSL').' :</label>
                                        <select style="width:auto" class="form-control  d-inline-block" id="mail_ssl_check" name="mail_ssl_check" >
                                            <option ';if($rparameters['mail_ssl_check']==1) echo "selected "; echo' value="1">'.T_('Activée').' ('.T_('Défaut').')</option>
                                            <option ';if($rparameters['mail_ssl_check']==0) echo "selected "; echo' value="0">'.T_('Désactivée').'</option>
                                        </select>
                                        <i data-toggle="tooltip" id="tooltip6" data-placement="auto" data-original-title="'.T_("Désactivation de la verification du certificat serveur et autorise les certificats auto-signés").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        <div class="pt-1"></div>
                                        <label class="lbl" for="mail_secure"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Préfixe').' :</label>
                                        <select style="width:auto" class="form-control  d-inline-block" id="mail_secure" name="mail_secure" >
                                            <option ';if($rparameters['mail_secure']==0) echo "selected "; echo' value="0">'.T_('Aucun').' ('.T_('Défaut').')</option>
                                            <option ';if($rparameters['mail_secure']=='SSL') echo "selected "; echo' value="SSL">ssl//</option>
                                            <option ';if($rparameters['mail_secure']=='TLS') echo "selected "; echo' value="TLS">tls//</option>
                                        </select>
                                            ';
                                            if($rparameters['mail_secure']=='SSL' || $rparameters['mail_secure']=='TLS') {echo'<i>('.T_("l'extension php_openssl devra être activée").')<!----></i>';} else {
                                                echo '<i data-toggle="tooltip" id="tooltip44" data-placement="auto" data-original-title="'.T_("Si votre serveur de messagerie est sécurisé avec SSL ou TLS (Exemple: Gmail aucun TLS, 1&1 aucun, OVH aucun, Office 365 aucun)").'" class="fa fa-question-circle text-primary-m2"><!----></i>';
                                            } 
                                            echo '
                                        
                                        <div class="pt-1"></div>
                                        <label class="lbl" for="mail_smtp_class"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Classe').' :</label>
                                        <select style="width:auto" class="form-control  d-inline-block" id="mail_smtp_class" name="mail_smtp_class" >
                                            <option ';if($rparameters['mail_smtp_class']=='IsSMTP()') echo "selected "; echo' value="IsSMTP()">IsSMTP ('.T_('Défaut').')</option>
                                            <option ';if($rparameters['mail_smtp_class']=='IsSendMail()') echo "selected "; echo' value="IsSendMail()">IsSendMail</option>
                                        </select> 
                                        <i data-toggle="tooltip" id="tooltip7" data-placement="auto" data-original-title="'.T_("Classe PHPMailer, par défaut utiliser isSMTP(), certains hébergements n'autorisent que le isSendMail() (exemple: OVH et 1&1 utilise isSendMail() et Office 365 isSMTP)").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        <div class="pt-1"></div>
                                        <label>
                                            <input type="checkbox"'; if($_POST['mail_auth']|| $rparameters['mail_auth']) {echo "checked";}  echo ' name="mail_auth" id="mail_auth" value="1">
                                            <span class="lbl">&nbsp;'.T_('Serveur authentifié').'</span>
                                            <i data-toggle="tooltip" id="tooltip8" data-placement="auto" data-original-title="'.T_("Cochez cette case si votre serveur de messagerie nécessite un identifiant et mot de passe ou Oauth pour envoyer des messages").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        </label>
                                        <div class="ml-4" id="smtp_credential">
                                            <label class="lbl" for="mail_auth_type"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_("Type d'authentification").' :</label>
                                            <select style="width:auto" class="form-control  d-inline-block" id="mail_auth_type" name="mail_auth_type" >
                                                <option ';if($rparameters['mail_auth_type']=='login') echo "selected "; echo' value="login">LOGIN ('.T_('Défaut').')</option>
                                                <option ';if($rparameters['mail_auth_type']=='oauth_google') echo "selected "; echo' value="oauth_google">XOAUTH2 Google</option>
                                                <option ';if($rparameters['mail_auth_type']=='oauth_microsoft') echo "selected "; echo' value="oauth_microsoft">XOAUTH2 Microsoft</option>
                                                <option ';if($rparameters['mail_auth_type']=='oauth_azure') echo "selected "; echo' value="oauth_azure">XOAUTH2 Azure</option>
                                            </select> 
                                            <i data-toggle="tooltip" id="tooltip9" data-placement="auto" data-original-title="'.T_("Détermine le type d'authentification, login utilise un identifiant et mot de passe et XOAUTH utilise les API Google et Microsoft").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                            ';
                                            if($rparameters['mail_smtp']=='smtp.office365.com' && $rparameters['mail_auth_type']!='oauth_azure')
                                            {
                                                echo'<i data-toggle="tooltip" id="tooltip10" data-placement="auto" data-original-title="'.T_("Sélectionner le type d'authentification XOAUTH Azure").'" class="fa fa-exclamation-triangle text-warning"><!----></i>';
                                            }
                                            echo '
                                            <div class="pt-1"></div>
                                            <label class="lbl" for="mail_username"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Adresse de messagerie').' :</label> 
                                            <input style="width:auto" class="form-control  d-inline-block" name="mail_username" id="mail_username" type="text" value="'; if($_POST['mail_username']){echo $_POST['mail_username'];} else {echo $rparameters['mail_username'];} echo'" size="30" /><br />
                                            <div class="pt-1" id="mail_password_section"
                                                <label class="lbl" for="mail_password"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Mot de passe').' :</label> 
                                                <input style="width:auto" class="form-control  d-inline-block" name="mail_password" id="mail_password" type="password" value="" size="30" />
                                            </div>
                                            <div id="mail_oauth_parameters">
                                                <label class="lbl" for="mail_oauth_client_id"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('ID client').' :</label>
                                                <input style="width:512px" class="form-control  d-inline-block" name="mail_oauth_client_id" id="mail_oauth_client_id" type="text" value="'.$rparameters['mail_oauth_client_id'].'" size="20" />
                                                <div class="pt-1"></div>
                                                <div id="mail_oauth_tenant_section">
                                                    <label class="lbl" for="mail_oauth_tenant_id"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('ID locataire').' :</label>
                                                    <input style="width:512px" class="form-control  d-inline-block" id="mail_oauth_tenant_id" name="mail_oauth_tenant_id" type="text" value="'.$rparameters['mail_oauth_tenant_id'].'" size="20" />
                                                    <div class="pt-1"></div>
                                                </div>
                                                <label class="lbl" for="mail_oauth_client_secret"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Code secret du client').' :</label>
                                                <input style="width:512px" class="form-control  d-inline-block" name="mail_oauth_client_secret" id="mail_oauth_client_secret" type="password" value="'.$rparameters['mail_oauth_client_secret'].'" size="20" />
                                                <div class="pt-1"></div>
                                                <span class="lbl" for="mail_oauth_refresh_token"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Refresh token').' :</span>
                                                '.substr($rparameters['mail_oauth_refresh_token'],0,24).'*****************
                                                ';
                                                if($rparameters['mail_auth_type']=='oauth_google')
                                                {
                                                    echo '<a class="btn btn-xs btn-info" target="_blank" href="'.$rparameters['server_url'].'/get_oauth_token.php?provider=smtp_google">'.T_('Générer Refresh Token').'</a>';
                                                }elseif($rparameters['mail_auth_type']=='oauth_microsoft')
                                                {
                                                    echo '<a class="btn btn-xs btn-info" target="_blank" href="'.$rparameters['server_url'].'/get_oauth_token.php?provider=Microsoft">'.T_('Générer Refresh Token').'</a>';
                                                } elseif($rparameters['mail_auth_type']=='oauth_azure')
                                                {
                                                    echo '<a class="btn btn-xs btn-info" target="_blank" href="'.$rparameters['server_url'].'/get_oauth_token.php?provider=smtp_azure">'.T_('Générer Refresh Token').'</a>';
                                                } else {
                                                    echo '<a class="btn btn-xs btn-info" target="_blank" href="'.$rparameters['server_url'].'/get_oauth_token.php">'.T_('Générer token').'</a>';
                                                }
                                                echo '
                                                <br />
                                                <i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Procédure de configuration').' :
                                                <div id="oauth_google_procedure">
                                                    <ul>
                                                        <li><a target="about_blank" href="https://doc.gestsup.fr/faq/#comment-configurer-le-connecteur-smtp-gmail-avec-authentification-par-xoauth2" >'.T_("Procédure SMTP XOAUTH2 Google").'</a></li>
                                                        <li>'.T_('Console développeur Google').' : <a target="_blank" href="https://console.cloud.google.com/apis/dashboard">https://console.cloud.google.com/apis/dashboard</a></li>
                                                        <li>'.T_("URI de redirection autorisée, renseigner ").': '.$rparameters['server_url'].'/get_oauth_token.php</li>
                                                    </ul>
                                                </div>
                                                <div id="oauth_microsoft_procedure">
                                                    <ul>
                                                        <li><a target="about_blank" href="https://doc.gestsup.fr/faq/#comment-configurer-le-connecteur-smtp-entra-id-azure-ad-avec-authentification-par-xoauth2" >'.T_("Procédure SMTP XOAUTH2 Azure").'</a></li>
                                                        <li>'.T_('Portail Microsoft Azure').' <a target="_blank" href="https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationsListBlade">https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationsListBlade</a></li>
                                                        <li>
                                                            '.T_("URI de redirection").': '.$rparameters['server_url'].'/get_oauth_token.php
                                                            <i title="'.T_('Copier dans le presse papier').'" onclick="setClipboard(\''.$rparameters['server_url'].'/get_oauth_token.php\')" class="fa fa-clipboard text-primary-m2"></i>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        ';
                                        //SMTP TEST CONNECTOR
                                        echo '
                                        <div class="mt-3"></div>
                                        <button id="test_smtp" name="test_smtp" value="1" type="submit" class="btn btn-xs btn-success">
                                            <i class="fa fa-exchange-alt"><!----></i>
                                            '.T_('Test du connecteur SMTP').'
                                        </button>
                                        ';
                                        if($_GET['smtptest'])
                                        {
                                        
                                            if(!$rparameters['mail_smtp']) {
                                                echo DisplayMessage('error',T_('Vous devez renseigner le champ "Serveur" puis valider, pour lancer le test'));
                                            } else {
                                                //use post values if form not submitted
                                                if($_POST['mail_username']) {$rparameters['mail_username'] = $_POST['mail_username'];}
                                                if($_POST['mail_password']) {$rparameters['mail_password'] = $_POST['mail_password'];}
            
                                                $smtp_test = new PHPMailer;
                                                try {
                                                    $smtp_test->CharSet = 'UTF-8'; //ISO-8859-1 possible if string problems
                                                    if($rparameters['mail_smtp_class']=='IsSendMail()') {$smtp_test->IsSendMail();}
                                                    if($rparameters['mail_smtp_class']=='IsSMTP()') {$smtp_test->IsSMTP();} 
                                                    if($rparameters['mail_secure']=='SSL') {$smtp_test->Host = "ssl://$rparameters[mail_smtp]";}
                                                    elseif($rparameters['mail_secure']=='TLS') {$smtp_test->Host = "tls://$rparameters[mail_smtp]";} 
                                                    else {$smtp_test->Host = "$rparameters[mail_smtp]";}
                                                    $smtp_test->SMTPAuth = $rparameters['mail_auth'];
                                                    if($rparameters['debug']) {$smtp_test->SMTPDebug = 4;}
                                                    if($rparameters['mail_auth_type']=='login') {$smtp_test->AuthType = 'LOGIN';}
                                                    elseif($rparameters['mail_auth_type']=='oauth_google') {$smtp_test->AuthType = 'XOAUTH2';}
                                                    elseif($rparameters['mail_auth_type']=='oauth_microsoft') {$smtp_test->AuthType = 'XOAUTH2';}
                                                    elseif($rparameters['mail_auth_type']=='oauth_azure') {$smtp_test->AuthType = 'XOAUTH2';}
                                                    if($rparameters['mail_auth'] && ($rparameters['mail_auth_type']=='oauth_google' || $rparameters['mail_auth_type']=='oauth_microsoft' || $rparameters['mail_auth_type']=='oauth_azure'))
                                                    {
                                                        if(preg_match('/gs_en/',$rparameters['mail_oauth_client_secret'])) {$rparameters['mail_oauth_client_secret']=gs_crypt($rparameters['mail_oauth_client_secret'], 'd' , $rparameters['server_private_key']);}
                                                        if($rparameters['mail_auth_type']=='oauth_google')
                                                        {
                                                            $smtp_test->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                                                            $provider = new Google(  //Create a new OAuth2 provider instance
                                                                [
                                                                    'clientId' => $rparameters['mail_oauth_client_id'],
                                                                    'clientSecret' => $rparameters['mail_oauth_client_secret'],
                                                                ]
                                                            );
                                                        }
                                                        if($rparameters['mail_auth_type']=='oauth_microsoft')
                                                        {
                                                            $smtp_test->SMTPSecure = 'tls';
                                                            $provider = new Microsoft( //Create a new OAuth2 provider instance
                                                                [
                                                                    'clientId' => $rparameters['mail_oauth_client_id'],
                                                                    'clientSecret' => $rparameters['mail_oauth_client_secret'],
                                                                ]
                                                            );
                                                        }
                                                        if($rparameters['mail_auth_type']=='oauth_azure')
                                                        {
                                                            $smtp_test->SMTPSecure = 'tls';
                                                            //Create a new OAuth2 provider instance
                                                            $provider = new Azure(
                                                                [
                                                                    'clientId' => $rparameters['mail_oauth_client_id'],
                                                                    'clientSecret' => $rparameters['mail_oauth_client_secret'],
                                                                    'tenantId' => $rparameters['mail_oauth_tenant_id'],
                                                                ]
                                                            );
                                                        }
                                                        $smtp_test->setOAuth( //Pass the OAuth provider instance to PHPMailer
                                                            new OAuth(
                                                                [
                                                                    'provider' => $provider,
                                                                    'clientId' => $rparameters['mail_oauth_client_id'],
                                                                    'clientSecret' => $rparameters['mail_oauth_client_secret'],
                                                                    'refreshToken' => $rparameters['mail_oauth_refresh_token'],
                                                                    'userName' => $rparameters['mail_username'],
                                                                ]
                                                            )
                                                        );
                                                    } else {
                                                        if($rparameters['mail_secure']!=0) {$smtp_test->SMTPSecure = $rparameters['mail_secure'];} #6121
                                                    }
                                                
                                                    if($rparameters['mail_port']!=25) {$smtp_test->Port = $rparameters['mail_port'];} else {$smtp_test->SMTPAutoTLS = false;}
                                                    $smtp_test->Username = "$rparameters[mail_username]";
                                                    if(preg_match('/gs_en/',$rparameters['mail_password'])) {$rparameters['mail_password']=gs_crypt($rparameters['mail_password'], 'd' , $rparameters['server_private_key']);}
                                                    $smtp_test->Password = "$rparameters[mail_password]";
                                                    $smtp_test->IsHTML(true); 
                                                    $smtp_test->Timeout = 30;
                                                    $smtp_test->getSMTPInstance()->Timelimit = 30;
                                                    $smtp_test->SMTPKeepAlive = true;
                                                    $smtp_test->From = "$rparameters[mail_from_adr]";
                                                    $smtp_test->FromName = "$rparameters[mail_from_name]";
                                                    $smtp_test->XMailer = '_';
                                                    $smtp_test->Subject = "SMTP CONNECTOR TEST";
                                                    if($rparameters['mail_ssl_check']==0)
                                                    {
                                                        //bug fix 3292 & 3427
                                                        $smtp_test->smtpConnect([
                                                        'ssl' => [
                                                            'verify_peer' => false,
                                                            'verify_peer_name' => false,
                                                            'allow_self_signed' => true
                                                            ]
                                                        ]);
                                                    }
                                                    $smtp_test->Body = "SMTP CONNECTOR TEST";
                                                    if($smtp_test->smtpConnect()){
                                                        $smtp_test->smtpClose();
                                                        echo DisplayMessage('success',T_('Connecteur SMTP opérationnel'));
                                                    } else {
                                                        echo DisplayMessage('error',T_("Le connecteur ne fonctionne pas vérifier vos paramètres, vous pouvez activer le mode debug pour plus d'informations"));
                                                        if($rparameters['log']) {LogIt('error','ERROR 9 : SMTP test connector ko',$_SESSION['user_id']);}
                                                    }
                                                } catch (Exception $e) {
                                                    echo DisplayMessage('error',T_("Le connecteur ne fonctionne pas vérifier vos paramètres, vous pouvez activer le mode debug pour plus d'informations").' ('.$e->getMessage().')');
                                                    //log
                                                    if($rparameters['log']) {LogIt('error', 'ERROR 10 : SMTP test connector'. $e->getMessage(),$_SESSION['user_id']);}
                                                }
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <!-- tab IMAP -->
                            <div class="tab-pane fade text-95 <?php if($_GET['subtab']=='imap') {echo 'show active';}?> ">
                                <div class="text-primary-d3 text-110 mb-2">
                                    <input id="imap" name="imap" type="checkbox" <?php if($rparameters['imap']) {echo "checked";} ?> value="1">
                                    <label for="imap"><?php echo T_('Activer la liaison IMAP'); ?></label>
                                    <i data-toggle="tooltip" id="tooltip11" data-placement="auto" data-original-title="<?php echo T_("Connecteur permettant de créer des tickets automatiquement en interrogeant une boite mail. Une fois le mail converti en ticket le message passe en lu dans la boite de messagerie. Attention une tâche planifiée doit être crée afin d'interroger de manière régulière la boite mail (cf FAQ)"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    <div class="pt-1"></div>
                                    <div id="imap_parameters">
                                        <?php
                                        //generate stat token access
                                        if($rright['admin'])
                                        {
                                            //generate mail2ticket token access
                                            $token = bin2hex(random_bytes(32));
                                            $qry=$db->prepare("INSERT INTO `ttoken` (`date`,`token`,`action`,`user_id`,`ip`) VALUES (NOW(),:token,'mail2ticket',:user_id,:ip)");
                                            $qry->execute(array('token' => $token,'user_id' => $_SESSION['user_id'],'ip' => $_SERVER['REMOTE_ADDR']));
                                        }
                                        echo '
                                            <label class="lbl" for="imap_server" ><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Serveur').' :</label>
                                            <input style="width:auto" class="form-control  d-inline-block" name="imap_server" id="imap_server" type="text" value="'.$rparameters['imap_server'].'" size="20" />
                                            <i data-toggle="tooltip" id="tooltip12" data-placement="auto" data-original-title="'.T_("Adresse IP ou nom netbios ou nom FQDN du serveur IMAP de messagerie (ex: imap.free.fr, imap.gmail.com, outlook.office365.com").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                            ';
                                            if($rparameters['imap_server']!='outlook.office365.com' && $rparameters['imap_auth_type']=='oauth_azure')
                                            {
                                                echo '<i data-toggle="tooltip" id="tooltip13" data-placement="auto" data-original-title="'.T_("Le serveur doit être outlook.office365.com dans cette configuration").'" class="fa fa-exclamation-triangle text-warning"><!----></i>';
                                            }
                                            echo '
                                            <div class="pt-1"></div>
                                        
                                            <label class="lbl" for="imap_port"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Port').' :</label>
                                            <select style="width:auto" class="form-control  d-inline-block" id="imap_port" name="imap_port" >
                                                <option ';if($rparameters['imap_port']=='143') {echo "selected ";} echo ' value="143">143 (IMAP)</option>
                                                <option ';if($rparameters['imap_port']=="993/imap/ssl") {echo "selected ";} echo ' value="993/imap/ssl">993 (IMAP sécurisé)</option>
                                            </select>
                                            <i data-toggle="tooltip" id="tooltip14" data-placement="auto" data-original-title="'.T_("Protocole utilisé sur le serveur POP ou IMAP sécurisé ou non (ex: pour free.fr sélectionner IMAP, pour gmail utiliser IMAP sécurisé").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                            ';
                                            //add control
                                            if($rparameters['imap_auth_type']=='oauth_azure' && $rparameters['imap_port']!='993/imap/ssl')
                                            {
                                                echo '<i data-toggle="tooltip" id="tooltip15" data-placement="auto" data-original-title="'.T_("Le serveur port pour l'authentification XOAUTH2 Entra ID (Azure AD) est 993").'" class="fa fa-exclamation-triangle text-warning"><!----></i>';
                                            }
                                            echo '
                                            <div class="pt-1"></div>
                                        
                                            <label class="lbl" for="imap_ssl_check"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Vérification SSL').' :</label>
                                            <select style="width:auto" class="form-control  d-inline-block"  id="imap_ssl_check" name="imap_ssl_check" >
                                                <option ';if($rparameters['imap_ssl_check']==1) echo "selected "; echo' value="1">'.T_('Activée').'</option>
                                                <option ';if($rparameters['imap_ssl_check']==0) echo "selected "; echo' value="0">'.T_('Désactivée').'</option>
                                            </select>
                                            <i data-toggle="tooltip" id="tooltip16" data-placement="auto" data-original-title="'.T_("Désactivation de la verification du certificat serveur et autorise les certificats auto-signés").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                            <div class="pt-1"></div>
                                            <label class="lbl" for="imap_inbox"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Dossier racine').' :</label>
                                            <select style="width:auto" class="form-control  d-inline-block" id="imap_inbox" name="imap_inbox" >
                                                <option ';if($rparameters['imap_inbox']=='INBOX') {echo "selected ";} echo ' value="INBOX">INBOX</option>
                                                <option ';if($rparameters['imap_inbox']=='') {echo "selected ";} echo ' value="">'.T_('Aucun').'</option>
                                            </select>
                                            <i data-toggle="tooltip" id="tooltip17" data-placement="auto" data-original-title="'.T_("Dossier racine ou se trouve les messages entrants (par défaut INBOX, pour gmail INBOX)").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                            ';
                                            if($rparameters['imap_server']=='outlook.office365.com' && $rparameters['imap_auth_type']=='oauth_azure' && $rparameters['imap_inbox']!='INBOX')
                                            {
                                                echo '<i data-toggle="tooltip" id="tooltip18" data-placement="auto" data-original-title="'.T_("Le dossier racine doit être INBOX dans cette configuration").'" class="fa fa-exclamation-triangle text-warning"><!----></i>';
                                            }
                                            echo '
                                            <div class="pt-1"></div>

                                            <label class="lbl" for="imap_inbox"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Date de création du ticket').' :</label>
                                            <select style="width:auto" class="form-control  d-inline-block" id="imap_date_create" name="imap_date_create" >
                                                <option ';if($rparameters['imap_date_create']=='date_mail') {echo "selected ";} echo ' value="date_mail">'.T_('Date du mail (Défaut)').'</option>
                                                <option ';if($rparameters['imap_date_create']=='date_system') {echo "selected ";} echo ' value="date_system">'.T_('Date de la relève').'</option>
                                            </select>
                                            <i data-toggle="tooltip" id="tooltip19" data-placement="auto" data-original-title="'.T_("Permet de définir la date de création du ticket, soit prise en compte de la date du mail soit la date ou le connecteur à créer le ticket").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                            <div class="pt-1"></div>

                                            <label class="lbl" for="imap_user"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Adresse de messagerie').' :</label>
                                            <input style="width:auto" class="form-control  d-inline-block" id="imap_user" name="imap_user" type="text" value="'.$rparameters['imap_user'].'" size="25" />
                                            <i data-toggle="tooltip" id="tooltip20" data-placement="auto" data-original-title="'.T_("Adresse de la boite de messagerie à relever, pour exchange mettre le login utilisateur de la boite aux lettres ou le nom FQDN de l'utilisateur exemple: user@domain.local").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                            <div class="pt-1"></div>

                                            <label class="lbl" for="imap_auth_type"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_("Type d'authentification").' :</label>
                                            <select style="width:auto" class="form-control  d-inline-block" id="imap_auth_type" name="imap_auth_type" >
                                                <option ';if($rparameters['imap_auth_type']=='login') {echo "selected "; $provider='login';} echo ' value="login" >LOGIN (Défaut)</option>
                                                <option ';if($rparameters['imap_auth_type']=='login2') {echo "selected "; $provider='login2';} echo ' value="login2" >LOGIN v2</option>
                                                <option ';if($rparameters['imap_auth_type']=='oauth_azure') {echo "selected "; $provider='imap_azure';} echo ' value="oauth_azure">'.T_('XOAUTH2 Azure').'</option>
                                                <option ';if($rparameters['imap_auth_type']=='oauth_google') {echo "selected "; $provider='imap_google';} echo ' value="oauth_google">'.T_('XOAUTH2 Google').'</option>
                                            </select>
                                            ';
                                            //display warning for office 365 and OAuth2
                                            if($rparameters['imap_server']=='outlook.office365.com' && $rparameters['imap_auth_type']!='oauth_azure')
                                            {
                                                echo'<i data-toggle="tooltip" id="tooltip70" data-placement="auto" data-original-title="'.T_("Le type d'authentification pour le serveur outlook.office365.com est l'OAuth2 Azure").'" class="fa fa-exclamation-triangle text-warning"><!----></i>';
                                            }
                                            echo '
                                            <div class="pt-1"></div>

                                            <div id="imap_oauth_parameters">
                                                <label class="lbl" for="imap_oauth_client_id"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('ID client').' :</label>
                                                <input style="width:512px" class="form-control  d-inline-block" name="imap_oauth_client_id" id="imap_oauth_client_id" type="text" value="'.$rparameters['imap_oauth_client_id'].'" size="20" />
                                                <div class="pt-1"></div>

                                                <div id="imap_oauth_tenant_id_section">
                                                    <label class="lbl" for="imap_oauth_tenant_id"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('ID locataire').' :</label>
                                                    <input style="width:512px" class="form-control  d-inline-block" id="imap_oauth_tenant_id" name="imap_oauth_tenant_id" type="text" value="'.$rparameters['imap_oauth_tenant_id'].'" size="20" />
                                                    <div class="pt-1"></div>
                                                </div>
                                            
                                                <label class="lbl" for="imap_oauth_client_secret"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Secret client').' :</label>
                                                <input style="width:512px" class="form-control  d-inline-block" id="imap_oauth_client_secret" name="imap_oauth_client_secret" type="password" value="'.$rparameters['imap_oauth_client_secret'].'" size="20" />
                                                <div class="pt-1"></div>

                                                <span class="lbl" for="imap_oauth_refresh_token"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Refresh token').' :</span>
                                                '.substr($rparameters['imap_oauth_refresh_token'],0,24).'*****************
                                                <a class="btn btn-xs btn-info" target="_blank" href="'.$rparameters['server_url'].'/get_oauth_token.php?provider='.$provider.'">'.T_('Générer Refresh Token').'</a>
                                                <div class="pt-1"></div>
                                        
                                                <i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Procédure de configuration ').' :
                                                <div id="imap_oauth_azure_procedure">
                                                    <ul>
                                                        <li><a target="about_blank" href="https://doc.gestsup.fr/faq/#comment-configurer-le-connecteur-imap-entra-id-azure-ad-avec-authentification-par-xoauth2" >'.T_("Procédure IMAP XOAUTH2 Entra ID (Azure AD)").'</a></li>
                                                        <li>'.T_('Portail Microsoft Azure').' : <a target="_blank" href="https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationsListBlade">https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationsListBlade</a></li>
                                                        <li>
                                                            '.T_("URI de redirection").' : 
                                                            <ul>
                                                                <li>
                                                                    '.$rparameters['server_url'].'/get_oauth_token.php
                                                                    <i title="'.T_('Copier dans le presse papier').'" onclick="setClipboard(\''.$rparameters['server_url'].'/get_oauth_token.php\')" class="fa fa-clipboard text-primary-m2"></i>
                                                                </li>
                                                                <li>
                                                                    '.$rparameters['server_url'].'/mail2ticket.php
                                                                    <i title="'.T_('Copier dans le presse papier').'" onclick="setClipboard(\''.$rparameters['server_url'].'/mail2ticket.php\')" class="fa fa-clipboard text-primary-m2"></i>
                                                                </li>                                                   
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div id="imap_oauth_google_procedure">
                                                    <ul>
                                                        <li><a target="about_blank" href="https://doc.gestsup.fr/faq/#comment-configurer-le-connecteur-imap-gmail-avec-authentification-par-xoauth2" >'.T_("Procédure IMAP XOAUTH2 Google").'</a></li>
                                                        <li>'.T_('Console développeur Google').' : <a target="_blank" href="https://console.cloud.google.com/apis/dashboard">https://console.cloud.google.com/apis/dashboard</a></li>
                                                        <li>
                                                            '.T_("URI de redirection autorisé").' : 
                                                            <ul>
                                                                <li>
                                                                    '.$rparameters['server_url'].'/get_oauth_token.php
                                                                    <i title="'.T_('Copier dans le presse papier').'" onclick="setClipboard(\''.$rparameters['server_url'].'/get_oauth_token.php\')" class="fa fa-clipboard text-primary-m2"></i>
                                                                </li>
                                                                <li>
                                                                    '.$rparameters['server_url'].'/mail2ticket.php
                                                                    <i title="'.T_('Copier dans le presse papier').'" onclick="setClipboard(\''.$rparameters['server_url'].'/mail2ticket.php\')" class="fa fa-clipboard text-primary-m2"></i>
                                                                </li>                                                   
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>

                                            <div id="imap_password_section">
                                                <label class="lbl" for="imap_password"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Mot de passe').' :</label>
                                                <input style="width:auto" class="form-control  d-inline-block" name="imap_password" id="imap_password" type="password" value="" size="20" /><br /><div class="pt-1"></div>
                                            </div>
                                            
                                            <input type="checkbox" '; if($rparameters['imap_reply']) {echo "checked";} echo ' name="imap_reply" id="imap_reply" value="1">
                                            <label class="lbl" for="imap_reply">'.T_('Gérer les réponses aux mails').'</label>
                                            <i data-toggle="tooltip" id="tooltip21" data-placement="auto" data-original-title="'.T_("Ajoute des délimiteurs dans le mail, indiquant à l'utilisateur qu'il peut répondre au message envoyé").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                            <div class="pt-1"></div>

                                            <div id="imap_mailbox_service_section">
                                                <input type="checkbox" '; if($rparameters['imap_mailbox_service']==1) {echo "checked";} echo ' name="imap_mailbox_service" id="imap_mailbox_service" value="1">
                                                <label class="lbl" for="imap_mailbox_service">'.T_('Activer le multi boite aux lettres par service').' </label>
                                                <i data-toggle="tooltip" id="tooltip22" data-placement="auto" data-original-title="'.T_("Permet de relever plusieurs boites aux lettres et d'associer les tickets crées à des services GestSup").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                                <div class="pt-1"></div>
                                            </div>

                                            <input type="checkbox" '; if($rparameters['imap_auto_create_user']) {echo "checked";} echo ' name="imap_auto_create_user" id="imap_auto_create_user" value="1">
                                            <label class="lbl" for="imap_auto_create_user">'.T_('Création automatique des utilisateurs').'</label>
                                            <i data-toggle="tooltip" id="tooltip72" data-placement="auto" data-original-title="'.T_("Si l'adresse mail de l'émetteur est inconnue alors un nouvel utilisateur GestSup est crée puis associé au ticket").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                            <div class="pt-1"></div>

                                            ';
                                            //display parameters of imap_mailbox_service
                                            if($rparameters['imap_mailbox_service'])
                                            {
                                                echo "<ul>";
                                                //display all existing association
                                                $qry = $db->prepare("SELECT * FROM `tparameters_imap_multi_mailbox`");
                                                $qry->execute();
                                                while ($row = $qry->fetch()) 
                                                {
                                                    //get service name do display
                                                    $qry2 = $db->prepare("SELECT `name` FROM `tservices` WHERE `id`=:id");
                                                    $qry2->execute(array('id' => $row['service_id']));
                                                    $row2=$qry2->fetch();
                                                    $qry2->closeCursor();
                                                    if(empty($row2['name'])) {$row2=array(); $row2['name']='';}
                                                    if(empty($row['mail'])) {$row=array(); $row['mail']='';}
                                                    echo '<li>
                                                            '.$row['mail'].' > '.$row2['name'].' 
                                                            ('.T_("type d'authentification").' : '.$row['mailbox_service_auth_type'].' '.substr($row['mailbox_service_oauth_refresh_token'],0,24).') 
                                                            ';
                                                            if($row['mailbox_service_auth_type']=='oauth_google')
                                                            {
                                                                echo '<a class="btn btn-xs btn-info" target="_blank" href="'.$rparameters['server_url'].'/get_oauth_token.php?provider=imap_google_service&mailboxid='.$row['id'].'">'.T_('Générer Refresh Token').'</a>';
                                                            }
                                                            if($row['mailbox_service_auth_type']=='oauth_azure')
                                                            {
                                                                echo '<a class="btn btn-xs btn-info mt-1" target="_blank" href="'.$rparameters['server_url'].'/get_oauth_token.php?provider=imap_azure_service&mailboxid='.$row['id'].'">'.T_('Générer Refresh Token').'</a>';
                                                            }
                                                            echo '
                                                            <a onClick="javascript: return confirm(\''.T_('Êtes-vous sur de vouloir supprimer cette boite aux lettres ?').'\');" href="./index.php?page=admin&subpage=parameters&tab=connector&delete_imap_service='.$row['id'].'"><i title="'.T_("Supprimer l'association").'" class="fa fa-trash text-danger bigger-130"><!----></i></a>
                                                        </li>';
                                                }
                                                $qry->closeCursor();
                                                echo "</ul>";
                                                //inputs for new association
                                                echo '&nbsp;&nbsp;&nbsp;
                                                <label class="lbl" for="mailbox_service">'.T_('Adresse mail').' :</label> <input style="width:auto" class="form-control  d-inline-block" id="mailbox_service" name="mailbox_service" type="text" value="" size="20" />&nbsp
                                                <label class="lbl" for="mailbox_service_auth_type">'.T_("Type d'authentification").' :</label> 
                                                <select style="width:auto" class="form-control  d-inline-block" name="mailbox_service_auth_type" id="mailbox_service_auth_type">
                                                    <option value="login">'.T_('LOGIN').'</option>
                                                    <option value="login2">'.T_('LOGIN v2').'</option>
                                                    <option value="oauth_google">'.T_('XOAUTH2 Google').'</option>
                                                    <option value="oauth_azure">'.T_('XOAUTH2 Azure').'</option>
                                                </select>&nbsp
                                                <label class="lbl" for="mailbox_service_id">'.T_('Service').' :</label> 
                                                <select style="width:auto" class="form-control  d-inline-block" id="mailbox_service_id" name="mailbox_service_id" >
                                                    ';
                                                    $qry = $db->prepare("SELECT `id`,`name` FROM `tservices` WHERE `disable`='0'");
                                                    $qry->execute();
                                                    while ($row = $qry->fetch()) {echo'<option value="'.$row['id'].'">'.$row['name'].'</option>';}
                                                    $qry->closeCursor();
                                                    echo '
                                                </select>
                                                <span id="mailbox_password_section">
                                                    <label class="lbl" for="mailbox_password">'.T_('Mot de passe').' :</label> 
                                                    <input style="width:auto" class="form-control  d-inline-block" name="mailbox_password" type="password" value="" size="20" />&nbsp
                                                </span>
                                                <span class="ml-2" id="mailbox_oauth_section">
                                                    <br />
                                                    &nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-caret-right text-primary-m2"><!----></i> <label class="lbl" for="mailbox_service_oauth_client_id">'.T_('ID client').' :</label> 
                                                    <input style="width:512px" class="form-control  d-inline-block" name="mailbox_service_oauth_client_id" type="text" value="" size="20" /><br />
                                                    
                                                    <span id="mailbox_tenant_id_section">
                                                        &nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-caret-right text-primary-m2"><!----></i> <label class="lbl" for="mailbox_service_oauth_tenant_id">'.T_('ID locataire').' :</label>
                                                        <input style="width:512px" class="form-control  d-inline-block" name="mailbox_service_oauth_tenant_id" type="text" value="" size="20" /><br />
                                                    </span>
                                                    &nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-caret-right text-primary-m2"><!----></i> <label class="lbl" for="mailbox_service_oauth_client_secret">'.T_('Secret client').' :</label> 
                                                    <input style="width:512px" class="form-control  d-inline-block" name="mailbox_service_oauth_client_secret" type="password" value="" size="20" /><br /> 
                                                </span>
                                            
                                                <div class="pt-2"></div>
                                                <label class="ml-3" >
                                                    <input type="checkbox" '; if($rparameters['imap_from_adr_service']) {echo "checked";} echo ' name="imap_from_adr_service" value="1">
                                                    <span class="lbl">&nbsp'.T_("Utiliser l'adresse mail du service pour l'émission des mails").'
                                                    <i data-toggle="tooltip" id="tooltip23" data-placement="auto" data-original-title="'.T_("Utilise l'adresse mail du service en tant qu'émetteur des mails, si un service un paramétré sur le ticket. A noter certains serveurs de messagerie n'accepte pas ce paramétrage").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                                </label>
                                                <div class="pt-1"></div>
                                                ';
                                            }
                                            echo '
                                            <label class="lbl" for="imap_blacklist"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Adresses à exclure').' :</label>
                                            <input style="width:auto" class="form-control  d-inline-block" name="imap_blacklist" id="imap_blacklist" type="text" value="'.$rparameters['imap_blacklist'].'" size="60" />
                                            <i data-toggle="tooltip" id="tooltip24" data-placement="auto" data-original-title="'.T_("Permet d'ajouter des adresses mail et/ou des domaines à exclure de la récupération des messages. Le séparateur est le point virgule exemple: john.doe@example.com;example2.com;outlook").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                            <div class="pt-1"></div>
                                            <label class="lbl" for="imap_post_treatment"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Action post-traitement').' :</label>
                                            <select style="width:auto" class="form-control  d-inline-block" id="imap_post_treatment" name="imap_post_treatment" >
                                                <option ';if($rparameters['imap_post_treatment']=='move') {echo "selected ";} echo ' value="move">'.T_('Déplacer le mail dans un répertoire').'</option>
                                                <option ';if($rparameters['imap_post_treatment']=='delete') {echo "selected ";} echo ' value="delete">'.T_('Supprimer le mail').'</option>
                                                <option ';if($rparameters['imap_post_treatment']=='') {echo "selected ";} echo ' value="">'.T_('Passer en lu le mail').'</option>
                                            </select>
                                            <i data-toggle="tooltip" id="tooltip25" data-placement="auto" data-original-title="'.T_("Permet de spécifier une action sur le mail de la boite aux lettre, une fois le mail convertit en ticket").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                            ';
                                            if($rparameters['imap_post_treatment']=='move') {echo '<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.T_('Répertoire').': <input name="imap_post_treatment_folder" style="width:auto" class="form-control  d-inline-block mt-2" type="text" value="'.$rparameters['imap_post_treatment_folder'].'"  /> <i title="'.T_('Permet de spécifier un répertoire de la messagerie dans lequel le mail sera déplacé exemple: INBOX/vu').'" class="fa fa-question-circle text-primary-m2"><!----></i>';}
                                            if($rparameters['imap'])
                                            {
                                                echo'
                                                <div class="pt-2"></div>
                                                <button id="test_imap" name="test_imap" value="1" type="submit" OnClick="window.open(\'./mail2ticket.php?token='.$token.'\')"  class="btn btn-xs btn-success">
                                                    <i class="fa fa-download"><!----></i>
                                                    '.T_("Lancer l'import des mails").'
                                                </button>
                                            ';
                                            }
                                        ?>
                                    </div>

                                </div>
                            </div>
                            <!-- tab ldap -->
                            <div class="tab-pane fade text-95 <?php if($_GET['subtab']=='ldap') {echo 'show active';}?> ">
                                <div class="text-primary-d3 text-110 mb-2">
                                    <label>
                                        <input id="ldap" name="ldap" type="checkbox" <?php if($rparameters['ldap']==1) echo "checked"; ?>  value="1">
                                        <span class="lbl"><?php echo T_('Activer la liaison LDAP'); ?> </span>	
                                        <i data-toggle="tooltip" id="tooltip26" data-placement="auto" data-original-title="<?php echo T_("Connecteur permettant la synchronisation entre l'annuaire d'entreprise (Active Directory ou OpenLDAP) et GestSup"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    </label>
                                    <div id="ldap_parameters">
                                        <?php
                                        echo '
                                        <div class="pt-1"></div>
                                        <label>
                                            <input type="checkbox"'; if($rparameters['ldap_auth']==1) echo "checked"; echo ' name="ldap_auth" value="1">
                                            <span class="lbl">&nbsp;'.T_("Activer l'authentification GestSup avec LDAP").'
                                            <i data-toggle="tooltip" id="tooltip27" data-placement="auto" data-original-title="'.T_("Active l'authentification des utilisateurs dans Gestsup, avec les identifiants présents dans l'annuaire LDAP. Cela ne désactive pas l'authentification avec la base utilisateurs de GestSup").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        </label>
                                        <div class="pt-1"></div>
                                        <label>
                                            <input type="checkbox"'; if($rparameters['ldap_sso']==1) echo "checked"; echo ' name="ldap_sso" value="1">
                                            <span class="lbl">&nbsp;'.T_("Activer le SSO").'
                                            <i data-toggle="tooltip" id="tooltip28" data-placement="auto" data-original-title="'.T_("Permet la connexion d'un utilisateur sans la saisie de l'identifiant et du mot de passe, sur un poste Windows connecté à un domaine Active Directory, cf documentation").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        </label>
                                        <div class="pt-1"></div>
                                        <label class="lbl">
                                            <input type="checkbox"'; if($rparameters['ldap_service']==1) echo "checked"; echo ' name="ldap_service" value="1">
                                            <span class="lbl">&nbsp;'.T_("Activer la synchronisation des groupes LDAP de services").'
                                            <i data-toggle="tooltip" id="tooltip29" data-placement="auto" data-original-title="'.T_("Permet de synchroniser des groupes LDAP de service: création, renommage, désactivation de services GestSup, création utilisateurs GestSup membres du groupe LDAP et association entre les deux. (Tous les utilisateurs doivent appartenir à un groupe)").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        </label>
                                        <div class="pt-1"></div>
                                        ';
                                        if($rparameters['ldap_service']==1 )
                                        {
                                            echo '
                                            <label class="lbl ml-4" for="ldap_service_url">'.T_("Emplacement des groupes de service").' :</label>
                                            <input style="width:auto" class="form-control  d-inline-block" name="ldap_service_url" type="text" value="'.$rparameters['ldap_service_url'].'" size="50" />
                                            <i data-toggle="tooltip" id="tooltip30" data-placement="auto" data-original-title="'.T_("Emplacement des groupes de service dans l'annuaire LDAP. (exemple: ou=service, ou=utilisateurs) Attention il ne doit pas être suffixé du domaine").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                            <div class="pt-1"></div>
                                            ';
                                        }
                                        if($rparameters['user_agency']==1)
                                        {
                                            echo '
                                            <label class="lbl">
                                                <input type="checkbox"'; if($rparameters['ldap_agency']==1) echo "checked"; echo ' name="ldap_agency" value="1">
                                                <span class="lbl">&nbsp;'.T_("Activer la synchronisation des groupes LDAP d'agences").'
                                                <i data-toggle="tooltip" id="tooltip31" data-placement="auto" data-original-title="'.T_("Permet de synchroniser des groupes LDAP d'agence: création, renommage, désactivation d'agences GestSup, création utilisateurs GestSup membres du groupe LDAP et association entre les deux. (Tous les utilisateurs doivent appartenir à un groupe)").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                            </label>
                                            <div class="pt-1"></div>
                                            ';
                                            if($rparameters['ldap_agency']==1)
                                            {
                                                echo '
                                                <label class="lbl ml-4" for="ldap_agency_url">'.T_("Emplacement des groupes d'agence").' :</label>
                                                <input style="width:auto" class="form-control  d-inline-block"  name="ldap_agency_url" type="text" value="'.$rparameters['ldap_agency_url'].'" size="50" />
                                                <i data-toggle="tooltip" id="tooltip32" data-placement="auto" data-original-title="'.T_("Emplacement des groupes d'agences dans l'annuaire LDAP. (exemple: ou=groupe_agence, ou=utilisateurs) Attention il ne doit pas être suffixé du domaine").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                                <div class="pt-1"></div>
                                                <label class="lbl ml-4" for="ldap_agency_url">'.T_("Déplacer les tickets associés à l'agence").' :</label>
                                                <select style="width:auto" class="form-control  d-inline-block"  id="from_agency" name="from_agency" />
                                                    ';
                                                    $qry = $db->prepare("SELECT `id`,`name` FROM `tagencies` ORDER BY name");
                                                    $qry->execute();
                                                    while ($row=$qry->fetch()){echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';}
                                                    $qry->closeCursor();
                                                    echo'
                                                </select>
                                                <label class="lbl " for="dest_agency">'.T_("vers l'agence").' :</label>
                                                <select style="width:auto" class="form-control  d-inline-block"  id="dest_agency" name="dest_agency" />
                                                ';
                                                    $qry = $db->prepare("SELECT `id`,`name` FROM `tagencies` ORDER BY name");
                                                    $qry->execute();
                                                    while ($row=$qry->fetch())	
                                                    {
                                                        echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
                                                    }
                                                    $qry->closeCursor();
                                                    echo'
                                                </select>
                                                <div class="pt-1"></div>
                                                ';
                                            }
                                        }
                                        echo '
                                        <label class="lbl" for="ldap_type"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Type de serveur').' : </label>
                                        <select style="width:auto" class="form-control  d-inline-block" id="ldap_type" name="ldap_type" >
                                            <option ';if($rparameters['ldap_type']==0) echo "selected "; echo ' value="0">Active Directory</option>
                                            <option ';if($rparameters['ldap_type']==1) echo "selected "; echo ' value="1">OpenLDAP</option>
                                            <option ';if($rparameters['ldap_type']==3) echo "selected "; echo ' value="3">Samba4</option>
                                            <option ';if($rparameters['ldap_type']==4) echo "selected "; echo ' value="4">Kwartz</option>
                                        </select>
                                        <i data-toggle="tooltip" id="tooltip33" data-placement="auto" data-original-title="'.T_("Sélectionner si votre serveur d'annuaire est Windows Active Directory ou OpenLDAP").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        <div class="pt-1"></div>
                                        <label class="lbl" for="ldap_server"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Serveur').' :</label>
                                        <input style="width:auto" class="form-control  d-inline-block" name="ldap_server" id="ldap_server" type="text" value="'.$rparameters['ldap_server'].'" size="20" />
                                        <i data-toggle="tooltip" id="tooltip34" data-placement="auto" data-original-title="'.T_("Adresse IP ou nom netbios du serveur d'annuaire, sans suffixe DNS (Exemple: 192.168.0.1 ou SRVDC1").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        <div class="pt-1"></div>
                                        <label class="lbl" for="ldap_port"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Port').' : </label>
                                        <select style="width:auto" class="form-control  d-inline-block" id="ldap_port" name="ldap_port" >
                                            <option ';if($rparameters['ldap_port']==389) echo "selected "; echo ' value="389">389</option>
                                            <option ';if($rparameters['ldap_port']==636) echo "selected "; echo ' value="636">636</option>
                                        </select>
                                        <i data-toggle="tooltip" id="tooltip35" data-placement="auto" data-original-title="'.T_("Le port par défaut est 389 si vous utilisez un serveur LDAPS (sécurisé) le port est 636. Si vous rencontrez des difficultés avec le port 636 vous pouvez ajouter TLS_REQCERT never dans le fichier /etc/ldap/ldap.conf").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        <div class="pt-1"></div>
                                        <label class="lbl" for="ldap_domain"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Domaine').' :</label>
                                        <input style="width:auto" class="form-control  d-inline-block" name="ldap_domain" id="ldap_domain" type="text" value="'.$rparameters['ldap_domain'].'" size="20" />
                                        <i data-toggle="tooltip" id="tooltip36" data-placement="auto" data-original-title="'.T_("Nom du domaine FQDN (Exemple: exemple.local)").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        <div class="pt-1"></div>
                                        <label class="lbl" for="ldap_url"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Emplacement des utilisateurs').' :</label>
                                        <input style="width:auto" class="form-control  d-inline-block" name="ldap_url" id="ldap_url" type="text" value="'.$rparameters['ldap_url'].'" size="80" />
                                        <i data-toggle="tooltip" id="tooltip37" data-placement="auto" data-original-title="'.T_("Emplacement dans l'annuaire des utilisateurs. Par défaut pour Active Directory cn=users, si vous utilisez plusieurs unités d'organisation séparer avec un point virgule (ou=France,ou=utilisateurs;ou=Belgique,ou=utilisateurs) Attention il ne doit pas être suffixé du domaine").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        <div class="pt-1"></div>
                                        ';
                                            if($rparameters['ldap_type']==0)
                                            {
                                                echo '
                                                <label class="lbl" for="ldap_login_field"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Champ identifiant').' : </label>
                                                <select style="width:auto" class="form-control  d-inline-block" id="ldap_login_field" name="ldap_login_field" >
                                                    <option ';if($rparameters['ldap_login_field']=='SamAcountName') {echo "selected ";} echo ' value="SamAcountName">SamAcountName</option>
                                                    <option ';if($rparameters['ldap_login_field']=='UserPrincipalName') {echo "selected ";} echo ' value="UserPrincipalName">UserPrincipalName</option>
                                                </select>
                                                <i data-toggle="tooltip" id="tooltip38" data-placement="auto" data-original-title="'.T_("Permet de configurer le champ AD à utiliser pour le login GestSup").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                                <div class="pt-1"></div>
                                                ';
                                            }
                                            
                                        echo '
                                        <label class="lbl" for="ldap_user"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Utilisateur').' : </label>
                                        <input style="width:auto" class="form-control  d-inline-block" name="ldap_user" id="ldap_user" type="text" value="'.$rparameters['ldap_user'].'" size="20" />
                                        <i data-toggle="tooltip" id="tooltip39" data-placement="auto" data-original-title="'.T_("Utilisateur présent dans l'annuaire LDAP, pour OpenLDAP l'utilisateur doit être à la racine et de type CN").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        <div class="pt-1"></div>
                                        <label class="lbl" for="ldap_password"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Mot de passe').' :</label>
                                        <input style="width:auto" class="form-control  d-inline-block" name="ldap_password" id="ldap_password" type="password" value="" size="20" /><br />
                                        ';
                                        if($rparameters['ldap_agency']==0 && $rparameters['ldap_service']==0)
                                        {
                                            echo '
                                            <i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Désactiver les utilisateurs GestSup lors de la synchronisation').' : 
                                            <select style="width:auto" class="form-control  d-inline-block" id="ldap_disable_user" name="ldap_disable_user" >
                                                <option ';if($rparameters['ldap_disable_user']==0) echo "selected "; echo ' value="0">Non</option>
                                                <option ';if($rparameters['ldap_disable_user']==1) echo "selected "; echo ' value="1">Oui</option>
                                            </select>
                                            <i data-toggle="tooltip" id="tooltip40" data-placement="auto" data-original-title="'.T_("Désactive les utilisateurs présents dans GestSup, mais qui ne sont pas présent dans l'annuaire LDAP").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                            <div class="pt-1"></div>
                                            ';
                                        }
                                        echo '
                                        <br />
                                        <button name="test_ldap" value="1" type="submit" class="btn btn-xs btn-success">
                                            <i class="fa fa-exchange-alt"><!----></i>
                                            '.T_('Test du connecteur LDAP').'
                                        </button>
                                        ';
                                        //check LDAP parameters
                                        if($_GET['ldaptest']==1) {
                                            
                                            if($rparameters['ldap_sso']==1) {
                                                if(isset($_SERVER['REMOTE_USER']))
                                                {
                                                    echo DisplayMessage('success',T_('Le SSO est opérationnel'));
                                                } else {
                                                    echo DisplayMessage('error',T_('Le SSO ne fonctionne pas vérifier votre configuration serveur'));
                                                }
                                            }
                                            include('./core/ldap.php');
                                            echo $ldap_connection;
                                        } 
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <!-- tab azure -->
                            <div class="tab-pane fade text-95 <?php if($_GET['subtab']=='azure') {echo 'show active';}?> ">
                                <div class="text-primary-d3 text-110 mb-2">
                                    <label>
                                        <input id="azure_ad" name="azure_ad" type="checkbox" <?php if($rparameters['azure_ad']) echo "checked"; ?>  value="1">
                                        <span class="lbl"><?php echo T_('Activer la liaison Entra ID (Azure AD)'); ?> </span>	
                                        <i data-toggle="tooltip" id="tooltip41" data-placement="auto" data-original-title="<?php echo T_("Connecteur la synchronisation entre un annuaire d'utilisateurs Entra ID (Azure AD) et GestSup"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    </label>
                                    <div id="azure_ad_parameters">

                                        <label class="lbl" for="azure_ad_tenant_number"><i class="fa fa-caret-right text-primary-m2"><!----></i> <?php echo T_('Nombre de locataires'); ?> :</label>
                                        <input style="width:50px" class="form-control  d-inline-block" name="azure_ad_tenant_number" id="azure_ad_tenant_number" type="text" value="<?php echo $rparameters['azure_ad_tenant_number']; ?>" />
                                        <div class="pt-1"></div>

                                        <?php
                                        //multi-tenant case
                                        if($rparameters['azure_ad_tenant_number']>=2){$multi_tenant=true; $margin='ml-4';} else {$multi_tenant=false; $margin='';}

                                        //for each tenant
                                        for($i = 1; $i <= $rparameters['azure_ad_tenant_number']; $i++) {

                                            $qry=$db->prepare("SELECT * FROM `tentra_tenant` WHERE `id`=:id");
                                            $qry->execute(array('id' => $i));
                                            $tenant=$qry->fetch();
                                            $qry->closeCursor();

                                            //get parameters for this tenant
                                            if($multi_tenant) {
                                                echo '
                                                <div id="azure_ad_tenant_1_label"><i class="fa fa-circle text-success"><!----></i> 
                                                    '.T_('Locataire').' '.$i.' :
                                                    <a href="index.php?page=admin&subpage=parameters&tab=connector&subtab=azure&delete='.$tenant['id'].'">
                                                        <i title="'.T_('Supprimer ce locataire').'" class="fa fa-trash text-danger"><!----></i>
                                                    </a>
                                                </div>';
                                            }
                                            echo '
                                                <label class="lbl '.$margin.'" for="entra_tenant_name_'.$i.'"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Nom locataire').' :</label>
                                                <input style="width:512px" class="form-control  d-inline-block" name="entra_tenant_name_'.$i.'" id="entra_tenant_name_'.$i.'" type="text" value="'.$tenant['tenant_name'].'" size="20" />
                                                <div class="pt-1"></div>

                                                <label class="lbl '.$margin.'" for="entra_client_id_'.$i.'" id="azure_ad_client_id_1_label"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('ID client').' :</label>
                                                <input style="width:512px" class="form-control  d-inline-block" name="entra_client_id_'.$i.'" id="entra_client_id_'.$i.'" type="text" value="'.$tenant['client_id'].'" size="20" />
                                                <div class="pt-1"></div>

                                                <label class="lbl '.$margin.'" for="entra_tenant_id_'.$i.'"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('ID locataire').' :</label>
                                                <input style="width:512px" class="form-control  d-inline-block" name="entra_tenant_id_'.$i.'" id="entra_tenant_id_'.$i.'" type="text" value="'.$tenant['tenant_id'].'" size="20" />
                                                <div class="pt-1"></div>

                                                <label class="lbl '.$margin.'" for="entra_client_secret_'.$i.'"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Secret client').' :</label>
                                                <input style="width:512px" class="form-control  d-inline-block" name="entra_client_secret_'.$i.'" id="entra_client_secret_'.$i.'" type="password" value="" size="20" />
                                                <div class="pt-1"></div>

                                                <label class="lbl '.$margin.'" for="entra_group_filter_'.$i.'"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Filtre par groupe').' :</label>
                                                <input style="width:512px" class="form-control  d-inline-block" name="entra_group_filter_'.$i.'" id="entra_group_filter_'.$i.'" type="text" value="'.$tenant['group_filter'].'" size="20" />
                                                <i data-toggle="tooltip" id="tooltip68" data-placement="auto" data-original-title="'.T_("Permet de limiter la synchronisation à un ou plusieurs groupe Entra ID (Azure AD), pour définir plusieurs groupes utiliser le délimiteur point virgule").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                                <div class="pt-1"></div>
                                            ';
                                        }
                                        ?>
                                        
                                        <div class="pt-1"></div>
                                        <label class="lbl" for="azure_uri" id="azure_ad_uri_label"><i class="fa fa-caret-right text-primary-m2"><!----></i> <?php echo T_("URI de redirection autorisée, renseigner ").': '.$rparameters['server_url'].'/azure_ad_auth.php'; ?></label>
                                        <div class="pt-1"></div>

                                        <label class="lbl" for="azure_ad_login_field"><i class="fa fa-caret-right text-primary-m2"><!----></i> <?php echo T_('Champ identifiant'); ?> :</label>
                                        <select style="width:auto" class="form-control  d-inline-block" id="azure_ad_login_field" name="azure_ad_login_field" id="azure_ad_login_field">
                                            <option <?php if($rparameters['azure_ad_login_field']=='UserPrincipalName') {echo 'selected';}?> value="UserPrincipalName">UserPrincipalName (<?php echo T_('Défaut'); ?>)</option>
                                            <option <?php if($rparameters['azure_ad_login_field']=='onPremisesSamAccountName') {echo 'selected';}?> value="onPremisesSamAccountName">onPremisesSamAccountName</option>
                                            <option <?php if($rparameters['azure_ad_login_field']=='onPremisesUserPrincipalName') {echo 'selected';}?> value="onPremisesUserPrincipalName">onPremisesUserPrincipalName</option>
                                        </select>
                                        <i data-toggle="tooltip" id="tooltip42" data-placement="auto" data-original-title="<?php echo T_("Permet de configurer le champ Entra ID (Azure AD) à utiliser pour le login GestSup"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        <div class="pt-1"></div>

                                        <div id="azure_ad_disable_user_parameters">
                                            <label class="lbl" for="azure_ad_disable_user"><i class="fa fa-caret-right text-primary-m2"><!----></i> <?php echo T_('Désactiver les utilisateurs GestSup lors de la synchronisation'); ?> :</label>
                                            <select style="width:auto" class="form-control  d-inline-block" id="azure_ad_disable_user" name="azure_ad_disable_user">
                                                <option <?php if($rparameters['azure_ad_disable_user']==0) {echo 'selected';}?> value="0"><?php echo T_('Non'); ?></option>
                                                <option <?php if($rparameters['azure_ad_disable_user']==1) {echo 'selected';}?> value="1"><?php echo T_('Oui'); ?></option>
                                            </select>
                                            <i data-toggle="tooltip" id="tooltip43" data-placement="auto" data-original-title="<?php echo T_("Désactive les utilisateurs présents dans GestSup, mais qui ne sont pas présent dans Entra ID (Azure AD)"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                            <div class="pt-1"></div>
                                        </div>

                                        <div id="azure_ad_sso_parameters">
                                            <label>
                                                <input id="azure_ad_sso" name="azure_ad_sso" type="checkbox" <?php if($rparameters['azure_ad_sso']) echo "checked"; ?>  value="1">
                                                <span class="lbl"><?php echo T_('Activer le SSO'); ?> </span>	
                                                <i data-toggle="tooltip" id="tooltip65" data-placement="auto" data-original-title="<?php echo T_("Permet de se connecter à l'application avec son mot de passe Entra ID "); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                            </label>
                                            <div class="pt-1"></div>
                                     
                                            <label>
                                                <input id="azure_ad_sso_hide_login" name="azure_ad_sso_hide_login" type="checkbox" <?php if($rparameters['azure_ad_sso_hide_login']) echo "checked"; ?>  value="1">
                                                <span class="lbl"><?php echo T_("Masquer la page d'authentification GestSup"); ?> </span>	
                                                <i data-toggle="tooltip" id="tooltip73" data-placement="auto" data-original-title="<?php echo T_("Permet de se connecter directement avec la mire de connexion Microsoft, il est possible de retrouver la page de connexion GestSup avec l'URL index.php?local_auth=1"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                            </label>
                                            <div class="pt-1"></div>
                                        </div>

                                        <i class="fa fa-caret-right text-primary-m2"><!----></i> <?php echo T_('Procédure et liens'); ?> :
                                        <div id="azure_ad_procedure">
                                            <ul>
                                                <li><a target="about_blank" href="https://doc.gestsup.fr/faq/#comment-configurer-le-connecteur-entra-id-azure-ad"><?php echo T_("Procédure Entra ID (Azure AD)"); ?></a></li>
                                                <li><a target="about_blank" href="https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationsListBlade"><?php echo T_('Portail Microsoft Entra ID (Azure AD)'); ?></a></li>
                                                <li><a target="about_blank" href="https://admin.microsoft.com/AdminPortal/Home#/users"><?php echo T_('Liste des utilisateurs Entra ID (Azure AD)'); ?></a></li>
                                                <li><a target="about_blank" href="index.php?page=admin&subpage=user&azure_ad=1"><?php echo T_('Synchronisation manuelle'); ?></a></li>
                                            </ul>
                                        </div>
                                        <button name="test_azure_ad" value="1" type="submit" class="btn btn-xs btn-success">
                                            <i class="fa fa-exchange-alt"><!----></i>
                                            <?php echo T_('Test du connecteur Entra ID (Azure AD)'); ?>
                                        </button>
                                        <?php
                                            //check Entra ID (Azure AD) parameters
                                            if($_POST['test_azure_ad']) {

                                                //for each tenant
                                                for($id = 1; $id <= $rparameters['azure_ad_tenant_number']; $id++) {
                                                    //get tenant properties
                                                    $qry=$db->prepare("SELECT * FROM `tentra_tenant` WHERE `id`=:id");
                                                    $qry->execute(array('id' => $id));
                                                    $tenant=$qry->fetch();
                                                    $qry->closeCursor();


                                                    //generate access token
                                                    try {
                                                        $guzzle = new \GuzzleHttp\Client(['verify' => false]);
                                                        $url = 'https://login.microsoftonline.com/'.$tenant['tenant_id'].'/oauth2/v2.0/token';
                                                        if(preg_match('/gs_en/',$tenant['client_secret'])) {$tenant['client_secret']=gs_crypt($tenant['client_secret'], 'd' , $rparameters['server_private_key']);}
                                                        $token = json_decode($guzzle->post($url, [
                                                            'form_params' => [
                                                                'client_id' => $tenant['client_id'],
                                                                'client_secret' => $tenant['client_secret'],
                                                                'scope' => 'https://graph.microsoft.com/.default',
                                                                'grant_type' => 'client_credentials',
                                                            ]
                                                        ])->getBody()->getContents());
                                                        $accessToken = $token->access_token;
                                                    }
                                                    catch (GuzzleHttp\Exception\ClientException $e) {
                                                        $response = $e->getResponse();
                                                        $responseBodyAsString = $response->getBody()->getContents();
                                                        $accessToken='';
                                                    }
                                                    if(!$accessToken)
                                                    {
                                                        echo DisplayMessage('error',T_("Erreur de génération du jeton d'accès".$responseBodyAsString));
                                                        LogIt('error','ERROR 37 : ENTRA ID, failed to generate access token '.$tenant['tenant_id'],$_SESSION['user_id']);
                                                    } else {
                                                        //query Entra ID
                                                        $graph = new Graph();
                                                        $graph->setApiVersion('beta');
                                                        $graph->setAccessToken($accessToken);
                                                        $url = '/users?$select=id&filter=&$top=1';
                                                        $qryUsers = $graph->createCollectionRequest("GET", $url)->setReturnType(Model\User::class);
                                                        if(empty($qryUsers))
                                                        {
                                                            echo DisplayMessage('error',T_("Aucun utilisateur trouvé pour le locataire ").$id.' '.$tenant['tenant_name']);
                                                           
                                                        } else {
                                                            echo DisplayMessage('success',T_("Connecteur Entra ID opérationnel locataire ".$id.' '.$tenant['tenant_name']));
                                                        }
                                                    }
                                                }

                                                /*
                                                //check number of tenant
                                                if($rparameters['azure_ad_tenant_number']==1 || $rparameters['azure_ad_tenant_number']==2)
                                                {
                                                   
                                                } 
                                                if($rparameters['azure_ad_tenant_number']==2)
                                                {
                                                    //generate access token
                                                    try {
                                                    $guzzle = new \GuzzleHttp\Client(['verify' => false]);
                                                    $url = 'https://login.microsoftonline.com/'.$rparameters['azure_ad_tenant_id_2'].'/oauth2/v2.0/token';
                                                    if(preg_match('/gs_en/',$rparameters['azure_ad_client_secret_2'])) {$rparameters['azure_ad_client_secret_2']=gs_crypt($rparameters['azure_ad_client_secret_2'], 'd' , $rparameters['server_private_key']);}
                                                    $token = json_decode($guzzle->post($url, [
                                                        'form_params' => [
                                                            'client_id' => $rparameters['azure_ad_client_id_2'],
                                                            'client_secret' => $rparameters['azure_ad_client_secret_2'],
                                                            'scope' => 'https://graph.microsoft.com/.default',
                                                            'grant_type' => 'client_credentials',
                                                        ]
                                                    ])->getBody()->getContents());
                                                    $accessToken = $token->access_token;
                                                }
                                                catch (GuzzleHttp\Exception\ClientException $e) {
                                                    $response = $e->getResponse();
                                                    $responseBodyAsString = $response->getBody()->getContents();
                                                    $accessToken='';
                                                }
                                                if(!$accessToken)
                                                {
                                                    echo DisplayMessage('error',T_("Erreur de génération du jeton d'accès pour le deuxième locataire".$responseBodyAsString));
                                                    LogIt('error','ERROR 37 : AZURE AD, failed to generate access token for tenant 2',$_SESSION['user_id']);
                                                } else {
                                                    //query Entra ID (Azure AD)
                                                    $graph = new Graph();
                                                    $graph->setApiVersion('beta');
                                                    $graph->setAccessToken($accessToken);
                                                    $url = '/users?$select=id&filter=&$top=1';
                                                    $qryUsers = $graph->createCollectionRequest("GET", $url)->setReturnType(Model\User::class);
                                                    if(empty($qryUsers))
                                                    {
                                                        echo DisplayMessage('error',T_("Aucun utilisateur trouvé"));
                                                    } else {
                                                        echo DisplayMessage('success',T_("Connecteur Entra ID (Azure AD) locataire 2 opérationnel"));
                                                    }
                                                }

                                                }
                                                */
                                            } 
                                            
                                            ?>
                                    </div>

                                </div>
                            </div>
                            <!-- tab ocs -->
                            <div class="tab-pane fade text-95 <?php if($_GET['subtab']=='ocs') {echo 'show active';}?> ">
                                <div class="text-primary-d3 text-110 mb-2">
                                    <?php 
                                    echo '
                                    <label>
                                        <input id="ocs" name="ocs" type="checkbox" ';if($rparameters['ocs']) {echo "checked";} echo ' value="1">
                                        <span class="lbl">'.T_("Activer la synchronisation avec OCS").'</span>	
                                        <i data-toggle="tooltip" id="tooltip66" data-placement="auto" data-original-title="'.T_("Connecteur permettant la synchronisation d'équipements GestSup depuis l'API OCS Inventory").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    </label>
                                    <div id="ocs_parameters">
                                        <label class="lbl" for="ocs_server_url"><i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('URL du serveur OCS').' :</label>
                                        <input style="width:512px" class="form-control  d-inline-block" name="ocs_server_url" id="ocs_server_url" type="text" value="'.$rparameters['ocs_server_url'].'" size="20" />
                                        <i data-toggle="tooltip" id="tooltip67" data-placement="auto" data-original-title="'.T_("URL du serveur OCS Inventory (exemple : http://192.168.0.1)").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        <div class="pt-1"></div>
                                        <button name="ocs_button" id="ocs_button" value="1" type="submit" class="btn btn-xs btn-success" onclick="window.open(\'./index.php?page=core/ocs\')">
                                            <i class="fa fa-sync"><!----></i>
                                            Synchroniser
                                        </button>
                                    </div>
                                    ';
                                    ?>
                                </div>
                            </div>
                            <!-- tab API -->
                            <div class="tab-pane fade text-95 <?php if($_GET['subtab']=='api') {echo 'show active';}?> ">
                                <div class="text-primary-d3 text-110 mb-2">
                                    <label>
                                        <input id="api" name="api" type="checkbox" <?php if($rparameters['api']) echo "checked"; ?>  value="1">
                                        <span class="lbl"><?php echo T_("Activer l'API"); ?> </span>	
                                        <i data-toggle="tooltip" id="tooltip60" data-placement="auto" data-original-title="<?php echo T_("API permettant d’interagir avec l'application depuis une application tierce"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    </label>
                                    <div id="api_parameters">
                                        <span class="lbl" for="api_key"><i class="fa fa-caret-right text-primary-m2"><!----></i> <?php echo T_('API KEY'); ?> :</span>
                                        <?php echo substr($rparameters['api_key'],0,24).'*****************'; ?>
                                        <i title="<?php echo T_('Copier dans le presse papier'); ?>" onclick="setClipboard('<?php echo $rparameters['api_key']; ?>')" class="fa fa-clipboard text-primary-m2"></i>
                                        <i data-toggle="tooltip" id="tooltip61" data-placement="auto" data-original-title="<?php echo T_("Clé nécessaire pour l'accès à l'API"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        <div class="pt-1"></div>

                                        <label class="lbl" for="api_client_ip"><i class="fa fa-caret-right text-primary-m2"><!----></i> <?php echo T_('IP Client'); ?> :</label>
                                        <input style="width:512px" class="form-control  d-inline-block" name="api_client_ip" id="api_client_ip" type="text" value="<?php echo $rparameters['api_client_ip']; ?>" size="20" />
                                        <i data-toggle="tooltip" id="tooltip62" data-placement="auto" data-original-title="<?php echo T_("Adresse IP autorisée à communiquer avec l'API, si vide toutes les adresses IP sont autorisées"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        <div class="pt-1"></div>
                                
                                        <span class="lbl" for="api_doc"><i class="fa fa-caret-right text-primary-m2"><!----></i> <?php echo T_('Documentation'); ?> :</span>
                                        <ul>
                                                <li>
                                                    <a target="about_blank" href="<?php echo $rparameters['server_url'].'/vendor/components/swagger-ui'; ?>"><?php echo $rparameters['server_url'].'/vendor/components/swagger-ui'; ?></a>
                                                </li>
                                                <li>
                                                    <?php echo $rparameters['server_url'].'/api/v1/swagger.json'; ?>
                                                    <i title="<?php echo T_('Copier dans le presse papier'); ?>" onclick="setClipboard('<?php echo $rparameters['server_url'].'/api/v1/swagger.json'; ?>')" class="fa fa-clipboard text-primary-m2"></i>

                                                </li>
                                        </ul>
                                        
                                        <div class="pt-1"></div>
                                        
                                    </div>
                                </div>
                            </div>
                            <?php
                            //include plugin
                            $section='connector';
                            include('./plugin.php');
                            ?>
                        </div>
                    </div>
                </div>
            </div>
           
        </div>
        <div class="border-t-1 brc-secondary-l1 bgc-secondary-l4 py-3 text-center">
            <button name="submit_connector" id="submit_connector" value="submit_connector" type="submit" class="btn btn-success">
                <i class="fa fa-check"><!----></i>
                <?php echo T_('Valider'); ?>
            </button>
        </div>
    </form>
</div>