function modifyJsonReformat(json) {
	delete json['active'];
	json['active'] = $('#input_active')[0].checked;
	return json;
}