<?php

/**
 * @package lib/ApiV3
 */
class ApiV3 extends EgoiRestApi
{

    /**
     * @var string API_URL
     */
    const API_URL = 'https://dev-api.egoiapp.com';

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
     * Returns all catalogs
     *
     * @return mixed
     */
    public function getCatalogs()
    {
        return $this->call('GET', '/catalogs');
    }

    /**
     * Creates a catalog
     *
     * @param $data
     *
     * @return mixed
     */
    public function createCatalog($data)
    {
        return $this->call('POST', '/catalogs', $data);
    }

    /**
     * Deletes a catalog
     *
     * @param $id
     *
     * @return mixed
     */
    public function deleteCatalog($id)
    {
        return $this->call('DELETE', '/catalogs/' . $id);
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
            "Apikey: e67c6ab91304fcc6929cbee346959f25fda3d7ec",
            'Pluginkey: ' . SmartMarketingPs::PLUGIN_KEY
        );

        switch ($method) {
            case 'GET':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                $url = sprintf("%s?%s", $url, http_build_query($data));
                break;
            case 'POST':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                $headers[] = 'Content-Type: application/json';
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
            default:
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($result) {
            return json_decode($result, true);
        }

        return $httpcode;
    }
}