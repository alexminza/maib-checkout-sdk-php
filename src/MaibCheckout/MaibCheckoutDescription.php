<?php

declare(strict_types=1);

namespace Maib\MaibCheckout;

use GuzzleHttp\Command\Guzzle\Description;
use Composer\InstalledVersions;

/**
 * maib e-Commerce Checkout API service description
 *
 * @link https://docs.maibmerchants.md/checkout
 * @link https://docs.maibmerchants.md/checkout/api-reference/examples/api-tools-and-resources
 */
class MaibCheckoutDescription extends Description
{
    private const PACKAGE_NAME    = 'alexminza/maib-checkout-sdk';
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
        $version   = self::detectVersion();
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
            'GenerateTokenRequest' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'clientId' => ['type' => 'string', 'required' => true],
                    'clientSecret' => ['type' => 'string', 'required' => true],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/register-a-new-hosted-checkout-session#request
            'CreateCheckoutRequest' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'amount' => ['type' => 'number', 'required' => true],
                    'currency' => ['type' => 'string', 'required' => true],
                    'orderInfo' => [
                        'type' => 'object',
                        '$ref' => 'OrderDto',
                    ],
                    'payerInfo' => [
                        'type' => 'object',
                        '$ref' => 'PayerDto',
                    ],
                    'language' => ['type' => 'string'],
                    'callbackUrl' => ['type' => 'string'],
                    'successUrl' => ['type' => 'string'],
                    'failUrl' => ['type' => 'string'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/register-a-new-hosted-checkout-session#orderinfo-object
            'OrderDto' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'description' => ['type' => 'string'],
                    'date' => ['type' => 'string', 'format' => 'date-time'],
                    'orderAmount' => ['type' => 'number'],
                    'orderCurrency' => ['type' => 'string'],
                    'deliveryAmount' => ['type' => 'number'],
                    'deliveryCurrency' => ['type' => 'string'],
                    'items' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'object',
                            '$ref' => 'OrderItemDto',
                        ],
                    ]
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/register-a-new-hosted-checkout-session#orderinfo.items
            'OrderItemDto' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'externalId' => ['type' => 'string'],
                    'title' => ['type' => 'string'],
                    'amount' => ['type' => 'number'],
                    'currency' => ['type' => 'string'],
                    'quantity' => ['type' => 'number'],
                    'displayOrder' => ['type' => 'integer'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/register-a-new-hosted-checkout-session#payerinfo-object
            'PayerDto' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'name' => ['type' => 'string'],
                    'email' => ['type' => 'string'],
                    'phone' => ['type' => 'string'],
                    'ip' => ['type' => 'string'],
                    'userAgent' => ['type' => 'string'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/refund-a-payment#request
            'CreateRefundRequest' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'amount' => ['type' => 'number'],
                    'reason' => ['type' => 'string'],
                    'callbackUrl' => ['type' => 'string'],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/retrieve-all-checkouts#request
            'GetAllCheckoutsRequest' => [
                'type' => 'object',
                'location' => 'query',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'orderId' => ['type' => 'string'],
                    'status' => ['type' => 'string', 'enum' => ['WaitingForInit', 'Initialized', 'PaymentMethodSelected', 'Completed', 'Expired', 'Abandoned', 'Cancelled', 'Failed']],
                    'minAmount' => ['type' => 'number'],
                    'maxAmount' => ['type' => 'number'],
                    'currency' => ['type' => 'string'],
                    'language' => ['type' => 'string', 'enum' => ['Undefined', 'Ro', 'En', 'Ru']],
                    'payerName' => ['type' => 'string'],
                    'payerEmail' => ['type' => 'string'],
                    'payerPhone' => ['type' => 'string'],
                    'payerIp' => ['type' => 'string'],
                    'createdAtFrom' => ['type' => 'string', 'format' => 'date-time'],
                    'createdAtTo' => ['type' => 'string', 'format' => 'date-time'],
                    'expiresAtFrom' => ['type' => 'string', 'format' => 'date-time'],
                    'expiresAtTo' => ['type' => 'string', 'format' => 'date-time'],
                    'cancelledAtFrom' => ['type' => 'string', 'format' => 'date-time'],
                    'cancelledAtTo' => ['type' => 'string', 'format' => 'date-time'],
                    'failedAtFrom' => ['type' => 'string', 'format' => 'date-time'],
                    'failedAtTo' => ['type' => 'string', 'format' => 'date-time'],
                    'completedAtFrom' => ['type' => 'string', 'format' => 'date-time'],
                    'completedAtTo' => ['type' => 'string', 'format' => 'date-time'],
                    'count' => ['type' => 'integer'],
                    'offset' => ['type' => 'integer'],
                    'sortBy' => ['type' => 'string', 'enum' => ['CreatedAt', 'Amount', 'Status', 'ExpiresAt', 'FailedAt', 'CancelledAt', 'CompletedAt']],
                    'order' => ['type' => 'string', 'enum' => ['Asc', 'Desc']],
                ],
                'additionalProperties' => ['location' => 'query'],
            ],
            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/retrieve-all-payments-by-filter#request
            'GetAllPaymentsRequest' => [
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
                    'status' => ['type' => 'string'],
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
                    'sortBy' => ['type' => 'string'],
                    'order' => ['type' => 'string', 'enum' => ['Asc', 'Desc']],
                ],
                'additionalProperties' => ['location' => 'query'],
            ],
            #endregion

            #region Response Models
            // https://docs.maibmerchants.md/checkout/api-reference/errors/api-errors#error-format
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

            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/authentication/obtain-authentication-token#response-parameters
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

            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/register-a-new-hosted-checkout-session#response
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

            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/cancel-a-checkout-session#response
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

            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/get-checkout-details#response
            'CheckoutModel' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'createdAt' => ['type' => 'string'],
                    'status' => ['type' => 'string'],
                    'amount' => ['type' => 'number'],
                    'currency' => ['type' => 'string'],
                    'callbackUrl' => ['type' => 'string'],
                    'successUrl' => ['type' => 'string'],
                    'failUrl' => ['type' => 'string'],
                    'language' => ['type' => 'string'],
                    'url' => ['type' => 'string'],
                    'order' => [
                        'type' => 'object',
                        '$ref' => 'OrderInfo',
                    ],
                    'payer' => [
                        'type' => 'object',
                        '$ref' => 'PayerDto',
                    ],
                    'completedAt' => ['type' => 'string'],
                    'failedAt' => ['type' => 'string'],
                    'cancelledAt' => ['type' => 'string'],
                    'expiresAt' => ['type' => 'string'],
                    'payment' => [
                        'type' => 'object',
                        '$ref' => 'PaymentResponseDto',
                    ],
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/get-checkout-details#result.order-object
            'OrderInfo' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'id' => ['type' => 'string'],
                    'description' => ['type' => 'string'],
                    'amount' => ['type' => 'number'],
                    'currency' => ['type' => 'string'],
                    'deliveryAmount' => ['type' => 'number'],
                    'deliveryCurrency' => ['type' => 'string'],
                    'date' => ['type' => 'string', 'format' => 'date-time'],
                    'orderItems' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'object',
                            '$ref' => 'OrderItemDto',
                        ],
                    ]
                ],
                'additionalProperties' => ['location' => 'json'],
            ],
            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/get-checkout-details#result.payment-object
            'PaymentResponseDto' => [
                'type' => 'object',
                'location' => 'json',
                'properties' => [
                    'paymentId' => ['type' => 'string'],
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
                    'paymentMethod' => ['type' => 'string'],
                    'approvalCode' => ['type' => 'string'],
                    'requestedRefundAmount' => ['type' => 'number'],
                    'firstRefundedAt' => ['type' => 'string'],
                    'lastRefundedAt' => ['type' => 'string'],
                    'note' => ['type' => 'string'],
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

            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/retrieve-all-checkouts#response
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

            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/get-payment-by-id#response
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
                    'paymentMethod' => ['type' => 'string'],
                    'approvalCode' => ['type' => 'string'],
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

            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/retrieve-all-payments-by-filter#response
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

            // https://docs.maibmerchants.md/checkout/api-reference/endpoints/refund-a-payment#response
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
                    'parameters' => self::getProperties($models, 'GenerateTokenRequest'),
                    'additionalParameters' => ['location' => 'json'],
                ],
                #endregion

                #region Checkout Operations
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/register-a-new-hosted-checkout-session
                'checkoutRegister' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/checkouts/',
                    'summary' => 'Register a new hosted checkout session',
                    'responseModel' => 'OperationResultOfCreateCheckoutResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                    ], self::getProperties($models, 'CreateCheckoutRequest')),
                    'additionalParameters' => ['location' => 'json'],
                ],
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/cancel-a-checkout-session
                'checkoutCancel' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/checkouts/{id}/cancel',
                    'summary' => 'Cancel a checkout session',
                    'responseModel' => 'OperationResultOfCancelCheckoutResult',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'id' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                    'additionalParameters' => ['location' => 'json'],
                ],
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/get-checkout-details
                'checkoutDetails' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/checkouts/{id}',
                    'summary' => 'Get checkout details',
                    'responseModel' => 'OperationResultOfCheckoutModel',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'id' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                    'additionalParameters' => ['location' => 'query'],
                ],
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/retrieve-all-checkouts
                'checkoutList' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/checkouts/',
                    'summary' => 'Retrieve all checkouts',
                    'responseModel' => 'OperationResultOfPagedListOfCheckoutModel',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                    ], self::getProperties($models, 'GetAllCheckoutsRequest', 'query')),
                    'additionalParameters' => ['location' => 'query'],
                ],
                #endregion

                #region Payment Operations
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/get-payment-by-id
                'paymentDetails' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/payments/{id}',
                    'summary' => 'Get payment by id',
                    'responseModel' => 'OperationResultOfGetPaymentResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'id' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                    'additionalParameters' => ['location' => 'query'],
                ],
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/retrieve-all-payments-by-filter
                'paymentList' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/v2/payments/',
                    'summary' => 'Retrieve all payments by filter',
                    'responseModel' => 'OperationResultOfGetAllPaymentsResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                    ], self::getProperties($models, 'GetAllPaymentsRequest', 'query')),
                    'additionalParameters' => ['location' => 'query'],
                ],
                // https://docs.maibmerchants.md/checkout/api-reference/endpoints/refund-a-payment
                'paymentRefund' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/v2/payments/{payId}/refund',
                    'summary' => 'Refund a payment',
                    'responseModel' => 'OperationResultOfCreateRefundResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                        'payId' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ], self::getProperties($models, 'CreateRefundRequest')),
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
        $props  = $models[$modelName]['properties'] ?? [];
        $result = [];

        foreach ($props as $name => $prop) {
            $prop['location'] = $location;
            $result[$name]    = $prop;
        }

        return $result;
    }
}
