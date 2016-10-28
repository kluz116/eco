
            $(document).ready(function(){
               $('#addNewClient').on('click', function(e){
                // We don't want this to act as a link so cancel the link action
                e.preventDefault();

                 addClient();
               });
               function addClient () {
                     
                     var firstname = $('#firstname').val();
                     var lastname = $('#lastname').val();
                     var phone =$('#phone').val(); 
                     var product= $('#product').val();
                     var partner = $('#partner').val();
                     var resa = $('#resa').val();
                     var erand = $('#erand').val();
                     var district = $('#district').val();
                     var region = $('#region').val();
                     var residance = $('#residance').val();

                     $.ajax({
                      url:'./clientss.php',
                      data:'firstname='+firstname+'&lastname='+ lastname+'&phone='+ phone +'&product='+product+'&partner='+ partner +'&resa='+ resa +'&erand='+erand+'&district='+district+'&region='+region+'&residance='+residance ,
                      type: "POST",
                      async: true,
                      success: function(data) {
                      $('#response').html(data);
                      console.log(data);
                       }
                       ,error:function (data,status,response) {
                         console.log(data);
                         console.log(status);
                         console.log(response);
                       }
                     });
                   }    

            });
        