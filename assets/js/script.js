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
          $(".un_assign").text('....................');
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
                $('.un_assign').attr('disabled',true);
              }
          });
      });
});






