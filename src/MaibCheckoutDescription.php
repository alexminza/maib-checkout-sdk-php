<?php

namespace Maib\MaibCheckout;

use GuzzleHttp\Command\Guzzle\Description;
use Composer\InstalledVersions;

class MaibCheckoutDescription extends Description
{
    public function __construct(array $options = [])
    {
        $package = 'alexminza/maib-checkout-sdk';
        $version = InstalledVersions::getPrettyVersion($package) ?? 'dev';
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
                'getToken' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/auth/token',
                    'summary' => 'Obtain authentication token',
                    'responseModel' => 'getResponse',
                    'additionalParameters' => [
                        'location' => 'json',
                        'schema' => ['$ref' => 'AuthTokenDto']
                    ]
                ],
                #endregion

                #region Checkout Operations
                'checkoutRegister' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/checkouts',
                    'summary' => 'Register a new hosted checkout session',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                    ],
                    'additionalParameters' => [
                        'location' => 'json',
                        'schema' => ['$ref' => 'CheckoutRegisterDto']
                    ]
                ],
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
                'checkoutList' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/checkouts',
                    'summary' => 'Retrieve all checkouts',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                    ],
                    'additionalParameters' => [
                        'location' => 'query',
                        'schema' => ['$ref' => 'CheckoutListDto']
                    ]
                ],
                #endregion

                #region Payment Operations
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
                'paymentList' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/payments',
                    'summary' => 'Retrieve all payments by filter',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                    ],
                    'additionalParameters' => [
                        'location' => 'query',
                        'schema' => ['$ref' => 'PaymentListDto']
                    ]
                ],
                'paymentRefund' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/payments/{paymentId}/refund',
                    'summary' => 'Refund a payment',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'paymentId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                    'additionalParameters' => [
                        'location' => 'json',
                        'schema' => ['$ref' => 'RefundDto']
                    ]
                ],
                #endregion

                #region Payment Simulation Operations
                'miaTestPay' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/mia/test-pay',
                    'summary' => 'Payment Simulation (Sandbox)',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                    ],
                    'additionalParameters' => [
                        'location' => 'json',
                        'schema' => ['$ref' => 'MiaTestPayDto']
                    ]
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
                'AuthTokenDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'clientId' => ['type' => 'string', 'required' => true],
                        'clientSecret' => ['type' => 'string', 'required' => true],
                    ],
                ],
                'CheckoutRegisterDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'amount' => ['type' => 'number', 'required' => true],
                        'currency' => ['type' => 'string', 'required' => true],
                        'orderInfo' => [
                            'type' => 'object',
                            '$ref' => 'OrderInfoDto',
                            'required' => true
                        ],
                        'payerInfo' => [
                            'type' => 'object',
                            '$ref' => 'PayerInfoDto',
                        ],
                        'language' => ['type' => 'string'],
                        'callbackUrl' => ['type' => 'string', 'required' => true],
                        'successUrl' => ['type' => 'string'],
                        'failUrl' => ['type' => 'string'],
                    ],
                ],
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
                'RefundDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'amount' => ['type' => 'number'],
                        'reason' => ['type' => 'string', 'required' => true],
                        'callbackUrl' => ['type' => 'string'],
                    ],
                ],
                'CheckoutListDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'id' => ['type' => 'string'],
                        'orderId' => ['type' => 'string'],
                        'status' => ['type' => 'string', 'enum' => ['WaitingForInit', 'Initialized', 'PaymentMethodSelected', 'Completed', 'Expired', 'Abandoned', 'Cancelled', 'Failed']],
                        'minAmount' => ['type' => 'number'],
                        'maxAmount' => ['type' => 'number'],
                        'currency' => ['type' => 'string'],
                        'language' => ['type' => 'string'],
                        'createdAtFrom' => ['type' => 'string', 'format' => 'date-time'],
                        'createdAtTo' => ['type' => 'string', 'format' => 'date-time'],
                        'expiresAtFrom' => ['type' => 'string', 'format' => 'date-time'],
                        'expiresAtTo' => ['type' => 'string', 'format' => 'date-time'],

                        'count' => ['type' => 'number'],
                        'offset' => ['type' => 'number'],
                        'sortBy' => ['type' => 'string', 'enum' => ['id', 'orderId', 'status', 'amount', 'currency', 'language', 'createdAt', 'expiresAt']],
                        'order' => ['type' => 'string', 'enum' => ['asc', 'desc']],
                    ],
                ],
                'PaymentListDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'paymentId' => ['type' => 'string'],
                        'paymentIntentId' => ['type' => 'string'],
                        'terminalId' => ['type' => 'string'],
                        'amountFrom' => ['type' => 'number'],
                        'amountTo' => ['type' => 'number'],
                        'currency' => ['type' => 'string'],
                        'orderId' => ['type' => 'string'],
                        'note' => ['type' => 'string'],
                        'status' => ['type' => 'string', 'enum' => ['Executed', 'PartiallyRefunded', 'Refunded', 'Failed']],
                        'executedAtFrom' => ['type' => 'string', 'format' => 'date-time'],
                        'executedAtTo' => ['type' => 'string', 'format' => 'date-time'],
                        'recipientIban' => ['type' => 'string'],
                        'referenceNumber' => ['type' => 'string'],
                        'senderIban' => ['type' => 'string'],
                        'senderName' => ['type' => 'string'],
                        'providerType' => ['type' => 'string', 'enum' => ['QR', 'RTP', 'Card']], //TODO: providerType
                        'mcc' => ['type' => 'string'],
                        'type' => ['type' => 'string', 'enum' => ['MIA']], //TODO: type

                        'count' => ['type' => 'number'],
                        'offset' => ['type' => 'number'],
                        'sortBy' => ['type' => 'string', 'enum' => ['paymentId', 'paymentIntentId', 'terminalId', 'amount', 'currency', 'orderId', 'note', 'status', 'executedAt', 'recipientIban', 'referenceNumber', 'senderIban', 'senderName', 'providerType', 'mcc', 'type']],
                        'order' => ['type' => 'string', 'enum' => ['asc', 'desc']],
                    ],
                ],
                'MiaTestPayDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'qrId' => ['type' => 'string', 'required' => true],
                        'amount' => ['type' => 'number', 'required' => true],
                        'iban' => ['type' => 'string', 'required' => true],
                        'currency' => ['type' => 'string', 'enum' => ['MDL'], 'required' => true],
                        'payerName' => ['type' => 'string', 'required' => true],
                    ],
                ],
            ]
        ];

        parent::__construct($description, $options);
    }
}
