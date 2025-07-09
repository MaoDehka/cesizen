//################################################################################
// @Name : js/admin_list.js
// @Description : script to admin list page
// @call : admin/user.php
// @parameters : 
// @Author : Flox
// @Create : 01/10/2021
// @Update : 18/04/2023
// @Version : 3.2.35
//################################################################################

//CTRL+S to save ticket 
$(document).keydown(function(e) {
    var key = undefined;
    var possible = [ e.key, e.keyIdentifier, e.keyCode, e.which ];
    while (key === undefined && possible.length > 0)
    {
        key = possible.pop();
    }
    if (key && (key == '115' || key == '83' ) && (e.ctrlKey || e.metaKey) && !(e.altKey))
    {
        e.preventDefault();
         $('#submit').click();
        return false;
    }
    return true;
}); 

//color picker for mail settings in general parameter form
$(function () {
    $('#color').colorpicker();
});

//input date fields
if(document.getElementById("limit_hour_date_start") || document.getElementById("limit_ticket_date_start"))
{
    jQuery(function($) {
        var date = moment($('#limit_hour_date_start').val(), 'DD-MM-YYYY').toDate();
        $('#limit_hour_date_start').datetimepicker({ date:date, format: 'DD/MM/YYYY' });
        var date = moment($('#limit_ticket_date_start').val(), 'DD-MM-YYYY').toDate();
        $('#limit_ticket_date_start').datetimepicker({ date:date, format: 'DD/MM/YYYY' });
    });
}
if(document.getElementById("ticket_recurrent_date_start"))
{
    jQuery(function($) {
        var date = moment($('#ticket_recurrent_date_start').val(), 'DD-MM-YYYY').toDate();
        $('#ticket_recurrent_date_start').datetimepicker({ date:date, format: 'DD/MM/YYYY' });
    });
}