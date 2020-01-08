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
    protected $dohoneAppName;

    /**
     * @param string $dohoneMerchantKey (optional)
     * @param string $dohoneAppName (optional)
     * @param string $notifyUrl (optional)
     */
    public function __construct($dohoneMerchantKey = '', $dohoneAppName = '', $notifyUrl = null)
    {
        parent::__construct($dohoneMerchantKey, $notifyUrl);

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
     */
    protected function parseDohoneResponse($res)
    {
        $words = explode(' ', trim($res));
        $status = $words[0]; // First word
        $message = substr($res, strpos($res, ':') + 2);
        $refIndex = strpos($message, 'REF');

        return new DohoneResponse([
            'success' => $status === 'OK',
            'status' => $status,
            'cmd' => count($words) > 1 ? $words[1] : null, // Second word or null
            'message' => $message,
            'needCFRMSMS' => strpos($message, 'SMS') !== false,
            'REF' => $refIndex !== false ? substr($message, $refIndex + 5) : null,
            'fullResponse' => $res
        ]);
    }

    /**
     * @param TransactionInterface $transaction
     * @param array $params
     * @return DohoneResponse
     */
    public function quote($transaction, $params = ['mode' => 0])
    {
        return $this->request('cotation', [
            'rH' => $this->getDohoneMerchantKey(),
            'rMo' => $this->OPERATORS[$transaction->getTransactionOperator()],
            'rMt' => $transaction->getTransactionAmount(),
            'rDvs' => $transaction->getTransactionCurrency(),
            'levelFeeds' => $params['mode']
        ]);
    }

    /**
     * @param TransactionInterface $transaction
     * @param array $params
     * @return DohoneResponse
     */
    public function start($transaction, $params = ['OTP' => null])
    {
        return $this->request('start', [
            'rN' => $transaction->getCustomerName(),
            'rT' => $transaction->getCustomerPhoneNumber(),
            'rE' => $transaction->getCustomerEmail(),
            'rH' => $this->getDohoneMerchantKey(),
            'rI' => $transaction->getTransactionRef(),
            'rMo' => $this->OPERATORS[$transaction->getTransactionOperator()],
            'rOTP' => $params['OTP'],
            'rMt' => $transaction->getTransactionAmount(),
            'rDvs' => $transaction->getTransactionCurrency(),
            'source' => $this->getDohoneAppName(),
            'notifyPage' => $this->getNotifyUrl(),
            'motif' => $transaction->getTransactionReason()
        ]);
    }

    /**
     * @param TransactionInterface $transaction
     * @param array $params
     * @return DohoneResponse
     */
    public function confirmSMS($transaction, $params)
    {
        return $this->request('cfrmsms', [
            'rCS' => $params['code'],
            'rT' => $transaction->getCustomerPhoneNumber()
        ]);
    }

    /**
     * @param TransactionInterface $transaction
     * @return DohoneResponse
     */
    public function verify($transaction)
    {
        return $this->request('verify', [
            'rI' => $transaction->getTransactionRef(),
            'rMt' => $transaction->getTransactionAmount(),
            'rDvs' => $transaction->getTransactionCurrency(),
            'idReqDoh' => $transaction->getOperatorTransactionRef()
        ]);
    }
}