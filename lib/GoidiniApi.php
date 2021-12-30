<?php

/**
 * @package lib/GoidiniApi
 */
class GoidiniApi extends EgoiRestApi
{

    /**
     * @var string API_URL
     */
    const API_URL = 'https://goidini.e-goi.com';

    /**
     * Enables client transactional
     */
    public function getLatestPluginVersions()
    {
        return $this->call('GET', '/plugin-versions/current.json');
    }

    /**
     * Calls transactional api
     *
     * @param $method
     * @param $url
     * @param array $data
     *
     * @return bool|string
     */
    protected function call($method, $url, $data = array())
    {
        $curl = curl_init();
        $url = self::API_URL . $url;
        $data = array_merge($data, array('apikey' => $this->apiKey));
        $headers = array();

        switch ($method) {
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                array_push($headers, 'Content-Type: application/json');
                break;
            default:
                $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }
}
