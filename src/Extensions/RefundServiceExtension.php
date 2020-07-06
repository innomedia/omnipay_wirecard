<?php
namespace Omnipay\Wirecard\Message\Payment\Page;
use SilverStripe\Dev\Debug;
use SilverStripe\ORM\DataExtension;

class RefundServiceExtension extends DataExtension
{
    public function onBeforeRefund(&$gatewayData)
    {
        if( $this->owner->getPayment()->Gateway == "Wirecard_HostedPaymentPage")
        {
            $message = $this->owner->getPayment()->Messages()->filter("ClassName","SilverStripe\Omnipay\Model\Message\PurchasedResponse")->first();
            $data = json_decode(base64_decode($message->JSONData),true);
            $paymentmethod = $data["payment"]["payment-methods"]["payment-method"][0]["name"];
            $transactiontype = $data["payment"]["transaction-type"];
            $parenttransactionid = $data["payment"]["parent-transaction-id"];
            $transactionid = $data["payment"]["transaction-id"];

            $gatewayData["paymentMethod"] = $paymentmethod;
            $gatewayData["transactionType"] = $transactiontype;
            $gatewayData["parentTransactionId"] = $parenttransactionid;
            $gatewayData["transactionId"] = $transactionid;

            $gatewayData["FirstName"] = $this->owner->getPayment()->Order()->FirstName;
            $gatewayData["Surname"] = $this->owner->getPayment()->Order()->Surname;
            $gatewayData["Email"] = $this->owner->getPayment()->Order()->Email;
            if($this->owner->getPayment()->Iban != "" || $this->owner->getPayment()->Iban != null)
            {
                $gatewayData["Iban"] = $this->owner->getPayment()->Iban;
            }
            if($this->owner->getPayment()->bic != "" || $this->owner->getPayment()->bic != null)
            {
                $gatewayData["bic"] = $this->owner->getPayment()->bic;
            }
        }
    }
}