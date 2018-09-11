{*
* Smart Marketing
*
*  @author    E-goi
*  @copyright 2018 E-goi
*  @license   LICENSE.txt
*}

{if isset($error_msg) and ($error_msg)}
	{$error_msg}
{/if}

{if isset($success_msg) and ($success_msg)}
	{$success_msg}
{/if}

{if isset($smart_api_key_error) and ($smart_api_key_error) and (!$config)}
<div class="alert alert-warning" style="text-align:center;">
	<p>
		{l s='No API key configured. Please go to the' mod='smartmarketingps'}
		<a href="index.php?controller=AdminModules&token={getAdminToken tab='AdminModules'}&configure=smartmarketingps">
			{l s='module configuration' mod='smartmarketingps'}
		</a>
		{l s='to add it' mod='smartmarketingps'}.
		<br/>
		
		{l s='If you don\'t have an E-goi Account please' mod='smartmarketingps'} 
		<a href="https://login.egoiapp.com/signup" target="_blank">{l s='Click Here' mod='smartmarketingps'}</a>.
		{l s='If you want to know more about E-goi' mod='smartmarketingps'} 
		<a href="https://www.e-goi.com" target="_blank">{l s='click here' mod='smartmarketingps'}</a></li>
	</p>
</div>
{/if}