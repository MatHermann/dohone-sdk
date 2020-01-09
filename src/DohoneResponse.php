<?php
/**
 * Created by PhpStorm.
 * User: mathermann
 * Date: 08/01/2020
 * Time: 12:10
 */

namespace Mathermann\DohoneSDK;


use ArrayObject;
use JsonSerializable;

class DohoneResponse extends ArrayObject implements JsonSerializable
{
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
     * @var array const
     */
    public static $PROPERTIES = ['status', 'message', 'REF', 'fullResponse', 'cmd', 'needCFRMSMS'];
    /**
     * @var array static const
     */
    public static $STATUSES = ['OK', 'KO', 'NO'];

    /**
     * @param array $responseData
     */
    public function __construct($responseData = [])
    {
        foreach (self::$PROPERTIES as $key)
            if (key_exists($key, $responseData))
                $this->{$key} = $responseData[$key];
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->status === 'OK';
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
     * @return string
     */
    public function hasREF()
    {
        return !in_array($this->getREF(), [null, '']);
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
            'hasREF' => $this->hasREF(),
            'REF' => $this->getREF(),
            'fullResponse' => $this->getFullResponse()
        ];
    }

    /**
     * @param mixed $index
     * @return bool true if the requested index exists, otherwise false
     */
    public function offsetExists($index)
    {
        return in_array($index, $this->PROPERTIES);
    }

    /**
     * @param mixed $index
     * @return mixed The value at the specified index or false.
     */
    public function offsetGet($index)
    {
        if ($this->offsetExists($index))
            return $this->{$index};

        if ($index === 'success')
            return $this->isSuccess();

        if ($index === 'hasREF')
            return $this->hasREF();

        return false;
    }

    /**
     * @param mixed $index
     * @param mixed $value
     * @return void
     */
    public function offsetSet($index, $value)
    {
        if ($this->offsetExists($index))
            $this->{$index} = $value;
    }
}