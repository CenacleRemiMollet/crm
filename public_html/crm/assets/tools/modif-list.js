$(document).ready(function(){
	$('.btndelete').click(function() {
		var uuid = $(this).attr('data-uuid');
		var message = '';
		$('.deletetext-' + uuid).each(function( index ) {
          message += $(this).html() + '<br>';
        });
		$('.modal-body').html(message);
		$('.btnconfirmdelete').attr('data-uuid', uuid);
	});

	$('#myModal').on('show.bs.modal', function (e) {
		$('#txtvalid-delete').hide();
		$('.btnconfirmdelete').prop('disabled', false);
    });
	
	$('.btnconfirmdelete').click(function() {
		var confirmDeleteButton = $('.btnconfirmdelete');
		var uuid = confirmDeleteButton.attr('data-uuid');
		var urlPrefix = confirmDeleteButton.attr('data-url-prefix');
		if(urlPrefix == null) {
			$('#txtvalid-delete').show();
			$('#txtvalid-delete').text("'data-url-prefix' undefined");
			return;
		}
		confirmDeleteButton.prop('disabled', true);
		$.ajax({
    		type: 'DELETE',
    		url: urlPrefix + '/' + uuid,
    		cache: false,
    		dataType: 'json',
    		beforeSend: function(xhr) { xhr.setRequestHeader('X-ClientId', 'Web'); },
    		success: function(response){
    			$('#myModal').modal('hide');
				confirmDeleteButton.prop('disabled', false);
				location.reload();
    		},
    		error: function(data) {
    			$('#txtvalid-delete').show();
				$('#txtvalid-delete').text(data.responseJSON.error + ' : ' + data.responseJSON.message);
				confirmDeleteButton.prop('disabled', false);
    		}
    	});
	});
});
