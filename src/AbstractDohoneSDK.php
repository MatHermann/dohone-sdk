<?php
/**
 * Created by PhpStorm.
 * User: mathermann
 * Date: 06/01/2020
 * Time: 17:09
 */

namespace Mathermann\DohoneSDK;


abstract class AbstractDohoneSDK
{
    // Overridable constants
    protected $BASE_URL;
    protected $OPERATORS;

    // Properties
    protected $dohoneMerchantKey;
    protected $notifyUrl;

    /**
     * @param string $dohoneMerchantKey (optional)
     * @param string $notifyUrl (optional)
     */
    public function __construct($dohoneMerchantKey = '', $notifyUrl = null)
    {
        $this->dohoneMerchantKey = $dohoneMerchantKey;
        $this->notifyUrl = $notifyUrl;
    }

    /**
     * @return string
     */
    public function getDohoneMerchantKey()
    {
        return $this->dohoneMerchantKey;
    }

    /**
     * @param string $dohoneMerchantKey
     * @return AbstractDohoneSDK
     */
    public function setDohoneMerchantKey($dohoneMerchantKey)
    {
        $this->dohoneMerchantKey = $dohoneMerchantKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getNotifyUrl()
    {
        return $this->notifyUrl;
    }

    /**
     * @param string $notifyUrl
     * @return AbstractDohoneSDK
     */
    public function setNotifyUrl($notifyUrl)
    {
        $this->notifyUrl = $notifyUrl;
        return $this;
    }

    /**
     * @param string $res
     * @return DohoneResponse
     * @throws InvalidDohoneResponseException
     */
    protected function parseDohoneResponse($res)
    {
        if ($res === '')
            throw new InvalidDohoneResponseException("Response body is either empty or contains only whitespaces");

        $status = explode(' ', $res)[0]; // First word

        if (!in_array($status, DohoneResponse::$STATUSES))
            throw new InvalidDohoneResponseException("Can't get request status from response body.");

        return new DohoneResponse([
            'status' => $status,
            'fullResponse' => $res
        ]);
    }

    /**
     * @param string $cmd
     * @param array $params
     * @return DohoneResponse
     */
    protected final function request($cmd, $params)
    {
        // create curl resource
        $ch = curl_init();

        // set url
        $url = $this->BASE_URL . '?cmd=' . $cmd;
        foreach ($params as $key => $value)
            $url .= '&' . $key . '=' . urlencode($value);
        curl_setopt($ch, CURLOPT_URL, $url);

        //fail on error
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        //follow redirects
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //disable ssl host verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        //disable ssl peer verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // $output contains the output string
        $output = curl_exec($ch);

        // close curl resource to free up system resources
        curl_close($ch);

        return $this->parseDohoneResponse(trim($output));
    }
}