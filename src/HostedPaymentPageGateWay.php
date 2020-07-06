<?php

namespace Omnipay\Wirecard;

/**
 * Wirecard Checkout Page driver for Omnipay
 */

use Exception;
use Omnipay\Common\Helper;
use SilverStripe\Dev\Debug;
use Omnipay\Common\Exception\InvalidRequestException;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Omnipay\Common\AbstractGateway as OmnipayAbstractGateway;

class HostedPaymentPageGateway extends OmnipayAbstractGateway
{
    public function getDefaultParameters()
    {
        $params = parent::getDefaultParameters();
        
        $params["CreditCard"] = [];
        $params["EPS"] = [];
        $params["Giropay"] = [];
        $params["Sofort"] = [];
        $params["MerchantAccountID"] = '';
        $params["UserName"] = '';
        $params["Password"] = '';
        $params["shopId"] = '';

        return $params;
    }

    public function getName()
    {
        return 'Wirecard';
    }

    /**
     * The purchase transaction.
     */
    public function purchase(array $parameters = array())
    {
        return $this->createRequest(
            'Omnipay\Wirecard\Message\Payment\Page\PurchaseRequest',
            $parameters
        );
    }
    /**
     * The complete purchase transaction (capturing data retuned with the user).
     */
    public function completePurchase(array $parameters = array())
    {
        return $this->createRequest(
            "Omnipay\Wirecard\Message\Payment\Page\CompleteRequest",
            $parameters
        );
    }

    public function refund(array $parameters = array())
    {
        return $this->createRequest(
            "Omnipay\Wirecard\Message\Payment\Page\RefundRequest",
            $parameters
        );
    }
    /*
setPayment-method</pre><pre style="background-color:#ccc;padding:5px;font-size:14px;line-height:18px;"><span style="font-size: 12px;color:#666;">HostedPaymentPageGateWay.php:66 - Omnipay\Wirecard\HostedPaymentPageGateway::createRequest() - </span>
setTransaction-type</pre><pre style="background-color:#ccc;padding:5px;font-size:14px;line-height:18px;"><span style="font-size: 12px;color:#666;">HostedPaymentPageGateWay.php:66 - Omnipay\Wirecard\HostedPaymentPageGateway::createRequest() - </span>
setParent-transaction-id</pr

    */
    protected function createRequest($class, array $parameters)
    {
        $obj = new $class($this->httpClient, $this->httpRequest);
        return $obj->initialize(array_replace($this->getParameters(), $parameters));
    }

    /**
     * The void refund transaction.
     */
    public function voidRefund(array $parameters = array())
    {
        throw new Exception('Not implemented');
    }
    public function setPaymentMethod($value)
    {
        return $this->setParameter("PaymentMethod",$value);
    }
    public function setTransactionType($value)
    {
        return $this->setParameter("TransactionType",$value);
    }
    public function setTransactionID($value)
    {
        return $this->setParameter("TransactionID",$value);
    }
    public function setParentTransactionId($value)
    {
        return $this->setParameter("ParentTransactionId",$value);
    }
    public function setCreditCard($value)
    {
        return $this->setParameter("creditcard",$value);
    }
    public function setEPS($value)
    {
        return $this->setParameter("eps",$value);
    }
    public function setGiropay($value)
    {
        return $this->setParameter("giropay",$value);
    }
    public function setSofortbanking($value)
    {
        return $this->setParameter("sofortbanking",$value);
    }
    public function setIban($value)
    {
        return $this->setParameter("Iban",$value);
    }
    public function setBic($value)
    {
        return $this->setParameter("BIC",$value);
    }
    public function setFirstName($value)
    {
        return $this->setParameter("FirstName",$value);
    }
    public function setSurname($value)
    {
        return $this->setParameter("Surname",$value);
    }
    public function setEmail($value)
    {
        return $this->setParameter("Email",$value);
    }
}