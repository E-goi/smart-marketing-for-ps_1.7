{include file='./alerts.tpl'}

{if $webPush}
    <div class="panel">
        <div class="egoi panel-heading">
            <span class="baseline">{l s='Your Configured Web Push' mod='smartmarketingps'}</span>
        </div>
        <form method="post">
            <table class="table" id="egoi-wp-table">
                <tr>
                    <th class="egoi-td" scope="row">{l s='ID' mod='smartmarketingps'}</th>
                    <td class="nowrap input-group col-xs-9 no-border">
                        <div id="wp-site-id" class="wp-row">{$webPush.site_id}</div>
                    </td>
                </tr>

                <tr>
                    <th class="egoi-td" scope="row">{l s='Site' mod='smartmarketingps'}</th>
                    <td class="nowrap input-group col-xs-9 no-border">
                        <div id="wp-site" class="wp-row">{$webPush.site}</div>
                    </td>
                </tr>


                <tr>
                    <th class="egoi-td" scope="row">{l s='Name' mod='smartmarketingps'}</th>
                    <td class="nowrap input-group col-xs-9 no-border">
                        <div id="wp-name" class="wp-row">{$webPush.name}</div>
                    </td>
                </tr>
            </table>
            <input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}">
            <input type="submit" name="delete-wp-config" class="btn btn-secondary mt20"
                   value="{l s='Delete Configuration' mod='smartmarketingps'}">
        </form>
    </div>
{/if}

<div class="panel">
    <div class="egoi panel-heading">
        <span class="baseline">{l s='Create New Web Push Site' mod='smartmarketingps'}</span>
    </div>

    <form method="post">
        <table class="table" id="create-wp-table">

            <!--<tr valign="top">
                <th class="egoi-td" scope="row">{l s='App Code' mod='smartmarketingps'}</th>
                <td class="col-xs-9 no-border">
                    <button type="button" class="btn btn-info">{l s='Create New' mod='smartmarketingps'}</button>
                </td>
            </tr>

            <tr valign="top">
                <th class="egoi-td" scope="row">{l s='App Code' mod='smartmarketingps'}</th>
                <td class="col-xs-9 no-border">
                    <input type="text" name="xpto" value="111111111111">
                    <p>{l s='Select the list to synchronize your PS customers base with.' mod='smartmarketingps'}</p>
                </td>
            </tr>-->

            <tr valign="top">
                <th class="egoi-td" scope="row">{l s='Name' mod='smartmarketingps'}</th>
                <td class="col-xs-9 no-border">
                    <input type="text" name="wp-create-site-name">
                    <p>{l s='The name of the web push site to create' mod='smartmarketingps'}</p>
                </td>
            </tr>
        </table>
        <input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}">
        <input type="submit" name="create-wp" class="btn btn-primary mt20"
               value="{l s='Create Web Push Site' mod='smartmarketingps'}">
    </form>
</div>

<div class="panel">
    <div class="egoi panel-heading">
        <span class="baseline">{l s='Select Existing Web Push Site' mod='smartmarketingps'}</span>
    </div>

    <form method="post">
        <table class="table" id="select-wp-table">
            <tr valign="top">
                <th class="egoi-td" scope="row">{l s='Web Push Site' mod='smartmarketingps'}</th>
                <td class="col-xs-9 no-border">
                    <select id="egoi-web-push-sites" class="form-control" name="egoi-web-push-sites">
                        {html_options values=$siteIds output=$webPushNames selected=$defaultWebPush}
                    </select>
                    <p>{l s='Select a web push site existing in your E-goi account' mod='smartmarketingps'}</p>
                </td>
            </tr>
        </table>
        <input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}">
        <input type="submit" name="select-wp" class="btn btn-primary mt20"
               value="{l s='Select Web Push Site' mod='smartmarketingps'}">
    </form>
</div>
