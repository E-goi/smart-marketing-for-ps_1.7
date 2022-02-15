/**
 * Smart Marketing
 *
 *  @author    E-goi
 *  @copyright 2018 E-goi
 *  @license   LICENSE.txt
 */

jQuery.fn.nShow = function() {
    return this.prop('style', 'display:inline-block');
};

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

	$('.del-form').on('click', function(e) {
		e.preventDefault();

		var text = $('#del-info').text();
		var url = $(this).attr('href');
		if (confirm(text)) {
			return window.location.replace(url);
		}
		return false;
	});

	$('.ps-tab').on('click', function () {
        var current_tab = $(this);
        var current_block = current_tab.data('block');

        if (current_tab.hasClass('current')) {
        	return false;
		}

        current_tab.addClass('current');
        $('#'+current_block+'_egoi').show();

        $.each($('.ps-tab').not("#"+this.id), function (index, el) {
        	el_data = el.getAttribute('data-block');
			el.className = 'ps-tab';
			$('#'+el_data+'_egoi').hide();
        });
    });

	$('#list_id').on('change', function () {
		var list = $(this).val();
		$('.sync_list').nShow();
        $('#sync_success').hide();

        var forms = $('#formid_egoi');
        forms.html('');

		$.ajax({
            type: 'POST',
            dataType: 'JSON',
            data:({
                list_id: list,
                _get_forms: 1
            }),
            success:function(data) {
                if (typeof data === "object") {
                    $.each(data, function (index, el) {
                        forms.append(
                            $("<option />").val(el.url).text(el.title)
                        ).trigger('change');
                        $('#show_preview').nShow();
                    });

                }else {
                    forms.append(
                        $("<option />").val("").text(data)
                    );
                    $('#show_preview').hide();
				}

                $('.sync_list').hide();
                $('#sync_success').nShow();
            },
            error:function(){
                $('.sync_list').hide();
                $('#sync_success').hide();
                $('#show_preview').hide();
            }
        });
    });

    $('#formid_egoi').on('change', function() {
        var url = $(this).val();
        if(url) {
            $('#prev_iframe').prop('src', '//'+url);
            $('#preview').modal('show');
        }
    });

    $('#btn_banner_close').on('click', function() {

        $('#forms_banner').hide();

    });

});