{*
* Smart Marketing
*
*  @author    E-goi
*  @copyright 2019 E-goi
*  @license   LICENSE.txt
*}

<div class="mt20">
    <label>{l s='Customization Codes' mod='smartmarketingps'}</label>
    <p>{l s="You can use these codes to customize your messages" mod='smartmarketingps'}</p>

    <div class="col-xs-6">
        <div class="sms-notifications-custom-info">
            <a id="custom_info_order_reference" class="btn btn-default sms-notifications-custom-info-btn" onclick="insertText('{$order_reference}')">
                {l s='Order Reference' mod='smartmarketingps'}
            </a>
            <b class="sms-notifications-custom-info-label">{$order_reference}</b>
        </div>

        <div class="sms-notifications-custom-info">
            <a id="custom_info_order_status" class="btn btn-default sms-notifications-custom-info-btn" onclick="insertText('{$order_status}')">
                {l s='Order Status' mod='smartmarketingps'}
            </a>
            <b class="sms-notifications-custom-info-label">{$order_status}</b>
        </div>

        <div class="sms-notifications-custom-info">
            <a id="custom_info_total_cost" class="btn btn-default sms-notifications-custom-info-btn" onclick="insertText('{$total_cost}')">
                {l s='Total Cost' mod='smartmarketingps'}
            </a>
            <b class="sms-notifications-custom-info-label">{$total_cost}</b>
        </div>

        <div class="sms-notifications-custom-info">
            <a id="custom_info_currency" class="btn btn-default sms-notifications-custom-info-btn" onclick="insertText('{$currency}')">
                {l s='Currency' mod='smartmarketingps'}
            </a>
            <b class="sms-notifications-custom-info-label">{$currency}</b>
        </div>

        <div class="sms-notifications-custom-info">
            <a id="custom_info_shop_name" class="btn btn-default sms-notifications-custom-info-btn" onclick="insertText('{$shop_name}')">
                {l s='Shop Name' mod='smartmarketingps'}
            </a>
            <b class="sms-notifications-custom-info-label">{$shop_name}</b>
        </div>

        <div class="sms-notifications-custom-info">
            <a id="custom_info_billing_name" class="btn btn-default sms-notifications-custom-info-btn" onclick="insertText('{$billing_name}')">
                {l s='Billing Name' mod='smartmarketingps'}
            </a>
            <b class="sms-notifications-custom-info-label">{$billing_name}</b>
        </div>
    </div>

    <div class="col-xs-6">
        <div class="sms-notifications-custom-info">
            <a id="custom_info_entity" class="btn btn-default sms-notifications-custom-info-btn" onclick="insertText('{$entity}')">
                {l s='Entity' mod='smartmarketingps'}
            </a>
            <b class="sms-notifications-custom-info-label">{$entity}</b>
        </div>

        <div class="sms-notifications-custom-info">
            <a id="custom_info_mb_reference" class="btn btn-default sms-notifications-custom-info-btn" onclick="insertText('{$mb_reference}')">
                {l s='MB Reference' mod='smartmarketingps'}
            </a>
            <b class="sms-notifications-custom-info-label">{$mb_reference}</b>
        </div>

        <div class="sms-notifications-custom-info">
            <a id="custom_info_carrier" class="btn btn-default sms-notifications-custom-info-btn" onclick="insertText('{$carrier}')">
                {l s='Carrier' mod='smartmarketingps'}
            </a>
            <b class="sms-notifications-custom-info-label">{$carrier}</b>
        </div>

        <div class="sms-notifications-custom-info">
            <a id="custom_info_tracking_url" class="btn btn-default sms-notifications-custom-info-btn" onclick="insertText('{$tracking_url}')">
                {l s='Tracking URL' mod='smartmarketingps'}
            </a>
            <b class="sms-notifications-custom-info-label">{$tracking_url}</b>
        </div>

        <div class="sms-notifications-custom-info">
            <a id="custom_info_tracking_number" class="btn btn-default sms-notifications-custom-info-btn" onclick="insertText('{$tracking_number}')">
                {l s='Tracking Number' mod='smartmarketingps'}
            </a>
            <b class="sms-notifications-custom-info-label">{$tracking_number}</b>
        </div>
    </div>
</div>