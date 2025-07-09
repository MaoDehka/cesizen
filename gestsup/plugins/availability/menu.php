<?php
################################################################################
# @Name : /plugins/availability/menu.php
# @Description : display menu of current plugin
# @Call : /plugin.php
# @Parameters : 
# @Author : Flox
# @Create : 21/01/2021
# @Update : 15/02/2021
# @Version : 3.2.9
################################################################################

if($rright['availability'])
{
    if($_GET['page']=='plugins/availability/availability') {echo '<li class="nav-item active" >';} else {echo '<li class="nav-item" >';} 
        echo '
        <a class="nav-link" href="index.php?page=plugins/availability/availability" >
            <i class="nav-icon fa fa-clock"></i>
            <span class="nav-text fadeable">'.T_('Disponibilité').'</span>
        </a>
    </li>';
}

?>