<?php
################################################################################
# @Name : /plugins/availability/ticket.php
# @Description : display ticket field
# @Call : /plugins.php
# @Parameters : 
# @Author : Flox
# @Create : 27/01/2021
# @Update : 27/03/2022
# @Version : 3.2.35
################################################################################

//init var
if(!isset($start_availability)) $start_availability = '';
if(!isset($end_availability)) $end_availability = '';
if(!isset($_POST['start_availability'])) $_POST['start_availability'] = '';
if(!isset($_POST['end_availability'])) $_POST['end_availability'] = '';
if(!isset($globalrow['start_availability'])) $globalrow['start_availability'] = '';
if(!isset($globalrow['end_availability'])) $globalrow['end_availability'] = '';
if(!isset($globalrow['availability_planned'])) $globalrow['availability_planned'] = '';
?>
<!-- START availability part --> 
<?php
    //check if the availability parameter is on and condition parameter
    if($rparameters['availability'])
    {
            if(
                ($rparameters['availability_condition_type']=='criticality' && ($globalrow['criticality']==$rparameters['availability_condition_value'] || $_POST['criticality']==$rparameters['availability_condition_value']))
                ||
                ($rparameters['availability_condition_type']=='types' && ($globalrow['type']==$rparameters['availability_condition_value'] || $_POST['type']==$rparameters['availability_condition_value']))
            )
            {    
                //calculate time
                if($globalrow['start_availability']!='0000-00-00 00:00:00' && $globalrow['end_availability']!='0000-00-00 00:00:00')
                {
                    $t1 =strtotime($globalrow['start_availability']) ;
                    $t2 =strtotime($globalrow['end_availability']) ;
                    $time=(($t2-$t1)/60)/60;
                    $time='('.$time.'h)';
                } else $time='';
                
                if($_POST['start_availability'])
                {
                    $start_availability=$_POST['start_availability'];
                }elseif($globalrow['start_availability'] && $globalrow['start_availability']!='0000-00-00 00:00:00') 
                {
                    $start_availability=$globalrow['start_availability'];
                    $start_availability=DateTimeToDisplay($start_availability);
                }else{
                    $start_availability=date("d/m/Y H:i:s");
                }

                if($_POST['end_availability'])
                {
                    $end_availability=$_POST['end_availability'];
                }elseif($globalrow['end_availability'] && $globalrow['end_availability']!='0000-00-00 00:00:00') {
                    $end_availability=DateTimeToDisplay($globalrow['end_availability']);
                }else{
                    $end_availability=date("d/m/Y H:i:s");
                }
                echo'
                <div class="form-group row '; if($rright['ticket_availability_disp']==0) echo 'd-none'; echo '">
                    <div class="col-sm-2 col-form-label text-sm-right pr-0">
                        <label class="mb-0" for="start_availability">'.T_("Début de l'indisponibilité").' :</label>
                    </div>
                    <div class="col-sm-5">
                        <input type="text" class="form-control col-5 datetimepicker-input" data-toggle="datetimepicker" data-target="#start_availability" id="start_availability" name="start_availability"  value="'.$start_availability.'" ';	
                            if($rright['ticket_availability']==0) echo ' disabled ';
                        echo '
                        >
                    </div>
                </div>
                <div class="form-group row '; if($rright['ticket_availability_disp']==0) echo 'd-none'; echo '">
                    <div class="col-sm-2 col-form-label text-sm-right pr-0">
                        <label class="mb-0" for="end_availability">'.T_("Fin de l'indisponibilité").' :</label>
                    </div>
                    <div class="col-sm-5">
                        <input type="text" class="form-control col-5 datetimepicker-input" data-toggle="datetimepicker" data-target="#end_availability" id="end_availability" name="end_availability"  value="'.$end_availability.'"';
                            if($rright['ticket_availability']==0) echo ' disabled ';
                        echo '
                        > '.$time.'
                    </div>
                </div>
                <div class="form-group row '; if($rright['ticket_availability_disp']==0) echo 'd-none'; echo '">
                    <div class="col-sm-2 col-form-label text-sm-right pr-0">
                        <label class="mb-0" for="availability_planned">'.T_('Indisponibilité planifiée').' :</label>
                    </div>
                    <div class="col-sm-2">
                        <input type="checkbox"'; if($globalrow['availability_planned']==1) {echo "checked";} echo ' name="availability_planned" value="1" />
                    </div>
                </div>
                ';
            } else {
                echo'
                    <input type="hidden" name="start_availability" id="start_availability" value="'.$start_availability.'" />
                    <input type="hidden" name="end_availability" id="end_availability" value="'.$end_availability.'" />
                ';
            }
    }
?>
<!-- END availability part -->