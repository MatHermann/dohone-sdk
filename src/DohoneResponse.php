<?php
/**
 * Created by PhpStorm.
 * User: mathermann
 * Date: 08/01/2020
 * Time: 12:10
 */

namespace Mathermann\DohoneSDK;


use JsonSerializable;
use Serializable;

class DohoneResponse implements Serializable, JsonSerializable
{
    /**
     * @var boolean
     */
    private $success;
    /**
     * @var string
     */
    private $status;
    /**
     * @var string
     */
    private $cmd;
    /**
     * @var string
     */
    private $message;
    /**
     * @var boolean
     */
    private $needCFRMSMS;
    /**
     * @var string
     */
    private $REF;
    /**
     * @var string
     */
    private $fullResponse;

    /**
     * @param array $responseData
     */
    public function __construct($responseData = [])
    {
        foreach (['success', 'status', 'message', 'REF', 'fullResponse', 'cmd', 'needCFRMSMS'] as $key)
            if (key_exists($key, $responseData))
                $this->{$key} = $responseData[$key];
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @param bool $success
     * @return DohoneResponse
     */
    public function setSuccess($success)
    {
        $this->success = $success;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return DohoneResponse
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getCmd()
    {
        return $this->cmd;
    }

    /**
     * @param string $cmd
     * @return DohoneResponse
     */
    public function setCmd($cmd)
    {
        $this->cmd = $cmd;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return DohoneResponse
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return bool
     */
    public function needCFRMSMS()
    {
        return $this->needCFRMSMS;
    }

    /**
     * @param bool $needCFRMSMS
     * @return DohoneResponse
     */
    public function setNeedCFRMSMS($needCFRMSMS)
    {
        $this->needCFRMSMS = $needCFRMSMS;
        return $this;
    }

    /**
     * @return string
     */
    public function getREF()
    {
        return $this->REF;
    }

    /**
     * @param string $REF
     * @return DohoneResponse
     */
    public function setREF($REF)
    {
        $this->REF = $REF;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullResponse()
    {
        return $this->fullResponse;
    }

    /**
     * @param string $fullResponse
     * @return DohoneResponse
     */
    public function setFullResponse($fullResponse)
    {
        $this->fullResponse = $fullResponse;
        return $this;
    }

    /**
     * String representation of object
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize($this->jsonSerialize());
    }

    /**
     * Constructs the object
     * @param string $serialized
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->__construct(unserialize($serialized));
    }

    /**
     * Specify data which should be serialized to JSON
     * @return mixed data which can be serialized by <b>json_encode</b>
     */
    public function jsonSerialize()
    {
        return [
            'success' => $this->isSuccess(),
            'status' => $this->getStatus(),
            'cmd' => $this->getCmd(),
            'message' => $this->getMessage(),
            'needCFRMSMS' => $this->needCFRMSMS(),
            'REF' => $this->getREF(),
            'fullResponse' => $this->getFullResponse()
        ];
    }
}