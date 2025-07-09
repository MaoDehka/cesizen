<?php
################################################################################
# @Name : /core/azure_ad.php
# @Description : page to synchronize users from Entra ID to GestSup
# @call : /admin/user.php
# @Author : Flox
# @Create : 17/01/2023
# @Update : 25/03/2024
# @Version : 3.2.49
################################################################################

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

//initialize variables
if(!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) $_SERVER['HTTP_ACCEPT_LANGUAGE']=0;
if(!isset($_GET['action'])) $_GET['action']=0;

//call via php cli on server
if(php_sapi_name() == "cli")
{
	//database connection
	require_once(__DIR__."/../vendor/autoload.php");
	require_once(__DIR__."/../connect.php");
    require_once(__DIR__."/../core/functions.php");
	
	//switch SQL MODE to allow empty values with latest version of MySQL
	$db->exec('SET sql_mode = ""');
	
	//load parameters table
	$qry=$db->prepare("SELECT * FROM `tparameters`");
	$qry->execute();
	$rparameters=$qry->fetch();
	$qry->closeCursor();
		
	//locales
	$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	if($lang=='fr') {$_GET['lang'] = 'fr_FR';}
	else {$_GET['lang'] = 'en_US';}
	define('PROJECT_DIR', realpath('../'));
	define('LOCALE_DIR', PROJECT_DIR .'/locale');
	define('DEFAULT_LOCALE', '($_GET[lang]');
	require_once(__DIR__.'/../vendor/components/php-gettext/gettext.inc');
	$encoding = 'UTF-8';
	$locale = (isset($_GET['lang']))? $_GET['lang'] : DEFAULT_LOCALE;
	T_setlocale(LC_MESSAGES, $locale);
	T_bindtextdomain($_GET['lang'], LOCALE_DIR);
	T_bind_textdomain_codeset($_GET['lang'], $encoding);
	T_textdomain($_GET['lang']);

    //run on cli
    $_GET['action']='run';

    //display php.ini location
    echo "php.ini location : ".php_ini_loaded_file();

}elseif($rright['admin']) {
    //execution from app
    require_once('./vendor/autoload.php');
    require_once('./core/init_get.php');
    require_once('./core/functions.php');
}else{
    echo 'ERROR : forbidden page, use CLI';
	exit;
}

if($rparameters['azure_ad'])
{
    echo '
        <div class="page-header position-relative">
            <h1 class="page-title text-primary-m2">
                <i class="fa fa-sync"></i>   
                '.T_('Synchronisation').' : Entra ID > GestSup 
            </h1>
        </div>
        	
        <button onclick=\'window.location.href="index.php?page=admin&amp;subpage=user&amp;azure_ad=1&amp;action=simul"\' type="submit" class="btn btn-success">
            <i class="fa fa-flask"></i>
          '.T_('Lancer une simulation').'
        </button>
        <button onclick=\'window.location.href="index.php?page=admin&amp;subpage=user&amp;azure_ad=1&amp;action=run"\' type="submit" class="btn btn-warning">
            <i class="fa fa-bolt text-white"></i>
            <span class="text-white">'.T_('Lancer la synchronisation').'</span>
        </button>
        <button onclick=\'window.location.href="index.php?page=admin&subpage=user"\' type="submit" class="btn btn-primary btn-danger">
            <i class="fa fa-reply"></i>
            '.T_('Retour').'
        </button>	
       	
    ';
    if($_GET['action']=='simul' || $_GET['action']=='run' || php_sapi_name() == "cli"){
        if($rparameters['debug']) {echo '<br />DEBUG :<br />';}
        //for each tenant
        for($id = 1; $id <= $rparameters['azure_ad_tenant_number']; $id++) 
        {
            //get tenant parameters
            $qry=$db->prepare("SELECT * FROM `tentra_tenant` WHERE `id`=:id");
            $qry->execute(array('id' => $id));
            $tenant=$qry->fetch();
            $qry->closeCursor();
            if($rparameters['debug']) {echo '- check tenant_id '.$id.' '.$tenant['tenant_name'].'</br />';}
       
            //get access token
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
                echo DisplayMessage('error',T_("Erreur de génération du jeton d'accès locataire ").$id.'<br />'.$responseBodyAsString);
                LogIt('error','ERROR 38 : ENTRA ID , failed to generate access token tenant '.$id,$_SESSION['user_id']);
            } else {
                //fields list
                $qry_select = 'id,onPremisesSecurityIdentifier,onPremisesSamAccountName,onPremisesUserPrincipalName,givenName,surname,displayName,jobTitle,department,businessPhones,mobilePhone,mail,companyName,userPrincipalName ,streetAddress,streetAddress,postalCode,city,faxNumber,accountEnabled';
                if(!$tenant['group_filter'])
                {
                    //get Entra ID users
                    $graph = new Graph();
                    $graph->setApiVersion('beta');
                    $graph->setAccessToken($accessToken);
                    $url = '/users?$select='.$qry_select.'&filter=&$top=100';
                    $qryUsers = $graph->createCollectionRequest("GET", $url)->setReturnType(Model\User::class);
                    $lstUsers = array();
                    while(!$qryUsers->isEnd()) {
                    $result = $qryUsers->getPage();
                        if (is_array($result)) {
                            $lstUsers = array_merge($lstUsers, $result);
                        }
                    }
                } else { //group filter case
                    $lstGroups = array();
                    //get all groups id
                    if(preg_match('/;/',$tenant['group_filter']))
                    {
                        if($rparameters['debug']) {echo '- multi group case';}
                        $groups=explode(';',$tenant['group_filter']);
                        foreach($groups as $group)
                        {
                            //if($rparameters['debug']) {echo '<br /> add grp '.$group;}
                            //graph call
                            $graph = new Graph();
                            $graph->setApiVersion('beta');
                            $graph->setAccessToken($accessToken);
                            $url = '/groups?$select=id,displayName&filter=startswith(displayName,\''.$group.'\')&$top=100';
                            $qryGroups = $graph->createCollectionRequest("GET", $url)->setReturnType(Model\User::class);
                            while(!$qryGroups->isEnd()) {
                                $result = $qryGroups->getPage();
                                if (is_array($result)) {
                                    $lstGroups = array_merge($lstGroups, $result);
                                }
                            }
                        }
                    } else { //get single group id
                        if($rparameters['debug']) {echo '- single group case';}
                        //graph call
                        $graph = new Graph();
                        $graph->setApiVersion('beta');
                        $graph->setAccessToken($accessToken);
                        $url = '/groups?$select=id,displayName&filter=startswith(displayName,\''.$tenant['group_filter'].'\')&$top=100';
                        $qryGroups = $graph->createCollectionRequest("GET", $url)->setReturnType(Model\User::class);
                        $lstGroups = array();
                        while(!$qryGroups->isEnd()) {
                            $result = $qryGroups->getPage();
                            if (is_array($result)) {
                                $lstGroups = array_merge($lstGroups, $result);
                            }
                        }
                    }

                    //display group list
                    if($rparameters['debug'])
                    {
                        echo '<br />Group list:<pre>';
                        var_dump($lstGroups);
                        echo '</pre>';
                    }

                    //list members of groups
                    $lstUsers = array();
                    foreach($lstGroups as $group)
                    {
                        //get group properties
                        $group = $group->getProperties();
                        //call graph
                        $graph = new Graph();
                        $graph->setApiVersion('beta');
                        $graph->setAccessToken($accessToken);
                        $url = '/groups/'.$group['id'].'/members';
                        $qryGroupMembers = $graph->createCollectionRequest("GET", $url)->setReturnType(Model\User::class);
                        while(!$qryGroupMembers->isEnd()) {
                            $result = $qryGroupMembers->getPage();
                            if (is_array($result)) {
                                $lstUsers = array_merge($lstUsers, $result);
                            }
                        }
                        
                    }

                    //display members of group
                    if($rparameters['debug'])
                    {
                        /*
                        echo 'Group members<pre>';
                        var_dump($lstUsers);
                        echo '</pre>';
                        */
                    }
                }
                $arrAzureUsers = array();
                foreach ($lstUsers as $gUser) {
                    $user = $gUser->getProperties();
                
                    //secure var
                    $user['onPremisesSamAccountName']=htmlspecialchars("$user[onPremisesSamAccountName]", ENT_QUOTES, 'UTF-8');
                    $user['onPremisesUserPrincipalName']=htmlspecialchars("$user[onPremisesUserPrincipalName]", ENT_QUOTES, 'UTF-8');
                    $user['surname']=htmlspecialchars("$user[surname]", ENT_QUOTES, 'UTF-8');
                    $user['givenName']=htmlspecialchars("$user[givenName]", ENT_QUOTES, 'UTF-8');
                    $user['displayName']=htmlspecialchars("$user[displayName]", ENT_QUOTES, 'UTF-8');
                    $user['userPrincipalName']=htmlspecialchars("$user[userPrincipalName]", ENT_QUOTES, 'UTF-8');
                    $user['mail']=htmlspecialchars("$user[mail]", ENT_QUOTES, 'UTF-8');
                    $user['businessPhones']=htmlspecialchars(implode(',',$user['businessPhones']), ENT_QUOTES, 'UTF-8');
                    $user['mobilePhone']=htmlspecialchars("$user[mobilePhone]", ENT_QUOTES, 'UTF-8');
                    $user['faxNumber']=htmlspecialchars("$user[faxNumber]", ENT_QUOTES, 'UTF-8');
                    $user['department']=htmlspecialchars("$user[department]", ENT_QUOTES, 'UTF-8');
                    $user['jobTitle']=htmlspecialchars("$user[jobTitle]", ENT_QUOTES, 'UTF-8');
                    $user['companyName']=htmlspecialchars("$user[companyName]", ENT_QUOTES, 'UTF-8');
                    $user['streetAddress']=htmlspecialchars("$user[streetAddress]", ENT_QUOTES, 'UTF-8');
                    $user['postalCode']=htmlspecialchars("$user[postalCode]", ENT_QUOTES, 'UTF-8');
                    $user['city']=htmlspecialchars("$user[city]", ENT_QUOTES, 'UTF-8');
                                
                    //var_dump($user); echo '<hr />'; //dump all data
                    $arrAzureUsers[$user['id']] = array(
                        'id' => $user['id'],
                        'op_sid' => $user['onPremisesSecurityIdentifier'],
                        'op_sam' => $user['onPremisesSamAccountName'],
                        'op_upn' => $user['onPremisesUserPrincipalName'],
                        'lastname' => $user['surname'],
                        'firstname' => $user['givenName'],
                        'displayname' => $user['displayName'],
                        'upn' => $user['userPrincipalName'],
                        'mail' => $user['mail'], 
                        'phone' => $user['businessPhones'],
                        'mobile' => $user['mobilePhone'],
                        'fax' => $user['faxNumber'], 
                        'service' => $user['department'],
                        'function' => $user['jobTitle'],
                        'company' => $user['companyName'],
                        'address1' => $user['streetAddress'],
                        'zip' => $user['postalCode'],
                        'city' => $user['city'],
                        'enable' => $user['accountEnabled'],
                    );
                }
                //var_dump($arrAzureUsers); echo '<hr />'; //dump all data
                if(!empty($arrAzureUsers))
                {
                    //count gestsup users
                    $qry=$db->prepare("SELECT COUNT(`id`) FROM `tusers` WHERE `disable`='0'");
                    $qry->execute();
                    $count_gs_user=$qry->fetch();
                    $qry->closeCursor();
            
                    //count users in directories
                    echo '<div class="mt-4"></div>';
                    echo '<h5><i class="fa fa-book text-success"></i> '.T_('Vérification des annuaires').' :</h5>';
                    echo '<ul>';
                        echo '<li>'.T_("ID locataire").' '.$tenant['id'].' : '.$tenant['tenant_name'].'</li>';
                        echo '<li>'.T_("Nombre d'utilisateurs trouvés dans Entra ID").' : '.count($arrAzureUsers).'</li>';
                        echo '<li>'.T_("Nombre d'utilisateurs trouvés dans GestSup").' : '.$count_gs_user[0].'</li>';
                        echo '<li>'.T_('Date').' : '.date('d/m/Y H:i:s').'</li>';
                    echo '</ul>';
                    echo '<h5><i class="fa fa-edit text-warning"></i> '.T_('Modifications à apporter dans GestSup').' : </h5>';
                    echo '<div class="ml-4">';
                        //init counter
                        $user_to_create=0;
                        $user_to_update=0;
                        $user_to_disable=0;
                        $user_to_enable=0;

                        //foreach Entra ID check if exist in GestSup
                        foreach ($arrAzureUsers as &$AzureUser) {
                            //define login field
                            if($rparameters['azure_ad_login_field']=='UserPrincipalName') {$azure_login=$AzureUser['upn'];}
                            if($rparameters['azure_ad_login_field']=='onPremisesSamAccountName') {$azure_login=$AzureUser['op_sam'];}
                            if($rparameters['azure_ad_login_field']=='onPremisesUserPrincipalName') {$azure_login=$AzureUser['op_upn'];}
                        
                            //check if user exist in GestSup database
                            $qry=$db->prepare("SELECT `id`,`login`,`ldap_sid`,`azure_ad_id`,`azure_ad_tenant_id`,`lastname`,`firstname`,`login`,`mail`,`phone`,`mobile`,`fax`,`function`,`company`,`address1`,`zip`,`city`,`disable` FROM `tusers` WHERE (`azure_ad_id`=:azure_ad_id OR `ldap_sid`=:ldap_sid)");
                            $qry->execute(array('azure_ad_id' => $AzureUser['id'],'ldap_sid' => $AzureUser['op_sid']));
                            $GestsupUser=$qry->fetch();
                            $qry->closeCursor();

                            //if GestSup account exist check create it
                            if(!isset($GestsupUser['id'])) 
                            {
                                $user_to_create++;
                                echo '<i class="fa fa-plus-circle text-success"></i> <i class="fa fa-user text-success"></i> '.T_("Ajout de l'utilisateur").' '.$AzureUser['upn'].' <br />';
                                if($_GET['action']=='run')
                                {
                                    //use upn for login if empty
                                    if(!$azure_login) {$azure_login=$AzureUser['upn'];}

                                    //use displayname for firstname and lastname are empty
                                    if(!$AzureUser['firstname'] && !$AzureUser['lastname'] && $AzureUser['displayname']){$AzureUser['lastname']=$AzureUser['displayname'];}

                                    //create user
                                    $qry=$db->prepare("INSERT INTO `tusers` (
                                        `login`,
                                        `lastname`,
                                        `firstname`,
                                        `mail`,
                                        `phone`,
                                        `mobile`,
                                        `fax`,
                                        `function`,
                                        `address1`,
                                        `zip`,
                                        `city`,
                                        `ldap_sid`,
                                        `azure_ad_id`,
                                        `azure_ad_tenant_id`,
                                        `profile`
                                        ) VALUES (
                                            :login,
                                            :lastname,
                                            :firstname,
                                            :mail,
                                            :phone,
                                            :mobile,
                                            :fax,
                                            :function,
                                            :address1,
                                            :zip,
                                            :city,
                                            :ldap_sid,
                                            :azure_ad_id,
                                            :azure_ad_tenant_id,
                                            '2'
                                        )");
                                    $qry->execute(array(
                                        'login' => "$azure_login",
                                        'lastname' => "$AzureUser[lastname]",
                                        'firstname' => "$AzureUser[firstname]",
                                        'mail' => "$AzureUser[mail]",
                                        'phone' => "$AzureUser[phone]",
                                        'mobile' => "$AzureUser[mobile]",
                                        'fax' => "$AzureUser[fax]",
                                        'function' => "$AzureUser[function]",
                                        'address1' => "$AzureUser[address1]",
                                        'zip' => "$AzureUser[zip]",
                                        'city' => "$AzureUser[city]",
                                        'ldap_sid' => "$AzureUser[op_sid]",
                                        'azure_ad_id' => "$AzureUser[id]",
                                        'azure_ad_tenant_id' => "$tenant[tenant_id]"
                                    ));
                                    LogIt('azure_ad','create user '.$AzureUser['upn'],0);
                                }

                                //add service association
                                if($AzureUser['service'])
                                {
                                    //check is Entra ID service exist in GS db
                                    $qry=$db->prepare("SELECT `id` FROM `tservices` WHERE `name`=:name");
                                    $qry->execute(array('name' => "$AzureUser[service]"));
                                    $service=$qry->fetch();
                                    $qry->closeCursor();
                                    //Entra ID service not exist in GS db
                                    if(empty($service['id'])) {
                                        echo '<i class="fa fa fa-plus-circle text-success"></i> <i class="fa fa fa-users text-success"></i> '.T_('Création du service').' '.$AzureUser['service'].'<br />';
                                        echo '<i class="fa fa fa-sync text-warning"></i> <i class="fa fa fa-users text-warning"></i> '.T_('Association du service').' '.$AzureUser['service'].' '.T_("avec l'utilisateur").' '.$AzureUser['upn'].'<br />';
                                        if($_GET['action']=='run') 
                                        {
                                            //create service in GS DB
                                            $qry=$db->prepare("INSERT INTO `tservices` (`name`) VALUES (:name)");
                                            $qry->execute(array('name' => $AzureUser['service']));
                                            $service_id=$db->lastInsertId();

                                            //add association
                                            $qry=$db->prepare("INSERT INTO tusers_services (user_id,service_id) VALUES ((SELECT MAX(id) FROM tusers WHERE azure_ad_id=:azure_ad_id),:service_id)");
                                            $qry->execute(array('azure_ad_id' => $AzureUser['id'],'service_id' => $service_id));
                                        }
                                    //Entra ID service already exist in GS DB
                                    } else {
                                        //check if exist an association with current GS user and service.
                                        $qry=$db->prepare("SELECT `id`,`user_id` FROM `tusers_services` WHERE user_id IN (SELECT id FROM tusers WHERE azure_ad_id=:azure_ad_id) AND service_id=:service_id");
                                        $qry->execute(array('azure_ad_id' => $AzureUser['id'],'service_id' => $service['id']));
                                        $row=$qry->fetch();
                                        $qry->closeCursor();
                                        if(empty($row))//if no association found create it
                                        {
                                            echo '<i class="fa fa-sync text-warning"></i> <i class="fa fa-users text-warning"></i> '.T_('Mise à jour du service').' '.$AzureUser['service'].' pour '.$AzureUser['upn'].'<br />';
                                            //delete old association
                                            $qry=$db->prepare("DELETE FROM tusers_services WHERE user_id IN (SELECT id FROM tusers WHERE azure_ad_id=:azure_ad_id)");
                                            $qry->execute(array('azure_ad_id' => $AzureUser['id']));
                                            //create association
                                            if($_GET['action']=='run') 
                                            {
                                                $qry=$db->prepare("INSERT INTO tusers_services (user_id,service_id) VALUES ((SELECT MAX(id) FROM tusers WHERE azure_ad_id=:azure_ad_id),:service_id)");
                                                $qry->execute(array('azure_ad_id' => $AzureUser['id'],'service_id' => $service['id']));
                                            }
                                        } 
                                    }
                                }
                                //add company association
                                if($AzureUser['company'])
                                {
                                    //check existing company
                                    $qry=$db->prepare("SELECT `id` FROM `tcompany` WHERE `name`=:name");
                                    $qry->execute(array('name' => $AzureUser['company']));
                                    $company=$qry->fetch();
                                    $qry->closeCursor();
                                    //create company
                                    if(empty($company['id'])) 
                                    {
                                        echo '<i class="fa fa fa-plus-circle text-success"></i> <i class="fa fa fa-building text-success"></i> '.T_('Création de la société').' '.$AzureUser['company'].'<br />';
                                        echo '<i class="fa fa-sync text-warning"></i> <i class="fa fa-building text-warning"></i> '.T_('Mise à jour de la société').' '.$AzureUser['company'].' pour '.$AzureUser['upn'].' <br />';
                                        if($_GET['action']=='run')
                                        {   
                                            //create company
                                            $qry=$db->prepare("INSERT INTO `tcompany` (`name`) VALUES (:name)");
                                            $qry->execute(array('name' => $AzureUser['company']));
                                            $company_id=$db->lastInsertId();
                                            //update association
                                            $qry=$db->prepare("UPDATE `tusers` SET `company`=:company WHERE `azure_ad_id`=:azure_ad_id");
                                            $qry->execute(array('company' => $company_id,'azure_ad_id' => $AzureUser['id']));
                                        }
                                    } else { //update company association
                                        echo '<i class="fa fa-sync text-warning"></i> <i class="fa fa-building text-warning"></i> '.T_('Mise à jour de la société').' '.$AzureUser['company'].' pour '.$AzureUser['upn'].'<br />';
                                        if($_GET['action']=='run')
                                        {
                                            //update association
                                            $qry=$db->prepare("UPDATE `tusers` SET `company`=:company WHERE `azure_ad_id`=:azure_ad_id");
                                            $qry->execute(array('company' => $company['id'],'azure_ad_id' => $AzureUser['id']));
                                        }
                                    }
                                }

                                //create disabled user
                                if(!$AzureUser['enable'])
                                {
                                    $qry=$db->prepare("UPDATE `tusers` SET `disable`=1 WHERE `azure_ad_id`=:azure_ad_id");
                                    $qry->execute(array('azure_ad_id' => $AzureUser['id']));
                                }

                            } else {  //if GestSup account exist, update properties
                                $update=0;

                                //update azure id, if SID and no userid (Migration from local on azure AD)
                                if($GestsupUser['ldap_sid'] && !$GestsupUser['azure_ad_id'] && $AzureUser['id'])
                                {
                                    $qry=$db->prepare("UPDATE `tusers` SET `azure_ad_id`=:azure_ad_id WHERE `ldap_sid`=:ldap_sid");
                                    $qry->execute(array('azure_ad_id' => $AzureUser['id'],'ldap_sid' => $GestsupUser['ldap_sid']));
                                }
                            
                                //check difference between Azure user an GestSup user
                                if($AzureUser['firstname'] && $AzureUser['firstname']!=$GestsupUser['firstname'])
                                {
                                    $update=1;
                                    echo '<i class="fa fa-sync text-warning"></i> <i class="fa fa-user text-warning"></i> '.T_("Mise à jour du prénom").' pour '.$AzureUser['upn'].' <br />';
                                    if($_GET['action']=='run')
                                    {
                                        $qry=$db->prepare("UPDATE `tusers` SET `firstname`=:firstname WHERE `azure_ad_id`=:azure_ad_id");
                                        $qry->execute(array('firstname' => $AzureUser['firstname'],'azure_ad_id' => $AzureUser['id']));
                                    }
                                }
                                if($AzureUser['lastname'] && $AzureUser['lastname']!=$GestsupUser['lastname'])
                                {
                                    $update=1;
                                    echo '<i class="fa fa-sync text-warning"></i> <i class="fa fa-user text-warning"></i> '.T_("Mise à jour du nom").' pour '.$AzureUser['upn'].' <br />';
                                    if($_GET['action']=='run')
                                    {
                                        $qry=$db->prepare("UPDATE `tusers` SET `lastname`=:lastname WHERE `azure_ad_id`=:azure_ad_id");
                                        $qry->execute(array('lastname' => $AzureUser['lastname'],'azure_ad_id' => $AzureUser['id']));
                                    }
                                }
                                if($AzureUser['mail'] && $AzureUser['mail']!=$GestsupUser['mail'])
                                {
                                    $update=1;
                                    echo '<i class="fa fa-sync text-warning"></i> <i class="fa fa-user text-warning"></i> '.T_("Mise à jour du mail").' pour '.$AzureUser['upn'].' <br />';
                                    if($_GET['action']=='run')
                                    {
                                        $qry=$db->prepare("UPDATE `tusers` SET `mail`=:mail WHERE `azure_ad_id`=:azure_ad_id");
                                        $qry->execute(array('mail' => $AzureUser['mail'],'azure_ad_id' => $AzureUser['id']));
                                    }
                                }
                                if($AzureUser['phone'] && $AzureUser['phone']!=$GestsupUser['phone'])
                                {
                                    $update=1;
                                    echo '<i class="fa fa-sync text-warning"></i> <i class="fa fa-user text-warning"></i> '.T_("Mise à jour du téléphone").' pour '.$AzureUser['upn'].' <br />';
                                    if($_GET['action']=='run')
                                    {
                                        $qry=$db->prepare("UPDATE `tusers` SET `phone`=:phone WHERE `azure_ad_id`=:azure_ad_id");
                                        $qry->execute(array('phone' => $AzureUser['phone'],'azure_ad_id' => $AzureUser['id']));
                                    }
                                }
                                if($AzureUser['mobile'] && $AzureUser['mobile']!=$GestsupUser['mobile'])
                                {
                                    $update=1;
                                    echo '<i class="fa fa-sync text-warning"></i> <i class="fa fa-user text-warning"></i> '.T_("Mise à jour du mobile").' pour '.$AzureUser['upn'].' <br />';
                                    if($_GET['action']=='run')
                                    {
                                        $qry=$db->prepare("UPDATE `tusers` SET `mobile`=:mobile WHERE `azure_ad_id`=:azure_ad_id");
                                        $qry->execute(array('mobile' => $AzureUser['mobile'],'azure_ad_id' => $AzureUser['id']));
                                    }
                                }
                                if($AzureUser['fax'] && $AzureUser['fax']!=$GestsupUser['fax'])
                                {
                                    $update=1;
                                    echo '<i class="fa fa-sync text-warning"></i> <i class="fa fa-user text-warning"></i> '.T_("Mise à jour du fax").' pour '.$AzureUser['upn'].' <br />';
                                    if($_GET['action']=='run')
                                    {
                                        $qry=$db->prepare("UPDATE `tusers` SET `fax`=:fax WHERE `azure_ad_id`=:azure_ad_id");
                                        $qry->execute(array('fax' => $AzureUser['fax'],'azure_ad_id' => $AzureUser['id']));
                                    }
                                }
                                if($AzureUser['function'] && $AzureUser['function']!=$GestsupUser['function'])
                                {
                                    $update=1;
                                    echo '<i class="fa fa-sync text-warning"></i> <i class="fa fa-user text-warning"></i> '.T_("Mise à jour de la fonction").' pour '.$AzureUser['upn'].' <br />';
                                    if($_GET['action']=='run')
                                    {
                                        $qry=$db->prepare("UPDATE `tusers` SET `function`=:function WHERE `azure_ad_id`=:azure_ad_id");
                                        $qry->execute(array('function' => $AzureUser['function'],'azure_ad_id' => $AzureUser['id']));
                                    }
                                }
                                if($AzureUser['address1'] && $AzureUser['address1']!=$GestsupUser['address1'])
                                {
                                    $update=1;
                                    echo '<i class="fa fa-sync text-warning"></i> <i class="fa fa-user text-warning"></i> '.T_("Mise à jour de l'adresse").' pour '.$AzureUser['upn'].' <br />';
                                    if($_GET['action']=='run')
                                    {
                                        $qry=$db->prepare("UPDATE `tusers` SET `address1`=:address1 WHERE `azure_ad_id`=:azure_ad_id");
                                        $qry->execute(array('address1' => $AzureUser['address1'],'azure_ad_id' => $AzureUser['id']));
                                    }
                                }
                                if($AzureUser['city'] && $AzureUser['city']!=$GestsupUser['city'])
                                {
                                    $update=1;
                                    echo '<i class="fa fa-sync text-warning"></i> <i class="fa fa-user text-warning"></i> '.T_("Mise à jour de la ville").' pour '.$AzureUser['upn'].' <br />';
                                    if($_GET['action']=='run')
                                    {
                                        $qry=$db->prepare("UPDATE `tusers` SET `city`=:city WHERE `azure_ad_id`=:azure_ad_id");
                                        $qry->execute(array('city' => $AzureUser['city'],'azure_ad_id' => $AzureUser['id']));
                                    }
                                }
                                if($AzureUser['zip'] && $AzureUser['zip']!=$GestsupUser['zip'])
                                {
                                    $update=1;
                                    echo '<i class="fa fa-sync text-warning"></i> <i class="fa fa-user text-warning"></i> '.T_("Mise à jour du code postal").' pour '.$AzureUser['upn'].' <br />';
                                    if($_GET['action']=='run')
                                    {
                                        $qry=$db->prepare("UPDATE `tusers` SET `zip`=:zip WHERE `azure_ad_id`=:azure_ad_id");
                                        $qry->execute(array('zip' => $AzureUser['zip'],'azure_ad_id' => $AzureUser['id']));
                                    }
                                }
                                //login update
                                if($azure_login && $azure_login!=$GestsupUser['login'])
                                {
                                    $update=1;
                                    echo '<i class="fa fa-sync text-warning"></i> <i class="fa fa-user text-warning"></i> '.T_("Mise à jour de l'identifiant").' '.$azure_login.' pour '.$AzureUser['upn'].' ('.$AzureUser['id'].') <br />';
                                    if($_GET['action']=='run')
                                    {
                                        $qry=$db->prepare("UPDATE `tusers` SET `login`=:login WHERE `azure_ad_id`=:azure_ad_id");
                                        $qry->execute(array('login' => $azure_login,'azure_ad_id' => $AzureUser['id']));
                                    }
                                }
                                //tenant id update
                                if($tenant['tenant_id'] && !$GestsupUser['azure_ad_tenant_id'])
                                {
                                    $update=1;
                                    echo '<i class="fa fa-sync text-warning"></i> <i class="fa fa-user text-warning"></i> '.T_("Mise à jour du locataire").' '.$tenant['tenant_id'].' pour '.$AzureUser['upn'].' ('.$AzureUser['id'].') <br />';
                                    if($_GET['action']=='run')
                                    {
                                        $qry=$db->prepare("UPDATE `tusers` SET `azure_ad_tenant_id`=:azure_ad_tenant_id WHERE `azure_ad_id`=:azure_ad_id");
                                        $qry->execute(array('azure_ad_tenant_id' => $tenant['tenant_id'],'azure_ad_id' => $AzureUser['id']));
                                    }
                                }
                                //service update
                                if($AzureUser['service'])
                                {
                                    //check is Entra ID service exist in GS db
                                    $qry=$db->prepare("SELECT `id` FROM `tservices` WHERE `name`=:name");
                                    $qry->execute(array('name' => "$AzureUser[service]"));
                                    $service=$qry->fetch();
                                    $qry->closeCursor();
                                    //Entra ID service not exist in GS db
                                    if(empty($service['id'])) {
                                        $update=1;
                                        echo '<i class="fa fa fa-plus-circle text-success"></i> <i class="fa fa fa-users text-success"></i> '.T_('Création du service').' '.$AzureUser['service'].'<br />';
                                        echo '<i class="fa fa fa-sync text-warning"></i> <i class="fa fa fa-users text-warning"></i> '.T_('Association du service').' '.$AzureUser['service'].' '.T_("avec l'utilisateur").' '.$AzureUser['upn'].'<br />';
                                        if($_GET['action']=='run') 
                                        {
                                            //create service in GS DB
                                            $qry=$db->prepare("INSERT INTO `tservices` (`name`) VALUES (:name)");
                                            $qry->execute(array('name' => $AzureUser['service']));
                                            $service_id=$db->lastInsertId();

                                            //delete old association
                                            $qry=$db->prepare("DELETE FROM tusers_services WHERE user_id IN (SELECT id FROM tusers WHERE azure_ad_id=:azure_ad_id)");
                                            $qry->execute(array('azure_ad_id' => $AzureUser['id']));

                                            //add association
                                            $qry=$db->prepare("INSERT INTO tusers_services (user_id,service_id) VALUES ((SELECT MAX(id) FROM tusers WHERE azure_ad_id=:azure_ad_id),:service_id)");
                                            $qry->execute(array('azure_ad_id' => $AzureUser['id'],'service_id' => $service_id));
                                        }
                                    //Entra ID service already exist in GS DB
                                    } else {
                                        //check if exist an association with current GS user and service.
                                        $qry=$db->prepare("SELECT `id`,`user_id` FROM `tusers_services` WHERE user_id IN (SELECT id FROM tusers WHERE azure_ad_id=:azure_ad_id) AND service_id=:service_id");
                                        $qry->execute(array('azure_ad_id' => $AzureUser['id'],'service_id' => $service['id']));
                                        $row=$qry->fetch();
                                        $qry->closeCursor();
                                        if(empty($row))//if no association found create it
                                        {
                                            $update=1;
                                            echo '<i class="fa fa-sync text-warning"></i> <i class="fa fa-users text-warning"></i> '.T_('Mise à jour du service').' '.$AzureUser['service'].' pour '.$AzureUser['upn'].'<br />';
                                            //delete old association
                                            $qry=$db->prepare("DELETE FROM tusers_services WHERE user_id IN (SELECT id FROM tusers WHERE azure_ad_id=:azure_ad_id)");
                                            $qry->execute(array('azure_ad_id' => $AzureUser['id']));
                                            //create association
                                            if($_GET['action']=='run') 
                                            {
                                                $qry=$db->prepare("INSERT INTO tusers_services (user_id,service_id) VALUES ((SELECT MAX(id) FROM tusers WHERE azure_ad_id=:azure_ad_id),:service_id)");
                                                $qry->execute(array('azure_ad_id' => $AzureUser['id'],'service_id' => $service['id']));
                                            }
                                        } 
                                    }
                                }

                                //company update
                                if($AzureUser['company'])
                                {
                                    //check existing company
                                    $qry=$db->prepare("SELECT `id` FROM `tcompany` WHERE `name`=:name");
                                    $qry->execute(array('name' => $AzureUser['company']));
                                    $company=$qry->fetch();
                                    $qry->closeCursor();
                                    //create company
                                    if(empty($company['id'])) 
                                    {
                                        $update=1;
                                        echo '<i class="fa fa fa-plus-circle text-success"></i> <i class="fa fa fa-building text-success"></i> '.T_('Création de la société').' '.$AzureUser['company'].'<br />';
                                        echo '<i class="fa fa-sync text-warning"></i> <i class="fa fa-building text-warning"></i> '.T_('Mise à jour de la société').' '.$AzureUser['company'].' pour '.$AzureUser['upn'].' <br />';
                                        if($_GET['action']=='run')
                                        {   
                                            //create company
                                            $qry=$db->prepare("INSERT INTO `tcompany` (`name`) VALUES (:name)");
                                            $qry->execute(array('name' => $AzureUser['company']));
                                            $company_id=$db->lastInsertId();
                                            //update association
                                            $qry=$db->prepare("UPDATE `tusers` SET `company`=:company WHERE `azure_ad_id`=:azure_ad_id");
                                            $qry->execute(array('company' => $company_id,'azure_ad_id' => $AzureUser['id']));
                                        }
                                    } else { //update company association
                                        if($company['id']!=$GestsupUser['company'])
                                        {
                                            $update=1;
                                            echo '<i class="fa fa-sync text-warning"></i> <i class="fa fa-building text-warning"></i> '.T_('Mise à jour de la société').' '.$AzureUser['company'].' pour '.$AzureUser['upn'].' <br />';
                                            if($_GET['action']=='run')
                                            {
                                                //update association
                                                $qry=$db->prepare("UPDATE `tusers` SET `company`=:company WHERE `azure_ad_id`=:azure_ad_id");
                                                $qry->execute(array('company' => $company['id'],'azure_ad_id' => $AzureUser['id']));
                                            }
                                        }
                                    }
                                }
                                //disable GestSup user if Entra ID user is disabled
                                if($rparameters['azure_ad_disable_user'] && !$AzureUser['enable'] && !$GestsupUser['disable'])
                                {
                                    $user_to_disable++;
                                    echo '<i class="fa fa-times-circle text-danger"></i> <i class="fa fa-user text-danger"></i> '.T_("Désactivation de l'utilisateur").' '.$AzureUser['upn'].' '.T_("car désactivé sur Entra ID et activé sur GestSup").'<br />';
                                    if($_GET['action']=='run')
                                    {
                                        //disable user
                                        $qry=$db->prepare("UPDATE `tusers` SET `disable`=1 WHERE `azure_ad_id`=:azure_ad_id");
                                        $qry->execute(array('azure_ad_id' => $AzureUser['id']));
                                        LogIt('azure_ad','disable user '.$AzureUser['upn'],0);
                                    }
                                }
                                //enable GestSup user if GestSup user id disabled and Entra ID is enable
                                if($rparameters['azure_ad_disable_user'] && $AzureUser['enable'] && $GestsupUser['disable'])
                                {
                                    $user_to_enable++;
                                    echo '<i class="fa fa-check-circle text-success"></i> <i class="fa fa-user text-success"></i> '.T_("Activation de l'utilisateur").' '.$AzureUser['upn'].' '.T_("car désactivé sur GestSup et activé sur Entra ID").'<br />';
                                    if($_GET['action']=='run')
                                    {
                                        //enable user
                                        $qry=$db->prepare("UPDATE `tusers` SET `disable`=0 WHERE `azure_ad_id`=:azure_ad_id");
                                        $qry->execute(array('azure_ad_id' => $AzureUser['id']));
                                        LogIt('azure_ad','enable user '.$AzureUser['upn'],0);
                                    }
                                }

                                //update user update counter
                                if($update) { 
                                    $user_to_update++;
                                    LogIt('azure_ad','update user '.$AzureUser['upn'],0);
                                }
                            }
                        }
                        //disable GestSup user if not in Entra ID and parameter enable
                        if($rparameters['azure_ad_disable_user'])
                        {
                            $qry=$db->prepare("SELECT `id`,`login`,`azure_ad_id`,`azure_ad_tenant_id`,`disable` FROM `tusers`");
                            $qry->execute();
                            while($GestsupUser=$qry->fetch()) 	
                            {
                                $find=0;
                                foreach ($arrAzureUsers as &$AzureUser) {
                                    if($GestsupUser['azure_ad_id']==$AzureUser['id']) {$find=1;}
                                }
                                if(!$find && !$GestsupUser['disable'] && $GestsupUser['login']!='admin' && $GestsupUser['azure_ad_tenant_id']==$tenant['tenant_id'])
                                {
                                    $user_to_disable++;
                                    echo '<i class="fa fa-times-circle text-danger"></i> <i class="fa fa-user text-danger"></i> '.T_("Désactivation de l'utilisateur").' <b>'.$GestsupUser['login'].'</b> '.T_("car activé sur GestSup et non présent sur Entra ID").'<br />';
                                    if($_GET['action']=='run')
                                    {
                                        $qry2=$db->prepare("UPDATE `tusers` SET `disable`=1 WHERE `id`=:id");
                                        $qry2->execute(array('id' => $GestsupUser['id']));
                                        LogIt('azure_ad','disable user '.$AzureUser['upn'],0);
                                    }
                                }
                            }
                            $qry->closeCursor();
                        }

                    echo '</div>';

                    if($user_to_create==0 && $user_to_update==0 && $user_to_enable==0 && $user_to_disable==0){echo DisplayMessage('success',T_('Les annuaires sont à jours'));}
                    //count users modification
                    if($_GET['action']=='simul')
                    {
                        echo '<hr />';
                        echo '<ul>';
                            echo '<li>'.T_("Nombre d'utilisateurs à créer dans GestSup").' : '.$user_to_create.'</li>';
                            echo '<li>'.T_("Nombre d'utilisateurs à mettre à jour dans GestSup").' : '.$user_to_update.'</li>';
                            echo '<li>'.T_("Nombre d'utilisateurs à activer dans GestSup").' : '.$user_to_enable.'</li>';
                            echo '<li>'.T_("Nombre d'utilisateurs à désactiver dans GestSup").' : '.$user_to_disable.'</li>';
                        echo '</ul>';
                    }elseif($_GET['action']=='run')
                    {
                        echo '<hr />';
                        echo '<ul>';
                            echo '<li>'.T_("Nombre d'utilisateurs crées dans GestSup").' : '.$user_to_create.'</li>';
                            echo '<li>'.T_("Nombre d'utilisateurs mis à jour dans GestSup").' : '.$user_to_update.'</li>';
                            echo '<li>'.T_("Nombre d'utilisateurs activés dans GestSup").' : '.$user_to_enable.'</li>';
                            echo '<li>'.T_("Nombre d'utilisateurs désactivés dans GestSup").' : '.$user_to_disable.'</li>';
                        echo '</ul>';
                    }
                } else {
                    echo DisplayMessage('error',T_("Aucun utilisateur trouvé sur Entra ID (tenant_id ".$rparameters['azure_ad_tenant_id'].")"));
                }
                //var_dump($arrAzureUsers);
            }
        }
    }
} else {
    echo DisplayMessage('error',T_("Le connecteur Entra ID est désactivé"));
}
?>