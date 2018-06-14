<?php

include_once dirname(__FILE__).'/../SmartMarketingBaseController.php';

/**
 * @package controllers/admin/FormsController
 */
class FormsController extends SmartMarketingBaseController 
{

	/**
	 * @var int
	 */
	protected $formId;

	/**
	 * Form default options
	 * 
	 * @var array
	 */
	protected $formOptions = array(
		'enable' => '', 
		'form_title' => '', 
		'form_content' => '', 
		'boot' => '', 
		'msg_gen' => '', 
		'msg_invalid' => '',
		'msg_exists' => '', 
		'success' => '', 
		'redirect' => '', 
		'style_width' => '', 
		'style_height' => '', 
		'block_header' => '', 
		'block_footer' => '', 
		'block_home' => '', 
		'popup' => '', 
		'once' => '', 
		'url' => '', 
		'hide' => '0',
		'estado' => 1
	);

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
		$this->meta_title = $this->l('E-goi Forms').' - '.$this->module->displayName;

		$this->formId = Tools::getIsset('form') ? (int)Tools::getValue('form') : false;

		// delete form if is defined
		$this->deleteForm();

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
		$this->addJS($this->_path. '/views/assets/js/forms.js');
		return parent::setMedia();
    }

    /**
	 * Toolbar settings
	 * 
	 * @return void
	 */
	public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        if ($this->formId) {
        	$this->page_header_toolbar_btn['save-and-stay'] = array(
			    'short' => $this->l('Save form'),
			    'href' => '#',
			    'desc' => $this->l('Save form'),
			    'js' => $this->l('$( \'#save-form\' ).click();')
			);
        }else{
        	 $this->page_header_toolbar_btn['new-form'] = array(
			    'short' => $this->l('Add new Form'),
			    'icon' => 'process-icon-new',
			    'href' => '#',
			    'desc' => $this->l('Add new Form'),
			    'js' => $this->l('$( \'#add-form\' ).click();')
			);
        }
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

			if(!empty($_POST)) {
				$this->saveForm();
			}		

			$forms = Db::getInstance(_PS_USE_SQL_SLAVE_)
						->executeS('SELECT * FROM '._DB_PREFIX_.'egoi_forms');
			if (!empty($forms)) {
				$this->assign('allforms', $forms);
				$this->assign('totalforms', count($forms));
			}
			
			$this->assign('form', $this->formId);
			$this->assign('token', $_GET['token']);
		}

		return $this->displayForm();
	}

	/**
	 * Display form content if specified
	 * 
	 * @return void 
	 */
	protected function displayForm() 
	{
		if($this->formId) {
			$res = Db::getInstance(_PS_USE_SQL_SLAVE_)
						->getRow("SELECT * FROM "._DB_PREFIX_."egoi_forms WHERE form_id='".(int)$this->formId."'");
			
			$form = !empty($res) ? $res : $this->formOptions;
			foreach ($form as $key => $val) {
				$this->assign($key, $val);
			}

			// get forms from E-goi
			$this->assign('form_data', $form['url']);
			if (isset($form['list_id']) && ($form['list_id'])) {
				$this->assign('myforms', $this->api->getForms($form['list_id']));
			}
			$this->assign('type', Tools::getIsset('type') ? Tools::getValue('type') : '');
			$this->assign('lists', $this->api->getLists());
		}

		$this->assign('content', $this->fetch('forms.tpl'));
	}

	/**
	 * Add/Update E-goi form
	 * 
	 * @return bool
	 */
	protected function saveForm() 
	{
		if(isset($_POST['save-form']) && ($_POST['save-form'])) {
			
			$post = $_POST;
			unset($post['save-form']);

			$client_data = $this->api->getClientData();
			$client = $client_data['CLIENTE_ID'];
			
			$res = Db::getInstance(_PS_USE_SQL_SLAVE_)
						->getRow('SELECT * FROM '._DB_PREFIX_.'egoi_forms WHERE form_id='.(int)$post['form_id']);

			foreach ($post as $key => $value) {
				$this->formOptions[$key] = is_numeric($value) ? (int)$value : pSQL($value);
			}

			if($res['form_id']) {
				$this->assign('success_message', $this->displaySuccess($this->l('Form settings updated')));
				return Db::getInstance()->update('egoi_forms', $this->formOptions, "form_id = ".(int)$form_id);
			}else{
				$this->assign('success_message', $this->displaySuccess($this->l('Form settings saved')));
				return Db::getInstance()->insert('egoi_forms', $this->formOptions);
			}

			return false;
		}
	}

	/**
	 * Delete form
	 * 
	 * @return bool
	 */
	protected function deleteForm()
	{
		if(isset($_POST['del']) && ($_POST['del']) && ($this->formId)) {
			if (base64_decode($_POST['del']) == $this->formId) {
				return Db::getInstance()->delete('egoi_forms', 'form_id='.(int)$this->formId);
			}
		}
	}

}