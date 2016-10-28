$(document).ready(function() {


$('#add_desc').on('click', function(e){
  
    e.preventDefault();

    addInventory();
  });

function addInventory () {

	  var editor1 = $('#editor1').val();
	  var product= $('#product').val();

       $.ajax({
        url: 'add_desc.php',
       data: 'product='+ product+'&editor1='+editor1,
       type: "POST",
       cache: false,  
       success: function(data) {
        $('#response').html(data);
        //location.reload();
       }
    }); 
}

});