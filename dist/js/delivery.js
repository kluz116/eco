
            $(document).ready(function(){
               $('#addDelivery').on('click', function(e){
                // We don't want this to act as a link so cancel the link action
                e.preventDefault();

                 addClient();
               });
               function addClient () {
                     
                     var customer = $('#customer').val();
                     var resa = $('#resa').val();
                     var product= $('#product').val();
                     var plate= $('#plate').val();
                     var date = $('#date').val();
                     var erand = $('#erand').val();

                     $.ajax({
                      url:'./delievery.php',
                      data:'customer='+customer+'&resa='+resa+'&product='+ product +'&plate='+ plate +'&date='+date+'&erand='+erand ,
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
        