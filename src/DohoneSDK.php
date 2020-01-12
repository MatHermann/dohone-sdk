<?php
/**
 * Created by PhpStorm.
 * User: mathermann
 * Date: 06/01/2020
 * Time: 17:09
 */

namespace Mathermann\DohoneSDK;


class DohoneSDK extends AbstractDohoneSDK
{
    // Attributes
    protected $merchantKey;
    protected $dohoneAppName;

    /**
     * @param string $merchantKey (optional)
     * @param string $dohoneAppName (optional)
     * @param string $hashCode (optional)
     * @param string $notifyUrl (optional)
     */
    public function __construct($merchantKey = '', $dohoneAppName = '', $hashCode = '', $notifyUrl = null)
    {
        parent::__construct($hashCode, $notifyUrl);

        $this->merchantKey = $merchantKey;
        $this->dohoneAppName = $dohoneAppName;
        $this->BASE_URL = 'https://www.my-dohone.com/dohone/pay';
        $this->OPERATORS = [
            'DOHONE_MOMO' => 1, // MTN Mobile Money
            'DOHONE_OM' => 2,   // Orange Money
            'DOHONE_EU' => 3,   // Express Union Mobile Money
            'DOHONE_TRANSFER' => 10 // Dohone Account Transfer
        ];
    }

    /**
     * @return string
     */
    public function getMerchantKey()
    {
        return $this->merchantKey;
    }

    /**
     * @param string $merchantKey
     * @return DohoneSDK
     */
    public function setMerchantKey($merchantKey)
    {
        $this->merchantKey = $merchantKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getDohoneAppName()
    {
        return $this->dohoneAppName;
    }

    /**
     * @param string $dohoneAppName
     * @return DohoneSDK
     */
    public function setDohoneAppName($dohoneAppName)
    {
        $this->dohoneAppName = $dohoneAppName;
        return $this;
    }

    /**
     * @param string $res
     * @return DohoneResponse
     * @throws InvalidDohoneResponseException
     */
    protected function parseDohoneResponse($res)
    {
        $words = explode(' ', $res);
        $cmd = count($words) > 1 ? $words[1] : null; // Second word
        $message = substr($res, strpos($res, ':') + 2);
        $refIndex = strpos($message, 'REF');
        $needCFRMSMS = strpos($message, 'SMS') !== false;
        $REF = $refIndex !== false ? substr($message, $refIndex + 5) : null;

        return parent::parseDohoneResponse($res)
            ->setCmd($cmd)
            ->setMessage($message)
            ->setNeedCFRMSMS($needCFRMSMS)
            ->setREF($REF);
    }

    /**
     * @param TransactionInterface $transaction
     * @param array $params
     * @return DohoneResponse
     * @throws InvalidDohoneResponseException
     */
    public function quote($transaction, $params = ['mode' => 0])
    {
        return $this->request('cotation', [
            'rH' => $this->getMerchantKey(),
            'rMo' => $this->getOperatorCodeFromSlug($transaction->getTransactionOperator()),
            'rMt' => $transaction->getTransactionAmount(),
            'rDvs' => $transaction->getTransactionCurrency(),
            'levelFeeds' => $params['mode']
        ]);
    }

    /**
     * @param TransactionInterface $transaction
     * @param array $params
     * @return DohoneResponse
     * @throws InvalidDohoneResponseException
     */
    public function start($transaction, $params = ['OTP' => null])
    {
        $notify_url = $this->getNotifyUrl();

        if ($transaction->getNotifyUrl() !== null)
            $notify_url = $transaction->getNotifyUrl();

        return $this->request('start', [
            'rN' => $transaction->getCustomerName(),
            'rT' => $transaction->getCustomerPhoneNumber(),
            'rE' => $transaction->getCustomerEmail(),
            'rH' => $this->getMerchantKey(),
            'rI' => $transaction->getTransactionRef(),
            'rMo' => $this->getOperatorCodeFromSlug($transaction->getTransactionOperator()),
            'rOTP' => $params['OTP'],
            'rMt' => $transaction->getTransactionAmount(),
            'rDvs' => $transaction->getTransactionCurrency(),
            'source' => $this->getDohoneAppName(),
            'notifyPage' => $notify_url,
            'motif' => $transaction->getTransactionReason()
        ]);
    }

    /**
     * @param TransactionInterface $transaction
     * @param array $params
     * @return DohoneResponse
     * @throws InvalidDohoneResponseException
     */
    public function confirmSMS($transaction, $params = ['code' => null])
    {
        return $this->request('cfrmsms', [
            'rCS' => $params['code'],
            'rT' => $transaction->getCustomerPhoneNumber()
        ]);
    }

    /**
     * @param TransactionInterface $transaction
     * @return DohoneResponse
     * @throws InvalidDohoneResponseException
     */
    public function verify($transaction)
    {
        return $this->request('verify', [
            'rI' => $transaction->getTransactionRef(),
            'rMt' => $transaction->getTransactionAmount(),
            'rDvs' => $transaction->getTransactionCurrency(),
            'idReqDoh' => $transaction->getDohoneTransactionRef()
        ]);
    }

    /**
     * @param array $data
     * @return array
     */
    public function mapNotificationData($data)
    {
        $map = [
            'rI' => 'transaction_ref',
            'rMt' => 'transaction_amount',
            'rDvs' => 'transaction_currency',
            'mode' => 'transaction_operator',
            'motif' => 'transaction_reason',
            'idReqDoh' => 'dohone_transaction_ref',
            'rH' => 'merchant_key',
            'hash' => 'hash',
        ];
        foreach ($map as $key => $value)
            if (key_exists($key, $data))
                $data[$value] = $data[$key];

        return $data;
    }

    /**
     * @param array $notificationData
     * @return boolean
     */
    public function checkHash($notificationData)
    {
        if ($notificationData['merchant_key'] !== $this->getMerchantKey())
            return false;

        $dohone_transaction_ref = $notificationData['dohone_transaction_ref'];
        $transaction_ref = $notificationData['transaction_ref'];
        $transaction_amount = $notificationData['transaction_amount'];
        $hash1 = md5($dohone_transaction_ref . $transaction_ref . $transaction_amount . $this->getHashCode());
        $hash2 = md5($dohone_transaction_ref . $transaction_ref . intval($transaction_amount) . $this->getHashCode());

        return in_array($notificationData['hash'], [$hash1, $hash2]);
    }
}