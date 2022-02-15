{*
* Smart Marketing
*
*  @author    E-goi
*  @copyright 2018 E-goi
*  @license   LICENSE.txt
*}
{if isset($activate) and ($activate eq 1)}
	{$te nofilter}{* No escape necessary for this tracking code*}
{/if}

{if !empty($wp)}
	{$wp nofilter}
{/if}
