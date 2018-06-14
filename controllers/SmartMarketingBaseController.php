<?php

/**
 * @package controllers/SmartMarketingBaseController
 */
abstract class SmartMarketingBaseController extends ModuleAdminController 
{
	
	/**
	 * @var string
	 */
	protected $module_name = "smartmarketingps";

	/**
	 * @var string
	 */
	protected $admin_path = _PS_ADMIN_DIR_;

	/**
	 * @var string
	 */
	protected $has_api_key;

	/**
	 * @var object
	 */
	protected $api;

	/**
	 * By default check if is a configuration page
	 * 
	 * @var integer
	 */
	protected $cfg = 1;
	
	/**
	 * Constructor
	 */
	public function __construct() 
	{
		parent::__construct();
		
		$this->_path = _PS_MODULE_DIR_ . $this->module_name;
		$this->has_api_key = Configuration::get('smart_api_key') ? true : false;
		$this->assign('has_api_key', $this->has_api_key);
		$this->assign('error_message', '');
		$this->assign('has_error', false);

		// on failture redirect to configuration page
        $this->assign('redirect', $this->redirectToConfig());
	}

	/**
     * Inject Dependencies
     * 
     * @return void
     */
	public function setMedia() 
	{
		$this->addJquery();
		$this->addCSS($this->_path. '/views/assets/css/main.css');
		$this->addJS($this->_path. '/views/assets/js/config.js');
		return parent::setMedia();
    }

	/**
	 * Activate E-goi API
	 * 
	 * @return void
	 */
	protected function activateApi()
	{
		$this->api = new SmartApi;
	}

	/**
	 * Validate errors from base content
	 * 
	 * @return bool
	 */
	protected function isValid()
	{
		$this->assign('config', $this->cfg);

		// check if not have account configured
		if (!$this->has_api_key) {
			$this->assign('smart_api_key_error', true);
			$this->assign('content', $this->fetch('errors.tpl'));
			return false;
		}

		return true;
	}
	
	/**
	 * Fetch a view
	 * 
	 * @param  string $template
	 * @return mixed  
	 */
	protected function fetch($template) 
	{
		return $this->context->smarty->fetch(
			$this->module->getTemplatePath('views/templates/admin/'.$template)
		);
	}
	
	/**
	 * Assign a variable to a view
	 * 
	 * @param  string $key
	 * @param  string $value
	 * @return object
	 */
	protected function assign($key, $value) 
	{
		return $this->context->smarty->assign($key, $value);
	}

	/**
	 * Display a closable Success
	 * 
	 * @param  string $message
	 * @return void
	 */
	protected function displaySuccess($message)
	{
		$this->confirmations = array($message);
	}

	/**
	 * Display a closable Warning
	 * 
	 * @param  string $message
	 * @return void        
	 */
	protected function displayWarning($message)
	{
		return parent::displayWarning($message);
	}

	/**
	 * Get custom Controller Route
	 * 
	 * @param  string $controller
	 * @param  string $withAdmin
	 * @return string
	 */
	protected function getControllerRoute($controller, $withAdmin = false)
	{
		if ($withAdmin) {
			$path = explode('/', $this->admin_path);
		 	$adminPath = array_pop($path);
		 	return $adminPath.'/index.php?controller='.$controller.'&token='.Tools::getAdminTokenLite($controller);
		}

		return 'index.php?controller='.$controller.'&token='.Tools::getAdminTokenLite($controller);
	}

	/**
	 * Redirect to custom Controller
	 * 
	 * @param url
	 * @return mixed
	 */
	protected function redirectTo($url)
    {
        return Tools::redirectAdmin($url);
    }

	/**
	 * Redirect to Configuration Page on Failure
	 * 
	 * @return string
	 */
	private function redirectToConfig() 
	{
        $url = 'index.php?controller=AdminModules';
        $url .= '&token='.Tools::getAdminTokenLite('AdminModules');
        $url .= '&configure=' . $this->module_name . '&tab_module=advertising_marketing&module_name=' . $this->module_name;
        return $url;
    }
	
}
