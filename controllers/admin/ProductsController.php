<?php
/**
 * Smart Marketing
 *
 * @author E-goi
 * @copyright 2019 E-goi
 * @license LICENSE.txt
 * @package controllers/admin/AccountController
 */

include_once dirname(__FILE__) . '/../SmartMarketingBaseController.php';

class ProductsController extends SmartMarketingBaseController
{

    protected $apiv3;

    public function __construct()
    {
        parent::__construct();

        $this->apiv3 = new ApiV3();
        $this->bootstrap = true;
        $this->cfg = 0;

        $this->meta_title = $this->l('Products') . ' - ' . $this->module->displayName;
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }

        if (!empty($_POST)) {
            $this->sanitize();
        }
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        $this->page_header_toolbar_btn['goto-egoi'] = array(
            'short' => $this->l('Go to E-goi'),
            'icon' => 'icon-external-link',
            'href' => 'https://login.egoiapp.com',
            'desc' => $this->l('Go to E-goi'),
            'js' => $this->l('$( \'#save-form\' ).click();')
        );
    }

    public function initContent()
    {
        parent::initContent();

        if ($this->isValid()) {
            if (!empty($_GET['createCatalog'])) {
                $this->createCatalog();
            } elseif (!empty($_GET['deleteCatalog'])) {
                $this->deleteCatalog($_GET['deleteCatalog']);
            } else {
                $this->syncProducts();
            }
        }
    }

    private function createCatalog()
    {
        if (!empty($_POST)) {
            $catalogSync = 0;
            if ($_POST['catalogSync']) {
                $catalogSync = 1;
            }

            $data = array(
                'title' => $_POST['egoi-catalog-name'],
                'language' => $_POST['egoi-catalog-language'],
                'currency' => $_POST['egoi-catalog-currency']
            );

            $result = $this->apiv3->createCatalog($data);
            if (!is_int($result)) {
                Db::getInstance()->insert(
                    'egoi_active_catalogs',
                    array(
                        'catalog_id' => $result['catalog_id'],
                        'active' => $catalogSync
                    )
                );

                $link = $this->context->link->getAdminLink('Products');
                Context::getContext()->cookie->notificacion = 'catalog_created';
                Tools::redirectAdmin($link);
            } else {
                $this->errors[] = $this->l('Error creating catalog');
            }

            return;
        }

        $languages = array();
        foreach (Language::getLanguages(true, $this->context->shop->id) as $language) {
            $languages[] = strtoupper($language['iso_code']);
        }
        $defaultLanguage = $this->context->language->iso_code;
        $this->assign('languages', $languages);
        $this->assign('defaultLanguage', $defaultLanguage);

        $currencies = array();
        foreach (Currency::getCurrencies(true) as $currency) {
            $currencies[] = $currency->iso_code;
        }
        $defaultCurrency = $this->context->currency->iso_code;
        $this->assign('currencies', $currencies);
        $this->assign('defaultCurrency', $defaultCurrency);

        $this->assign('content', $this->fetch('create-catalog.tpl'));
    }

    private function deleteCatalog($id)
    {
        if (is_int($_GET['deleteCatalog'])) {
            $link = $this->context->link->getAdminLink('Products');
            Context::getContext()->cookie->notificacion = 'invalid_catalog';
            Tools::redirectAdmin($link);
            return;
        }

        $result = $this->apiv3->deleteCatalog($id);
        if ($result === 204) {
            Db::getInstance()->delete('egoi_active_catalogs', 'catalog_id = '.(int)$id);
            Context::getContext()->cookie->notificacion = 'catalog_delete_success';
        } else {
            Context::getContext()->cookie->notificacion = 'catalog_delete_error';
        }

        $link = $this->context->link->getAdminLink('Products');
        Tools::redirectAdmin($link);
    }

    private function syncProducts()
    {
        $this->checkNotifications();

        $catalogs = $this->apiv3->getCatalogs();
        if (!is_int($catalogs)) {
            $catalogs = $catalogs['items'];
            $catalogEnabled = Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "egoi_active_catalogs ORDER BY catalog_id DESC");
            $catalogCount = count($catalogEnabled);

            for ($i = 0; $i < $catalogCount; $i++) {
                $catalogs[$i]['active'] = 0;
                if ($catalogs[$i]['catalog_id'] == $catalogEnabled[$i]['catalog_id']) {
                    if (isset($catalogEnabled[$i])) {
                        $catalogs[$i]['active'] = $catalogEnabled[$i]['active'];
                    } else {
                        Db::getInstance()->insert(
                            'egoi_active_catalogs',
                            array(
                                'catalog_id' => $catalogs[$i]['catalog_id'],
                                'active' => 0
                            )
                        );
                        $catalogs[$i]['active'] = 0;
                    }
                }
            }
        } else {
            $this->errors[] = $this->l('Error loading catalogs');
        }

        $this->assign('catalogs', $catalogs);
        $this->assign('content', $this->fetch('sync-products.tpl'));
    }

    private function checkNotifications()
    {
        switch (Context::getContext()->cookie->notificacion) {
            case 'catalog_created':
                $this->assign('success_msg', $this->displaySuccess($this->l('Catalog created')));
                break;
            case 'invalid_catalog':
                $this->errors[] = $this->l('Invalid catalog');
                break;
            case 'catalog_delete_error':
                $this->errors[] = $this->l('Error deleting catalog');
                break;
            case 'catalog_delete_success':
                $this->assign('success_msg', $this->displaySuccess($this->l('Catalog deleted')));
                break;
            default:
        }

        Context::getContext()->cookie->notificacion = '';
    }
}