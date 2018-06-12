<?php

/**
 * @package lib/SmartApi
 */
class SmartApi
{

	 /**
     * @var string
     */
    public $apiKey;

    /**
     * @var string
     */
    protected $plugin_key = 'e4bf5263e34400a6eda8a8d612e9b44b';

    /**
     * @var string
     */
    protected $api_url = 'https://api.e-goi.com/v2/soap.php?wsdl';

    /**
     * @var object
     */
    private $client;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->client = new SoapClient($this->api_url);
    }

    /**
     * Get API Key
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey ? $this->apiKey : Configuration::get('smart_api_key');
    }

    /**
     * Default parameters
     * 
     * @return array
     */
    private function getSoapParams()
    {
        return array(
            'apikey'     => $this->getApiKey(),
            'plugin_key' => self::PLUGIN_KEY,
        );
    }

    /**
     * Get User Data
     * 
     * @return array
     */
    public function getUserData()
    {
        return $this->client->getUserData($this->getSoapParams());
    }

    /**
     * Get Client Data
     * 
     * @return array
     */
    public function getClientData()
    {
        return $this->client->getClientData($this->getSoapParams());
    }
    
    /**
     * Create List
     * 
     * @param  string $name
     * @param  string $lang
     * @return string|int   
     */
    public function createList($name, $lang)
    {
        $params = array(
            'nome' => $name,
            'idioma_lista' => $lang,
        );

        $result = $this->_client->createList(array_merge($this->getSoapParams(), $params));
        if (isset($result['ERROR'])) {
            return $result['ERROR'];
        } else {
            $id = $result['LIST_ID'];
            return $id;
        }
    }

    /**
     * Get Lists
     * 
     * @return array
     */
    public function getLists()
    {

        return $this->_client->getLists($this->getSoapParams());
    }

    /**
     * Get Forms from List
     * 
     * @return array
     */
    public function getForms($listId = '', $option = false)
    {

        $forms = $this->_client->getForms(array_merge($this->getSoapParams(), array('listID' => $listId)));

        if ($option) {
            $array = array();
            foreach ($forms as $key => $value) {
                $array['url']  = $value['url'];
                $array['hash'] = $value['hash'];
            }
            return $array;

        }
        return $forms;
    }

    /**
     * Get All Subscribers from List
     * 
     * @return array
     */
    public function getSubscribersFromListId($listId, $option = '')
    {

        $subs = $this->_client->subscriberData(array_merge($this->getSoapParams(), array('listID' => $listId, 'subscriber' => 'all_subscribers')));
        if ($option) {
            $array_subs = array();
            foreach ($subs['subscriber'] as $key => $subscriber) {
                if ($subscriber['STATUS'] != '2') {
                    $array_subs[] = $subscriber;
                }
            }
            $total_subs = count($array_subs);
            return $total_subs;
        } else {
            return $subs;
        }
    }

    /**
     * Get specific Subscriber from List
     * 
     * @param  string $listId
     * @param  string $email 
     * @return array      
     */
    public function getSubscriber($listId, $email)
    {
        return $this->_client->subscriberData(array_merge($this->getSoapParams(), array('listID' => $listId, 'subscriber' => $email)));
    }

    /**
     * Get All subscribers from List
     * 
     * @param  string|int $listId
     * @return array        
     */
    public function getAllSubscribers($listId)
    {
        $subs  = $this->_client->subscriberData(array_merge($this->getSoapParams(), array('listID' => $listId, 'subscriber' => 'all_subscribers')));
        $count = 0;
        foreach ($subs['subscriber'] as $key => $subscriber) {
            if ($subscriber['STATUS'] != '2') {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Add Subscriber
     * 
     * @param CustomerCore $customer   
     * @param string|int       $id_list    
     * @param string       $formID     
     * @param mixed       $extraFields
     */
    public function addSubscriber(CustomerCore $customer, $id_list, $formID = '', $extraFields)
    {
        $params = array(
            'listID'         => $id_list,
            'subscriber'     => $customer->id,
            'status'         => '1',
            'from'           => '',
            'lang'           => Language::getIsoById($customer->id_lang),
            'email'          => $customer->email,
            'validate_email' => '0',
            'cellphone'      => '',
            'telephone'      => '',
            'fax'            => '',
            'formID'         => $formID ? $formID : '',
            'first_name'     => $customer->firstname,
            'last_name'      => $customer->lastname,
            'birth_date'     => ($customer->birthday) ? date('Y-m-d', strtotime($customer->birthday)) : '',
        );

        if ($extraFields and count($extraFields) > 0) {
            $params = array_merge($params, $extraFields);
        }

        $result = $this->_client->addSubscriber(array_merge($this->getSoapParams(), $params));

        if (isset($result['ERROR'])) {
            return false;
        } else {
            $id = $result['UID'];
            return $id;
        }
    }

    /**
     * Add Subscriber in Bulk
     * 
     * @param string|int  $id_list    
     * @param array   $subscribers
     * @param bool $tag        
     */
    public function addSubscriberBulk($id_list, $subscribers = array(), $tag = false)
    {
        $params = array(
            'listID'       => $id_list,
            'compareField' => 'email',
            'operation'    => 2,
            'subscribers'  => $subscribers,
            'tags'         => array($tag),
        );

        $result = $this->_client->addSubscriberBulk(array_merge($this->getSoapParams(), $params));
        return true;
    }

    /**
     * Add Subscriber
     * 
     * @TODO check if this really necessary
     * 
     * @param $list
     * @param $email 
     * @param string $fname 
     * @param string $lname 
     * @param string $number
     * @param string $formID
     * @param string $tag   
     * @param array  $fields
     */
    public function addSubscriberForm($list, $email, $fname = '', $lname = '', $number = '', $formID = '', $tag = '', $fields = array())
    {
        if ($number) {
            if (substr($number, 0, 1) != '9') {
                $tel = $number;
            } else {
                $phone = $number;
            }
        }

        $params = array(
            'listID'         => $list,
            'subscriber'     => $email,
            'email'          => $email,
            'validate_email' => '0',
            'cellphone'      => $phone ? $phone : '',
            'telephone'      => $tel ? $tel : '',
            'first_name'     => $fname ? $fname : '',
            'last_name'      => $lname ? $lname : '',
            'formID'         => $formID ? $formID : '',
            'tags'           => array($tag ? $tag : ''),
            'status'         => 1,
        );

        if (!empty($fields)) {
            foreach ($fields as $key => $value) {
                if ($key == 'birth_date') {
                    $value = date('Y-m-d', strtotime($value));
                }
                $params[$key] = $value;
            }
        }

        return $this->_client->addSubscriber(array_merge($this->getSoapParams(), $params));
    }

    /**
     * Edit subscriber
     * 
     * @param  $list 
     * @param  $email
     * @param  string $fname
     * @param  string $lname
     * @param  array  $fields
     * @return array
     */
    public function editSubscriber($list, $email, $fname = '', $lname = '', $fields = array())
    {
        $params = array(
            'listID'     => $list,
            'subscriber' => $email,
            'email'      => $email,
            'first_name' => $fname,
            'last_name'  => $lname,
        );

        if (!empty($fields)) {
            foreach ($fields as $key => $value) {
                if ($key == 'birth_date') {
                    $value = date('Y-m-d', strtotime($value));
                }
                $params[$key] = $value;
            }
        }

        return $this->_client->editSubscriber(array_merge($this->getSoapParams(), $params));
    }

    /**
     * Delete subscriber
     * 
     * @param $list
     * @param $email
     * @return array
     */
    public function removeSubscriber($list, $email)
    {
        $params = array(
            'listID'     => $list,
            'subscriber' => $email,
        );

        return $this->_client->removeSubscriber(array_merge($this->getSoapParams(), $params));
    }

    /**
     * Create extra fields
     * 
     * @param $id_list
     * @param $name
     * @param $type
     * @return mixed
     */
    public function createExtraField($id_list, $name, $type)
    {
        $params = array(
            'listID' => $id_list,
            'name'   => $name,
            'type'   => $type,
        );

        $result = $this->_client->addExtraField(array_merge($this->getSoapParams(), $params));

        if (isset($result['ERROR'])) {
            return false;
        } else {
            $id = $result['NEW_ID'];
            return $id;
        }
    }

    /**
     * Ger all mapped fields
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
     * @return array
     */
    public function getFieldMap($name = false, $field = false)
    {
        if ($field) {
            $sql = "SELECT * FROM " . _DB_PREFIX_ . "egoi_map_fields WHERE ps='" . pSQL($field) . "'";
        } else {
            $sql = "SELECT * FROM " . _DB_PREFIX_ . "egoi_map_fields WHERE egoi='" . pSQL($name) . "'";
        }
        $rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $rq[0]['egoi'];
    }

    /**
     * Get extra fields
     * 
     * @param $listID
     * @return array
     */
    public function getExtraFields($listID)
    {
        $params = array(
            'listID' => $listID,
        );

        $result_client = $this->_client->getExtraFields(array_merge($this->getSoapParams(), $params));
        return $result_client['extra_fields'];
    }

    /**
     * Add a tag
     * 
     * @param $name
     * @return array
     */
    public function addTag($name)
    {
        $params = array(
            'name' => $name,
        );
        return $this->_client->addTag(array_merge($this->getSoapParams(), $params));
    }

    /**
     * Get Tags
     * 
     * @return array
     */
    public function getTags()
    {
        $result = $this->_client->getTags(array_merge($this->getSoapParams()));
        return $result['TAG_LIST'];
    }

}
