$(function () {
	// body...
	var form = $("#contactForm");

	form.submit(function(e){
		$(this).attr("disable", "disable");
		e.preventDefault();

		$.ajax({
			type: form.attr("method"), // post
			url: "{{ path('sendmail') }}", //send to contactForm.php
			data: form.serialize(), // values of all field
			dataType: "json", // set answer data type to json
			success: function(data) {
				alert(data.content);
				inputNoText();
				$('button:submit').removeAttr("disable");
			},
			error: function(data) {
				alert("Une erreur est survenue.");
				$('button:submit').removeAttr("disable");
			}
		});
	});

	function inputNoText() {
		// body...
		$('#yrName').val("");
		$('#yrEmail').val("");
		$('#subject').val("");
		$('#message').val("");
	}
});