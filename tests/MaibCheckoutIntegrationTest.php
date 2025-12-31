<?php

namespace Maib\MaibCheckout\Tests;

use Maib\MaibCheckout\MaibCheckoutClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

/**
 * @group integration
 */
class MaibCheckoutIntegrationTest extends TestCase
{
    protected static $clientId;
    protected static $clientSecret;
    protected static $signatureKey;
    protected static $baseUrl;

    // Shared state
    protected static $accessToken;
    protected static $checkoutId;
    protected static $checkoutData;
    protected static $paymentId;

    /**
     * @var MaibCheckoutClient
     */
    protected $client;

    public static function setUpBeforeClass(): void
    {
        self::$clientId = getenv('MAIB_CHECKOUT_CLIENT_ID');
        self::$clientSecret = getenv('MAIB_CHECKOUT_CLIENT_SECRET');
        self::$signatureKey = getenv('MAIB_CHECKOUT_SIGNATURE_KEY');
        self::$baseUrl = MaibCheckoutClient::SANDBOX_BASE_URL;

        if (!self::$clientId || !self::$clientSecret || !self::$signatureKey) {
            self::markTestSkipped('Integration test credentials not provided.');
        }
    }

    protected function setUp(): void
    {
        $options = [
            'base_uri' => self::$baseUrl,
            'timeout' => 15
        ];

        #region Logging
        $classParts = explode('\\', self::class);
        $logName = end($classParts) . '_guzzle';
        $logFileName = "$logName.log";

        $log = new \Monolog\Logger($logName);
        $log->pushHandler(new \Monolog\Handler\StreamHandler($logFileName, \Monolog\Logger::DEBUG));

        $stack = \GuzzleHttp\HandlerStack::create();
        $stack->push(\GuzzleHttp\Middleware::log($log, new \GuzzleHttp\MessageFormatter(\GuzzleHttp\MessageFormatter::DEBUG)));

        $options['handler'] = $stack;
        #endregion

        $this->client = new MaibCheckoutClient(new Client($options));
    }

    protected function onNotSuccessfulTest(\Throwable $t): void
    {
        if ($this->isDebugMode()) {
            // https://github.com/guzzle/guzzle/issues/2185
            if ($t instanceof \GuzzleHttp\Command\Exception\CommandException) {
                $response = $t->getResponse();
                $responseBody = (string) $response->getBody();
                $exceptionMessage = $t->getMessage();

                $this->debugLog($responseBody, $exceptionMessage);
            }
        }

        parent::onNotSuccessfulTest($t);
    }

    protected function isDebugMode()
    {
        // https://stackoverflow.com/questions/12610605/is-there-a-way-to-tell-if-debug-or-verbose-was-passed-to-phpunit-in-a-test
        return in_array('--debug', $_SERVER['argv'] ?? []);
    }

    protected function debugLog($message, $data)
    {
        $data_print = print_r($data, true);
        error_log("$message: $data_print");
    }

    protected function assertResultOk($response)
    {
        $this->assertNotNull($response);
        $this->assertArrayHasKey('ok', $response);
        $this->assertTrue($response['ok']);
        $this->assertArrayHasKey('result', $response);
        $this->assertNotEmpty($response['result']);
    }

    protected function assertResultNotOk($response)
    {
        $this->assertNotNull($response);
        $this->assertArrayHasKey('ok', $response);
        $this->assertFalse($response['ok']);
        $this->assertArrayHasKey('errors', $response);
        $this->assertNotEmpty($response['errors']);
    }

    public function testAuthenticate()
    {
        $response = $this->client->getToken(self::$clientId, self::$clientSecret);
        // $this->debugLog('getToken', $response);

        $this->assertResultOk($response);
        $this->assertNotEmpty($response['result']['accessToken']);

        self::$accessToken = $response['result']['accessToken'];
    }

    #region Checkout
    /**
     * @depends testAuthenticate
     */
    public function testCheckoutRegister()
    {
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

        $response = $this->client->checkoutRegister($checkoutData, self::$accessToken);
        // $this->debugLog('checkoutRegister', $response);

        $this->assertResultOk($response);
        $this->assertNotEmpty($response['result']['checkoutId']);
        $this->assertNotEmpty($response['result']['checkoutUrl']);

        $this->debugLog('checkoutUrl', $response['result']['checkoutUrl']);

        self::$checkoutId = $response['result']['checkoutId'];
        self::$checkoutData = $checkoutData;
    }

    /**
     * @depends testCheckoutRegister
     */
    public function testCheckoutDetails()
    {
        $response = $this->client->checkoutDetails(self::$checkoutId, self::$accessToken);
        // $this->debugLog('checkoutDetails', $response);

        $this->assertResultOk($response);
        $this->assertEquals(self::$checkoutId, $response['result']['id']);
        $this->assertEquals('WaitingForInit', $response['result']['status']);
        $this->assertEquals(self::$checkoutData['amount'], $response['result']['amount']);
        $this->assertEquals(self::$checkoutData['currency'], $response['result']['currency']);
    }

    /**
     * @depends testCheckoutRegister
     */
    public function testCheckoutCancel()
    {
        $response = $this->client->checkoutCancel(self::$checkoutId, self::$accessToken);
        // $this->debugLog('checkoutCancel', $response);

        $this->assertResultOk($response);
        $this->assertEquals(self::$checkoutId, $response['result']['checkoutId']);
        $this->assertEquals('Cancelled', $response['result']['status']);
    }

    /**
     * @depends testAuthenticate
     */
    public function testCheckoutList()
    {
        $checkoutListData = [
            'count' => 10,
            'offset' => 0,
            'minAmount' => 10.00,
            'maxAmount' => 100.00,
            // 'sortBy' => 'createdAt', //TODO: payments.acquiring.shared.api-0001001 Endpoint has been interrupted with an exception
            'order' => 'desc'
        ];

        $response = $this->client->checkoutList($checkoutListData, self::$accessToken);
        // $this->debugLog('checkoutList', $response);

        $this->assertResultOk($response);
        $this->assertArrayHasKey('items', $response['result']);
        $this->assertArrayHasKey('totalCount', $response['result']);
    }
    #endregion

    #region Payment
    /**
     * @depends testCheckoutRegister
     */
    public function testMiaTestPay()
    {
        $this->markTestSkipped();

        $testPayData = [
            'qrId' => null, //TODO: qrId
            'amount' => self::$checkoutData['amount'],
            'currency' => self::$checkoutData['currency'],
            'iban' => 'MD88AG000000011621810140',
            'payerName' => 'TEST QR PAYMENT'
        ];

        $response = $this->client->miaTestPay($testPayData, self::$accessToken);
        // $this->debugLog('testPay', $response);

        $this->assertResultOk($response);
        $this->assertEquals(null, $response['result']['qrId']); //TODO: qrId
        $this->assertEquals('Paid', $response['result']['qrStatus']);
        $this->assertEquals(self::$checkoutData['amount'], $response['result']['amount']);
        $this->assertEquals(self::$checkoutData['currency'], $response['result']['currency']);
        $this->assertNotEmpty($response['result']['payId']);

        self::$paymentId = $response['result']['paymentId'];
    }

    /**
     * @depends testMiaTestPay
     */
    public function testPaymentDetails()
    {
        $response = $this->client->paymentDetails(self::$paymentId, self::$accessToken);
        // $this->debugLog('paymentDetails', $response);

        $this->assertResultOk($response);
        $this->assertEquals(self::$paymentId, $response['result']['paymentId']);
        $this->assertEquals('Executed', $response['result']['status']);
        $this->assertEquals(self::$checkoutData['amount'], $response['result']['amount']);
        $this->assertEquals(self::$checkoutData['currency'], $response['result']['currency']);
    }

    /**
     * @depends testAuthenticate
     */
    public function testPaymentList()
    {
        $params = [
            'count' => 10,
            'offset' => 0,
            // 'paymentId' => self::$paymentId,
            'sortBy' => 'executedAt',
            'order' => 'asc'
        ];

        $response = $this->client->paymentList($params, self::$accessToken);
        // $this->debugLog('paymentList', $response);

        $this->assertResultOk($response);
        $this->assertArrayHasKey('items', $response['result']);
        $this->assertArrayHasKey('totalCount', $response['result']);
    }

    /**
     * @depends testMiaTestPay
     */
    public function testPaymentRefundPartial()
    {
        $refundData = [
            'amount' => self::$checkoutData['amount'] / 2,
            'reason' => 'testPaymentRefundPartial reason',
            'callbackUrl' => 'https://example.com/refund'
        ];

        $response = $this->client->paymentRefund(self::$paymentId, $refundData, self::$accessToken);
        // $this->debugLog('paymentRefund', $response);

        $this->assertResultOk($response);
        $this->assertNotEmpty($response['result']['refundId']);
        $this->assertEquals('Created', $response['result']['status']);
    }

    /**
     * @depends testPaymentRefundPartial
     */
    public function testPaymentRefundFull()
    {
        $refundData = [
            'reason' => 'testPaymentRefundFull reason',
            'callbackUrl' => 'https://example.com/refund'
        ];

        $response = $this->client->paymentRefund(self::$paymentId, $refundData, self::$accessToken);
        // $this->debugLog('paymentRefund', $response);

        $this->assertResultOk($response);
        $this->assertNotEmpty($response['result']['refundId']);
        $this->assertEquals('Created', $response['result']['status']);
    }

    /**
     * @depends testPaymentRefundFull
     */
    public function testPaymentRefundError()
    {
        $this->markTestSkipped();

        $refundData = [
            'reason' => 'testRefundPaymentError reason',
            'callbackUrl' => 'https://example.com/refund'
        ];

        $response = $this->client->paymentRefund(self::$paymentId, $refundData, self::$accessToken);
        // $this->debugLog('paymentRefund', $response);

        $this->assertResultNotOk($response);
        $this->assertEquals('payments.acquiring.payments-01001', $response['errors'][0]['errorCode']);
    }
    #endregion

    #region Signature
    const CALLBACK_EXAMPLE = '{"checkoutId":"5a4d27a4-79f5-426b-9403-cccdeee81747","paymentIntentId":"baa2a48d-b3ba-48b8-917e-07607d447c4f","merchantId":"37e48a96-37d7-49b3-8373-2e7e69ef8c2e","terminalId":"23456543","amount":193.54,"currency":"MDL","completedAt":"2024-11-23T19:35:00.6772285+02:00","payerName":"John","payerEmail":"Smith","payerPhone":"37368473653","payerIp":"192.175.12.22","orderId":"1142353","orderDescription":"OrderDescriptiondda760d7-a318-451b-8e47-f3377c06dcf5","orderDeliveryAmount":92.65,"orderDeliveryCurrency":8,"paymentId":"379b31a3-8283-43d4-8a7b-eef8c0736a32","paymentAmount":64.76,"paymentCurrency":"MDL","paymentStatus":"Executed","paymentExecutedAt":"2025-05-05T23:38:07.2760698+03:00","providerType":"Ips","senderIban":"NL43RABO1438227787","senderName":"Steven","senderCardNumber":"444433******1111","retrievalReferenceNumber":"ABC324353245"}';

    public function testValidateCallbackSignatureExample()
    {
        // https://docs.maibmerchants.md/checkout/api-reference/callback-notifications
        $callbackBody = self::CALLBACK_EXAMPLE;
        $signatureHeader = 'sha256=h7/NNr0+SVwqfc1seJNl/m4M4/wzBiZwKHjE1gbmMKA=';
        $signatureTimestamp = '1761032516817';
        $signatureKey = '67be8e54-ac28-485d-9369-27f6d3c55a27';

        $this->assertTrue(MaibCheckoutClient::validateCallbackSignature($callbackBody, $signatureHeader, $signatureTimestamp, $signatureKey));
    }

    public function testValidateCallbackSignature()
    {
        // https://docs.maibmerchants.md/checkout/api-reference/callback-notifications
        $callbackBody = self::CALLBACK_EXAMPLE;
        $signatureTimestamp = time();

        $signature = MaibCheckoutClient::computeCallbackSignature($callbackBody, $signatureTimestamp, self::$signatureKey);
        $signatureHeader = "sha256=$signature";

        $this->assertTrue(MaibCheckoutClient::validateCallbackSignature($callbackBody, $signatureHeader, $signatureTimestamp, self::$signatureKey));
    }
    #endregion
}
