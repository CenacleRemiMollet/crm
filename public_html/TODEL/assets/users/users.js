var countCreated = 0;

$(document).ready(function () {
	//var submitCreateButton = $("#submitCreate");
	var submitCreateAndAddNewButton = $("#submitCreateAndAddNew");
	$("#student_form").on("submit", function(e) {
		//disableButton(submitCreateButton);
		disableButton(submitCreateAndAddNewButton);
		var postData = $(this).serializeObject();
		var formURL = $(this).attr("action");
		$.ajax({
			url: formURL,
			type: "POST",
			data: JSON.stringify(postData),
			dataType : 'json',
			contentType: 'application/json',
			success: function(data2, textStatus, jqXHR) {
				++countCreated;
				handleOk("student_form", jqXHR.responseJSON);
				//console.log('OK with ', postData);
			},
			error: function(jqXHR, status, error) {
				console.log('ERRRRROR', status + ": " + error, jqXHR.responseJSON);
				handleError("student_form", jqXHR.responseJSON);
			}
		});
		e.preventDefault();
		//enableButton(submitCreateButton);
		enableButton(submitCreateAndAddNewButton);
	});
	/*$("#submitCreate").on('click', function() {
		$("#student_form").trigger("reset");
		//$("#student_form").submit();
	});*/
	$("#submitCreateAndAddNew").on('click', function() {
		$("#student_form").submit();
	});
	
    $('#table-users > tbody > tr').click(function(e) {
    	document.location.href = '/user/' + e.target.parentNode.id;
    	//console.log('/user/' + e.target.parentNode.id);
	});

});


function enableButton(button) {
	button.prop('disabled', false);
	button.removeClass('disabled');
}

function disableButton(button) {
	button.prop('disabled', true);
	button.addClass('disabled');
}

function handleOk(formName, jqXHR) {
	$("#student_form").trigger("reset");
	var user = jqXHR.user;
	console.log('user', user);
	if(user) {
		var lastname = user.lastname;
		var firstname = user.firstname;
		console.log('lastname', lastname, firstname, firstname);
		if(lastname && firstname) {
			console.log('lastname', lastname, firstname, firstname);
			$("#created_message").css('display', '');
			$("#name_created").text(lastname + ' ' + firstname);
		}
	}
	resetValidation(formName);
}

function resetValidation(formName) {
	$("#" + formName + " .form-control").removeClass("is-valid");
	$("#" + formName + " .form-control").removeClass("is-invalid");
}

function handleError(formName, errorJson) {
	var form = document.getElementById(formName);
	resetValidation(formName);
	var data = errorJson.extra;
	Object.keys(data).forEach(function(k) {
		var input = $("#" + formName + " [name='" + k + "']");
		if(input) {
			input.addClass("is-invalid");
			console.log(k + ' - ' + data[k], input);
			var textElement = input.parent().find(".invalid-feedback");
			if(textElement) {
				textElement.text(data[k]);
				input.prop('invalid', true);
				valid = false;
			}
		}
	});
	
	//console.log(form, form.checkValidity());
	event.preventDefault();
	event.stopPropagation();
}

$('#createStudentModal').on('hidden.bs.modal', function (e) {
	if(countCreated > 0) {
		location.reload();
	}
})

