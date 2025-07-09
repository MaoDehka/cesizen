//################################################################################
// @Name : js/procedure.js
// @Description : script to procedure page
// @call : procedure.php
// @parameters : 
// @Author : Flox
// @Create : 29/09/2021
// @Update : 30/09/2021
// @Version : 3.2.17
//################################################################################

document.addEventListener('DOMContentLoaded', function() {
    "use strict";
    Procedure();
});

/*----------------------*/
/*    Procedure Form     */
/*----------------------*/
function Procedure() {
    $('#myform').on('submit', function(e) {
         //display loading popup
         $.aceToaster.add({
            placement: 'tc',
            title: "",
            body: '',
            icon: '<i class="text-success mr-2 text-130"><i class="fas fa-spinner fa-pulse mb-3 fa-2x text-success"></i></i>',
            iconClass: '',
            delay: 500,
            closeClass: 'btn btn-light-danger border-0 btn-bgc-tp btn-xs px-2 py-0 text-150 position-tr mt-n25',
            className: 'bgc-white-tp1 border-none border-t-4 brc-success-tp1 rounded-sm pl-3 pr-1',
            headerClass: 'bg-transparent border-0 text-120 text-success-d3 font-bolder mt-3',
            bodyClass: 'pt-4 pb-0 text-105'
        })

        //ajax call
        $.ajax({
            type: "POST",
            url: 'ajax/procedure.php',
            dataType: 'text',
            cache: false,
            contentType: false,
            processData: false,
            data:  new FormData(this),
            success: function(response) {
                var jsonData = JSON.parse(response);
                if (jsonData.status == "success")
                {
                    //display success popup
                    $.aceToaster.removeAll(null, true);
                    $.aceToaster.add({
                        placement: 'tc',
                        title: jsonData.message,
                        body: '',
                        icon: '<i class="text-success mr-2 text-130"><i class="fas fa-check mt-1 fa-2x text-success"></i></i>',
                        iconClass: '',
                        delay: Number(jsonData.delay),
                        closeClass: 'btn btn-light-danger border-0 btn-bgc-tp btn-xs px-2 py-0 text-150 position-tr mt-n25',
                        className: 'bgc-white-tp1 border-none border-t-4 brc-success-tp1 rounded-sm pl-3 pr-1',
                        headerClass: 'bg-transparent border-0 text-120 text-success-d3 font-bolder mt-3',
                        bodyClass: 'pt-4 pb-0 text-105'
                    })
                    //after add procedure; remove submit button and redirect to edit page
                    if(jsonData.action=='add')
                    {   
                        $("#submit").hide();
                        setTimeout(function () {
                            window.location.href = 'index.php?page=procedure&id='+Number(jsonData.procedure_id)+'&action=edit';
                         }, 1000); 
                    }
                    //when upload file display it
                    if(jsonData.uploaded_file)
                    {
                        //erase previous selected file on input field
                        $("#procedure_file").replaceWith($("#procedure_file").val('').clone(true));
                        var div = document.getElementById('uploaded_file');
                        div.innerHTML += jsonData.uploaded_file;
                    }
                } else {
                    //display error popup
                    $.aceToaster.removeAll(null, true);
                    $.aceToaster.add({
                        placement: 'tc',
                        title: jsonData.message,
                        body: '',
                        icon: '<i class="text-danger mr-2 text-130"><i class="fas fa-exclamation-triangle mt-1 fa-2x text-danger"></i></i>',
                        iconClass: '',
                        delay: Number(jsonData.delay)*10,
                        closeClass: 'btn btn-light-danger border-0 btn-bgc-tp btn-xs px-2 py-0 text-150 position-tr mt-n25',
                        className: 'bgc-white-tp1 border-none border-t-4 brc-danger-tp1 rounded-sm pl-3 pr-1',
                        headerClass: 'bg-transparent border-0 text-120 text-danger-d3 font-bolder mt-3',
                        bodyClass: 'pt-4 pb-0 text-105'
                    })
                }
            }
        });
        e.preventDefault();
    });

    //update subcat list in category switch case
    $('#category').change(function(){ //detect category switch
        //get value
        var CategorySelected = $(this).val();

        //get token value
        var token=document.getElementById("token").value;
    
        //replace subcat field with new associated values
        $.ajax({
            url:"ajax/procedure_subcat.php",
            type:"post",
            data: {CategoryId: CategorySelected,token: token},
            async:true,
            success: function(result) {
                var data = JSON.parse(result);
                //reset and populate subcat field
                $("#subcat").empty();
                jQuery.each(data, function(index, value){
                    $("#subcat").append("<option value='"+value['id']+"'>"+value['name']+"</option>");
                });
            },
            error: function() {
                console.log('ERROR : unable to get subcat for category '+CategorySelected)
            }
        });
    });	

    //CTRL+S to save procedure 
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
            $('#myform #edit').click();
            $('#myform #submit').click();
            return false;
        }
        return true;
    }); 
};