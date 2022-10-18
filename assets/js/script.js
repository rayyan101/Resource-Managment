jQuery(document).ready(function ($) {
        $("#assign-project").submit(function(){
            event.preventDefault();
            $("#contact-submit").text('Submitting....');
            var serialize_data = $(this).serialize();
            // $("#contact-submit").prop("disabled",true);
              $.ajax({
                type:"POST",
                url: localize.ajaxurl,
                data: serialize_data,
                dataType : 'json',
              success: function (response) {
                $("#register_submit").children().remove();
                    $("#register_submit").prop("disabled",false);
                    $("#contact-submit").text('Submit');
                        // var error = response.error;
                        console.log("response ",response);
                        alert(response.message);
                        $(".container").hide();      
                },
                    error: function (errorThrown) {
                       alert('error');
                        console.log(errorThrown);
                        
                    },
            });
        });

        $(".un_assign").on("click",function(){
          var resourse_id =   $(this).attr("data-res-id");
          var project_id =   $(this).attr("data-pro-id") ;
          $(this).text('Un Assigned');
          $(this).attr('disabled',true);
          // alert(project_id);
          jQuery.ajax({
            url: localize.ajaxurl,
              type: 'POST',
              data: {  
                  action: 'rm_unassign_resource',  
                  resourse_id: resourse_id,
                  project_id: project_id 
              },
              success: function (data) {
                console.log(data);
                $(this).attr('disabled',true);
                  
              }
          });
      });

      $("#searching_button").on("click",function(){
        var resource_name = $("#resource_name").val();
        var project_name = $("#project_name").val();
        var availability = $("#availability").val();
        
        if($("#availability").prop("checked") == true){
          var availability = "checked";
          // console.log("var availability == checked");
        }
        else if($("#availability").prop("checked") == false){
          var availability = "Unchecked";
          // console.log("var availability == Unchecked");
        }
        if(resource_name == "" && project_name == "" && availability == "Unchecked"){
          location.reload();
          return false;
        }
        // if(resource_name == "" && availability == "checked"){
        //   $("#project_resources_table").hide();
        //   $('#pr-pagination').hide();
        //   $("#resource_allocation_table").show();
        //   console.log("resource name == null and check is true");
        //   return false;
        // } 
        jQuery.ajax({
          url: localize.ajaxurl,
            type: 'POST',
            data: {  
                action: 'rm_resource_data_searching',  
                resource_name: resource_name,
                project_name: project_name,
                availability: availability
            },
            success: function (data) {
              $('#pr-pagination').hide();

  
              if($("#availability").prop("checked") == true){
                $("#project_resources_table").hide();
                $("#resource_allocation_table").show();
                $("#resource_allocation_table tbody").html(data.table);
                console.log(data.table);
              }
              else if($("#availability").prop("checked") == false){
                $("#project_resources_table tbody").html(data.table);
                console.log(data.table);
              }
              
            }
     });
  });
      $("#assign_project").on("click",function(){
        $(".container").show();
      });
        
        jQuery(document).ready(function($) {
          $('.select-data').select2();
      });



      $('#designation').on('change', function() {
        var designation = $(this).val();
        var availability = $("#availability").val();
        
        if($("#availability").prop("checked") == true){
          var availability = "checked";
          // console.log("var availability == checked");
        }
        else if($("#availability").prop("checked") == false){
          var availability = "Unchecked";
          // console.log("var availability == Unchecked");
        }

        jQuery.ajax({
          url: localize.ajaxurl,
            type: 'POST',
            data: {  
                action: 'rm_resource_designation',  
                designation: designation,
                availability: availability
            },
            success: function (data) {
              $('#pr-pagination').hide();

  
              if($("#availability").prop("checked") == true){
                $("#project_resources_table").hide();
                $("#resource_allocation_table").show();
                $("#resource_allocation_table tbody").html(data.table);
                console.log(data.table);
                console.log(data);
              }
              else if($("#availability").prop("checked") == false){
                $("#project_resources_table tbody").html(data.table);
                console.log(data.table);
                console.log(data);
              }
            
            }
        });

      });




});






