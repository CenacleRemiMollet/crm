$(document).ready(function () {
	$("#btn-modify-top-info").on('click', function(e) {
		toggleToModify(true, 'info');
	});
	$("#btn-modify-bottom-info").on('click', function(e) {
		toggleToModify(true, 'info');
	});

	$("#btn-cancel-top-info").on('click', function(e) {
		toggleToModify(false, 'info');
		// TODO revert value
	});
	$("#btn-cancel-bottom-info").on('click', function(e) {
		toggleToModify(false, 'info');
		// TODO revert value
	});
	
	$("#submitUpdate-top-info").on('click', function() {
		$("#user_form-info").submit();
	});
	$("#submitUpdate-bottom-info").on('click', function() {
		$("#user_form-info").submit();
	});
	
	$("#user_form-info").on('submit', function(e) {
		var submitUpdateTopInfo = $("#submitUpdate-top-info");
		var submitUpdateBottomInfo = $("#submitUpdate-bottom-info");
		updateInfo($(this), e, submitUpdateTopInfo, submitUpdateBottomInfo);
	});

});

function toggleToModify(enableModify, tab) {
	$("#btn-modify-top-" + tab).toggle(! enableModify);
	$("#btn-modify-bottom-" + tab).toggle(! enableModify);
	$("#btn-cancel-top-" + tab).toggle(enableModify);
	$("#btn-cancel-bottom-" + tab).toggle(enableModify);
	$("#submitUpdate-top-" + tab).toggle(enableModify);
	$("#submitUpdate-bottom-" + tab).toggle(enableModify);
	
	$("#user_form-" + tab + " :input").each(function() {
		var type = $(this).prop('type');
		if(type != 'button' ) {
			//console.log('toggle to modify OK ', '[' + type + ']', '[' + $(this).prop('id') + ']');
			$(this).prop("readonly", ! enableModify);
			$(this).toggleClass('form-control', enableModify);
			$(this).toggleClass('form-control-plaintext', ! enableModify);
			if(! enableModify) {
				$(this).val($(this).data("previous"));
			}
			if(type == 'radio') {
				var parent = $(this).parent();
				parent.toggleClass('disabled', ! enableModify);
				if(enableModify) {
					parent.removeClass('btn-disabled-active');
				} else {
					parent.toggleClass('btn-disabled-active', parent.data("previous") == 1);
				}
				parent.parent().toggleClass('disabled', ! enableModify);
			}
		}
	});
}

function updateInfo(form, e, submitUpdateTop, submitUpdateBottom) {
	var formId = form.attr('id');
	resetValidation(formId);
	disableButton(submitUpdateTop);
	disableButton(submitUpdateBottom);
	var postData = form.serializeObject();
	var formURL = form.attr("action");
	var formMethod = form.attr("method");
	//console.log(postData);
	$.ajax({
		url: formURL,
		type: formMethod,
		data: JSON.stringify(postData),
		dataType : 'json',
		contentType: 'application/json',
		success: function(data2, textStatus, jqXHR) {
			console.log('OK with ', postData);
			handleOk(formId, jqXHR.responseJSON);
			toggleToModify(false, 'info');
		},
		error: function(jqXHR, status, error) {
			//console.log('ERRRRROR', status + ": " + error, jqXHR.responseJSON);
			handleError(formId, jqXHR.responseJSON);
		}
	});
	e.preventDefault();
	//enableButton(submitCreateButton);
	enableButton(submitUpdateTop);
	enableButton(submitUpdateBottom);
}

function enableButton(button) {
	button.prop('disabled', false);
	button.removeClass('disabled');
}

function disableButton(button) {
	button.prop('disabled', true);
	button.addClass('disabled');
}

function resetValidation(formId) {
	$("#" + formId + " .form-control").removeClass("is-valid");
	$("#" + formId + " .form-control").removeClass("is-invalid");
	$('.invalid-feedback').toggle(false);
}

function handleOk(formId, okJson) {
	$("#" + formId + " :input").each(function() {
		var type = $(this).prop('type');
		if(type != 'button' ) {
			console.log('handleOk', type);
			if(type == 'radio') {
				console.log($(this).prop( "checked" ));
				//$('#filterDay input:radio:checked').val()
			} else {
				$(this).data("previous", $(this).val());
			}
		}
	});
}

function handleError(formId, errorJson) {
	var form = document.getElementById(formId);
	var data = errorJson.extra;
	Object.keys(data).forEach(function(k) {
		var input = $("#" + formId + " [name='" + k + "']");
		//console.log('input', k, input.attr('type'));
		if(input) {
			input.addClass("is-invalid");
			var findFrom = input.parent();
			if(input.attr('type') == 'radio') {
				findFrom = findFrom.parent().parent();
			}
			var textElement = findFrom.find(".invalid-feedback");
			console.log(k + ' - ' + data[k], input, textElement);
			if(textElement) {
				textElement.text(data[k]);
				textElement.toggle(true);
				input.prop('invalid', true);
				valid = false;
			}
		}
	});
	//console.log(form, form.checkValidity());
	event.preventDefault();
	event.stopPropagation();
}