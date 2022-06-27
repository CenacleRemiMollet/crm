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
				var roles = subsc['roles'];
				if(roles == null) {
					roles = [];
					subsc['roles'] = roles;
				}
				c = roles.push(key);
			} else {
				subsc[key] = value;
			}
			delete json[fullkey];
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
	$('.btnremovesubscribe').click(function(event){
		var parent = $(this);
		while(! parent.hasClass('divsubscribe')) {
			parent = parent.parent()
		}
		if(parent.hasClass('divsubscribe')) {
			parent.remove();
		}
	});
	
	$('.btnaddsubscribe').click(function(event){
		++subscId;
		var e = $('.divsubscribereference').clone();
		e.insertBefore($('.divsubscribereference'));
		e.html(e.html().replaceAll('${subscid}', 'x' + subscId));
		e.removeClass('divsubscribereference');
		e.show();
		e.css("visibility", "visible");
	});
});
