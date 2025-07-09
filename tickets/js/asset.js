//################################################################################
// @Name : js/asset.js
// @Description : script to asset page
// @call : asset.php
// @parameters : 
// @Author : Flox
// @Create : 17/11/2020
// @Update : 26/08/2022
// @Version : 3.2.26
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
         $('#myform #modify').click();
        return false;
    }
    return true;
}); 

//input date fields
jQuery(function($) {
    var date = moment($('#date_install').val(), 'DD-MM-YYYY').toDate();
    $('#date_install').datetimepicker({ date:date, format: 'DD/MM/YYYY' });
 
    var date = moment($('#date_end_warranty').val(), 'DD-MM-YYYY').toDate();
    $('#date_end_warranty').datetimepicker({ date:date, format: 'DD/MM/YYYY' });
    var date = moment($('#date_stock').val(), 'DD-MM-YYYY').toDate();
    $('#date_stock').datetimepicker({ date:date, format: 'DD/MM/YYYY' });
    var date = moment($('#date_recycle').val(), 'DD-MM-YYYY').toDate();
    $('#date_recycle').datetimepicker({ date:date, format: 'DD/MM/YYYY' });
    var date = moment($('#date_standbye').val(), 'DD-MM-YYYY').toDate();
    $('#date_standbye').datetimepicker({ date:date, format: 'DD/MM/YYYY' });
});
