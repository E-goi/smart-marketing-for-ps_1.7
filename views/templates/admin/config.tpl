
{include file='./alerts.tpl' config='true'}

{if isset($success_msg) and ($success_msg)}
	{$success_msg}
{/if}

<form method="post" action="" autocomplete="off">
	<div class="panel" id="panel_egbody">
		<div class="egoi panel-heading">
			<span class="img_forms">&nbsp;</span> <span class="baseline">{l s='Configuration' mod='smartmarketingps'}</span>
		</div>
		
		<div class="form-group">
			<label class="control-label col-lg-3">

				<div>
					<span data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='To get your API Key, login into your E-goi Account' mod='smartmarketingps'} BO Panel, {l s='go to your user menu (upper right corner), select <<Integrations>> and copy the account API key' mod='smartmarketingps'}">
						{l s='Your API Key' mod='smartmarketingps'}
					</span>
				</div>
			</label>
			
			<div class="col-lg-9">	
				<input type="hidden" id="egoi_client_id" name="egoi_client_id">
				<input type="password" style="width:50%;display:-webkit-inline-box;" name="smart_api_key" id="smart_api_key" size="55" value="{Configuration::get('smart_api_key')}" disabled />

				<div id="load" style="display:none;"></div>
				<div id="valid" style="display:none;"></div>
				<div id="error" style="display:none;"></div>
				<a class="btn btn-info" id="edit_key">Edit API Key</a>
			</div>
		</div>

		<div class="form-group">
			<p>&nbsp;</p>
			{l s='To get your API Key, login into your' mod='egoiforps'} <a target="_blank" href="https://login.egoiapp.com">BO Panel</a>, {l s='go to your user menu (upper right corner), select "Integrations" and copy the account API key' mod='smartmarketingps'}
		</div>

		<div class="form-group">
			<input type="submit" name="submit_api_key" id="apikey_submit" value="{l s='Save' mod='smartmarketingps'}" class="btn btn-primary" style="display: none;" />
			<a class="btn btn-default" id="account_button">{l s='Go to My Account' mod='smartmarketingps'} <span class="icon-external-link"></span></a>
		</div>

	</div>
</form>
