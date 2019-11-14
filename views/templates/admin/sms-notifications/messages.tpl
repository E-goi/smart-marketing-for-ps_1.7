{*
* Smart Marketing
*
*  @author E-goi
*  @copyright 2019 E-goi
*  @license LICENSE.txt
*}

{include file='../alerts.tpl'}
{include file='./menu.tpl'}

<div class="panel">
    <div class="egoi panel-heading">
        <span class="icon-settings" id="sms-messages"></span> <span
                class="baseline">{l s='SMS Messages' mod='smartmarketingps'}</span>
    </div>
    <form class="row" method="post">
        {include file='./partials/balance.tpl'}
        {include file='./partials/language_selection.tpl'}
        {include file='./partials/custom_information.tpl'}

        <table class="table col-xs-12 mt20">
            <tr valign="top">
                <th scope="row" class="col-xs-3 egoi-td">{l s='Order Status' mod='smartmarketingps'}</th>
                <th scope="row" class="egoi-td">{l s='Client Message' mod='smartmarketingps'}</th>
                <th scope="row" class="egoi-td">{l s='Admin Message' mod='smartmarketingps'}</th>
            </tr>
            {foreach $orders as $order}
                <tr valign="top">
                    <td class="nowrap">{$order.name}</td>
                    <td class="nowrap">
                        <textarea rows="5" maxlength="300"
                                  name="egoi-sms-messages-client-{$order.id_order_state}">{$order.sms_notif.client_message}</textarea>
                    </td>
                    <td class="nowrap">
                        <textarea rows="5" maxlength="300"
                                  name="egoi-sms-messages-admin-{$order.id_order_state}">{$order.sms_notif.admin_message}</textarea>
                    </td>
                </tr>
            {/foreach}
        </table>

        <div class="col-xs-12 mt20">
            <input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}">
            <input type="submit" id="save-messages" name="save-messages"
                   value="{l s='Save Messages' mod='smartmarketingps'}" class="btn btn-primary">
        </div>
    </form>
</div>
