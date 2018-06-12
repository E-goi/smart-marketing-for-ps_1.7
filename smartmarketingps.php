<?php

if (!defined('_PS_VERSION_'))
  	exit;

/**
 * Package SmartMarketing
 */
class SmartMarketingPs extends Module
{
	
	/**
	 * @var mixed
	 */
	protected $success_msg;

	/**
	 * @var mixed
	 */
	protected $error_msg;

	/**
	* Module Constructor
	*/
	public function __construct()
	{
		$this->name = 'smartmarketingps';
	    $this->tab = 'advertising_marketing';
	    $this->version = '1.0.0';
	    $this->author = 'E-goi';
	    $this->need_instance = 1;
	    $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
	    $this->bootstrap = true;

	    parent::__construct();

	    // Name & Description
	    $this->displayName = $this->l('Smart Marketing for Prestashop');
	    $this->description = $this->l('E-goi Syncronization for Lists and Subscribers');

	   	// on uninstall
	    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

	    // media path
	    $this->jspath = 'views/js/';
        $this->csspath = 'views/css/';

	    if (!Configuration::get('SMART_MARKETING')) {
	      	$this->warning = $this->l('No name provided');
	    }

	    $this->registerHook('displayHeader');
	}

	/**
	 * Install App
	 * 
	 * @return bool
	 */
	public function install()
	{
	  	if (!parent::install() || !$this->installDb())
	    	return false;
	  	return true;
	}

	/**
	 * Install required Tables
	 * 
	 * @return bool
	 */
	protected function installDb() 
	{
		$return = true;
		$sql = array();
		include __DIR__ . '/install/sql.php';
		
		foreach ($sql as $s){
		    $return &= Db::getInstance()->execute($s);
		}
		return $return;
    }

    /**
     * Uninstall required tables
     * 
     * @return type
     */
    protected function uninstallDb() 
	{
		
    }

	/**
	 * Uninstall App
	 * 
	 * @return bool
	 */
	public function uninstall()
	{
	  	if (!parent::uninstall() || !Configuration::deleteByName('SMART_MARKETING') || !$this->uninstallDb())
	    	return false;
	  	return true;
	}

	/**
    * Enable module.
    *
    * @return $force_all
    * @param bool
    */
    public function enable($force_all = false) 
    {
        return parent::enable($force_all);
    }

    /**
    * Disable module.
    *
    * @return $force_all
    * @param bool
    */
    public function disable($force_all = false) 
    {
        return parent::disable($force_all);
    }

    /**
     * Dependencies
     * 
     * @return void
     */
    public function hookDisplayHeader() 
	{
		$this->context->controller->addCSS(($this->_path.$this->csspath). 'main.css');
	}

	/**
	 * Filter Request data from Configuration page
	 * 
	 * @return string
	 */
	public function getContent()
	{	 
	    if (Tools::isSubmit('submit_egoi')) {

	    	$api_key = Tools::getValue('smart_api_key');
	    	
	    	if (!$api_key)
                $this->error_msg = $this->displayError($this->l('Indicate correct API key.'));
	        
	        if (!sizeof($this->_errors)) {
                Configuration::updateValue('smart_api_key', ($api_key));
                $this->success_msg = $this->displayConfirmation('API Key saved and updated');
            }

	        /*if (!$my_module_name || empty($my_module_name) || !Validate::isGenericName($my_module_name)) {
	            $output .= $this->displayError($this->l('Invalid Configuration value'));
	        } else{
	            Configuration::updateValue('SMART_MARKETING', $my_module_name);
	            $output .= $this->displayConfirmation($this->l('Settings updated'));
	        }*/
	    }
	    return $this->displayForm();
	}

	/**
	 * Show Configuration Form
	 * 
	 * @return type
	 */
	public function displayForm() 
	{
	    $h = new HelperForm();

	    // Module, token and currentIndex
	    $h->module = $this;
	    $h->name_controller = $this->name;
	    $h->token = Tools::getAdminTokenLite('AdminModules');
	    $h->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

	    // Language
	    $h->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');

	    // Title and toolbar
	    $h->title = 'E-goi Configuration';
	    $h->show_toolbar = true;
	    $h->toolbar_scroll = true;
	    $h->submit_action = 'submit'.$this->name;
	    $h->toolbar_btn = array(
			'save' => array(
			    'desc' => $this->l('Save'),
			    'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
			    '&token='.Tools::getAdminTokenLite('AdminModules'),
			),
			'back' => array(
			    'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
			    'desc' => $this->l('Back to list')
			)
	    );

        $this->smarty->assign('success_msg', $this->success_msg);
        $this->smarty->assign('error_msg', $this->error_msg);
        $this->smarty->assign('smart_api_key_error', Configuration::get('smart_api_key') ? false : true);

	    // Load current value
	    $h->fields_value['smart_api_key'] = Configuration::get('smart_api_key');

	    return $this->display($this->name, 'views/templates/admin/config.tpl');
	}

	/**
	 * Create menus
	 * 
	 * @return type
	 */
	private function createMenu() 
	{
		$mainTab = Tab::getInstanceFromClassName('EgoiforPs');

		$subtabs = array(
			'Account' => array('1'=> $this->l('My Account')),
			'Lists' => array('1' => $this->l('My Lists')),
           	'Subscribers' => array('1' => $this->l('My Subscribers')),
			'Forms' => array('1' => $this->l('My Forms'))
		);

		$res = true;

		if(!Validate::isLoadedObject($mainTab)) {
			$mainTab->active = 1;
			$mainTab->class_name = 'EgoiforPs';
			$mainTab->id_parent = 0;
			$mainTab->module = $this->name;
			$mainTab->name = $this->createMultiLangField('Smart Marketing');
			$res &= $mainTab->save();
			$mainTab = Tab::getInstanceFromClassName('EgoiforPs');
		}

		if($subtabs)
    		foreach($subtabs as $className => $menuName) {
    			$res &= $this->createSubmenu($mainTab->id, $menuName, $className);
    		}

		return $res;
	}

	/**
	 * Create submenu
	 * 
	 * @param type $parentId 
	 * @param type $menuName 
	 * @param type $className 
	 * @return type
	 */
	private function createSubmenu($parentId, $menuName, $className) 
	{
		$subTab = Tab::getInstanceFromClassName($className);
		if(!Validate::isLoadedObject($subTab)) {

			$subTab->active = 1;
			$subTab->class_name = $className;
			$subTab->id_parent = $parentId;
			$subTab->module = $this->name;
			$subTab->name = $this->createMultiLangFieldHard($menuName);
			return $subTab->save();

		}else if($subTab->id_parent != $parentId) {

			$subTab->id_parent = $parentId;
			return $subTab->save();
		}
		return true;
	}

	/**
	 * Delete menu
	 * 
	 * @return bool
	 */
	private function deleteMenu() 
	{
		Tab::getInstanceFromClassName('EgoiforPs');
		$subtabs = array(
			'Account',
			'Lists',
            'Subscribers',
            'Forms'
		);
		$res = true;

		if($subtabs){
			foreach($subtabs as $className) {
				$res &= $this->deleteSubmenu($className);
			}
        }

        return true;
	}

	/**
	 * Delete submenu
	 * 
	 * @param string $className 
	 * @return mixed
	 */
	private function deleteSubmenu($className) 
	{
		$subTab = Tab::getInstanceFromClassName($className);
		return $subTab->delete();
	}


}