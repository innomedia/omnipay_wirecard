<?php

namespace Omnipay\Wirecard\Message\Payment\Page;

/**
 * Complete a Wirecard Checkout Page purchase transaction on the
 * user returning to the merchant shop.
 * Experimentally, this one class covers both the request and the response,
 * since not further requests back to the gateway are needed.
 * The advantage of doing this is that all the results needed are in the
 * initial request object. A merchant site can still send() that message
 * and get the same message back.
 * It should be possible to extend this as the notification handler too. If
 * so, then the HasFingerprintTrait becomes redundant.
 */

use Omnipay\Common\Message\ResponseInterface;

use SilverStripe\Dev\Debug;

use Omnipay\Common\Message\AbstractResponse;

class CompleteResponse extends AbstractResponse
{
    public function isSuccessful()
    {
        $data = $this->request->getData();
        if($data != null && is_array($data) && array_key_exists("response-base64",$data))
        {
            $data = base64_decode($this->request->getData()["response-base64"]);
            $data = json_decode($data,true);
            if(array_key_exists("payment",$data) && array_key_exists("transaction-state",$data["payment"]) && $data["payment"]["transaction-state"] == "success")
            {
                return true;
            }
        }
        return false;
    }
    public function getTransactionReference()
    {
        return $this->request->getTransactionReference();
    }
    //Convert To Text
    public function getData()
    {
        return $this->data["response-base64"];
    }
}
