
            $(document).ready(function(){
               $('#accepted').on('click', function(e){
                // We don't want this to act as a link so cancel the link action
                e.preventDefault();

                 addClient();
               });
               function addClient () {
                     
                     var customer= $('#customer').val();
                     var phone = $('#phone').val();
                     var order_status = $('#order_status').val();
                  
                     $.ajax({
                      url:'./approveItems.php',
                      data:'customer='+customer+'&phone='+ phone+'&order_status='+ order_status ,
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
        