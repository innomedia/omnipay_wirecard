<?php
namespace Omnipay\Wirecard\Message\Payment\Page;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Omnipay\Model\Message\PurchasedResponse;
use SilverStripe\Omnipay\Model\Message\PurchaseRedirectResponse;

class PaymentExtension extends Extension
{
    private static $db = [
        "Iban" => 'Text',
        "bic" => 'Text',
        "ExtractionTry" => "Boolean(0)",
    ];
    private static $summary_fields = [
        'Money' => 'Money',
        'GatewayTitle' => 'Gateway',
        'PaymentStatus' => 'Status',
        'Created.Nice' => 'Created',
        "ReadablePaymentType" => 'Methode',
    ];
    public function getReadablePaymentType()
    {
        if ($this->owner->Gateway == "Wirecard_HostedPaymentPage") {

            $message = $this->owner->Messages()->filter("ClassName", PurchasedResponse::class)->first();

            if ($message != null) {
                $data = null;
                if ($this->isJson($message->JSONData)) {
                    $data = json_decode($message->JSONData, true);
                } else {
                    $data = json_decode(base64_decode($message->JSONData), true);
                }

                if (array_key_exists("payment", $data) && array_key_exists("payment-methods", $data["payment"]) && array_key_exists("payment-method", $data["payment"]["payment-methods"])) {
                    return $data["payment"]["payment-methods"]["payment-method"][0]["name"];
                }
            }
        }
        return "";
    }
    public function canRefund($member)
    {
        $this->tryExtractIban_BIC(true);
        if ($this->owner->Gateway == "Wirecard_HostedPaymentPage" && ($this->owner->Iban == "" || $this->owner->bic == "")) {

            if ($this->isDebitPayment()) {
                return false;
            }
        }
    }
    private function tryExtractIban_BIC($write = false)
    {
        if ($this->owner->ExtractionTry == false && ($this->owner->Iban == "" || $this->owner->Iban == null && $this->owner->bic == "" || $this->owner->bic == null)) {
            $message = $this->owner->Messages()->filter("ClassName", PurchasedResponse::class)->first();
            $data = null;
            if ($this->isJson($message->JSONData)) {
                $data = json_decode($message->JSONData, true);
            } else {
                $data = json_decode(base64_decode($message->JSONData), true);
            }
            if (is_array($data) && array_key_exists("payment", $data) && array_key_exists("bank-account", $data["payment"]) && array_key_exists("iban", $data["payment"]["bank-account"])) {
                $this->owner->Iban = $data["payment"]["bank-account"]["iban"];
            }
            if (is_array($data) && array_key_exists("payment", $data) && array_key_exists("bank-account", $data["payment"]) && array_key_exists("bic", $data["payment"]["bank-account"])) {
                $this->owner->bic = $data["payment"]["bank-account"]["bic"];
            }
            if ($write) {
                $this->owner->write();
            }
        }
    }
    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function onBeforeWrite()
    {
        $this->tryExtractIban_BIC();
    }
    private function isDebitPayment()
    {
        $message = $this->owner->Messages()->filter("ClassName", PurchasedResponse::class)->first();

        if ($message != null) {
            if ($message->JSONData == null) {
                $message = $this->owner->Messages()->filter("ClassName", PurchaseRedirectResponse::class)->first();
            }
            $data = json_decode(base64_decode($message->JSONData), true);
            if (array_key_exists("payment", $data) && array_key_exists("transaction-type", $data["payment"]) && $data["payment"]["transaction-type"] == "debit") {
                return true;
            }
        }
        return false;
    }
    public function updateCMSFields(FieldList $fields)
    {
        if ($this->isDebitPayment()) {
            $fields->push(TextField::create('Iban', 'Iban (für "Refunds")'));
            $fields->push(TextField::create('bic', 'BIC (für "Refunds")'));
        }

    }
}
