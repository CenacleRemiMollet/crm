$(document).ready(function(){
	$('.btndelete').click(function() {
		var uuid = $(this).attr('data-uuid');
		var message = '';
		$('.deletetext-' + uuid).each(function( index ) {
          message += $(this).html() + '<br>';
        });
		$('.modal-body').html(message);
	});
	$('.btnconfirmdelete').click(function() {
		console.log('fff');
	});
	//$('#deleteModal').on('hidden.bs.modal', function (e) {
      // do something...
      //console.log('hide');
    //});
});
