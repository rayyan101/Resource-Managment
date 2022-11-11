jQuery(document).ready(function ($) {
      $("#assign-project").submit(function(){
          event.preventDefault();
          $("#contact-submit").text('Submitting....');
          var serialize_data = $(this).serialize();
            $.ajax({
              type:"POST",
              url: localize.ajaxurl,
              data: serialize_data,
              dataType : 'json',
              success: function (response) {
              $("#register_submit").children().remove();
                  $("#register_submit").prop("disabled",false);
                  $("#contact-submit").text('Submit');
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
                // console.log(data);
                location.reload();
                // $(this).attr('disabled',true);
                  
              }
          });
      });

      $("#searching_button").on("click",function(){
        var resource_name = $("#resource_name").val();
        var project_name = $("#project_name").val();
        var availability = $("#availability").val(); 
        if($("#availability").prop("checked") == true){
          var availability = "checked";
        }
        else if($("#availability").prop("checked") == false){
          var availability = "Unchecked";
        }  
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
                $("#status").val("1").change();
                $("#resource_allocation_table tbody").html(data.table);
                console.log(data.table);
                console.log(data);
              }
              else if($("#availability").prop("checked") == false){
                $("#resource_allocation_table").hide();
                $("#project_resources_table").show();
                $("#status").val("1").change();
                $("#project_resources_table tbody").html(data.table);
                console.log(data.table);
                console.log(data);
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
        }
        else if($("#availability").prop("checked") == false){
          var availability = "Unchecked"; 
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
                $("#status").val("1").change();
                $("#resource_allocation_table tbody").html(data.table);
                console.log(data.table);
                console.log(data);
              }
              else if($("#availability").prop("checked") == false){
                $("#resource_allocation_table").hide();
                $("#project_resources_table").show();
                $("#status").val("1").change();
                $("#project_resources_table tbody").html(data.table);
                console.log(data.table);
                console.log(data);
              }    
            }
        });
      });

      $('#status').on('change', function() {  
        var status = $(this).val();
        jQuery.ajax({
          url: localize.ajaxurl,
            type: 'POST',
            data: {  
                action: 'rm_resource_status',  
                status: status
            },
            success: function (data) {
              $('#pr-pagination').hide();                   
                $("#project_resources_table tbody").html(data.table);
                console.log(data.table);
                console.log(data);       
            }
        });
      });
});






