<?php
################################################################################
# @Name : general.php
# @Description : admin general parameters
# @Call : /core/parameters.php
# @Parameters : 
# @Author : Flox
# @Create : 22/09/2020
# @Update : 02/05/2024
# @Version : 3.2.50
################################################################################

//initialize variables 
if(!isset($_FILES['logo']['name'])) $_FILES['logo']['name'] = '';
if(!isset($_FILES['login_background']['name'])) $_FILES['login_background']['name'] = '';

//default variable
if(empty($_GET['subtab'])) {$_GET['subtab']='company';}

//check last slash in server url
$_POST['server_url']=str_replace('index.php','',$_POST['server_url']);
if(substr($_POST['server_url'], -1)=='/') {$_POST['server_url']=substr_replace($_POST['server_url'] ,'',-1);}

//check mail addresses
if(!filter_var($_POST['mail_from_adr'], FILTER_VALIDATE_EMAIL)) {$_POST['mail_from_adr']='';}
$mail_cc=explode(';',$_POST['mail_cc']);
foreach ($mail_cc as &$mail_adr) { if(!filter_var($mail_adr, FILTER_VALIDATE_EMAIL)) {$_POST['mail_cc']='';}}
$mail_newticket_address=explode(';',$_POST['mail_newticket_address']);
foreach ($mail_newticket_address as &$mail_adr) { if(!filter_var($mail_adr, FILTER_VALIDATE_EMAIL)) {$_POST['mail_newticket_address']='';}}

//default value
if($_POST['maxline']==0) {$_POST['maxline']=14;}

//delete logo file
if($_GET['action']=="deletelogo" && $rright['admin'])
{
    //remove file
    unlink('upload/logo/'.$rparameters['logo']);

    //update database
	$qry=$db->prepare("UPDATE `tparameters` SET `logo`=''");
	$qry->execute();
	//reload
	$www = "./index.php?page=admin&subpage=parameters";
	echo '<script language="Javascript">
	<!--
	document.location.replace("'.$www.'");
	// -->
	</script>'; 
}

//delete logo file
if($_GET['action']=="delete_login_background" && $rright['admin'])
{
    //remove file
    unlink('upload/login_background/'.$rparameters['login_background']);
    //update database
	$qry=$db->prepare("UPDATE `tparameters` SET `login_background`=''");
	$qry->execute();
	//reload
	$www = "./index.php?page=admin&subpage=parameters&tab=general&subtab=display";
	echo '<script language="Javascript">
	<!--
	document.location.replace("'.$www.'");
	// -->
	</script>'; 
}

//delete user validation cat or subcat
if($_GET['action']=="delete_user_validation_exclusion" && $_GET['id'] && $rright['admin'])
{
    $qry=$db->prepare("DELETE FROM tparameters_user_validation_exclusion WHERE id=:id");
	$qry->execute(array('id' => $_GET['id']));
	//reload
	$www = "./index.php?page=admin&subpage=parameters";
	echo '<script language="Javascript">
	<!--
	document.location.replace("'.$www.'");
	// -->
	</script>'; 
}

if($_POST['submit_general'] && $rright['admin'])
{
    //delete current login messages
    if(!$_POST['login_message'])
    {
        $qry=$db->prepare("UPDATE `tparameters` SET `login_message_info`='',`login_message_alert`=''");
        $qry->execute();
    }

	//modify ticket increment number if specify
	if($_POST['ticket_increment_number'] && is_numeric($_POST['ticket_increment_number']))
	{
		$db->exec("ALTER TABLE `tincidents` auto_increment=$_POST[ticket_increment_number]");
	}
	//upload logo file
	if($_FILES['logo']['name'])
	{
        //create dir
        if(!is_dir('upload/logo') && is_writeable('upload/index.htm')) {mkdir('upload/logo');}

        //check extension
        $file_name=$_FILES['logo']['name'];
        $file_extension=new SplFileInfo($file_name);
        $file_extension=$file_extension->getExtension();
        $whitelist=array('png','jpg','jpeg','gif','bmp','tiff','webp','psd','raw','heif','indd','svg','ai','eps','webp');
        if(in_array(strtolower($file_extension),$whitelist,true)) {
            //generate file name
            $file_rename_logo = sha1(rand()).'.'.strtolower($file_extension);
            $destination_folder = "./upload/logo/";
            if(move_uploaded_file($_FILES['logo']['tmp_name'], $destination_folder.$file_rename_logo)) 
            {
                //content check security
                $file_content = file_get_contents($destination_folder.$file_rename_logo, true);
                if(preg_match('{\<?php}',$file_content) || preg_match('/system\(/',$file_content)) 
                {
                    echo DisplayMessage('error',T_("Fichier interdit"));
                    unlink($destination_folder.$file_rename_logo); //remove file
                    if($rparameters['log']) {logit('security','File upload blocked on logo',$_SESSION['user_id']);}
                }
            } else {
                echo DisplayMessage('error',T_("Erreur de transfert vérifier le chemin").' '.$destination_folder);
            }
        } else {
            echo DisplayMessage('error',T_('Blocage de sécurité').' :</strong> '.T_('Fichier interdit'));
            if($rparameters['log']) {logit('security','File upload blocked on logo',$_SESSION['user_id']);}
            $file_rename_logo='';
        }
	} else {$file_rename_logo=$rparameters['logo'];}
    
    //upload login_background file
	if($_FILES['login_background']['name'])
	{
        //create dir
        if(!is_dir('upload/login_background') && is_writeable('upload/index.htm')) {mkdir('upload/login_background');}

        //check extension
	    $file_name=$_FILES['login_background']['name'];
        $file_extension=new SplFileInfo($file_name);
        $file_extension=$file_extension->getExtension();
        $whitelist=array('png','jpg','jpeg','gif','bmp','tiff','webp','psd','raw','heif','indd','svg','ai','eps','webp');
        if(in_array(strtolower($file_extension),$whitelist,true)) {
            //generate file name
            $file_rename_login_background = sha1(rand()).'.'.strtolower($file_extension);
            $destination_folder = "./upload/login_background/";
    		if(move_uploaded_file($_FILES['login_background']['tmp_name'], $destination_folder.$file_rename_login_background)) 
    		{
                //content check security
                $file_content = file_get_contents($destination_folder.$file_rename_login_background, true);
                if(preg_match('{\<?php}',$file_content) || preg_match('/system\(/',$file_content)) 
                {
                    echo DisplayMessage('error',T_("Fichier interdit"));
                    unlink($destination_folder.$file_rename_login_background); //remove file
                    if($rparameters['log']) {logit('security','File upload blocked on login_background',$_SESSION['user_id']);}
                }
    		} else {
                echo DisplayMessage('error',T_("Erreur de transfert vérifier le chemin").' '.$destination_folder);
    		}
        } else {
            echo DisplayMessage('error',T_('Blocage de sécurité').' :</strong> '.T_('Fichier interdit'));
            if($rparameters['log']) {logit('security','File upload blocked on login_background',$_SESSION['user_id']);}
            $file_rename_login_background='';
        }
	} else {$file_rename_login_background=$rparameters['login_background'];}

	//init var
	$_POST['login_message_info']=$_POST['text'];
	$_POST['login_message_alert']=$_POST['text2'];
	$_POST['login_message_warning']=$_POST['text3'];

    //modify default theme
    if($_POST['default_skin']!=$rparameters['default_skin'])
    {
        $qry=$db->prepare("UPDATE `tusers` SET `skin`=:skin");
        $qry->execute(array('skin' => $_POST['default_skin']));
    } 

    //add user_validation_cat
    if($_POST['user_validation_category'])
    {
        $qry=$db->prepare("INSERT INTO `tparameters_user_validation_exclusion` (`category`) VALUES (:category)");
        $qry->execute(array('category' => $_POST['user_validation_category']));
    }
    if($_POST['user_validation_subcat'])
    {
        $qry=$db->prepare("INSERT INTO `tparameters_user_validation_exclusion` (`subcat`) VALUES (:subcat)");
        $qry->execute(array('subcat' => $_POST['user_validation_subcat']));
    }

    //update all user language on switch value
    if($rparameters['server_language']!=$_POST['server_language'])
    {
        $qry=$db->prepare("UPDATE `tusers` SET `language`=:language");
        $qry->execute(array('language' => $_POST['server_language']));

        $qry=$db->prepare(" ALTER TABLE `tusers` CHANGE `language` `language` VARCHAR(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT :language");
        $qry->execute(array('language' => $_POST['server_language']));
    }

    //check allowed char in IPv4 & IPv6 adresses
    if($_POST['restrict_ip'])
    {
        $blacklisted=0;
        $whitelist = array(',','.',':',';','0','1','2','3','4','5','6','7','8','9','2','a','b','c','d','e','f'); 
        foreach(str_split(strtolower($_POST['restrict_ip'])) as $char){if(!in_array($char,$whitelist)) {$blacklisted=1;}}
        if($blacklisted) {$_POST['restrict_ip']='';}
    }

	//update general tab
	$qry=$db->prepare("
		UPDATE `tparameters` SET 
		`company`=:company,
		`server_url`=:server_url,
		`restrict_ip`=:restrict_ip,
		`server_proxy_url`=:server_proxy_url,
		`server_timezone`=:server_timezone,
		`server_language`=:server_language,
		`log`=:log,
		`maxline`=:maxline,
		`timeout`=:timeout,
		`mail_txt`=:mail_txt,
		`mail_txt_end`=:mail_txt_end,
		`mail_cc`=:mail_cc,
		`mail_cc_tech`=:mail_cc_tech,
		`mail_cci`=:mail_cci,
		`mail_from_name`=:mail_from_name,
		`mail_from_adr`=:mail_from_adr,
		`mail_reply`=:mail_reply,
		`mail_color_title`=:mail_color_title,
		`mail_color_bg`=:mail_color_bg,
		`mail_color_text`=:mail_color_text,
		`mail_link`=:mail_link,
		`mail_link_redirect_url`=:mail_link_redirect_url,
		`mail_order`=:mail_order,
		`logo`=:logo,
		`login_background`=:login_background,
		`time_display_msg`=:time_display_msg,
		`auto_refresh`=:auto_refresh,
		`login_state`=:login_state,
		`default_skin`=:default_skin,
		`login_message`=:login_message,
		`login_message_info`=:login_message_info,
		`login_message_warning`=:login_message_warning,
		`login_message_alert`=:login_message_alert,
		`company_message`=:company_message,
		`notify`=:notify,
		`user_advanced`=:user_advanced,
		`user_register`=:user_register,
		`user_agency`=:user_agency,
		`user_limit_service`=:user_limit_service,
		`user_disable_attempt`=:user_disable_attempt,
		`user_disable_attempt_number`=:user_disable_attempt_number,
		`user_password_policy`=:user_password_policy,
		`user_password_policy_min_lenght`=:user_password_policy_min_lenght,
		`user_password_policy_special_char`=:user_password_policy_special_char,
		`user_password_policy_min_maj`=:user_password_policy_min_maj,
		`user_password_policy_expiration`=:user_password_policy_expiration,
		`user_forgot_pwd`=:user_forgot_pwd,
		`user_admin_ip`=:user_admin_ip,
		`company_limit_ticket`=:company_limit_ticket,
		`company_limit_hour`=:company_limit_hour,
		`user_limit_ticket`=:user_limit_ticket,
		`user_company_view`=:user_company_view,
		`mail_auto`=:mail_auto,
		`mail_auto_tech_modify`=:mail_auto_tech_modify,
		`mail_auto_tech_attribution`=:mail_auto_tech_attribution,
		`mail_auto_type`=:mail_auto_type,
		`mail_auto_user_modify`=:mail_auto_user_modify,
		`mail_auto_user_newticket`=:mail_auto_user_newticket,
		`mail_newticket`=:mail_newticket,
		`mail_newticket_address`=:mail_newticket_address,
		`mail_template`=:mail_template,
		`telemetry`=:telemetry,
		`debug`=:debug,
		`order`=:order,
		`ticket_places`=:ticket_places,
		`ticket_type`=:ticket_type,
		`ticket_observer`=:ticket_observer,
		`ticket_autoclose`=:ticket_autoclose,
		`ticket_autoclose_delay`=:ticket_autoclose_delay,
		`ticket_autoclose_state`=:ticket_autoclose_state,
		`user_validation`=:user_validation,
		`user_validation_delay`=:user_validation_delay,
		`user_validation_perimeter`=:user_validation_perimeter,
		`ticket_cat_auto_attribute`=:ticket_cat_auto_attribute,
		`ticket_time_response_element`=:ticket_time_response_element,
		`ticket_open_message`=:ticket_open_message,
		`ticket_open_message_text`=:ticket_open_message_text,
		`ticket_recurrent_create`=:ticket_recurrent_create,
		`ticket_default_state`=:ticket_default_state,
		`meta_state`=:meta_state
		WHERE
		`id`=:id
	");
	$qry->execute(array(
		'company' => $_POST['company'],
		'server_url' => $_POST['server_url'],
		'restrict_ip' => $_POST['restrict_ip'],
		'server_proxy_url' => $_POST['server_proxy_url'],
		'server_timezone' => $_POST['server_timezone'],
		'server_language' => $_POST['server_language'],
		'log' => $_POST['log'],
		'maxline' => $_POST['maxline'],
		'timeout' => $_POST['timeout'],
		'mail_txt' => $_POST['mail_txt'],
		'mail_txt_end' => $_POST['mail_txt_end'],
		'mail_cc' => $_POST['mail_cc'],
		'mail_cc_tech' => $_POST['mail_cc_tech'],
		'mail_cci' => $_POST['mail_cci'],
		'mail_from_name' => $_POST['mail_from_name'],
		'mail_from_adr' => $_POST['mail_from_adr'],
		'mail_reply' => $_POST['mail_reply'],
		'mail_color_title' => $_POST['mail_color_title'],
		'mail_color_bg' => $_POST['mail_color_bg'],
		'mail_color_text' => $_POST['mail_color_text'],
		'mail_link' => $_POST['mail_link'],
		'mail_link_redirect_url' => $_POST['mail_link_redirect_url'],
		'mail_order' => $_POST['mail_order'],
		'logo' => $file_rename_logo,
		'login_background' => $file_rename_login_background,
		'time_display_msg' => $_POST['time_display_msg'],
		'auto_refresh' => $_POST['auto_refresh'],
		'login_state' => $_POST['login_state'],
		'default_skin' => $_POST['default_skin'],
		'login_message' => $_POST['login_message'],
		'login_message_info' => $_POST['login_message_info'],
		'login_message_warning' => $_POST['login_message_warning'],
		'login_message_alert' => $_POST['login_message_alert'],
		'company_message' => $_POST['company_message'],
		'notify' => $_POST['notify'],
		'user_advanced' => $_POST['user_advanced'],
		'user_register' => $_POST['user_register'],
		'user_agency' => $_POST['user_agency'],
		'user_limit_service' => $_POST['user_limit_service'],
		'user_disable_attempt' => $_POST['user_disable_attempt'],
		'user_disable_attempt_number' => $_POST['user_disable_attempt_number'],
		'user_password_policy' => $_POST['user_password_policy'],
		'user_password_policy_min_lenght' => $_POST['user_password_policy_min_lenght'],
		'user_password_policy_special_char' => $_POST['user_password_policy_special_char'],
		'user_password_policy_min_maj' => $_POST['user_password_policy_min_maj'],
		'user_password_policy_expiration' => $_POST['user_password_policy_expiration'],
		'user_forgot_pwd' => $_POST['user_forgot_pwd'],
		'user_admin_ip' => $_POST['user_admin_ip'],
		'company_limit_ticket' => $_POST['company_limit_ticket'],
		'company_limit_hour' => $_POST['company_limit_hour'],
		'user_limit_ticket' => $_POST['user_limit_ticket'],
		'user_company_view' => $_POST['user_company_view'],
		'mail_auto' => $_POST['mail_auto'],
		'mail_auto_tech_modify' => $_POST['mail_auto_tech_modify'],
		'mail_auto_tech_attribution' => $_POST['mail_auto_tech_attribution'],
		'mail_auto_type' => $_POST['mail_auto_type'],
		'mail_auto_user_modify' => $_POST['mail_auto_user_modify'],
		'mail_auto_user_newticket' => $_POST['mail_auto_user_newticket'],
		'mail_newticket' => $_POST['mail_newticket'],
		'mail_newticket_address' => $_POST['mail_newticket_address'],
		'mail_template' => $_POST['mail_template'],
		'telemetry' => $_POST['telemetry'],
		'debug' => $_POST['debug'],
		'order' => $_POST['order'],
		'ticket_places' => $_POST['ticket_places'],
		'ticket_type' => $_POST['ticket_type'],
		'ticket_observer' => $_POST['ticket_observer'],
		'ticket_autoclose' => $_POST['ticket_autoclose'],
		'ticket_autoclose_delay' => $_POST['ticket_autoclose_delay'],
		'ticket_autoclose_state' => $_POST['ticket_autoclose_state'],
		'user_validation' => $_POST['user_validation'],
		'user_validation_delay' => $_POST['user_validation_delay'],
		'user_validation_perimeter' => $_POST['user_validation_perimeter'],
		'ticket_cat_auto_attribute' => $_POST['ticket_cat_auto_attribute'],
		'ticket_time_response_element' => $_POST['ticket_time_response_element'],
		'ticket_open_message' => $_POST['ticket_open_message'],
		'ticket_open_message_text' => $_POST['ticket_open_message_text'],
		'ticket_recurrent_create' => $_POST['ticket_recurrent_create'],
		'ticket_default_state' => $_POST['ticket_default_state'],
		'meta_state' => $_POST['meta_state'],
		'id' => '1'
		));
	//redirect
	$www = "./index.php?page=admin&subpage=parameters&tab=general&subtab=$_GET[subtab]";
		echo '<script language="Javascript">
		<!--
		document.location.replace("'.$www.'");
		// -->
		</script>'; 
}
?>
<!-- /////////////////////////////////////////////////////////////// general tab /////////////////////////////////////////////////////////////// -->
<input type="hidden" name="tab" id="tab" value="general" />
<div id="general"  class="tab-pane <?php if($_GET['tab']=='general' || $_GET['tab']=='') echo 'active'; ?>">
    <form enctype="multipart/form-data" name="general_form" id="general_form" method="post" action="" onsubmit="loadVal();">
        <div class="table-responsive">
            <div class="tab-content " style="background-color:#FFF;">
                <div class="card bcard bgc-transparent shadow-none">
                    <div class="card-body tabs-left p-0">
                        <ul class="nav nav-tabs align-self-start" role="tablist">
                            <li class="nav-item brc-success shadow-sm">
                                <a class="nav-link text-left  py-3 <?php if($_GET['subtab']=='company') {echo 'active';}?>" href="index.php?page=admin&subpage=parameters&tab=general&subtab=company">
                                    <i class="fa fa-building text-success pr-1"><!----></i><?php echo T_('Société'); ?> 
                                </a>
                            </li>
                            <li class="nav-item brc-success shadow-sm">
                                <a class="nav-link text-nowrap py-3 <?php if($_GET['subtab']=='server') {echo 'active';}?>" href="index.php?page=admin&subpage=parameters&tab=general&subtab=server">
                                    <i class="fa fa-server text-success pr-1"><!----></i> <?php echo T_('Serveur'); ?>
                                </a>
                            </li>
                            <li class="nav-item brc-success shadow-sm">
                                <a class="nav-link text-nowrap py-3 <?php if($_GET['subtab']=='display') {echo 'active';}?>" href="index.php?page=admin&subpage=parameters&tab=general&subtab=display">
                                    <i class="fa fa-desktop text-success pr-1"><!----></i><?php echo T_('Affichage'); ?> 
                                </a>
                            </li>
                            <li class="nav-item brc-success shadow-sm">
                                <a class="nav-link text-nowrap py-3 <?php if($_GET['subtab']=='ticket') {echo 'active';}?>" href="index.php?page=admin&subpage=parameters&tab=general&subtab=ticket">
                                    <i class="fa fa-ticket text-success pr-1"><!----></i><?php echo T_('Tickets'); ?> 
                                </a>
                            </li>
                            <li class="nav-item brc-success shadow-sm">
                                <a class="nav-link text-nowrap py-3 <?php if($_GET['subtab']=='sound') {echo 'active';}?>" href="index.php?page=admin&subpage=parameters&tab=general&subtab=sound">
                                    <i class="fa fa-volume-up text-success pr-1"><!----></i><?php echo T_('Son'); ?> 
                                </a>
                            </li>
                            <li class="nav-item brc-success shadow-sm">
                                <a class="nav-link text-nowrap py-3 <?php if($_GET['subtab']=='user') {echo 'active';}?>" href="index.php?page=admin&subpage=parameters&tab=general&subtab=user">
                                    <i class="fa fa-user text-success pr-1"><!----></i><?php echo T_('Utilisateurs'); ?> 
                                </a>
                            </li>
                            <li class="nav-item brc-success shadow-sm">
                                <a class="nav-link text-nowrap py-3 <?php if($_GET['subtab']=='mail') {echo 'active';}?>" href="index.php?page=admin&subpage=parameters&tab=general&subtab=mail">
                                    <i class="fa fa-envelope text-success pr-1"><!----></i><?php echo T_('E-mails'); ?> 
                                </a>
                            </li>
                            <li class="nav-item brc-success shadow-sm">
                                <a class="nav-link text-nowrap py-3 <?php if($_GET['subtab']=='debug') {echo 'active';}?>" href="index.php?page=admin&subpage=parameters&tab=general&subtab=debug">
                                    <i class="fa fa-bug text-success pr-1"><!----></i><?php echo T_('Debug'); ?> 
                                </a>
                            </li>
                        </ul>
                        <!-- subtab content -->
                        <div class="tab-content p-35 border-1 brc-grey-l1 shadow-sm bgc-white">
                            <!-- subtab company -->
                            <div class="tab-pane fade text-95 <?php if($_GET['subtab']=='company') {echo 'show active';}?> ">
                                <div class="text-primary-d3 text-110 mb-2">
                                    <label class="lbl" for="company" ><?php echo T_("Nom de l'entreprise"); ?> : </label>
                                    <input style="width:auto" class="form-control d-inline-block" type="text" name="company" id="company" value="<?php echo $rparameters['company']; ?>" placeholder="<?php echo T_('Société'); ?>" autocomplete="on">
                                    <div class="pt-1"></div>
                                    <label class="lbl" for="logo"><?php echo T_('Logo'); ?> : </label>
                                    <?php
                                        if($rparameters['logo'])
                                        {
                                            echo '
                                                <img height="40" src="./upload/logo/'.$rparameters['logo'].'" />	
                                                <a title="'.T_('Supprimer ce logo').'" href="./index.php?page=admin&subpage=parameters&tab=general&action=deletelogo">
                                                    <i class="fa fa-trash text-danger "><!----></i>
                                                </a>
                                            ';
                                        } else {
                                            echo '<input type="file" id="logo" name="logo" />';
                                        }
                                    ?>
                                </div>
                            </div>
                            <!-- subtab server -->
                            <div class="tab-pane fade text-95 <?php if($_GET['subtab']=='server') {echo 'show active';}?> ">
                                <div class="text-primary-d3 text-110 mb-2">
                                    <label class="lbl" for="server_url"><?php echo T_("URL d'accès au serveur"); ?> : </label>
                                    <input style="width:400px" class="form-control d-inline-block" type="text" name="server_url" id="server_url" value="<?php echo $rparameters['server_url']; ?>">
                                    <i data-toggle="tooltip" id="tooltip1" data-placement="auto" data-original-title="<?php echo T_("URL de l'accès au serveur pour vos utilisateurs, utilisé dans l'envoi de mail (exemple: https://gestsup en LAN ou https://support.masociete.com sur Internet)"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    <div class="pt-1"></div>

                                    <label class="lbl" for="restrict_ip"><?php echo T_('Restriction IP'); ?> : </label>
                                    <input style="width:auto" class="form-control d-inline-block" type="text" name="restrict_ip" id="restrict_ip" value="<?php echo $rparameters['restrict_ip']; ?>">
                                    <i data-toggle="tooltip" id="tooltip2" data-placement="auto" data-original-title="<?php echo T_("Permet de limiter l'accès des clients au serveur à une IP ou une plage d'IP. &#10;Si le champ n'est pas renseigné aucune restriction ne sera active. &#10;Vous pouvez ajouter plusieurs IP avec le séparateur virgule. &#10;Exemples : 192.168.0.1 ou 192.168.0 ou 192.168, 2001:0db8:85a3)"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    <div class="pt-1"></div>
                                    
                                    <label class="lbl" for="server_proxy_url"><?php echo T_('URL Proxy'); ?> : </label>
                                    <input style="width:auto" class="form-control d-inline-block" type="text" name="server_proxy_url" id="server_proxy_url" value="<?php echo $rparameters['server_proxy_url']; ?>">
                                    <i data-toggle="tooltip" id="tooltip64" data-placement="auto" data-original-title="<?php echo T_("Permet de specifier l'URL d'un serveur proxy, afin que l'application puisse télécharger depuis Internet (ex: http://10.0.0.1:3615)"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    <div class="pt-1"></div>

                                    <label class="lbl" for="server_timezone"><?php echo T_('Fuseau horaire'); ?> : </label>
                                    <select style="width:auto" class="form-control d-inline-block" name="server_timezone" id="server_timezone">
                                        <option <?php if($rparameters['server_timezone']=='') {echo 'selected';} ?> value="">Définit php.ini</option>
                                        <?php
                                            $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
                                            foreach ($tzlist as &$tz) {echo '<option '; if($rparameters['server_timezone']==$tz) {echo 'selected';} echo' value="'.$tz.'">'.$tz.'</option>';}
                                        ?>
                                    </select>
                                    <i data-toggle="tooltip" id="tooltip3" data-placement="auto" data-original-title="<?php echo T_('Force le fuseau horaire, par défaut la valeur définie et celle présente dans le fichier php.ini'); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    <div class="pt-1"></div>
                                    <label class="lbl" for="server_language"><?php echo T_('Langue'); ?> : </label>
                                    <select style="width:auto" class="form-control d-inline-block" name="server_language" id="server_language">
                                        <?php
                                            if($rparameters['server_language']=='fr_FR') {echo '<option selected value="fr_FR">'.T_('Français (France)').'</option>';} else {echo '<option value="fr_FR">'.T_('Français (France)').'</option>';}
                                            if($rparameters['server_language']=='en_US') {echo '<option selected value="en_US">'.T_('Anglais (États-Unis)').'</option>';} else {echo '<option value="en_US">'.T_('Anglais (États-Unis)').'</option>';}
                                            if($rparameters['server_language']=='de_DE') {echo '<option selected value="de_DE">'.T_('Allemand (Allemagne)').'</option>';} else {echo '<option value="de_DE">'.T_('Allemand (Allemagne)').'</option>';}
                                            if($rparameters['server_language']=='es_ES') {echo '<option selected value="es_ES">'.T_('Espagnol (Espagne)').'</option>';} else {echo '<option value="es_ES">'.T_('Espagnol (Espagne)').'</option>';}
                                            if($rparameters['server_language']=='it_IT') {echo '<option selected value="it_IT">'.T_('Italien (Italie)').'</option>';} else {echo '<option value="it_IT">'.T_('Italien (Italie)').'</option>';}
                                        ?>
                                    </select>
                                    <i data-toggle="tooltip" id="tooltip4" data-placement="auto" data-original-title="<?php echo T_("Défini la langue par défaut du serveur, lors d'une modification l'ensemble des utilisateurs sont mis à jour avec la langue sélectionnée. Lors de la création de nouveau utilisateur la nouvelle langue définie est également pris en compte"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    <div class="pt-1"></div>
                                    <input type="checkbox" <?php if($rparameters['log']) {echo "checked";}  ?> name="log" id="log" value="1">
                                    <label class="lbl mr-1" for="log"><?php echo T_('Gestion des logs'); ?></label>
                                    <i data-toggle="tooltip" id="tooltip5" data-placement="auto" data-original-title="<?php echo T_("Active l'enregistrement de données liées aux erreurs et à la sécurité dans le logiciel, affiche une nouvelle section dans Administration"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    <div class="pt-1"></div>
                                    <input type="checkbox" <?php if($rparameters['telemetry']) {echo "checked";}  ?> name="telemetry" id="telemetry" value="1">
                                    <label class="lbl mr-1" for="telemetry"><?php echo T_('Gestion de la télémétrie'); ?></label>
                                    <i data-toggle="tooltip" id="tooltip45" data-placement="auto" data-original-title="<?php echo T_("Active l'envoi de données vers gestsup.fr à des fins statistiques et d'amélioration de service, cf documentation"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    <div class="pt-1"></div>
                                </div>
                            </div>
                            <!-- subtab display -->
                            <div class="tab-pane fade text-95 <?php if($_GET['subtab']=='display') {echo 'show active';}?> ">
                                <div class="text-primary-d3 text-110 mb-2">
                                    <label class="lbl" for="timeout"><?php echo T_('Temps de déconnexion'); ?> : </label>
                                    <input style="width:auto" class="form-control d-inline-block" type="text" size="2" name="timeout" id="timeout" value="<?php echo $rparameters['timeout']; ?>"> m
                                    <i data-toggle="tooltip" id="tooltip6" data-placement="auto" data-original-title="<?php echo T_("Valeur en minutes, permettant de déconnecter la session au bout d'un temps d'inactivité. Doit être inférieur au session.gc_maxlifetime définit en secondes dans le php.ini"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    <div class="pt-1"></div>
                                    <label class="lbl" for="maxline"><?php echo T_('Nombre de lignes par page'); ?> : </label>
                                    <input style="width:auto" class="form-control d-inline-block" type="text" size="2" name="maxline" id="maxline" value="<?php echo $rparameters['maxline']; ?>">
                                    <i data-toggle="tooltip" id="tooltip7" data-placement="auto" data-original-title="<?php echo T_("Si cette valeur est trop grande cela peut ralentir l'application"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    <div class="pt-1"></div>
                                    <label class="lbl" for="time_display_msg"><?php echo T_("Temps d'affichage des messages d'actions"); ?> :</label>
                                    <input style="width:auto" class="form-control d-inline-block" name="time_display_msg" id="time_display_msg" type="text" value="<?php echo $rparameters['time_display_msg']; ?>" size="4" /> ms<br />
                                    <label class="lbl" for="auto_refresh"><?php echo T_('Actualisation automatique'); ?> :</label>
                                    <input style="width:auto" class="form-control d-inline-block" name="auto_refresh" id="auto_refresh" type="text" value="<?php echo $rparameters['auto_refresh']; ?>" size="3" /> s 
                                    <i data-toggle="tooltip" id="tooltip8" data-placement="auto" data-original-title="<?php echo T_("Si la valeur est à 0, alors l'actualisation automatique est désactivée. Attention, cette fonction peut faire clignoter l'écran selon les navigateurs"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    <div class="pt-1"></div>
                                    <label class="lbl" for="login_state"><?php echo T_("Page par défaut à la connexion"); ?> :</label>
                                    <select style="width:auto" class="form-control d-inline-block" id="login_state" name="login_state" >
                                        <?php
                                            $qry = $db->prepare("SELECT `id`,`name` FROM `tstates` ORDER BY `number`");
                                            $qry->execute();
                                            while ($row = $qry->fetch())
                                            {
                                                if($rparameters['login_state']==$row['id'])
                                                {echo '<option selected value="'.$row['id'].'">'.T_('Vos tickets').' '.$row['name'].'</option>';}
                                                else 
                                                {echo '<option value="'.$row['id'].'">'.T_('Vos tickets').' '.$row['name'].'</option>';}
                                            }
                                            $qry->closeCursor();
                                            
                                            //check if user have right to display side all menu before display option
                                            $qry = $db->prepare("SELECT `side_all` FROM `trights` WHERE profile='2'");
                                            $qry->execute();
                                            $row=$qry->fetch();
                                            $qry->closeCursor();
                                            if($row['side_all']==2)
                                            {
                                                echo '<option '; if($rparameters['login_state']=='all') {echo "selected ";} echo 'value="all">'.T_('Tous les tickets').'</option>';
                                                if($rparameters['meta_state']==1) 
                                                {
                                                    echo '<option '; if($rparameters['login_state']=='meta_all') {echo "selected ";} echo ' value="meta_all">'.T_('Tous les tickets à traiter').'</option>';
                                                }
                                            }
                                        ?>
                                    </select>
                                    <i data-toggle="tooltip" id="tooltip9" data-placement="auto" data-original-title="<?php echo T_("Détermine l'état par défaut affiché lors de la connexion de l'utilisateur, un utilisateur peut outrepasser ce paramètre en le modifiant dans ses paramètres personnels"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    <div class="pt-1"></div>
                                    <label class="lbl" for="default_skin"><?php echo T_("Thème par défaut"); ?> :</label>
                                    <select style="width:auto" class="form-control d-inline-block" id="default_skin" name="default_skin" >
                                        <?php
                                        echo '
                                        <option '; if($rparameters['default_skin']==''){echo "selected";} echo ' value="">'.T_('Bleu (Défaut)').'</option>
                                        <option '; if($rparameters['default_skin']=='skin-3'){echo "selected";} echo ' value="skin-3">'.T_('Gris').'</option>
                                        <option '; if($rparameters['default_skin']=='skin-1'){echo "selected";} echo ' value="skin-1">'.T_('Noir').'</option>
                                        <option '; if($rparameters['default_skin']=='skin-2'){echo "selected";} echo ' value="skin-2">'.T_('Violet').'</option>
                                        <option '; if($rparameters['default_skin']=='skin-5'){echo "selected";} echo ' value="skin-5">'.T_('Vert').'</option>
                                        <option '; if($rparameters['default_skin']=='skin-7'){echo "selected";} echo ' value="skin-7">'.T_('Vert et violet').'</option>
                                        <option '; if($rparameters['default_skin']=='skin-6'){echo "selected";} echo ' value="skin-6">'.T_('Orange').'</option>
                                        <option '; if($rparameters['default_skin']=='skin-8'){echo "selected";} echo ' value="skin-8">'.T_('Orange et violet').'</option>
                                        <option '; if($rparameters['default_skin']=='skin-4'){echo "selected";} echo ' value="skin-4">'.T_('Sombre').'</option>
                                        ';
                                        ?>
                                    </select>
                                    <i data-toggle="tooltip" id="tooltip10" data-placement="auto" data-original-title="<?php echo T_("Modifie le thème de tous les utilisateurs utilisateurs"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    <div class="pt-1"></div>
                                    <label class="lbl">
                                        <input id="login_message" name="login_message" type="checkbox" <?php if($rparameters['login_message']) {echo 'checked';}?>  value="1" />
                                    </label>
                                    <span class="lbl">&nbsp;<?php echo T_('Message sur la page de connexion'); ?></span>
                                    <i data-toggle="tooltip" id="tooltip11" data-placement="auto" data-original-title="<?php echo T_("Permet d'afficher un message d'information ou d'alerte ou les deux, sur la page de connexion de l'application afin d'informer les utilisateurs."); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    <div id="login_message_parameters">
                                    <?php
                                        echo '
                                        <div class="ml-4" >
                                            '.T_("Message d'information").' :
                                            <table class="mb-2" border="1" style="width:auto; border: 1px solid #D8D8D8;" >
                                                <tr >
                                                    <td>
                                                        <div id="editor" class="bootstrap-wysiwyg-editor px-3 py-2" style="min-height:100px; min-width:330px;">'.$rparameters['login_message_info'].'</div>
                                                    </td>
                                                </tr>
                                            </table>
                                            '.T_("Message de warning").' :
                                            <table class="mb-2"" border="1" style="width:auto; border: 1px solid #D8D8D8;" >
                                                <tr >
                                                    <td>
                                                        <div id="editor3" class="bootstrap-wysiwyg-editor px-3 py-2" style="min-height:100px; min-width:330px;">'.$rparameters['login_message_warning'].'</div>
                                                    </td>
                                                </tr>
                                            </table>
                                            '.T_("Message d'alerte").' :
                                            <table class="" border="1" style="width:auto; border: 1px solid #D8D8D8;" >
                                                <tr >
                                                    <td>
                                                        <div id="editor2" class="bootstrap-wysiwyg-editor px-3 py-2" style="min-height:100px; min-width:330px;">'.$rparameters['login_message_alert'].'</div>
                                                    </td>
                                                </tr>
                                            </table>
                                            <input type="hidden" name="text" />
                                            <input type="hidden" name="text2" />
                                            <input type="hidden" name="text3" />
                                        </div>
                                        ';
                                        ?>
                                    </div>
                                    <div class="pt-1"></div>
                                    <label class="lbl">
                                        <input id="company_message" name="company_message" type="checkbox" <?php if($rparameters['company_message']) {echo 'checked';}?>  value="1" />
                                    </label>
                                    <span class="lbl">&nbsp;<?php echo T_("Message d'information par société"); ?></span>
                                    <i data-toggle="tooltip" id="tooltip74" data-placement="auto" data-original-title="<?php echo T_("Permet d'afficher un message d'information dans l'application en fonction d'une société, le paramétrage est à réaliser sur l'administration de la liste des sociétés."); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    <div class="pt-1"></div>
                                    <label class="lbl" for="login_background"><?php echo T_("Fond d'écran sur la page de connexion"); ?> : </label>
                                    <?php
                                        if($rparameters['login_background'])
                                        {
                                            echo '
                                                <img height="40" src="./upload/login_background/'.$rparameters['login_background'].'" />	
                                                <a title="'.T_("Supprimer ce fond d'écran").'" href="./index.php?page=admin&subpage=parameters&tab=general&subtab=display&action=delete_login_background">
                                                    <i class="fa fa-trash text-danger "><!----></i>
                                                </a>
                                            ';
                                        } else {
                                            echo '<input type="file" id="login_background" name="login_background" />';
                                        }
                                    ?>
                                    <div class="pt-1">
                                        <span  class="lbl"><i class="fa fa-caret-right text-primary-m2"><!----></i> <a target="_blank" href="./monitor.php?user_id=<?php echo $_SESSION['user_id']; ?>&amp;key=<?php echo $rparameters['server_private_key']; ?>"><?php echo T_('Écran de supervision'); ?></a></span>
                                    </div>
                                </div>
                            </div>
                            <!-- subtab tickets -->
                            <div class="tab-pane fade text-95 <?php if($_GET['subtab']=='ticket') {echo 'show active';}?> ">
                                <div class="text-primary-d3 text-110 mb-2">
                                    <label  class="lbl" for="ticket_increment_number"><?php echo T_("Numéro d'incrémentation"); ?> : </label>
                                    <input style="width:auto" class="form-control d-inline-block" type="text" size="6" name="ticket_increment_number" id="ticket_increment_number" value="">
                                    <i data-toggle="tooltip" id="tooltip12" data-placement="auto" data-original-title="<?php echo T_("Permet d'initialiser le compteur de ticket à une valeur numérique. Attention vous devez spécifier une valeur supérieur au numéro de ticket actuel le plus haut et ne pourrez plus redéfinir le compteur à une valeur inférieur. Saisir la valeur puis enregistrer, il est normal que le champ soit ensuite vide."); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    <div class="pt-1"></div>
                                    <label class="lbl" for="order"><?php echo T_('Ordre de tri'); ?> :</label>
                                    <select style="width:auto" class="form-control d-inline-block" id="order" name="order" >
                                        <option <?php if($rparameters['order']=='tstates.number, tincidents.priority, tincidents.criticality, tincidents.date_create') {echo "selected ";} ?> value="tstates.number, tincidents.priority, tincidents.criticality, tincidents.date_create"><?php echo T_('État > Priorité > Criticité > Date de création'); ?></option>
                                        <option <?php if($rparameters['order']=='tstates.number, tincidents.priority, tincidents.criticality, tincidents.date_hope') {echo "selected ";} ?> value="tstates.number, tincidents.priority, tincidents.criticality, tincidents.date_hope"><?php echo T_('État > Priorité > Criticité > Date de résolution estimée'); ?></option>
                                        <option <?php if($rparameters['order']=='tstates.number, tincidents.date_hope, tincidents.priority, tincidents.criticality') {echo "selected ";} ?> value="tstates.number, tincidents.date_hope, tincidents.priority, tincidents.criticality"><?php echo T_('État > Date de résolution estimée > Priorité > Criticité'); ?></option>
                                        <option <?php if($rparameters['order']=='tstates.number, tincidents.date_hope, tincidents.criticality, tincidents.priority') {echo "selected ";} ?> value="tstates.number, tincidents.date_hope, tincidents.criticality, tincidents.priority"><?php echo T_('État > Date de résolution estimée > Criticité > Priorité'); ?></option>
                                        <option <?php if($rparameters['order']=='tstates.number, tincidents.criticality, tincidents.date_hope, tincidents.priority') {echo "selected ";} ?> value="tstates.number, tincidents.criticality, tincidents.date_hope, tincidents.priority"><?php echo T_('État > Criticité > Date de résolution estimée > Priorité'); ?></option>
                                        <option <?php if($rparameters['order']=='id') {echo "selected ";} ?> value="id"><?php echo T_('Numéro de ticket'); ?></option>
                                        <option <?php if($rparameters['order']=='date_modif') {echo "selected ";} ?> value="date_modif"><?php echo T_('Date de dernière modification'); ?></option>
                                    </select>
                                    <i data-toggle="tooltip" id="tooltip13" data-placement="auto" data-original-title="<?php echo T_("Détermine l'ordre de classement des tickets dans la liste des tickets"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    <div class="pt-1"></div>
                                    <label class="lbl" for="ticket_default_state"><?php echo T_('État par défaut lors de la création de tickets'); ?> :</label>
                                    <select style="width:auto" class="form-control d-inline-block" id="ticket_default_state" name="ticket_default_state" >
                                        <?php
                                            $qry = $db->prepare("SELECT `id`,`name` FROM `tstates` ORDER BY `number`");
                                            $qry->execute();
                                            while ($row = $qry->fetch())
                                            {
                                                if($rparameters['ticket_default_state']==$row['id'])
                                                {echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';}
                                                else 
                                                {echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';}
                                            }
                                            $qry->closeCursor();
                                        ?>
                                    </select>
                                    <i data-toggle="tooltip" id="tooltip14" data-placement="auto" data-original-title="<?php echo T_("Détermine l'état par défaut lors de la création des tickets par les utilisateurs ou par le connecteur IMAP"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    <div class="pt-1"></div>
                                    <input type="checkbox" <?php if($rparameters['meta_state']) {echo "checked";}  ?> name="meta_state" id="meta_state" value="1">
                                    <label class="lbl mr-1" for="meta_state"><?php echo T_('Gestion du méta état "à traiter"'); ?> </label>
                                    <i data-toggle="tooltip" id="tooltip15" data-placement="auto" data-original-title="<?php echo T_("Permet d'afficher un nouvel état regroupant les états en attente de PEC, en cours, et en attente de retour"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    <div class="pt-1"></div>
                                    <label class="lbl">
                                        <input type="checkbox" <?php if($rparameters['ticket_places']) {echo "checked";}  ?> name="ticket_places" id="ticket_places" value="1">
                                        <span class="lbl"><?php echo T_('Gestion des lieux'); ?></span>
                                        <i data-toggle="tooltip" id="tooltip16" data-placement="auto" data-original-title="<?php echo T_("Permet un rattachement du ticket à une localité, une liste des lieux est éditable dans la section liste, un nouveau champ sera disponible sur le ticket"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    </label>
                                    <div class="pt-1"></div>
                                    <label class="lbl">
                                        <input type="checkbox" <?php if($rparameters['ticket_type']) {echo "checked";} ?> name="ticket_type" id="ticket_type" value="1">
                                        <span class="lbl"><?php echo T_('Gestion des types'); ?></span>
                                        <i data-toggle="tooltip" id="tooltip17" data-placement="auto" data-original-title="<?php echo T_("Permet de définir un type à un ticket (ex: Demande, Incident...), ajoute une ligne sur le ticket, la liste des types est administrable dans Administration > Liste"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    </label>
                                    <div class="pt-1"></div> 
                                    <label class="lbl">
                                        <input type="checkbox" <?php if($rparameters['ticket_observer']) {echo "checked";} ?> name="ticket_observer" id="ticket_observer" value="1">
                                        <span class="lbl"><?php echo T_('Gestion des observateurs'); ?></span>
                                        <i data-toggle="tooltip" id="tooltip18" data-placement="auto" data-original-title="<?php echo T_("Permet d'ajouter sur le ticket des utilisateurs qui ne sont pas demandeur ou technicien, et qui peuvent suivre le ticket cf droits ticket_observer, ticket_observer_disp, side_your_observer. Lorsque cette option est activée, l'observateur recevra un mail si la notification automatique au demandeur lors de la modification par un technicien est activée"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    </label>
                                    <div class="pt-1"></div>
                                    <label class="lbl">
                                        <input id="ticket_autoclose" name="ticket_autoclose" type="checkbox" <?php if($rparameters['ticket_autoclose']) {echo "checked";} ?>  value="1">
                                        <span class="lbl"><?php echo T_('Fermeture automatique'); ?></span>
                                        <i data-toggle="tooltip" id="tooltip19" data-placement="auto" data-original-title="<?php echo T_("Permet de modifier automatiquement les tickets dans l'état résolu après X jours depuis la date de création du ticket. A noter les modifications sont réalisées une fois par jour lors de l'affichage de la page de login"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    </label>
                                    <div class="ml-4" id="ticket_autoclose_parameters">
                                        <?php
                                            echo '
                                                <label  class="lbl">
                                                    '.T_('Délais').' : <input style="width:auto" class="form-control d-inline-block" name="ticket_autoclose_delay" type="text" value="'.$rparameters['ticket_autoclose_delay'].'" size="3" /> '.T_('jours').'
                                                    <i data-toggle="tooltip" id="tooltip20" data-placement="auto" data-original-title="'.T_("Définit le nombre de jours avant la clôture automatique du ticket").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                                    <div class="pt-1"></div>
                                                    '.T_('État').' :
                                                    <select style="width:auto" class="form-control d-inline-block" id="ticket_autoclose_state" name="ticket_autoclose_state">
                                                        <option '; if($rparameters['ticket_autoclose_state']==0) {echo 'selected';} echo ' value="0">'.T_('Tous').'</option>
                                                        <option '; if($rparameters['ticket_autoclose_state']==6) {echo 'selected';} echo ' value="6">'.T_('Attente retour').'</option>
                                                    </select>
                                                    <i data-toggle="tooltip" id="tooltip21" data-placement="auto" data-original-title="'.T_("Spécifie si la fermeture automatique s'applique à tous les états ou uniquement aux tickets dans l'état Attente retour").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                                </label>
                                            ';
                                        ?>
                                    </div>
                                    <div class="pt-1"></div>
                                    <label  class="lbl">
                                        <input id="user_validation" name="user_validation" type="checkbox" <?php if($rparameters['user_validation']) {echo "checked";} ?> value="1">
                                        <span class="lbl"><?php echo T_('Validation demandeur'); ?></span>
                                        <i data-toggle="tooltip" id="tooltip22" data-placement="auto" data-original-title="<?php echo T_("Permet de valider auprès du demandeur que son ticket est bien résolu, un mail lui est transmit X jours après la clôture du ticket"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    </label>
                                    <div id="user_validation_parameters">
                                        <?php
                                            echo '
                                                <blockquote>
                                                    <label class="lbl ml-4">
                                                        <i class="fa fa-circle text-success"><!----></i>
                                                        '.T_('Envoyer le mail').' <input style="width:auto" class="form-control d-inline-block" name="user_validation_delay" type="text" value="'.$rparameters['user_validation_delay'].'" size="2" />  '.T_('jours après la résolution du ticket').'
                                                        <i data-toggle="tooltip" id="tooltip23" data-placement="auto" data-original-title="'.T_("Définit le nombre de jours après la clôture du ticket qui déclenchera l'émission du mail de validation").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                                        <div class="pt-1"></div>
                                                        <i class="fa fa-circle text-success"><!----></i>
                                                        '.T_('Périmètre').' :
                                                        <div class="controls ml-4">
                                                            <div class="radio">
                                                                <label><input type="radio" class="ace" name="user_validation_perimeter" value="all" '; if($rparameters['user_validation_perimeter']=='all') {echo 'checked';} echo ' > <span class="lbl"> '.T_('Tous les tickets').'</span></label>
                                                            </div>
                                                            <div class="radio">
                                                                <label><input type="radio" class="ace" name="user_validation_perimeter" value="mark" '; if($rparameters['user_validation_perimeter']=='mark') {echo 'checked';} echo '> <span class="lbl"> '.T_('Tickets marqués à valider').' </span></label>
                                                                <i data-toggle="tooltip" id="tooltip24" data-placement="auto" data-original-title="'.T_("Ajoute un nouveau champ sur le ticket, permettant de sélectionner par ticket ceux qui nécessite une validation. Une nouvelle section est également disponible dans l'administration de la liste des types, permettant de renseigner automatiquement le champ en fonction d'un type.").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                                            </div>
                                                        </div>
                                                        <i class="fa fa-circle text-success"><!----></i>
                                                        '.T_('Exclusion').' : 
                                                        <i data-toggle="tooltip" id="tooltip25" data-placement="auto" data-original-title="'.T_("Permet de ne pas envoyer de validation sur une catégorie ou sous-catégorie de ticket spécifiques").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                                        <div class="pt-1"></div>
                                                        <div class="controls ml-4">';
                                                            $qry=$db->prepare("SELECT `id`,`category`,`subcat` FROM `tparameters_user_validation_exclusion`");
                                                            $qry->execute();
                                                            while($exclusion=$qry->fetch()) 
                                                            {
                                                                if($exclusion['category'])
                                                                {
                                                                    //get category name
                                                                    $qry2=$db->prepare("SELECT `name` FROM `tcategory` WHERE id=:id");
                                                                    $qry2->execute(array('id' => $exclusion['category']));
                                                                    $category=$qry2->fetch();
                                                                    $qry2->closeCursor();
                                                                    echo '<i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Catégorie').' : '.$category['name'].' <a title="'.T_('Supprimer cette exclusion').'"  onClick="javascript: return confirm(\''.T_('Êtes-vous sur de vouloir supprimer cette exclusion ?').'\');" href="./index.php?page=admin&amp;subpage=parameters&amp;tab=general&amp;action=delete_user_validation_exclusion&id='.$exclusion['id'].'"><i class="fa fa-trash text-danger"><!----></i></a><br />';
                                                                }
                                                                if($exclusion['subcat'])
                                                                {
                                                                    //get subcat name
                                                                    $qry2=$db->prepare("SELECT `name` FROM `tsubcat` WHERE id=:id");
                                                                    $qry2->execute(array('id' => $exclusion['subcat']));
                                                                    $subcat=$qry2->fetch();
                                                                    $qry2->closeCursor();
                                                                    echo '<i class="fa fa-caret-right text-primary-m2"><!----></i> '.T_('Sous-catégorie').' : '.$subcat['name'].' <a title="'.T_('Supprimer cette exclusion').'" onClick="javascript: return confirm(\''.T_('Êtes-vous sur de vouloir supprimer cette exclusion ?').'\');" href="./index.php?page=admin&amp;subpage=parameters&amp;tab=general&amp;action=delete_user_validation_exclusion&id='.$exclusion['id'].'"<i class="fa fa-trash text-danger "><!----></i></a><br />';
                                                                }
                                                            }
                                                            $qry->closeCursor();
                                                            echo T_('Ajouter une catégorie'). ' :
                                                            <select style="width:auto" class="form-control d-inline-block" id="user_validation_category" name="user_validation_category">
                                                                ';
                                                                $qry=$db->prepare("SELECT `id`,`name` FROM `tcategory`");
                                                                $qry->execute();
                                                                while($category=$qry->fetch()) 
                                                                {
                                                                    echo '<option value="'.$category['id'].'">'.$category['name'].'</option>';
                                                                }
                                                                $qry->closeCursor();
                                                                echo '
                                                            </select>
                                                            <div class="pt-1"></div>
                                                            '.T_('Ajouter une sous-catégorie').' :
                                                            <select style="width:auto" class="form-control d-inline-block" id="user_validation_subcat" name="user_validation_subcat">
                                                                ';
                                                                $qry=$db->prepare("SELECT `id`,`name` FROM `tsubcat`");
                                                                $qry->execute();
                                                                while($subcat=$qry->fetch()) 
                                                                {
                                                                    echo '<option value="'.$subcat['id'].'">'.$subcat['name'].'</option>';
                                                                }
                                                                $qry->closeCursor();
                                                                echo '
                                                            </select>
                                                        </div>
                                                    </label>
                                                </blockquote>
                                            ';
                                        ?>
                                    </div>
                                    <div class="pt-1"></div>
                                    <label  class="lbl">
                                        <input type="checkbox" <?php if($rparameters['ticket_cat_auto_attribute']) {echo "checked";} ?> name="ticket_cat_auto_attribute" value="1">
                                        <span class="lbl"><?php echo T_('Attribution automatique à un technicien en fonction de la catégorie'); ?></span>
                                        <i data-toggle="tooltip" id="tooltip26" data-placement="auto" data-original-title="<?php echo T_("Permet d'attribuer automatiquement un ticket à un technicien ou un groupe de technicien, en fonction de la catégorie ou sous-catégorie du ticket. &#10;Disponible uniquement lors de l'ouverture d'un ticket et que le champ technicien n'est pas affiché. &#10;Si un conflit existe entre une attribution définie sur une catégorie et sous-catégorie alors c'est la sous-catégorie qui sera prise en compte.&#10;(Cf Administration > Liste > Catégorie"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    </label>
                                    <div class="pt-1"></div>
                                    <label  class="lbl">
                                        <input type="checkbox" <?php if($rparameters['ticket_time_response_element']) {echo "checked";} ?> name="ticket_time_response_element" value="1">
                                        <span class="lbl"><?php echo T_('Gestion du temps passé par élément de résolution'); ?></span>
                                        <i data-toggle="tooltip" id="tooltip27" data-placement="auto" data-original-title="<?php echo T_("Ajoute une nouvelle liste déroulante sur l'ajout ou la modification d'un élément de résolution, permettant de définir du temps passé par élément"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    </label>
                                    <div class="pt-1"></div>
                                    <label  class="lbl">
                                        <input type="checkbox" <?php if($rparameters['ticket_open_message']) {echo "checked";} ?> name="ticket_open_message" id="ticket_open_message" value="1">
                                        <span class="lbl"><?php echo T_("Message à l'ouverture d'un ticket"); ?></span>
                                        <i data-toggle="tooltip" id="tooltip28" data-placement="auto" data-original-title="<?php echo T_("Ajoute un message d'information en haut de la page lors de l'ouverture d'un nouveau ticket"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        <div id="ticket_open_message_parameter">
                                            <textarea id="ticket_open_message_text" name="ticket_open_message_text" class="form-control mt-2" style="width:500px;" ><?php echo $rparameters['ticket_open_message_text']; ?></textarea>
                                        </div>
                                    </label>
                                    <div class="pt-1"></div>
                                    <label  class="lbl">
                                        <input type="checkbox" <?php if($rparameters['ticket_recurrent_create']) {echo "checked";} ?> name="ticket_recurrent_create" id="ticket_recurrent_create" value="1">
                                        <span class="lbl"><?php echo T_("Création de tickets récurrents"); ?></span>
                                        <i data-toggle="tooltip" id="tooltip53" data-placement="auto" data-original-title="<?php echo T_("Permets la création automatique de ticket à une fréquence définie basée sur un modèle de ticket, cf Administration > Listes > Modèles de ticket"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    </label>
                                </div>
                            </div>
                            <!-- subtab sound -->
                            <div class="tab-pane fade text-95 <?php if($_GET['subtab']=='sound') {echo 'show active';}?> ">
                                <div class="text-primary-d3 text-110 mb-2">
                                    <label  class="lbl">
                                        <input type="checkbox" <?php if($rparameters['notify']) echo "checked"; ?> name="notify" value="1">
                                        <span class="lbl">&nbsp;<?php echo T_('Activer la notification sonore pour les nouveaux tickets'); ?></span>
                                        <i data-toggle="tooltip" id="tooltip29" data-placement="auto" data-original-title="<?php echo T_("Active l'avertisseur sonore pour le technicien si un utilisateur déclare un ticket (fonctionne uniquement sur Chrome, Firefox et Safari"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    </label>
                                </div>
                            </div>
                            <!-- subtab users -->
                            <div class="tab-pane fade text-95 <?php if($_GET['subtab']=='user') {echo 'show active';}?> ">
                                <div class="text-primary-d3 text-110 mb-2">
                                    <label  class="lbl"> 
                                        <input type="checkbox" <?php if($rparameters['user_advanced']) echo "checked"; ?> name="user_advanced" value="1">
                                        <span class="lbl">&nbsp;<?php echo T_('Utiliser les propriétés utilisateur avancées'); ?></span>
                                        <i data-toggle="tooltip" id="tooltip30" data-placement="auto" data-original-title="<?php echo T_("Ajoute des champs supplémentaires aux propriétés utilisateurs, Société, FAX, Adresses..."); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    </label>
                                    <br />
                                    <label  class="lbl"> 
                                        <input type="checkbox" <?php if($rparameters['user_register']) echo "checked"; ?> name="user_register" value="1">
                                        <span class="lbl">&nbsp;<?php echo T_("Les utilisateurs peuvent s'enregistrer"); ?></span>
                                        <i data-toggle="tooltip" id="tooltip31" data-placement="auto" data-original-title="<?php echo T_("Ajoute un bouton sur la page de connexion, permettant la création de nouveaux utilisateurs"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    </label>
                                    <br />
                                    <label  class="lbl"> 
                                        <input type="checkbox" <?php if($rparameters['user_limit_ticket']) echo "checked"; ?> name="user_limit_ticket" value="1">
                                        <span class="lbl">&nbsp;<?php echo T_('Limite de tickets par utilisateur'); ?></span>
                                        <i data-toggle="tooltip" id="tooltip32" data-placement="auto" data-original-title="<?php echo T_("Permet de limiter le nombre de tickets qu'un utilisateur peut ouvrir pour un période donnée, les paramètres se trouvent sur la fiche de l'utilisateur. Si aucun paramètres n'est définit la limitation ne sera pas active. Si la limite de ticket est atteinte alors la création de ticket est bloqué pour l'utilisateur"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        <br />
                                    </label>
                                    <br />
                                    <label  class="lbl"> 
                                        <input type="checkbox" <?php if($rparameters['company_limit_ticket']) echo "checked"; ?> name="company_limit_ticket" value="1">
                                        <span class="lbl">&nbsp;<?php echo T_('Limite de tickets par Société'); ?></span>
                                        <i data-toggle="tooltip" id="tooltip33" data-placement="auto" data-original-title="<?php echo T_("Permet de limiter le nombre de tickets qu'une société peut ouvrir pour un période donnée, les paramètres se trouvent Administration > Listes > Société . Si aucun paramètres n'est définit la limitation ne sera pas active. Si la limite de ticket est atteinte alors la création de ticket est bloqué pour l'ensemble des utilisateurs associés à cette société"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        <br />
                                    </label>
                                    <br />
                                    <label  class="lbl"> 
                                        <input type="checkbox" <?php if($rparameters['company_limit_hour']) echo "checked"; ?> name="company_limit_hour" value="1">
                                        <span class="lbl">&nbsp;<?php echo T_("Limite d'heures par Société"); ?></span>
                                        <i data-toggle="tooltip" id="tooltip34" data-placement="auto" data-original-title="<?php echo T_("Permet d'associer un nombre d'heures à une société pour une période donnée, les heures utilisées sont basées sur le champ temps passé du ticket. Les paramètres se trouvent Administration > Listes > Société . Si aucun paramètres n'est définit la limitation ne sera pas active. Si la limite d'heures est atteinte alors la création de ticket est bloqué pour l'ensemble des utilisateurs associés à cette société"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        <br />
                                    </label>
                                    <br />
                                    <label  class="lbl"> 
                                        <input type="checkbox" <?php if($rparameters['user_company_view']) echo "checked"; ?> name="user_company_view" value="1">
                                        <span class="lbl">&nbsp;<?php echo T_('Les utilisateurs peuvent voir tous les tickets de leur société'); ?></span>
                                        <i data-toggle="tooltip" id="tooltip35" data-placement="auto" data-original-title="<?php echo T_("Ajoute une nouvelle vue pour les utilisateurs, afin de visualiser tous les tickets déclarés par l'ensemble des utilisateurs associés à une société. (Le droit side_company est nécessaire pour disposer de l'accès, le droit de modification de société user_profil_company est à désactiver pour les utilisateurs)"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        <br />
                                    </label>
                                    <br />
                                    <label class="lbl"> 
                                        <input type="checkbox" <?php if($rparameters['user_agency']) echo "checked"; ?> name="user_agency" value="1">
                                        <span class="lbl">&nbsp;<?php echo T_('Les utilisateurs appartiennent à des agences'); ?></span>
                                        <i data-toggle="tooltip" id="tooltip36" data-placement="auto" data-original-title="<?php echo T_("Ajoute une nouvelle liste dans Administration > Liste > Agence"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        <br />
                                    </label>
                                    <br />
                                    <label  class="lbl"> 
                                        <input type="checkbox" <?php if($rparameters['user_limit_service']) echo "checked"; ?> name="user_limit_service" value="1">
                                        <span class="lbl">&nbsp;<?php echo T_('Les utilisateurs ne voient que les tickets de leurs services'); ?></span>
                                        <i data-toggle="tooltip" id="tooltip37" data-placement="auto" data-original-title="<?php echo T_("Permet de cloisonner la liste de tickets ainsi que les catégories, droits associés : dashboard_service_only, side_all, side_all_service_disp, side_all_service_edit"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        <br />
                                    </label>
                                    <br />
                                    <label  class="lbl"> 
                                        <input type="checkbox" <?php if($rparameters['user_forgot_pwd']) echo "checked"; ?> name="user_forgot_pwd" value="1">
                                        <span class="lbl">&nbsp;<?php echo T_('Les utilisateurs peuvent réinitialiser leurs mots de passe'); ?></span>
                                        <i data-toggle="tooltip" id="tooltip38" data-placement="auto" data-original-title="<?php echo T_("Ajoute un lien à l'écran de connexion permettant de réinitialiser le mot de passe de l'utilisateur, ne fonctionne que si le connecteur LDAP est désactivé et le connecteur SMTP activé"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        <br />
                                    </label>
                                    <br />
                                    <label class="lbl"> 
                                        <input id="user_disable_attempt" name="user_disable_attempt" type="checkbox" <?php if($rparameters['user_disable_attempt']==1) echo "checked"; ?>  value="1">
                                        <span class="lbl">&nbsp;<?php echo T_('Les utilisateurs sont désactivés après plusieurs tentatives de connexion infructueuses'); ?></span>
                                        <i data-toggle="tooltip" id="tooltip39" data-placement="auto" data-original-title="<?php echo T_("Permet de désactiver automatiquement les utilisateurs après X tentatives d'authentification échoués (Le nombre de tentatives est paramétrable)"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        <br />
                                        <div id="user_disable_attempt_parameters">
                                            <?php
                                                echo '
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-caret-right text-primary-m2"><!----></i>
                                                Nombre de tentatives : 
                                                <input style="width:auto" class="form-control d-inline-block" name="user_disable_attempt_number" type="text" value="'.$rparameters['user_disable_attempt_number'].'" size="3">
                                                ';
                                            ?>
                                        </div>
                                    </label>
                                    <br />
                                    <label class="lbl"> 
                                        <input id="user_password_policy" name="user_password_policy" type="checkbox" <?php if($rparameters['user_password_policy']) {echo "checked";} ?> value="1">
                                        <span class="lbl">&nbsp;<?php echo T_("Politique de gestion des mots de passe"); ?></span>
                                        <i data-toggle="tooltip" id="tooltip40" data-placement="auto" data-original-title="<?php echo T_("Permet d'ajouter des contraintes lors de la définition de mot de passe utilisateur"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                        <br />
                                        <div id="user_password_policy_parameters">
                                            <?php
                                                echo '
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-caret-right text-primary-m2"><!----></i>
                                                Longueur minimum : <input style="width:auto" class="form-control d-inline-block"  name="user_password_policy_min_lenght" type="text" value="'.$rparameters['user_password_policy_min_lenght'].'" size="2"> '.T_('caractères').'<br />
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-caret-right text-primary-m2"><!----></i>
                                                Caractères spéciaux obligatoires : <input style="vertical-align: middle;" type="checkbox" '; if($rparameters['user_password_policy_special_char']==1) {echo "checked";} echo ' name="user_password_policy_special_char" value="1">
                                                <span class="lbl">&nbsp;</span>
                                                <br />
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-caret-right text-primary-m2"><!----></i>
                                                Minuscule et majuscule obligatoires : <input style="vertical-align: middle;" type="checkbox" '; if($rparameters['user_password_policy_min_maj']==1) {echo "checked";} echo ' name="user_password_policy_min_maj" value="1">
                                                <span class="lbl">&nbsp;</span>
                                                <br />
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-caret-right text-primary-m2"><!----></i>
                                                Expiration après : <input style="width:auto" class="form-control d-inline-block" name="user_password_policy_expiration" type="text" value="'.$rparameters['user_password_policy_expiration'].'" size="2"> '.T_('jours').'
                                                <i data-toggle="tooltip" id="tooltip65" data-placement="auto" data-original-title="'.T_("Si la valeur est définie à 0, alors ce paramètre est désactivé").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                                ';
                                            ?>
                                        </div>
                                    </label>
                                    <br />
                                    <label class="lbl"> 
                                        <input id="user_admin_ip" name="user_admin_ip" type="checkbox" <?php if($rparameters['user_admin_ip']) {echo "checked";} ?> value="1">
                                        <span class="lbl">&nbsp;<?php echo T_("Notification de connexion d'un administrateur sur une nouvelle IP"); ?></span>
                                        <i data-toggle="tooltip" id="tooltip70" data-placement="auto" data-original-title="<?php echo T_("Permet d'envoyer un mail lorsqu'un profil administrateur se connecte depuis une nouvelle adresse IP"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    </label>
                                </div>
                            </div>
                            <!-- subtab mail -->
                            <div class="tab-pane fade text-95 <?php if($_GET['subtab']=='mail') {echo 'show active';}?> ">
                                <div class="text-primary-d3 text-110 mb-2">
                                <i class="fa fa-caret-right text-primary-m2"><!----></i> <span  class="lbl"><?php echo T_("Envoi de mail automatique"); ?> :</span>
                                <br />
                                <div class="ml-4">
                                    <label  class="lbl">
                                        <input type="checkbox" <?php if($rparameters['mail_auto']) echo "checked"; ?> name="mail_auto" value="1" />
                                        <?php echo T_("Au demandeur lors de l'ouverture ou fermeture d'un ticket par un technicien"); ?>
                                    </label>
                                    <br />
                                    <label  class="lbl">
                                        <input type="checkbox" <?php if($rparameters['mail_auto_user_newticket']) echo "checked"; ?> name="mail_auto_user_newticket" value="1" />
                                        <?php echo T_("Au demandeur lors de l'ouverture d'un ticket par le demandeur"); ?>
                                    </label>
                                    <br />
                                    <label  class="lbl">
                                        <input type="checkbox" <?php if($rparameters['mail_auto_user_modify']) echo "checked"; ?> name="mail_auto_user_modify" value="1" />
                                        <?php echo T_("Au demandeur lors de l'ajout ou la modification de la résolution d'un ticket par un technicien"); ?>
                                    </label>
                                    <br />
                                    <label  class="lbl">
                                        <input type="checkbox" <?php if($rparameters['mail_auto_tech_modify']) echo "checked"; ?> name="mail_auto_tech_modify" value="1" />
                                        <?php echo T_("Au technicien lors de la modification d'un ticket par un demandeur"); ?>
                                    </label>
                                    <br />
                                    <label  class="lbl">
                                        <input type="checkbox" <?php if($rparameters['mail_auto_tech_attribution']) echo "checked"; ?> name="mail_auto_tech_attribution" value="1" />
                                        <?php echo T_("Au technicien lors de l'attribution d'un ticket à un technicien"); ?>
                                    </label class="lbl">
                                    <br />
                                    <label class="lbl">
                                        <input id="mail_newticket" name="mail_newticket" type="checkbox" <?php if($rparameters['mail_newticket']) echo "checked"; ?> value="1" />
                                        <?php echo T_("A une adresse mail lors de l'ouverture d'un ticket par un demandeur"); ?>
                                    </label>
                                    <div id="mail_newticket_parameters" >
                                        <?php 
                                        echo '
                                        <div class="pt-1"></div>
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label for="mail_newticket_address">'.T_('Adresse mail').' :</label> <input style="width:auto" class="form-control d-inline-block" name="mail_newticket_address" id="mail_newticket_address" type="text" value="'.$rparameters['mail_newticket_address'].'" size="30" />
                                        <div class="pt-1"></div>
                                        ';
                                        ?>
                                    </div>
                                    <div id="mail_auto_type_parameters" >
                                        <label class="lbl">
                                            <input id="mail_auto_type" name="mail_auto_type" type="checkbox" <?php if($rparameters['mail_auto_type']) echo "checked"; ?> value="1" />
                                            <?php echo T_("Aux adresses mails définies sur le type de ticket, lors de l'ouverture du ticket"); ?>
                                        </label>
                                    </div>
                                </div>
                                <i class="fa fa-caret-right text-primary-m2"><!----></i> <label class="lbl" for="mail_txt"><?php echo T_('Texte début du mail'); ?> :</label> 
                                <input style="width:auto" class="form-control d-inline-block" name="mail_txt" id="mail_txt" type="text" value="<?php echo $rparameters['mail_txt']; ?>" size="80" />
                                <i data-toggle="tooltip" id="tooltip42" data-placement="auto" data-original-title="<?php echo T_("Vous pouvez utiliser du code HTML (Exemple: <br />, <b></b>...)"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                <div class="pt-1"></div>
                                <i class="fa fa-caret-right text-primary-m2"><!----></i> <label class="lbl" for="mail_txt_end"><?php echo T_('Texte fin du mail'); ?> :</label> 
                                <input style="width:auto" class="form-control  d-inline-block" name="mail_txt_end" id="mail_txt_end" type="text" value="<?php echo $rparameters['mail_txt_end']; ?>" size="83" />
                                <i data-toggle="tooltip" id="tooltip43" data-placement="auto" data-original-title="<?php echo T_("Si vide texte automatique généré, pour le personnaliser sous pouvez utiliser du code HTML (<br />, <b></b>), des balises sont également disponible ([tech_name] Prénom et Nom du technicien, [tech_phone] téléphone du technicien, [link] Lien vers le ticket si le paramètre est activé)"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                <div class="pt-1"></div>
                                <i class="fa fa-caret-right text-primary-m2"><!----></i> <label class="lbl" for="mail_cc"><?php echo T_('Adresse en copie'); ?> :</label> 
                                <input style="width:auto" class="form-control d-inline-block" name="mail_cc" id="mail_cc" type="text" value="<?php echo $rparameters['mail_cc']; ?>" size="30" />
                                <i data-toggle="tooltip" id="tooltip44" data-placement="auto" data-original-title="<?php echo T_("Adresse mail en copie des mails émit par l'application, il est possible d'enregistrer plusieurs adresses en les séparant avec un point-virgule"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                <div class="pt-1"></div>
                                <i class="fa fa-caret-right text-primary-m2"><!----></i> <label class="lbl" for="mail_from_name"><?php echo T_("Intitulé de l'émetteur"); ?> :</label> 
                                <input style="width:auto" class="form-control d-inline-block" name="mail_from_name" id="mail_from_name" type="text" value="<?php echo $rparameters['mail_from_name']; ?>" size="30" /><br />
                                <div class="pt-1"></div>
                                <i class="fa fa-caret-right text-primary-m2"><!----></i> <label class="lbl" for="mail_from_adr"><?php echo T_("Adresse de l'émetteur"); ?> :</label> 
                                <input style="width:auto" class="form-control d-inline-block" name="mail_from_adr" id="mail_from_adr" type="email" value="<?php echo $rparameters['mail_from_adr']; ?>" size="30" />
                                <i data-toggle="tooltip" id="tooltip45" data-placement="auto" data-original-title="<?php echo T_("Adresse d'envoi de tous les messages de l'application, si ce paramètre est vide les messages seront envoyés avec l'adresse mail de l'utilisateur connecté. Certains serveurs SMTP peuvent exiger que l'émetteur soit le même que le compte de connexion"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                <?php
                                //add warning if adresse is different than smtp connector
                                if($rparameters['mail'] && $rparameters['mail_username'] && $rparameters['mail_from_adr'] && ($rparameters['mail_from_adr']!=$rparameters['mail_username']))
                                {
                                    echo '<i data-toggle="tooltip" id="tooltip46" data-placement="auto" data-original-title="'.T_("L'adresse mail de l'émetteur est différente de celle configurée sur le connecteur SMTP, vos mails peuvent être bloqués ou considérés comme SPAM").'" class="fa fa-exclamation-triangle text-warning"><!----></i>
                                    ';
                                }
                                ?>
                                <br />
                                <div class="pt-1"></div>
                                <i class="fa fa-caret-right text-primary-m2"><!----></i> <label class="lbl" for="mail_reply"><?php echo T_("Adresse de réponse"); ?> :</label> 
                                <select name="mail_reply" id="mail_reply" class="form-control d-inline-block" style="width:auto;">
                                    <option value="sender" <?php if($rparameters['mail_reply']=='sender') {echo 'selected';} ?> ><?php echo T_("Adresse de l'émetteur"); ?></option>
                                    <option value="connected_user" <?php if($rparameters['mail_reply']=='connected_user') {echo 'selected';} ?>><?php echo T_("Adresse de l'utilisateur connecté"); ?></option>
                                </select>
                                <i data-toggle="tooltip" id="tooltip47" data-placement="auto" data-original-title="<?php echo T_("Adresse de réponse de tous les mails de l'application"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                <br />
                                
                                <div class="pt-1"></div>
                                <label class="lbl">
                                    <input type="checkbox" <?php if($rparameters['mail_cci']) echo "checked"; ?> name="mail_cci" value="1">
                                    <span class="lbl">&nbsp;<?php echo T_('Gestion de la copie cachée'); ?></span>
                                </label>
                                <i data-toggle="tooltip" id="tooltip48" data-placement="auto" data-original-title="<?php echo T_("Ajoute une nouvelle section Copie cachée, sur la page : Paramètres du mail disponible depuis le ticket, permettant d'ajouter des destinataires en copie cachée"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                
                                <div class="pt-1"></div>
                                <label class="lbl">
                                    <input type="checkbox" <?php if($rparameters['mail_cc_tech']) echo "checked"; ?> name="mail_cc_tech" value="1">
                                    <span class="lbl">&nbsp;<?php echo T_("Technicien en copie par défaut sur les mails manuels"); ?></span>
                                </label>
                                <i data-toggle="tooltip" id="tooltip71" data-placement="auto" data-original-title="<?php echo T_("Lors de l'envoi d'un mail manuel, si l'utilisateur réalisant l'action n'est pas le technicien en charge alors, le technicien en charge est ajouté en copie du mail"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                
                                <div class="pt-1"></div>
                                <label class="lbl">
                                    <input id="mail_link" name="mail_link" type="checkbox" <?php if($rparameters['mail_link']) echo "checked"; ?> value="1">
                                    <span class="lbl">&nbsp;<?php echo T_('Intégrer un lien vers GestSup'); ?></span>
                                </label>
                                <div id="mail_link_parameters">
                                    <?php
                                    echo '
                                    <div class="pt-1 ml-4">
                                        <i class="fa fa-caret-right text-primary-m2"><!----></i>
                                        <span class="lbl">&nbsp;'.T_('URL de redirection pour les utilisateurs non connectés').'</span> :
                                        <input style="width:auto;" class="form-control d-inline-block" type="text" name="mail_link_redirect_url" value="'.$rparameters['mail_link_redirect_url'].'">
                                        <i data-toggle="tooltip" id="tooltip49" data-placement="auto" data-original-title="'.T_("Lorsque qu'un utilisateur utilise le lien vers le ticket présent dans le mail de notification, il est alors redirigé vers l'URL spécifié si il n'est pas déja connecté à l'application. Si ce champ est vide alors l'utilisateur est redirigé vers la page de connexion.").'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    </div>
                                    ';
                                    ?>
                                </div>
                                <div class="pt-1"></div>
                                <label  class="lbl">
                                    <input type="checkbox" <?php if($rparameters['mail_order']==1) echo "checked"; ?> name="mail_order" value="1">
                                    <span class="lbl">&nbsp;<?php echo T_('Ordre antéchronologique dans les éléments de résolution'); ?></span>
                                    <i data-toggle="tooltip" id="tooltip50" data-placement="auto" data-original-title="<?php echo T_("Permet d'inverser le sens du fil de suivi de la résolution les éléments les plus récents seront en premier"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                </label>
                                <div class="pt-1"></div>
                                <label  class="lbl" for="mail_template"><?php echo T_("Modèle de mail"); ?> :</label>
                                <select style="width:auto" class="form-control d-inline-block" id="mail_template" name="mail_template" >
                                    <?php
                                        $mail_template = 'template/mail';
                                        $scanned_directory = array_diff(scandir($mail_template), array('..', '.', 'readme.txt'));
                                        foreach ($scanned_directory as $value)
                                        {
                                            if($value==$rparameters['mail_template']) {echo '<option selected value="'.$value.'">'.$value.'</option>';} else {echo '<option value="'.$value.'">'.$value.'</option>';}
                                        }
                                    ?>
                                </select>
                                <i data-toggle="tooltip" id="tooltip51" data-placement="auto" data-original-title="<?php echo T_("Permet de sélectionner le modèle de mail utilisé dans les notifications, parmi les fichiers présents dans le repertoire /template/mail. Vous pouvez créer un nouveau modèle de mail en déposant un fichier .htm dans le repertoire /template/mail, le fichier readme.txt vous indiquera les tags disponibles"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                <div class="pt-1"></div>
                                <i class="fa fa-caret-right text-primary-m2"><!----></i> <label class="lbl" for="mail_color_title"><?php echo T_('Couleur du titre'); ?> :</label> #<input style="width:auto" class="form-control d-inline-block" name="mail_color_title" id="mail_color_title" type="text" value="<?php echo $rparameters['mail_color_title']; ?>" size="6" /><br />
                                <div class="pt-1"></div>
                                <i class="fa fa-caret-right text-primary-m2"><!----></i> <label class="lbl" for="mail_color_title"><?php echo T_('Couleur du fond'); ?> :</label> #<input style="width:auto" class="form-control d-inline-block" name="mail_color_bg" id="mail_color_bg" type="text" value="<?php echo $rparameters['mail_color_bg']; ?>" size="6" /><br />
                                <div class="pt-1"></div>
                                <i class="fa fa-caret-right text-primary-m2"><!----></i> <label class="lbl" for="mail_color_text"><?php echo T_('Couleur du texte'); ?> :</label> #<input style="width:auto" class="form-control d-inline-block" name="mail_color_text" id="mail_color_text" type="text" value="<?php echo $rparameters['mail_color_text']; ?>" size="6" /><br />		
                                </div>
                            </div>
                            <!-- subtab debug -->
                            <div class="tab-pane fade text-95 <?php if($_GET['subtab']=='debug') {echo 'show active';}?> ">
                                <div class="text-primary-d3 text-110 mb-2">
                                    <label> 
                                        <input type="checkbox" <?php if($rparameters['debug']) echo "checked"; ?> name="debug" value="1">
                                        <span class="lbl">&nbsp;<?php echo T_('Activer le mode de débogage'); ?></span>
                                        <i data-toggle="tooltip" id="tooltip52" data-placement="auto" data-original-title="<?php echo T_("Active le mode débogage afin d'afficher les éléments de résolution de problèmes"); ?>" class="fa fa-question-circle text-primary-m2"><!----></i>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="border-t-1 brc-secondary-l1 bgc-secondary-l4 py-3 text-center">
            <button name="submit_general" id="submit_general" value="submit_general" type="submit" class="btn btn-success">
                <i class="fa fa-check mr-1"><!----></i>
                <?php echo T_('Valider'); ?>
            </button>
        </div>
    </form>
</div>