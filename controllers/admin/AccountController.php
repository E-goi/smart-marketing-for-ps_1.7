<?php

include_once dirname(__FILE__).'/../SmartMarketingBaseController.php';

/**
 * @package controllers/admin/AccountController
 */
class AccountController extends SmartMarketingBaseController 
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// instantiate API
		$this->activateApi();

		$this->bootstrap = true;
		$this->cfg = 0;
		
		$this->meta_title = $this->l('My Account').' - '.$this->module->displayName;
		if (!$this->module->active)
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
	}

	/**
     * Inject Dependencies
     * 
     * @return void
     */
	public function setMedia() 
	{
		parent::setMedia();
		$this->addJS($this->_path. '/views/assets/js/account.js');
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
		    'js' => $this->l('$( \'#save-form\' ).click();')
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
			
			if($data = $this->api->getClientData()) {
				$this->assign('clientData', $data);
			} else {
				$this->assign('clientData', false);
			}

			$this->getLists();
			$this->assign('content', $this->fetch('account.tpl'));
		}
	}

	/**
	 * Get all lists from this Account
	 * 
	 * @return void
	 */
	protected function getLists() 
	{
		$msg = '';
		if(!empty($_POST)){
			$msg = $this->postList();
		}
		
		$this->assign('lists', $this->api->getLists() ?: false);

		if($msg) {
			if(is_numeric($msg)){
				$this->assign('success_message', 'ok');
			}else{
				$this->assign('error_message', $msg);
			}
		}
		
		$url_list = '/?action=lista_definicoes_principal&list=';
		$this->assign('url_list', $url_list);
	}
	
	/**
	 * Create list
	 * 
	 * @return mixed
	 */
	protected function postList() 
	{
		if (isset($_POST['add-list']) && ($_POST['add-list'])) {
			$name = trim($_POST['egoi_ps_title']);
			$lang = $_POST['egoi_ps_lang'];

			$result = $this->api->createList($name, $lang);
			if($result) {
				return $result;
			}
		}
		return false;
	}
	
}
