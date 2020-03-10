<?php
/**
 * Created by PhpStorm.
 * Date: 14.11.18
 * Time: 15:09
 */

namespace AppBundle\Helper;


use pimax\FbBotApp;
use pimax\Messages\Message;
use pimax\Messages\SenderAction;

class MyFbBotApp extends FbBotApp
{
    /**
     * FB Messenger API Url
     *
     * @var string
     */
    protected $apiUrl = 'https://graph.facebook.com/v3.3/';

    /**
     * @param Message|SenderAction $message
     * @param null $user_ref
     * @return array
     */
    public function send($message, $user_ref = null)
    {
        $data = $message->getData();
        if(!is_null($user_ref)){
            if(isset($data['recipient']['id']) && !empty($data['recipient']['id'])){
                $data['recipient']['user_ref'] = $data['recipient']['id'];
                unset($data['recipient']['id']);
            }
        }

        return $this->call('me/messages', $data);
    }

    /**
     * Request to API
     *
     * @access public
     * @param string $url
     * @param array  $data
     * @param string $type Type of request (GET|POST|DELETE)
     * @return array
     */
    public function call($url, $data, $type = self::TYPE_POST)
    {
        $data['access_token'] = $this->token;

        $headers = [
            'Content-Type: application/json',
        ];

        if ($type == self::TYPE_GET) {
            $url .= '?'.http_build_query($data);
        }

        $process = curl_init($this->apiUrl.$url);
        curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($process, CURLOPT_HEADER, false);
        curl_setopt($process, CURLOPT_TIMEOUT, 60);

        if($type == self::TYPE_POST || $type == self::TYPE_DELETE) {
            curl_setopt($process, CURLOPT_POST, 1);
            curl_setopt($process, CURLOPT_POSTFIELDS, json_encode($data));
        }

        if ($type == self::TYPE_DELETE) {
            curl_setopt($process, CURLOPT_CUSTOMREQUEST, "DELETE");
        }

        curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
        $return = curl_exec($process);

        /**
         * Check for cURL Errors and, if found display the error code
         *
         * @see http://php.net/manual/en/function.curl-error.php
         */
        $curl_error = curl_error($process);
        if ($curl_error) {
            $this->curl_error = $curl_error;
        }

        curl_close($process);

        return json_decode($return, true);
    }

    /**
     * Request to API
     *
     * @access public
     * @param string $url
     * @param array  $data
     * @param string $type Type of request (GET|POST|DELETE)
     * @return array
     */
    public function callWithUpperLimit($url, $data, $type = self::TYPE_POST)
    {
        $data['access_token'] = $this->token;

        $headers = [
            'Content-Type: application/json',
        ];

        if ($type == self::TYPE_GET) {
            $url .= '?'.http_build_query($data);
        }

        $process = curl_init($this->apiUrl.$url);
        curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($process, CURLOPT_HEADER, false);
        curl_setopt($process, CURLOPT_TIMEOUT, 300);

        if($type == self::TYPE_POST || $type == self::TYPE_DELETE) {
            curl_setopt($process, CURLOPT_POST, 1);
            curl_setopt($process, CURLOPT_POSTFIELDS, json_encode($data));
        }

        if ($type == self::TYPE_DELETE) {
            curl_setopt($process, CURLOPT_CUSTOMREQUEST, "DELETE");
        }

        curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
        $return = curl_exec($process);

        /**
         * Check for cURL Errors and, if found display the error code
         *
         * @see http://php.net/manual/en/function.curl-error.php
         */
        $curl_error = curl_error($process);
        if ($curl_error) {
            $this->curl_error = $curl_error;
        }

        curl_close($process);

        return json_decode($return, true);
    }

    /**
     * @param $url
     * @param $data
     * @return mixed
     */
    public function callFile($url, $data)
    {
        $data['access_token'] = $this->token;

        $process = curl_init($this->apiUrl.$url);
        curl_setopt($process, CURLOPT_TIMEOUT, 60);
        curl_setopt($process, CURLOPT_POST, 1);
        curl_setopt($process, CURLOPT_POSTFIELDS, $data);
        $return = curl_exec($process);

        $curl_error = curl_error($process);
        if ($curl_error) {
            $this->curl_error = $curl_error;
        }

        curl_close($process);

        return json_decode($return, true);
    }
}
