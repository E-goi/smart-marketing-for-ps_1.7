$(document).ready(function() {
	
	$('#add-list').click(function(e) {
		e.preventDefault();

	 	var title = $('#egoi_ps_title');
		if (title.val()) {
			return $(this).submit();
		}

		$("html, body").animate({ scrollTop: $(document).height() }, 1000);
		title.focus();
		return false;
	});

});