$(document).ready(function($) {
	
	var token = $('#subtab-Account > a').attr('href');
	$('#account_button').prop('href', token);

	$('#edit_key').on('click', function (){
		$('#smart_api_key')
			.prop('type', 'text')
			.prop('disabled', false);

		$('#smart_api_key').focus();
		$(this).hide();
	})

	$("#smart_api_key").on('input', function() {

		var api_key = $(this).val();

		$("#load").show();
		$("#error").hide();
		$("#valid").hide();

		if(api_key.length == 40){

			$.ajax({
			    type: 'POST',
			    dataType: 'JSON',
			    data:({
			        api_key: api_key
			    }),
			    success:function(data, status) {
			        
			        $("#load").hide();
			        if(status == '403'){
			        	$("#apikey_submit").hide();
			        	
			        	$("#error").show();
			        	$("#valid").hide();
			        }else{
			        	$('#egoi_client_id').val(data.CLIENTE_ID);
			        	$("#apikey_submit").show();
			        	
			        	$("#valid").show();
			        	$("#error").hide();
			        }
			    },
			    error:function(status){
			    	if(status){
				    	$("#apikey_submit").hide();
				    	
				    	$("#valid").hide();
				    	$("#error").show();
				    	$("#load").hide();
				    }
			    }
			});

		}else{
			$("#apikey_submit").hide();
			$("#valid").hide();
			$("#load").hide();
		}
	})
});