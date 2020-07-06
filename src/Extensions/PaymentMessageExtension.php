<?php
namespace Omnipay\Wirecard\Message\Payment\Page;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\DataObject;


class PaymentMessageExtension extends Extension
{
    private static $db = [
        "JSONData"  =>  'Text'
    ];
}