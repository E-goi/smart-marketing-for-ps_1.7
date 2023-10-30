/**
 * Smart Marketing
 *
 *  @author    E-goi
 *  @copyright 2018 E-goi
 *  @license   LICENSE.txt
 */

jQuery.fn.show = function() {
	return this.prop('style', 'display:inline-block');
};

$(document).ready(function() {

    var totalPages = 0;
    var currentPage = 0;
    var pagesStores = [];
    var btn_sync = $("#sync_old_subs");
    var btn_news_sync = $("#sync_old_news_subs");
	var listID = $('#egoi_lists').val();
	var sub_egoi = $("#sub_in_egoi").text();
	var sub_ps = $("#sub_in_ps").text();

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
    		$('#egoi_sync_users_ps').hide();
    		$('#sync').html(sub_egoi+': <span class="help"><b>'+egoi+'</b></span><p>'+sub_ps+': <span class="help"><b>'+jm+'</b></span><p>');
    	}
    });

    function interaction(store,num){

        var subs = num;//numero do count
        var token_list = '1';

        $.ajax({
            type: 'POST',
            data:({
                token_list: token_list,
                subs: subs,
                newsletter: + $('#newsletter_only').is(':checked'),
                store_id: store
            }),
            success:function(data, status) {
                var json = JSON.parse(data);

                if (json.hasOwnProperty("error")) {

                    btn_sync.prop('disabled', false);
                    $('.sync_customers').hide();
                    $('#sync_success').hide();
                    $('#progressbarSync').hide();
                    totalPages = 0;
                    currentPage = 0;
                    pagesStores = [];

                    $('#sync_nousers').show();
                    window.setTimeout(function(){
                        $("#sync_nousers").fadeOut(400);
                    }, 6000);

                    return false;
                }

                currentPage++;

                $('#progressbarValues').attr("aria-valuenow",currentPage);
                $('#progressbarValues').width(Math.ceil(currentPage * 100 / totalPages) + "%");
                $('#progressbarValues').text( currentPage + "/" + totalPages);

                if(+json['imported'] >= getPagesByStore(store)){
                    var next = getNextStore(store);
                    if(next == false){
                        $('.loading').prop('id', 'valid');

                        setTimeout(function() {
                            $('#progressbarSync').hide();
                            $('#progressbarValues').width("0%");
                        }, 1000);

                        btn_sync.prop('disabled', false);
                        $('.sync_customers').hide();
                        $('#sync_success').show();

                        totalPages = 0;
                        currentPage = 0;
                        pagesStores = [];
                    }else{
                        interaction(next, 0);
                    }

                } else{
                    interaction(store,json['imported']);
                }

            },
            error:function(status){
                btn_sync.prop('disabled', false);
                $('.sync_customers').hide();
                $('#sync_success').hide();
                $('#progressbarSync').hide();
                totalPages = 0;
                currentPage = 0;
                pagesStores = [];
            }
        });


        return 0;
    }

    function interactionN(store,num){

        var subs = num;//numero do count
        var token_list = '1';

        $.ajax({
            type: 'POST',
            data:({
                token_list: token_list,
                subs: subs,
                newsletter: true,
                store_id: store
            }),
            success:function(data, status) {
                var json = JSON.parse(data);

                if (json.hasOwnProperty("error")) {
                    btn_sync.prop('disabled', false);
                    $('.sync_customers2').hide();
                    $('#sync_success2').hide();
                    $('#progressbarSync2').hide();

                    $('#sync_nousers2').show();
                    window.setTimeout(function(){
                        $("#sync_nousers2").fadeOut(400);
                    }, 6000);

                    totalPages = 0;
                    currentPage = 0;
                    pagesStores = [];
                    return false;
                }

                currentPage++;

                $('#progressbarValues2').attr("aria-valuenow",currentPage);
                $('#progressbarValues2').width(Math.ceil(currentPage * 100 / totalPages) + "%");
                $('#progressbarValues2').text( currentPage + "/" + totalPages);

                if(+json['imported'] >= getPagesByStore(store)){
                    var next = getNextStore(store);
                    if(next == false){
                        $('.loading').prop('id', 'valid');

                        setTimeout(function() {
                            $('#progressbarSync2').hide();
                            $('#progressbarValues2').width("0%");
                        }, 1000);

                        btn_news_sync.prop('disabled', false);
                        $('.sync_customers2').hide();
                        $('#sync_success2').show();

                        totalPages = 0;
                        currentPage = 0;
                        pagesStores = [];
                    }else{
                        interactionN(next, 0);
                    }

                } else{
                    interactionN(store,json['imported']);
                }

            },
            error:function(status){
                btn_sync.prop('disabled', false);
                $('.sync_customers2').hide();
                $('#sync_success2').hide();
                $('#progressbarSync2').hide();
                totalPages = 0;
                currentPage = 0;
                pagesStores = [];
            }
        });


        return 0;
    }

    btn_news_sync.on('click', function() {

        $('.sync_customers2').show();
        $('#sync_success2').hide();
        btn_news_sync.prop('disabled', true);
        $.ajax({
            type: 'POST',
            data:({
                size: 1,
            }),
            success:function(data, status) {
                var json = JSON.parse(data);
                json = pagesStores = calcPages(json);

                $('#progressbarSync2').show();
                $('#progressbarValues2').attr("aria-valuemax", totalPages);
                $('#progressbarValues2').width("0%");
                $('#progressbarValues2').text("0/" + totalPages);
                interactionN(json[0].id_shop,0);

            },
            error:function(status){
                btn_sync.prop('disabled', false);
                $('.sync_customers2').hide();
                $('#sync_success2').hide();
            }
        });
    });

    btn_sync.on('click', function() {
		$('.sync_customers').show();
        $('#sync_success').hide();
		btn_sync.prop('disabled', true);

        $.ajax({
            type: 'POST',
            data:({
                size: 1,
            }),
            success:function(data, status) {
                var json = JSON.parse(data);
                json = pagesStores = calcPages(json);
                $('#progressbarSync').show();
                $('#progressbarValues').attr("aria-valuemax", totalPages);
                $('#progressbarValues').width("0%");
                $('#progressbarValues').text("0/" + totalPages);
                interaction(json[0].id_shop,0);

            },
            error:function(status){
                btn_sync.prop('disabled', false);
                $('.sync_customers').hide();
                $('#sync_success').hide();
            }
        });
	});

	$('#ps_fields').on('change', function() {
		if(($(this).val() !== '') && ($('#egoi').val() !== '')){
			$('#save_map_fields').prop('disabled', false);
		}else{
			$('#save_map_fields').prop('disabled', true);
		}
	});

	$('#egoi').on('change', function() {
		if(($(this).val() !== '') && ($('#ps_fields').val() !== '')){
			$('#save_map_fields').prop('disabled', false);
		}else{
			$('#save_map_fields').prop('disabled', true);
		}
	});

	$('#save_map_fields').on('click', function() {
		
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
                        window.setTimeout(function(){
                            $("#error_map").fadeOut(400);
                        }, 4000);
			    	}
			    	$ps.val('');
			    	$egoi.val('');
			    	$('#save_map_fields').prop('disabled', true);
			       	
			       	$('#load_map').hide();
			    },
			    error:function(status){
			    	if(status){
				    	$("#error_map").show();
                        window.setTimeout(function(){
                            $("#error_map").fadeOut(400);
                        }, 4000);
				    	$('#load_map').hide();
				    }
			    }
			});
		}

	});


	$('.modal').on('click', '.egoi_fields', function(){

		var id = $(this).data('target');
		var tr = 'egoi_fields_'+id;

		$.ajax({
		    type: 'POST',
		    data:({
		        id_egoi: id
		    }),
		    success:function(data, status) {
		       $('#'+tr).remove();
		    },
		    error:function(status){
		    	if(status){
			    	$("#error").show();
			    }
		    }
		});

	});

    function calcPages(arr){
        var tot = 0;
        for (var i = 0;i<arr.length;i++){
            arr[i]['total'] = Math.ceil(arr[i]['total']/1000);
            tot += arr[i]['total'];
        }
        totalPages = tot;
        return arr
    }

    function getPagesByStore(store){
        for (var i = 0;i<pagesStores.length;i++){
            if(pagesStores[i]['id_shop'] == store){
                return pagesStores[i]['total'];
            }
        }
    }

    function getNextStore(store){
        for (var i = 0;i<pagesStores.length;i++){
            if(pagesStores[i]['id_shop'] == store){
                i++;
                if(pagesStores.length == i){
                    return false;
                }
                return pagesStores[i]['id_shop'];
            }
        }
    }

	// Hidden options
	$('.egoi_json_trigger' ).change( function() {
		if ( $('.egoi_track_social').first().is( ':checked' ) ) {
			$('#egoi_track_json' ).css("display", "table-row");
		} else {
			$('#egoi_track_json' ).hide();
		}
	});
});
