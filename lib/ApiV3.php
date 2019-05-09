<?php

/**
 * @package lib/ApiV3
 */
class ApiV3 extends EgoiRestApi
{

    /**
     * @var string API_URL
     */
    const API_URL = 'https://api.egoiapp.com';

    /**
     * Returns user account information
     *
     * @return mixed
     */
    public function getMyAccount()
    {
        return $this->call('GET', '/my-account');
    }

    /**
     * Calls api v3
     *
     * @param $method
     * @param $url
     * @param array $data
     *
     * @return mixed
     */
    protected function call($method, $url, $data = array())
    {
        $curl = curl_init();
        $url = self::API_URL . $url;
        $headers = array(
            "Apikey: $this->apiKey",
            'Pluginkey: ' . SmartMarketingPs::PLUGIN_KEY
        );

        //TODO implement POST, PUT, PATCH and DELETE
        $url = sprintf("%s?%s", $url, http_build_query($data));

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);

        if ($result) {
            return json_decode($result, true);
        }

        return false;
    }
}