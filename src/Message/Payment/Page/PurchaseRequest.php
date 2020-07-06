<?php

namespace Omnipay\Wirecard\Message\Payment\Page;

use Exception;
use CustomPaymentOption;
use SilverStripe\Dev\Debug;
use TractorCow\Fluent\Model\Locale;
use Omnipay\Common\Message\AbstractRequest as OmnipayAbstractRequest;

/**
 * Wirecard Checkout Page Purchase.
 */

class PurchaseRequest extends OmnipayAbstractRequest
{

    protected $autoDeposit = 'yes';

    public function setPaymentType($value)
    {
        return $this->setParameter('paymentType', $value);
    }

    public function getPaymentType()
    {
        return $this->getParameter('paymentType');
    }
    public function getTransactionType()
    {
        $CustomPaymentOption = CustomPaymentOption::get()->filter("PaymentMethod", $this->getParameter('paymentType'))->first();
        if ($CustomPaymentOption == null) {
            $message = $this->getParameter('paymentType') . " is missing Configuration";
            throw new Exception($message);
        }
        return $CustomPaymentOption->TransactionType;
    }
    /**
     * @inherit
     */
    protected function createResponse($data, $redirecturl)
    {
        return $this->response = new PurchaseResponse($this, $data, $redirecturl);
    }

    /**
     * Construct the request data to send.
     *
     * @return array
     */
    public function getData()
    {
        $Locale = Locale::getCurrentLocale();
        $Locale = explode("_",$Locale->Locale)[0];
        //throw new Exception('Not implemented');
        return '{
            "payment": {
              "merchant-account-id": {
                "value": "' . $this->getMerchantAccountID() . '"
              },
              "account-holder": {
                "first-name": "' . $this->getBillingFirstname() . '",
                "last-name": "' . $this->getBillingSurname() . '"
              },
              "request-id": "' . $this->getTransactionID() . '",
              "requested-amount": {
                "value": ' . $this->getChargeAmount() . ',
                "currency": "' . $this->getChargeCurrency() . '"
              },
              "transaction-type": "' . $this->getTransactionType() . '",
              "three-d": {
                "attempt-three-d": "true"
              },
              "locale": "'.$Locale.'",
              "notifications": {
                "format": "application/xml",
                "notification": [
                  {
                    "url": "' . $this->getNotifyURL() . '"
                  }
                ]
              },
              "payment-methods": {
                "payment-method": [
                  {
                    "name": "' . $this->getPaymentType() . '"
                  }
                ]
              },
              "descriptor": "Esta Application",
              "success-redirect-url": "' . $this->getSuccessURL() . '",
              "fail-redirect-url": "' . $this->getFailURL() . '",
              "cancel-redirect-url": "' . $this->getCancelUrl() . '"
            }
          }';
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
    public function getTransactionID()
    {
        return $this->getParameter("transactionId");
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
        return number_format($this->getParameter("amount"),2);
    }
    public function getChargeCurrency()
    {
        return $this->getParameter("currency");
    }
    private function getPaymentSessionUrl()
    {
        if ($this->getParameter("testMode")) {
            return "https://wpp-test.wirecard.com/api/payment/register";
        } else {
            return "https://wpp.wirecard.com/api/payment/register";
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
            switch ($this->getPaymentType()) {
                case "creditcard":
                    return "70000-APILUHN-CARD:8mhwavKVb91T";
                case "sofortbanking":
                    return "70000-APITEST-AP:qD2wzQ_hrc!8";
                case "giropay":
                    return "16390-testing:3!3013=D3fD8X7";
                case "eps":
                    return "16390-testing:3!3013=D3fD8X7";
            }
        } else {
            $paymenttype = $this->getPaymentType();
            return $this->getUserName($paymenttype) . ":" . $this->getPassword($paymenttype);
        }

    }
    private function getBase64UserCredentials()
    {
        return base64_encode($this->getUserCredentials());
    }
    public function sendData($data)
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
        $return = curl_exec($ch);
        curl_close($ch);
        $HPPUrl = json_decode($return, true)["payment-redirect-url"];
        return $this->createResponse($data, $HPPUrl);
    }
    public function getMerchantAccountID()
    {
        $paymenttype = $this->getPaymentType();
        if ($this->getParameter("testMode")) {
            switch ($paymenttype) {
                case "creditcard":
                    return "cad16b4a-abf2-450d-bcb8-1725a4cef443";
                case "sofortbanking":
                    return "f19d17a2-01ae-11e2-9085-005056a96a54";
                case "giropay":
                    return "9b4b0e5f-1bc8-422e-be42-d0bad2eadabc";
                case "eps":
                    return "1f629760-1a66-4f83-a6b4-6a35620b4a6d";
            }
        } else {
            return $this->getMerchantAccountIDFromPaymentType($paymenttype);
        }

    }

    private function getMerchantAccountIDFromPaymentType($paymenttype)
    {
        return $this->getParameter($paymenttype)["MerchantAccountID"];
    }

    /**
     * Sets the test mode of the request.
     *
     * @param boolean $value True for test mode on.
     * @return $this
     */
    public function setCreditCard($value)
    {
        return $this->setParameter("creditcard", $value);
    }
    public function setEPS($value)
    {
        return $this->setParameter("eps", $value);
    }
    public function setGiropay($value)
    {
        return $this->setParameter("giropay", $value);
    }
    public function setSofortbanking($value)
    {
        return $this->setParameter("sofortbanking", $value);
    }
}
