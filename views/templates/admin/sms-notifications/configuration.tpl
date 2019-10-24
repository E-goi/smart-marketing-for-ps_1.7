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
        <span class="icon-settings" id="sms-configuration"></span> <span
                class="baseline">{l s='SMS Notifications Configuration' mod='smartmarketingps'}</span>
    </div>

    {if $senderIds}
        <form method="post">
            {include file='./partials/balance.tpl'}
            <div class="row mt20">
                <label class="col-xs-3 p5"
                       for="egoi-transactional-sms-sender">{l s='E-goi SMS Sender' mod='smartmarketingps'}</label>
                <div class="col-xs-9">
                    <select id="egoi-transactional-sms-sender" class="form-control"
                            name="egoi-transactional-sms-sender">
                        {html_options values=$senderIds output=$senderNames selected=$defaultSender}
                    </select>
                    <p class="col-xs-12 p5">{l s='Select the sender to be used by SMS notifications' mod='smartmarketingps'}</p>
                </div>
            </div>
            <div class="row mt20">
                <label class="col-xs-3 p5"
                       for="egoi-transactional-sms-administrator">{l s='Account Administrator' mod='smartmarketingps'}</label>
                <div class="col-xs-9">
                    <select id="egoi-transactional-sms-administrator-prefix"
                            name="egoi-transactional-sms-administrator-prefix">
                        {html_options values=$prefixes output=$prefixOutput selected=$defaultPrefix}
                    </select>
                    <input type="text" id="egoi-transactional-sms-administrator"
                           id="egoi-transactional-sms-administrator" name="egoi-transactional-sms-administrator"
                           value="{$administrator}">
                    <p class="col-xs-12 p5">{l s='Insert account administrator that will receive SMS notifications' mod='smartmarketingps'}</p>
                </div>
            </div>
            <div class="row mt20">
                <label class="col-xs-3 p5"
                       for="egoi-transactional-sms-destination">{l s='Destinations to Send SMS Notifications' mod='smartmarketingps'}</label>
                <div class="col-xs-9">
                    <select id="egoi-transactional-sms-destination" class="form-control"
                            name="egoi-transactional-sms-destination">
                        <option value="double-address" {if $deliveryAddress && $invoiceAddress}selected{/if}>{l s='Delivery and Invoice Address' mod='smartmarketingps'}</option>
                        <option value="delivery-address" {if $deliveryAddress && !$invoiceAddress}selected{/if}>{l s='Only Delivery Address' mod='smartmarketingps'}</option>
                        <option value="invoice-address" {if !$deliveryAddress && $invoiceAddress}selected{/if}>{l s='Only Invoice Address' mod='smartmarketingps'}</option>
                    </select>
                    <p class="p5">{l s='Select addresses that will receive SMS notifications' mod='smartmarketingps'}</p>
                </div>
            </div>
            <table class="table col-xs-12 mt20">
                <tr valign="top">
                    <th scope="row" class="egoi-td">{l s='Order Status' mod='smartmarketingps'}</th>
                    <th scope="row" class="egoi-td">{l s='Client' mod='smartmarketingps'}</th>
                    <th scope="row" class="egoi-td">{l s='Administrator' mod='smartmarketingps'}</th>
                </tr>
                {foreach $orders as $order}
                    <tr valign="top">
                        <td class="nowrap">{$order.name}</td>
                        <td class="nowrap">
                            <input type="checkbox" name="sms-notif-client-{$order.id_order_state}"
                                   {if $order.sms_notif.send_client == 1}checked="checked"{/if}
                                   value="{$order.sms_notif.send_client}">
                        </td>
                        <td class="nowrap">
                            <input type="checkbox" name="sms-notif-admin-{$order.id_order_state}"
                                   {if $order.sms_notif.send_admin == 1}checked="checked"{/if}
                                   value="{$order.sms_notif.send_admin}">
                        </td>
                    </tr>
                {/foreach}
            </table>
            <input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}">
            <input type="submit" id="save-config" name="save-config"
                   value="{l s='Save Configuration' mod='smartmarketingps'}"
                   class="btn btn-primary mt20">
        </form>
    {else}
        {l s='Error retrieving senders from E-goi' mod='smartmarketingps'}
    {/if}
</div>
