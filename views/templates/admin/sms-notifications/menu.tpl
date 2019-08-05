<div class="sms-notifications-nav" id="head_tabs">
    <ul class="nav nav-pills">
        <li class="sms-notifications-nav-item{if $menu_tab == 0} sms-notifications-nav-item-selected{/if}">
            <a href="{$smarty.server.REQUEST_URI|replace:'messages=1':''|replace:'reminders=1':''}" id="subtab-sms-notifications-config" class="sms-notifications-nav-link tab">
                {l s='Configuration' mod='smartmarketingps'}
            </a>
        </li>
        <li class="sms-notifications-nav-item{if $menu_tab == 1} sms-notifications-nav-item-selected{/if}">
            <a href="{$smarty.server.REQUEST_URI|replace:'reminders=1':''}&messages=1" id="subtab-sms-notifications-messages" class="sms-notifications-nav-link tab">
                {l s='Sms Messages' mod='smartmarketingps'}
            </a>
        </li>
        <li class="sms-notifications-nav-item{if $menu_tab == 2} sms-notifications-nav-item-selected{/if}">
            <a href="{$smarty.server.REQUEST_URI|replace:'messages=1':''}&reminders=1" id="subtab-sms-notifications-messages" class="sms-notifications-nav-link tab">
                {l s='Payment Reminders' mod='smartmarketingps'}
            </a>
        </li>
    </ul>
</div>