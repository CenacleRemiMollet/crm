function modifyJsonReformat(json) {
	var subscMap = {};
	for (const [fullkey, value] of Object.entries(json)) {
		if(fullkey.startsWith("subscribe-")) {
			var index = fullkey.indexOf('-', 11);
			var uuid = fullkey.substring(10, index);
			var subsc = subscMap[uuid];
			if(subsc == null) {
				subsc = {};
				subscMap[uuid] = subsc;
				subsc['uuid'] = uuid;
			}
			var key = fullkey.substring(index + 1);
			if(key.startsWith('role_')) {
				var subscRoles = subsc['roles'];
				if(subscRoles == null) {
					subscRoles = [];
					subsc['roles'] = subscRoles;
				}
				c = subscRoles.push(key);
			} else {
				subsc[key] = value;
			}
			delete json[fullkey];
		}
		if(fullkey == "admin_check_exists") {
			if($('#input_admin_check_exists').length > 0) {
				var roles = [];
				delete json[fullkey];
				delete json['admin_checkbox'];
				if($('#input_admin_checkbox')[0].checked) {
					roles.push('ROLE_ADMIN');
				}
				delete json['input_admin_checkbox'];
				json['roles'] = roles;
			}
		}
	}
	var subscList = [];
	for (const [uuid, subsc] of Object.entries(subscMap)) {
		subscList.push(subsc);
	}
	json['subscribes'] = subscList;
	return json;
}

var subscId = 0;

$(document).ready(function(){	
	$('.btnremovesubscribe').on("click", function(event){
		removeSubscribe($(this));
	});
	
	$('.btnaddsubscribe').on("click", function(event){
		++subscId;
		var e = $('.divsubscribereference').clone();
		e.insertBefore($('.divsubscribereference'));
		e.html(e.html().replaceAll('${subscid}', 'x' + subscId));
		e.removeClass('divsubscribereference');
		e.addClass('btnremovesubscribe' + subscId)
		e.show();
		e.css("visibility", "visible");
		
		$('.btnremovesubscribe' + subscId).on("click", function(event){
			removeSubscribe($(this));
		});

	});
});

function removeSubscribe(parent) {
	while(! parent.hasClass('divsubscribe')) {
		parent = parent.parent()
	}
	if(parent.hasClass('divsubscribe')) {
		parent.remove();
	}
}
