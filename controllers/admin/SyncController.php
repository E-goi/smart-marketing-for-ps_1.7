<?php

include_once dirname(__FILE__).'/../abstract/PSEgoiController.php';

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

		$this->bootstrap = true;
		$this->meta_title = $this->l('E-goi Subscribers').' - '.$this->module->displayName;
		
		if (!$this->module->active)
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
		
		$this->retrieveRoles();
		$this->mapFieldsEgoi();
		$this->syncronizeEgoi($_POST);
	}

	/**
	 * Initiate content
	 * 
	 * @return void
	 */
	public function initContent() 
	{
		parent::initContent();
		
		if($this->has_api_key) {
			
			$msg = '';
			if(!empty($_POST)){
				$msg = $this->saveSync();
			}

			$api = new SmartApi();

			$data_lists = $api->getLists();
			$this->assign('lists', $data_lists);

			$rq = Db::getInstance(_PS_USE_SQL_SLAVE_)
					->getRow('SELECT * FROM '._DB_PREFIX_.'egoi where client_id!="" order by egoi_id DESC');
			
			if(!empty($rq)){
				$list_id = $rq['list_id'];
				$sync = $rq['sync'];
				$track = $rq['track'];
				$role = $rq['role'];
			}

			if(isset($list_id) && ($list_id)){
				
				$this->assign('subscribers', $api->getSubscribersFromListId($list_id));
				$this->assign('list_id', $list_id);
				$this->assign('sync', $sync);
				$this->assign('track', $track);
				$this->assign('role_id', $role);

				//map fields
				$egoi_fields = array(
					'first_name' => 'First name',
					'last_name' => 'Last name',
					'surname' => 'Surname',
					'cellphone' => 'Mobile',
					'telephone' => 'Telephone',
					'birth_date' => 'Birth Date'
				);

				foreach($api->getExtraFields($list_id) as $key => $extra_field){
					$egoi_fields['extra_'.$key] = $extra_field['NAME'];
				}
				
				$option = '';
				foreach($egoi_fields as $key => $field){
					$option .= '<option value='.$key.'>'.$field.'</option>'.PHP_EOL;
				}

				$this->assign('select', $option);
				
				$mapped_fields = $api->getMappedFields();
				$this->assign('mapped_fields', $mapped_fields);
			}

			$this->assign('token', $_GET['token']);
			$this->assign('egoi_success_message', $msg);
		}

		$this->assign('content', $this->fetch('subs.tpl'));
	}

	/**
	 * Save syncronization data
	 * 
	 * @return mixed
	 */
	public function saveSync() 
	{
		if(isset($_POST['action_add']) && ($_POST['action_add'])) {

			$list = $_POST['list'];
			$sync = $_POST['enable'];
			$role = $_POST['role'];
			$track = $_POST['track'];
			$estado = 1;
			
			$update = '';
			$insert = '';

			if($track == ''){
				$track = 1;
			}

			$api = new SmartApi();
			$client_data = $api->getClientData();
			$client = $client_data['CLIENTE_ID'];

			$res = Db::getInstance(_PS_USE_SQL_SLAVE_)
						->getRow('SELECT * FROM '._DB_PREFIX_.'egoi WHERE client_id='.(int)$client);

			$sql_insert = array(
				'list_id' => (int)$list, 
				'client_id' => (int)$client,
				'sync' => (int)$sync,
				'track' => (int)$track,
				'role' => pSQL($role),
				'estado' => (int)$estado,
                'total' => 0
			);

			if($res['client_id']) {
				$where = "client_id = ".(int)$client;
				$update = Db::getInstance()->update('egoi', $sql_insert, $where);
			}else{
				$insert = Db::getInstance()->insert('egoi', $sql_insert);
			}

			if($insert || $update){
				$msg = 1;
				return $msg;
			}

		}else{
			return false;
		}
	}

	/**
	 * Retrieve customers roles
	 * 
	 * @return void
	 */
	public function retrieveRoles()
	{
		$this->assign('roles', Group::getGroups(Context::getContext()->language->id, true));
	}

	/**
	 * Map fields
	 * 
	 * @return void
	 */
	public function mapFieldsEgoi() 
	{
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

         		echo $this->context->smarty->display(_PS_MODULE_DIR_.$this->name.'/views/templates/admin/fields.tpl');
	        }

			exit;

		}else if($id){

			Db::getInstance()->delete('egoi_map_fields', 'id = '.(int)$id);
			exit;
		}
	}

	/**
	 * Get Subscribers count
	 * 
	 * @param $listID
	 * @return int        
	 */
	private function getEgoiSubscribers($listID)
	{
		$count = 0;
		$api = new SmartApi();
		$result = $api->getLists();

		foreach ($result as $key => $value) {
			if($value['listnum'] == $listID){
				$count = $value['subs_activos'];
			}
		}

	    return $count;
	}

	/**
	 * Join Subscribers count from DB and API
	 * 
	 * @param  array  $post
	 * @return void
	 */
	public function syncronizeEgoi($post = array())
	{
		if(!empty($post)) {
			if(isset($post['action']) && ($post['action'] == 'synchronize')) {

				$total = array();
				$exec = Db::getInstance(_PS_USE_SQL_SLAVE_)
							->getRow('SELECT COUNT(*) AS CT FROM '._DB_PREFIX_.'customer WHERE active="1"');

				$total[] = $this->getEgoiSubscribers($post['list']);
				$total[] = $exec['CT'];

				echo json_encode($total);
				exit;
			}
		}
	}
	
}