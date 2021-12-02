<?php

/**
 * @package lib/TransactionalApi
 */
class TransactionalApi extends EgoiRestApi
{

    const MAX_MESSAGES = 10;

    /**
     * @var string API_URL
     */
    const API_URL = 'https://www51.e-goi.com/api/public';

    /**
     * Enables client transactional
     */
    public function enableClient()
    {
        $this->call('POST', '/client');
    }

    /**
     * Gets all sms senders
     *
     * @return array
     */
    public function getSmsSenders()
    {
        $senders = json_decode($this->call('POST', '/sms/senders'), true);
        $newSenders = [];

        foreach ($senders as $sender) {
            if ($sender['deleted'] === false) {
                $newSenders[] = $sender;
            }
        }

        return $newSenders;
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
    public function sendSms($mobile, $senderHash, $message, $senderIdSet = false)
    {
        if($senderIdSet){
            $data = array(
                'mobile' => $mobile,
                'senderId' => $senderHash,
                'message' => $message,
                'options' => array(
                    'gsm0338' => false,
                    'maxCount' => self::MAX_MESSAGES
                )
            );
        }else{
            $data = array(
                'mobile' => $mobile,
                'senderHash' => $senderHash,
                'message' => $message,
                'options' => array(
                    'gsm0338' => false,
                    'maxCount' => self::MAX_MESSAGES
                )
            );
        }

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
