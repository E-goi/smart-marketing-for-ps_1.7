$(document).ready(function() {
	
	$("#home1").click(function() {
		$('#popup_form').show();
	});
	$("#home2").click(function() {
	    $('#popup_form').hide();
	});

	$("#footer0").click(function() {
	    $('#help_footer').show();
	});
	$("#footer1").click(function() {
	    $('#help_footer').hide();
	});

	$("#popup1").click(function() {
	    $('#once').show();
	});
	$("#popup2").click(function() {
	    $('#once').hide();
	});

	$('#formid_egoi').change(function() {
		var e = document.getElementById('formid_egoi');
		var result = e.options[e.selectedIndex].value;

		if(result != ''){
			$.ajax({
			    url: 'http://'+window.location.host+'/modules/egoiforps/includes/forms.php',
			    type: 'POST',
			    data:({
			        url: result
			    }),
			    success:function(data, status) {
			        $('#egoi_form_inter').html(data);
			        $('#preview').modal('show');
			    },
			    error:function(status){
			    	if(status){
				    	$("#egoi_ps_key").attr('disabled', 'disabled');
				    	$("#valid").hide();
				    	$("#error").show();
				    }
			    }
			});
		}

	});
});