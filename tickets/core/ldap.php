<?php
################################################################################
# @Name : /core/ldap.php
# @Description : page to synchronize users from LDAP to GestSup
# @call : /admin/user.php
# @Author : Flox
# @Create : 15/10/2012
# @Update : 26/02/2024
# @Version : 3.2.49
################################################################################

//initialize variables
if(!isset($_POST['test_ldap'])) $_POST['test_ldap'] = '';
if(!isset($cnt_ldap)) $cnt_ldap= 0;
if(!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) $_SERVER['HTTP_ACCEPT_LANGUAGE']=0;
if(!isset($g_login)) $g_login='';
if(!isset($rright['admin'])) $rright['admin']='0';

//call via php cli on server
if(php_sapi_name() == "cli")
{
	//database connection
	require_once(__DIR__."/../connect.php");
	require_once(__DIR__."/../core/init_get.php");
	require_once(__DIR__."/../core/functions.php");
	
	//switch SQL MODE to allow empty values with latest version of MySQL
	$db->exec('SET sql_mode = ""');
	
	//load parameters table
	$qry=$db->prepare("SELECT * FROM `tparameters`");
	$qry->execute();
	$rparameters=$qry->fetch();
	$qry->closeCursor();
		
	//variable
	$_GET['ldap']='1';
	$_GET['action']='run';
	
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
} elseif($rright['admin']) {
	//execution from app
	require_once('./core/init_get.php');
	require_once('./core/functions.php');
} else {
	echo 'ERROR : forbidden page, use CLI';
	exit;
}

//special char db
$rparameters['ldap_url']=str_replace("&#039;","'",$rparameters['ldap_url']);

if(!isset($ldap_query)) $ldap_query = '';
if(!isset($find)) $find = '';
if(!isset($dcgen)) $dcgen = '';
if(!isset($find2_login)) $find2_login= '';
if(!isset($update)) $update= '';
if(!isset($find_dpt)) $find_dpt= '';
if(!isset($find_company)) $find_company= '';
if(!isset($samaccountname)) $samaccountname= '';
if(!isset($ldap_type)) $ldap_type= '';
if(!isset($ldap_auth)) $ldap_auth= '';
if(!isset($g_company)) $g_company= '';
if(!isset($g_mobile)) $g_mobile= '';
if(!isset($g_telephonenumber)) $g_telephonenumber= '';

if($rparameters['ldap'])
{
	//LDAP connection parameters
	$user=$rparameters['ldap_user']; 
	if(preg_match('/gs_en/',$rparameters['ldap_password'])) {$rparameters['ldap_password']=gs_crypt($rparameters['ldap_password'], 'd' , $rparameters['server_private_key']);}
	$password=$rparameters['ldap_password']; 
	$domain=$rparameters['ldap_domain'];
	if($rparameters['ldap_port']==636) {
		putenv('LDAPTLS_REQCERT=never') or die('Failed to setup the env'); //enable AD self sign cert
		putenv('TLS_REQCERT=never') or die('Failed to setup the env'); //enable AD self sign cert
		$hostname='ldaps://'.$rparameters['ldap_server'];
	} else {
		$hostname=$rparameters['ldap_server'];
	}

	//generate DC Chain from domain parameter
	$dcpart=explode(".",$domain);
	$i=0;
	while($i<count($dcpart)) {
		$dcgen="$dcgen,dc=$dcpart[$i]";
		$i++;
	}

	
	//LDAP URL for users emplacement
	$ldap_url="$rparameters[ldap_url]$dcgen";

	//display head title
	if($rparameters['ldap_type']==0) {$ldap_type='Active Directory';}
	if($rparameters['ldap_type']==1) {$ldap_type='OpenLDAP';}
	if($rparameters['ldap_type']==3) {$ldap_type='Samba4';}
	if($rparameters['ldap_type']==4) {$ldap_type='Kwartz';}
	
	if($_GET['subpage']=='user')
	{
		echo '
		<div class="page-header position-relative">
			<h1 class="page-title text-primary-m2">
				<i class="fa fa-sync"></i>   
				'.T_('Synchronisation').' : '.$ldap_type.' > GestSup 
			</h1>
		</div>';
	}
	if(($_GET['action']=='simul') || ($_GET['action']=='run') || ($_GET['ldaptest']==1) || ($_GET['ldap']==1) || ($ldap_auth==1))
	{
		//LDAP connect
		if(phpversion()>'8.3.0')
		{
			if($rparameters['ldap_port']=='389')
			{
				$ldap = ldap_connect('ldap://'.$rparameters['ldap_server'].':'.$rparameters['ldap_port']) or die("Unable to connect to LDAP server"); 
			} else {
				$ldap = ldap_connect('ldaps://'.$rparameters['ldap_server'].'/') or die("Unable to connect to LDAP server");
			}
		} else {
			$ldap = ldap_connect($hostname,$rparameters['ldap_port']) or die("Unable to connect to LDAP server");
		}
		
		ldap_set_option($ldap, LDAP_OPT_NETWORK_TIMEOUT, 1);
		ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
		//check LDAP type for bind
		if($rparameters['ldap_type']==0 || $rparameters['ldap_type']==3) {
			$ldapbind = ldap_bind($ldap, "$user@$domain", $password);
		}elseif($rparameters['ldap_type']==4) { //Kwartz
			$ldapbind = ldap_bind($ldap, "uid=$user,ou=Users$domain", $password);
		} else {
			$ldapbind = ldap_bind($ldap, "cn=$user$dcgen", $password);
		}	
		//check ldap authentication
		if($ldapbind) {
			$ldap_connection=DisplayMessage('success',T_('Connecteur LDAP opérationnel'));
		} else {
			$ldap_connection=DisplayMessage('error',T_('Le connecteur LDAP ne fonctionne pas vérifier vos paramètres').' ('.ldap_error($ldap).')');
			//log
			if($rparameters['log'])
			{
				LogIt('error', "ERROR 25 : LDAP, connexion failed ".ldap_error($ldap),$_SESSION['user_id']);
			}
		}
		if($ldapbind) 
		{
			if(($_GET['action']=='simul') || ($_GET['action']=='run')) 
			{		
					$list_dn = preg_split("/;/",$rparameters['ldap_url']);
					$data = array();
					$data_temp = array();
					foreach ($list_dn as $value) {
						$ldap_url="$value$dcgen";
						
						//change query filter for OpenLDAP or AD
						if($rparameters['ldap_type']==0 || $rparameters['ldap_type']==3) {$filter="(&(objectClass=user)(objectCategory=person)(cn=*))";} else {$filter="(uid=*)";}	
						$pagesize = 100;
						$cookie = '';
						$justthese = array('samaccountname', 'userprincipalname', 'objectguid', 'objectsid', 'uid', 'entryuuid', 'useraccountcontrol', 'givenname', 'sn', 'telephonenumber', 'homePhone', 'mobile', 'vvdpostetel', 'streetaddress', 'postalcode', 'l', 'mail', 'company', 'facsimiletelephonenumber', 'title', 'department', 'manager');
						//check phpversion for ldap_search compatibility #5060
						$phpversion=phpversion();
						if($phpversion<'7.3.0') {
							do {
								ldap_control_paged_result($ldap, $pagesize, true, $cookie);
								$query  = ldap_search($ldap, $ldap_url, $filter, $justthese);
								$data_temp = ldap_get_entries($ldap, $query);
								$data = array_merge($data, $data_temp);
								//count LDAP number of users
								$cnt_ldap += @ldap_count_entries($ldap, $query);
								ldap_control_paged_result_response($ldap, $query, $cookie);
							} while($cookie !== null && $cookie != '');
						} else {
							do {
								$result = ldap_search(
									$ldap, $ldap_url, $filter, $justthese, 0, 0, 0, LDAP_DEREF_NEVER,
									[['oid' => LDAP_CONTROL_PAGEDRESULTS, 'value' => ['size' => $pagesize, 'cookie' => $cookie]]]
								);
								ldap_parse_result($ldap, $result, $errcode , $matcheddn , $errmsg , $referrals, $controls);
								$data_temp = ldap_get_entries($ldap, $result);
								$data = array_merge($data, $data_temp);
								$cnt_ldap += @ldap_count_entries($ldap, $result);
								if (isset($controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'])) {
									$cookie = $controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'];
								} else {
									$cookie = '';
								}
							} while (!empty($cookie));
						}
					}
						
					//count GestSup users
					$qry=$db->prepare("SELECT COUNT(*) FROM `tusers` WHERE disable='0'");
					$qry->execute();
					$cnt_gestsup=$qry->fetch();
					$qry->closeCursor();
					
					$sync_date=date('d/m/Y H:i:s');
					
					echo '<i class="fa fa-book text-primary-m2"></i> <b>'.T_('Vérification des Annuaires').'  :</b><br />';
					echo '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-circle text-success"></i> '.T_("Nombre d'utilisateurs trouvés dans l'annuaire").' '.$ldap_type.' : '.$cnt_ldap.'<br />';
					echo '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-circle text-success"></i> '.T_("Nombre d'utilisateurs actif trouvés dans GestSup").' : '.$cnt_gestsup[0].'<br />';
					echo '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-circle text-success"></i> '.T_('Date').' : '.$sync_date.'<br /><br />';
					echo '<i class="fa fa-edit text-warning"></i> <b>'.T_('Modifications à apporter dans GestSup').' :</b><br />';
					
					//init counter
					$cnt_maj=0;
					$cnt_create=0;
					$cnt_disable=0;
					$cnt_enable=0;
					
					//display all data for debug
					/*
					echo '<pre>';
					print_r($data);
					echo '</pre>';
					*/
					
					//for each LDAP user 
					for ($i=0; $i < $cnt_ldap; $i++) 
					{
						//Initialize variable for empty data
						if(!isset($data[$i]['samaccountname'][0])) $data[$i]['samaccountname'][0] = '';
						if(!isset($data[$i]['userprincipalname'][0])) $data[$i]['userprincipalname'][0] = '';
						if(!isset($data[$i]['objectguid'][0])) $data[$i]['objectguid'][0] = '';
						if(!isset($data[$i]['objectsid'][0])) $data[$i]['objectsid'][0] = '';
						if(!isset($data[$i]['uid'][0])) $data[$i]['uid'][0] = ''; //OpenLDAP object only
						if(!isset($data[$i]['entryuuid'][0])) $data[$i]['entryuuid'][0] = ''; //OpenLDAP object only
						if(!isset($data[$i]['useraccountcontrol'][0])) $data[$i]['useraccountcontrol'][0] = '';
						if(!isset($data[$i]['givenname'][0])) $data[$i]['givenname'][0] = '';
						if(!isset($data[$i]['sn'][0])) $data[$i]['sn'][0] = '';
						if(!isset($data[$i]['telephonenumber'][0])) $data[$i]['telephonenumber'][0] = '';
						if(!isset($data[$i]['homePhone'][0])) $data[$i]['homePhone'][0] = '';
						if(!isset($data[$i]['mobile'][0])) $data[$i]['mobile'][0] = '';
						if(!isset($data[$i]['vvdpostetel'][0])) $data[$i]['vvdpostetel'][0] = '';
						if(!isset($data[$i]['streetaddress'][0])) $data[$i]['streetaddress'][0] = '';
						if(!isset($data[$i]['postalcode'][0])) $data[$i]['postalcode'][0] = '';
						if(!isset($data[$i]['l'][0])) $data[$i]['l'][0] = '';
						if(!isset($data[$i]['mail'][0])) $data[$i]['mail'][0] = '';
						if(!isset($data[$i]['company'][0])) $data[$i]['company'][0] = '';
						if(!isset($data[$i]['facsimiletelephonenumber'][0])) $data[$i]['facsimiletelephonenumber'][0] = '';
						if(!isset($data[$i]['title'][0])) $data[$i]['title'][0] = '';
						if(!isset($data[$i]['department'][0])) $data[$i]['department'][0] = '';					
						if(!isset($data[$i]['manager'][0])) $data[$i]['manager'][0] = '';					
																																										
						$UPN=$data[$i]['userprincipalname'][0];
						$UAC=$data[$i]['useraccountcontrol'][0];
						$GUID=$data[$i]['objectguid'][0];
						$GUID=unpack("H*hex",$data[$i]['objectguid'][0]);
						$GUID=$GUID['hex'];
						if($rparameters['ldap_type']!=1) {$SID=SIDtoString($data[$i]['objectsid'][0]);} else {$SID=$data[$i]['objectsid'][0];}
						$entryuuid=$data[$i]['entryuuid'][0]; 
						$givenname=$data[$i]['givenname'][0];
						$sn=$data[$i]['sn'][0];
						$telephonenumber=$data[$i]['telephonenumber'][0];  
						$homephone=$data[$i]['homePhone'][0];  
						$mobile=$data[$i]['mobile'][0];  
						$vvdpostetel=$data[$i]['vvdpostetel'][0];  
						$streetaddress=$data[$i]['streetaddress'][0];  
						$postalcode=$data[$i]['postalcode'][0]; 
						$l=$data[$i]['l'][0]; 
						$mail=$data[$i]['mail'][0];
						$company=$data[$i]['company'][0]; 
						$fax=$data[$i]['facsimiletelephonenumber'][0]; 
						$title=$data[$i]['title'][0]; 
						$department=$data[$i]['department'][0];

						//get specific fields from Windows AD or Samba4 or OpenLDAP
						if($rparameters['ldap_type']==0 || $rparameters['ldap_type']==3) {
							$samaccountname=$data[$i]['samaccountname'][0];
							$ldap_guid=$GUID;
						} else {
							$samaccountname=$data[$i]['uid'][0];
							$ldap_guid=$entryuuid;
						}
						
						//remove special characters
						$UPN=str_replace(array('�','','','�ַ','M틣','˃','`'), '', $UPN); 
						$UAC=str_replace(array('�','','','�ַ','M틣','˃','`'), '', $UAC); 
						$ldap_guid=str_replace(array('�','','','�ַ','M틣','˃','`'), '', $ldap_guid); 
																								
						$givenname=str_replace (array('�','','','�ַ','M틣','˃','`'), '', $givenname); 
						$sn=str_replace (array('�','','','�ַ','M틣','˃','`'), '', $sn); 
						$telephonenumber=str_replace (array('�','','','�ַ','M틣','˃','`'), '', $telephonenumber); 
						$homephone=str_replace (array('�','','','�ַ','M틣','˃','`'), '', $homephone); 
						$mobile=str_replace (array('�','','','�ַ','M틣','˃','`'), '', $mobile); 
						$vvdpostetel=str_replace (array('�','','','�ַ','M틣','˃','`'), '', $vvdpostetel); 
						$streetaddress=str_replace (array('�','','','�ַ','M틣','˃','`'), '', $streetaddress); 
						$postalcode=str_replace (array('�','','','�ַ','M틣','˃','`'), '', $postalcode); 
						$l=str_replace (array('�','','','�ַ','M틣','˃','`'), '', $l); 
						$mail=str_replace (array('�','','','�ַ','M틣','˃','`'), '', $mail); 
						$company=str_replace(array('�','','','�ַ','M틣','˃','`'), '', $company); 
						$fax=str_replace(array('�','','','�ַ','M틣','˃','`'), '', $fax); 
						$title=str_replace(array('�','','','�ַ','M틣','˃','`'), '', $title); 
						$department=str_replace(array('�','','','�ַ','M틣','˃','`'), '', $department); 

						if($vvdpostetel) {$mobile=$vvdpostetel;}
						
						//define login field
						if($rparameters['ldap_login_field']=='UserPrincipalName' && $UPN){$LDAP_login=$UPN;} else {$LDAP_login=$samaccountname;}
						if($rparameters['debug']) echo "[DEBUG MODE] - LDAP_SamAccountName=$samaccountname LDAP_UPN=$UPN LDAP_login=$LDAP_login ldap_guid=$ldap_guid LDAP_UAC=$UAC LDAP_company=$company LDAP_department=$department LDAP_telephonenumber=$telephonenumber LDAP_mobile=$mobile LDAP_homephone=$homephone";
						
						////check if account not exist in GestSup user database
						//1st check login
						$find_guid=0;
						$qry=$db->prepare("SELECT `id`,`login`,`firstname`,`lastname`, `disable`,`mail`, `phone`,`mobile`,`address1`,`zip`,`city`,`company`,`fax`,`function`,`ldap_guid`,`ldap_sid` FROM `tusers`");
						$qry->execute();
						while($row=$qry->fetch()) 
						{
							//update ldap guid for old user
							if(strtolower($LDAP_login)==strtolower($row['login']) && !$row['ldap_guid'] && $ldap_guid) 
							{
								$qry2=$db->prepare("UPDATE `tusers` SET `ldap_guid`=:ldap_guid WHERE `id`=:id");
								$qry2->execute(array('ldap_guid' => $ldap_guid,'id' => $row['id']));
								$g_guid=$ldap_guid;
							} else {$g_guid=$row['ldap_guid'];}
							//update ldap sid for old user
							if(strtolower($LDAP_login)==strtolower($row['login']) && !$row['ldap_sid'] && $SID) 
							{
								$qry2=$db->prepare("UPDATE `tusers` SET `ldap_sid`=:ldap_sid WHERE `id`=:id");
								$qry2->execute(array('ldap_sid' => $SID,'id' => $row['id']));
							}
							//update guid if empty and sid
							if($row['ldap_sid']==$SID && !$row['ldap_guid'] && $ldap_guid) 
							{
								$qry2=$db->prepare("UPDATE `tusers` SET `ldap_guid`=:ldap_guid WHERE `ldap_sid`=:ldap_sid");
								$qry2->execute(array('ldap_guid' => $ldap_guid,'ldap_sid' => $SID));
								$g_guid=$ldap_guid;
							}
							
							if($ldap_guid==$g_guid)
							{
								//get user data from GS db
								$g_login=$row['login'];
								$g_firstname=$row['firstname'];
								$g_lastname=$row['lastname'];
								$g_disable=$row['disable'];
								$g_mail=$row['mail'];
								$g_telephonenumber=$row['phone'];
								$g_homephone=$row['phone'];
								$g_mobile=$row['mobile'];
								$g_streetaddress=$row['address1'];
								$g_postalcode=$row['zip'];
								$g_l=$row['city'];
								$g_company=$row['company'];
								$g_fax=$row['fax'];
								$g_title=$row['function'];
								$find_guid=$ldap_guid;
								break;
							}
						}
						$qry->closeCursor();
						if($rparameters['debug']) echo "<b>|</b> GS_login=$g_login GS_company=$g_company GS_mobile=$g_mobile GS_telephonenumber=$g_telephonenumber find_guid=$find_guid ldap_sid=$SID <br />";
						if($find_guid!="0")
						{	
							////update exist account
							if(($UAC=='66050' || $UAC=='514' || $UAC=='546' || $UAC=='66082') && ($g_disable==0)) //66050=Disabled Password Doesn't Expire, 514=Disabled Account, 546=Disabled Password Not Require, 66082=Disabled Password Doesn't Expire & Not Require
							{
								//disable GestSup account if AD user is disabled
								$cnt_disable=$cnt_disable+1;
								if($_GET['action']=='run') {
									echo '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-times-circle text-danger"></i><font class="text-danger"> '.T_('Utilisateur').' <b>'.$givenname.' '.$sn.'</b> ('.$LDAP_login.'), '.T_('désactivé').'.</font><br />';
									$qry=$db->prepare("UPDATE `tusers` SET `disable`=1 WHERE `ldap_guid`=:ldap_guid");
									$qry->execute(array('ldap_guid' => $find_guid));
								} else {
									echo '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-times-circle text-danger"></i><font class="text-danger"> '.T_("Désactivation de l'utilisateur").' <b>'.$givenname.' '.$sn.'</b> ('.$LDAP_login.'). <span style="font-size: x-small;">'.T_('Raison').': '.T_("Utilisateur désactivé").'.</span></font><br />';
								}
							}elseif((($rparameters['ldap_type']==0 || $rparameters['ldap_type']==3) && ($UAC=='512' || $UAC=='544' || $UAC=='66048' || $UAC=='66080')) || $rparameters['ldap_type']==1) { //512=Enabled Account, 544=Enabled Password Not Require, 66048=Enabled Password Doesn't Expire, 66080=Enabled Password Doesn't Expire & Not Require
								if($g_disable=='1') //enable gestsup account if LDAP user is re-activate
								{
									$cnt_enable=$cnt_enable+1;
									if($_GET['action']=='run') {
									echo '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-check-circle text-success"></i><font class="text-success"> '.T_('Utilisateur').' <b>'.$givenname.' '.$sn.'</b> ('.$LDAP_login.'), '.T_('activé').'.</font><br />';
									$qry=$db->prepare("UPDATE `tusers` SET `disable`='0' WHERE `ldap_guid`=:ldap_guid");
									$qry->execute(array('ldap_guid' => $find_guid));
									} else {
										echo '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-check-circle text-success"></i><font class="text-success"> '.T_("Activation de l'utilisateur").' <b>'.$givenname.' '.$sn.'</b> ('.$LDAP_login.').</font><br />';
									}
								} else { //update gestsup account if LDAP have informations
									//compare data 
									$update=0;
									if($g_firstname!=$givenname) 
									{
										$update=T_('du prénom').' "'.$givenname.'"';
										if($_GET['action']=='run') {
											$qry=$db->prepare("UPDATE `tusers` SET `firstname`=:firstname WHERE `ldap_guid`=:ldap_guid");
											$qry->execute(array('firstname' => $givenname,'ldap_guid' => $find_guid));
										}
									}
									if($g_lastname!=$sn) 
									{
										$update=T_('du nom').' "'.$sn.'"';
										if($_GET['action']=='run') {
											$qry=$db->prepare("UPDATE `tusers` SET `lastname`=:lastname WHERE `ldap_guid`=:ldap_guid");
											$qry->execute(array('lastname' => $sn,'ldap_guid' => $find_guid));										
										}
									}
									if($g_mail!=$mail) 
									{
										if($mail && !filter_var($mail, FILTER_VALIDATE_EMAIL)) {
											$update=T_("de l'adresse mail est incorrecte");
											LogIt('error','ERROR 26 : LDAP, invalid mail address detected ('.$mail.')',0);
										} else {
											$update=T_("de l'adresse mail").' "'.$mail.'"';
											if($_GET['action']=='run') {
												$qry=$db->prepare("UPDATE `tusers` SET `mail`=:mail WHERE `ldap_guid`=:ldap_guid");
												$qry->execute(array('mail' => $mail,'ldap_guid' => $find_guid));		
											}
										}
									}
									if(($g_telephonenumber!=$telephonenumber) && $telephonenumber)
									{
										$update=T_('du numéro de téléphone').' "'.$telephonenumber.'" ';
										if($_GET['action']=='run') {
											$qry=$db->prepare("UPDATE `tusers` SET `phone`=:phone WHERE `ldap_guid`=:ldap_guid");
											$qry->execute(array('phone' => $telephonenumber,'ldap_guid' => $find_guid));
										}
									}
									if(($g_homephone!=$homephone) && $homephone && !$telephonenumber)
									{
										$update=T_('du numéro de téléphone').' "'.$homephone.'" ';
										if($_GET['action']=='run') {
											$qry=$db->prepare("UPDATE `tusers` SET `phone`=:phone WHERE `ldap_guid`=:ldap_guid");
											$qry->execute(array('phone' => $homephone,'ldap_guid' => $find_guid));
										}
									}
									if(($g_mobile!=$mobile) && $mobile)
									{
										$update=T_('du numéro du mobile').' "'.$mobile.'" ';
										if($_GET['action']=='run') {
											$qry=$db->prepare("UPDATE `tusers` SET `mobile`=:mobile WHERE `ldap_guid`=:ldap_guid");
											$qry->execute(array('mobile' => $mobile,'ldap_guid' => $find_guid));
										}
									}
									if($g_streetaddress!=$streetaddress) 
									{
										$update=T_("de l'adresse").' "'.$streetaddress.'" ';
										if($_GET['action']=='run') {
											$qry=$db->prepare("UPDATE `tusers` SET `address1`=:address1 WHERE `ldap_guid`=:ldap_guid");
											$qry->execute(array('address1' => $streetaddress,'ldap_guid' => $find_guid));
										}
									}
									if($g_postalcode!=$postalcode) 
									{
										$update=T_('du code postal').' "'.$postalcode.'" ';
										if($_GET['action']=='run') {
											$qry=$db->prepare("UPDATE `tusers` SET `zip`=:zip WHERE `ldap_guid`=:ldap_guid");
											$qry->execute(array('zip' => $postalcode,'ldap_guid' => $find_guid));
										}
									}
									if($g_l!=$l) 
									{
										$update=T_('de la ville').' "'.$l.'" ';
										if($_GET['action']=='run') {
											$qry=$db->prepare("UPDATE `tusers` SET `city`=:city WHERE `ldap_guid`=:ldap_guid");
											$qry->execute(array('city' => $l,'ldap_guid' => $find_guid));
										}
									}
									if($g_fax!=$fax) 
									{
										$update=T_('du FAX').' "'.$fax.'"';
										if($_GET['action']=='run') {
											$qry=$db->prepare("UPDATE `tusers` SET `fax`=:fax WHERE `ldap_guid`=:ldap_guid");
											$qry->execute(array('fax' => $fax,'ldap_guid' => $find_guid));
										}
									}
									if($g_title!=$title) 
									{
										$update=T_('de la fonction').' "'.$title.'"';
										if($_GET['action']=='run') {
											$qry=$db->prepare("UPDATE `tusers` SET `function`=:function WHERE `ldap_guid`=:ldap_guid");
											$qry->execute(array('function' => $title,'ldap_guid' => $find_guid));
										}
									}
									//login update
									if($LDAP_login && $g_login!=$LDAP_login)
									{
										$update=T_('du login ').' "'.$LDAP_login.'"';
										if($_GET['action']=='run') {
											$qry=$db->prepare("UPDATE `tusers` SET `login`=:login WHERE `ldap_guid`=:ldap_guid");
											$qry->execute(array('login' => $LDAP_login,'ldap_guid' => $find_guid));
										}
									}
									
									//get gestsup company name
									$qry=$db->prepare("SELECT `name` FROM `tcompany` WHERE id=:id");
									$qry->execute(array('id' => $g_company));
									$g_company_name=$qry->fetch();
									$qry->closeCursor();
									
									//update company name in lowercase to compare
									$company_lower=strtolower($company);
									$g_company_name_lower=strtolower($g_company_name[0]);
									
									if(($company_lower!=$g_company_name_lower) && $company!='' ) 
									{
										$update=T_('de la Société').' "'.$company.'" ';
										if($_GET['action']=='run') 
										{
											//find company in GestSup database
											$qry=$db->prepare("SELECT * FROM `tcompany`");
											$qry->execute();
											while($row=$qry->fetch()) 
											{
												if(strcasecmp($company, $row['name']) == 0)
												{
													$find_company=$row['id'];
													break;
												} 
												else 
												{
													$find_company='';
												}
											}
											$qry->closeCursor();
											//if company is find update company id else create company in gestsup
											if($find_company!='')
											{
												$qry=$db->prepare("UPDATE `tusers` SET `company`=:company WHERE `ldap_guid`=:ldap_guid");
												$qry->execute(array('company' => $find_company,'ldap_guid' => $ldap_guid));
											} 
											elseif($company!='')
											{
												$qry=$db->prepare("INSERT INTO `tcompany` (`name`) VALUES (:name)");
												$qry->execute(array('name' => $company));
												echo '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-plus-circle text-success"></i><font class="text-success"> '.T_('Société').' '.$company.' '.T_('crée').'.</font><br />';
												//get GestSup company table
												$qry=$db->prepare("SELECT `name`,`id` FROM `tcompany`");
												$qry->execute();
												while($row=$qry->fetch())
												{
													if($company==$row['name']) $find_company=$row['id']; 
												}
												$qry->closeCursor();
												//if company is find update company id else create company in gestsup
												if($find_company!='')
												{
													$qry=$db->prepare("UPDATE `tusers` SET `company`=:company WHERE `ldap_guid`=:ldap_guid");
													$qry->execute(array('company' => $find_company, 'ldap_guid' => $find_guid));
												}
											}											
										} 
										else
										{
											//get company table
											$qry=$db->prepare("SELECT `id`,`name` FROM `tcompany`");
											$qry->execute();
											while($row=$qry->fetch())
											{
												if($company==$row['name']) $find_company=$row['id']; else $find_company='';
											}
											$qry->closeCursor();
											// if company is find update company id else create company in gestsup
											if($find_company=='')	echo '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-plus-circle text-success"></i><font class="text-success"> '.T_('Création de la Société').' '.$company.'.</font><br />';
										}
									}
									// ************************************START Synchronize service******************************************
									//check if LDAP service is not empty
									if($department)
									{
										$department=str_replace('\'','&#039;',$department); #4995
										//check is LDAP service exist in GS db
										$qry=$db->prepare("SELECT `id` FROM `tservices` WHERE name=:name");
										$qry->execute(array('name' => $department));
										$row=$qry->fetch();
										$qry->closeCursor();
										//LDAP service not exist in GS db
										if(!$row) {
											//create service in GS DB
											if($_GET['action']=='run') 
											{
												$qry=$db->prepare("INSERT INTO `tservices` (`name`) VALUES (:name)");
												$qry->execute(array('name' => $department));
												echo '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-plus-circle text-success"></i><font class="text-success"> '.T_('Service').' '.$department.' '.T_('crée').'.</font><br />';
											} else {
												echo '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-plus-circle text-success"></i><font class="text-success"> '.T_('Création du service').' '.$department.'.</font><br />';
											}
										//LDAP service already exist in GS DB
										} else {
											
											//check if exist an association with current GS user and service.
											$qry2=$db->prepare("SELECT `id`,`user_id` FROM `tusers_services` WHERE user_id IN (SELECT id FROM tusers WHERE ldap_guid=:ldap_guid) AND service_id=:service_id");
											$qry2->execute(array('ldap_guid' => $find_guid,'service_id' => $row['id']));
											$row2=$qry2->fetch();
											$qry2->closeCursor();
											
											if(!$row2)//if no association found create it
											{
												$update=T_('du Service').' "'.$department.'" ';
												//create association
												if($_GET['action']=='run') 
												{
													//delete old association
													$qry=$db->prepare("DELETE FROM tusers_services WHERE user_id IN (SELECT id FROM tusers WHERE ldap_guid=:ldap_guid)");
													$qry->execute(array('ldap_guid' => $find_guid));
													//create new association
													$qry=$db->prepare("INSERT INTO tusers_services (user_id,service_id) VALUES ((SELECT MAX(id) FROM tusers WHERE ldap_guid=:ldap_guid),:service_id)");
													$qry->execute(array('ldap_guid' => $find_guid,'service_id' => $row['id']));
												}
											} 
										}
									}
									// ************************************END Synchronize service******************************************
									if($update)
									{
										$cnt_maj=$cnt_maj+1;
										if($_GET['action']=='run') {
											echo '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-sync text-warning"></i><font class="text-warning"> '.T_('Utilisateur').' <b>'.$givenname.' '.$sn.'</b> ('.$LDAP_login.'), '.T_('mis à jour').' '.$update.'.</font><br />';
										} else {
											echo '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-sync text-warning"></i><font class="text-warning"> '.T_('Mise à jour').' '.$update.' '.T_('pour').' <b>'.$givenname.' '.$sn.'</b> ('.$LDAP_login.').</font><br />';
										}
									}
								}
							}
						} elseif($samaccountname!='Invité' && $samaccountname!='krbtgt' && $samaccountname!='') {
							//create GestSup account
								$cnt_create=$cnt_create+1;
								//generate default pwd and salt
								$salt = substr(md5(uniqid(rand(), true)), 0, 5); //generate a random key as salt
								$pwd=substr(str_shuffle(strtolower(sha1(rand() . time() . $salt))),0, 50);
								$pwd=md5($salt . md5($pwd)); 
								
							if($_GET['action']=='run') {
								if(!filter_var($mail, FILTER_VALIDATE_EMAIL)) {$mail='';}
								echo '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-plus-circle text-success"></i><font class="text-success"> '.T_('Utilisateur').' <b>'.$givenname.' '.$sn.'</b> ('.$LDAP_login.') '.T_('à été crée').'.</font><br />';
								$qry=$db->prepare("
								INSERT INTO tusers (login,password,salt,firstname,lastname,profile,mail,phone,mobile,address1,zip,city,company,fax,ldap_guid,ldap_sid)
								VALUES
								(:login,:password,:salt,:firstname,:lastname,'2',:mail,:phone,:mobile,:address1,:zip,:city,:company,:fax,:ldap_guid,:ldap_sid)");
								$qry->execute(array(
									'login' => $LDAP_login,
									'password' => $pwd,
									'salt' => $salt,
									'firstname' => $givenname,
									'lastname' => $sn,
									'mail' => $mail,
									'phone' => $telephonenumber,
									'mobile' => $mobile,
									'address1' => $streetaddress,
									'zip' => $postalcode,
									'city' => $l,
									'company' => $company,
									'fax' => $fax,
									'ldap_guid' => $ldap_guid,
									'ldap_sid' => $SID
									));
							} else {
								echo '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-plus-circle text-success"></i><font class="text-success"> '.T_("Création de l'utilisateur").' <b>'.$givenname.' '.$sn.'</b> ('.$LDAP_login.').</font><br />';
							}
						}
					}
					
					//for each Gestsup user (find user not present in LDAP for disable in GestSup)
					if($rparameters['ldap_disable_user']==1)
					{
						$qry=$db->prepare("SELECT `id`,`login`,`firstname`,`lastname`, `disable`,`mail`, `phone`,`mobile`,`mobile`,`address1`,`zip`,`city`,`company`,`fax`,`function`,`ldap_guid` FROM `tusers`");
						$qry->execute();
						while($row=$qry->fetch()) 	
						{
							$find2_guid='';
							for ($i=0; $i < $cnt_ldap; $i++) 
							{
								if($rparameters['ldap_type']==0 || $rparameters['ldap_type']==3) { //Windows / Samba
									$GUID=$data[$i]['objectguid'][0];
									$GUID=unpack("H*hex",$data[$i]['objectguid'][0]);
									$ldap_guid=$GUID['hex'];
								} else { //OpenLDAP
									$ldap_guid=$data[$i]['entryuuid'][0];
								}
								if($ldap_guid==$row['ldap_guid']) $find2_guid=$row['ldap_guid'];
							}
							if(($find2_guid=='') && ($row['disable']=='0') && ($row['ldap_guid']!='') && ($row['ldap_guid']!=' ') && ($row['login']!='admin'))
							{
								
								$cnt_disable=$cnt_disable+1;
								if($_GET['action']=='run')
								{
									echo '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-times-circle text-danger"></i><font class="text-danger"> '.T_('Utilisateur').' <b>'.$row['firstname'].' '.$row['lastname'].'</b> ('.$row['login'].'), '.T_('désactivé').'.</font><br />';
									$qry2=$db->prepare("UPDATE `tusers` SET `disable`=1 WHERE `ldap_guid`=:ldap_guid");
									$qry2->execute(array('ldap_guid' => $row['ldap_guid']));
								} else {
									echo '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-times-circle text-danger"></i><font class="text-danger"> '.T_("Désactivation de l'utilisateur").' <b>'.$row['firstname'].' '.$row['lastname'].'</b> ('.$row['login'].'). <span style="font-size: x-small;">'.T_('Raison').': '.T_("Utilisateur non présent dans l'annuaire LDAP").'.</span></font><br />';
								}
							}
						}
						$qry->closeCursor();
					}
					
					if(($cnt_create=='0') && ($cnt_disable=='0') && ($cnt_maj=='0') && ($cnt_enable=='0'))
					{
						echo '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-check-circle text-success"></i><font class="text-success"> '.T_('Aucune modification à apporter, les annuaires sont à jour').'.</font><br />';
					} else {
						echo'
						<br />
						&nbsp;&nbsp;&nbsp;&nbsp;'.T_("Nombre de d'utilisateurs à créer dans GestSup").' : '.$cnt_create.' <br />
						&nbsp;&nbsp;&nbsp;&nbsp;'.T_("Nombre de d'utilisateurs à mettre à jour dans GestSup").' : '.$cnt_maj.' <br />
						&nbsp;&nbsp;&nbsp;&nbsp;'.T_("Nombre de d'utilisateurs à désactiver dans GestSup").' : '.$cnt_disable.' <br />
						&nbsp;&nbsp;&nbsp;&nbsp;'.T_("Nombre de d'utilisateurs à activer dans GestSup").' : '.$cnt_enable.' <br />
						';
					}
			}
			if(($_GET['action']=='simul') || ($_GET['action']=='run') || ($_GET['ldap']=='1')) 
			{
				echo'
					<br />
					<button onclick=\'window.location.href="index.php?page=admin&amp;subpage=user&amp;ldap=1&amp;action=simul"\' type="submit" class="btn btn-primary">
						<i class="fa fa-flask"></i>
						'.T_('Lancer une simulation').'
					</button>
					<button onclick=\'window.location.href="index.php?page=admin&amp;subpage=user&amp;ldap=1&amp;action=run"\' type="submit" class="btn btn-primary">
						<i class="fa fa-bolt"></i>
						'.T_('Lancer la synchronisation').'
					</button>
					<button onclick=\'window.location.href="index.php?page=admin&subpage=user"\' type="submit" class="btn btn-primary btn-danger">
						<i class="fa fa-reply"></i>
						'.T_('Retour').'
					</button>					
				';
			}
			//unbind LDAP server
			ldap_unbind($ldap);
		} elseif($_GET['subpage']=='user')
		{
			echo DisplayMessage('error',T_("La connection LDAP n'est pas disponible, vérifier si votre serveur LDAP est joignable ou vérifier vos paramètres de connection (".ldap_error($ldap).")"));
			//log
			if($rparameters['log']){LogIt('error', "ERROR 27 : LDAP, connexion failed ".ldap_error($ldap),$_SESSION['user_id']);}
		}
	} 
} else {
	echo '<span class="text-danger"> <b>'.T_('Erreur').' :</b> '.T_("Le connecteur LDAP est désactivé").'.<span>';
}
?>