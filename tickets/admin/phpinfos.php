<?php 
################################################################################
# @Name : /admin/phpinfos.php
# @Description : PHP Parameters 
# @Call : /admin/system.php
# @Parameters : 
# @Author : Flox
# @Create : 17/09/2009
# @Update : 29/07/2021
# @Version : 3.2.15
################################################################################

//initialize variables
require_once(__DIR__."/../core/init_get.php");

//connexion script with database parameters
require "./../connect.php";

//locales
$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
if ($lang=='fr') {$_GET['lang'] = 'fr_FR';}
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

//load parameters table
$qry = $db->prepare("SELECT * FROM `tparameters`");
$qry->execute();
$rparameters=$qry->fetch();
$qry->closeCursor();

if ($rparameters['server_private_key']==$_GET['key'])
{
	phpinfo();
} else {
	echo '<br /><br /><div style="text-align:center"><font color="red"><b>'.T_('Erreur').'</b> : '.T_("Vous n'avez pas accès à cette page, contactez votre administrateur").'</font></div>';
}
?>