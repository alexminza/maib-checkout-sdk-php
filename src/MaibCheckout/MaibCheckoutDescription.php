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

        $models = [
            #region Generic Models
            'getResponse' => [
                'type' => 'object',
                'location' => 'json',
                'additionalProperties' => [
                    'location' => 'json'
                ]
            ],
            #endregion

            #region Schema-based Models
            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/authentication/obtain-authentication-token#request-parameters-body
            'AuthTokenDto' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'clientId' => ['type' => 'string', 'required' => true],
                    'clientSecret' => ['type' => 'string', 'required' => true],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/register-a-new-hosted-checkout-session#request
            'CheckoutRegisterDto' => [
                'type' => 'object',
                'location' => 'json',
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
                'additionalProperties' => ['location' => 'json'],
            ],
            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/register-a-new-hosted-checkout-session#orderinfo-object
            'OrderInfoDto' => [
                'type' => 'object',
                'location' => 'json',
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
                'additionalProperties' => ['location' => 'json'],
            ],
            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/register-a-new-hosted-checkout-session#orderinfo.items
            'OrderInfoItemDto' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'externalId' => ['type' => 'string', 'required' => true],
                    'title' => ['type' => 'string', 'required' => true],
                    'amount' => ['type' => 'number', 'required' => true],
                    'currency' => ['type' => 'string', 'required' => true],
                    'quantity' => ['type' => 'integer', 'required' => true],
                    'displayOrder' => ['type' => 'integer'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/register-a-new-hosted-checkout-session#payerinfo-object
            'PayerInfoDto' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'name' => ['type' => 'string', 'required' => true],
                    'email' => ['type' => 'string', 'required' => true],
                    'phone' => ['type' => 'string', 'required' => true],
                    'ip' => ['type' => 'string', 'required' => true],
                    'userAgent' => ['type' => 'string', 'required' => true],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/refund-a-payment#request
            'RefundDto' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'amount' => ['type' => 'number'],
                    'reason' => ['type' => 'string', 'required' => true],
                    'callbackUrl' => ['type' => 'string'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/retrieve-all-checkouts#request
            'CheckoutListDto' => [
                'type' => 'object',
                'location' => 'query',
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
                    'count' => ['type' => 'integer'],
                    'offset' => ['type' => 'integer'],
                    'sortBy' => ['type' => 'string'], //TODO: 'enum' => ['id', 'orderId', 'status', 'amount', 'currency', 'language', 'createdAt', 'expiresAt']
                    'order' => ['type' => 'string', 'enum' => ['asc', 'desc']],
                ],
                'additionalProperties' => ['location' => 'query'],
            ],
            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/retrieve-all-payments-by-filter#request
            'PaymentListDto' => [
                'type' => 'object',
                'location' => 'query',
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
                    'providerType' => ['type' => 'string'],
                    'mcc' => ['type' => 'string'],
                    'type' => ['type' => 'string'],
                    'count' => ['type' => 'integer'],
                    'offset' => ['type' => 'integer'],
                    'sortBy' => ['type' => 'string', 'enum' => ['paymentId', 'paymentIntentId', 'terminalId', 'amount', 'currency', 'orderId', 'note', 'status', 'executedAt', 'recipientIban', 'referenceNumber', 'senderIban', 'senderName', 'providerType', 'mcc', 'type']],
                    'order' => ['type' => 'string', 'enum' => ['asc', 'desc']],
                ],
                'additionalProperties' => ['location' => 'query'],
            ],
            // https://docs.maibmerchants.md/mia-qr-api/en/payment-simulation-sandbox#request-parameters-body-json
            'MiaTestPayDto' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'qrId' => ['type' => 'string', 'required' => true],
                    'amount' => ['type' => 'number', 'required' => true],
                    'iban' => ['type' => 'string', 'required' => true],
                    'currency' => ['type' => 'string', 'enum' => ['MDL'], 'required' => true],
                    'payerName' => ['type' => 'string', 'required' => true],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            #endregion

            #region Response Models
            'OperationError' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'errorCode' => ['type' => 'string'],
                    'errorMessage' => ['type' => 'string'],
                    'errorArgs' => ['type' => 'object', 'additionalProperties' => ['type' => 'string']],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],

            'GenerateTokenResponse' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'accessToken' => ['type' => 'string'],
                    'expiresIn' => ['type' => 'integer'],
                    'tokenType' => ['type' => 'string'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            'OperationResultOfGenerateTokenResponse' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'ok' => ['type' => 'boolean'],
                    'errors' => ['type' => 'array', 'items' => ['$ref' => 'OperationError']],
                    'result' => ['$ref' => 'GenerateTokenResponse'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],

            'CreateCheckoutResponse' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'checkoutId' => ['type' => 'string'],
                    'checkoutUrl' => ['type' => 'string'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            'OperationResultOfCreateCheckoutResponse' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'ok' => ['type' => 'boolean'],
                    'errors' => ['type' => 'array', 'items' => ['$ref' => 'OperationError']],
                    'result' => ['$ref' => 'CreateCheckoutResponse'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],

            'CancelCheckoutResult' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'checkoutId' => ['type' => 'string'],
                    'status' => ['type' => 'string'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            'OperationResultOfCancelCheckoutResult' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'ok' => ['type' => 'boolean'],
                    'errors' => ['type' => 'array', 'items' => ['$ref' => 'OperationError']],
                    'result' => ['$ref' => 'CancelCheckoutResult'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],

            'CheckoutModel' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'createdAt' => ['type' => 'string'],
                    'merchantId' => ['type' => 'string'],
                    'paymentIntentId' => ['type' => 'string'],
                    'status' => ['type' => 'string'],
                    'redirectUrl' => ['type' => 'string'],
                    'amount' => ['type' => 'number'],
                    'currency' => ['type' => 'string'],
                    'order' => ['type' => 'object', 'additionalProperties' => true],
                    'expiresAt' => ['type' => 'string'],
                    'paymentMethods' => ['type' => 'array', 'items' => ['type' => 'object', 'additionalProperties' => true]],
                    'paymentIntent' => ['type' => 'object', 'additionalProperties' => true],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            'OperationResultOfCheckoutModel' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'ok' => ['type' => 'boolean'],
                    'errors' => ['type' => 'array', 'items' => ['$ref' => 'OperationError']],
                    'result' => ['$ref' => 'CheckoutModel'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],

            'PagedListOfCheckoutModel' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'items' => ['type' => 'array', 'items' => ['$ref' => 'CheckoutModel']],
                    'count' => ['type' => 'integer'],
                    'totalCount' => ['type' => 'integer'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            'OperationResultOfPagedListOfCheckoutModel' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'ok' => ['type' => 'boolean'],
                    'errors' => ['type' => 'array', 'items' => ['$ref' => 'OperationError']],
                    'result' => ['$ref' => 'PagedListOfCheckoutModel'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],

            'GetPaymentResponse' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'paymentId' => ['type' => 'string'],
                    'paymentIntentId' => ['type' => 'string'],
                    'executedAt' => ['type' => 'string'],
                    'status' => ['type' => 'string'],
                    'amount' => ['type' => 'number'],
                    'currency' => ['type' => 'string'],
                    'type' => ['type' => 'string'],
                    'providerType' => ['type' => 'string'],
                    'senderName' => ['type' => 'string'],
                    'senderIban' => ['type' => 'string'],
                    'recipientIban' => ['type' => 'string'],
                    'referenceNumber' => ['type' => 'string'],
                    'mcc' => ['type' => 'string'],
                    'orderId' => ['type' => 'string'],
                    'terminalId' => ['type' => 'string'],
                    'refundedAmount' => ['type' => 'number'],
                    'requestedRefundAmount' => ['type' => 'number'],
                    'firstRefundedAt' => ['type' => 'string'],
                    'lastRefundedAt' => ['type' => 'string'],
                    'note' => ['type' => 'string'],
                    'isRefundable' => ['type' => 'boolean'],
                    'partialRefundAvailable' => ['type' => 'boolean'],
                    'paymentEntryPoint' => ['type' => 'string'],
                    'refundableAmount' => ['type' => 'number'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            'OperationResultOfGetPaymentResponse' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'ok' => ['type' => 'boolean'],
                    'errors' => ['type' => 'array', 'items' => ['$ref' => 'OperationError']],
                    'result' => ['$ref' => 'GetPaymentResponse'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],

            'GetAllPaymentsResponse' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'items' => ['type' => 'array', 'items' => ['$ref' => 'GetPaymentResponse']],
                    'totalCount' => ['type' => 'integer'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            'OperationResultOfGetAllPaymentsResponse' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'ok' => ['type' => 'boolean'],
                    'errors' => ['type' => 'array', 'items' => ['$ref' => 'OperationError']],
                    'result' => ['$ref' => 'GetAllPaymentsResponse'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],

            'CreateRefundResponse' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'refundId' => ['type' => 'string'],
                    'status' => ['type' => 'string'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            'OperationResultOfCreateRefundResponse' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'ok' => ['type' => 'boolean'],
                    'errors' => ['type' => 'array', 'items' => ['$ref' => 'OperationError']],
                    'result' => ['$ref' => 'CreateRefundResponse'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],

            'MiaTestPayResponse' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'qrId' => ['type' => 'string'],
                    'qrStatus' => ['type' => 'string'],
                    'amount' => ['type' => 'number'],
                    'currency' => ['type' => 'string'],
                    'payId' => ['type' => 'string'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            'OperationResultOfMiaTestPayResponse' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'ok' => ['type' => 'boolean'],
                    'errors' => ['type' => 'array', 'items' => ['$ref' => 'OperationError']],
                    'result' => ['$ref' => 'MiaTestPayResponse'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            #endregion
        ];

        $description = [
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
                    'responseModel' => 'OperationResultOfGenerateTokenResponse',
                    'parameters' => self::getProperties($models, 'AuthTokenDto'),
                    'additionalParameters' => ['location' => 'json'],
                ],
                #endregion

                #region Checkout Operations
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/register-a-new-hosted-checkout-session
                'checkoutRegister' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/checkouts',
                    'summary' => 'Register a new hosted checkout session',
                    'responseModel' => 'OperationResultOfCreateCheckoutResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                    ], self::getProperties($models, 'CheckoutRegisterDto')),
                    'additionalParameters' => ['location' => 'json'],
                ],
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/cancel-a-checkout-session
                'checkoutCancel' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/checkouts/{checkoutId}/cancel',
                    'summary' => 'Cancel a checkout session',
                    'responseModel' => 'OperationResultOfCancelCheckoutResult',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'checkoutId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                    'additionalParameters' => ['location' => 'json'],
                ],
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/get-checkout-details
                'checkoutDetails' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/checkouts/{checkoutId}',
                    'summary' => 'Get checkout details',
                    'responseModel' => 'OperationResultOfCheckoutModel',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'checkoutId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                    'additionalParameters' => ['location' => 'query'],
                ],
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/retrieve-all-checkouts
                'checkoutList' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/checkouts',
                    'summary' => 'Retrieve all checkouts',
                    'responseModel' => 'OperationResultOfPagedListOfCheckoutModel',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                    ], self::getProperties($models, 'CheckoutListDto', 'query')),
                    'additionalParameters' => ['location' => 'query'],
                ],
                #endregion

                #region Payment Operations
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/get-payment-by-id
                'paymentDetails' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/payments/{paymentId}',
                    'summary' => 'Get payment by id',
                    'responseModel' => 'OperationResultOfGetPaymentResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'paymentId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                    'additionalParameters' => ['location' => 'query'],
                ],
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/retrieve-all-payments-by-filter
                'paymentList' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/payments',
                    'summary' => 'Retrieve all payments by filter',
                    'responseModel' => 'OperationResultOfGetAllPaymentsResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                    ], self::getProperties($models, 'PaymentListDto', 'query')),
                    'additionalParameters' => ['location' => 'query'],
                ],
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/refund-a-payment
                'paymentRefund' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/payments/{paymentId}/refund',
                    'summary' => 'Refund a payment',
                    'responseModel' => 'OperationResultOfCreateRefundResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                        'paymentId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ], self::getProperties($models, 'RefundDto')),
                    'additionalParameters' => ['location' => 'json'],
                ],
                #endregion

                #region Payment Simulation Operations
                // https://docs.maibmerchants.md/mia-qr-api/en/endpoints/information-retrieval-get/display-list-of-qr-codes-with-filtering-options
                'miaTestPay' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/mia/test-pay',
                    'summary' => 'Payment Simulation (Sandbox)',
                    'responseModel' => 'OperationResultOfMiaTestPayResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                    ], self::getProperties($models, 'MiaTestPayDto')),
                    'additionalParameters' => ['location' => 'json'],
                ],
                #endregion
            ],

            'models' => $models
        ];

        parent::__construct($description, $options);
    }

    /**
     * Get property definitions from a model and inject a specific location.
     */
    private static function getProperties(array $models, string $modelName, string $location = 'json'): array
    {
        $props = $models[$modelName]['properties'] ?? [];
        foreach ($props as &$prop) {
            $prop['location'] = $location;
        }
        return $props;
    }
}
