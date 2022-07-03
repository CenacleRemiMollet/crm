$(document).ready(function(){
    
	$('#input_file').change(function (event) {
		var files = $(this)[0].files;
		if(files.length > 0) {
			// TODO
		}
        console.log(file);
	
    });

    $(".btnupload").click(function(){
        var fd = new FormData();
        var files = $('#input_file')[0].files;
		var updateButton = $('.btnupload');
		var urlUpload = updateButton.attr('data-url-upload');
		console.log(files);
        return;
        

// Check file selected or not
        if(files.length > 0) {
			
	
           fd.append('file',files[0]);

           $.ajax({
              url: 'upload.php',
              type: 'post',
              data: fd,
              contentType: false,
              processData: false,
              success: function(response) {
                 if(response != 0){
                    $("#img").attr("src",response); 
                    $(".preview img").show(); // Display image element
                 }else{
                    alert('file not uploaded');
                 }
              },
           });
        } else {
           alert("Please select a file.");
        }
    });
});