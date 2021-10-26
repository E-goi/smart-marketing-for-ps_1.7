<?php

/**
 * @package lib/SmartApi
 */
class SmartApi
{

    const GET_TAGS_TTL = 'egoi_tags_ttl';
    const GET_TAGS_CONTENT = 'egoi_tags_content';
    const DEFAULT_TTL = 300;//5 min

	 /**
     * @var string
     */
    public $apiKey;

    /**
     * @var string
     */
    protected $api_url = 'https://api.e-goi.com/v2/soap.php?wsdl';

    /**
     * @var object
     */
    private $client;

    /**
     * SmartApi constructor.
     *
     * @param bool $apikey
     */
    public function __construct($apikey = false)
    {
        try{
            $this->client = new SoapClient($this->api_url);
        }catch (\Exception $e){
            if(!class_exists('EgoiSoapFailSafe')){
                require_once dirname(__FILE__) . '/EgoiSoapFailSafe.php';
            }
            $this->client = new EgoiSoapFailSafe();
        }

        if ($apikey) {
            $this->apiKey = $apikey;
        }else{
            $this->apiKey = $this->apiKey ?: Configuration::get('smart_api_key');
        }
    }

    /**
     * Default parameters
     * 
     * @return array
     */
    private function getBaseParams()
    {
        return array(
            'apikey' => $this->apiKey,
            'plugin_key' => SmartMarketingPs::PLUGIN_KEY,
            'status' => $this->getDoubleOptin()
        );
    }

    private function getDoubleOptin(){
        $a = Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->getValue("SELECT optin FROM "._DB_PREFIX_."egoi");
        return empty($a)?1:0;
    }

    /**
     * Get User Data
     * 
     * @return array
     */
    public function getUserData()
    {
        return $this->client->getUserData($this->getBaseParams());
    }

    /**
     * Get Client Data
     * 
     * @return array
     */
    public function getClientData()
    {
        return $this->client->getClientData($this->getBaseParams());
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
        $result = $this->client->createList(
            array_merge($this->getBaseParams(), 
                array(
                    'nome' => $name,
                    'idioma_lista' => $lang,
                )
            )
        );

        if (isset($result['ERROR'])) {
            return $result['ERROR'];
        }
        return $result['LIST_ID'];
    }

    /**
     * Get Lists
     * 
     * @return array
     */
    public function getLists()
    {
        return $this->client->getLists($this->getBaseParams());
    }

    /**
     * Get Forms from List
     * 
     * @param $listId
     * @param $option
     * @return array
     */
    public function getForms($listId, $option = false)
    {
        $forms = $this->client->getForms(
            array_merge($this->getBaseParams(), 
                array(
                    'listID' => $listId
                )
            )
        );

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
     * @param $listId
     * @param $option
     * @return mixed
     */
    public function getSubscribersFromListId($listId, $option = '')
    {
        $subs = $this->client->subscriberData(
            array_merge($this->getBaseParams(), 
                array(
                    'listID' => $listId, 
                    'subscriber' => 'all_subscribers'
                )
            )
        );

        if ($option) {
            $array_subs = array();
            foreach ($subs['subscriber'] as $key => $subscriber) {
                if ($subscriber['STATUS'] != '2') {
                    $array_subs[] = $subscriber;
                }
            }
            $total_subs = count($array_subs);
            return $total_subs;
        }
        return $subs;
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
        return $this->client->subscriberData(
            array_merge($this->getBaseParams(), 
                array(
                    'listID' => $listId, 
                    'subscriber' => $email
                )
            )
        );
    }

    /**
     * Get All subscribers from List
     * 
     * @param  string|int $listId
     * @return int
     */
    public function getAllSubscribers($listId)
    {
        $subs  = $this->client->subscriberData(array_merge($this->getBaseParams(), array('listID' => $listId, 'subscriber' => 'all_subscribers')));
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
     * @param array $params     
     * @param $tags
     * @return mixed
     */
    public function addSubscriber($params, $tags = array())
    {
        $result = $this->client->addSubscriber(
            array_merge($this->getBaseParams(), $params, !empty($tags) ? $tags : array())
        );

        if (isset($result['ERROR']) && ($result['ERROR'])) {
            return false;
        }
        return $result['UID'];
    }

    /**
     * Add Subscriber in Bulk
     * 
     * @param string|int  $id_list    
     * @param array  $subscribers
     * @param bool $tag
     * @return array|null 
     */
    public function addSubscriberBulk($id_list, $subscribers = array(), $tag = false)
    {
        $params = array(
            'listID'       => $id_list,
            'compareField' => 'email',
            'operation'    => 2,
            'notification'  => 0,
            'subscribers'  => $subscribers,
            'tags'         => $tag,

        );

        return $this->client->addSubscriberBulk(
            array_merge($this->getBaseParams(), $params)
        );
    }

    /**
     * Edit subscriber
     * 
     * @param array $fields
     * @param array $tags
     * @return array
     */
    public function editSubscriber($fields, $tags = array())
    {
        return $this->client->editSubscriber(
            array_merge($this->getBaseParams(), $fields, array('tags' => $tags))
        );
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
        return $this->client->removeSubscriber(
            array_merge($this->getBaseParams(), 
                array(
                    'listID'     => $list,
                    'subscriber' => $email
                )
            )
        );
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

        $result = $this->client->addExtraField(array_merge($this->getBaseParams(), $params));

        if (isset($result['ERROR'])) {
            return false;
        } else {
            $id = $result['NEW_ID'];
            return $id;
        }
    }

    /**
     * Get extra fields
     * 
     * @param $listID
     * @return array|null
     */
    public function getExtraFields($listID)
    {
        $params = array(
            'listID' => $listID
        );

        $resultclient = $this->client->getExtraFields(
            array_merge($this->getBaseParams(), $params)
        );

        if (!empty($resultclient['extra_fields'])) {
            return $resultclient['extra_fields'];
        }
        return null;
    }

    /**
     * Add a tag
     * 
     * @param $name
     * @return array
     */
    public function addTag($name)
    {
        Configuration::updateValue(self::GET_TAGS_TTL, 0);//reset tags ttl if new is added
        $params = array(
            'name' => $name
        );
        return $this->client->addTag(array_merge($this->getBaseParams(), $params));
    }

    /**
     * Get Tags
     * 
     * @return array
     */
    public function getTags()
    {
        $timeSaved = Configuration::get(self::GET_TAGS_TTL);
        if (empty($timeSaved) || $timeSaved + self::DEFAULT_TTL < time()){
            $result = $this->client->getTags(array_merge($this->getBaseParams()));
            if(!empty($result['TAG_LIST'])){
                Configuration::updateValue(self::GET_TAGS_TTL, time());
                Configuration::updateValue(self::GET_TAGS_CONTENT, json_encode($result['TAG_LIST']));
            }
            return $result['TAG_LIST'];
        }else{
            return json_decode(Configuration::get(self::GET_TAGS_CONTENT), true);
        }
    }

    /**
     * Get tag ID then if not exists create it 
     * 
     * @param string $tag_name
     * @return int|string
     */
    public function processNewTag($tag_name)
    {
        $tags = $this->getTags();

        $tag_id = 0;
        if (!empty($tags)) {
            foreach ($tags as $tag) {
                if (strtolower($tag['NAME']) == strtolower($tag_name)) {
                    $tag_id = $tag['ID'];
                }
            }
        }

        if (!$tag_id) {
            $tag = $this->addTag($tag_name);
            $tag_id = $tag['ID'];
        }
        return $tag_id;
    }

}
