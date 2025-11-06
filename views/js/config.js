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
		$("#api_key_success").hide();
		$("#api_key_error").hide();

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
						$("#api_key_error").fadeIn();
					}else{
						$('#egoi_client_id').val(data.general_info.client_id);
						$("#apikey_submit").prop('disabled', false).show();
						$('#error').hide();
						$("#api_key_success").fadeIn();
					}
				},
				error:function(status){
					if(status){
						$("#apikey_submit").prop('disabled', true).hide();
						$(".sync_api_key").hide();
						$("#error").prop('style', 'display:inline-block');
						$("#api_key_error").fadeIn();
					}
				}
			});

		}else{

			$(".sync_api_key").hide();
			$("#apikey_submit").prop('disabled', true).hide();
			$('#error').hide();
		}
	});

	// Handle Debug Mode toggle
	$('input[name="EGOI_DEBUG_MODE"]').on('change', function() {
		var debugValue = $(this).val();

		$('.sync_debug_mode').prop('style', '');
		$('#debug_error').hide();
		$("#debug_mode_success").hide();
		$("#debug_mode_error").hide();

		$.ajax({
			type: 'POST',
			dataType: 'JSON',
			data: {
				EGOI_DEBUG_MODE: debugValue
			},
			success: function(data, status) {
				// Hide loader
				$('.sync_debug_mode').hide();
				$('#debug_error').hide();

				if (data.success) {
					// Show success message
					$("#debug_mode_success").fadeIn();
				} else {
					// Show error if success is false
					$("#debug_mode_error").fadeIn();
				}
			},
			error: function(xhr, status, error) {
				// Hide loader, show error message
				$('.sync_debug_mode').hide();
				$('#debug_error').prop('style', 'display:inline-block');
				$("#debug_mode_error").fadeIn();
			}
		});
	});
});
