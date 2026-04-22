{*
 * Smart Marketing
 *
 *  @author    E-goi
 *  @copyright 2024 E-goi
 *  @license   LICENSE.txt
 *}

{include file='./alerts.tpl'}

<div class="panel">
    <div class="egoi panel-heading">
        <span class="baseline">{l s='E-commerce configurations' mod='smartmarketingps'}</span>
    </div>
    <form method="post">
        <table class="table" id="egoi-subs-table">
            <tr valign="top">
                <th class="egoi-td" scope="row" style="padding-bottom: 25px;">{l s='Abandoned Cart' mod='smartmarketingps'}</th>
                <td>
                    <div id="egoi_paused_warning" style="display: none; color: #ca5c54; font-weight: bold; background: #ffe4e4; padding: 8px 12px; border-radius: 4px; margin-top: 0; width: fit-content;">
                        {l s='This configuration is already active for another domain' mod='smartmarketingps'}
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="switch prestashop-switch fixed-width-lg" id="egoi_paused_toggle_wrapper" style="display: none;">
                            <input type="radio" name="egoi_paused_toggle" id="egoi_paused_toggle_on" value="0">
                            <label for="egoi_paused_toggle_on">{l s='Active' mod='smartmarketingps'}</label>
                            <input type="radio" name="egoi_paused_toggle" id="egoi_paused_toggle_off" value="1" checked="checked">
                            <label for="egoi_paused_toggle_off">{l s='Disabled' mod='smartmarketingps'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                        <i class="icon-spinner icon-spin" id="egoi_paused_loading"></i>
                    </div>
                    <p class="help" id="egoi_paused_help" style="display: none; margin-top: 10px;">{l s='Enable to track abandoned carts and trigger your automated journey in E-goi.' mod='smartmarketingps'}</p>
                </td>
            </tr>
            <tr valign="top">
                <th class="egoi-td" scope="row" style="padding-bottom: 25px;">{l s='Welcome' mod='smartmarketingps'}</th>
                <td>
                    <div id="egoi_welcome_warning" style="display: none; color: #ca5c54; font-weight: bold; background: #ffe4e4; padding: 8px 12px; border-radius: 4px; margin-top: 0; width: fit-content;">
                        {l s='This configuration is already active for another domain' mod='smartmarketingps'}
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="switch prestashop-switch fixed-width-lg" id="egoi_welcome_toggle_wrapper" style="display: none;">
                            <input type="radio" name="egoi_welcome_toggle" id="egoi_welcome_toggle_on" value="0">
                            <label for="egoi_welcome_toggle_on">{l s='Active' mod='smartmarketingps'}</label>
                            <input type="radio" name="egoi_welcome_toggle" id="egoi_welcome_toggle_off" value="1" checked="checked">
                            <label for="egoi_welcome_toggle_off">{l s='Disabled' mod='smartmarketingps'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                        <i class="icon-spinner icon-spin" id="egoi_welcome_loading"></i>
                    </div>
                    <p class="help" id="egoi_welcome_help" style="display: none; margin-top: 10px;">{l s='Enable to track new registrations and trigger your automated journey in E-goi.' mod='smartmarketingps'}</p>
                </td>
            </tr>
            <tr valign="top">
                <th class="egoi-td" scope="row" style="padding-bottom: 25px;">{l s='Order Confirmation' mod='smartmarketingps'}</th>
                <td>
                    <div id="egoi_order_status_updated_warning" style="display: none; color: #ca5c54; font-weight: bold; background: #ffe4e4; padding: 8px 12px; border-radius: 4px; margin-top: 0; width: fit-content;">
                        {l s='This configuration is already active for another domain' mod='smartmarketingps'}
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="switch prestashop-switch fixed-width-lg" id="egoi_order_status_updated_toggle_wrapper" style="display: none;">
                            <input type="radio" name="egoi_order_status_updated_toggle" id="egoi_order_status_updated_toggle_on" value="0">
                            <label for="egoi_order_status_updated_toggle_on">{l s='Active' mod='smartmarketingps'}</label>
                            <input type="radio" name="egoi_order_status_updated_toggle" id="egoi_order_status_updated_toggle_off" value="1" checked="checked">
                            <label for="egoi_order_status_updated_toggle_off">{l s='Disabled' mod='smartmarketingps'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                        <i class="icon-spinner icon-spin" id="egoi_order_status_updated_loading"></i>
                    </div>
                    <p class="help" id="egoi_order_status_updated_help" style="display: none; margin-top: 10px;">{l s='Enable to track order confirmation and trigger your automated journey in E-goi.' mod='smartmarketingps'}</p>
                </td>
            </tr>
            <tr valign="top">
                <th class="egoi-td" scope="row">{l s='Sync existing orders' mod='smartmarketingps'}</th>
                <td>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <button type="button" class="btn btn-info" id="sync_old_orders">
                            {l s='Sync Orders' mod='smartmarketingps'}
                        </button>
                        <div class="sync_orders" style="display: none;"></div>
                        <i class="material-icons" id="sync_success" style="display: none;">beenhere</i>
                        <i class="material-icons" id="sync_noorders" style="display: none;">remove_shopping_cart</i>
                        <div id="spanprogress" style="width: 200px; height: 20px;">
                            <div id="progressbarSync" class="progress" style="display: none; margin: 0;">
                                <div id="progressbarValues" class="progress-bar progress-bar-striped active"
                                     role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                                     style="width: 0%;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="help">{l s='This option will import all your Prestashop orders into E-goi contacts. If the contact does not exist in the list, it will be created along with the respective order.' mod='smartmarketingps'}</p>
                </td>
            </tr>
            <tr style="display:none;">
                <td colspan="2" style="text-align: right;">
                    <input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}">
                </td>
            </tr>
        </table>
    </form>
</div>

<div class="panel">
    <div class="panel-heading">{l s='Mapping of order states' mod='smartmarketingps'}</div>
    <div class="panel-info">
        <p class="help">
            <i class="icon-info-circle"></i>
            {l s='Here you can map Prestashop order statuses with the statuses accepted by E-goi. These will be the statuses that appear in the E-goi contact.' mod='smartmarketingps'}
        </p>
    </div>
    <form method="post">
        <table class="table">
            <thead>
            <tr>
                <th>{l s='ID' mod='smartmarketingps'}</th>
                <th>{l s='Prestashop State' mod='smartmarketingps'}</th>
                <th>{l s='E-goi State' mod='smartmarketingps'}</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$statesData item=state}
                <tr>
                    <td>{$state.id|escape:'htmlall':'UTF-8'}</td>
                    <td>
                        <span style="background-color: {$state.color|escape:'htmlall':'UTF-8'}; color: white; padding: 2px 6px; border-radius: 3px;">
                            {$state.name|escape:'htmlall':'UTF-8'}
                        </span>
                    </td>
                    <td>
                        <select name="egoi_state_mappings[{$state.id}]" class="form-control egoi-state-dropdown">
                            <option value="" disabled>{l s='Select E-goi state' mod='smartmarketingps'}</option>
                            {foreach from=$state.egoi_states item=egoiState}
                                <option value="{$egoiState.egoi_id|escape:'htmlall':'UTF-8'}"
                                        {if $state.egoi_state_id == $egoiState.egoi_id}selected{/if}>
                                    {if $egoiState.name == 'created'}
                                        {l s='Created' mod='smartmarketingps'}
                                    {elseif $egoiState.name == 'pending'}
                                        {l s='Pending' mod='smartmarketingps'}
                                    {elseif $egoiState.name == 'completed'}
                                        {l s='Completed' mod='smartmarketingps'}
                                    {elseif $egoiState.name == 'canceled'}
                                        {l s='Canceled' mod='smartmarketingps'}
                                    {elseif $egoiState.name == 'payment_pending'}
                                        {l s='PaymentPending' mod='smartmarketingps'}
                                    {elseif $egoiState.name == 'payment_failed'}
                                        {l s='PaymentFailed' mod='smartmarketingps'}
                                    {elseif $egoiState.name == 'shipped'}
                                        {l s='Shipped' mod='smartmarketingps'}
                                    {elseif $egoiState.name == 'paid'}
                                        {l s='Paid' mod='smartmarketingps'}
                                    {else}
                                        {l s='Unknown' mod='smartmarketingps'}
                                    {/if}
                                </option>
                            {/foreach}
                        </select>
                    </td>
                </tr>
            {/foreach}
            <tr style="display:none;">
                <td colspan="2" style="text-align: right;">
                    <input type="hidden" name="egoi_paused_toggle_hidden" id="egoi_paused_toggle_hidden" value="1">
                    <input type="hidden" name="egoi_welcome_toggle_hidden" id="egoi_welcome_toggle_hidden" value="1">
                    <input type="hidden" name="egoi_order_status_updated_toggle_hidden" id="egoi_order_status_updated_toggle_hidden" value="1">
                    <input type='submit' name='action_add' id='action_add' value="1" style="display: none;">
                </td>
            </tr>
            </tbody>
        </table>
    </form>
</div>