<?php
################################################################################
# @Name : /plugins/availability/breadcrumb.php
# @Description : display breadcrumb of current plugin
# @Call : /plugin.php
# @Parameters : 
# @Author : Flox
# @Create : 22/01/2021
# @Update : 15/02/2021
# @Version : 3.2.9
################################################################################

if($rright['availability'])
{
    if($_GET['page']=='plugins/availability/availability') echo '<i class="pr-2 pl-2 fa fa-angle-right "></i><a href="index.php?page=plugins/availability/availability"><i class="fa fa-clock text-primary-m2"></i></a>';
}
?>