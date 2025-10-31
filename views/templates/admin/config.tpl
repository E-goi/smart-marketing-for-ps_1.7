{*
* Smart Marketing
*
*  @author    E-goi
*  @copyright 2018 E-goi
*  @license   LICENSE.txt
*}

{include file='./alerts.tpl' config='true'}

<!-- Inline success/error messages for AJAX -->
<div id="api_key_success" class="alert alert-success" style="display:none;">
	{l s='API Key saved successfully!' mod='smartmarketingps'}
</div>
<div id="api_key_error" class="alert alert-danger" style="display:none;">
	{l s='Error saving API Key!' mod='smartmarketingps'}
</div>
<div id="debug_mode_success" class="alert alert-success" style="display:none;">
	{l s='Debug Mode saved successfully!' mod='smartmarketingps'}
</div>
<div id="debug_mode_error" class="alert alert-danger" style="display:none;">
	{l s='Error saving Debug Mode!' mod='smartmarketingps'}
</div>

<form method="post" action="" autocomplete="off">
	<div class="panel" id="panel_egbody">
		<div class="egoi panel-heading">
			<span class="img_forms">&nbsp;</span> <span class="baseline">{l s='Configuration' mod='smartmarketingps'}</span>
		</div>

		<div class="form-group">
			<label class="control-label col-lg-3">
				<span>
					{l s='Your API Key' mod='smartmarketingps'}
					<i class="material-icons label-tooltip"
					   data-toggle="tooltip"
					   data-placement="right"
					   data-html="true"
					   data-original-title="{l s='To get your API Key, login into your E-goi account BO. Go to your user menu (upper right corner), select &quot;Integrations&quot; and copy the account API key.' mod='smartmarketingps'}"
					   style="font-size:16px;color:#00aff0;vertical-align:middle;cursor:help;margin-right:6px;">
						info_outline
					</i>
				</span>
			</label>


			<div class="col-lg-9">
				<input type="hidden" id="egoi_client_id" name="egoi_client_id">
				<input type="password" style="width:50%;display:-webkit-inline-box;" name="smart_api_key" id="smart_api_key" size="55" value="{Configuration::get('smart_api_key')|escape:'htmlall':'UTF-8'}" disabled />

				<div class="sync_api_key" style="display:none;"></div>
				<div data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='This key is invalid!' mod='smartmarketingps'}" id="error" style="display:none;">
					<i class="material-icons action-enabled">error</i>
				</div>
				<a class="btn btn-info" id="edit_key">{l s='Change API Key' mod='smartmarketingps'}</a>
				<input type="submit" name="submit_api_key" id="apikey_submit" value="{l s='Save' mod='smartmarketingps'}" class="btn btn-primary" style="display: none;">
				<div style="margin-bottom: 25px"></div>

			</div>
		</div>

		<div class="form-group">

			<label class="control-label col-lg-3">
				<span>
					{l s='Debug Mode' mod='smartmarketingps'}
					<i class="material-icons label-tooltip"
					   data-toggle="tooltip"
					   data-placement="right"
					   data-html="true"
					   data-original-title="{l s='Enable this option to log actions of the module. Useful for troubleshooting or support purposes. Logs will be saved in the /modules/smartmarketingps/logs/ directory.' mod='smartmarketingps'}"
					   style="font-size:16px;color:#00aff0;vertical-align:middle;cursor:help;margin-left:6px;">
						info_outline
					</i>
				</span>
			</label>


			<div class="col-lg-9">
				<span class="switch prestashop-switch fixed-width-lg">
					<input type="radio" name="EGOI_DEBUG_MODE" id="EGOI_DEBUG_MODE_on" value="1" {if Configuration::get('EGOI_DEBUG_MODE')}checked="checked"{/if}>
					<label for="EGOI_DEBUG_MODE_on">{l s='Yes' mod='smartmarketingps'}</label>

					<input type="radio" name="EGOI_DEBUG_MODE" id="EGOI_DEBUG_MODE_off" value="0" {if !Configuration::get('EGOI_DEBUG_MODE')}checked="checked"{/if}>
					<label for="EGOI_DEBUG_MODE_off">{l s='No' mod='smartmarketingps'}</label>

					<a class="slide-button btn"></a>
				</span>
				<div class="sync_debug_mode" style="display:none;"></div>
				<div data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Error saving debug mode!' mod='smartmarketingps'}" id="debug_error" style="display:none;">
					<i class="material-icons action-enabled">error</i>
				</div>
			</div>
			<p>&nbsp;</p>
		</div>

		<div class="form-group">
			<a class="btn btn-default" id="egoiDocumentation_button"
			   href="{l s='egoiDocumentationLink' mod='smartmarketingps'}"
			   target="_blank">
				<div style="font-size: 13px; display: inline-block; vertical-align: super;">
					{l s='E-goi Documentation' mod='smartmarketingps'}
				</div>
				<i class="material-icons action-enabled" style="font-size: 18px;">navigate_next</i>
			</a>

			<a class="btn btn-default" id="account_button">
				<div style="font-size: 13px;display: inline-block;vertical-align: super;">{l s='Go to My Account' mod='smartmarketingps'}</div>
				<i class="material-icons action-enabled" style="font-size: 18px;">navigate_next</i>
			</a>
		</div>
	</div>
</form>
