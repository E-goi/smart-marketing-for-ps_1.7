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
        <span class="icon-settings" id="sms-reminders"></span> <span
                class="baseline">{l s='Payment Reminders' mod='smartmarketingps'}</span>
    </div>
    {if $reminder}
        <form class="row" method="post">
            {include file='./partials/balance.tpl'}

            <div class="row sms-notifications-margin-top">
                <label class="col-xs-3 p5"
                       for="sms-reminder-active">{l s='Activate Reminders' mod='smartmarketingps'}</label>
                <div class="col-xs-9">
                    <input type="checkbox" name="sms-reminder-active" id="sms-reminder-active"
                           {if $reminder.active == 1}checked="checked"{/if}
                           value="{$reminder.active}">
                </div>
            </div>

            {include file='./partials/language_selection.tpl'}

            <div class="row sms-notifications-margin-top">
                <label class="col-xs-3 p5"
                       for="egoi-sms-reminder-time">{l s='SMS Reminder' mod='smartmarketingps'}</label>
                <div class="col-xs-9">
                    <select id="egoi-sms-reminder-time" class="form-control" name="egoi-sms-reminder-time">
                        {html_options values=$reminderTimes output=$reminderTimeNames selected=$defaultReminderTime}
                    </select>
                    <p class="col-xs-12 p5">{l s='Choose the amount of time to send the reminder.' mod='smartmarketingps'}</p>
                </div>
            </div>

            {include file='./partials/custom_information.tpl'}

            <div class="row sms-notifications-margin-top">
                <label class="col-xs-12 sms-notifications-margin-top"
                       for="egoi-sms-reminder-message-{$reminder.order_status_id}">{l s='Payment Reminder' mod='smartmarketingps'}</label>
                <div class="col-xs-12 sms-notifications-margin-top">
                    <textarea rows="5" maxlength="300" id="egoi-sms-reminder-message-{$reminder.order_status_id}"
                              name="egoi-sms-reminder-message-{$reminder.order_status_id}">{$reminder.message}</textarea>
                </div>
            </div>

            <div class="col-xs-12 sms-notifications-margin-top">
                <input type="submit" id="save-reminders" name="save-reminders"
                       value="{l s='Save Reminders' mod='smartmarketingps'}" class="btn btn-primary">
            </div>
        </form>
    {else}
        {l s='No payment module configured!' mod='smartmarketingps'}
    {/if}
</div>
