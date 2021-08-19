$(document).ready(function() {
	$('#language').change(function() {
		// true : en
		// false : fr
		lang = this.checked ? 'en' : 'fr';
		//console.log("Change locale to " + lang);
		$.ajax({
			url:        '/locale',
			type:       'PUT',
			dataType:   'json',
			data:       JSON.stringify({'locale': lang}),
			async:      true,
			success: function(data, status) {
			    //console.info("data", data);
			    location.reload();
			},
			error : function(xhr, textStatus, errorThrown) {
				console.info("error", xhr, textStatus, errorThrown);
			}
		});
	});
});
