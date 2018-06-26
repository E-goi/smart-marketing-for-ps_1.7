jQuery.fn.show = function() {
	return this.prop('style', 'display:inline-block');
};

$(document).ready(function() {

	var listID = $('#egoi_lists').val();
	var sub_egoi = "Subscribed in E-goi (Active)'";
	var sub_ps = "Subscribed in PrestaShop";

   	$.ajax({
	    type: 'POST',
	    data:({
	        action: 'synchronize',
        	list: listID
	    }),
    	success:function(data, status) {
    		resp = JSON.parse(data);
    		egoi = resp[0];
    		jm = resp[1];
    		$('#egoi_sinc_users_ps').hide();
    		$('#sync').html(sub_egoi+': <span class="help"><b>'+egoi+'</b></span><p>'+sub_ps+': <span class="help"><b>'+jm+'</b></span><p>');
    	}
    });

	$("#sync_old_subs").on('click', function() {

		$('.sync_customers').show();
        $('#sync_success').hide();
		var btn_sync = $(this);
		btn_sync.prop('disabled', true);

		$.ajax({
		    type: 'POST',
		    data:({
		        token_list: '1',
		        subs: 'OK'
		    }),
		    success:function(data, status) {
		    	if (data) {
		    		btn_sync.prop('disabled', false);
		    		$('.sync_customers').hide();
		    		$('#sync_success').show();
		    	}
		    },
		    error:function(status){
		    	btn_sync.prop('disabled', false);
		    	$('.sync_customers').hide();
                $('#sync_success').hide();
		    }
		});
	});

	$('#ps_fields').change(function() {
		if(($(this).val() != '') && ($('#egoi').val() != '')){
			$('#save_map_fields').prop('disabled', false);
		}else{
			$('#save_map_fields').prop('disabled', true);
		}
	});

	$('#egoi').change(function() {
		if(($(this).val() != '') && ($('#ps_fields').val() != '')){
			$('#save_map_fields').prop('disabled', false);
		}else{
			$('#save_map_fields').prop('disabled', true);
		}
	});

	$('#save_map_fields').click(function() {
		
		var $ps = $('#ps_fields');
		var $ps_name = $('#ps_fields option:selected');
		var $egoi = $('#egoi');
		var $egoi_name = $('#egoi option:selected');

		if(($ps.val() !== '') && ($egoi.val() !== '')) {

			$('#load_map').show();

			$.ajax({
			    type: 'POST',
			    data:({
			        ps: $ps.val(),
			        ps_name: $ps_name.text(),
			        egoi: $egoi.val(),
			        egoi_name: $egoi_name.text(),
			        token_egoi_api: 1
			    }),
			    success:function(data, status) {
			    	if(data){
			    		$(data).appendTo('#all_fields_mapped');
			    		$("#error_map").hide();
			    	}else{
			    		$("#error_map").show();
			    	}
			    	$ps.val('');
			    	$egoi.val('');
			    	$('#save_map_fields').prop('disabled', true);
			       	
			       	$('#load_map').hide();
			    },
			    error:function(status){
			    	if(status){
				    	$("#error_map").show();
				    	$('#load_map').hide();
				    }
			    }
			});
		}

	});


	$('.egoi_fields').live('click', function(){

		var id = $(this).data('target');
		var tr = 'egoi_fields_'+id;
		//$('#load_map').show();
		
		$.ajax({
		    type: 'POST',
		    data:({
		        id_egoi: id
		    }),
		    success:function(data, status) {
		       $('#'+tr).remove();
		       //$('#load_map').hide();
		    },
		    error:function(status){
		    	if(status){
			    	$("#error").show();
			    	//$('#load_map').hide();
			    }
		    }
		});

	});
});