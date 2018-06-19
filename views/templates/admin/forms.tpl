
{include file='./alerts.tpl'}

	<style>
		.table {
			font-size: 14px;
		}
		.egoi-icon{
			font-size: 20px;
		}

		.col-lg-9{
			width: 100% !important;
		}
		#mce_41-open{
			display: none !important;
		}

		{if isset($type) and ($type eq 'iframe')}
			#panel_egbody{
				height: 400px;
			}
		{else}
			#panel_egbody{
				height: 660px;
			}
		{/if}
		.bootstrap hr{
			margin-top: 10px;
		}
	</style>

	{if isset($form) and ($form)}

		<div class="panel" id="panel_egbody">
			<div class="egoi panel-heading">
				<span class="icon-file-text" id="forms"></span> <span class="baseline">{l s='My Forms >> Form' mod='smartmarketingps'} {$form}</span>
			</div>

			<div style="height:40px;" >
				<form method="get" action="">
					<input type="hidden" name="controller" value="Forms">
					<input type="hidden" name="token" value="{$token}">
					<input type="hidden" name="form" value="{$form}">
					<span class="label_span col-md-3"><b class="egoi-b">{l s='Select the Form Type you want' mod='smartmarketingps'}</b></span>
					<div class="form_name_egoi" style="padding:0 14px;">
						<select name="type" style="width: 250px;" onchange="this.form.submit();">
							<option value="popup" {if isset($type) and ($type eq 'popup')} selected {/if}>{l s='E-goi Form Popup' mod='smartmarketingps'}</option>
							<option value="html" {if isset($type) and ($type eq 'html')} selected {/if}>{l s='E-goi Form HTML' mod='smartmarketingps'}</option>
							<option value="iframe" {if isset($type) and ($type eq 'iframe')} selected {/if}>{l s='E-goi Form IFRAME' mod='smartmarketingps'}</option>
						</select>
					</div>
				</form>
			</div>
			<hr/>

			<form name="form1" id="form1" action="" method="post">

				<div class="categoriesTitle col-md-3" style="padding-right:20px;">
					<div class="list-group">
						<a class="list-group-item active" id="editor_link" {if $type eq 'iframe'} onclick="form_egoi_exc(1);" {else} onclick="form_egoi(1);" {/if}>
							{l s='Content' mod='smartmarketingps'}
						</a>
						{if $type eq 'popup' or $type eq 'html'}
							<a class="list-group-item" id="msg_link" onclick="form_egoi(2);">
								{l s='Success/Error Messages' mod='smartmarketingps'}
							</a>
						{/if}
						<a class="list-group-item" id="sett_link" {if $type eq 'iframe'} onclick="form_egoi_exc(3);" {else} onclick="form_egoi(3);" {/if}>
							{l s='Settings' mod='smartmarketingps'}
						</a>
						<a class="list-group-item" id="widget_link" {if $type eq 'iframe'} onclick="form_egoi_exc(4);" {else} onclick="form_egoi(4);" {/if}>
							{l s='Frontend Settings' mod='smartmarketingps'}
						</a>
					</div>
				</div>

				{* Editor Content *}
				<div id="editor_egoi" style="padding-left: 20px;">
					<div class="form_name_egoi">
						<input type="text" name="form_title" placeholder="{l s='My Form Name' mod='smartmarketingps'}" value="{$form_title}" required>
					</div>
					<div style="float:right;width:75%;">

						<div style="float:left;"><b>{l s='Enable this Form' mod='smartmarketingps'}</b></div>
						<div class="enable">
							<span class="switch prestashop-switch fixed-width-lg">
								<input type="radio" name="enable" id="enable0" value="1" {if $enable eq '1'} checked {/if}>
								<label for="enable0">{l s='Yes' mod='smartmarketingps'}</label>
								<input type="radio" name="enable" id="enable1" value="0" {if $enable eq '0' or $enable eq ''} checked {/if}>
								<label for="enable1">{l s='No' mod='smartmarketingps'}</label>
								<a class="slide-button btn"></a>
							</span>
						</div>

					{if $type eq 'iframe'}
						<div class="form-group" style="padding-bottom:40px;margin-top:50px;">							
							{if isset($myforms) and ($myforms)}
								<div style="float:left;"><b>{l s='E-goi Form..' mod='smartmarketingps'}</b></div>
								<div class="form">
									<select name="form" id="formid_egoi">
										<option value="">
											{l s='Select a Form..' mod='smartmarketingps'}
										</option>
										{foreach $myforms as $form_egoi}
											{if $form_data eq $form_egoi.url}
												<option value="{$form_egoi.url}" selected="selected">
													{$form_egoi.title}
												</option>
											{else}
												<option value="{$form_egoi.url}">{$form_egoi.title}</option>
											{/if}

										{/foreach}
									</select>
								</div>
							{else}
								<b>{l s='No forms found, you must set your list in Settings' mod='smartmarketingps'}</b>
							{/if}

						</div>

						<div style="float:left;"><b>{l s='Box Width and Height' mod='smartmarketingps'}</b></div>
						<div class="styles">
							<input type="text" style="display:-webkit-inline-box;width:25%;" placeholder="Width" maxlength="5" name="style_width" value="{$style_width}">
							<input type="text" style="display:-webkit-inline-box;width:25%;" placeholder="Height" maxlength="5" name="style_height" value="{$style_height}">
							<p class="egoi-help">(values in px)</p>
						</div>
						

					{elseif $type eq 'popup' or $type eq 'html'}

						{* $editor *}
						<div class="form-group">
							<textarea placeholder="{l s='Insert the' mod='smartmarketingps'} {$type|upper} {l s='Content here' mod='smartmarketingps'}" name="form_content" id="form_content" cols="50" rows="20">{$form_content}</textarea>
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

				</div>

				{if $type eq 'popup' or $type eq 'html'}
					{* Error messages *}
					<div id="msg_egoi" style="display: none;padding-left: 20px;" class="form_egoi_main">
						<div class="form-group">
							<input type="text" name="msg_gen" class="form-control" placeholder="{l s='General error' mod='smartmarketingps'}" value="{$msg_gen}">
							<p class="egoi-help">{l s='Ex: Error!' mod='smartmarketingps'}</p>
						</div>
						<div class="form-group">
							<input type="text" name="msg_invalid" class="form-control" placeholder="{l s='Error when Email is invalid' mod='smartmarketingps'}" value="{$msg_invalid}">
							<p class="egoi-help">{l s='Ex: Invalid Message!' mod='smartmarketingps'}</p>
						</div>
						<div class="form-group">
							<input type="text" name="msg_exists" class="form-control" placeholder="{l s='Error when Subscriber Already exists' mod='smartmarketingps'}" value="{$msg_exists}">
							<p class="egoi-help">{l s='Ex: Subscriber already exists!' mod='smartmarketingps'}</p>
						</div>
						<div class="form-group">
							<input type="text" name="success" class="form-control" placeholder="{l s='To be displayed when form is successfull submitted' mod='smartmarketingps'}" value="{$success}">
							<p class="egoi-help">{l s='Ex: User succesfully subscribed!' mod='smartmarketingps'}</p>
						</div>
						<p>&nbsp;</p>
					</div>
				{/if}

				{* Settings *}
				<div id="settings_egoi" style="display: none;" class="form_egoi_main">
					<div class="sett">
						<div style="float:left;"><b>{l s='Hide after Success' mod='smartmarketingps'}</b></div>
						<div class="hide_form">
							<span class="switch prestashop-switch fixed-width-lg">
								<input type="radio" name="hide" id="hide0" value="1" {if $hide eq '1'} checked {/if}>
								<label for="hide0">{l s='Yes' mod='smartmarketingps'}</label>
								<input type="radio" name="hide" id="hide1" value="0" {if $hide eq '0' or $hide eq ''} checked {/if}>
								<label for="hide1">{l s='No' mod='smartmarketingps'}</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>

					{if $type eq 'iframe'}
						<div class="sett">
							{if !$lists}
								{l s='No lists found, are you connected to E-goi and/or have created lists?' mod='smartmarketingps'}
							{else}
								<div style="float:left;"><b>{l s='List' mod='smartmarketingps'}</b></div>
								<div class="list">
									<select name="list">
										<option disabled>
											{l s='Select a list..' mod='smartmarketingps'}
										</option>
										{foreach $lists as $list}
											{if isset($list_id) and ($list_id eq $list.listnum)}
												<option value="{$list.listnum}" selected="selected">{$list.title}</option>
											{else}
												<option value="{$list.listnum}">{$list.title}</option>
											{/if}

										{/foreach}
									</select>
								</div>
							{/if}
						</div>
					{else}
						<div class="sett">
						</div>
					{/if}
					
					<div class="sett">
						<input type="text" name="redirect" placeholder="{l s='Redirect to URL' mod='smartmarketingps'}" value="{$redirect}">
						<p class="egoi-help">{l s='Use absolute url - example: http://yourdomain.com/page' mod='smartmarketingps'}</p>
					</div>

				</div>

				{* Frontend *}
				<div id="widgets_egoi" style="display: none;" class="form_egoi_main">
					<div class="reg">
						<div style="float:left;"><b>{l s='Show this form in Header' mod='smartmarketingps'}</b></div>
						<div class="register">
							<span class="switch prestashop-switch fixed-width-lg">
								<input type="radio" name="block_header" id="header0" value="1" {if $block_header eq '1'} checked {/if}>
								<label for="header0">{l s='Yes' mod='smartmarketingps'}</label>
								<input type="radio" name="block_header" id="header1" value="0" {if $block_header eq '0' or $block_header eq ''} checked {/if}>
								<label for="header1">{l s='No' mod='smartmarketingps'}</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>

					<div class="reg">
						<div style="float:left;"><b>{l s='Show this form in Footer' mod='smartmarketingps'}</b></div>
						<div class="register">
							<span class="switch prestashop-switch fixed-width-lg">
								<input type="radio" name="block_footer" id="footer0" value="1" {if $block_footer eq '1'} checked {/if}>
								<label for="footer0">{l s='Yes' mod='smartmarketingps'}</label>
								<input type="radio" name="block_footer" id="footer1" value="0" {if $block_footer eq '0' or $block_footer eq ''} checked {/if}>
								<label for="footer1">{l s='No' mod='smartmarketingps'}</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>
					<p class="egoi-help" style="text-indent:40px; {if $block_footer eq '0' or $block_footer eq ''} display:none; {/if}" id="help_footer">
						<b>{l s='To be fully efficient you need change positions from footer in' mod='smartmarketingps'} <a href="index.php?controller=AdminModulesPositions">{l s='Positions' mod='smartmarketingps'}</a></b>
					</p>

					<div class="reg">
						<div style="float:left;"><b>{l s='Show this form in Home Page' mod='smartmarketingps'}</b></div>
						<div class="register">
							<span class="switch prestashop-switch fixed-width-lg">
								<input type="radio" name="block_home" id="home1" value="1" {if $block_home eq '1'} checked {/if}>
								<label for="home1">{l s='Yes' mod='smartmarketingps'}</label>
								<input type="radio" name="block_home" id="home2" value="0" {if $block_home eq '0' or $block_home eq ''} checked {/if}>
								<label for="home2">{l s='No' mod='smartmarketingps'}</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>

					<div class="reg" {if !$block_home} style="display: none;" {/if} id="popup_form">
						<div style="float:left;"><b>{l s='Show in Popup?' mod='smartmarketingps'}</b></div>
						<div class="register">
							<span class="switch prestashop-switch fixed-width-lg">
								<input type="radio" name="popup" id="popup1" value="1" {if $popup eq '1'} checked {/if}>
								<label for="popup1">{l s='Yes' mod='smartmarketingps'}</label>
								<input type="radio" name="popup" id="popup2" value="0" {if $popup eq '0' or $popup eq ''} checked {/if}>
								<label for="popup2">{l s='No' mod='smartmarketingps'}</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>

					<div class="reg" {if !$popup} style="display: none;" {/if} id="once">
						<div style="float:left;"><b>{l s='Show only Once?' mod='smartmarketingps'}</b></div>
						<div class="register">
							<span class="switch prestashop-switch fixed-width-lg">
								<input type="radio" name="once" id="once1" value="1" {if $once eq '1'} checked {/if}>
								<label for="once1">{l s='Yes' mod='smartmarketingps'}</label>
								<input type="radio" name="once" id="once2" value="0" {if $once eq '0' or $once eq ''} checked {/if}>
								<label for="once2">{l s='No' mod='smartmarketingps'}</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>

					<div class="reg">
						<p>&nbsp;</p>
					</div>
				</div>
				
				<div>
					{if $type eq 'iframe'}
						<span><a data-toggle="modal" class="btn" data-target="#preview">{l s='Preview' mod='smartmarketingps'}</a></div>
					{else}
						{if $form_content}
							<span><a style="display: none;" class="btn" href="//{$smarty.server.SERVER_NAME}/index.php?fc=module&module=smartmarketingps&controller=egoi_forms&form={$form}" target="_blank">
							{l s='Preview' mod='smartmarketingps'}</a></div>
						{/if}
					{/if}
				</div>
				
				<input type="hidden" name="form_type" value="{$type}">
				<input type="hidden" name="form_id" value="{$form}">
				<input type="submit" name="save-form" id="save-form" value="1" style="display: none;">
			</form>
		</div>

		{if $type eq 'iframe'}
			{* Modal Preview *}
			<div class="modal fade" id="preview" role="dialog">
			    <div class="modal-dialog" style="width:730px;height:400px;">
			     	<div class="modal-content">
				        <div class="modal-body">
				        	{if $form_data}
				        		<div id="egoi_form_inter">
				        			<iframe src="//{$url}" width="700" height="600" style="border: 0 none;" onload="window.parent.parent.scrollTo(0,0);"></iframe>
				        		</div>
							{else}
				        		<div id="egoi_form_inter"></div>
				        	{/if}

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
						<th>{l s='Active' mod='smartmarketingps'}</th>
						<th colspan="2">{l s='Options' mod='smartmarketingps'}</th>
					</tr>
				</thead>
				
				{if isset($allforms) and !empty($allforms)}
					{foreach $allforms as $forms}
						<tr>
							<td>{$forms.form_id}</td>
							<td>{$forms.form_title}</td>
							<td> 
								{if $forms.enable}
									<i class="material-icons action-enabled egoi-icon" style="color:#78d07d;">check</i> 
								{else} 
									<i class="material-icons action-enabled egoi-icon" style="color:#900;">not_interested</i>
								{/if} 
							</td>
							<td>
								<a href="{$smarty.server.REQUEST_URI}&form={$forms.form_id}&type={$forms.form_type}">
									<i class="material-icons action-enabled egoi-icon">edit</i>
								</a>
							</td>
							<td>
								<a class="del-form" href="{$smarty.server.REQUEST_URI}&form={$forms.form_id}&del={base64_encode($forms.form_id)}">
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

			<a id="add-form" data-href="{$smarty.server.REQUEST_URI}&form={if isset($totalforms)}{$totalforms+1}{else}1{/if}&type=html"></a>
		</div>

	{/if}
