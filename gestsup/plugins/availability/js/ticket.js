//################################################################################
// @Name : /plugins/availability/js/ticket.js
// @Description : script to ticket page
// @call : ticket.php
// @parameters : 
// @Author : Flox
// @Create : 29/01/2021
// @Update : 29/01/2021
// @Version : 3.2.8
//################################################################################

var date = moment($('#start_availability').val(), 'DD-MM-YYYY hh:mm:ss').toDate();
$('#start_availability').datetimepicker({ date:date, format: 'DD/MM/YYYY HH:mm:ss' });
var date = moment($('#end_availability').val(), 'DD-MM-YYYY hh:mm:ss').toDate();
$('#end_availability').datetimepicker({ date:date, format: 'DD/MM/YYYY HH:mm:ss' });
