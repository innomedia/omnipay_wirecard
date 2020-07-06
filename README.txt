Quick and very dirty implementation for Omnipay wirecard (does not work OTB)
Supports Wirecard CreditCard, EPS,Giropay and Sofortbanking
Function implemented purchase (works live), refund (works with TestCredentials)

Setup

SilverStripe\Omnipay\GatewayInfo:
  Wirecard_HostedPaymentPage:
    parameters:
      testMode: true
      CreditCard:
        MerchantAccountID: 7a6dd74f-06ab-4f3f-a864-adc52687270a
        UserName: 70000-APIDEMO-CARD
        Password: ohysS0-dvfMx
        shopId: 3D
      EPS:
        MerchantAccountID: 1f629760-1a66-4f83-a6b4-6a35620b4a6d
        UserName: 16390-testing
        Password: 3!3013=D3fD8X7
        shopId: 3D
      Giropay:
        MerchantAccountID: 9b4b0e5f-1bc8-422e-be42-d0bad2eadabc
        UserName: 16390-testing
        Password: 3!3013=D3fD8X7
        shopId: 3D
      Sofortbanking:
        MerchantAccountID: f19d17a2-01ae-11e2-9085-005056a96a54
        UserName: 70000-APITEST-AP
        Password: qD2wzQ_hrc!8
        shopId: 3D

Needs to adapt the getTransactionType/SetTransactionType in Purchase/Refund Request and supply it with either "purchase" for CC or "debit" for the other 3

For more Test credentials visit https://doc.wirecard.com

