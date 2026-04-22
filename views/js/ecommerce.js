/**
 * Smart Marketing
 *
 *  @author    E-goi
 *  @copyright 2018 E-goi
 *  @license   LICENSE.txt
 */

jQuery.fn.show = function () {
    return this.prop('style', 'display:inline-block');
};

$(document).ready(function () {
    var totalPages = 0;
    var currentPage = 0;
    var pagesStores = [];
    var btn_sync_orders = $("#sync_old_orders");

    var $pausedToggleOn = $('#egoi_paused_toggle_on');
    var $pausedToggleOff = $('#egoi_paused_toggle_off');
    var $pausedLoading = $('#egoi_paused_loading');

    var $welcomeToggleOn = $('#egoi_welcome_toggle_on');
    var $welcomeToggleOff = $('#egoi_welcome_toggle_off');
    var $welcomeLoading = $('#egoi_welcome_loading');

    var $orderStatusToggleOn = $('#egoi_order_status_updated_toggle_on');
    var $orderStatusToggleOff = $('#egoi_order_status_updated_toggle_off');
    var $orderStatusLoading = $('#egoi_order_status_updated_loading');

    // Fetch paused status on page load
    $.ajax({
        type: 'POST',
        data: {
            action: 'getPausedStatus',
            ajax: true
        },
        success: function (response) {
            $pausedLoading.hide();
            $welcomeLoading.hide();
            $orderStatusLoading.hide();
            try {
                var json = typeof response === 'string' ? JSON.parse(response) : response;
                var isActive = false;
                var showWarning = false;
                
                var isWelcomeActive = false;
                var showWelcomeWarning = false;

                var isOrderStatusActive = false;
                var showOrderStatusWarning = false;

                if (json && Array.isArray(json.items) && json.items.length > 0) {
                    var abandonedCartItem = null;
                    var welcomeItem = null;
                    var orderStatusItem = null;
                    for (var i = 0; i < json.items.length; i++) {
                        if (json.items[i].type === 'abandoned_cart') {
                            abandonedCartItem = json.items[i];
                        }
                        if (json.items[i].type === 'welcome') {
                            welcomeItem = json.items[i];
                        }
                        if (json.items[i].type === 'order_status_updated') {
                            orderStatusItem = json.items[i];
                        }
                    }

                    // If we found the item and paused is explicitly false
                    if (abandonedCartItem && abandonedCartItem.paused === false) {
                        if (abandonedCartItem.url && json.current_domain && abandonedCartItem.url !== json.current_domain) {
                            showWarning = true;
                        } else {
                            isActive = true;
                        }
                    }
                    
                    if (welcomeItem && welcomeItem.paused === false) {
                        if (welcomeItem.url && json.current_domain && welcomeItem.url !== json.current_domain) {
                            showWelcomeWarning = true;
                        } else {
                            isWelcomeActive = true;
                        }
                    }

                    if (orderStatusItem && orderStatusItem.paused === false) {
                        if (orderStatusItem.url && json.current_domain && orderStatusItem.url !== json.current_domain) {
                            showOrderStatusWarning = true;
                        } else {
                            isOrderStatusActive = true;
                        }
                    }
                }

                if (showWarning) {
                    $('#egoi_paused_toggle_wrapper').hide();
                    $('#egoi_paused_help').hide();
                    $('#egoi_paused_warning').show();
                    $('#egoi_paused_toggle_hidden').val("1");
                } else {
                    $('#egoi_paused_toggle_wrapper').show();
                    $('#egoi_paused_help').show();
                    $('#egoi_paused_warning').hide();

                    if (isActive) {
                        $pausedToggleOn.prop('checked', true);
                        $pausedToggleOff.prop('checked', false);
                        $('#egoi_paused_toggle_hidden').val("0");
                    } else {
                        $pausedToggleOn.prop('checked', false);
                        $pausedToggleOff.prop('checked', true);
                        $('#egoi_paused_toggle_hidden').val("1");
                    }
                }
                
                if (showWelcomeWarning) {
                    $('#egoi_welcome_toggle_wrapper').hide();
                    $('#egoi_welcome_help').hide();
                    $('#egoi_welcome_warning').show();
                    $('#egoi_welcome_toggle_hidden').val("1");
                } else {
                    $('#egoi_welcome_toggle_wrapper').show();
                    $('#egoi_welcome_help').show();
                    $('#egoi_welcome_warning').hide();

                    if (isWelcomeActive) {
                        $welcomeToggleOn.prop('checked', true);
                        $welcomeToggleOff.prop('checked', false);
                        $('#egoi_welcome_toggle_hidden').val("0");
                    } else {
                        $welcomeToggleOn.prop('checked', false);
                        $welcomeToggleOff.prop('checked', true);
                        $('#egoi_welcome_toggle_hidden').val("1");
                    }
                }

                if (showOrderStatusWarning) {
                    $('#egoi_order_status_updated_toggle_wrapper').hide();
                    $('#egoi_order_status_updated_help').hide();
                    $('#egoi_order_status_updated_warning').show();
                    $('#egoi_order_status_updated_toggle_hidden').val("1");
                } else {
                    $('#egoi_order_status_updated_toggle_wrapper').show();
                    $('#egoi_order_status_updated_help').show();
                    $('#egoi_order_status_updated_warning').hide();

                    if (isOrderStatusActive) {
                        $orderStatusToggleOn.prop('checked', true);
                        $orderStatusToggleOff.prop('checked', false);
                        $('#egoi_order_status_updated_toggle_hidden').val("0");
                    } else {
                        $orderStatusToggleOn.prop('checked', false);
                        $orderStatusToggleOff.prop('checked', true);
                        $('#egoi_order_status_updated_toggle_hidden').val("1");
                    }
                }

            } catch (e) {
                console.error('Error parsing response', e);
                $pausedToggleOn.prop('checked', false);
                $pausedToggleOff.prop('checked', true);
                $('#egoi_paused_toggle_hidden').val("1");
                $('#egoi_paused_toggle_wrapper').show();
                $('#egoi_paused_help').show();
                
                $welcomeToggleOn.prop('checked', false);
                $welcomeToggleOff.prop('checked', true);
                $('#egoi_welcome_toggle_hidden').val("1");
                $('#egoi_welcome_toggle_wrapper').show();
                $('#egoi_welcome_help').show();

                $orderStatusToggleOn.prop('checked', false);
                $orderStatusToggleOff.prop('checked', true);
                $('#egoi_order_status_updated_toggle_hidden').val("1");
                $('#egoi_order_status_updated_toggle_wrapper').show();
                $('#egoi_order_status_updated_help').show();
            }
        },
        error: function () {
            $pausedLoading.hide();
            $('#egoi_paused_toggle_wrapper').show();
            $('#egoi_paused_help').show();
            
            $welcomeLoading.hide();
            $('#egoi_welcome_toggle_wrapper').show();
            $('#egoi_welcome_help').show();

            $orderStatusLoading.hide();
            $('#egoi_order_status_updated_toggle_wrapper').show();
            $('#egoi_order_status_updated_help').show();
            console.error('Error fetching paused status');
        }
    });

    $('input[name="egoi_paused_toggle"]').on('change', function () {
        $('#egoi_paused_toggle_hidden').val($(this).val());
    });
    
    $('input[name="egoi_welcome_toggle"]').on('change', function () {
        $('#egoi_welcome_toggle_hidden').val($(this).val());
    });

    $('input[name="egoi_order_status_updated_toggle"]').on('change', function () {
        $('#egoi_order_status_updated_toggle_hidden').val($(this).val());
    });


    function interactionOrders(store, page) {
        var token_list = '1';

        $.ajax({
            type: 'POST',
            data: {
                token_list: token_list,
                orders: page,
                store_id: store
            },
            success: function (data, status) {
                var json = JSON.parse(data);

                if (json.hasOwnProperty("error")) {
                    btn_sync_orders.prop('disabled', false);
                    $('.sync_orders').hide();
                    $('#sync_success').hide();
                    $('#progressbarSync').hide();
                    totalPages = 0;
                    currentPage = 0;
                    pagesStores = [];

                    $('#sync_noorders').show();
                    window.setTimeout(function () {
                        $("#sync_noorders").fadeOut(400);
                    }, 6000);
                    return;
                }

                // Atualiza o progresso
                currentPage++;
                $('#progressbarValues').attr("aria-valuenow", currentPage);
                $('#progressbarValues').width(Math.ceil((currentPage * 100) / totalPages) + "%");
                $('#progressbarValues').text(currentPage + "/" + totalPages);

                // Verifica se ainda há páginas para processar na loja atual
                if (json.has_more) {
                    interactionOrders(store, json.imported);
                } else {
                    // Passa para a próxima loja
                    var next = getNextStore(store);
                    if (next) {
                        interactionOrders(next, 0);
                    } else {
                        // Finaliza a sincronização
                        setTimeout(function () {
                            $('#progressbarSync').hide();
                            $('#progressbarValues').width("0%");
                            btn_sync_orders.prop('disabled', false);
                            $('.sync_orders').hide();
                            $('#sync_success').show();
                            window.setTimeout(function () {
                                $("#sync_success").fadeOut(400);
                            }, 6000);
                            totalPages = 0;
                            currentPage = 0;
                            pagesStores = [];
                        }, 1000);
                    }
                }
            },
            error: function (status) {
                btn_sync_orders.prop('disabled', false);
                $('.sync_orders').hide();
                $('#sync_success').hide();
                $('#progressbarSync').hide();
                totalPages = 0;
                currentPage = 0;
                pagesStores = [];
            }
        });
    }

    btn_sync_orders.on('click', function () {
        $('.sync_orders').show();
        $('#sync_success').hide();
        btn_sync_orders.prop('disabled', true);

        $.ajax({
            type: 'POST',
            data: ({
                size: 1,
            }),
            success: function (data, status) {
                if (data !== "" && data !== "No orders!") {
                    var json = JSON.parse(data);
                    json = pagesStores = calcPages(json);

                    $('#progressbarSync').show();
                    $('#progressbarValues').attr("aria-valuemax", totalPages);
                    $('#progressbarValues').width("0%");
                    $('#progressbarValues').text("0/" + totalPages);

                    if (json.length > 0) {
                        interactionOrders(json[0].id_shop, 0);
                    } else {
                        btn_sync_orders.prop('disabled', false);
                        $('.sync_orders').hide();
                        $('#sync_success').hide();
                        $('#progressbarSync').hide();

                        $('#sync_noorders').show();
                        window.setTimeout(function () {
                            $("#sync_noorders").fadeOut(400);
                            btn_sync_orders.prop('disabled', false);
                        }, 6000);

                        return false;
                    }
                } else {
                    btn_sync_orders.prop('disabled', false);
                    $('.sync_orders').hide();
                    $('#sync_success').hide();
                }
            },
            error: function (status) {
                btn_sync_orders.prop('disabled', false);
                $('.sync_orders').hide();
                $('#sync_success').hide();
            }
        });
    });

    function calcPages(arr) {
        var tot = 0;
        for (var i = 0; i < arr.length; i++) {
            arr[i]['total'] = Math.ceil(arr[i]['total'] / 1000); // Ajusta o tamanho da página
            tot += arr[i]['total'];
        }
        totalPages = tot;
        return arr;
    }

    function getNextStore(store) {
        for (var i = 0; i < pagesStores.length; i++) {
            if (pagesStores[i]['id_shop'] === store) {
                i++;
                if (pagesStores.length === i) {
                    return false;
                }
                return pagesStores[i]['id_shop'];
            }
        }
    }
});