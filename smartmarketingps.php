<?php
/**
 * Smart Marketing
 *
 *  @author    E-goi
 *  @copyright 2018 E-goi
 *  @license   LICENSE.txt
 *  @package SmartMarketingPs
 */

if (!defined('_PS_VERSION_'))
  	exit;

class SmartMarketingPs extends Module
{

    const PLUGIN_KEY = 'b2d226e839b116c38f53204205c8410c';

    const SMS_NOTIFICATIONS_SENDER_CONFIGURATION = 'sms_notifications_sender';
    const SMS_NOTIFICATIONS_ADMINISTRATOR_CONFIGURATION = 'sms_notifications_administrator';
    const SMS_NOTIFICATIONS_ADMINISTRATOR_PREFIX_CONFIGURATION = 'sms_notifications_administrator_prefix';
    const SMS_NOTIFICATIONS_DELIVERY_ADDRESS_CONFIGURATION = 'sms_notifications_delivery_address';
    const SMS_NOTIFICATIONS_INVOICE_ADDRESS_CONFIGURATION = 'sms_notifications_invoice_address';

    const SMS_MESSAGES_DEFAULT_LANG_CONFIGURATION = 'sms_messages_default_lang';
    const SMS_REMINDERS_DEFAULT_LANG_CONFIGURATION = 'sms_reminders_default_lang';

    const CUSTOM_INFO_DELIMITER = '%';
    const CUSTOM_INFO_ORDER_REFERENCE = self::CUSTOM_INFO_DELIMITER . 'order_reference' . self::CUSTOM_INFO_DELIMITER;
    const CUSTOM_INFO_ORDER_STATUS = self::CUSTOM_INFO_DELIMITER . 'order_status' . self::CUSTOM_INFO_DELIMITER;
    const CUSTOM_INFO_TOTAL_COST = self::CUSTOM_INFO_DELIMITER . 'total_cost' . self::CUSTOM_INFO_DELIMITER;
    const CUSTOM_INFO_CURRENCY = self::CUSTOM_INFO_DELIMITER . 'currency' . self::CUSTOM_INFO_DELIMITER;
    const CUSTOM_INFO_ENTITY = self::CUSTOM_INFO_DELIMITER . 'entity' . self::CUSTOM_INFO_DELIMITER;
    const CUSTOM_INFO_MB_REFERENCE = self::CUSTOM_INFO_DELIMITER . 'mb_reference' . self::CUSTOM_INFO_DELIMITER;
    const CUSTOM_INFO_SHOP_NAME = self::CUSTOM_INFO_DELIMITER . 'shop_name' . self::CUSTOM_INFO_DELIMITER;
    const CUSTOM_INFO_BILLING_NAME = self::CUSTOM_INFO_DELIMITER . 'billing_name' . self::CUSTOM_INFO_DELIMITER;

    const LIMIT_HOUR_MIN = 10;
    const LIMIT_HOUR_MAX = 22;

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
     * Dev Index file Cache
     *
     * @var string
     */
	protected $dev_cache;

    /**
     * Production Index file Cache
     *
     * @var string
     */
	protected $prod_cache;

    /**
     * Transactional API
     *
     * @var $transactionalApi
     */
	protected $transactionalApi;

	/**
	* Module Constructor
	*/
	public function __construct()
	{
		// Module metadata
		$this->name = 'smartmarketingps';
	    $this->tab = 'advertising_marketing';
	    $this->version = '1.1.0';
	    $this->author = 'E-goi';
	    $this->need_instance = 1;
	    $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
	    $this->bootstrap = true;

	    // module key
	    $this->module_key = '2f50fb7b4988d0a880ac658653d637ad';

        $this->dev_cache = dirname(__FILE__).'/../../app/cache/dev/class_index.php';
        $this->prod_cache = dirname(__FILE__).'/../../app/cache/prod/class_index.php';

	    parent::__construct();

	    // Name & Description
	    $this->displayName = $this->l('Smart Marketing');
	    $this->description = $this->l('Easily sync your Prestashop contacts with E-goi.');

	   	// on uninstall
	    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        spl_autoload_register(array($this, 'autoloadApi'));

        $this->transactionalApi = new TransactionalApi();

	    if (!Configuration::get('SMART_MARKETING')) {
	      	$this->warning = $this->l('No name provided');
	    }

		if(!empty(Tools::getValue('smart_api_key'))) {
			$this->addClientId($_POST);
		}
	    $this->validateApiKey();

		if(Tools::getIsset('id_cart') && (Tools::getValue('id_order')) && (Tools::getValue('key'))) {
			$this->teOrder();
		}

        // check newsletter submissions anywhere
		$this->checkNewsletterSubmissions();
	}

	/**
	 * Autoload API
	 * 
	 * @return void
	 */
	public function autoloadApi() 
	{
        include_once dirname(__FILE__) . '/lib/SmartApi.php';
        include_once dirname(__FILE__) . '/lib/TransactionalApi.php';
    }

	/**
	 * Install App
	 * 
	 * @return bool
	 */
	public function install()
	{
	  	if (!parent::install() || !$this->installDb() || !$this->createMenu()
	  		|| !$this->registerHook(
	  		    array(
	  		        'cart',
	  		        'actionCartSave',
	  		        'actionDispatcher',
	  		        'actionObjectCustomerAddAfter',
                    'actionObjectCustomerUpdateAfter',
	  		        'actionObjectCustomerDeleteAfter',
	  		        'actionOrderStatusPostUpdate',
	  		        'displayHome',
	  		        'displayTop',
	  		        'displayFooter'
                )
            ))
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
		include dirname(__FILE__) . '/install/sql.php';
		
		foreach ($sql as $s){
		    $return &= Db::getInstance()->execute($s);
		}
		return $return;
    }

    /**
	 * Create menu
	 * 
	 * @return bool
	 */
	private function createMenu()
	{
		$subtabs = array(
			'Account' => $this->l('Account'),
			'Sync' => $this->l('Sync Contacts'),
			'Forms' => $this->l('Forms'),
            'SmsNotifications' => $this->l('SMS Notifications')
		);

		foreach (array('ACCOUNT_READ', 'SYNC_READ', 'FORMS_READ') as $val) {
			$result = Db::getInstance()->getValue("SELECT slug FROM "._DB_PREFIX_."authorization_role WHERE slug = 'ROLE_MOD_TAB_".$val."'");
			
			if (isset($result) && ($result)) {
				break;
			}

			Db::getInstance()->insert('authorization_role', 
				array(
	    			'slug' => 'ROLE_MOD_TAB_'.$val
	    		)
			);

			Db::getInstance()->insert('access', 
				array(
					'id_profile' => '1',
	    			'id_authorization_role' => Db::getInstance()->Insert_ID()
	    		)
			);
		}

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

			// insert 2 langs for Menus
			$tab_id = Db::getInstance()->Insert_ID();

			Db::getInstance()->insert('tab_lang', 
				array(
					'id_tab' => $tab_id,
	    			'id_lang' => 1,
	    			'name' => $val
	    		)
			);
			Db::getInstance()->insert('tab_lang', 
				array(
					'id_tab' => $tab_id,
	    			'id_lang' => 2,
	    			'name' => $val
	    		)
			);

			// get spanish lang
			$idlang = Db::getInstance()->getValue("SELECT id_lang FROM "._DB_PREFIX_."lang WHERE iso_code='es'");
			if (isset($idlang) && ($idlang)) {
				Db::getInstance()->insert('tab_lang', 
					array(
						'id_tab' => $tab_id,
		    			'id_lang' => $idlang,
		    			'name' => $val
		    		)
				);
			}

			$index++;
		}

		return true;
	}

    /**
     * Uninstall required tables
     * 
     * @return bool
     */
    protected function uninstallDb() 
	{
		// drop all tables from the plugin
		include dirname(__FILE__) . '/install/sql.php';
    	foreach ($sql as $name => $v){
       		Db::getInstance()->execute('DROP TABLE IF EXISTS '.$name);
   		}

   		// remove menus
   		Db::getInstance()->delete('tab', "module = '$this->name'");
   		Db::getInstance()->delete('tab_lang', "name = 'Smart Marketing' or name='Account' or name='Sync Contacts' or name='Forms' or name='SMS Notifications'");

   		// remove API Key in cache
   		Configuration::deleteByName('smart_api_key');

        Configuration::deleteByName(self::SMS_NOTIFICATIONS_SENDER_CONFIGURATION);
        Configuration::deleteByName(self::SMS_NOTIFICATIONS_ADMINISTRATOR_CONFIGURATION);
		
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
	 * @return mixed
	 */
	public function displayForm() 
	{
		//add headers
		$this->context->controller->addJS($this->_path. 'views/js/config.js');
		$this->context->controller->addCSS($this->_path. 'views/css/main.css');
		
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
		if(!empty(Tools::getValue("api_key"))) {

	        $api = new SmartApi(Tools::getValue("api_key"));
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
    * @return bool
    */
    public function hookActionObjectCustomerAddAfter($params) 
    {
		return $this->addCustomer($params);
	}

	/**
	 * Hook for Update customer
	 * 
	 * @param array $params
	 * @return bool
	 */
	public function hookActionObjectCustomerUpdateAfter($params)
	{
		return $this->updateCustomer($params);
	}

	/**
	 * Hook for Delete customer
	 * 
	 * @param array $params
	 * @return bool
	 */
	public function hookActionObjectCustomerDeleteAfter($params)
	{
		return $this->deleteCustomer($params);
	}

    /**
     * Hook for triggering reminders
     *
     * @param $params
     */
    public function hookActionDispatcher($params)
    {
        $this->triggerReminders();
    }

    /**
     * Hook for updating order status
     *
     * @param array $params
     *
     * @return bool
     */
	public function hookActionOrderStatusPostUpdate($params)
    {
        return $this->sendSmsNotification($params);
    }

    /**
     * Triggers reminders
     */
    private function triggerReminders()
    {
        //Server time. Perhaps retrieve it from store?
        $time = time();
        $hour = $time / 3600 % 24;

        if ($hour > self::LIMIT_HOUR_MIN && $hour < self::LIMIT_HOUR_MAX) {
            $reminders = Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "egoi_sms_notif_order_reminder");
            foreach ($reminders as $reminder) {
                if ($time >= $reminder['send_date']) {
                    $this->sendReminder($reminder['mobile'], $reminder['message'], $reminder['order_id']);
                }
            }
        }
    }

    /**
     * Sends a reminder and removes its entry from database
     *
     * @param $mobile
     * @param $message
     * @param $orderId
     */
    private function sendReminder($mobile, $message, $orderId)
    {
        $senderHash = Configuration::get(self::SMS_NOTIFICATIONS_SENDER_CONFIGURATION);
        $this->transactionalApi->sendSms($mobile, $senderHash, $message);
        $this->deleteReminder($orderId);
    }

    /**
     * Deletes a reminder
     *
     * @param $orderId
     */
    private function deleteReminder($orderId)
    {
        $orderId = pSQL($orderId);
        Db::getInstance()->delete('egoi_sms_notif_order_reminder', "order_id=$orderId");
    }

    /**
     * Sends sms notification
     *
     * @param  $params
     * @return bool
     */
    private function sendSmsNotification($params)
    {
        if (!Configuration::hasKey(self::SMS_NOTIFICATIONS_SENDER_CONFIGURATION)) {
            return false;
        }

        $order = new Order($params['id_order']);
        $newOrderStatus = $params['newOrderStatus'];
        $smsNotif = Db::getInstance()->getRow("SELECT * FROM "._DB_PREFIX_."egoi_sms_notif_order_status WHERE order_status_id=".pSQL($newOrderStatus->id));
        $send = $smsNotif['send_client'] == 1;
        $this->sendClient($newOrderStatus, $order, $send);

        $admin = Configuration::get(self::SMS_NOTIFICATIONS_ADMINISTRATOR_CONFIGURATION);
        if (!empty($admin) && $smsNotif['send_admin'] == 1) {
            return $this->sendAdmin($params['newOrderStatus'], $order, $admin);
        }

        return true;
    }

    /**
     * Sends client message
     *
     * @param $newOrderStatus
     * @param $order
     * @param $send
     *
     * @return bool
     */
    private function sendClient($newOrderStatus, $order, $send)
    {
        $senderHash = Configuration::get(self::SMS_NOTIFICATIONS_SENDER_CONFIGURATION);

        $messages = $this->getNotifMessages($newOrderStatus->id, $order->id_lang);
        $message = $this->parseMessage($messages['client_message'], $newOrderStatus, $order);
        if ($send && empty($message)) {
            return false;
        }

        $addresses = array();
        if (!empty(Configuration::get(self::SMS_NOTIFICATIONS_DELIVERY_ADDRESS_CONFIGURATION))) {
            array_push($addresses, new Address($order->id_address_delivery));
        }
        if (!empty(Configuration::get(self::SMS_NOTIFICATIONS_INVOICE_ADDRESS_CONFIGURATION))) {
            array_push($addresses, new Address($order->id_address_invoice));
        }

        $sent = array();
        foreach ($addresses as $address) {
            $mobile = $this->getPhoneFromOrder($address);
            if (!$mobile) {
                return false;
            }

            $country = new Country($address->id_country);
            $mobile = $country->call_prefix . '-' . $mobile;
            if (isset($sent[$mobile])) {
                continue;
            }

            $sent[$mobile] = 1;
            if ($send) {
                $this->transactionalApi->sendSms($mobile, $senderHash, $message);
            }

            $this->reminder($order, $newOrderStatus, $mobile);
        }

        return true;
    }

    /**
     * Sends admin message
     *
     * @param $newOrderStatus
     * @param $order
     * @param $admin
     *
     * @return bool
     */
    private function sendAdmin($newOrderStatus, $order, $admin)
    {
        $senderHash = Configuration::get(self::SMS_NOTIFICATIONS_SENDER_CONFIGURATION);

        $messages = empty($messages) ? $this->getNotifMessages($newOrderStatus->id, $order->id_lang) : $messages;
        $message = $this->parseMessage($messages['admin_message'], $newOrderStatus, $order);
        if (empty($message)) {
            return false;
        }

        $mobile = Configuration::get(self::SMS_NOTIFICATIONS_ADMINISTRATOR_PREFIX_CONFIGURATION) . '-' . $admin;
        $this->transactionalApi->sendSms($mobile, $senderHash, $message);

        return true;
    }

    /**
     * Get SMS notification messages
     *
     * @param $orderStatusId
     * @param $langId
     *
     * @return mixed
     */
    private function getNotifMessages($orderStatusId, $langId)
    {
        return Db::getInstance()->getRow("SELECT client_message,admin_message FROM "._DB_PREFIX_.
            "egoi_sms_notif_messages WHERE order_status_id=" .pSQL($orderStatusId). " AND lang_id=".pSQL($langId)
        );
    }

    /**
     * Parses custom information and returns the real message
     *
     * @param $message
     * @param $orderStatus
     * @param $order
     *
     * @return mixed
     */
    private function parseMessage($message, $orderStatus, $order)
    {
        $currency = new Currency($order->id_currency);
        $mb = $this->getMbData($order);
        $customer = new Customer($order->id_customer);

        return str_replace(
            [
                self::CUSTOM_INFO_ORDER_REFERENCE,
                self::CUSTOM_INFO_ORDER_STATUS,
                self::CUSTOM_INFO_TOTAL_COST,
                self::CUSTOM_INFO_CURRENCY,
                self::CUSTOM_INFO_ENTITY,
                self::CUSTOM_INFO_MB_REFERENCE,
                self::CUSTOM_INFO_SHOP_NAME,
                self::CUSTOM_INFO_BILLING_NAME
            ],
            [
                $order->reference,
                $orderStatus->name,
                $mb['total_cost'],
                $currency->sign,
                $mb['entity'],
                $mb['reference'],
                Configuration::get('PS_SHOP_NAME'),
                $customer->firstname . ' ' . $customer->lastname
            ],
            $message
        );
    }

    /**
     * Get keys for payment reminders
     *
     * @return array
     */
    public static function getPaymentReminderKeys()
    {
        return array(
            //reminder for ifThenPay. Check OrderState instance on state "Aguardar pagamento multibanco"
            'multibanco' => array('template' => 'multibanco')
        );
    }

    /**
     * Handles reminders
     *
     * @param $order
     * @param $orderState
     * @param $mobile
     */
    private function reminder($order, $orderState, $mobile)
    {
        $reminders = self::getPaymentReminderKeys();
        if (isset($reminders[$orderState->module_name]) &&
            $reminders[$orderState->module_name]['template'] === $orderState->template) {
            $message = $this->getReminderMessage($orderState->id, $order->id_lang)['message'];
            if (empty($message)) {
                return;
            }

            $message = $this->parseMessage($message, $orderState, $order);
            if (!empty($message)) {
                Db::getInstance()->insert(
                    'egoi_sms_notif_order_reminder',
                    array(
                        'order_id' => pSQL($order->id),
                        'mobile' => pSQL($mobile),
                        'send_date' => time() + 300,
                        'message' => pSQL($message)
                    )
                );
            }
        } else {
            $this->deleteReminder($order->id);
        }
    }

    /**
     * Get SMS reminder message
     *
     * @param $orderStatusId
     * @param $langId
     *
     * @return mixed
     */
    private function getReminderMessage($orderStatusId, $langId)
    {
        return Db::getInstance()->getRow("SELECT message FROM "._DB_PREFIX_.
            "egoi_sms_notif_reminder_messages WHERE order_status_id=".pSQL($orderStatusId)
            ." AND lang_id=".pSQL($langId)
            ." AND active=1"
        );
    }

    /**
     * Gets phone or cellphone from order
     *
     * @param $address
     *
     * @return bool
     */
    private function getPhoneFromOrder($address)
    {
        if (!empty($address->phone_mobile)) {
            return $address->phone_mobile;
        } elseif (!empty($address->phone)) {
            return $address->phone;
        }

        return false;
    }

    /**
     * Get mb data
     *
     * @param $order
     *
     * @return array
     */
    private function getMbData($order)
    {
        switch ($order->module) {
            case 'multibanco':
                $data = $this->getIfThenPayData($order);
                break;
            case 'eupago_multibanco':
                $module = Module::getInstanceByName('eupago_multibanco');
                $ref = $module->GenerateReference($order);
                break;
            default:
                $data = array('entity' => '', 'reference' => '', 'total_cost' => '');
        }

        return $data;
    }

    /**
     * Get IfThenPay data
     *
     * @param $order
     *
     * @return array
     */
    private function getIfThenPayData($order)
    {
        $module = Module::getInstanceByName('multibanco');
        $details = $module->getMBDetails();
        $total = $order->getOrdersTotalPaid();
        $ref = $module->GenerateMbRef($details[0], $details[1], $order->id, $total);

        $data = array();
        $data['entity'] = $details[0];
        $data['reference'] = $ref;
        $data['total_cost'] = Tools::displayPrice($total, new Currency($order->id_currency), false);

        return $data;
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
			$row = $this->getFieldMap(0, $key);
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

        $res = $this->getClientData();
		if($res['sync']) {
            // check if is a role defined
            if (!$this->getRole($params['object']->id, $res['role'])) {
                return false;
            }

			$fields['listID'] = $res['list_id'];
			$fields['validate_email'] = '0';

            if($params['object']->newsletter == '0') {
                return false;
            }

			$add = $api->addSubscriber($fields);
			if(isset($add['ERROR']) && ($add['ERROR'])) {
				return false;
			}

            $client_data = $api->getClientData();
            $client = (int)$client_data['CLIENTE_ID'];

			return Db::getInstance()->update('egoi', 
				array(
					'total' => (int)$res['total']
				), "client_id = $client");
		}
	}

	/**
	 * Update customer
	 *
     * @param array $params
	 * @return mixed
	 */
	protected function updateCustomer($params)
	{
		$res = $this->getClientData();
		if($res['sync']) {

			$id = isset($params['object']->id) && ($params['object']->id) ? $params['object']->id : false;
			if($id) {
				$customer = new Customer((int)$id);
				if (!empty($customer)) {
				    // check if is a role defined
                    if (!$this->getRole($customer->id, $res['role'])) {
				        return false;
                    }

					$fields = array(
						'email' => $customer->email
					);
					foreach ($customer as $key => $value) {
						$row = $this->getFieldMap(0, $key);

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

                    $api = new SmartApi();

                    $tag = '';
                    if($params['object']->newsletter == '0') {
                        $name = 'NO_Newsletter';
                        $get_tags = $api->getTags();
                        if (!empty($get_tags)) {
                            foreach ($get_tags as $tags) {
                                if ($tags['NAME'] == $name) {
                                    $tag = $tags['ID'];
                                }
                            }
                        }

                        if (!$tag) {
                            $add_tag = $api->addTag($name);
                            $tag = $add_tag['ID'];
                        }
                    }

                    $fields['listID'] = $res['list_id'];
                    $result = $api->editSubscriber($fields, array($tag));
                    if (isset($result['ERROR']) && ($result['ERROR'])) {
                        $api->addSubscriber($fields, array($tag));
                    }
				}
			}
		}
	}

	/**
	 * Delete customer
	 *
     * @param array $params
	 * @return bool
	 */
	protected function deleteCustomer($params)
	{
		$res = $this->getClientData();
		if($res['sync']) {

			$api = new SmartApi();

			$email = isset($params['object']->email) && ($params['object']->email) ? $params['object']->email : false;
			if ($email) {
                // check if is a role defined
                if (!$this->getRole($params['object']->id, $res['role'])) {
                    return false;
                }

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
     * Get role from Customer
     *
     * @param $customer_id
     * @param $customer_role
     * @return bool
     */
	protected function getRole($customer_id, $customer_role)
    {
        if ($customer_role) {
            $role = Db::getInstance()
                ->getValue("SELECT COUNT(*) FROM "._DB_PREFIX_."customer_group WHERE id_customer='".(int)$customer_id."' and id_group='".(int)$customer_role."'");
            if (!$role) {
                return false;
            }
        }
        return true;
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
		if(isset($this->context->cookie->order) && ($this->context->cookie->order)) {
			$this->context->cookie->__unset('order');
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
		if($this->processBlockOptions('home')) {
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

                $this->context->cookie->__set('order', 1);
                $this->context->cookie->write();
				$this->removeCart();
			}
		}
	}

    /**
     * Get all mapped fields
     *
     * @return array
     */
    public function getMappedFields()
    {
        $sql = "SELECT * FROM " . _DB_PREFIX_ . "egoi_map_fields order by id DESC";
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    /**
     * Get field value map
     *
     * @param $name
     * @param $field
     * @return string
     */
    public function getFieldMap($name = false, $field = false)
    {
        if ($field) {
            $sql = "SELECT * FROM " . _DB_PREFIX_ . "egoi_map_fields WHERE ps='" . pSQL($field) . "'";
        } else {
            $sql = "SELECT * FROM " . _DB_PREFIX_ . "egoi_map_fields WHERE egoi='" . pSQL($name) . "'";
        }
        $rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        return $rq['egoi'];
    }

	/**
	 * Get Client Data from DB
	 *
     * @param $field
     * @param $val
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
        copy(
            dirname(__FILE__).'/override/classes/webservice/WebserviceSpecificManagementEgoi.php',
            dirname(__FILE__).'/../../override/classes/webservice/WebserviceSpecificManagementEgoi.php'
        );

        // if main file not exists
        $file = dirname(__FILE__).'/../../override/classes/webservice/WebserviceRequest.php';
        if (!file_exists($file)) {
            copy(dirname(__FILE__).'/override/classes/webservice/WebserviceRequest.php', $file);
        }

        $this->cleanCache();
    }

    /**
     * Remove overrides
     * 
     * @return void
     */
    private function uninstallSmartOverrides()
    {
        unlink(dirname(__FILE__).'/../../override/classes/webservice/WebserviceSpecificManagementEgoi.php');
        $this->cleanCache();
    }

    /**
     * Clean Index class from cache for Dev && Production
     *
     * @return void
     */
    private function cleanCache()
    {
        if (file_exists($this->dev_cache)) {
            unlink($this->dev_cache);
        }else{
            @unlink(dirname(__FILE__).'/../../var/cache/dev/class_index.php');
        }

        if (file_exists($this->prod_cache)) {
            unlink($this->prod_cache);
        }else{
            @unlink(dirname(__FILE__).'/../../var/cache/prod/class_index.php');
        }
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
					->getValue("SELECT id_cart FROM "._DB_PREFIX_."egoi_customers WHERE customer='".(int)$customerId."'");
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
					->getRow("SELECT * FROM "._DB_PREFIX_."orders WHERE id_cart='".(int)$orderId."' OR id_order='".(int)$orderId."'");
	}

	/**
	 * Get current customer ID from cookie or from session
	 * 
	 * @return int|null
	 */
	private function getCustomerId() 
	{
		return (int)$this->context->cookie->id_customer;
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
   	 * @return bool
   	 */
   	private function processBlockOptions($blockName)
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

			if($res['popup']) {
                $this->context->controller->addJS($this->_path. 'views/js/modal.js');
                $this->context->controller->addCSS($this->_path. 'views/css/modal.css');

                $this->assign($res['popup'], 'popup');
				if ($res['once']) {
					$this->assign($res['once'], 'once');
				}
			}

			if($res['form_type'] == 'iframe') {
				$content = '<iframe src="http://'.$res['url'].'" width="'.$res['style_width'].'" height="'.$res['style_height'].'" style="border: 0 none;" onload="window.parent.parent.scrollTo(0,0);"></iframe>';
			}else{
				$content = html_entity_decode($res['form_content']);
			}

			if ($res['enable']) {
				$this->assign($content, 'content');
				return true;
			}
		}

		return false;
   	}

   	/**
   	 * @param  array|bool $values
   	 * @param $key
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

    /**
     * @return mixed
     */
	private function checkNewsletterSubmissions()
    {
        if (Tools::isSubmit('submitNewsletter')) {
            if ($email = Tools::getValue('email')) {

                $client = $this->getClientData();
                if ($client['sync'] && $client['newsletter_sync']) {

                    if (Validate::isEmail($email)) {

                    	$api = new SmartApi;
                    	$tag_id = $api->processNewTag("NewsletterSubscriptions");

                        return $api
                            ->addSubscriber(
                                array(
                                    'listID' => $client['list_id'],
                                    'email' => $email,
                                    'status' => $client['optin'] ? 0 : 1,
                                    'tags' => array($tag_id)
                                )
                            );
                    }
                }
            }
            return false;
        }
    }


}