<?php

declare(strict_types=1);

namespace Maib\MaibCheckout;

use GuzzleHttp\Command\Guzzle\Description;
use Composer\InstalledVersions;

class MaibCheckoutDescription extends Description
{
    private const PACKAGE_NAME = 'alexminza/maib-checkout-sdk';
    private const DEFAULT_VERSION = 'dev';

    private static function detectVersion(): string
    {
        if (!class_exists(InstalledVersions::class)) {
            return self::DEFAULT_VERSION;
        }

        if (!InstalledVersions::isInstalled(self::PACKAGE_NAME)) {
            return self::DEFAULT_VERSION;
        }

        return InstalledVersions::getPrettyVersion(self::PACKAGE_NAME)
            ?? self::DEFAULT_VERSION;
    }

    public function __construct(array $options = [])
    {
        $version = self::detectVersion();
        $userAgent = "maib-checkout-sdk-php/$version";

        $authorizationHeader = [
            'type' => 'string',
            'location' => 'header',
            'sentAs' => 'Authorization',
            'summary' => 'Bearer Authentication with JWT Token',
            'required' => true,
        ];

        $description = [
            //'baseUrl' => 'https://api.maibmerchants.md/',
            'name' => 'maib e-Commerce Checkout API',
            'apiVersion' => 'v2',

            'operations' => [
                'baseOp' => [
                    'parameters' => [
                        'User-Agent' => [
                            'location' => 'header',
                            'default'  => $userAgent,
                        ],
                    ],
                ],

                #region Authentication Operations
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/authentication/obtain-authentication-token
                'getToken' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/auth/token',
                    'summary' => 'Obtain authentication token',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'clientId' => ['type' => 'string', 'location' => 'json', 'required' => true],
                        'clientSecret' => ['type' => 'string', 'location' => 'json', 'required' => true],
                    ],
                ],
                #endregion

                #region Checkout Operations
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/register-a-new-hosted-checkout-session
                'checkoutRegister' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/checkouts',
                    'summary' => 'Register a new hosted checkout session',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'amount' => ['type' => 'number', 'location' => 'json', 'required' => true],
                        'currency' => ['type' => 'string', 'location' => 'json', 'required' => true],
                        'orderInfo' => [
                            'type' => 'object',
                            'location' => 'json',
                            'required' => true,
                            '$ref' => 'OrderInfoDto'
                        ],
                        'payerInfo' => [
                            'type' => 'object',
                            'location' => 'json',
                            '$ref' => 'PayerInfoDto'
                        ],
                        'language' => ['type' => 'string', 'location' => 'json'],
                        'callbackUrl' => ['type' => 'string', 'location' => 'json', 'required' => true],
                        'successUrl' => ['type' => 'string', 'location' => 'json'],
                        'failUrl' => ['type' => 'string', 'location' => 'json'],
                    ],
                ],
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/cancel-a-checkout-session
                'checkoutCancel' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/checkouts/{checkoutId}/cancel',
                    'summary' => 'Cancel a checkout session',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'checkoutId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                ],
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/get-checkout-details
                'checkoutDetails' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/checkouts/{checkoutId}',
                    'summary' => 'Get checkout details',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'checkoutId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                ],
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/retrieve-all-checkouts
                'checkoutList' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/checkouts',
                    'summary' => 'Retrieve all checkouts',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'id' => ['type' => 'string', 'location' => 'query'],
                        'orderId' => ['type' => 'string', 'location' => 'query'],
                        'status' => ['type' => 'string', 'location' => 'query', 'enum' => ['WaitingForInit', 'Initialized', 'PaymentMethodSelected', 'Completed', 'Expired', 'Abandoned', 'Cancelled', 'Failed']],
                        'minAmount' => ['type' => 'number', 'location' => 'query'],
                        'maxAmount' => ['type' => 'number', 'location' => 'query'],
                        'currency' => ['type' => 'string', 'location' => 'query'],
                        'language' => ['type' => 'string', 'location' => 'query'],
                        'createdAtFrom' => ['type' => 'string', 'location' => 'query', 'format' => 'date-time'],
                        'createdAtTo' => ['type' => 'string', 'location' => 'query', 'format' => 'date-time'],
                        'expiresAtFrom' => ['type' => 'string', 'location' => 'query', 'format' => 'date-time'],
                        'expiresAtTo' => ['type' => 'string', 'location' => 'query', 'format' => 'date-time'],
                        'count' => ['type' => 'number', 'location' => 'query'],
                        'offset' => ['type' => 'number', 'location' => 'query'],
                        'sortBy' => ['type' => 'string', 'location' => 'query'], // 'enum' => ['id', 'orderId', 'status', 'amount', 'currency', 'language', 'createdAt', 'expiresAt']
                        'order' => ['type' => 'string', 'location' => 'query', 'enum' => ['asc', 'desc']],
                    ],
                ],
                #endregion

                #region Payment Operations
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/get-payment-by-id
                'paymentDetails' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/payments/{paymentId}',
                    'summary' => 'Get payment by id',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'paymentId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                ],
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/retrieve-all-payments-by-filter
                'paymentList' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/payments',
                    'summary' => 'Retrieve all payments by filter',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'paymentId' => ['type' => 'string', 'location' => 'query'],
                        'paymentIntentId' => ['type' => 'string', 'location' => 'query'],
                        'terminalId' => ['type' => 'string', 'location' => 'query'],
                        'amountFrom' => ['type' => 'number', 'location' => 'query'],
                        'amountTo' => ['type' => 'number', 'location' => 'query'],
                        'currency' => ['type' => 'string', 'location' => 'query'],
                        'orderId' => ['type' => 'string', 'location' => 'query'],
                        'note' => ['type' => 'string', 'location' => 'query'],
                        'status' => ['type' => 'string', 'location' => 'query', 'enum' => ['Executed', 'PartiallyRefunded', 'Refunded', 'Failed']],
                        'executedAtFrom' => ['type' => 'string', 'location' => 'query', 'format' => 'date-time'],
                        'executedAtTo' => ['type' => 'string', 'location' => 'query', 'format' => 'date-time'],
                        'recipientIban' => ['type' => 'string', 'location' => 'query'],
                        'referenceNumber' => ['type' => 'string', 'location' => 'query'],
                        'senderIban' => ['type' => 'string', 'location' => 'query'],
                        'senderName' => ['type' => 'string', 'location' => 'query'],
                        'providerType' => ['type' => 'string', 'location' => 'query'],
                        'mcc' => ['type' => 'string', 'location' => 'query'],
                        'type' => ['type' => 'string', 'location' => 'query'],
                        'count' => ['type' => 'number', 'location' => 'query'],
                        'offset' => ['type' => 'number', 'location' => 'query'],
                        'sortBy' => ['type' => 'string', 'location' => 'query', 'enum' => ['paymentId', 'paymentIntentId', 'terminalId', 'amount', 'currency', 'orderId', 'note', 'status', 'executedAt', 'recipientIban', 'referenceNumber', 'senderIban', 'senderName', 'providerType', 'mcc', 'type']],
                        'order' => ['type' => 'string', 'location' => 'query', 'enum' => ['asc', 'desc']],
                    ],
                ],
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/refund-a-payment
                'paymentRefund' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/payments/{paymentId}/refund',
                    'summary' => 'Refund a payment',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'paymentId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                        'amount' => ['type' => 'number', 'location' => 'json'],
                        'reason' => ['type' => 'string', 'location' => 'json', 'required' => true],
                        'callbackUrl' => ['type' => 'string', 'location' => 'json'],
                    ],
                ],
                #endregion

                #region Payment Simulation Operations
                // https://docs.maibmerchants.md/checkout/api-reference/sandbox-simulation-environment
                // https://docs.maibmerchants.md/mia-qr-api/en/payment-simulation-sandbox
                'miaTestPay' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/mia/test-pay',
                    'summary' => 'Payment Simulation (Sandbox)',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'qrId' => ['type' => 'string', 'location' => 'json', 'required' => true],
                        'amount' => ['type' => 'number', 'location' => 'json', 'required' => true],
                        'iban' => ['type' => 'string', 'location' => 'json', 'required' => true],
                        'currency' => ['type' => 'string', 'location' => 'json', 'enum' => ['MDL'], 'required' => true],
                        'payerName' => ['type' => 'string', 'location' => 'json', 'required' => true],
                    ],
                ],
                #endregion
            ],

            'models' => [
                'getResponse' => [
                    'type' => 'object',
                    'additionalProperties' => [
                        'location' => 'json'
                    ]
                ],
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/register-a-new-hosted-checkout-session#orderinfo-object
                'OrderInfoDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'id' => ['type' => 'string', 'required' => true],
                        'description' => ['type' => 'string', 'required' => true],
                        'date' => ['type' => 'string', 'format' => 'date-time', 'required' => true],
                        'orderAmount' => ['type' => 'number'],
                        'orderCurrency' => ['type' => 'string'],
                        'deliveryAmount' => ['type' => 'number'],
                        'deliveryCurrency' => ['type' => 'string'],
                        'items' => [
                            'type' => 'array',
                            'required' => true,
                            'items' => [
                                'type' => 'object',
                                '$ref' => 'OrderInfoItemDto',
                            ],
                        ]
                    ],
                ],
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/register-a-new-hosted-checkout-session#orderinfo.items
                'OrderInfoItemDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'externalId' => ['type' => 'string', 'required' => true],
                        'title' => ['type' => 'string', 'required' => true],
                        'amount' => ['type' => 'number', 'required' => true],
                        'currency' => ['type' => 'string', 'required' => true],
                        'quantity' => ['type' => 'number', 'required' => true],
                        'displayOrder' => ['type' => 'integer'],
                    ],
                ],
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/register-a-new-hosted-checkout-session#payerinfo-object
                'PayerInfoDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'name' => ['type' => 'string', 'required' => true],
                        'email' => ['type' => 'string', 'required' => true],
                        'phone' => ['type' => 'string', 'required' => true],
                        'ip' => ['type' => 'string', 'required' => true],
                        'userAgent' => ['type' => 'string', 'required' => true],
                    ],
                ],
            ]
        ];

        parent::__construct($description, $options);
    }
}
