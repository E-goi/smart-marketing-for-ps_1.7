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

    const ACTION_CRON_TIME_CONFIGURATION = 'egoi_action_cron_time';
    const ADDRESS_CRON_TIME_CONFIGURATION = 'egoi_address_cron_time';
    const SMS_NOTIFICATIONS_SENDER_CONFIGURATION = 'sms_notifications_sender';
    const SMS_NOTIFICATIONS_ADMINISTRATOR_CONFIGURATION = 'sms_notifications_administrator';
    const SMS_NOTIFICATIONS_ADMINISTRATOR_PREFIX_CONFIGURATION = 'sms_notifications_administrator_prefix';
    const SMS_NOTIFICATIONS_DELIVERY_ADDRESS_CONFIGURATION = 'sms_notifications_delivery_address';
    const SMS_NOTIFICATIONS_INVOICE_ADDRESS_CONFIGURATION = 'sms_notifications_invoice_address';
    const SMS_REMINDER_DEFAULT_TIME_CONFIG = 'sms_reminder_default_time_config';

    const SMS_MESSAGES_DEFAULT_LANG_CONFIGURATION = 'sms_messages_default_lang';
    const SMS_REMINDERS_DEFAULT_LANG_CONFIGURATION = 'sms_reminders_default_lang';

    const CONFIGURED_WEB_PUSH = 'egoi_web_push_config';
    const WEB_PUSH_APP_CODE = 'egoi_web_push_app_code';

    const CUSTOM_INFO_DELIMITER = '%';
    const CUSTOM_INFO_ORDER_REFERENCE = self::CUSTOM_INFO_DELIMITER . 'order_reference' . self::CUSTOM_INFO_DELIMITER;
    const CUSTOM_INFO_ORDER_STATUS = self::CUSTOM_INFO_DELIMITER . 'order_status' . self::CUSTOM_INFO_DELIMITER;
    const CUSTOM_INFO_TOTAL_COST = self::CUSTOM_INFO_DELIMITER . 'total_cost' . self::CUSTOM_INFO_DELIMITER;
    const CUSTOM_INFO_CURRENCY = self::CUSTOM_INFO_DELIMITER . 'currency' . self::CUSTOM_INFO_DELIMITER;
    const CUSTOM_INFO_ENTITY = self::CUSTOM_INFO_DELIMITER . 'entity' . self::CUSTOM_INFO_DELIMITER;
    const CUSTOM_INFO_MB_REFERENCE = self::CUSTOM_INFO_DELIMITER . 'mb_reference' . self::CUSTOM_INFO_DELIMITER;
    const CUSTOM_INFO_SHOP_NAME = self::CUSTOM_INFO_DELIMITER . 'shop_name' . self::CUSTOM_INFO_DELIMITER;
    const CUSTOM_INFO_BILLING_NAME = self::CUSTOM_INFO_DELIMITER . 'billing_name' . self::CUSTOM_INFO_DELIMITER;
    const CUSTOM_INFO_CARRIER = self::CUSTOM_INFO_DELIMITER . 'carrier' . self::CUSTOM_INFO_DELIMITER;
    const CUSTOM_INFO_TRACKING_URL = self::CUSTOM_INFO_DELIMITER . 'tracking_url' . self::CUSTOM_INFO_DELIMITER;
    const CUSTOM_INFO_TRACKING_NUMBER = self::CUSTOM_INFO_DELIMITER . 'tracking_number' . self::CUSTOM_INFO_DELIMITER;

    const PAYMENT_MODULE_IFTHENPAY = 'multibanco';
    const PAYMENT_MODULE_EUPAGO = 'eupago_multibanco';
    const PAYMENT_STATUS_WAITING_MB_PAYMENT = 'waiting_mb_payment';

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
     * API v3
     *
     * @var $transactionalApi
     */
    protected $apiv3;

	/**
	* Module Constructor
	*/
	public function __construct()
	{
		// Module metadata
		$this->name = 'smartmarketingps';
	    $this->tab = 'advertising_marketing';
	    $this->version = '1.6.11';
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
        $this->apiv3 = new ApiV3();

        $warning_message = $this->l('No Apikey provided');
	    if (!Configuration::get('smart_api_key')) {
	      	$this->warning = $warning_message;
	    }

		if(!empty(Tools::getValue('smart_api_key'))) {
			$this->addClientId($_POST);
		}
	    $this->validateApiKey();

		//if( (Tools::getValue('id_order')) && (Tools::getValue('key'))) {
			//$this->teOrder();
		//}

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
        include_once dirname(__FILE__) . '/lib/EgoiRestApi.php';
        include_once dirname(__FILE__) . '/lib/SmartApi.php';
        include_once dirname(__FILE__) . '/lib/TransactionalApi.php';
        include_once dirname(__FILE__) . '/lib/ApiV3.php';
        include_once dirname(__FILE__) . '/includes/TESDK.php';
    }

	/**
	 * Install App
	 *
	 * @return bool
	 */
	public function install()
	{
	    if(!class_exists('SoapClient') || !function_exists('curl_version')){
	        return false;
        }
	  	if (!parent::install() || !$this->installDb() || !$this->createMenu() || !$this->registerHooksEgoi())
	    	return false;

	    // register WebService
		$this->registerWebService();
	  	return true;
	}

	function registerHooksEgoi(){
        return $this->registerHook(
            array(
                'cart',
                'actionCartSave',
                'actionDispatcher',
                'actionObjectCustomerAddAfter',
                'actionObjectCustomerUpdateAfter',
                'actionObjectCustomerDeleteAfter',
                'actionOrderStatusPostUpdate',
                'actionObjectCategoryUpdateAfter',
                'actionObjectCategoryDeleteAfter',
                'actionObjectProductUpdateAfter',
                'actionObjectProductDeleteAfter',
                'displayHome',
                'displayTop',
                'displayFooter',
                'egoiDisplayTE',
            )
        );
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

		if ($return) {
		    $this->mapOrderStateTemplates();
        }

		return $return;
    }

    /**
     * Maps order state templates
     */
    private function mapOrderStateTemplates()
    {
        $orderState = new OrderState($this->context->language->id);
        $orderStates = $orderState->getOrderStates($this->context->language->id);
        $langs = Language::getLanguages(true, $this->context->shop->id);

        $orderStateTemplates = $this->getOrderStateTemplates();
        foreach ($orderStates as $orderState) {
            $paymentMod = self::getPaymentModule($orderState);
            if ($paymentMod) {
                $orderState['template'] = self::PAYMENT_STATUS_WAITING_MB_PAYMENT;
                $this->mapReminderLangs($langs, $orderState);
            }
            if (isset($orderStateTemplates[$orderState['template']])) {
                $this->mapOrderStateTemplateLangs($langs, $orderStateTemplates[$orderState['template']], $orderState);
            }
        }
    }

    /**
     * Maps languages for an order state template
     *
     * @param $langs
     * @param $template
     * @param $orderState
     */
    private function mapOrderStateTemplateLangs($langs, $template, $orderState)
    {
        foreach ($langs as $lang) {
            if (isset($template[$lang['iso_code']])) {
                $clientMessage = $template[$lang['iso_code']]['client'];
                $adminMessage = $template[$lang['iso_code']]['admin'];
            } else {
                $clientMessage = $template['en']['client'];
                $adminMessage = $template['en']['admin'];
            }

            Db::getInstance()->insert(
                'egoi_sms_notif_messages',
                array(
                    'order_status_id' => $orderState['id_order_state'],
                    'lang_id' => $lang['id_lang'],
                    'client_message' => $clientMessage,
                    'admin_message' => $adminMessage
                )
            );
        }
    }

    /**
     * Maps languages for reminder
     *
     * @param $langs
     * @param $orderState
     */
    private function mapReminderLangs($langs, $orderState)
    {
        $reminderTemplates = $this->getReminderTemplates();
        foreach ($langs as $lang) {
            if (isset($reminderTemplates[$lang['iso_code']])) {
                $message = $reminderTemplates[$lang['iso_code']];
            } else {
                $message = $reminderTemplates['en'];
            }

            Db::getInstance()->insert(
                'egoi_sms_notif_reminder_messages',
                array(
                    'order_status_id' => $orderState['id_order_state'],
                    'lang_id' => $lang['id_lang'],
                    'message' => $message,
                    'active' => 0
                )
            );
        }
    }

    /**
     * Get reminder templates
     *
     * @return array
     */
    private function getReminderTemplates()
    {
        return array(
            'en' => 'Hi, ' . self::CUSTOM_INFO_BILLING_NAME . '! MB payment details for your order at '
                . self::CUSTOM_INFO_SHOP_NAME . '. Ent.: '
                . self::CUSTOM_INFO_ENTITY . ' Ref.: ' . self::CUSTOM_INFO_MB_REFERENCE . ' Amount: '
                . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' Thanks!',
            'pt' => 'Olá, ' . self::CUSTOM_INFO_BILLING_NAME . '! Aguardamos o pagamento por MB do pedido que fez na '
                . self::CUSTOM_INFO_SHOP_NAME . '. Ent.: '
                . self::CUSTOM_INFO_ENTITY . ' Ref.: ' . self::CUSTOM_INFO_MB_REFERENCE . ' Valor: '
                . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' Obrigado!',
            'es' => 'Hola ' . self::CUSTOM_INFO_BILLING_NAME . ', Datos para el pago vía MB de tu pedido en '
                . self::CUSTOM_INFO_SHOP_NAME . '. Ent.: '
                . self::CUSTOM_INFO_ENTITY . ' Ref.: ' . self::CUSTOM_INFO_MB_REFERENCE . ' Importe: '
                . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' ¡Gracias!'
        );
    }

    /**
     * Get templates for each order state
     *
     * @return array
     */
    private function getOrderStateTemplates()
    {
        return array(
            'cheque' => array(
                'en' => array(
                    'client' => 'Hi, ' . self::CUSTOM_INFO_BILLING_NAME . '! We are waiting for the payment of your order at ' . self::CUSTOM_INFO_SHOP_NAME . '. Amount to pay: ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . '.',
                    'admin' => 'New order ' . self::CUSTOM_INFO_ORDER_REFERENCE . ' amounting ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' is in ' . self::CUSTOM_INFO_ORDER_STATUS . ' status.'
                ),
                'pt' => array(
                    'client' => 'Olá, ' . self::CUSTOM_INFO_BILLING_NAME . '! Obrigado pelo seu pedido na ' . self::CUSTOM_INFO_SHOP_NAME . '. O valor é de ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . '. Aguardamos o pagamento.',
                    'admin' => 'O novo pedido ' . self::CUSTOM_INFO_ORDER_REFERENCE . ' de ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' está no estado ' . self::CUSTOM_INFO_ORDER_STATUS . '.'
                ),
                'es' => array(
                    'client' => 'Hola ' . self::CUSTOM_INFO_BILLING_NAME . ', Gracias por tu pedido en ' . self::CUSTOM_INFO_SHOP_NAME . '. El importe es de ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . '. Estamos esperando tu pago.',
                    'admin' => 'El nuevo pedido ' . self::CUSTOM_INFO_ORDER_REFERENCE . ' de ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' está en el estado ' . self::CUSTOM_INFO_ORDER_STATUS . '.'
                )
            ),
            'bankwire' => array(
                'en' => array(
                    'client' => 'Hi, ' . self::CUSTOM_INFO_BILLING_NAME . '! We are waiting for the payment of your order at ' . self::CUSTOM_INFO_SHOP_NAME . '. Amount to pay: ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . '.',
                    'admin' => 'New order ' . self::CUSTOM_INFO_ORDER_REFERENCE . ' amounting ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' is in ' . self::CUSTOM_INFO_ORDER_STATUS . ' status.'
                ),
                'pt' => array(
                    'client' => 'Olá, ' . self::CUSTOM_INFO_BILLING_NAME . '! Obrigado pelo seu pedido na ' . self::CUSTOM_INFO_SHOP_NAME . '. O valor é de ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . '. Aguardamos o pagamento.',
                    'admin' => 'O novo pedido ' . self::CUSTOM_INFO_ORDER_REFERENCE . ' de ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' está no estado ' . self::CUSTOM_INFO_ORDER_STATUS . '.'
                ),
                'es' => array(
                    'client' => 'Hola ' . self::CUSTOM_INFO_BILLING_NAME . ', Gracias por tu pedido en ' . self::CUSTOM_INFO_SHOP_NAME . '. El importe es de ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . '. Estamos esperando tu pago.',
                    'admin' => 'El nuevo pedido ' . self::CUSTOM_INFO_ORDER_REFERENCE . ' de ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' está en el estado ' . self::CUSTOM_INFO_ORDER_STATUS . '.'
                )
            ),
            'order_canceled' => array(
                'en' => array(
                    'client' => 'Hi, ' . self::CUSTOM_INFO_BILLING_NAME . '! Your order at ' . self::CUSTOM_INFO_SHOP_NAME . ' has been cancelled.',
                    'admin' => 'New order ' . self::CUSTOM_INFO_ORDER_REFERENCE . ' amounting ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' is in ' . self::CUSTOM_INFO_ORDER_STATUS . ' status.'
                ),
                'pt' => array(
                    'client' => 'Olá, ' . self::CUSTOM_INFO_BILLING_NAME . '! O pedido que fez na nossa loja ' . self::CUSTOM_INFO_SHOP_NAME . ' foi cancelado.',
                    'admin' => 'O novo pedido ' . self::CUSTOM_INFO_ORDER_REFERENCE . ' de ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' está no estado ' . self::CUSTOM_INFO_ORDER_STATUS . '.'
                ),
                'es' => array(
                    'client' => 'Hola ' . self::CUSTOM_INFO_BILLING_NAME . ', Tu pedido en nuestra tienda ' . self::CUSTOM_INFO_SHOP_NAME . ' ha sido cancelado.',
                    'admin' => 'El nuevo pedido ' . self::CUSTOM_INFO_ORDER_REFERENCE . ' de ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' está en el estado ' . self::CUSTOM_INFO_ORDER_STATUS . '.'
                )
            ),
            'payment' => array(
                'en' => array(
                    'client' => 'Hi, ' . self::CUSTOM_INFO_BILLING_NAME . '! The payment for your order at ' . self::CUSTOM_INFO_SHOP_NAME . ' has been confirmed. We are now processing your order. Thanks!',
                    'admin' => 'New order ' . self::CUSTOM_INFO_ORDER_REFERENCE . ' amounting ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' is in ' . self::CUSTOM_INFO_ORDER_STATUS . ' status.'
                ),
                'pt' => array(
                    'client' => 'Olá, ' . self::CUSTOM_INFO_BILLING_NAME . '! O pagamento do pedido que fez na ' . self::CUSTOM_INFO_SHOP_NAME . ' está confirmado. O pedido já está em processamento. Obrigado!',
                    'admin' => 'O novo pedido ' . self::CUSTOM_INFO_ORDER_REFERENCE . ' de ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' está no estado ' . self::CUSTOM_INFO_ORDER_STATUS . '.'
                ),
                'es' => array(
                    'client' => 'Hola ' . self::CUSTOM_INFO_BILLING_NAME . ', El pago de tu pedido en ' . self::CUSTOM_INFO_SHOP_NAME . ' fue confirmado. Estamos procesando el pedido. ¡Gracias!',
                    'admin' => 'El nuevo pedido ' . self::CUSTOM_INFO_ORDER_REFERENCE . ' de ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' está en el estado ' . self::CUSTOM_INFO_ORDER_STATUS . '.'
                )
            ),
            'preparation' => array(
                'en' => array(
                    'client' => 'Hi, ' . self::CUSTOM_INFO_BILLING_NAME . '! We are preparing your order at ' . self::CUSTOM_INFO_SHOP_NAME . ' for shipment. Thanks!',
                    'admin' => ''
                ),
                'pt' => array(
                    'client' => 'Olá, ' . self::CUSTOM_INFO_BILLING_NAME . '! O pedido que fez na ' . self::CUSTOM_INFO_SHOP_NAME . ' está em preparação. Obrigado!',
                    'admin' => ''
                ),
                'es' => array(
                    'client' => 'Hola ' . self::CUSTOM_INFO_BILLING_NAME . ', Estamos preparando el envío de tu pedido en ' . self::CUSTOM_INFO_SHOP_NAME . '. ¡Gracias!',
                    'admin' => ''
                )
            ),
            'refund' => array(
                'en' => array(
                    'client' => 'Hi, ' . self::CUSTOM_INFO_BILLING_NAME . '! We have just refunded your order at ' . self::CUSTOM_INFO_SHOP_NAME . '.',
                    'admin' => 'New order ' . self::CUSTOM_INFO_ORDER_REFERENCE . ' amounting ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' is in ' . self::CUSTOM_INFO_ORDER_STATUS . ' status.'
                ),
                'pt' => array(
                    'client' => 'Olá, ' . self::CUSTOM_INFO_BILLING_NAME . '! Fizemos o reembolso do valor do seu pedido na nossa loja ' . self::CUSTOM_INFO_SHOP_NAME . '.',
                    'admin' => 'O pedido ' . self::CUSTOM_INFO_ORDER_REFERENCE . ' de ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' está no estado ' . self::CUSTOM_INFO_ORDER_STATUS . '.'
                ),
                'es' => array(
                    'client' => 'Hola ' . self::CUSTOM_INFO_BILLING_NAME . ', Hemos finalizado la devolución del importe de tu pedido en ' . self::CUSTOM_INFO_SHOP_NAME . '.',
                    'admin' => 'El pedido ' . self::CUSTOM_INFO_ORDER_REFERENCE . ' de ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' está en el estado ' . self::CUSTOM_INFO_ORDER_STATUS . '.'
                )
            ),
            'outofstock' => array(
                'en' => array(
                    'client' => 'Hi, ' . self::CUSTOM_INFO_BILLING_NAME . '! Unfortunately the item you chose at ' . self::CUSTOM_INFO_SHOP_NAME . ' is out of stock. We will contact you soon about it.',
                    'admin' => 'New order ' . self::CUSTOM_INFO_ORDER_REFERENCE . ' amounting ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' is in ' . self::CUSTOM_INFO_ORDER_STATUS . ' status.'
                ),
                'pt' => array(
                    'client' => 'Olá, ' . self::CUSTOM_INFO_BILLING_NAME . '! Infelizmente o produto que escolheu na ' . self::CUSTOM_INFO_SHOP_NAME . ' está esgotado. Aguarde o nosso contato.',
                    'admin' => 'O pedido ' . self::CUSTOM_INFO_ORDER_REFERENCE . ' de ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' está no estado ' . self::CUSTOM_INFO_ORDER_STATUS . '.'
                ),
                'es' => array(
                    'client' => 'Hola ' . self::CUSTOM_INFO_BILLING_NAME . ', Lamentablemente el producto que elegiste en ' . self::CUSTOM_INFO_SHOP_NAME . ' está agotado. Te contactaremos pronto.',
                    'admin' => 'El pedido ' . self::CUSTOM_INFO_ORDER_REFERENCE . ' de ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' está en el estado ' . self::CUSTOM_INFO_ORDER_STATUS . '.'
                )
            ),
            self::PAYMENT_STATUS_WAITING_MB_PAYMENT => array(
                'en' => array(
                    'client' => 'Hi, ' . self::CUSTOM_INFO_BILLING_NAME . '! MB payment details for your order at ' . self::CUSTOM_INFO_SHOP_NAME . '. Ent.: ' . self::CUSTOM_INFO_ENTITY . ' Ref.: ' . self::CUSTOM_INFO_MB_REFERENCE . ' Amount: ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' Thanks!',
                    'admin' => 'New order ' . self::CUSTOM_INFO_ORDER_REFERENCE . ' amounting ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' is in %order_status% status.'
                ),
                'pt' => array(
                    'client' => 'Olá, ' . self::CUSTOM_INFO_BILLING_NAME . '! Aguardamos o pagamento por MB do pedido que fez na ' . self::CUSTOM_INFO_SHOP_NAME . '. Ent.: ' . self::CUSTOM_INFO_ENTITY . ' Ref.: ' . self::CUSTOM_INFO_MB_REFERENCE . ' Valor: ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' Obrigado!',
                    'admin' => 'O novo pedido ' . self::CUSTOM_INFO_ORDER_REFERENCE . ' de ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' está no estado ' . self::CUSTOM_INFO_ORDER_STATUS . '.'
                ),
                'es' => array(
                    'client' => 'Hola ' . self::CUSTOM_INFO_BILLING_NAME . ', Datos para el pago vía MB de tu pedido en ' . self::CUSTOM_INFO_SHOP_NAME . '. Ent.: ' . self::CUSTOM_INFO_ENTITY . ' Ref.: ' . self::CUSTOM_INFO_MB_REFERENCE . ' Importe: ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' ¡Gracias!',
                    'admin' => 'El nuevo pedido ' . self::CUSTOM_INFO_ORDER_REFERENCE . ' de ' . self::CUSTOM_INFO_TOTAL_COST . self::CUSTOM_INFO_CURRENCY . ' está en el estado ' . self::CUSTOM_INFO_ORDER_STATUS . '.'
                )
            )
        );
    }

    private function disableMenu(){
        Db::getInstance()->delete('tab', "module = '$this->name'");
        Db::getInstance()->delete('tab_lang', "name = 'Smart Marketing' or name='Account' or name='Sync Contacts' or name='Forms' or name='SMS Notifications' or name='Push Notifications'");
        Db::getInstance()->delete('tab_lang', "name = 'Smart Marketing' or name='Conta' or name='Sincronizar contactos' or name='Formulários' or name='Notificações SMS' or name='Notificações Push'");
        return true;
    }

    private function createPermissions(){
        foreach (array('ACCOUNT_READ', 'SYNC_READ', 'FORMS_READ', 'SMSNOTIFICATIONS_READ', 'PRODUCTS_READ', 'PUSHNOTIFICATIONS_READ') as $val) {
            $id_authorization_role = Db::getInstance()->getValue("SELECT id_authorization_role FROM "._DB_PREFIX_."authorization_role WHERE slug = 'ROLE_MOD_TAB_".$val."'");

            if (empty($id_authorization_role)) {
                Db::getInstance()->insert('authorization_role',
                    array(
                        'slug' => 'ROLE_MOD_TAB_'.$val
                    )
                );
                $id_authorization_role = Db::getInstance()->getValue("SELECT id_authorization_role FROM "._DB_PREFIX_."authorization_role WHERE slug = 'ROLE_MOD_TAB_".$val."'");
            }

            $result = Db::getInstance()->getValue("SELECT id_profile,id_authorization_role FROM "._DB_PREFIX_."access WHERE id_profile = '1' AND id_authorization_role = '".$id_authorization_role."'");
            if (empty($result)) {
                Db::getInstance()->insert('access',
                    array(
                        'id_profile' => '1',
                        'id_authorization_role' => $id_authorization_role
                    )
                );
            }
        }
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
            'SmsNotifications' => $this->l('SMS Notifications'),
            'Products' => $this->l('Products'),
            'PushNotifications' => $this->l('PushNotifications')
		);

		$this->createPermissions();

		// main tab
        $result = Db::getInstance()->getValue("SELECT id_tab FROM "._DB_PREFIX_."tab WHERE position = '11' AND module = 'smartmarketingps' AND class_name = 'SmartMarketingPs'");
		if(empty($result)){
            Db::getInstance()->insert('tab',
                array(
                    'position' => '11',
                    'module' => 'smartmarketingps',
                    'class_name' => 'SmartMarketingPs',
                    'active' => 1,
                )
            );
            $main_id = Db::getInstance()->Insert_ID();
        }else{
            $main_id = $result;
        }

        $langs = Language::getLanguages(true, $this->context->shop->id);
        foreach ($langs as $lang) {
            if (empty($lang['id_lang'])) {
                continue;
            }

            // main tab lang
            Db::getInstance()->insert('tab_lang',
                array(
                    'id_tab' => $main_id,
                    'id_lang' => $lang['id_lang'],
                    'name' => 'Smart Marketing'
                )
            );
        }

        $index = 1;
        foreach ($subtabs as $key => $val) {
            $result = Db::getInstance()->getValue("SELECT id_tab FROM "._DB_PREFIX_."tab WHERE module = 'smartmarketingps' AND class_name = '".$key."'");
            if(!empty($result)){
                $index++;
                continue;
            }
            Db::getInstance()->insert('tab',
                array(
                    'id_parent' => $main_id,
                    'position' => $index,
                    'module' => 'smartmarketingps',
                    'class_name' => $key,
                    'active' => 1
                )
            );

            $tab_id = Db::getInstance()->Insert_ID();
            foreach ($langs as $lang) {
                if (empty($lang['id_lang'])) {
                    continue;
                }
                Db::getInstance()->insert('tab_lang',
                    array(
                        'id_tab' => $tab_id,
                        'id_lang' => $lang['id_lang'],
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
   		$this->disableMenu();

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
        $this->registerHooksEgoi();
        if( !parent::enable($force_all) || !$this->createMenu() || !$this->installDb()){
            return false;
        }
        return true;
    }

    /**
    * Disable module.
    *
    * @return $force_all
    * @param bool
    */
    public function disable($force_all = false)
    {
        if (!parent::disable($force_all) || !$this->disableMenu()){
            return false;
        }
        return true;
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
                $this->transactionalApi = new TransactionalApi();
                $this->transactionalApi->enableClient();
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

        $this->createMenu();

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
        $timeSaved = Configuration::get(self::ACTION_CRON_TIME_CONFIGURATION);
        if (empty($timeSaved)){
            Configuration::updateValue(self::ACTION_CRON_TIME_CONFIGURATION, time());
            return;
        }
        if(time() - $timeSaved < 60){
            return;
        }
        $this->triggerReminders();
        $this->triggerCellphoneSync();
        Configuration::updateValue(self::ACTION_CRON_TIME_CONFIGURATION, time());
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
        $this->syncOrderTE($params);
        return $this->sendSmsNotification($params);
    }

    /**
     * Hook for category update
     *
     * @param $params
     */
    public function hookActionObjectCategoryUpdateAfter($params)
    {
        $this->updateCategories();
    }

    /**
     * Hook for category removal
     *
     * @param $params
     */
    public function hookActionObjectCategoryDeleteAfter($params)
    {
        $this->updateCategories();
    }

    private function updateCategories()
    {
        $catalogsEnabled = Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "egoi_active_catalogs ORDER BY catalog_id DESC");
        if(!empty($catalogsEnabled)) {
            Configuration::updateValue('egoi_import_categories', true);
        }
    }

    /**
     * Hook for product update
     *
     * @param array $params
     */
    public function hookActionObjectProductUpdateAfter($params)
    {
        $product = $params['object'];
        if ($product->active) {
            $languages = Language::getLanguages(true, Context::getContext()->shop->id);
            $currencies = Currency::getCurrencies(true);
            $catalogsEnabled = Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "egoi_active_catalogs ORDER BY catalog_id DESC");
            foreach ($catalogsEnabled as $catalog) {
                if (!$this->checkLangCurrency($languages, $langId, $currencies, $currencyId, $catalog) || !$catalog['active']) {
                    continue;
                }

                $data = static::mapProduct($product, $langId, $currencyId);
                $result = $this->apiv3->createProduct($catalog['catalog_id'], $data);

                if (!empty($result['errors']['product_already_exists'])) {
                    $id = $data['product_identifier'];
                    unset($data['product_identifier']);
                    $this->apiv3->updateProduct($catalog['catalog_id'], $id, $data);
                }
            }
        }
    }

    /**
     * Hook for product add
     *
     * @param array $params
     */
    public function hookActionObjectProductDeleteAfter($params)
    {
        $product = $params['object'];
        if ($product->active) {
            $languages = Language::getLanguages(true, Context::getContext()->shop->id);
            $currencies = Currency::getCurrencies(true);
            $catalogsEnabled = Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "egoi_active_catalogs ORDER BY catalog_id DESC");
            foreach ($catalogsEnabled as $catalog) {
                if (!$this->checkLangCurrency($languages, $langId, $currencies, $currencyId, $catalog) || !$catalog['active']) {
                    continue;
                }

                $this->apiv3->deleteProduct($catalog['catalog_id'], $product->id);
            }
        }
    }

    private function checkLangCurrency($languages, &$langId, $currencies, &$currencyId, $catalog)
    {
        $langId = 0;
        foreach ($languages as $language) {
            if ($language['iso_code'] === strtolower($catalog['language'])) {
                $langId = $language['id_lang'];
            }
        }
        if ($langId === 0) {
            return false;
        }

        $currencyId = 0;
        foreach ($currencies as $currency) {
            if ($currency->iso_code === $catalog['currency']) {
                $currencyId = $currency->id;
            }
        }
        if ($currencyId === 0) {
            return false;
        }

        return true;
    }

    public static function mapProduct($product, $lang, $currency)
    {
        if (is_array($product)) {
            $product = new Product($product['id_product'], true, $lang);
        } else {
            $product = new Product($product->id, true, $lang);
        }

        $link = new Link();

        $desc = filter_var($product->description_short, FILTER_SANITIZE_STRING);
        $price = $product->getPrice(true);
        $salePrice = $product->getPrice(true);

        if ($price == $salePrice) {
            $salePrice = 0;
        }

        $url = $link->getProductLink($product, null, null, null, $lang, null);
        if (strpos($url, '?') !== false) {
            $concatChar = '&';
        } else {
            $concatChar = '?';
        }

        $url = $link->getProductLink($product, null, null, null, $lang, null) . $concatChar . 'SubmitCurrency=1&id_currency=' . $currency;

        $img = $product->getCover($product->id);
        $ssl = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
        $imageUrl = $ssl . $link->getImageLink(isset($product->link_rewrite) ? $product->link_rewrite : $product->name, (int)$img['id_image'], 'home_default');

        $categories = static::buildBreadcrumbs($product->getCategories(), $lang);

        $relatedProducts = array();

        $acessories = Product::getAccessoriesLight($lang, $product->id);
        foreach ($acessories as $item) {
            $relatedProducts[] = $item['id_product'];
        }

        return array(
            'product_identifier' => $product->id,
            'name' => $product->name,
            'description' => $desc,
            'sku' => $product->reference,
            'upc' => $product->upc,
            'ean' => $product->ean13,
            'link' => $url,
            'image_link' => $imageUrl,
            'price' => $price,
            'sale_price' => $salePrice,
            'brand' => $product->manufacturer_name,
            'categories' => $categories,
            'related_products' => $relatedProducts
        );
    }

    private static function buildBreadcrumbs($categories, $lang)
    {
        $categoryCount = count($categories);
        $result = array();
        for ($i = 0; $i < $categoryCount; $i++) {
            $category = new Category($categories[$i], $lang);
            $breadcrumb = $category->name;

            while ($category->id_parent !== '1' && $category->id_parent !== '0') {
                $category = new Category($category->id_parent, $lang);
                $breadcrumb = $category->name . '>' . $breadcrumb;
            }

            $result[] = $breadcrumb;
        }

        return $result;
    }


    /**
     * needed because customer's cellphone is in address and this is
     * created after user
     */
    private function triggerCellphoneSync(){

        $timeSaved = Configuration::get(self::ADDRESS_CRON_TIME_CONFIGURATION);
        if(empty($timeSaved) || ( $timeSaved + (10 * 60) ) > time() ){//working after first sync && in 10 min intervals
            return;
        }

        $res = SmartMarketingPs::getClientData();

        $list_id            = $res['list_id'];
        $newsletter_sync    = $res['newsletter_sync'];
        $store_id = Tools::getValue("store_id");
        if(!empty($store_id)){
            $store_filter = ' AND '._DB_PREFIX_.'customer.id_shop="'.$store_id.'" ';
        }else{
            $store_filter = '';
        }


        $ts = [];
        if(!empty($store_id) && !empty($store_name)){
            array_push($ts, $store_name);
        }

        $add='';
        if(!empty($newsletter_sync) && $newsletter_sync == '1'){
            $add = ' AND '._DB_PREFIX_.'customer.newsletter="1" ';
            array_push($ts, 'newsletter');
        }

        $sqlc = 'SELECT email, '._DB_PREFIX_.'customer.firstname, '._DB_PREFIX_.'customer.lastname, birthday, newsletter, optin, id_shop, id_lang, phone, phone_mobile, call_prefix FROM '._DB_PREFIX_.'customer INNER JOIN '._DB_PREFIX_.'address ON '._DB_PREFIX_.'customer.id_customer = '._DB_PREFIX_.'address.id_customer INNER JOIN '._DB_PREFIX_.'country ON '._DB_PREFIX_.'country.id_country = '._DB_PREFIX_.'address.id_country WHERE '._DB_PREFIX_.'customer.active="1" AND '._DB_PREFIX_.'address.date_upd >= "'. date('Y-m-d H:i:s', $timeSaved) .'" OR '._DB_PREFIX_.'address.date_add >= "'. date('Y-m-d H:i:s', $timeSaved) .'"'.$add.$store_filter.' GROUP BY '._DB_PREFIX_.'customer.id_customer';
        $getcs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sqlc);

        if(empty($getcs)){
            return;
        }

        $tags = SmartMarketingPs::makeTagMap($ts);

        $array = [];
        foreach($getcs as $row){
            $array[] = SmartMarketingPs::mapSubscriber($row);
        }

        if(!empty($array)){
            $api = new SmartApi();
            if(count($array) == 1){
                $array[0]['listID'] = $list_id;
                $result = $api->editSubscriber($array[0], $tags);
                if (isset($result['ERROR']) && ($result['ERROR'])) {
                    $api->addSubscriber($array[0], $tags);
                }
            }else{
                $api->addSubscriberBulk($list_id, $array, $tags);
            }
        }

        Configuration::updateValue(self::ADDRESS_CRON_TIME_CONFIGURATION, time());

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
        $mb = $this->getMbData($order, $orderStatus);
        $customer = new Customer($order->id_customer);
        $carrier = new Carrier($order->id_carrier);

        return str_replace(
            [
                self::CUSTOM_INFO_ORDER_REFERENCE,
                self::CUSTOM_INFO_ORDER_STATUS,
                self::CUSTOM_INFO_TOTAL_COST,
                self::CUSTOM_INFO_CURRENCY,
                self::CUSTOM_INFO_ENTITY,
                self::CUSTOM_INFO_MB_REFERENCE,
                self::CUSTOM_INFO_SHOP_NAME,
                self::CUSTOM_INFO_BILLING_NAME,
                self::CUSTOM_INFO_CARRIER,
                self::CUSTOM_INFO_TRACKING_URL,
                self::CUSTOM_INFO_TRACKING_NUMBER
            ],
            [
                $order->reference,
                $orderStatus->name,
                $mb['total_cost'],
                $currency->sign,
                $mb['entity'],
                $mb['reference'],
                Configuration::get('PS_SHOP_NAME'),
                $customer->firstname . ' ' . $customer->lastname,
                $carrier->name,
                str_replace('@', $order->shipping_number, $carrier->url),
                $order->shipping_number
            ],
            $message
        );
    }

    /**
     * Returns the payment module key from order
     *
     * @param $orderState
     *
     * @return bool|string
     */
    public static function getPaymentModule($orderState)
    {
        if (self::isPaymentIfthenPay($orderState)) {
            return self::PAYMENT_MODULE_IFTHENPAY;
        }

        if (self::isPaymentEuPago($orderState)) {
            return self::PAYMENT_MODULE_EUPAGO;
        }

        return false;
    }

    /**
     * Checks if payment is from ifThenPay module
     *
     * @param $orderState
     *
     * @return bool
     */
    private static function isPaymentIfthenPay($orderState)
    {
        $orderState = (array)$orderState;
        if ($orderState['template'] == 'multibanco' || ($orderState['module_name'] == 'ifthenpay' && stripos($orderState['name'], "Multibanco") !== false) ) {
            return true;
        }

        return false;
    }

    /**
     * Checks if payment is from euPago module
     *
     * @param $orderState
     *
     * @return bool
     */
    private static function isPaymentEuPago($orderState)
    {
        $search = 'euPago';
        $orderState = (array)$orderState;
        if (substr($orderState['name'], 0, strlen($search)) === $search && $orderState['template'] == '') {
            return true;
        }

        return false;
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
        if (self::getPaymentModule($orderState)) {
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
                        'send_date' => time() + Configuration::get(self::SMS_REMINDER_DEFAULT_TIME_CONFIG) * 3600,
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
            "egoi_sms_notif_reminder_messages WHERE lang_id=".pSQL($langId)
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
     * @param $orderState
     *
     * @return array
     */
    private function getMbData($order, $orderState)
    {
        $data = array('entity' => '', 'reference' => '', 'total_cost' => $order->total_paid);
        $paymentModule = self::getPaymentModule($orderState);
        switch ($paymentModule) {
            case 'multibanco':
                $data = $this->getIfThenPayData($order);
                break;
            case 'eupago_multibanco':
                $data = $this->getEuPagoData($order);
                break;
            default:
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

        $total = $order->getOrdersTotalPaid();
        if(!empty($total) && is_numeric($total)){
            $total = sprintf('%0.2f', $total);
        }

        if($order->module == 'ifthenpay'){//check ifthenpay module
            $details = (array) unserialize(Configuration::get('IFTHENPAY_USER_ACCOUNT'));
            $ent = $details[0]['Entidade'];
            $ref = $this->IfThenPayGenerateMbRef($ent, $details[0]['SubEntidade'][0], $order->id, $total);
        }else{
            $module = Module::getInstanceByName('multibanco');
            $details = $module->getMBDetails();
            $ent = $details[0];
            $ref = $module->GenerateMbRef($details[0], $details[1], $order->id, $total);
        }
        return $this->buildMbArray($ent, $ref, $total, $order->id_currency);
    }


    /**
     * IFTHENPAY
     * @param $ent_id
     * @param $subent_id
     * @param $order_id
     * @param $order_value
     * @return string
     */
    private function IfThenPayGenerateMbRef($ent_id, $subent_id, $order_id, $order_value)
    {

        $order_id ="0000".$order_id;

        $order_value =  $this->format_number($order_value);

        //Apenas sao considerados os 4 caracteres mais a direita do order_id
        $order_id = substr($order_id, (strlen($order_id) - 4), strlen($order_id));


        while ($order_value >= 1000000){
            $this->IfThenPayGenerateMbRef($order_id++, 999999.99);
            $order_value -= 999999.99;
        }

        //cálculo dos check digits
        $chk_str = sprintf('%05u%03u%04u%08u', $ent_id, $subent_id, $order_id, round($order_value*100));

        $chk_array = array(3, 30, 9, 90, 27, 76, 81, 34, 49, 5, 50, 15, 53, 45, 62, 38, 89, 17, 73, 51);

        $chk_val=0;

        for ($i = 0; $i < 20; $i++)
        {
            $chk_int = substr($chk_str, 19-$i, 1);
            $chk_val += ($chk_int%10)*$chk_array[$i];
        }

        $chk_val %= 97;

        $chk_digits = sprintf('%02u', 98-$chk_val);

        return $subent_id." ".substr($chk_str, 8, 3)." ".substr($chk_str, 11, 1).$chk_digits;

    }


    /**
     * IFTHENPAY number format
     * @param $number
     * @return string
     */
    private function format_number($number)
    {
        $verifySepDecimal = number_format(99,2);

        $valorTmp = $number;

        $sepDecimal = substr($verifySepDecimal, 2, 1);

        $hasSepDecimal = True;

        $i=(strlen($valorTmp)-1);

        for($i;$i!=0;$i-=1)
        {
            if(substr($valorTmp,$i,1)=="." || substr($valorTmp,$i,1)==","){
                $hasSepDecimal = True;
                $valorTmp = trim(substr($valorTmp,0,$i))."@".trim(substr($valorTmp,1+$i));
                break;
            }
        }

        if($hasSepDecimal!=True){
            $valorTmp=number_format($valorTmp,2);

            $i=(strlen($valorTmp)-1);

            for($i;$i!=1;$i--)
            {
                if(substr($valorTmp,$i,1)=="." || substr($valorTmp,$i,1)==","){
                    $hasSepDecimal = True;
                    $valorTmp = trim(substr($valorTmp,0,$i))."@".trim(substr($valorTmp,1+$i));
                    break;
                }
            }
        }

        for($i=1;$i!=(strlen($valorTmp)-1);$i++)
        {
            if(substr($valorTmp,$i,1)=="." || substr($valorTmp,$i,1)=="," || substr($valorTmp,$i,1)==" "){
                $valorTmp = trim(substr($valorTmp,0,$i)).trim(substr($valorTmp,1+$i));
                break;
            }
        }

        if (strlen(strstr($valorTmp,'@'))>0){
            $valorTmp = trim(substr($valorTmp,0,strpos($valorTmp,'@'))).trim($sepDecimal).trim(substr($valorTmp,strpos($valorTmp,'@')+1));
        }

        return $valorTmp;
    }

    /**
     * Get EuPago data
     *
     * @param $order
     *
     * @return array
     */
    private function getEuPagoData($order)
    {
        $module = Module::getInstanceByName('eupago_multibanco');
        $result = $module->GenerateReference((int)$order->id, $order->total_paid);

        $entity = '';
        $ref = '';
        $total = $order->total_paid;
        if ($result->estado == 0) {
            $entity = $result->entidade;
            $ref = $result->referencia;
            $total = $result->valor;
        }

        return $this->buildMbArray($entity, $ref, $total, $order->id_currency);
    }

    /**
     * Returns MB data
     *
     * @param $entity
     * @param $ref
     * @param $total
     * @param $currency
     *
     * @return array
     */
    private function buildMbArray($entity, $ref, $total, $currency)
    {
        $data = array();
        $data['entity'] = $entity;
        $data['reference'] = $ref;
        $data['total_cost'] = Tools::displayPrice($total, new Currency($currency), false);

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
			'email' => $params['object']->email,
            'lang' => Language::getLanguage($params['object']->id_lang)['iso_code']
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

            $options = self::getClientData();

            if( !empty($options['newsletter_sync']) && $params['object']->newsletter == '0') {
                return false;
            }

			$add = $api->addSubscriber($fields);
			if(isset($add['ERROR']) && ($add['ERROR'])) {
				return false;
			}

			if ($add !== false) {
                $this->context->cookie->__set('egoi_uid', $add);
                $this->context->cookie->write();

                Db::getInstance()->insert('egoi_customer_uid', array(
                    'uid' => $add,
                    'email' => pSQL($params['object']->email)
                ));
                //Configuration::updateValue('egoi_contacts', array($params['object']->email => $add));
            }

            $client_data = $api->getClientData();
            $client = (int)$client_data['CLIENTE_ID'];

			return Db::getInstance()->update('egoi',
				array(
					'total' => (int)$res['total']
				), "client_id = $client");
		}
	}

    public static function getShopsName($id){
        try{
            $sql = 'SELECT * FROM '._DB_PREFIX_.'shop where id_shop = '.$id;
            $rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            return empty($rq[0]['name'])?false:$rq[0]['name'];
        }catch (Exception $e){
            return false;
        }
    }

    /*
     * Count size of list by store
     * */
    public static function sizeList(){
        $options = self::getClientData();
        $add = '';
        if(!empty($options['newsletter_sync'])){
            $add = 'AND newsletter="1"';
        }
        $sql = 'SELECT COUNT(*) as total, id_shop FROM '._DB_PREFIX_.'customer WHERE active="1" '.$add.' group by id_shop';//AND newsletter="1"

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    /*
     * gets tag number from name (creates if they dont exist)
     * */
    public static function makeTagMap($ts = []){
        $api = new SmartApi();


        $resp = $api->getTags();

        $mapped_ids = [];

        foreach ($resp as $tag){
            for($i = 0;$i<count($ts);$i++){
                if(strcasecmp($tag['NAME'], $ts[$i]) == 0){
                    unset($ts[$i]);
                    array_push($mapped_ids, $tag['ID']);
                }
            }
        }

        foreach ($ts as $tagName){
            $resp = $api->addTag($tagName);
            if(isset($resp['ID'])){
                array_push($mapped_ids, $resp['ID']);
            }
        }

        return $mapped_ids;
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
						'email' => $customer->email,
                        'lang' => Language::getLanguage($params['object']->id_lang)['iso_code']
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
     * Return Scripts TPL
     *
     * @param array $params
     * @return mixed
     */
    public function egoiScriptsTPL($params){
        $webPush = null;
        $appCode = Configuration::get(static::WEB_PUSH_APP_CODE);
        include_once 'includes/webPush.php';

        $this->assign(
            array(
                'te' => $this->te(),
                'activate' => 1,
                'wp'=> $webPush
            )
        );

        // check if this block is activated
        if($this->processBlockOptions('header')) {
            return $this->display(__FILE__, 'smartmarketingps.tpl');
        }


        return $this->display(__FILE__, 'ecommerce/front-scripts.tpl');
    }

	/**
   	 * Hook for display content in Top Page
   	 *
   	 * @param array $params
   	 * @return mixed
   	 */
   	public function hookDisplayTop($params)
   	{
        return $this->egoiScriptsTPL($params);
	}

    /**
     * Hook for display TE sccript, use if theme doesn't call hookDisplayTop naturaly
     *
     * @param array $params
     * @return mixed
     */
    public function hookEgoiDisplayTE($params)
    {
        return $this->egoiScriptsTPL($params);
    }

    /**
     * Hook for adding webservice ressources
     *
     * @param array $params
     * @return mixed
     */
    public function hookAddWebserviceResources($params)
    {
        return [
            'egoi' => [
                'description' => 'Manage Product info in JSON',
                'specific_management' => true
            ]
        ];

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
            $social_track = $res['social_track'];
            $social_track_json = $res['social_track_json'];
            $social_track_id = $res['social_track_id'];
            $te = '';

			if($client && $list_id && $track) {
				if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
					return false;
				}

				// set customer email var to use in t&e
				$customer = !$this->context->cookie->__isset('egoi_uid')
                    ? $this->context->cookie->email
                    : $this->context->cookie->__get('egoi_uid');

				$cart_id = $this->context->cookie->id_cart;//$this->getCartId($this->getCustomerId());
				$cart = new Cart($cart_id);

				$products = $cart->getProducts();

				//$this->removeCart();
                include 'includes/te.php';
			}

            if($social_track){
                include 'includes/TrackingSocial.php';
                if ($this->context->controller instanceof ProductController && $social_track_json)
                {
                    $product = $this->context->controller->getProduct();
                    if($product instanceof Product){
                        include 'includes/TrackingLdJson.php';
                    }
                }
            }

            return $te;
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

    /*
     * Map subscriber to egoi map
     * */
    public static function mapSubscriber($row){
        $subscriber=[//default map
            'first_name'    => $row['firstname'],
            'email'         => $row['email'],
            'last_name'     => $row['lastname'],
            'birth_date'    => isset($row['birthdate'])?$row['birthdate']:$row['birthday'],
            'status'        => 1,
            'lang'          => Language::getLanguage($row['id_lang'])['iso_code']
        ];

        if(!empty($row['call_prefix']) && !empty($row['phone_mobile'])){
            $subscriber['cellphone'] = $row['call_prefix'].'-'.$row['phone_mobile'];
        }

        if(!empty($row['call_prefix']) && !empty($row['phone'])){
            $subscriber['telephone'] = $row['call_prefix'].'-'.$row['phone'];
        }
        foreach ($row as $field => $value){
            $field = self::getFieldMap(0, $field);

            if(empty($field)){
                continue;
            }

            $subscriber[$field] = $value;
        }

        return $subscriber;

    }


    /**
	 * Get Client Data from DB
	 *
     * @param $field
     * @param $val
	 * @return array|null
	 */
	public static function getClientData($field = false, $val = false)
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
        /*
        $file = dirname(__FILE__).'/../../override/classes/webservice/WebserviceRequest.php';
        if (!file_exists($file)) {
            copy(dirname(__FILE__).'/override/classes/webservice/WebserviceRequest.php', $file);
        }*/

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
					->getRow("SELECT * FROM "._DB_PREFIX_."orders WHERE id_order='".(int)$orderId."'");
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

    private function syncOrderTE($params) {

        $res = $this->getClientData('track', 1);
        if (empty($res['track'])) {
            return false;
        }

        $order = new Order($params['id_order']);
        $products = $order->getProducts();

        $customer = new Customer($order->id_customer);

        $list_id = $res['list_id'];
        $client = $res['client_id'];

        if(!empty($res['track_state'])){
            if($res['track_state'] != $params['newOrderStatus']->id){
                return false;
            }
        }

        $uid = Db::getInstance()->getValue('SELECT uid FROM '._DB_PREFIX_."egoi_customer_uid WHERE email='".pSQL($customer->email)."';");
        $teSdk = new TESDK($client, empty($uid)?$customer->email:$uid ,$list_id);
        $teSdk->convertOrder([
            'order' => $order,
            'products' => $products
        ]);

    }
}