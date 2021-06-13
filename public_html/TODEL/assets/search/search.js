$(document).ready(function () {
	$('#table-search > tbody > tr').click(function(e) {
    	document.location.href = e.target.parentNode.dataset.url;
	});

});
