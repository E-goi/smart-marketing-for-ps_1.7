$(document).ready(function() {

	$("#home1").on('click', function() {
		$('#popup_form').show();
	});
	$("#home2").on('click', function() {
	    $('#popup_form').hide();
	});

	$("#footer0").on('click', function() {
	    $('#help_footer').show();
	});
	$("#footer1").on('click', function() {
	    $('#help_footer').hide();
	});

	$("#popup1").on('click', function() {
	    $('#once').show();
	});
	$("#popup2").on('click', function() {
	    $('#once').hide();
	});

	$('#add-form').on('click', function() {
		var url = $(this).data('href');
		return window.location.replace(url);
	});

	$('#formid_egoi').on('change', function() {
		var url = $(this).val();
		if(url) {
			$('#prev_iframe').prop('src', '//'+url);
            $('#preview').modal('show');
		}
	});

	$('.del-form').on('click', function(e) {
		e.preventDefault();

		var text = $('#del-info').text();
		var url = $(this).attr('href');
		if (confirm(text)) {
			return window.location.replace(url);
		}
		return false;
	});
});

function form_egoi(args){
	var arg = args;
	var panel = document.getElementById('panel_egbody');
	var edit = document.getElementById('editor_egoi');
	var msg = document.getElementById('msg_egoi');
	var sett = document.getElementById('settings_egoi');
	var wid = document.getElementById('widgets_egoi');

	var editor_link = document.getElementById('editor_link');
	var msg_link = document.getElementById('msg_link');
	var sett_link = document.getElementById('sett_link');
	var widget_link = document.getElementById('widget_link');

	if(arg == '1'){
		edit.style.display = 'block';
		editor_link.className += ' active';
			msg.style.display = 'none';
			msg_link.className = 'list-group-item';
			sett.style.display = 'none';
			sett_link.className = 'list-group-item';
			wid.style.display = 'none';
			widget_link.className = 'list-group-item';
				panel.style.height = '';

	}else if(arg == '2'){
		msg.style.display = 'block';
		msg_link.className += ' active';
			edit.style.display = 'none';
			editor_link.className = 'list-group-item';
			sett.style.display = 'none';
			sett_link.className = 'list-group-item';
			wid.style.display = 'none';
			widget_link.className = 'list-group-item';
				panel.style.height = '';

	}else if(arg == '3'){
		sett.style.display = 'block';
		sett_link.className += ' active';
			msg.style.display = 'none';
			msg_link.className = 'list-group-item';
			edit.style.display = 'none';
			editor_link.className = 'list-group-item';
			wid.style.display = 'none';
			widget_link.className = 'list-group-item';
				panel.style.height = '';

	}else if(arg == '4'){
		wid.style.display = 'block';
		widget_link.className += ' active';
			msg.style.display = 'none';
			msg_link.className = 'list-group-item';
			edit.style.display = 'none';
			editor_link.className = 'list-group-item';
			sett.style.display = 'none';
			sett_link.className = 'list-group-item';
				panel.style.height = '';
	}
}

function form_egoi_exc(args){
	var arg = args;
	var panel = document.getElementById('panel_egbody');
	var edit = document.getElementById('editor_egoi');
	var sett = document.getElementById('settings_egoi');
	var wid = document.getElementById('widgets_egoi');

	var editor_link = document.getElementById('editor_link');
	var sett_link = document.getElementById('sett_link');
	var widget_link = document.getElementById('widget_link');

	if(arg == '1'){
		edit.style.display = 'block';
		editor_link.className += ' active';
			sett.style.display = 'none';
			sett_link.className = 'list-group-item';
			wid.style.display = 'none';
			widget_link.className = 'list-group-item';
				panel.style.height = '';

	}else if(arg == '3'){
		sett.style.display = 'block';
		sett_link.className += ' active';
			edit.style.display = 'none';
			editor_link.className = 'list-group-item';
			wid.style.display = 'none';
			widget_link.className = 'list-group-item';
				panel.style.height = '';

	}else if(arg == '4'){
		wid.style.display = 'block';
		widget_link.className += ' active';
			edit.style.display = 'none';
			editor_link.className = 'list-group-item';
			sett.style.display = 'none';
			sett_link.className = 'list-group-item';
				panel.style.height = '';
	}
}