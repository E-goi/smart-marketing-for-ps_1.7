<script>
    function confirmDeleteCatalog(id) {
        $('#confirm-delete').attr('value', id);
        $('#confirm_modal').modal('show');
    }

    $(document).ready(function () {
        $('#confirm-delete').click(function () {
            window.location.replace("{$smarty.server.REQUEST_URI|replace:'&createCatalog=1':''}&deleteCatalog=" + $('#confirm-delete').attr('value'));
            $('#confirm_modal').modal('hide');
        });
    });
</script>

<div class="modal fade" id="confirm_modal" tabindex="-1" style="display: none; padding-right: 15px;">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{l s='Delete catalog?' mod='smartmarketingps'}</h4>
            </div>

            <div class="modal-body">
                {l s='This catalog and its content will be deleted in your E-goi account. Please confirm!' mod='smartmarketingps'}
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-lg uppercase"
                        data-dismiss="modal">{l s='Close' mod='smartmarketingps'}
                </button>
                <button type="button" id="confirm-delete" class="btn btn-primary btn-lg">
                    {l s='Confirm' mod='smartmarketingps'}
                </button>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="egoi panel-heading">
        <span class="baseline">{l s='Catalogs' mod='smartmarketingps'}</span>
    </div>

    <form name="language" method="post" action="" id="language_filter_form"
          class="table-responsive form-horizontal">
        <table class="grid-table js-grid-table table" id="language_grid_table">
            <thead class="thead-default">
            <tr class="column-headers ">
                <th>
                    <div class="text-center">
                        <b>{l s='ID' mod='smartmarketingps'}</b>
                    </div>
                </th>

                <th>
                    <div class="text-center">
                        <b>{l s='Name' mod='smartmarketingps'}</b>
                    </div>
                </th>

                <th>
                    <div class="text-center">
                        <b>{l s='Language' mod='smartmarketingps'}</b>
                    </div>
                </th>

                <th>
                    <div class="text-center">
                        <b>{l s='Currency' mod='smartmarketingps'}</b>
                    </div>
                </th>

                <th>
                    <div class="text-center">
                        <b>{l s='Automatically Sync Products' mod='smartmarketingps'}</b>
                    </div>
                </th>

                <th>
                    <div class="grid-actions-header-text text-center">
                        <b>{l s='Actions' mod='smartmarketingps'}</b>
                    </div>
                </th>
            </tr>

            </thead>
            <tbody>
            {if $catalogs}
                {foreach $catalogs as $catalog}
                    <tr>
                        <td class="data-type text-center">
                            {l s=$catalog.catalog_id mod='smartmarketingps'}
                        </td>
                        <td class="link-type text-center egoi-catalog-name-listing">
                            {l s=$catalog.title mod='smartmarketingps'}
                        </td>
                        <td class="data-type text-center">
                            {l s=$catalog.language mod='smartmarketingps'}
                        </td>
                        <td class="data-type text-center">
                            {l s=$catalog.currency mod='smartmarketingps'}
                        </td>
                        <td class="text-center">
                            {if isset($catalog.active) && $catalog.active == 1}
                                <i class="material-icons action-enabled egoi-enabled">
                                    check
                                </i>
                            {else}
                                <i class="material-icons action-disabled egoi-disabled">
                                    clear
                                </i>
                            {/if}
                        </td>
                        <td class="action-type text-center">
                            <a class="btn tooltip-link js-submit-row-action dropdown-item">
                                <i class="material-icons">refresh</i>
                            </a>
                            <a class="btn tooltip-link js-submit-row-action dropdown-item"
                               onclick="confirmDeleteCatalog({$catalog.catalog_id})">
                                <i class="material-icons">delete</i>
                            </a>
                        </td>
                    </tr>
                {/foreach}
            {else}
                <tr>
                    <td class="list-empty" colspan="8">
                        <div class="list-empty-msg">
                            <i class="icon-warning-sign list-empty-icon"></i>
                            {l s='No records found' mod='smartmarketingps'}
                        </div>
                    </td>
                </tr>
            {/if}
            </tbody>
        </table>

        <div class="mt20 align-right">
            <a href="{$smarty.server.REQUEST_URI|replace:'&createCatalog=1':''|regex_replace:"#&deleteCatalog=[0-9]+#":''}&createCatalog=1"
               class="btn btn-default btn-lg uppercase">{l s='Add Catalog' mod='smartmarketingps'}
            </a>
        </div>
    </form>
</div>
