
{include file='./alerts.tpl'}

<div class="panel">
	<div class="egoi panel-heading">
		<span class="icon-user" id="account"></span> <span class="baseline">{l s='Account Details' mod='smartmarketingps'}</span>
	</div>

	{if $clientData}
		<table class="table">
			<thead>
				<th>
					{l s='Client ID' mod='smartmarketingps'}
				</th>
				<th>
					{l s='Company Name' mod='smartmarketingps'}
				</th>
				<th>
					{l s='E-goi Plan' mod='smartmarketingps'}
				</th>
				<th>
					{l s='Registration Date' mod='smartmarketingps'}
				</th>
				<th></th>
			</thead>
			<tbody>
				<tr>
					<td>{if isset($clientData['CLIENTE_ID'])} {$clientData['CLIENTE_ID']} {/if}</td>
					<td>{if $clientData['COMPANY_NAME'] != "E-goi"} {$clientData['COMPANY_NAME']} {else} - {/if}</td>
					<td>-</td>
					<td>{if isset($clientData['DATE_REGISTRATION'])} {$clientData['DATE_REGISTRATION']} {/if}</td>
					<td>
						<a href="{$redirect}" class="btn btn-primary" style="font-size:13px;text-transform:none;">
							{l s='Change E-goi API Key' mod='smartmarketingps'} <span class="icon-edit"></span>
						</a>
					</td>
				</tr>
			</tbody>
		</table>
	{else}
		{l s='Error retrieving client data' mod='smartmarketingps'}
	{/if}
</div>

{include file='./lists.tpl'}

