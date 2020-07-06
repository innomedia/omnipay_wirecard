<?php

use SilverStripe\Dev\Debug;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Omnipay\Service\PurchaseService;
use Omnipay\PayPal\Message\ExpressAuthorizeResponse;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\NotificationInterface;

class PurchaseServiceFix extends PurchaseService
{
    protected function createMessage($type, $data = null)
    {
        $output = array();
        
        if (is_string($data)) {
            $output = [
                'Message' => $data
            ];
        } elseif (is_array($data)) {
            $output = $data;
        } elseif ($data instanceof \Exception) {
            $output = [
                'Message' => $data->getMessage(),
                'Code' => $data->getCode(),
                'Exception' => get_class($data),
                'Backtrace' => $data->getTraceAsString()
            ];
        } elseif ($data instanceof AbstractResponse) {
            $output = [
                'Message' => $data->getMessage(),
                'Code' => $data->getCode(),
                'Reference' => $data->getTransactionReference(),
                'Data' => $data->getData()
            ];
            if(is_scalar($data->getData()))
            {
                $output["JSONData"] = $data->getData();
            }
        } elseif ($data instanceof AbstractRequest) {
            $output = [
                'Token' => $data->getToken(),
                'CardReference' => $data->getCardReference(),
                'Amount' => $data->getAmount(),
                'Currency' => $data->getCurrency(),
                'Description' => $data->getDescription(),
                'TransactionId' => $data->getTransactionId(),
                'Reference' => $data->getTransactionReference(),
                'ClientIp' => $data->getClientIp(),
                'ReturnUrl' => $data->getReturnUrl(),
                'CancelUrl' => $data->getCancelUrl(),
                'NotifyUrl' => $data->getNotifyUrl(),
                'Parameters' => $data->getParameters()
            ];
        } elseif ($data instanceof NotificationInterface) {
            
            $output = [
                'Message' => $data->getMessage(),
                'Code' => $data->getTransactionStatus(),
                'Reference' => $data->getTransactionReference(),
                'Data' => $data->getData()
            ];
            if(is_scalar($data->getData()))
            {
                $output["JSONData"] = $data->getData();
            }
        }
        
        $output = array_merge($output, [
            'PaymentID' => $this->payment->ID,
            'Gateway' => $this->payment->Gateway
        ]);

        $this->logToFile($output, $type);

        /** @var PaymentMessage $message */
        $message = Injector::inst()->create($type)->update($output);
        $message->write();

        $this->payment->Messages()->add($message);
        return $message;
    }
}