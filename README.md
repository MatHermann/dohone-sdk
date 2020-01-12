# DohoneSDK
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-packagist]  
A PHP 5.4+ SDK for easily use Dohone payment API.

## Note
This PHP package let you easily integrate **Dohone payment API** to your application or your web site for every Cameroon mobile payments operators (MTN Mobile Money, Orange Money and Express Union Mobile Money).  

Before you start using this package, it's highly recommended to read documents below.
- [TUTORIAL D’INSTALLATION DE L’API DE PAIEMENT EN LIGNE POUR APPLICATIONS MOBILES][link-dohone-doc-mobile]
- [TUTORIAL D’INSTALLATION DE L’API DE PAIEMENT EN LIGNE][link-dohone-doc-web]
- [TUTORIAL D’INSTALLATION DE L’API DE TRANSFERTS AUTOMATIQUES (PAYOUT)][link-dohone-doc-payout]

## Table of contents
* [1. Requirements](#1-requirements)
* [2. Installation](#2-installation)
    * [2.1. Install Composer package](#21-install-composer-package)
    * [2.2. Implement TransactionInterface](#22-implement-transactioninterface)
* [3. Payin requests (collect payments)](#3-payin-requests-collect-payments)
    * [3.1. Create a DohoneSDK object](#31-create-a-dohonesdk-object)
    * [3.2. Make a &laquo; COTATION &raquo; command](#32-make-a--cotation--command)
    * [3.3. Make a &laquo; START &raquo; command](#33-make-a--start--command)
    * [3.4. Make a &laquo; CFRMSMS &raquo; command](#34-make-a--crfmsms--command)
    * [3.5. Make a &laquo; VERIFY &raquo; command](#35-make-a--verify--command)
    * [3.6. Handle Dohone's notifications](#36-handle-dohones-notifications)
* [4. Payout requests (refund customer or withdraw money)](#4-payout-requests-refund-customer-or-withdraw-money)
    * [4.1. Create a DohonePayoutSDK object](#41-create-a-dohonepayoutsdk-object)
    * [4.2. Make a &laquo; COTATION &raquo; command](#42-make-a--cotation--command)
    * [4.3. Make a transfer](#43-make-a-transfer)
    * [4.4. Handle Dohone's payout notifications](#44-handle-dohones-payout-notifications)

## 1. Requirements
This package requires: ![Required PHP version][ico-php-version]

## 2. Installation
### 2.1. Install Composer package
First of all, install the Composer package from your CMD or your Terminal.
``` bash
$ composer require mathermann/dohone-sdk
```
### 2.2. Implement TransactionInterface
Then you need a class that implements [**TransactionInterface**][link-transaction-interface] interface.  
For example:
``` php
<?php

namespace Your\Name\Space; // Replace with your namespace

use Mathermann\DohoneSDK\TransactionInterface;

class Transaction implements TransactionInterface
{
    /**
     * Transaction reference (or id) in your system
     */
    private $transactionRef;
    
    /**
     * Transaction operator, must be one of the following values:
     * ['DOHONE_MOMO', 'DOHONE_OM', 'DOHONE_EU', 'DOHONE_TRANSFER']
     */
    private $transactionOperator;
    
    private $transactionAmount;
    
    /**
     * Transaction currency, must be one of the following values:
     * ['XAF', 'EUR', 'USD']
     */
    private $transactionCurrency;
    
    private $transactionReason;
    
    /**
     * Transaction reference in Dohone's system
     */
    private $dohoneTransactionRef;
    
    private $customerName;
    
    private $customerPhoneNumber;
    
    private $customerEmail;
    
    private $customerCountry;
    
    private $customerCity;
    
    /**
     * Notification URL for this transaction
     */
    private $notifyUrl;
    
    // You can add some additional properties...
    
    
    public function getTransactionRef()
    {
        return $this->transactionRef;
    }

    public function getTransactionOperator()
    {
        return $this->transactionOperator;
    }

    public function getTransactionAmount()
    {
        return $this->transactionAmount;
    }

    public function getTransactionCurrency()
    {
        return $this->transactionCurrency;
    }

    public function getTransactionReason()
    {
        return $this->transactionReason;
    }

    public function getDohoneTransactionRef()
    {
        return $this->dohoneTransactionRef;
    }

    public function getCustomerName()
    {
        return $this->customerName;
    }

    public function getCustomerPhoneNumber()
    {
        return $this->customerPhoneNumber;
    }

    public function getCustomerEmail()
    {
        return $this->customerEmail;
    }

    public function getCustomerCountry()
    {
        return $this->customerCountry;
    }

    public function getCustomerCity()
    {
        return $this->customerCity;
    }

    public function getNotifyUrl()
    {
        return $this->notifyUrl;
    }
    
    // add setters...
    
    // you can add some additional methods...
}
```

## 3. Payin requests (collect payments)
### 3.1. Create a DohoneSDK object
You can find [DohoneSDK class here][link-dohone-sdk].
``` php
<?php

use Mathermann\DohoneSDK\DohoneSDK;

// constants
define('MERCHANT_KEY', '...'); // your dohone merchant key (required)
define('APP_NAME', '...'); // the name by which your application is recognized at Dohone
define('HASH_CODE', '...'); // your dohone hash code (only if you handle dohone notifications in your system)
define('NOTIFY_URL', '...'); // default notification URL for incoming payments

$dohoneSdk = new DohoneSDK(MERCHANT_KEY, APP_NAME, HASH_CODE, NOTIFY_URL);

// ...
```
### 3.2. Make a &laquo; COTATION &raquo; command
``` php
<?php

// ...

use Mathermann\DohoneSDK\InvalidDohoneResponseException;

try 
{
    /**
     * $transaction is an object of type Transaction defined above,
     * $mode is exactly the same as "mode" in Dohone's documentation
     */
    $response = $dohoneSdk->quote($transaction, ['mode' => $mode]);
    
    if ($response->isSuccess())
    {
        echo $response->getMessage(); // display result
    }
    else
    {
        echo $response->getMessage(); // display error message
    }                
}
catch (InvalidDohoneResponseException $e)
{
    echo $e->getMessage(); // display error message
}

// ...
```

### 3.3. Make a &laquo; START &raquo; command
``` php
<?php

// ...

use Mathermann\DohoneSDK\InvalidDohoneResponseException;

try
{
    /**
     * $transaction is an object of type Transaction defined above,
     * $OTP is exactly the same as "rOTP" in Dohone's documentation, required only for Orange Money payment
     */
    $response = $dohoneSdk->start($transaction, ['OTP' => $OTP]);
    
    if ($response->isSuccess())
    {
        if ($response->hasREF()) {
            // ToDo: handle success
        }
        else if ($response->needCFRMSMS()) {
            // ToDo: request SMS confirmation code to user
        }
    }
    else
    {
        echo $response->getMessage(); // display error message
    }                
}
catch (InvalidDohoneResponseException $e)
{
    echo $e->getMessage(); // display error message
}

// ...
```

### 3.4. Make a &laquo; CRFMSMS &raquo; command
``` php
<?php

// ...

use Mathermann\DohoneSDK\InvalidDohoneResponseException;

try
{
    /**
     * $transaction is an object of type Transaction defined above,
     * $code is exactly the same as "rCS" in Dohone's documentation
     */
    $response = $dohoneSdk->confirmSMS($transaction, ['code' => $code]);
    
    if ($response->isSuccess())
    {
        if ($response->hasREF()) {
            // ToDo: handle success
        }
        else if ($response->needCFRMSMS()) {
            // ToDo: request SMS confirmation code to user
        }
    }
    else
    {
        echo $response->getMessage(); // display error message
    }                
}
catch (InvalidDohoneResponseException $e)
{
    echo $e->getMessage(); // display error message
}

// ...
```

### 3.5. Make a &laquo; VERIFY &raquo; command
``` php
<?php

// ...

use Mathermann\DohoneSDK\InvalidDohoneResponseException;

try
{
    /**
     * $transaction is an object of type Transaction defined above
     */
    $response = $dohoneSdk->verify($transaction);
    
    if ($response->isSuccess()) {
        // ToDo: handle OK
    }
    else {
        // ToDo: handle NO
    }                
} 
catch (InvalidDohoneResponseException $e)
{
    echo $e->getMessage(); // display error message
}

// ...
```

### 3.6. Handle Dohone's notifications
``` php
<?php

// ...

use Mathermann\DohoneSDK\InvalidDohoneResponseException;

// convert data into more readable format
/**
 * $notificationData is data received from the request, it can be
 * $_GET if you use vanilla PHP,
 * $request->query->all() if you use Symfony or
 * Input::all() if you use Laravel
 */
$data = $dohoneSdk->mapNotificationData($notificationData);

// check for request integrity
if ($dohoneSdk->checkHash($data))
{
    // fill new Transaction() with data and check if Dohone recognizes the transaction
    try
    {
        /**
         * $transaction is an object of type Transaction defined above
         */
        $response = $dohoneSdk->verify($transaction);
        
        if ($response->isSuccess()) {
            // ToDo: handle OK
        }
        else {
            // ToDo: handle NO (or just ignore the request)
        }                
    } 
    catch (InvalidDohoneResponseException $e)
    {
        echo $e->getMessage(); // display error message
    }
}

// ...
```

## 4. Payout requests (refund customer or withdraw money)
### 4.1. Create a DohonePayoutSDK object
You can find [DohonePayoutSDK class here][link-dohone-payout-sdk].
``` php
<?php

use Mathermann\DohoneSDK\DohonePayoutSDK;

// constants
define('ACCOUNT', '...'); // the phone number of your Dohone account (required)
define('HASH_CODE', '...'); // your dohone hash code (required)
define('NOTIFY_URL', '...'); // default notification URL for payouts

$dohonePayoutSdk = new DohonePayoutSDK(ACCOUNT, HASH_CODE, NOTIFY_URL);

// ...
```
### 4.2. Make a &laquo; COTATION &raquo; command
``` php
<?php

// ...

use Mathermann\DohoneSDK\InvalidDohoneResponseException;

try 
{
    /**
     * $transaction is an object of type Transaction defined above
     */
    $response = $dohonePayoutSdk->quote($transaction);
    
    if ($response->isSuccess())
    {
        echo $response->getMessage(); // display result
    }
    else
    {
        echo $response->getMessage(); // display error message
    }                
}
catch (InvalidDohoneResponseException $e)
{
    echo $e->getMessage(); // display error message
}

// ...
```

### 4.3. Make a transfer
``` php
<?php

// ...

use Mathermann\DohoneSDK\InvalidDohoneResponseException;

try
{
    /**
     * $transaction is an object of type Transaction defined above
     */
    $response = $dohonePayoutSdk->transfer($transaction);
    
    if ($response->isSuccess() && $response->hasREF()) {
        // ToDo: handle success
    }
    else {
        echo $response->getMessage(); // display error message
    }
}
catch (InvalidDohoneResponseException $e)
{
    echo $e->getMessage(); // display error message
}

// ...
```

### 4.4. Handle Dohone's payout notifications
``` php
<?php

// ...

// convert data into more readable format
/**
 * $notificationData is data received from the request, it can be
 * $_GET if you use vanilla PHP,
 * $request->query->all() if you use Symfony or
 * Input::all() if you use Laravel
 */
$data = $dohonePayoutSdk->mapNotificationData($notificationData);

// ToDo: handle notification

// ...
```

## Credits
- [mathermann][link-author]

[ico-version]: https://img.shields.io/packagist/v/mathermann/dohone-sdk
[ico-downloads]: https://img.shields.io/packagist/dt/mathermann/dohone-sdk
[ico-php-version]: https://img.shields.io/packagist/php-v/mathermann/dohone-sdk

[link-packagist]: https://packagist.org/packages/mathermann/dohone-sdk

[link-author]: mailto:wkouogangkamdem@gmail.com

[link-transaction-interface]: src/TransactionInterface.php
[link-dohone-sdk]: src/DohoneSDK.php
[link-dohone-payout-sdk]: src/DohonePayoutSDK.php

[link-dohone-doc-mobile]: https://www.my-dohone.com/dohone/site/modules/pagesExtra/api/1/tuto-api-mobile.pdf
[link-dohone-doc-web]: https://www.my-dohone.com/dohone/site/modules/pagesExtra/api/1/tuto-api-dohone.pdf
[link-dohone-doc-payout]: https://www.my-dohone.com/dohone/site/modules/pagesExtra/api/1/tuto-api-payout.pdf
