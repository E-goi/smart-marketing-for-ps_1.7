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
	 * Custom Override file
	 * 
	 * @var string
	 */
	protected $custom_override = '/../../override/classes/WebserviceSpecificManagementEgoi.php';

	/*public $tabs = array(
        array(
            'name' => 'Smart Marketing',
            'class_name' => 'SmartMarketingPs',
            'parent_class_name' => 'AdminDashboard',
            'visible' => true,
        )
    );*/

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
	    $this->jspath = 'views/assets/js/';
        $this->csspath = 'views/assets/css/';

        spl_autoload_register(array($this, 'autoloadApi'));

	    if (!Configuration::get('SMART_MARKETING')) {
	      	$this->warning = $this->l('No name provided');
	    }

	    $this->validateApiKey();
	}

	public function autoloadApi() 
	{
        $classFile = __DIR__ . '/lib/SmartApi.php';
        if(is_file($classFile)){
            include_once $classFile;
        }
    }

	/**
	 * Install App
	 * 
	 * @return bool
	 */
	public function install()
	{
	  	if (!parent::install() || !$this->installDb() || !$this->createMenu()
	  		|| !$this->registerHook('actionObjectCustomerAddAfter')
	  		|| !$this->registerHook('actionValidateOrder')
	  		|| !$this->registerHook('displayTop')
	  		|| !$this->registerHook('displayFooter')
	  		|| !$this->registerHook('displayHeader')
	  		|| !$this->registerHook('header'))
	    	return false;

	    // register WebService
		//$this->registerWebService();
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
		// drop all tables from the plugin
		include __DIR__ . '/install/sql.php';
    	foreach ($sql as $name => $v){
       		Db::getInstance()->execute('DROP TABLE IF EXISTS '.$name);
   		}

   		// remove menus
   		Db::getInstance()->delete('tab', "module = '$this->name'");
   		Db::getInstance()->delete('tab_lang', "name = 'Smart Marketing' or name='Account'");
   		Db::getInstance()->delete('authorization_role', "slug like '%SMARTMARKETINGPS%'");

   		// remove API Key in cache
   		Configuration::deleteByName('smart_api_key');

   		// remove custom override file
   		@unlink(dirname(__FILE__).$this->custom_override);
    	return true;
    }

	/**
	 * Uninstall App
	 * 
	 * @return bool
	 */
	public function uninstall()
	{
	  	if (!parent::uninstall() || !$this->uninstallDb() || !$this->deleteMenu())
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
    public function hookDisplayHeader($params) 
	{
		$this->context->controller->addCSS(($this->_path.$this->csspath). 'main.css');
		$this->context->controller->addJS(($this->_path.$this->jspath). 'config.js');
	}

	public function hookHeader() 
	{
		$this->context->controller->addCSS(($this->_path.$this->csspath). 'main.css');
		$this->context->controller->addJS(($this->_path.$this->jspath). 'config.js');
	}

	/**
	 * Register WebService Overrides
	 * 
	 * @return bool
	 */
	public function registerWebService() 
	{
		$sql = array(
			'key' => md5(time()),
			'class_name' => "WebserviceRequest",
			'description' => "E-goi",
			'active' => 1
		);
		Db::getInstance()->insert('webservice_account', $sql);

		$sql = 'SELECT id_webservice_account FROM '._DB_PREFIX_.'webservice_account WHERE description="E-goi"';
		$row = Db::getInstance()->executeS($sql);

		if(!empty($row)) {
			$id_webservice = $row[0]['id_webservice_account'];

			// add webservice relation
			Db::getInstance()->insert('webservice_account_shop', 
				array(
					'id_webservice_account' => $id_webservice,
					'id_shop' => 1,
				)
			);

			// assign webservice permissions
			Db::getInstance()->insert('webservice_permission', 
				array(
					'id_webservice_account' => $id_webservice,
					'resource' => 'egoi',
					'method' => "GET",
				)
			);

			// install custom overrides
			$this->installSmartOverrides();
			return true;
		}

		return false;
	}

	/**
	 * Uninstall WebService Overrides
	 * 
	 * @return bool
	 */
	public function uninstallWebService() 
	{
		$sql = 'SELECT id_webservice_account FROM '._DB_PREFIX_.'webservice_account WHERE description="E-goi"';
		$row = Db::getInstance()->executeS($sql);

		if(!empty($row)) {
			$id_webservice = $row[0]['id_webservice_account'];
			$sql = 'DELETE FROM '._DB_PREFIX_.'webservice_account WHERE id_webservice_account="'.$id_webservice.'"';
			Db::getInstance()->executeS($sql);

			// remove webservice from Shop
			$sql = 'DELETE FROM '._DB_PREFIX_.'webservice_account_shop WHERE id_webservice_account="'.$id_webservice.'"';
			Db::getInstance()->executeS($sql);

			// remove webservice permissions
			$sql = 'DELETE FROM '._DB_PREFIX_.'webservice_permission WHERE id_webservice_account="'.$id_webservice.'"';
			Db::getInstance()->executeS($sql);

			return true;
		}

		return false;
	}

	/**
	 * Filter Request data from Configuration page
	 * 
	 * @return string
	 */
	public function getContent()
	{
	    if (Tools::isSubmit('submit_api_key')) {

	    	$api_key = Tools::getValue('smart_api_key');
	    	
	    	if (!$api_key)
                $this->error_msg = $this->displayError($this->l('Indicate correct API key.'));
	        
	        if (!sizeof($this->_errors)) {
                Configuration::updateValue('smart_api_key', ($api_key));
                $this->success_msg = $this->displayConfirmation($this->l('API Key saved and updated'));
            }
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
        $this->smarty->assign('success_msg', $this->success_msg);
        $this->smarty->assign('error_msg', $this->error_msg);
        $this->smarty->assign('smart_api_key_error', Configuration::get('smart_api_key') ? false : true);

	    return $this->display($this->name, 'views/templates/admin/config.tpl');
	}

	/**
	 * Validate Api Key
	 * 
	 * @return void
	 */
	public function validateApiKey()
	{
		if(isset($_POST["api_key"]) && ($_POST["api_key"])) {

	        $api = new SmartApi($_POST["api_key"]);
	        $clientData = $api->getClientData();

	        if(isset($clientData['CLIENTE_ID']) && ($clientData['CLIENTE_ID'])) {
	        	echo json_encode($clientData);
	        	exit;
	        }else{
	        	header('HTTP/1.1 403 Forbidden');
				exit;
	        }
		}
	}

	/**
	 * Create menu
	 * 
	 * @return type
	 */
	private function createMenu()
	{
		$subtabs = array(
			'Account' => array('1' => $this->l('Account'))
		);

		$mainTab = Tab::getInstanceFromClassName('SmartMarketingPs');
		$mainTab->active = 1;
		$mainTab->class_name = 'SmartMarketingPs';
		$mainTab->id_parent = 0;
		$mainTab->module = $this->name;
		$mainTab->name = $this->createMultiLangField('Smart Marketing');
		$mainTab->save();

		foreach($subtabs as $className => $menuName) {
			$this->createSubmenu($mainTab->id, $menuName[1], $className);
		}

		return true;
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
			$subTab->name = $this->createMultiLangField($menuName);
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
		$subtabs = array(
			'Account'
		);

		if($subtabs){
			foreach($subtabs as $className) {
				$this->deleteSubmenu($className);
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

	/**
     * Process Overrides
     * 
     * @return void
     */
    private function installSmartOverrides()
    {
    	exec('cp '.dirname(__FILE__).'/override/classes/webservice/WebserviceSpecificManagementEgoi.php '.dirname(__FILE__).'/../../override/classes/webservice/');
		exec('rm -f '.dirname(__FILE__).'/../../cache/class_index.php');
    }

    /**
     * Remove overrides
     * 
     * @return void
     */
    private function uninstallSmartOverrides()
    {
    	exec('rm -f '.dirname(__FILE__).'/../../override/classes/webservice/WebserviceSpecificManagementEgoi.php');
    }

    /**
     * Create an array with language key name and respective field
     * 
     * @param $field
     * @return array
     */
    private function createMultiLangField($field) 
    {
    	$res = array();
		$languages = Language::getLanguages();
		foreach ($languages as $lang)
		    $res[$lang['id_lang']] = $field;

		return $res;
   	}


}