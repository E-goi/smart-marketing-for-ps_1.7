<?php

include_once dirname(__FILE__).'/../SmartMarketingBaseController.php';

class WebPushController extends SmartMarketingBaseController
{

    /**
     * @var ApiV3 $apiv3
     */
    protected $apiv3;

    /**
     * WebPushController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->apiv3 = new ApiV3();

        $this->bootstrap = true;
        $this->cfg = 0;

        $this->meta_title = $this->l('WebPush') . ' - ' . $this->module->displayName;
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }

        if (!empty($_POST)) {
            $this->sanitize();
        }
    }

    /**
     * Toolbar settings
     *
     * @return void
     */
    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        $this->page_header_toolbar_btn['goto-egoi'] = array(
            'short' => $this->l('Go to E-goi'),
            'icon' => 'icon-external-link',
            'href' => 'https://login.egoiapp.com',
            'desc' => $this->l('Go to E-goi'),
            'js' => '$( \'#save-form\' ).click();'
        );
    }

    /**
     * Initiate content
     *
     * @return void
     */
    public function initContent()
    {
        parent::initContent();

        if ($this->isValid()) {
            $this->getConfiguration();
            $this->assign('content', $this->fetch('web-push.tpl'));
        }
    }

    /**
     * Setups configuration to send sms notifications
     *
     * @return void
     */
    private function getConfiguration()
    {
        $data = Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->getRow('SELECT * FROM '._DB_PREFIX_.'egoi where client_id!="" order by egoi_id DESC');

        if ($_POST) {
            $this->configureWebPush($data['list_id']);
        }

        $this->webPushRead($data['list_id']);
    }

    private function webPushRead($listId)
    {
        $webPushSites = array_reverse($this->apiv3->getWebPushSites($listId)['items']);

        $webPush = null;
        $siteIds = array();
        $webPushNames = array();
        foreach ($webPushSites as $site) {
            if ($site['site_id'] == Configuration::get(SmartMarketingPs::CONFIGURED_WEB_PUSH)) {
                $webPush = $site;
            }

            array_push($siteIds, $site['site_id']);
            array_push($webPushNames, $site['name']);
        }

        $this->assign('webPush', $webPush);
        $this->assign('siteIds', $siteIds);
        $this->assign('webPushNames', $webPushNames);
        $this->assign('defaultWebPush', $siteIds[0]);
    }

    private function configureWebPush($listId)
    {
        $webPush = false;
        if (!empty($_POST['select-wp'])) {
            $webPush = $this->selectWebPush($listId);
        } elseif (!empty($_POST['create-wp'])) {
            $webPush = $this->createWebPush($listId);
            if ($webPush === 'name_already_exists') {
                $this->assign('error_msg', $this->displayError($this->l('The provided name already exists in your E-goi account!')));
                return;
            }
        }

        if (is_array($webPush)) {
            Configuration::updateValue(SmartMarketingPs::CONFIGURED_WEB_PUSH, $webPush['site_id']);
            Configuration::updateValue(SmartMarketingPs::WEB_PUSH_APP_CODE, $webPush['app_code']);
        }

        if(!empty($_POST['delete-wp-config'])) {
            Configuration::updateValue(SmartMarketingPs::CONFIGURED_WEB_PUSH, null);
            Configuration::updateValue(SmartMarketingPs::WEB_PUSH_APP_CODE, null);
        }

        $this->assign('success_msg', $this->displaySuccess($this->l('Configuration Saved')));
    }

    private function selectWebPush($listId)
    {
        $data = $this->apiv3->getWebPushSites($listId);

        foreach ($data['items'] as $webPush){
            if ($webPush['site_id'] == $_POST['egoi-web-push-sites']) {
                return $webPush;
            }
        }

        return false;
    }

    private function createWebPush($listId)
    {
        $data = array(
            'site' => Context::getContext()->shop->getBaseURL(true),
            'list_id' => $listId,
            'name' => $_POST['wp-create-site-name']
        );

        $webPush = $this->apiv3->createWebPushSite($data);

        if (!empty($webPush['errors'])) {
            if (!empty($webPush['errors']['name_already_exists'])) {
                return 'name_already_exists';
            }

            $webPush = false;
        }

        return $webPush;
    }
}
