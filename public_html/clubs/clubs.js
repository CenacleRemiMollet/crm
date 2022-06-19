var clubHBTemplate = Handlebars.compile(document.getElementById("club-template").innerHTML);
var clubs;
var volatileClubIndex = 0;

var url_starter = 'https://cenaclerm.fr';

function populateClubs() {
	$.ajax({
		beforeSend: function(request) {
			request.setRequestHeader("Accept", 'application/json');
		},
		dataType: "json",
		url: url_starter + '/ng/api/clubs/',
		success: function(data) {
			clubs = data.sort(function (a, b) {
	    	return a.city.localeCompare(b.city);
			});
			var content = "";
			$.each(clubs, function(key, val) {
				val.id = volatileClubIndex++;
				// image
				if(val.image_url == null) {
					val.image_url = url_starter + '/param_clubs/logo_club/logocenaclerm.gif';
				}
				// url
				val.url = val.website_url;
				if(val.url == null) {
					val.url = val.facebook_url;
				}
				console.log(val.city, val.url);
				// generate
				content += clubHBTemplate(val);
				// prepare search
				val.name = removeAccentsAndToLowerCase(val.name, '');
				val.city = removeAccentsAndToLowerCase(val.city, '');
				val.department = removeAccentsAndToLowerCase(val.department, '');
			});
			$('#searchclubinput').show(); // display search bar
			document.getElementById("club-row").innerHTML = content; // display table
		}
	});
	}

function removeAccentsAndToLowerCase(str, valueIfNull) {
	 return str ? str.normalize('NFD').replace(/[\u0300-\u036f]/g, "").toLowerCase() : valueIfNull;
}

$(document).ready(function() {
	populateClubs();
	
	$("#searchclubinput").on('input',function(e) {
		var q = removeAccentsAndToLowerCase(this.value);
		console.log(q);
		$.each(clubs, function(key, val) {
			var dvcjq = $("#club-" + val.id);
			if(q == null || val.name.includes(q) || val.city.includes(q) || val.department.includes(q)) {
				dvcjq.show();
			} else {
				dvcjq.hide();
			}
		});
	});
});
