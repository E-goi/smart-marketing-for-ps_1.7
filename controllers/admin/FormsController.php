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
		'is_bootstrap' => '', 
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

		if (!$this->module->active)
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));

		$this->checkSelectedList();
	}

	/**
     * Inject Dependencies
     * 
     * @return void
     */
	public function setMedia() 
	{
		parent::setMedia();
		$this->addJS($this->_path. '/views/assets/js/forms.js');
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
			/*$this->page_header_toolbar_btn['form-preview'] = array(
			    'short' => $this->l('Preview form'),
			    'icon' => 'icon-desktop',
			    'href' => '#',
			    'desc' => $this->l('Preview form'),
			    'js' => $this->l('$( \'#preview-form\' ).click();')
			);*/
        }else{
        	 $this->page_header_toolbar_btn['new-form'] = array(
			    'short' => $this->l('Add new Form'),
			    'icon' => 'process-icon-new',
			    'href' => '#',
			    'desc' => $this->l('Add new Form'),
			    'js' => $this->l('$( \'#add-form\' ).trigger("click");')
			);
        }
    }
	
    /**
     * Initiate content
     * 
     * @return mixed
     */
	public function initContent()
	{
		parent::initContent();

		if ($this->isValid()) {

			if(!empty($_POST)) {
                $this->saveForm();
			}

			// delete form if is defined
			$this->deleteForm();

			$forms = Db::getInstance(_PS_USE_SQL_SLAVE_)
						->executeS('SELECT * FROM '._DB_PREFIX_.'egoi_forms');
			if (!empty($forms)) {
				$this->assign('allforms', $forms);
				$this->assign('totalforms', count($forms));
			}
			
			$this->assign('form', $this->formId);
			$this->assign('token', $_GET['token']);

			return $this->displayForm();
		}
	}

	/**
	 * Display form content if specified
	 * 
	 * @return mixed
	 */
	protected function displayForm() 
	{
		if($this->formId) {
			$res = Db::getInstance(_PS_USE_SQL_SLAVE_)
						->getRow("SELECT * FROM "._DB_PREFIX_."egoi_forms WHERE form_id='".(int)$this->formId."'");
			
			$form = !empty($res) ? $res : $this->formOptions;
			foreach ($form as $key => $val) {
                if ($key == 'form_content') {
                    $val = base64_decode($val);
                }
                $this->assign($key, $val);
			}

			// get forms from E-goi
			$this->assign('form_data', $form['url']);
			if (isset($form['list_id']) && ($form['list_id'])) {

                $forms = $this->api->getForms($form['list_id']);
                if (!empty($forms)) {
				    $this->assign('myforms', $forms);
                }
			}
			$this->assign('type', Tools::getIsset('type') ? Tools::getValue('type') : '');
			$this->assign('lists', $this->api->getLists());
		}

		return $this->assign('content', $this->fetch('forms.tpl'));
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
			
			$res = Db::getInstance(_PS_USE_SQL_SLAVE_)
						->getRow('SELECT * FROM '._DB_PREFIX_.'egoi_forms WHERE form_id='.(int)$this->formId);

			foreach ($post as $key => $value) {
				if ($key == 'form_content') {
					$this->formOptions[$key] = pSQL(
					    base64_encode(
					        htmlentities(Tools::getValue('form_content')
                            )
                        )
                    );
				}else{
					$this->formOptions[$key] = is_numeric($value) ? (int)$value : pSQL($value);
				}
			}

			if($res['form_id']) {
                $result = Db::getInstance()->update('egoi_forms', $this->formOptions, "form_id = ".(int)$this->formId);
                if ($result) {
                    $this->assign('success_msg', $this->displaySuccess($this->l('Form settings updated')));
                }

			}else{
				$result = Db::getInstance()->insert('egoi_forms', $this->formOptions);
				if ($result) {
                    $this->assign('success_msg', $this->displaySuccess($this->l('Form settings saved')));
                }
			}

            if (!$result) {
                $this->assign('error_msg', $this->displayWarning($this->l('Form settings error')));
            }
		}
		return false;
	}

	/**
	 * Delete form
	 * 
	 * @return bool
	 */
	protected function deleteForm()
	{
		if(isset($_GET['del']) && ($_GET['del']) && ($this->formId)) {
			if (base64_decode($_GET['del']) == $this->formId) {
				
				$res = Db::getInstance()->delete('egoi_forms', 'form_id='.(int)$this->formId);
				if ($res) {
					$this->assign('success_message', $this->displaySuccess($this->l('Form deleted')));
					return $this->redirectTo($this->getControllerRoute('Forms'));
				}

				$this->assign('error_message', $this->displayError($this->l('Error')));
				return false;
			}
		}
	}

    /**
     * @return bool
     */
	protected function checkSelectedList()
    {
        if (!empty($_POST) && ($_POST['_get_forms'])) {
            $forms = $this->api->getForms($_POST['list_id']);
            if (!empty($forms)) {

                if (isset($forms['ERROR']) && ($forms['ERROR'])) {
                    echo json_encode($this->l('This list dont have any forms'));
                    exit;
                }

                echo json_encode($forms);
                exit;
            }
            echo $this->l('Error: The list chosen has an error');
            exit;
        }
        return false;
    }

}