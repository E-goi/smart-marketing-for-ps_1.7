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
		
		$this->registerHook('cart');
		$this->registerHook('actionCartSave');

		if(Tools::getIsset('id_cart') && (Tools::getValue('id_order')) && (Tools::getValue('key'))) {
			$this->teOrder();
		}
	}

	/**
	 * Autoload API
	 * 
	 * @return void
	 */
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
	  	if (!parent::install() || !$this->installDb() //|| !$this->createMenu()
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
	  	if (!parent::uninstall() || !$this->uninstallDb())
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
	 * Register WebService Overrides
	 * 
	 * @return bool
	 */
	public function registerWebService() 
	{
		Db::getInstance()->insert('webservice_account', array(
			'key' => md5(time()),
			'class_name' => "WebserviceRequest",
			'description' => "E-goi",
			'active' => 1
		));

		$row = Db::getInstance()
					->getRow('SELECT id_webservice_account FROM '._DB_PREFIX_.'webservice_account WHERE description="E-goi"');

		if(!empty($row)) {
			$id_webservice = $row['id_webservice_account'];

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
		$row = Db::getInstance()
					->getRow('SELECT id_webservice_account FROM '._DB_PREFIX_.'webservice_account WHERE description="E-goi"');

		if(!empty($row)) {
			$qry_webservice = 'id_webservice_account="'.$row['id_webservice_account'].'"';
			Db::getInstance()->delete('webservice_account', $qry_webservice);

			// remove webservice from Shop
			Db::getInstance()->delete('webservice_account_shop', $qry_webservice);

			// remove webservice permissions
			Db::getInstance()->delete('webservice_permission', $qry_webservice);

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
			'Account' => array('1' => $this->l('Account')),
			'Sync' => array('1' => $this->l('Sync Contacts')),
			'Forms' => array('1' => $this->l('Capture Contacts'))
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

	public function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'SmartMarketingPs';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Smart Marketing';
        }
        
        $tab->id_parent = 0;
        $tab->module = $this->name;
        return $tab->add();
    }

	/**
	 * Delete menu
	 * 
	 * @return bool
	 */
	public function uninstallTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('SmartMarketingPs');
        
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        } else {
            return false;
        }
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
   	 * Hook for display content in Top Page
   	 * 
   	 * @param array $params
   	 * @return mixed
   	 */
   	public function hookDisplayTop($params)
   	{
		if(isset($_SESSION['order']) && ($_SESSION['order'])) {
			unset($_SESSION['order']);
		}else{

			// assign t&e vars
			$this->assign(
		      	array(
		          	'te' => $this->te(),
		          	'activate' => 1
		      	)
		  	);
			
			// check if this block is activated 
			if($this->processBlockOptions('header')) {
				return $this->display(__FILE__, 'smartmarketingps.tpl');
			}
		}

		return $this->display(__FILE__, 'ecommerce/te.tpl');
	}

	/**
	 * Hook for display content in Bottom Page
	 * 
	 * @param  array $params
	 * @return mixed        
	 */
	public function hookDisplayFooter($params)
	{
		// check if this block is activated 
		if($this->processBlockOptions('footer')) {
			return $this->display(__FILE__, 'smartmarketingps.tpl');
		}
	}

	/**
	 * Hook for display content in Home Page
	 * 
	 * @param  array $params
	 * @return mixed
	 */
	public function hookDisplayHome($params)
	{
		// check if this block is activated 
		if($this->processBlockOptions('home', 
			array(
	          'popup' => $popup,
	          'once' => $once
	      	))
		) {
			return $this->display(__FILE__, 'smartmarketingps.tpl');
		}
	}

	/**
	 * Track&Engage - Abandoned Cart
	 * 
	 * @return string|bool
	 */
	public function te()
	{
		$res = $this->getTrackingAuthorization();
		if (!empty($res)) {
			$list_id = $res['list_id'];
			$client = $res['client_id'];
			$track = $res['track'];
			
			if($client && $list_id && $track) {

				// set customer email var to use in t&e
				$customer = $this->context->cookie->email;

				$cart = new Cart($this->getCartId($this->getCustomerId()));
				$products = $cart->getProducts();

				$this->removeCart();

				include 'includes/te.php';
				return $te;
			}
		}

		return false;
	}

	/**
	 * Abandoned cart hook
	 * 
	 * @param  array $params
	 * @return void        
	 */
	public function hookActionCartSave($params) 
	{
		$this->addToCart($params);
	}

	/**
	 * Abandoned cart hook
	 * 
	 * @param  array $params
	 * @return void        
	 */
	public function hookCart($params) 
	{
		$this->addToCart($params);
	}

	/**
	 * Add to Cart
	 * 
	 * @param array $params
	 */
	protected function addToCart($params) 
	{
	 	$json = is_array($params['json']) ? json_decode($params['json']['id']) : $params['cookie']->id_cart;

	 	$res = $this->getTrackingAuthorization();

		if (!empty($res)) {
			$list_id = $sql['list_id'];
			$track = $sql['track'];
			$client = $sql['client_id'];
			if($client && $track && $list_id){

				// check if customer has products in the cart (has cart ID?)
				if(isset($json) && ($json)) {
					$idc = $this->getCustomerId();

					if($this->getCartId($idc)) {
						Db::getInstance()->insert('egoi_customers', array(
							'customer' => $idc,
							'id_cart' => (int)$json,
							'estado' => 1
						));
					}
				}
			}
		}
	}

	/**
	 * Track&Engage - TrackOrder
	 * 
	 * @return void
	 */
	public function teOrder() 
	{
		$res = $this->getTrackingAuthorization();

		if (!empty($res)) {
			$list_id = $sql['list_id'];
			$track = $sql['track'];
			$client = $sql['client_id'];

			if($client && $track && $list_id) {

				$cart = new Cart(Tools::getValue('id_cart'));
				$customer = $this->context->cookie->email;

				$order = $this->getOrderDetails(Tools::getValue('id_order'));
				$order_id = $order['reference'];
				$order_total = number_format($order['total_paid'], 0);
				$order_subtotal = number_format($order['total_paid_tax_excl'], 0);
				$order_tax = number_format($order['total_shipping'], 1);
				$order_shipping = number_format($order['total_wrapping'], 1);
				$order_discount = $order['total_discounts'];
				$products = $cart->getProducts();

				include 'includes/te.php';

				$this->assign(
			      	array(
			          	'te' => $te,
		          		'activate' => 1
			      	)
			  	);
				
				$_SESSION['order'] = 1;
				$this->removeCart();
			}
		}
	}

	/**
	 * Check for Tracking authorization
	 * 
	 * @return array|null
	 */
	private function getTrackingAuthorization()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)
					->getRow('SELECT * FROM '._DB_PREFIX_.'egoi WHERE client_id != "" and track="1" order by egoi_id DESC');
	}

	/**
	 * Get Cart ID from Customer Id
	 * 
	 * @param  $customerId
	 * @return mixed
	 */
	private function getCartId($customerId) 
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)
					->getValue("SELECT id_cart FROM "._DB_PREFIX_."egoi_customers WHERE customer='".$customerId."'");
	}

	/**
	 * Get Order details from ID 
	 * 
	 * @param int $orderId
	 * @return mixed     
	 */
	private function getOrderDetails($orderId)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)
					->getRow("SELECT * FROM "._DB_PREFIX_."orders WHERE id_cart='$orderId' OR id_order='$orderId'");
	}

	/**
	 * Get current customer ID from cookie or from session
	 * 
	 * @return int|null
	 */
	private function getCustomerId() 
	{
		return (int)$this->context->cookie->id_customer ?: (int)session_id();
	}

	/**
	 * Remove customer cart from DB
	 * 
	 * @return bool
	 */
	private function removeCart() 
	{
		$idc = $this->getCustomerId();
		return Db::getInstance()->delete('egoi_customers', "customer='$idc'");
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
   	 * Process Block Options
   	 * 
   	 * @param  $blockName
   	 * @param  $optionalArgs
   	 * @return void
   	 */
   	private function processBlockOptions($blockName, $optionalArgs = false)
   	{
   		$block = 'block_'.$blockName;
   		$res = Db::getInstance(_PS_USE_SQL_SLAVE_)
					->getRow('SELECT * FROM '._DB_PREFIX_.'egoi_forms WHERE '.$block.'="1"');
		if (!empty($res)) {

			$this->assign(
		      	array(
	          		'form_id' => $res['form_id'],
		          	'bootstrap' => $res['bootstrap'],
		          	'form_title' => $res['form_title'],
		          	$block => $res[$block],
		          	'form_type' => $form_type
		      	)
		  	);

			if (!empty($optionalArgs) && is_array($optionalArgs)) {
				$this->assign($optionalArgs);
			}

			/*if($res['popup']) {
				$once = $res['once'];
			} */

			if($res['form_type'] == 'iframe') {
				$content = '<iframe src="http://'.$res['url'].'" width="'.$res['style_width'].'" height="'.$res['style_height'].'" style="border: 0 none;" onload="window.parent.parent.scrollTo(0,0);"></iframe>';
			}else{
				$content = html_entity_decode($res['content']);
			}

			if ($res['enable']) {
				$this->assign('content', $content);
				return true;
			}
		}

		return false;
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

   	/**
   	 * @param  array|bool $values
   	 * @return void        
   	 */
   	private function assign($values, $key = false) 
   	{
    	if(!empty($values) && is_array($values)){
	        foreach ($values as $key => $value) {
	        	$this->smarty->assign($key, $value);
	        }

	    }else{
	    	if ($key) {
	    		$this->smarty->assign($key, $values);
			}
	    }
    }


}