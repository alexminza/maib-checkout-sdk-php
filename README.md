# PHP SDK for maib e-Commerce Checkout API

![maib e-Commerce Checkout](https://repository-images.githubusercontent.com/1125238716/a3cef907-c160-4575-9339-0cce7c89f295)

* maib e-Commerce Checkout API docs: https://docs.maibmerchants.md/checkout
* GitHub project https://github.com/alexminza/maib-checkout-sdk-php
* Composer package https://packagist.org/packages/alexminza/maib-checkout-sdk

## Installation
To easily install or upgrade to the latest release, use `composer`:
```shell
composer require alexminza/maib-checkout-sdk
```

To enable logging add the `monolog` package:
```shell
composer require monolog/monolog
```
## Getting started
Import SDK:

```php
require_once __DIR__ . '/vendor/autoload.php';

use Maib\MaibCheckout\MaibCheckoutClient;
```

Add project configuration:

```php
$DEBUG = getenv('DEBUG');

$MAIB_CHECKOUT_BASE_URI = getenv('MAIB_CHECKOUT_BASE_URI');
$MAIB_CHECKOUT_CLIENT_ID = getenv('MAIB_CHECKOUT_CLIENT_ID');
$MAIB_CHECKOUT_CLIENT_SECRET = getenv('MAIB_CHECKOUT_CLIENT_SECRET');
$MAIB_CHECKOUT_SIGNATURE_KEY = getenv('MAIB_CHECKOUT_SIGNATURE_KEY');
```

Initialize client:

```php
$options = [
    'base_uri' => $MAIB_CHECKOUT_BASE_URI,
    'timeout' => 30
];

if ($DEBUG) {
    $logName = 'maib_checkout_guzzle';
    $logFileName = "$logName.log";

    $log = new \Monolog\Logger($logName);
    $log->pushHandler(new \Monolog\Handler\StreamHandler($logFileName, \Monolog\Logger::DEBUG));

    $stack = \GuzzleHttp\HandlerStack::create();
    $stack->push(\GuzzleHttp\Middleware::log($log, new \GuzzleHttp\MessageFormatter(\GuzzleHttp\MessageFormatter::DEBUG)));

    $options['handler'] = $stack;
}

$guzzleClient = new \GuzzleHttp\Client($options);
$maibCheckoutClient = new MaibCheckoutClient($guzzleClient);
```

## SDK usage examples
### Get Access Token with Client ID and Client Secret

```php
$tokenResponse = $maibCheckoutClient->getToken($MAIB_CHECKOUT_CLIENT_ID, $MAIB_CHECKOUT_CLIENT_SECRET);
$accessToken = $tokenResponse['result']['accessToken'];
```

### Register a new hosted checkout session

```php

$checkoutData = [
    'amount' => 50.61,
    'currency' => 'MDL',
    'orderInfo' => [
        'id' => 'EK123123BV',
        'description' => 'Order description',
        'date' => '2025-11-03T09:28:40.814748+00:00',
        'orderAmount' => null,
        'orderCurrency' => null,
        'deliveryAmount' => null,
        'deliveryCurrency' => null,
        'items' => [
            [
                'externalId' => '243345345',
                'title' => 'Product1',
                'amount' => 50.61,
                'currency' => 'MDL',
                'quantity' => 3,
                'displayOrder' => null,
            ],
            [
                'externalId' => '54353453',
                'title' => 'Product2',
                'amount' => 50.61,
                'currency' => 'MDL',
                'quantity' => 2,
                'displayOrder' => null,
            ],
        ],
    ],
    'payerInfo' => [
        'name' => 'John D.',
        'email' => 'test@gmail.com',
        'phone' => '+37377382716',
        'ip' => '192.168.172.22',
        'userAgent' => 'Mozilla/5.0',
    ],
    'language' => 'ro',
    'callbackUrl' => 'https://example.com/path',
    'successUrl' => 'https://example.com/path',
    'failUrl' => 'https://example.com/path',
];

$checkoutRegisterResponse = $maibCheckoutClient->checkoutRegister($checkoutData, $accessToken);
$checkoutUrl = $checkoutRegisterResponse['result']['checkoutUrl'];
```

### Validate callback signature

```php
// https://docs.maibmerchants.md/checkout/api-reference/callback-notifications#signature-example
$callbackBody = '{"checkoutId": "5a4d27a4-79f5-426b-9403-cccdeee81747","terminalId": "T1234567","amount": 1234.56,"currency": "MDL","completedAt": "2024-11-23T19:35:00.6772285+02:00","payerName": "John","payerEmail": "Smith","payerPhone": "37368473653","payerIp": "192.175.12.22","orderId": "ORDER-2025-0001","orderDescription": "Online purchase of electronics","orderDeliveryAmount": 50.00,"orderDeliveryCurrency": "MDL","paymentId": "379b31a3-8283-43d4-8a7b-eef8c0736a32","paymentAmount": 1234.56,"paymentCurrency": "MDL","paymentStatus": "Executed","paymentExecutedAt": "2025-05-05T23:38:07.2760698+03:00","senderIban": "NL43RABO1438227787","senderName": "Steven","senderCardNumber": "444433******1111","retrievalReferenceNumber": "ABC324353245","processingStatus": "OK","processingStatusCode": "00","approvalCode": "123456","threeDsResult": "Y","threeDsReason": null,"paymentMethod": "Card"}';

$signatureHeader = 'sha256=mtFkCMxfwj9t2wvY/7JASHLw+K/du4mY8azf1lV0ttg=';
$signatureTimestamp = '1761032516817';
$signatureKey = '67be8e54-ac28-485d-9369-27f6d3c55a27';

$validationResult = MaibCheckoutClient::validateCallbackSignature($callbackBody, $signatureHeader, $signatureTimestamp, $signatureKey);
```

### Get checkout details

```php
$checkoutId = $checkoutRegisterResponse['result']['checkoutId'];
$checkoutDetailsResponse = $maibCheckoutClient->checkoutDetails($checkoutId, $accessToken);
```

### Refund payment

```php
$callbackData = json_decode($callbackBody, true);
$paymentId = $callbackData['paymentId'];

$refundData = [
    'amount' => 25.00,
    'reason' => 'Test refund reason',
];

$paymentRefundResponse = $maibCheckoutClient->paymentRefund($paymentId, $refundData, $accessToken);
```

### Get refund details

```php
$refundId = $paymentRefundResponse['result']['refundId'];
$paymentRefundDetailsResponse = $maibCheckoutClient->paymentRefundDetails($refundId, $accessToken);
```
