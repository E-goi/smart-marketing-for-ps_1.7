
{if isset($form_id) and ($form_id)}
	
	{if isset($popup) and ($popup)}

		{if isset($once) and ($once)}
			<script type="text/javascript">
				$(document).ready(function(){
					if ($.cookie('show_popup') == null) {
						$.cookie('show_popup', 'yes', { expires: 7, path: '/' });
						$('#egoi-form').modal({
						    show: true
						});
					}
				});
			</script>
		{else}
			<script type="text/javascript">
				$(document).ready(function(){
					$('#egoi-form').modal({
					    show: true
					});
				});
			</script>
		{/if}

		<div class="modal fade" id="egoi-form" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h2 class="modal-title">{$form_title}</h2>
					</div>
					<div class="modal-body">
						<form method="post" action="">
							<div id="smart_form">
								{if isset($error_submit) and ($error_submit)} 
									<p class="egoi-error">
										{$error_submit}
									</p>
								{elseif isset($success_submit) and ($success_submit)}
									<p class="egoi-success">
										{$success_submit}
									</p>
								{/if}

								<input type="hidden" name="form_id" value="{$form_id}">
								{$content nofilter}
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	{else}
		
		<div id="smart_form" {if isset($header) and ($header)} style="width:50%;margin-left:25%;text-align:center;margin-top:10%;" {/if}>
			{if isset($form_type) and ($form_type eq 'iframe')}
				{$content}
			{else}

				{if isset($error_submit) and ($error_submit)}
					<p class="egoi-error">
						{$error_submit}
					</p>
				{elseif isset($success_submit) and ($success_submit)}
					<p class="egoi-success">
						{$success_submit}
					</p>
				{/if}

				{$content nofilter}
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
