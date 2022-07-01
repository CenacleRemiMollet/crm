$(document).ready(function() {
	if($('#filter-by-club').length > 0) {
		$('#filter-by-club').on('change', function() {
			var club = $('#filter-by-club').val();
			var href = getURLForSearch(); // in tools/modif-list.js
			if(club == "") {
				href.searchParams.delete('club');
			} else {
				href.searchParams.set('club', club);
			}
			location.href = href.toString();
		});
	}
});