<?php
################################################################################
# @Name : /plugin.php
# @Description : plugin system
# @Call : /*.php
# @Parameters : $section
# @Author : Flox
# @Create : 21/01/2021
# @Update : 30/04/2024
# @Version : 3.2.50
################################################################################

//connect db
if(!isset($db)) {require_once('connect.php');}

//foreach enabled plugin, add specific section
$qry2=$db->prepare("SELECT `name` FROM `tplugins` WHERE `enable`='1' ");
$qry2->execute();
while($plugin=$qry2->fetch()) 
{
    //login page
    if($section=='index' && $plugin['name']!='availability' && file_exists('plugins/'.$plugin['name'].'/index.php')) {include('plugins/'.$plugin['name'].'/index.php');}   
    //index page
    if($section=='download' && $plugin['name']!='availability' && file_exists('plugins/'.$plugin['name'].'/download.php')) {include('plugins/'.$plugin['name'].'/download.php');}
    //page white list
    if($section=='page_white_list' && file_exists('plugins/'.$plugin['name'].'/page_white_list.php')) {include('plugins/'.$plugin['name'].'/page_white_list.php');} 
    //favicon
    if($section=='favicon' && file_exists('plugins/'.$plugin['name'].'/favicon.php')) {include('plugins/'.$plugin['name'].'/favicon.php');} 
    //breadcrumb
    if($section=='breadcrumb' && file_exists('plugins/'.$plugin['name'].'/breadcrumb.php')) {include('plugins/'.$plugin['name'].'/breadcrumb.php');}
    //menu
    if($section=='menu' && file_exists('plugins/'.$plugin['name'].'/menu.php')) {include('plugins/'.$plugin['name'].'/menu.php');} 
    //ticket form
    if($section=='ticket_form' && file_exists('plugins/'.$plugin['name'].'/ticket.php')) {include('plugins/'.$plugin['name'].'/ticket.php');} 
    //ticket js
    if($section=='ticket_js' && file_exists('plugins/'.$plugin['name'].'/js/ticket.js')) {echo '<script type="text/javascript" src="plugins/'.$plugin['name'].'/js/ticket.js"></script>';} 
    //ticket core
    if($section=='ticket_core' && file_exists('plugins/'.$plugin['name'].'/core/ticket.php')) {include('plugins/'.$plugin['name'].'/core/ticket.php');} 
    //parameters connector
    if($section=='connector' && file_exists('plugins/'.$plugin['name'].'/admin/parameters/connector.php')) {include('plugins/'.$plugin['name'].'/admin/parameters/connector.php');} 
    //parameters user_list_btn
    if($section=='user_list_btn' && file_exists('plugins/'.$plugin['name'].'/admin/users/user_list_btn.php')) {include('plugins/'.$plugin['name'].'/admin/users/user_list_btn.php');} 
    //parameters user_list
    if($section=='user_list' && file_exists('plugins/'.$plugin['name'].'/admin/users/list.php')) {include('plugins/'.$plugin['name'].'/admin/users/list.php');} 
    //parameters login
    if($section=='login' && file_exists('plugins/'.$plugin['name'].'/login.php')) {include('plugins/'.$plugin['name'].'/login.php');} 
    //parameters login_post
    if($section=='login_post' && file_exists('plugins/'.$plugin['name'].'/login_post.php')) {include('plugins/'.$plugin['name'].'/login_post.php');} 
    //parameters login
    if($section=='logout' && file_exists('plugins/'.$plugin['name'].'/auth_logout.php')) {include('plugins/'.$plugin['name'].'/auth_logout.php');} 
    //calendar title
    if($section=='calendar_title' && file_exists('plugins/'.$plugin['name'].'/calendar_title.php')) {include('plugins/'.$plugin['name'].'/calendar_title.php');}
    //calendar start
    if($section=='calendar_start' && file_exists('plugins/'.$plugin['name'].'/calendar_start.php')) {include('plugins/'.$plugin['name'].'/calendar_start.php');} 
    //calendar event
    if($section=='calendar_event' && file_exists('plugins/'.$plugin['name'].'/calendar_event.php')) {include('plugins/'.$plugin['name'].'/calendar_event.php');} 
    //ticket print
    if($section=='ticket_print' && file_exists('plugins/'.$plugin['name'].'/ticket_print.php')) {include('plugins/'.$plugin['name'].'/ticket_print.php');}    
    //asset fields
    if($section=='asset_field' && file_exists('plugins/'.$plugin['name'].'/asset_field.php')) {include('plugins/'.$plugin['name'].'/asset_field.php');} 
    //admin user infos
    if($section=='admin_user_infos' && file_exists('plugins/'.$plugin['name'].'/admin/users/edit.php')) {include('plugins/'.$plugin['name'].'/admin/users/edit.php');} 
    //mail2ticket 
    if($section=='mail2ticket' && file_exists('plugins/'.$plugin['name'].'/mail2ticket.php')) {include('plugins/'.$plugin['name'].'/mail2ticket.php');} 
}
$qry2->closeCursor();
?>