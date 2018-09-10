{*
* Smart Marketing
*
*  @author    E-goi
*  @copyright 2018 E-goi
*  @license   LICENSE.txt
*}

<div id='egoi_fields_{$post_id|escape:'htmlall':'UTF-8'}'>
	<div class='col-sm-5' style='font-size: 14px;padding-top: 10px;'>{$ps_name|escape:'htmlall':'UTF-8'}</div>
	<div class='col-sm-5' style='font-size: 14px;padding-top: 10px;'>{$egoi_name|escape:'htmlall':'UTF-8'}</div>
	<div class='col-sm-2' style='padding-top: 10px;'>
		<button type='button' id='field_{$post_id|escape:'htmlall':'UTF-8'}' class='egoi_fields btn btn-default' data-target='{$post_id|escape:'htmlall':'UTF-8'}'>
		<span class='icon-trash'></span></button>
	</div>
</div>
