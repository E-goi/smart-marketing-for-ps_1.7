{*
* Smart Marketing
*
*  @author    E-goi
*  @copyright 2018 E-goi
*  @license   LICENSE.txt
*}

{include file='./alerts.tpl'}

	<style>
		.banner{
			background: white;
			position: fixed;
			text-align: center;
			width: 70%;
			top: 20%;
			left: 22vw;
			z-index: 10000;
			border: 1px solid #dbe6e9;
			border-radius: 5px;
			box-shadow: 0 0 4px 0 rgb(0 0 0 / 6%);
		}

		.btn-banner-forms{
			position: absolute;
			z-index: 20;
			top: 2%;
			left: 98%;
			cursor: pointer;
		}

		.btn-findmore-forms{
			bottom: 10%;
			position: absolute;
			font-style: normal;
			font-weight: normal;
			font-size: 1.5em !important;
			padding: 1vh 4vw 1vh 4vw;
			background: #00AEDA;
			color: #FFFFFF !important;
			border: 0;
			cursor: pointer;
		}

		.connected-sites{
			width: 100%;
			position: relative;
		}
		
		.cs-title{
			width: 100%;
    		text-align: center;
			position: absolute;
			top: 12%;
			z-index: 100;
			font-family: Open Sans;
			font-size: 1.5vw;
		}

		.cs-description{
			position: absolute;
			width: 100%;
    		text-align: center;
			top: 18%;
			z-index: 100;
			font-family: Open Sans;
			font-size: 30px;
			font-size: 0.8vw;
		}

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
							<option value="embedded" selected >{l s='E-goi Embbeded Form' mod='smartmarketingps'}</option>
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
					
					{if $type eq 'embedded' or $type eq 'html'}

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

	{else}
	{if $connectedsites eq 0}
	<div class="banner" id="forms_banner">

		<i class="icon-close btn-banner-forms" id="btn_banner_close"></i>
		<p class="cs-title">{l s='Do you already know connected sites?' mod='smartmarketingps'}</p>
		<p class="cs-description">{l s='Connected Site may automatically embed our various products on your site.' mod='smartmarketingps'}</p>
		<img src="{_MODULE_DIR_}smartmarketingps/img/connected-sites_final.png" class="connected-sites">


		<div style="width: 80%; text-align: center">
			<a type="button" class="btn-findmore-forms" href="https://helpdesk.e-goi.com/205361-Connected-Sites-O-que-%C3%A9-e-como-usar" target="_blank">{l s='Find more' mod='smartmarketingps'}</a>
		</div>
	</div>
	{/if}
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
							<td>{$forms.form_id|escape:'htmlall':'UTF-8'}</td>
							<td>{$forms.form_title|escape:'htmlall':'UTF-8'}</td>
							<td>{$forms.form_type|escape:'htmlall':'UTF-8'}</td>
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
								<a class="del-form" href="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}&form={$forms.form_id|escape:'htmlall':'UTF-8'}&del={$forms.form_id|escape:'htmlall':'UTF-8'}">
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

			<a id="add-form" data-href="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}&form={if isset($totalforms)}{$totalforms+1|escape:'htmlall':'UTF-8'}{else}1{/if}&type=embedded"></a>
		</div>

	{/if}
