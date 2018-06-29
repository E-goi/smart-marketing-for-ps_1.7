<?php

include_once dirname(__FILE__).'/../SmartMarketingBaseController.php';

/**
 * @package controllers/admin/SyncController
 */
class SyncController extends SmartMarketingBaseController 
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
		$this->meta_title = $this->l('E-goi Sync Contacts').' - '.$this->module->displayName;
		
		if (!$this->module->active)
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
		
		$this->retrieveRoles();
		$this->mapFieldsEgoi();
		$this->syncronizeEgoi();
		$this->sincronizeList();
	}

	/**
	 * Inject Dependencies
	 * 
	 * @return mixed
	 */
	public function setMedia()
	{
		parent::setMedia();
		$this->addJS($this->_path. '/views/assets/js/sync.js');
	}

	/**
	 * Toolbar settings
	 * 
	 * @return void
	 */
	public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        $this->page_header_toolbar_btn['save-and-stay'] = array(
		    'short' => $this->l('Save Settings'),
		    'href' => '#',
		    'desc' => $this->l('Save Settings'),
		    'js' => $this->l('$( \'#action_add\' ).click();')
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

			if(!empty($_POST)) {
				$this->saveSync();
			}

			$this->assign('lists', $this->api->getLists());

			$rq = Db::getInstance(_PS_USE_SQL_SLAVE_)
					->getRow('SELECT * FROM '._DB_PREFIX_.'egoi where client_id!="" order by egoi_id DESC');

			if(!empty($rq)) {
				$list_id = $rq['list_id'];
				$sync = $rq['sync'];
				$track = $rq['track'];
				$role = $rq['role'];
                $optin = $rq['optin'];
			}

			if(isset($list_id) && ($list_id)) {
				
				$this->assign('subscribers', $this->api->getSubscribersFromListId($list_id));
				$this->assign('list_id', $list_id);
				$this->assign('sync', $sync);
				$this->assign('track', $track);
				$this->assign('role_id', $role);
				$this->assign('optin', $optin);

				//map fields
				$egoi_fields = array(
					'first_name' => 'First name',
					'last_name' => 'Last name',
					'surname' => 'Surname',
					'cellphone' => 'Mobile',
					'telephone' => 'Telephone',
					'birth_date' => 'Birth Date'
				);

                $extra_fields = $this->api->getExtraFields($list_id);
                if (!empty($extra_fields)) {
                    foreach($extra_fields as $key => $extra_field) {
                        $egoi_fields['extra_'.$key] = $extra_field['NAME'];
                    }

                    $option = '';
                    foreach($egoi_fields as $key => $field) {
                        $option .= '<option value='.$key.'>'.$field.'</option>'.PHP_EOL;
                    }

                    $this->assign('select', $option);
                }

				$this->assign('mapped_fields', $this->getMappedFields());
			}

			$this->assign('token', $_GET['token']);
			$this->assign('content', $this->fetch('sync.tpl'));
		}
	}

	/**
	 * Save syncronization data
	 * 
	 * @return mixed
	 */
	protected function saveSync() 
	{
		if(isset($_POST['action_add']) && ($_POST['action_add'])) {

			$list = $_POST['list'];
			$sync = $_POST['enable'];
			$role = $_POST['role'];

            $optin = 0;
			if (isset($_POST['newsletter_optin'])) {
                $optin = $_POST['newsletter_optin'];
            }
			$track = isset($_POST['track']) ? $_POST['track'] : 1;

			// compare client ID -> API with DB
			$client_data = $this->api->getClientData();
			$client = $client_data['CLIENTE_ID'];

			$res = Db::getInstance(_PS_USE_SQL_SLAVE_)
						->getRow('SELECT * FROM '._DB_PREFIX_.'egoi WHERE client_id='.(int)$client);

			$values = array(
				'list_id' => (int)$list, 
				'client_id' => (int)$client,
				'sync' => (int)$sync,
				'track' => (int)$track,
				'role' => pSQL($role),
				'optin' => (int)$optin,
				'estado' => 1,
                'total' => 0
			);

			if(isset($res['client_id']) && ($res['client_id'])) {
				$this->assign('success_message', $this->displaySuccess($this->l('Settings updated')));
				return Db::getInstance()->update('egoi', $values, "client_id = ".(int)$client);
			}else{
				$this->assign('success_message', $this->displaySuccess($this->l('Settings saved')));
				return Db::getInstance()->insert('egoi', $values);
			}

		}else{
			return false;
		}
	}

	/**
	 * Assign customers roles
	 * 
	 * @return void
	 */
	protected function retrieveRoles()
	{
		$this->assign('roles', Group::getGroups(Context::getContext()->language->id, true));
	}

    /**
     * Get role from Customer ID
     *
     * @param $id
     * @param $role
     * @return bool
     */
	protected function getRole($id, $role)
    {
        if ($role) {
            $exists = Db::getInstance()
                ->getValue("SELECT COUNT(*) FROM "._DB_PREFIX_."customer_group WHERE id_customer='".$id."' and id_group='$role'");
            if (!$exists) {
                return false;
            }
        }
        return true;
    }

	/**
	 * Map fields
	 * 
	 * @return void
	 */
	protected function mapFieldsEgoi() 
	{
		if (!empty($_POST)) {
			$id = isset($_POST["id_egoi"]) ? (int)$_POST["id_egoi"] : '';
			$token = isset($_POST["token_egoi_api"]) ? (int)$_POST["token_egoi_api"] : '';
			$ps = isset($_POST["ps"]) ? pSQL($_POST["ps"]) : '';
			$egoi = isset($_POST["egoi"]) ? pSQL($_POST["egoi"]) : '';

			if(($token) && ($ps) && ($egoi)){

				$ps_name = pSQL($_POST["ps_name"]);
				$egoi_name = pSQL($_POST["egoi_name"]);
				$status = 1;

				$sql_exists = "SELECT id FROM "._DB_PREFIX_."egoi_map_fields WHERE ps='".$ps."' OR egoi='".$egoi."'";
				$exists = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql_exists);
				$field_exist = isset($exists[0]) ? $exists[0] : false;

				if (!$field_exist){

					// insert new mapped field
					Db::getInstance()->insert('egoi_map_fields', array(
						'ps' => $ps,
						'ps_name' => $ps_name,
						'egoi' => $egoi,
						'egoi_name' => $egoi_name,
						'status' => (int)$status
					));

		         	Db::getInstance(_PS_USE_SQL_SLAVE_)
		         				->getRow("SELECT * FROM "._DB_PREFIX_."egoi_map_fields order by id DESC");

	         		$this->assign('post_id', Db::getInstance()->Insert_ID());
	         		$this->assign('ps_name', $ps_name);
	         		$this->assign('egoi_name', $egoi_name);

	         		echo $this->context->smarty->display($this->_path.'/views/templates/admin/fields.tpl');
		        }

				exit;

			}else if($id) {
				Db::getInstance()->delete('egoi_map_fields', 'id = '.(int)$id);
				exit;
			}
		}
	}

	/**
	 * Get Subscribers count
	 * 
	 * @param $listID
	 * @return int        
	 */
	protected function getEgoiSubscribers($listID)
	{
		$count = 0;
		$result = $this->api->getLists();

		foreach ($result as $key => $value) {
			if($value['listnum'] == $listID) {
				$count = $value['subs_activos'];
			}
		}

	    return $count;
	}

	/**
	 * Join Subscribers count from DB and API
	 * 
	 * @return void
	 */
	protected function syncronizeEgoi()
	{
		if(!empty($_POST)) {
			if(isset($_POST['action']) && ($_POST['action'] == 'synchronize')) {

				if (isset($_POST['list']) && ($_POST['list'])) {
					$total = array();
					$exec = Db::getInstance(_PS_USE_SQL_SLAVE_)
								->getRow('SELECT COUNT(*) AS CT FROM '._DB_PREFIX_.'customer WHERE active="1"');

					$total[] = $this->getEgoiSubscribers($_POST['list']);
					$total[] = $exec['CT'];

					echo json_encode($total);
					exit;
				}
			}
		}
	}

    /**
     * @param bool $name
     * @param bool $field
     * @return string
     */
	protected function getFieldMap($name = false, $field = false)
    {
        require_once dirname(__FILE__) . '/../../smartmarketingps.php';
        return (new SmartMarketingPs)->getFieldMap($name, $field);
    }

    /**
     * @return array
     */
	protected function getMappedFields()
    {
        require_once dirname(__FILE__) . '/../../smartmarketingps.php';
        return (new SmartMarketingPs)->getMappedFields();
    }

	/**
	 * Syncronize all subscribers to list
	 *
	 * @return bool
	 */
	protected function sincronizeList()
	{
		if(!empty($_POST)) {
			if(isset($_POST["token_list"]) && ($_POST["token_list"])) {

		        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)
		        			->getRow('SELECT list_id, client_id, sync, role FROM '._DB_PREFIX_.'egoi WHERE client_id != "" and sync="1" order by egoi_id DESC');
		        $sync = $res['sync'];
		        $client_id = $res['client_id'];
		        $list_id = $res['list_id'];
                $role = $res['role'];

		        if($sync) {

		            $sql = 'SELECT id, email, firstname, lastname, birthday, newsletter, optin FROM '._DB_PREFIX_.'customer WHERE active="1"';
		            $customers = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

		            $parent_subs = array();
		            $subscribers = array();

		            if (!empty($customers)) {
			            foreach($customers as $customer) {

                            if (!$this->getRole((int)$customer['id'], $role)) {
                                return false;
                            }

			                $subscribers['email'] = $customer['email'];
			                foreach ($customer as $key => $value) {
			                    $row_new_value = $this->getFieldMap(0, $key);

			                    if($row_new_value){
			                        $subscribers[$row_new_value] = $value;
			                    }
			                }

			                // check if this fields are not mapped
			                if (count($subscribers) == 1) {
                                $subscribers['first_name'] = $customer['firstname'];
                                $subscribers['last_name'] = $customer['lastname'];
                                $subscribers['birth_date'] = $customer['birthday'];
                            }

                            $parent_subs[] = $subscribers;
			            }

			            // add subscribers to E-goi
			            $this->api->addSubscriberBulk($list_id, $parent_subs);

			            // update in DB
			            echo Db::getInstance()->update('egoi',
			            	array(
			                	'total' => (int)count($customers) + count($parent_subs)
			            	), "client_id = $client_id");
                        exit;
			        }
		        }
			}
		}
	}
	
}