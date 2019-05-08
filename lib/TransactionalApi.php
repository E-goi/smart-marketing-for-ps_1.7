<?php

/**
 * @package lib/TransactionalApi
 */
class TransactionalApi
{

    const MAX_MESSAGES = 10;

    /**
     * @var string $apiKey
     */
    private $apiKey;

    /**
     * @var string API_URL
     */
    const API_URL = 'https://www51.e-goi.com/api/public';

    /**
     * SmartApi constructor.
     *
     * @param bool $apikey
     */
    public function __construct($apikey = false)
    {
        if ($apikey) {
            $this->apiKey = $apikey;
        } else {
            $this->apiKey = $this->apiKey ?: Configuration::get('smart_api_key');
        }
    }

    /**
     * Gets all sms senders
     *
     * @return array
     */
    public function getSmsSenders()
    {
        $senders = json_decode($this->call('POST', '/sms/senders'), true);

        foreach ($senders as $key => &$sender) {
            if ($sender['deleted'] === true) {
                array_splice($senders, $key, $key);
            }
        }

        return $senders;
    }

    /**
     * Sends transactional sms
     *
     * @param $mobile
     * @param $senderHash
     * @param $message
     *
     * @return array
     */
    public function sendSms($mobile, $senderHash, $message)
    {
        $data = array(
            'mobile' => $mobile,
            'senderHash' => $senderHash,
            'message' => $message,
            'options' => array(
                'gsm0338' => false,
                'maxCount' => self::MAX_MESSAGES
            )
        );

        return $this->call('POST', '/sms/send', $data);
    }

    /**
     * Calls transactional api
     *
     * @param $method
     * @param $url
     * @param array $data
     *
     * @return mixed
     */
    private function call($method, $url, $data = array())
    {
        $curl = curl_init();
        $url = self::API_URL . $url;
        $data = array_merge($data, array('apikey' => $this->apiKey));
        $headers = array();

        switch ($method) {
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, 1);

                if (!empty($data)) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                }

                array_push($headers, 'Content-Type: application/json');
                break;
            default:
                if (!empty($data)) {
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }
}
