$(document).ready(function(){	
	$('.btnmodify').click(function(event){
		$('*').each(function( index ) {
          $(this).removeClass('is-invalid');
          if($(this).hasClass('invalid-feedback')) {
          	$(this).text('');
          }
        });

		var updateButton = $('.btnmodify');
		var urlUpdate = updateButton.attr('data-url-update');
		var urlOnSuccess = updateButton.attr('data-url-onsuccess');
		var data = convertFormToJSON($('form#myForm'));
		
		if(typeof modifyDebug === "boolean" && modifyDebug){
			console.log("Before reformat :", data);
		}
		
		if(typeof modifyJsonReformat === "function"){
			data = modifyJsonReformat(data);
		}
		if(typeof modifyDebug === "boolean" && modifyDebug){
			console.log("After reformat :", data);
			return;
		}
		
		$.ajax({
    		type: 'PATCH',
    		url: urlUpdate,
    		contentType: 'application/json',
    		data: JSON.stringify(data),
    		cache: false,
    		dataType: 'json',
    		beforeSend: function(xhr) { xhr.setRequestHeader('X-ClientId', 'Web'); },
    		success: function(response) {
				if(typeof modifyRequestDebug === "boolean" && modifyRequestDebug){
					console.log(data);
					return;
				}
    			location.assign(urlOnSuccess);
    		},
    		error: function(data) {
    			var details = data.responseJSON.details;
    			if(details) {
    				Object.keys(details).forEach(key => {
						console.log(key, details[key]);
						var valido = $('#txtvalid-' + key);
						valido.show();
						valido.text(details[key]);
						$('#input-' + key).addClass('is-invalid');
					});
    			}
    		}
    	});
	});
	
	$('.btncreate').click(function(event){
		$('*').each(function( index ) {
          $(this).removeClass('is-invalid');
          if($(this).hasClass('invalid-feedback')) {
          	$(this).text('');
          }
        });

		var createButton = $('.btncreate');
		var urlCreate = createButton.attr('data-url-create');
		var urlOnSuccess = createButton.attr('data-url-onsuccess');
		
		$.ajax({
    		type: 'POST',
    		url: urlCreate,
    		contentType: 'application/json',
    		data: JSON.stringify(convertFormToJSON($('form#myForm'))),
    		cache: false,
    		dataType: 'json',
    		beforeSend: function(xhr) { xhr.setRequestHeader('X-ClientId', 'Web'); },
    		success: function(response){
    			location.assign(urlOnSuccess);
    		},
    		error: function(data) {
    			var details = data.responseJSON.details;
    			if(details) {
    				Object.keys(details).forEach(key => {
						//console.log(key, details[key]);
						var valido = $('#txtvalid-' + key);
						valido.show();
						valido.text(details[key]);
						$('#input-' + key).addClass('is-invalid');
					});
    			}
    		}
    	});
	});

});

function convertFormToJSON(form) {
	return form
			.serializeArray()
			.reduce(function (json, { name, value }) {
		if(name.indexOf('$') < 0) {
			var format = $('input[name=' + name + ']').attr('data-format');
			if('float' == format) {
				value = value ? parseFloat(value) : null;
			}
			if(value != null) {
				Object.assign(json, {[name]: value});
			}
		}
		return json;
    }, {});
}