<?php
/**
 * Created by PhpStorm.
 * User: mathermann
 * Date: 07/01/2020
 * Time: 22:31
 */

namespace Mathermann\DohoneSDK;


interface TransactionInterface
{
    /**
     * @return string
     */
    public function getTransactionRef();

    /**
     * @return string
     * Note: Must be one of the following: ['DOHONE_MOMO', 'DOHONE_OM', 'DOHONE_EU', 'DOHONE_TRANSFER']
     */
    public function getTransactionOperator();

    /**
     * @return double
     */
    public function getTransactionAmount();

    /**
     * @return string
     * Note: Must be one of the following: ['XAF', 'EUR', 'USD']
     */
    public function getTransactionCurrency();

    /**
     * @return string
     */
    public function getTransactionReason();

    /**
     * @return string
     */
    public function getDohoneTransactionRef();

    /**
     * @return string
     */
    public function getCustomerName();

    /**
     * @return string
     */
    public function getCustomerPhoneNumber();

    /**
     * @return string
     */
    public function getCustomerEmail();

    /**
     * @return string
     */
    public function getCustomerCountry();

    /**
     * @return string
     */
    public function getCustomerCity();

    /**
     * @return string|null
     */
    public function getNotifyUrl();
}