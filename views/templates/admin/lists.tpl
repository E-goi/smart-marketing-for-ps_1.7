
	<div class="panel">
		<div class="egoi panel-heading"><span class="icon-list" id="lists"></span> <span class="baseline">{l s='My Lists' mod='smartmarketingps'}</span></div>
		{if $lists}
			<table class="table">
				<thead>
					<tr>
						<th>{l s='List ID' mod='smartmarketingps'}</th>
						<th>{l s='Title' mod='smartmarketingps'}</th>
						<th>{l s='Internal Title' mod='smartmarketingps'}</th>
						<th>{l s='Active' mod='smartmarketingps'}</th>
						<th>{l s='Total' mod='smartmarketingps'}</th>
						<th>{l s='Language' mod='smartmarketingps'}</th>
						<th>{l s='Edit' mod='smartmarketingps'}</th>
					</tr>
				</thead>
				{foreach $lists as $key => $list}
					<tr>
						<td width="60">{$list.listnum}</td>
						<td width="250">{$list.title}</td>
						<td width="250">{$list.title_ref}</td>
						<td width="100">{$list.subs_activos}</td>
						<td width="100">{$list.subs_total}</td>
						<td width="100">{$list.idioma}</td>
						<td width="100">
							<a href="http://bo.e-goi.com/?from={$url_list}{$list.listnum}" class="btn btn-default" target="_blank">
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
				<tr>
					<td>
						<label for="egoi_ps_lang">{l s='Language' mod='smartmarketingps'}</label>
					</td>
					<td>
						<select name='egoi_ps_lang'>
							<option value='en'>English</option>
							<option value='pt'>Portuguese</option>
							<option value='br'>Portuguese (Brasil)</option>
							<option value='es'>Spanish</option>
						</select>
					</td>
				</tr>
				</table>

				<input type="submit" id="add-list" name="add-list" value="{l s='Save List' mod='smartmarketingps'}" class="btn btn-primary">
			</form>
		{else}
			{l s='Error retrieving lists from E-goi' mod='smartmarketingps'}
		{/if}
	</div>

