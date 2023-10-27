<?php

/**
 * @package lib/ApiV3
 */
abstract class EgoiRestApi
{

    /**
     * @var string $apiKey
     */
    protected $apiKey;

    /**
     * SmartApi constructor.
     *
     * @param bool $apikey
     */
    public function __construct($apikey = false)
    {
        if ($apikey) {
            $this->apiKey = $apikey;
        }else{
            $this->apiKey = $this->apiKey ?: Configuration::get('smart_api_key');
        }
    }

    /**
     * Calls Api
     *
     * @param $method
     * @param $url
     * @param array $data
     *
     * @return mixed
     */
    protected abstract function call($method, $url, $data = array());
}
