{*
* Smart Marketing
*
*  @author E-goi
*  @copyright 2019 E-goi
*  @license LICENSE.txt
*}

<div class="row mt20">
    <label class="col-xs-3 p5" for="egoi-sms-messages-languages">{l s='Language' mod='smartmarketingps'}</label>
    <div class="col-xs-9">
        <select id="egoi-sms-messages-languages" class="form-control" name="egoi-sms-messages-languages" onchange="this.form.submit()">
            {html_options values=$langIds output=$locales selected=$defaultLang}
        </select>
        <p class="col-xs-12 p5">{l s='Language to configure SMS content' mod='smartmarketingps'}</p>
    </div>
</div>