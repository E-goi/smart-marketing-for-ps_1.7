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
     * Imports products to a catalog
     *
     * @param $id
     * @param $data
     *
     * @return mixed
     */
    public function importProducts($id, $data)
    {
        return $this->call('POST', '/catalogs/' . $id . '/products/actions/import', $data);
    }

    /**
     * Creates a product
     *
     * @param $catalogId
     * @param $data
     *
     * @return mixed
     */
    public function createProduct($catalogId, $data)
    {
        return $this->call('POST', '/catalogs/' . $catalogId . '/products', $data);
    }

    /**
     * Updates a product
     *
     * @param $catalogId
     * @param $id
     * @param $data
     *
     * @return mixed
     */
    public function updateProduct($catalogId, $id, $data)
    {
        return $this->call('PATCH', '/catalogs/' . $catalogId . '/products/' . $id, $data);
    }

    /**
     * Deletes a product
     *
     * @param $catalogId
     * @param $id
     * @param $data
     *
     * @return mixed
     */
    public function deleteProduct($catalogId, $id)
    {
        return $this->call('DELETE', '/catalogs/' . $catalogId . '/products/' . $id);
    }

    /**
     * Asks for a social track ID
     *
     * @return string social_track_id
     */
    public function getSocialTrackID()
    {
        $accountData = $this->getMyAccount();
        $curl = curl_init();
        $url = "https://egoiapp2.com/ads/getPixel?account_id=" . $accountData['general_info']['client_id'] . "&domain=" . _PS_BASE_URL_;

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        $headers[] = 'Content-Type: application/json';
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($result && $httpCode === 200) {
            $json = json_decode($result, true);
            return $json['data'][0]['code'];
        }

        return false;
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

        switch ($method) {
            case 'GET':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                $url = sprintf("%s?%s", $url, http_build_query($data));
                break;
            case 'POST':
            case 'PATCH':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
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
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($result && $httpCode !== 500) {
            return json_decode($result, true);
        }

        return $httpCode;
    }
}