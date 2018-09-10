{*
* Smart Marketing
*
*  @author    E-goi
*  @copyright 2018 E-goi
*  @license   LICENSE.txt
*}

{include file='./alerts.tpl'}

	<style>
		.bootstrap .table tbody>tr>td{
			padding: 16px 7px;
			height: 75px;
		}

		.form_type{
			width: 25% !important;
		}

		select{
			width: 50% !important;
			display: inline-block !important;
		}

		.ps-tab{
			padding-top: 10px;
			font-size: 16px;
		}
		.current{
			border-bottom: 3px solid #3ed2f0;
		}

		.egoi-icon{
			font-size: 20px;
		}

		{if isset($type) and ($type eq 'iframe')}
			#panel_egbody{
				height: 550px;
			}
		{else}
			#panel_egbody{
				height: 720px;
			}
		{/if}

		.bootstrap hr{
			margin-top: 10px;
		}
	</style>

	{if isset($form) and ($form)}

		<div class="panel" id="panel_egbody">
			<div class="egoi panel-heading">
				<div class="col-md-8">
					<span class="icon-file-text" id="forms"></span>
					<span class="baseline">{l s='My Forms >> Form' mod='smartmarketingps'} {$form|escape:'htmlall':'UTF-8'}</span>
				</div>

				<div class="col-md-2">
					<div class="ps-tab current" id="content_link" data-block="content" style="cursor: pointer">
						{l s='Content' mod='smartmarketingps'}
					</div>
				</div>

				<div class="col-md-2">
					<div class="ps-tab" id="widget_link" data-block="widgets" style="cursor: pointer">
						{l s='Frontend Settings' mod='smartmarketingps'}
					</div>
				</div>
			</div>

			<div style="height:40px; margin-top:20px;">
				<form method="get" action="">
					<input type="hidden" name="controller" value="Forms">
					<input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}">
					<input type="hidden" name="form" value="{$form|escape:'htmlall':'UTF-8'}">
					<span class="label_span col-md-3"><b class="egoi-b">{l s='Select the Form Type you want' mod='smartmarketingps'}</b></span>
					<div class="form_name_egoi" style="padding:0 14px;">
						<select name="type" class="form_type" onchange="this.form.submit();">
							<option value="popup" {if isset($type) and ($type eq 'popup')} selected {/if}>{l s='E-goi Form Popup' mod='smartmarketingps'}</option>
							<option value="html" {if isset($type) and ($type eq 'html')} selected {/if}>{l s='E-goi Form HTML' mod='smartmarketingps'}</option>
							<option value="iframe" {if isset($type) and ($type eq 'iframe')} selected {/if}>{l s='E-goi Form IFRAME' mod='smartmarketingps'}</option>
						</select>
					</div>
				</form>
			</div>
			<hr/>

			<form name="form1" id="form1" action="" method="post">
				<div id="content_egoi" style="padding-left: 20px;">
					<table class="table">
						<tr>
							<td>
								<b>{l s='Form Name' mod='smartmarketingps'}</b>
							</td>
							<td>
								<input style="width: 75%;" type="text" name="form_title" placeholder="{l s='My Form Name' mod='smartmarketingps'}" value="{$form_title|escape:'htmlall':'UTF-8'}" required>
							</td>
						</tr>
						<tr>
							<td style="width: 26%;">
								<b>{l s='Enable this Form' mod='smartmarketingps'}</b>
							</td>
							<td>
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="enable" id="enable0" value="1" {if $enable eq '1'} checked {/if}>
									<label for="enable0">{l s='Yes' mod='smartmarketingps'}</label>
									<input type="radio" name="enable" id="enable1" value="0" {if $enable eq '0' or $enable eq ''} checked {/if}>
									<label for="enable1">{l s='No' mod='smartmarketingps'}</label>
									<a class="slide-button btn"></a>
								</span>
							</td>
						</tr>
					</table>

					{if $type eq 'iframe'}

						<table class="table">
							<tr>
								{if !$lists}
									{l s='No lists found, are you connected to E-goi and/or have created lists?' mod='smartmarketingps'}
								{else}
									<td>
										<b>{l s='Select your List' mod='smartmarketingps'}</b>
									</td>
									<td>
										<select name="list_id" id="list_id" required>
											<option value="" disabled selected>
                                                {l s='Select List ...' mod='smartmarketingps'}
											</option>
											{foreach $lists as $list}
												{if isset($list_id) and ($list_id eq $list.listnum)}
													<option value="{$list.listnum|escape:'htmlall':'UTF-8'}" selected="selected">{$list.title|escape:'htmlall':'UTF-8'}</option>
												{else}
													<option value="{$list.listnum|escape:'htmlall':'UTF-8'}">{$list.title|escape:'htmlall':'UTF-8'}</option>
												{/if}
											{/foreach}
										</select>
										<div class="sync_list" style="display: none;"></div>
										<i class="material-icons" id="sync_success" style="display: none;">beenhere</i>
									</td>
                                {/if}
							</tr>
							<tr>
								<td>
									<b>{l s='Select your Form' mod='smartmarketingps'}</b>
								</td>
								<td>
									<select name="form" id="formid_egoi" required>
										<option value="" disabled selected>
											{l s='Select first your list ...' mod='smartmarketingps'}
										</option>
										{if isset($myforms) and ($myforms)}
											{foreach $myforms as $form_egoi}
												{if $form_data eq $form_egoi.url}
													<option value="{$form_egoi.url|escape:'htmlall':'UTF-8'}" selected="selected">
														{$form_egoi.title|escape:'htmlall':'UTF-8'}
													</option>
												{else}
													<option value="{$form_egoi.url|escape:'htmlall':'UTF-8'}">{$form_egoi.title|escape:'htmlall':'UTF-8'}</option>
												{/if}
											{/foreach}
										{/if}
									</select>
									<div id="show_preview" {if isset($list_id) and ($list_id)} {else} style="display: none; {/if}">
										<a data-toggle="modal" class="btn" data-target="#preview">{l s='Preview' mod='smartmarketingps'}</a>
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<b>{l s='Box Dimensions' mod='smartmarketingps'}</b>
									<p class="egoi-help">{l s='Values in px' mod='smartmarketingps'}</p>
								</td>
								<td>
									<input type="text" style="display:-webkit-inline-box;width:25%;" placeholder="{l s='Width' mod='smartmarketingps'}" maxlength="5" name="style_width" value="{$style_width|escape:'htmlall':'UTF-8'}">
									<input type="text" style="display:-webkit-inline-box;width:25%;" placeholder="{l s='Height' mod='smartmarketingps'}" maxlength="5" name="style_height" value="{$style_height|escape:'htmlall':'UTF-8'}">
								</td>
							</tr>
						</table>

					{elseif $type eq 'popup' or $type eq 'html'}

						{* $editor *}
						<div class="form-group">
							<textarea placeholder="{l s='Insert the' mod='smartmarketingps'} {$type|upper|escape:'htmlall':'UTF-8'} {l s='Content here' mod='smartmarketingps'}" name="form_content" id="form_content" cols="50" rows="20">{$form_content}</textarea>
						</div>
						<p>{l s='Use Bootstrap?' mod='smartmarketingps'} &nbsp;
							<span class="switch prestashop-switch fixed-width-lg">
								<input type="radio" name="is_bootstrap" id="doptin0" value="1" {if $is_bootstrap eq '1'} checked {/if}>
								<label for="doptin0">{l s='Yes' mod='smartmarketingps'}</label>
								<input type="radio" name="is_bootstrap" id="doptin1" value="0" {if $is_bootstrap eq '0' or $is_bootstrap eq ''} checked {/if}>
								<label for="doptin1">{l s='No' mod='smartmarketingps'}</label>
								<a class="slide-button btn"></a>
							</span>
						</p>
					{/if}
				</div>

				{* Frontend *}
				<div id="widgets_egoi" style="display: none; padding-left: 20px;">
					<table class="table">
						<tr>
							<td>
								<b>{l s='Show this form in Header' mod='smartmarketingps'}</b>
								<p>
								{l s='To be fully efficient you need change positions from header in' mod='smartmarketingps'} <a href="index.php?controller=AdminModulesPositions">{l s='Positions' mod='smartmarketingps'}</a>
								</p>
							</td>
							<td>
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="block_header" id="header0" value="1" {if $block_header eq '1'} checked {/if}>
									<label for="header0">{l s='Yes' mod='smartmarketingps'}</label>
									<input type="radio" name="block_header" id="header1" value="0" {if $block_header eq '0' or $block_header eq ''} checked {/if}>
									<label for="header1">{l s='No' mod='smartmarketingps'}</label>
									<a class="slide-button btn"></a>
								</span>
							</td>
						</tr>
						<tr>
							<td>
								<b>{l s='Show this form in Footer' mod='smartmarketingps'}</b>
								<p>
								{l s='To be fully efficient you need change positions from footer in' mod='smartmarketingps'} <a href="index.php?controller=AdminModulesPositions">{l s='Positions' mod='smartmarketingps'}</a>
								</p>
							</td>
							<td>
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="block_footer" id="footer0" value="1" {if $block_footer eq '1'} checked {/if}>
									<label for="footer0">{l s='Yes' mod='smartmarketingps'}</label>
									<input type="radio" name="block_footer" id="footer1" value="0" {if $block_footer eq '0' or $block_footer eq ''} checked {/if}>
									<label for="footer1">{l s='No' mod='smartmarketingps'}</label>
									<a class="slide-button btn"></a>
								</span>
							</td>
						</tr>

						<tr>
							<td>
								<b>{l s='Show this form in Home Page' mod='smartmarketingps'}</b>
							</td>
							<td>
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="block_home" id="home1" value="1" {if $block_home eq '1'} checked {/if}>
									<label for="home1">{l s='Yes' mod='smartmarketingps'}</label>
									<input type="radio" name="block_home" id="home2" value="0" {if $block_home eq '0' or $block_home eq ''} checked {/if}>
									<label for="home2">{l s='No' mod='smartmarketingps'}</label>
									<a class="slide-button btn"></a>
								</span>
							</td>
						</tr>

						<tr {if !$block_home} style="display: none;" {/if} id="popup_form">
							<td>
								<b>{l s='Show in Popup?' mod='smartmarketingps'}</b>
							</td>
							<td>
								<span class="switch prestashop-switch status fixed-width-lg">
									<input type="radio" name="popup" id="popup1" value="1" {if $popup eq '1'} checked {/if}>
									<label for="popup1">{l s='Yes' mod='smartmarketingps'}</label>
									<input type="radio" name="popup" id="popup2" value="0" {if $popup eq '0' or $popup eq ''} checked {/if}>
									<label for="popup2">{l s='No' mod='smartmarketingps'}</label>
									<a class="slide-button btn"></a>
								</span>
							</td>
						</tr>

						<tr {if !$block_home} style="display: none;" {/if} id="once">
							<td>
								<b>{l s='Show only Once?' mod='smartmarketingps'}</b>
							</td>
							<td>
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="once" id="once1" value="1" {if $once eq '1'} checked {/if}>
									<label for="once1">{l s='Yes' mod='smartmarketingps'}</label>
									<input type="radio" name="once" id="once2" value="0" {if $once eq '0' or $once eq ''} checked {/if}>
									<label for="once2">{l s='No' mod='smartmarketingps'}</label>
									<a class="slide-button btn"></a>
								</span>
							</td>
						</tr>
					</table>
				</div>
				
				<input type="hidden" name="form_type" value="{$type}">
				<input type="hidden" name="form_id" value="{$form}">
				<input type="submit" name="save-form" id="save-form" value="1" style="display: none;">
			</form>

		{if $type eq 'iframe'}
			{* Modal Preview *}
			<div class="modal fade" id="preview" role="dialog">
			    <div class="modal-dialog" style="width:730px;height:400px;">
			     	<div class="modal-content">
				        <div class="modal-body">
							<div id="egoi_form_inter">
								<iframe id="prev_iframe" src="" width="700" height="600" style="border: 0 none;" onload="window.parent.parent.scrollTo(0,0);"></iframe>
							</div>
				        </div>
				        <div class="modal-footer">
				          <button type="button" class="btn btn-default" id="close_fields" data-dismiss="modal">{l s='Close' mod='smartmarketingps'}</button>
				        </div>
			      	</div>
			    </div>
			</div>
		{/if}

	{else}

		<div class="panel">
			<div class="egoi panel-heading">
				<span class="icon-file-text" id="forms"></span> <span class="baseline">{l s='My Forms' mod='smartmarketingps'}</span>
			</div>
			<table class="table" id="egoi-table">
				<thead>
					<tr>
						<th>{l s='Form ID' mod='smartmarketingps'}</th>
						<th>{l s='Form Title' mod='smartmarketingps'}</th>
						<th>{l s='Form Type' mod='smartmarketingps'}</th>
						<th>{l s='Active' mod='smartmarketingps'}</th>
						<th colspan="2">{l s='Options' mod='smartmarketingps'}</th>
					</tr>
				</thead>
				
				{if isset($allforms) and !empty($allforms)}
					{foreach $allforms as $forms}
						<tr>
							<td>{$forms.form_id}</td>
							<td>{$forms.form_title}</td>
							<td>{$forms.form_type}</td>
							<td>
								{if $forms.enable}
									<i class="material-icons action-enabled egoi-icon" style="color:#78d07d;">check</i> 
								{else} 
									<i class="material-icons action-enabled egoi-icon" style="color:#900;">not_interested</i>
								{/if} 
							</td>
							<td>
								<a href="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}&form={$forms.form_id|escape:'htmlall':'UTF-8'}&type={$forms.form_type|escape:'htmlall':'UTF-8'}">
									<i class="material-icons action-enabled egoi-icon">edit</i>
								</a>
							</td>
							<td>
								<a class="del-form" href="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}&form={$forms.form_id|escape:'htmlall':'UTF-8'}&del={base64_encode($forms.form_id|escape:'htmlall':'UTF-8')}">
									{l s='Delete' mod='smartmarketingps'}
								</a>
							</td>
						</tr>
					{/foreach}
				{else}
					<td colspan='4'>
						<div class="alert alert-info">{l s='Your Subscriber Forms are empty' mod='smartmarketingps'}</div>
					</td>
				{/if}
			</table>

			<span id="del-info" style="display: none;">{l s='You are certain to remove this form?' mod='smartmarketingps'}</span>

			<a id="add-form" data-href="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}&form={if isset($totalforms)}{$totalforms+1|escape:'htmlall':'UTF-8'}{else}1{/if}&type=html"></a>
		</div>

	{/if}
