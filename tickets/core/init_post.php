<?php
################################################################################
# @Name : init_post.php
# @Description : init and secure all app var
# @Call : 
# @Parameters : 
# @Author : Flox
# @Create : 08/11/2019
# @Update : 06/05/2024
# @Version : 3.2.50 p1
################################################################################

//POST var definition
$all_post_var=array(
	'information_message',
	'company_message',
	'azure_ad_sso_hide_login',
	'imap_auto_create_user',
	'id',
	'allday',
	'end',
	'start',
	'mail_cc_tech',
	'date_stock',
	'location',
	'description',
	'sn_internal',
	'user_admin_ip',
	'asset_model',
	'netbios',
	'azure_ad_name_2',
	'azure_ad_name',
	'azure_ad_group_filter_2',
	'azure_ad_group_filter',
	'azure_ad_client_secret_2',
	'azure_ad_tenant_id_2',
	'azure_ad_client_id_2',
	'azure_ad_tenant_number',
	'server_timezone',
	'server_proxy_url',
	'mail_color_title',
	'mail_color_bg',
	'mail_color_text',
	'ocs',
	'ocs_server_url',
	'azure_ad_sso',
	'proxy_url',
	'planning_ics',
	'ticket_recurrent_date_frequency',
	'ticket_recurrent_date_start',
	'ticket_recurrent_create',
	'api',
	'api_key',
	'api_client_ip',
	'add_observer',
	'comment',
	'date',
	'selectrow',
	'ticket',
	'technician',
	'technician_group',
	'title',
	'resolution',
	'submit',
	'userid',
	'company',
	'user',
	'category',
	'subcat',
	'asset',
	'place',
	'service',
	'u_service',
	'sender_service',
	'agency',
	'date_create',
	'date_hope',
	'date_res',
	'date_start',
	'date_end',
	'state',
	'priority',
	'criticality',
	'type',
	'u_group',
	't_group',
	'Modifier',
	'Ajouter',
	'cat',
	'model',
	'ip',
	'wifi',
	'manufacturer',
	'name',
	'confirm',
	'number',
	'observer',
	'observer1',
	'observer2',
	'observer3',
	'billable',
	'keywords',
	'date_modif',
	'mail',
	'upload',
	'type_answer',
	'modify',
	'quit',
	'quit',
	'time_hope',
	'cancel',
	'private',
	'asset_id',
	'u_agency',
	'addcalendar',
	'addevent',
	'user_validation',
	'user_validation_date',
	'action',
	'edituser',
	'user_validation_delay',
	'user_validation_perimeter',
	'user_validation_delay_parameters',
	'planification',
	'add_calendar_start',
	'add_calendar_end',
	'time',
	'reminder',
	'add_reminder',
	'addsubcat',
	'modifysubcat',
	'adduser',
	'subcatname',
	'target_ticket',
	'duplicate',
	'add',
	'firstname',
	'lastname',
	'phone',
	'mobile',
	'usermail',
	'modifyuser',
	'manual_address',
	'withattachment',
	'manual_address_cci',
	'assetkeywords',
	'checkbox',
	'sn_indent',
	'virtualization',
	'netbios_lan_new',
	'ip_lan_new',
	'mac_lan_new',
	'netbios_wifi_new',
	'ip_wifi_new',
	'mac_wifi_new',
	'sn_manufacturer',
	'socket',
	'date_install',
	'date_end_warranty',
	'date_standbye',
	'date_recycle',
	'MAX_FILE_SIZE',
	'file',
	'filter',
	'captcha',
	'login',
	'userkeywords',
	'rightkeywords',
	'procedurekeywords',
	'modifypwd',
	'procedure_file',
	'add_ticket_number',
	'add_task_number',
	'answer',
	'question_number',
	'question_id',
	'profile',
	'chgpwd',
	'viewname',
	'limit_ticket_number',
	'limit_ticket_days',
	'limit_ticket_date_start',
	'fax',
	'function',
	'address1',
	'address2',
	'zip',
	'custom1',
	'custom2',
	'color',
	'file1',
	'warranty',
	'mail_smtp',
	'mail_username',
	'ldap',
	'ldap_auth',
	'ldap_sso',
	'ldap_service',
	'ldap_service_url',
	'ldap_agency',
	'ldap_agency_url',
	'from_agency',
	'dest_agency',
	'ldap_server',
	'ldap_server_url',
	'ldap_domain',
	'ldap_url',
	'ldap_user',
	'ldap_disable_user',
	'imap',
	'imap_server',
	'imap_user',
	'imap_reply',
	'imap_mailbox_service',
	'mailbox_service',
	'imap_from_adr_service',
	'imap_blacklist',
	'imap_post_treatment_folder',
	'asset_ip',
	'asset_warranty',
	'asset_vnc_link',
	'asset_import',
	'procedure',
	'survey',
	'survey_new_question_number',
	'survey_new_question_text',
	'logo',
	'login_background',
	'server_url',
	'restrict_ip',
	'log',
	'maxline',
	'time_display_msg',
	'auto_refresh',
	'login_message',
	'ticket_increment_number',
	'meta_state',
	'ticket_places',
	'ticket_type',
	'ticket_observer',
	'ticket_autoclose',
	'ticket_autoclose_delay',
	'ticket_autoclose_state',
	'ticket_cat_auto_attribute',
	'notify',
	'user_advanced',
	'user_register',
	'user_limit_ticket',
	'company_limit_ticket',
	'company_limit_hour',
	'user_company_view',
	'user_agency',
	'user_limit_service',
	'user_forgot_pwd',
	'user_disable_attempt',
	'user_disable_attempt_number',
	'user_password_policy',
	'user_password_policy_min_lenght',
	'user_password_policy_special_char',
	'user_password_policy_min_maj',
	'user_password_policy_expiration',
	'mail_auto',
	'mail_auto_user_newticket',
	'mail_auto_user_modify',
	'mail_auto_tech_attribution',
	'mail_auto_type',
	'mail_auto_tech_modify',
	'mail_newticket',
	'mail_newticket_address',
	'mail_txt',
	'mail_txt_end',
	'mail_cc',
	'mail_from_name',
	'mail_from_adr',
	'mail_cci',
	'mail_link',
	'mail_link_redirect_url',
	'mail_order',
	'debug',
	'plugin',
	'step',
	'install',
	'addiface',
	'edit',
	'editiface',
	'server',
	'dbname',
	'port',
	'warranty_type',
	'warranty_time',
	'virtual',
	'department',
	'tech',
	'month',
	'year',
	'maintenance',
	'receiver',
	'usercopy',
	'usercopy2',
	'usercopy3',
	'usercopy4',
	'usercopy5',
	'usercopy6',
	'usercopy_cci',
	'usercopy2_cci',
	'usercopy3_cci',
	'usercopy4_cci',
	'usercopy5_cci',
	'usercopy6_cci',
	'update_channel',
	'attachment',
	'language',
	'skin',
	'dashboard_ticket_order',
	'mail_port',
	'mail_ssl_check',
	'mail_secure',
	'mail_auth',
	'mail_smtp_class',
	'ldap_type',
	'ldap_port',
	'ldap_login_field',
	'imap_port',
	'imap_ssl_check',
	'imap_inbox',
	'mailbox_service_id',
	'imap_post_treatment',
	'survey_ticket_state',
	'survey_new_question_type',
	'survey_auto_close_ticket',
	'server_language',
	'login_state',
	'default_skin',
	'order',
	'ticket_default_state',
	'user_validation_category',
	'user_validation_subcat',
	'mail_template',
	'network',
	'role',
	'ModalCategory',
	'template',
	'availability_condition_type',
	'availability_condition_value',
	'depcategory',
	'depsubcat',
	'serials',
	'reason',
	'survey_mail_text',
	'addview',
	'profil',
	'city',
	'default_ticket_state',
	'action',
	'token',
	'user_id',
	'CategoryId',
	'UserId',
	'test_smtp',
	'submit_general',
	'submit_connector',
	'submit_function',
	'submit_plugin',
	'uninstall_plugin',
	'test_imap',
	'test_ldap',
	'plugin_connector',
	'timeout',
	'login_message_info',
	'login_message_warning',
	'login_message_alert',
	'planning',
	'project',
	'planning_color',
	'connexion',
	'listkeywords',
	'logkeywords',
	'mail_oauth_client_id',
	'mail_oauth_client_secret',
	'mail_oauth_refresh_token',
	'mail_auth_type',
	'legal_status',
	'ticket_time_response_element',
	'time_response_element',
	'asset_type',
	'ticket_open_message',
	'ticket_open_message_text',
	'mail_oauth_tenant_id',
	'imap_auth_type',
	'imap_oauth_client_id',
	'imap_oauth_tenant_id',
	'imap_oauth_client_secret',
	'imap_oauth_refresh_token',
	'mailbox_service_auth_type',
	'mailbox_service_oauth_client_id',
	'mailbox_service_oauth_tenant_id',
	'mailbox_service_oauth_client_secret',
	'mailbox_service_oauth_refresh_token',
	'limit_hour_date_start',
	'limit_hour_number',
	'limit_hour_days',
	'mail_reply',
	'TVA',
	'SIRET',
	'country',
	'address',
	'add_btn1',
	'add_btn2',
	'imap_date_create',
	'azure_ad',
	'azure_ad_client_id',
	'azure_ad_tenant_id',
	'azure_ad_client_secret',
	'test_azure_ad',
	'azure_ad_login_field',
	'azure_ad_disable_user',
	'telemetry',
	'install_store_plugin',
	'install_licenced_plugin',
);

//action on all post var
foreach($all_post_var as $post_var) {
	//init var
	if(!isset($_POST[$post_var])){$_POST[$post_var]='';}
	//secure var
	$_POST[$post_var]=htmlspecialchars($_POST[$post_var], ENT_QUOTES, 'UTF-8');
}


//init secure for wysiwyg
if(!isset($_POST['text'])){$_POST['text']='';}
if(!isset($_POST['text2'])){$_POST['text2']='';}
if(!isset($_POST['text3'])){$_POST['text3']='';}

//secure for wysiwyg
if($_POST['text'] || $_POST['text2'] || $_POST['text3'] || $_POST['mail_txt'] || $_POST['mail_txt_end'])
{
	require_once(__DIR__.'/../vendor/autoload.php');
	$config = HTMLPurifier_Config::createDefault();
	if(preg_match('/Linux/',PHP_OS)) //enable cache on Linux
	{
		//create cache dir
		if(!is_dir(__DIR__.'/../upload/html_purifier'))
		{
			if(is_writeable(__DIR__.'/../upload/index.htm')) {
				mkdir(__DIR__.'/../upload/html_purifier');
			} else {
				LogIt('error','ERROR 41 : unable to create upload/html_purifier folder',0);
			}
		}
		if(is_dir(__DIR__.'/../upload/html_purifier'))
		{
			$config->set('Cache.SerializerPath', __DIR__.'/../upload/html_purifier');
		}else {
			LogIt('error','ERROR 42 : unable to find upload/html_purifier',0);
		}
	} else { //disable cache on Windows
		$config->set('Cache.DefinitionImpl', null);
	}
	$config->set('URI.AllowedSchemes',  array('data' => true, 'http' => true, 'https' => true));
	$purifier = new HTMLPurifier($config);

    $_POST['text'] = $purifier->purify($_POST['text']);
    $_POST['text2'] = $purifier->purify($_POST['text2']);
    $_POST['text3'] = $purifier->purify($_POST['text3']);
    $_POST['mail_txt'] = $purifier->purify($_POST['mail_txt']);
    $_POST['mail_txt_end'] = $purifier->purify($_POST['mail_txt_end']);
}

//whitelist mail template values
if($_POST['mail_template'])
{
	$template_whitelist = array_diff(scandir(__DIR__.'/../template/mail'), array('..', '.', 'readme.txt'));
	if(!in_array($_POST['mail_template'],$template_whitelist)) {$_POST['mail_template']='default.htm';} 
}

//check numeric var 
$all_numeric_var=array(
	'ticket_increment_number',
	'id',
	'sender_service',
	'asset',
	'userid',
	'ticket',
	'priority',
	'criticality',
	'type',
	'u_group',
);	
foreach($all_numeric_var as $numeric_var) {
	if($_POST[$numeric_var] && !is_numeric($_POST[$numeric_var]) && $_POST[$numeric_var]!='%') {  echo 'ERROR : incorrect value on $_POST['.$numeric_var.']'; exit;}
}
?>