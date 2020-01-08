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
     * @param string $dohoneMerchantKey (optional)
     * @param string $dohoneAccount (optional)
     * @param string $notifyUrl (optional)
     */
    public function __construct($dohoneMerchantKey = '', $dohoneAccount = '', $notifyUrl = null)
    {
        parent::__construct($dohoneMerchantKey, $notifyUrl);

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
     */
    protected function parseDohoneResponse($res)
    {
        $words = explode(' ', trim($res));
        $status = $words[0]; // First word
        $success = $status === 'OK';
        $message = substr($res, strpos($res, '/') + 2);

        return new DohoneResponse([
            'success' => $success,
            'status' => $status,
            'message' => $message,
            'REF' => $success ? $message : null,
            'fullResponse' => $res
        ]);
    }

    /**
     * @param TransactionInterface $transaction
     * @param array $params
     * @return DohoneResponse
     */
    public function quote($transaction, $params = ['mode' => 1])
    {
        return $this->request('cotation', [
            'amount' => $transaction->getTransactionAmount(),
            'devise' => $transaction->getTransactionCurrency(),
            'mode' => $params['mode']
        ]);
    }

    /**
     * @param TransactionInterface $transaction
     * @param array $params
     * @return DohoneResponse
     */
    public function transfer($transaction, $params = ['mode' => 1])
    {
        $account = $this->getDohoneAccount();
        $mode = $params['mode'];
        $amount = $transaction->getTransactionAmount();
        $devise = $transaction->getTransactionCurrency();
        $transID = $transaction->getTransactionRef();

        $hash = md5($account . $mode . $amount . $devise . $transID . $this->getDohoneMerchantKey());

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
            'notifyUrl' => $this->getNotifyUrl()
        ]);
    }
}