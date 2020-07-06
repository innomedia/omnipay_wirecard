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
use Omnipay\Common\Message\RequestInterface;

use SilverStripe\Dev\Debug;



use Omnipay\Common\Message\AbstractResponse;

class RefundResponse extends AbstractResponse
{
    protected $refundData;
    public function isSuccessful()
    {
        if($this->getArrayRefundData()["transaction-state"] == "success")
        {
            return true;
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
    public function __construct(RequestInterface $request, $data,$refundData)
    {
        $this->request = $request;
        $this->data = $data;
        
        $this->refundData = $refundData;
    }
    private function getArrayRefundData()
    {
        $xml = simplexml_load_string($this->refundData, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $array = json_decode($json,TRUE);
        return $array;
    }
}
