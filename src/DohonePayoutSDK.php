<?php
/**
 * Created by PhpStorm.
 * User: mathermann
 * Date: 08/01/2020
 * Time: 01:58
 */

namespace Mathermann\DohoneSDK;


class DohonePayoutSDK extends AbstractDohoneSDK
{
    // Attributes
    protected $dohoneAccount;

    /**
     * @param string $dohoneAccount (optional)
     * @param string $hashCode (optional)
     * @param string $notifyUrl (optional)
     */
    public function __construct($dohoneAccount = '', $hashCode = '', $notifyUrl = null)
    {
        parent::__construct($hashCode, $notifyUrl);

        $this->dohoneAccount = $dohoneAccount;
        $this->BASE_URL = 'https://www.my-dohone.com/dohone/transfert';
        $this->OPERATORS = [
            'DOHONE_MOMO' => 5, // MTN Mobile Money
            'DOHONE_OM' => 6,   // Orange Money
            'DOHONE_EU' => 3,   // Express Union Mobile Money
            'DOHONE_TRANSFER' => 1 // Dohone Account Transfer
        ];
    }

    /**
     * @return string
     */
    public function getDohoneAccount()
    {
        return $this->dohoneAccount;
    }

    /**
     * @param string $dohoneAccount
     * @return DohonePayoutSDK
     */
    public function setDohoneAccount($dohoneAccount)
    {
        $this->dohoneAccount = $dohoneAccount;
        return $this;
    }

    /**
     * @param string $res
     * @return DohoneResponse
     * @throws InvalidDohoneResponseException
     */
    protected function parseDohoneResponse($res)
    {
        // If response is a float number, add 'OK / ' before
        if (preg_match('/^[+-]?[0-9]*\.?[0-9]+$/', $res))
            $res = 'OK / ' . $res;

        $dohoneResponse = parent::parseDohoneResponse($res);

        $message = substr($res, strpos($res, '/') + 2);

        if ($dohoneResponse->isSuccess())
            $dohoneResponse->setREF($message);

        return $dohoneResponse->setMessage($message);
    }

    /**
     * @param TransactionInterface $transaction
     * @return DohoneResponse
     * @throws InvalidDohoneResponseException
     */
    public function quote($transaction)
    {
        return $this->request('cotation', [
            'amount' => $transaction->getTransactionAmount(),
            'devise' => $transaction->getTransactionCurrency(),
            'mode' => $this->getOperatorCodeFromSlug($transaction->getTransactionOperator())
        ]);
    }

    /**
     * @param TransactionInterface $transaction
     * @param array $params
     * @return DohoneResponse
     * @throws InvalidDohoneResponseException
     */
    public function transfer($transaction)
    {
        $account = $this->getDohoneAccount();
        $mode = $this->getOperatorCodeFromSlug($transaction->getTransactionOperator());
        $amount = $transaction->getTransactionAmount();
        $devise = $transaction->getTransactionCurrency();
        $transID = $transaction->getTransactionRef();
        $hash = md5($account . $mode . $amount . $devise . $transID . $this->getHashCode());
        $notify_url = $this->getNotifyUrl();

        if ($transaction->getNotifyUrl() !== null)
            $notify_url = $transaction->getNotifyUrl();

        return $this->request('transfert', [
            'account' => $account,
            'destination' => $transaction->getCustomerPhoneNumber(),
            'mode' => $mode,
            'amount' => $amount,
            'devise' => $devise,
            'nameDest' => $transaction->getCustomerName(),
            'ville' => $transaction->getCustomerCity(),
            'pays' => $transaction->getCustomerCountry(),
            'transID' => $transID,
            'hash' => $hash,
            'notifyUrl' => $notify_url
        ]);
    }

    /**
     * @param array $data
     * @return array
     */
    public function mapNotificationData($data)
    {
        $map = [
            'transID' => 'transaction_ref',
            'amount' => 'transaction_amount',
            'devise' => 'transaction_currency',
            'mode' => 'transaction_operator',
            'status' => 'transaction_status',
            'refTrans' => 'dohone_transaction_ref',
            'nameDest' => 'customer_name',
            'destination' => 'customer_phone_number',
            'withdrawalMode' => 'withdrawal_mode',
        ];
        foreach ($map as $key => $value)
            if (key_exists($key, $data))
                $data[$value] = $data[$key];

        return $data;
    }
}