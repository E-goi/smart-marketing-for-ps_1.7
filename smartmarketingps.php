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

        spl_autoload_register(array($this, 'autoloadApi'));

	    if (!Configuration::get('SMART_MARKETING')) {
	      	$this->warning = $this->l('No name provided');
	    }

		if(isset($_POST['smart_api_key']) && ($_POST['smart_api_key'])){
			$this->addClientId($_POST);
		}
	    $this->validateApiKey();
		
		// register hooks
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
	  	if (!parent::install() || !$this->installDb() || !$this->createMenu()
	  		|| !$this->registerHook('actionObjectCustomerAddAfter')
	  		|| !$this->registerHook('actionObjectCustomerUpdateAfter')
	  		|| !$this->registerHook('actionObjectCustomerDeleteAfter')
	  		|| !$this->registerHook('actionValidateOrder')
	  		|| !$this->registerHook('displayTop')
	  		|| !$this->registerHook('displayFooter'))
	    	return false;

	    // register WebService
		$this->registerWebService();
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
	 * Create menu
	 * 
	 * @return type
	 */
	private function createMenu()
	{
		$subtabs = array(
			'Account' => $this->l('Account'),
			'Sync' => $this->l('Sync Contacts'),
			'Forms' => $this->l('Forms')
		);

		// main tab
		Db::getInstance()->insert('tab', 
			array(
				'position' => '11',
    			'module' => 'smartmarketingps',
    			'class_name' => 'SmartMarketingPs',
    			'active' => 1
    		)
		);
		
		// main tab lang
		$main_id = Db::getInstance()->Insert_ID();
		Db::getInstance()->insert('tab_lang', 
			array(
				'id_tab' => $main_id,
    			'id_lang' => 1,
    			'name' => 'Smart Marketing'
    		)
		);
		
		$index = 1;
		foreach ($subtabs as $key => $val) {
			Db::getInstance()->insert('tab', 
				array(
					'id_parent' => $main_id,
					'position' => $index,
	    			'module' => 'smartmarketingps',
	    			'class_name' => $key,
	    			'active' => 1
	    		)
			);

			Db::getInstance()->insert('tab_lang', 
				array(
					'id_tab' => Db::getInstance()->Insert_ID(),
	    			'id_lang' => 1,
	    			'name' => $val
	    		)
			);
			$index++;
		}

		return true;
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
   		Db::getInstance()->delete('tab_lang', "name = 'Smart Marketing' or name='Account' or name='Sync Contacts' or name='Forms'");

   		// remove API Key in cache
   		Configuration::deleteByName('smart_api_key');
		
		// remove webservice
		$this->uninstallWebService();

		// remove overrides
		$this->uninstallSmartOverrides();

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
		//add headers
		$this->context->controller->addJS($this->_path. 'views/assets/js/config.js');
		$this->context->controller->addCSS($this->_path. 'views/assets/css/main.css');
		
		// assign vars
        $this->assign($this->success_msg, 'success_msg');
        $this->assign($this->error_msg, 'error_msg');
        $this->assign(Configuration::get('smart_api_key') ? false : true, 'smart_api_key_error');

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
    * Hook for Add customer
    *
    * @param array $params
    * @return void
    */
    public function hookActionObjectCustomerAddAfter($params) 
    {
		return $this->addCustomer($params);
	}

	/**
	 * Hook for Update customer
	 * 
	 * @param array $params
	 * @return void
	 */
	public function hookActionObjectCustomerUpdateAfter($params)
	{
		return $this->updateCustomer($params);
	}

	/**
	 * Hook for Delete customer
	 * 
	 * @param array $params
	 * @return void
	 */
	public function hookActionObjectCustomerDeleteAfter($params)
	{
		return $this->deleteCustomer($params);
	}

	/**
	 * Add customer
	 * 
	 * @param  $params
	 * @return bool
	 */
	protected function addCustomer($params)
	{
		$api = new SmartApi();
		
		$fields = array(
			'email' => $params['object']->email
		);
		foreach ($params['object'] as $key => $value) {
			$row = $api->getFieldMap(0, $key);
			if($row) {
				$fields[$row] = $value;
			}
		}

		if (count($fields) <= 1) {
			// default fields to be passed to E-goi in case the fields are not mapped
			$fields = array_merge($fields, 
				array(
					'first_name' => $params['object']->firstname,
					'last_name' => $params['object']->lastname,
					'birth_date' => $params['object']->birthday
				)
			);
		}
		
		$client_data = $api->getClientData();
		$client = (int)$client_data['CLIENTE_ID'];

		$res = Db::getInstance(_PS_USE_SQL_SLAVE_)
					->getRow('SELECT * FROM '._DB_PREFIX_.'egoi WHERE client_id='.$client);		
		
		if($res['sync']) {

			$fields['listID'] = $res['list_id'];
			$fields['validate_email'] = '0';

			/*if($res['doptin'] == '1') {
				$fields['formID'] = $res['form_id'];
			}*/

			if($params['object']->newsletter == '0') {
				$name = 'NO_Newsletter';
				$get_tags = $api->getTags($name);
				
				
				$tag = '';
				foreach ($get_tags as $tags){
					if($tags['NAME'] == $name) {
						$tag = $tags['ID'];
					}
				}

				if (!$tag) {
					$add_tag = $api->addTag($name);
					$tag = $add_tag['ID'];
				}
			}

			$add = $api->addSubscriber($fields, array($tag));
			if(isset($add['ERROR']) && ($add['ERROR'])) {
				return false;
			}		
			
			return Db::getInstance()->update('egoi', 
				array(
					'total' => (int)$res['total']
				), "client_id = $client");
		}
	}

	/**
	 * Update customer
	 * 
	 * @return void
	 */
	protected function updateCustomer($params)
	{
		$res = $this->getClientData();
		if($res['sync']) {

			$id = isset($params['object']->id) && ($params['object']->id) ? $params['object']->id : false;
			if($id) {
				$customer = new Customer((int)$id);

				if (!empty($customer)) {

					$api = new SmartApi();
					$fields = array(
						'email' => $customer->email
					);
					foreach ($customer as $key => $value) {
						$row = $api->getFieldMap(0, $key);

						if($row){
							$fields[$row] = $value;
						}
					}

					if (count($fields) <= 1) {
						// default fields to be passed to E-goi in case the fields are not mapped
						$fields = array_merge($fields, 
							array(
								'first_name' => $customer->firstname,
								'last_name' => $customer->lastname,
								'birth_date' => $customer->birthday
							)
						);
					}
				}
				
				$fields['listID'] = $res['list_id'];
			 	$result = $api->editSubscriber($fields);
				if (isset($result['ERROR']) && ($result['ERROR'])) {
					$api->addSubscriber($fields);
				}
			}
		}
	}

	/**
	 * Delete customer
	 * 
	 * @return void
	 */
	protected function deleteCustomer($params)
	{
		$res = $this->getClientData();
		if($res['sync']) {

			$api = new SmartApi();

			$email = isset($params['object']->email) && ($params['object']->email) ? $params['object']->email : false;
			if ($email) {
				$rm = $api->removeSubscriber($res['list_id'], $email);
				if(isset($rm['ERROR']) && ($rm['ERROR'])) {
					return false;
				}

				$client_data = $api->getClientData();
				$client = (int)$client_data['CLIENTE_ID'];

				return Db::getInstance()->update('egoi', 
						array(
							'total' => (int)($res['total']-1)
						), "client_id = $client");
			}
		}
	}

	/**
   	 * Hook for display content in Top Page
   	 * 
   	 * @param array $params
   	 * @return mixed
   	 */
   	public function hookDisplayTop($params)
   	{
   		// check if the customer did any Order
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
	 * Track&Engage - check is customer has an Abandoned Cart
	 * 
	 * @return string|bool
	 */
	public function te()
	{
		$res = $this->getClientData('track', 1);
		if (!empty($res)) {
			$list_id = $res['list_id'];
			$client = $res['client_id'];
			$track = $res['track'];
			
			if($client && $list_id && $track) {

				if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
					return false;
				}

				// set customer email var to use in t&e
				$customer = $this->context->cookie->email;

				$cart_id = $this->getCartId($this->getCustomerId());
				$cart = new Cart($cart_id);
				$products = $cart->getProducts();
				
				$cart_zero = 0;
				if ($cart_id) {
					if (empty($products)) {
						$cart_zero = 1;
					}
				}

				$this->removeCart();

				include 'includes/te.php';
				return $te;
			}
		}

		return false;
	}

	/**
	 * Add to Cart
	 * 
	 * @param array $params
	 */
	protected function addToCart($params) 
	{
	 	$json = isset($params['json']) && is_array($params['json']) ? json_decode($params['json']['id']) : $params['cookie']->id_cart;

	 	$res = $this->getClientData('track', 1);
		if (!empty($res)) {
			$list_id = $res['list_id'];
			$track = $res['track'];
			$client = $res['client_id'];
			if($client && $track && $list_id){

				// check if customer has products in the cart (has cart ID?)
				if($json) {
					$idc = $this->getCustomerId();
					
					if(empty($this->getCartId($idc))) {
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
		$res = $this->getClientData('track', 1);

		if (!empty($res)) {
			$list_id = $res['list_id'];
			$track = $res['track'];
			$client = $res['client_id'];

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
	 * Get Client Data from DB
	 * 
	 * @return array|null
	 */
	private function getClientData($field = false, $val = false)
	{
		$instance = Db::getInstance(_PS_USE_SQL_SLAVE_);
		if ($field && $val) {
			$instance->getRow("SELECT * FROM "._DB_PREFIX_."egoi WHERE client_id != '' and $field='$val' order by egoi_id DESC");
		}
		return $instance->getRow("SELECT * FROM "._DB_PREFIX_."egoi WHERE client_id != '' order by egoi_id DESC");	
	}

	/**
     * Process Overrides
     * 
     * @return void
     */
    private function installSmartOverrides()
    {
    	exec('cp '.dirname(__FILE__).'/override/classes/webservice/WebserviceSpecificManagementEgoi.php '.dirname(__FILE__).'/../../override/classes/webservice/');
		@exec('rm -f '.dirname(__FILE__).'/../../app/cache/dev/class_index.php');
		@exec('rm -f '.dirname(__FILE__).'/../../app/cache/prod/class_index.php');
    }

    /**
     * Remove overrides
     * 
     * @return void
     */
    private function uninstallSmartOverrides()
    {
    	exec('rm -f '.dirname(__FILE__).'/../../override/classes/webservice/WebserviceSpecificManagementEgoi.php');
    	@exec('rm -f '.dirname(__FILE__).'/../../app/cache/dev/class_index.php');
    	@exec('rm -f '.dirname(__FILE__).'/../../app/cache/prod/class_index.php');
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
		if(!session_id()){
			session_start();
		}

		return (int)$this->context->cookie->id_customer ?: session_id();
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
		          	'is_bootstrap' => $res['is_bootstrap'],
		          	'form_title' => $res['form_title'],
		          	$block => $res[$block],
		          	'form_type' => $res['form_type']
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
				$content = html_entity_decode($res['form_content']);
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

    /**
     * Add new Client ID
     * 
     * @param array $post
     * @return bool
     */
    private function addClientId($post) 
    {
    	// clean table
		Db::getInstance()->execute('TRUNCATE TABLE '._DB_PREFIX_.'egoi');
		// then insert new data
		Db::getInstance()->insert('egoi', 
			array(
				'client_id' => (int)$post['egoi_client_id']
			)
		);
		return true;
	}


}