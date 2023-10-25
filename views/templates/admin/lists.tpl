{*
* Smart Marketing
*
*  @author    E-goi
*  @copyright 2018 E-goi
*  @license   LICENSE.txt
*}

<div class="panel">
    <div class="egoi panel-heading"><span class="icon-list" id="lists"></span> <span class="baseline">{l s='My Lists' mod='smartmarketingps'}</span></div>
    {if $lists.items}
        <table class="table">
            <thead>
                <tr>
                    <th>{l s='List ID' mod='smartmarketingps'}</th>
                    <th>{l s='Title' mod='smartmarketingps'}</th>
                    <th>{l s='Internal Title' mod='smartmarketingps'}</th>
                    <th>{l s='Created' mod='smartmarketingps'}</th>
                    <th>{l s='Updated' mod='smartmarketingps'}</th>
                    <th>{l s='Status' mod='smartmarketingps'}</th>
                    <th>{l s='Edit' mod='smartmarketingps'}</th>
                </tr>
            </thead>
            {foreach $lists.items as $key => $list}
                <tr>
                    <td width="60">{$list.list_id|escape:'htmlall':'UTF-8'}</td>
                    <td width="250">{$list.public_name|escape:'htmlall':'UTF-8'}</td>
                    <td width="250">{$list.internal_name|escape:'htmlall':'UTF-8'}</td>
                    <td width="100">{$list.created|escape:'htmlall':'UTF-8'}</td>
                    <td width="100">{$list.updated|escape:'htmlall':'UTF-8'}</td>
                    <td width="100">{$list.status|escape:'htmlall':'UTF-8'}</td>
                    <td width="100">
                        <a href="http://login.egoiapp.com/?from={$url_list|escape:'htmlall':'UTF-8'}{$list.listnum|escape:'htmlall':'UTF-8'}" class="btn btn-default" target="_blank">
                            {l s='Change' mod='smartmarketingps'} <span class="icon-edit"></span>
                        </a>
                    </td>
                </tr>
            {/foreach}
        </table>
        <p>&nbsp;</p>

        <h3>{l s='Create another list' mod='smartmarketingps'}</h3>
        <form method="post">
            <table class="table">
            <tr>
                <td>
                    <label for="egoi_ps_title">{l s='Name' mod='smartmarketingps'}</label>
                </td>
                <td>
                    <input type='text' size='60' name='egoi_ps_title' id="egoi_ps_title" required="required" />
                </td>
            </tr>
{*            <tr>*}
{*                <td>*}
{*                    <label for="egoi_ps_lang">{l s='Language' mod='smartmarketingps'}</label>*}
{*                </td>*}
{*                <td>*}
{*                    <select name='egoi_ps_lang'>*}
{*                        <option value='en'>English</option>*}
{*                        <option value='pt'>Portuguese</option>*}
{*                        <option value='br'>Portuguese (Brasil)</option>*}
{*                        <option value='es'>Spanish</option>*}
{*                    </select>*}
{*                </td>*}
{*            </tr>*}
            </table>

            <input type="submit" id="add-list" name="add-list" value="{l s='Save List' mod='smartmarketingps'}" class="btn btn-primary">
        </form>
    {else}
        {l s='Error retrieving lists from E-goi' mod='smartmarketingps'}
    {/if}
</div>
