<?php

use SilverStripe\ORM\DataObject;

class WireCardRefundData extends DataObject
{
    private static $db = [
        "parenttransactionid"   =>  'Text',
        "transactiontype"   =>  'Text'
    ];
}