$(document).ready(function () {
	$("#search-text").keypress(function(e) {
		if(e.which == 13) {
	        document.location.href = '/search?q=' + $("#search-text").val();
	    }
	});
});