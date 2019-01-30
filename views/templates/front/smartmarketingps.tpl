{*
* Smart Marketing
*
*  @author    E-goi
*  @copyright 2018 E-goi
*  @license   LICENSE.txt
*}

{if isset($form_id) and ($form_id)}
	
	{if isset($popup) and ($popup)}

		<div data-modal-show="egoi-show-once" style="display: none;">{if isset($once) and ($once)} 1 {/if}</div>

		<div class="modal fade" data-modal-name="egoi-form">
			<div class="modal__dialog">
				<button class="modal__close" data-modal-dismiss>×</button>
				<header class="modal__header">
					<h3 class="modal__title">{$form_title|escape:'htmlall':'UTF-8'}</h3>
				</header>
				<div class="modal__content">
					<div id="smart_form">
						{$content nofilter}{* No escape necessary for this code *}
					</div>
				</div>
				<footer class="modal__footer">
				</footer>
			</div>
		</div>

	{else}

		<div id="smart_form" {if isset($header) and ($header)} style="width:50%;margin-left:25%;text-align:center;margin-top:10%;" {/if}>
			{if isset($form_type) and ($form_type eq 'iframe')}
				{$content nofilter}{* No escape necessary for this code *}
			{else}
				{$content nofilter}{* No escape necessary for this code *}
			{/if}
		</div>
	{/if}

    {if isset($is_bootstrap) and ($is_bootstrap)}
		<script type="text/javascript">
            var inputs = document.getElementById("smart_form").getElementsByTagName('input');
            var btns = document.getElementById("smart_form").getElementsByTagName('button');
            if (inputs) {
            	for (var i = 0; i < inputs.length; i++) {
                	inputs[i].className = 'form-control';
            	}
			}

            if (btns) {
                for (var j = 0; j < btns.length; j++) {
                    btns[j].className = 'form-control';
                }
            }
		</script>
    {/if}

{/if}
