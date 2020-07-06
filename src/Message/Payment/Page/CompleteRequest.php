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
use Exception;

use SilverStripe\Dev\Debug;

use Omnipay\Common\Message\AbstractRequest;

class CompleteRequest extends AbstractRequest
{
    protected function createResponse($data)
    {
        $this->response = new CompleteResponse($this,$data);
        return $this->response;
    }
    public function sendData($data)
    {
        return $this->createResponse($data);
    }
    public function getData()
    {
        return $this->httpRequest->request->all();
    }
    public function getTransactionId()
    {
        return $this->getParameter("transactionId");
    }
    public function getCode()
    {
        return get_object_vars($this->getParsedMessage()->payment)["authorization-code"];
    }
    public function getMessage()
    {
        if($this->getParsedMessage()->payment->statuses->status[0]->severity != "information")
        {
            return $this->getParsedMessage()->payment->statuses->status[0]->description;
        }
        return "";
        
    }
    private function getParsedMessage()
    {
        return json_decode(base64_decode($this->getData()["response-base64"]));
    }
    public function getClientIp()
    {
        return $this->getParameter("clientIp");
    }
    public function getTransactionReference()
    {
        return $this->getTransactionId();
    }
}
