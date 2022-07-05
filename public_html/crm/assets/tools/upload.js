$(document).ready(function(){
    
	$('#input_file').change(function (event) {
		resetTextInvalid();
		var files = $(this)[0].files;
		if(files.length > 0) {
			var f = files[0];
			$("#label_file").text(f.name);
			checkFile(f);
		}
    });

    $(".btnupload").click(function(){
        resetTextInvalid();
        var fd = new FormData();
        var files = $('#input_file')[0].files;
		var updateButton = $('.btnupload');
		var urlOnSuccess = updateButton.attr('data-url-onsuccess');
		var urlUpload = updateButton.attr('data-url-upload');
		if(files.length > 0) {
			if( ! checkFile(files[0])) {
				return;
			}
			fd.append('logo', files[0]);
			$.ajax({
				url: urlUpload,
				type: 'post',
				data: fd,
				contentType: false,
				processData: false,
				beforeSend: function(xhr) { xhr.setRequestHeader('X-ClientId', 'Web'); },
				success: function(response) {
				if(typeof uploadRequestDebug === "boolean" && uploadRequestDebug){
					console.log(files[0], response);
					return;
				}
					location.assign(urlOnSuccess);
				},
				error: function(data) {
					appendTextInvalid(data.responseJSON.message);
				}
           });
        }
    });
});

function checkFile(file) {
	var accept = true;
	var maxSize = $('#input_file').attr('data-max-size');
	if(maxSize && file.size > maxSize) {
		accept = false;
		appendTextInvalid('File size too large: ' + Math.floor(file.size / 1024) + 'kb > ' + Math.floor(maxSize / 1024) + 'kb');
	}
	var type = file.type;
	if(type && ! type.startsWith('image/')) {
		accept = false;
		appendTextInvalid('Not an image');
	}
	return accept;
}


function appendTextInvalid(message) {
	var textinvalidElement = $('#txtvalid-file');
	textinvalidElement.addClass('is-invalid');
	var h = textinvalidElement.html();
	if(h != '') {
		h += '<br>';
	}
	textinvalidElement.html(h + message);
	textinvalidElement.show();
}

function resetTextInvalid() {
	var textinvalidElement = $('#txtvalid-file');
	textinvalidElement.removeClass('is-invalid');
	textinvalidElement.text('');
	textinvalidElement.hide();
}