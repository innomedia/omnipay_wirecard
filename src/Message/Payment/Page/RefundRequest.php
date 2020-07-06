<?php

namespace Omnipay\Wirecard\Message\Payment\Page;

use Exception;
use Omnipay\Common\Helper;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Common\Message\AbstractRequest as OmnipayAbstractRequest;
use Omnipay\Wirecard\Message\Payment\Page\RefundResponse;
use SilverStripe\Dev\Debug;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * Wirecard Payment Page Refund.
 */

class RefundRequest extends OmnipayAbstractRequest
{

    protected $autoDeposit = 'yes';

    public function __construct(ClientInterface $httpClient, HttpRequest $httpRequest)
    {
        $this->httpClient = $httpClient;
        $this->httpRequest = $httpRequest;
    }

    /**
     * Initialize the object with parameters.
     *
     * If any unknown parameters passed, they will be ignored.
     *
     * @param array $parameters An associative array of parameters
     *
     * @return $this
     * @throws RuntimeException
     */
    public function initialize(array $parameters = array())
    {
        if (null !== $this->response) {
            throw new RuntimeException('Request cannot be modified after it has been sent!');
        }

        $this->parameters = new ParameterBag;

        Helper::initialize($this, $parameters);

        return $this;
    }

    public function setPaymentType($value)
    {
        return $this->setParameter('paymentType', $value);
    }

    public function setPaymentMethod($value)
    {
        return $this->setParameter("PaymentMethod", $value);
    }
    public function setTransactionType($value)
    {
        return $this->setParameter("TransactionType", $value);
    }
    public function setParentTransactionId($value)
    {
        return $this->setParameter("ParentTransactionId", $value);
    }
    public function setTransactionId($value)
    {
        return $this->setParameter("TransactionId", $value);
    }
    /**
     * @inherit
     */
    protected function createResponse($data, $requestdata)
    {
        return $this->response = new RefundResponse($this, $data, $requestdata);
    }

    /**
     * Construct the request data to send.
     *
     * @return array
     */
    public function getData()
    {
        switch ($this->getTransactionType()) {
            case "purchase":
                return $this->getCreditCardData();
            case "debit":
                return $this->getDebitData();
        }
        //throw new Exception('Not implemented');

    }
    private function getCreditCardData()
    {
        return '{
            "payment": {
              "merchant-account-id": {
                "value": "' . $this->getMerchantAccountID() . '"
              },
              "request-id": "' . $this->getRequestID() . '",
              "parent-transaction-id": "' . $this->getTransactionId() . '",
              "transaction-type": "refund-' . $this->getTransactionType() . '",
              "requested-amount": {
                "value": ' . $this->getChargeAmount() . ',
                "currency": "' . $this->getChargeCurrency() . '"
              },
              "ip-address": "127.0.0.1"
            }
          }';
    }
    private function getDebitData()
    {
        if ($this->getParameter("testMode")) {
            return '<?xml version="1.0" encoding="utf-8" standalone="yes"?>
            <payment xmlns="http://www.elastic-payments.com/schema/payment">
                <merchant-account-id>' . $this->getMerchantAccountID() . '</merchant-account-id>
                <request-id>' . $this->getRequestID() . '</request-id>
                <transaction-type>pending-credit</transaction-type>
                <requested-amount currency="' . $this->getChargeCurrency() . '">' . $this->getChargeAmount() . '</requested-amount>
                <account-holder>
                    <first-name>' . $this->getFirstName() . '</first-name>
                    <last-name>' . $this->getSurname() . '</last-name>
                    <email>' . $this->getEmail() . '</email>
                </account-holder>
                <payment-methods>
                    <payment-method name="sepacredit" />
                </payment-methods>
                <bank-account>
                    <iban>' . $this->getIban() . '</iban>
                    <bic>' . $this->getBic() . '</bic>
                </bank-account>
                </payment>';
        }
        else
        {
            return '<?xml version="1.0" encoding="utf-8" standalone="yes"?>
            <payment xmlns="http://www.elastic-payments.com/schema/payment">
                <merchant-account-id>' . $this->getMerchantAccountID() . '</merchant-account-id>
                <request-id>' . $this->getRequestID() . '</request-id>
                <transaction-type>pending-credit</transaction-type>
                <requested-amount currency="' . $this->getChargeCurrency() . '">' . $this->getChargeAmount() . '</requested-amount>
                <parent-transaction-id>' . $this->getTransactionId() . '</parent-transaction-id>
                <account-holder>
                    <first-name>' . $this->getFirstName() . '</first-name>
                    <last-name>' . $this->getSurname() . '</last-name>
                    <email>' . $this->getEmail() . '</email>
                </account-holder>
                <payment-methods>
                    <payment-method name="sepacredit" />
                </payment-methods>
                <bank-account>
                    <iban>' . $this->getIban() . '</iban>
                    <bic>' . $this->getBic() . '</bic>
                </bank-account>
                </payment>';
        }
    }
    public function sendCreditCardData($data)
    {
        $ch = curl_init();
        $payload = $data;
        $headers = [
            'Content-Type: application/json',
            "Authorization: Basic " . $this->getBase64UserCredentials(),
        ];
        curl_setopt($ch, CURLOPT_URL, $this->getPaymentSessionUrl());
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERPWD, $this->getUserCredentials());
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $requestdata = curl_exec($ch);
        curl_close($ch);
        return $this->createResponse($data, $requestdata);
    }
    public function sendDebitData($data)
    {
        $ch = curl_init();
        $payload = $data;
        $headers = [
            'Content-Type: application/xml',
            "Authorization: Basic " . $this->getBase64UserCredentials(),
        ];
        curl_setopt($ch, CURLOPT_URL, $this->getPaymentSessionUrl());
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERPWD, $this->getUserCredentials());
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $requestdata = curl_exec($ch);
        curl_close($ch);
        return $this->createResponse($data, $requestdata);
    }
    public function sendData($data)
    {
        switch ($this->getTransactionType()) {
            case "purchase":
                return $this->sendCreditCardData($data);
            case "debit":
                return $this->sendDebitData($data);
        }
    }
    private function getRequestID()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }
    public function getMerchantAccountID()
    {
        $paymenttype = $this->getPaymentMethod();
        if ($this->getParameter("testMode")) {
            switch ($paymenttype) {
                case "creditcard":
                    return "cad16b4a-abf2-450d-bcb8-1725a4cef443";
                case "sofortbanking":
                    return "59a01668-693b-49f0-8a1f-f3c1ba025d45";
                case "giropay":
                    return "59a01668-693b-49f0-8a1f-f3c1ba025d45";
                case "eps":
                    return "59a01668-693b-49f0-8a1f-f3c1ba025d45";
            }
        } else {
            return $this->getMerchantAccountIDFromPaymentType($paymenttype);
        }

    }

    private function getMerchantAccountIDFromPaymentType($paymenttype)
    {
        return $this->getParameter($paymenttype)["MerchantAccountID"];
    }
    public function getNotifyURL()
    {
        return $this->getParameter("notifyUrl");
    }
    public function getSuccessURL()
    {
        return $this->getParameter("returnUrl");
    }
    public function getFailURL()
    {
        return $this->getParameter("cancelUrl");
    }
    public function getCancelURL()
    {
        return $this->getParameter("cancelUrl");
    }
    public function getTransactionReference()
    {
        return $this->getParameter("transactionReference");
    }
    public function getBillingFirstname()
    {
        return $this->getParameter("billingFirstName");
    }
    public function getBillingSurname()
    {
        return $this->getParameter("billingLastName");
    }
    public function getChargeAmount()
    {
        return $this->getParameter("amount");
    }
    public function getChargeCurrency()
    {
        return $this->getParameter("currency");
    }
    public function getPaymentMethod()
    {
        return $this->getParameter("PaymentMethod");
    }
    public function getTransactionType()
    {
        return $this->getParameter("TransactionType");
    }
    public function getParentTransactionId()
    {
        return $this->getParameter("ParentTransactionId");
    }
    public function getTransactionId()
    {
        //TODO Remove this .. just for testing purposes
        //return "d5e77911-fc94-4bc1-8e80-78e9737bc81b";
        return $this->getParameter("TransactionId");
    }
    private function getPaymentSessionUrl()
    {
        switch ($this->getTransactionType()) {
            case "purchase":
                if ($this->getParameter("testMode")) {
                    return "https://api-test.wirecard.com/engine/rest/payments/";
                } else {
                    return "https://api.wirecard.com/engine/rest/payments/";
                }
            case "debit":
                if ($this->getParameter("testMode")) {
                    return "https://api-test.wirecard.com/engine/rest/paymentmethods/";
                } else {
                    return "https://api.wirecard.com/engine/rest/paymentmethods/";
                }
        }

    }
    public function getUserName($paymenttype)
    {
        return $this->getParameter($paymenttype)["UserName"];
    }
    public function getPassword($paymenttype)
    {
        return $this->getParameter($paymenttype)["Password"];
    }
    private function getUserCredentials()
    {
        if ($this->getParameter("testMode")) {
            switch ($this->getPaymentMethod()) {
                case "creditcard":
                    return "70000-APILUHN-CARD:8mhwavKVb91T";
                case "sofortbanking":
                    return "16390-testing:3!3013=D3fD8X7";
                case "giropay":
                    return "16390-testing:3!3013=D3fD8X7";
                case "eps":
                    return "16390-testing:3!3013=D3fD8X7";
            }
        } else {
            $paymenttype = $this->getPaymentMethod();
            return $this->getUserName($paymenttype) . ":" . $this->getPassword($paymenttype);
        }

    }
    private function getBase64UserCredentials()
    {
        return base64_encode($this->getUserCredentials());
    }

    public function setIban($value)
    {
        return $this->setParameter("Iban", $value);
    }
    public function setBic($value)
    {
        return $this->setParameter("BIC", $value);
    }
    public function getIban()
    {
        return $this->getParameter("Iban");
    }
    public function getBic()
    {
        return $this->getParameter("BIC");
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
    public function getFirstName()
    {
        return $this->getParameter("FirstName");
    }
    public function getSurname()
    {
        return $this->getParameter("Surname");
    }
    public function getEmail()
    {
        return $this->getParameter("Email");
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
}
