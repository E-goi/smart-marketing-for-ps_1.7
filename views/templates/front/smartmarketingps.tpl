
{if isset($form_id) and ($form_id)}

	{include './ecommerce/te.tpl'}

	{if $bootstrap}
		<script type="text/javascript">
			$(document).ready(function(){
				$('input#egoi_form button').attr('class', 'form-control');
				$('.egoi_form input').attr('class', 'form-control');
				$('input[type="submit"]').attr('class', 'form-control');
			});
		</script>
	{/if}
	
	{if $popup}

		{if $once}
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
							<div id="egoi_form">
								{if $error_submit} 
									<p class="egoi-error">
										{$error_submit}
									</p>
								{elseif $success_submit}
									<p class="egoi-success">
										{$success_submit}
									</p>
								{/if}

								<input type="hidden" name="form_id" value="{$form_id}">
								{$content}
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	{else}
		
		<div class="egoi_form" {if $header} style="width:50%;margin-left:25%;text-align:center;margin-top:10%;" {/if}>	
			{if $form_type eq 'iframe'}
				{$content}
			{else}

				{if $error_submit} 
					<p class="egoi-error">
						{$error_submit}
					</p>
				{elseif $success_submit}
					<p class="egoi-success">
						{$success_submit}
					</p>
				{/if}

				{$content}
			{/if}
		</div>

	{/if}

{/if}