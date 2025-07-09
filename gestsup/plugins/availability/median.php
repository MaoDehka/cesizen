<?php
################################################################################
# @Name : /plugins/availability/median.php
# @Description : calculate median
# @Call : /plugins/availability.php
# @Parameters : category
# @Author : Flox
# @Create : 23/05/2015
# @Update : 03/11/2023
# @Version : 3.2.43
################################################################################

//check var
if(!is_numeric($year)) {echo 'ERROR : wrong year';}

//function median
function calculate_median($arr) {
    sort($arr);
    $count = count($arr); //total numbers in array
    $middleval = floor(($count-1)/2); // find the middle value, or the lowest middle value
    if($count % 2) { // odd number, middle is the median
        $median = $arr[$middleval];
    } else { // even number, calculate avg of 2 medians
		
		//avoid negative values for php warning without values
		if ($middleval!='-1') { 
			$low = $arr[$middleval];
			$high = $arr[$middleval+1];
			$median = (($low+$high)/2);
		} else {$median=0;}
    }
    return $median;
}

//target median
$median_target_array = array();
$qry=$db->prepare("SELECT `target` FROM `tavailability_target` WHERE `year`=:year");
$qry->execute(array('year' => $year));
while($row=$qry->fetch()) 
{
    array_push($median_target_array, "$row[0]");
}
$qry->closeCursor();
$median_target = calculate_median($median_target_array);

//global median
$median_global_array = array();
$qry=$db->prepare("SELECT * FROM `tavailability`");
$qry->execute();
while($rowsubcat=$qry->fetch()) 
{
    include('core.php');
    array_push($median_global_array, "$tx");
}
$qry->closeCursor();
$median_global = calculate_median($median_global_array);

//none planned median
$median_none_planned_array = array();
$qry=$db->prepare("SELECT * FROM `tavailability`");
$qry->execute();
while($rowsubcat=$qry->fetch()) 
{
    include('core.php');
    array_push($median_none_planned_array, "$tx_none_planned");
}
$qry->closeCursor();
$median_none_planned = calculate_median($median_none_planned_array);

?>