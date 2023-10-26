/**
 * Smart Marketing
 *
 *  @author    E-goi
 *  @copyright 2018 E-goi
 *  @license   LICENSE.txt
 */

$(document).ready(function($) {
	
	var token = $('#subtab-Account > a').attr('href');
	$('#account_button').prop('href', token);

	$('#edit_key').on('click', function (){
		$('#smart_api_key')
			.prop('type', 'text')
			.prop('disabled', false);

		$('#smart_api_key').focus();
		$(this).hide();
	});

	$("#smart_api_key").on('input', function(e) {

		var api_key = $(this).val();

		$(".sync_api_key").prop('style', '');
		$("#error").hide();

		if(api_key.length === 40){

			$.ajax({
			    type: 'POST',
			    dataType: 'JSON',
			    data:({
			        api_key: api_key
			    }),
			    success:function(data, status) {
			        $(".sync_api_key").hide();
			        if(status == '403'){
			        	$("#apikey_submit").prop('disabled', true).hide();
			        	$("#error").prop('style', 'display:inline-block');
			        }else{
			        	$('#egoi_client_id').val(data.general_info.client_id);
			        	$("#apikey_submit").prop('disabled', false).show();
			        	$('#error').hide();
			        }
			    },
			    error:function(status){
			    	if(status){
				    	$("#apikey_submit").prop('disabled', true).hide();
				    	$(".sync_api_key").hide();
				    	$("#error").prop('style', 'display:inline-block');
				    }
			    }
			});

		}else{

			$(".sync_api_key").hide();
			$("#apikey_submit").prop('disabled', true).hide();
			$('#error').hide();
		}
	})
});
