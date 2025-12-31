<?php

namespace Maib\MaibCheckout;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Command\Guzzle\DescriptionInterface;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Result;

/**
 * maib e-Commerce Checkout API client
 * @link https://docs.maibmerchants.md/checkout
 */
class MaibCheckoutClient extends GuzzleClient
{
    public const DEFAULT_BASE_URL = 'https://api.maibmerchants.md/';
    public const SANDBOX_BASE_URL = 'https://sandbox.maibmerchants.md/';

    public function __construct(?ClientInterface $client = null, ?DescriptionInterface $description = null, array $config = [])
    {
        $client = $client ?? new Client();
        $description = $description ?? new MaibCheckoutDescription($config);

        parent::__construct($client, $description, null, null, null, $config);
    }

    #region Auth
    /**
     * Obtain authentication token
     * @link https://docs.maibmerchants.md/checkout/api-reference/endpoints/authentication/obtain-authentication-token
     * @link https://docs.maibmerchants.md/checkout/getting-started/api-fundamentals#authentication
     */
    public function getToken(string $clientId, string $clientSecret): Result
    {
        $args = [
            'clientId' => $clientId,
            'clientSecret' => $clientSecret
        ];

        return parent::getToken($args);
    }
    #endregion

    #region Checkout
    /**
     * Register a new hosted checkout session
     * @link https://docs.maibmerchants.md/checkout/api-reference/endpoints/register-a-new-hosted-checkout-session
     */
    public function checkoutRegister(array $checkoutData, string $authToken): Result
    {
        $args = $checkoutData;
        self::setBearerAuthToken($args, $authToken);
        return parent::checkoutRegister($args);
    }

    /**
     * Cancel a checkout session
     * @link https://docs.maibmerchants.md/checkout/api-reference/endpoints/cancel-a-checkout-session
     */
    public function checkoutCancel(string $checkoutId, string $authToken): Result
    {
        $args = [
            'checkoutId' => $checkoutId,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::checkoutCancel($args);
    }

    /**
     * Get checkout details
     * @link https://docs.maibmerchants.md/checkout/api-reference/endpoints/get-checkout-details
     */
    public function checkoutDetails(string $checkoutId, string $authToken): Result
    {
        $args = [
            'checkoutId' => $checkoutId,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::checkoutDetails($args);
    }

    /**
     * Retrieve all checkouts
     * @link https://docs.maibmerchants.md/checkout/api-reference/endpoints/retrieve-all-checkouts
     */
    public function checkoutList(array $checkoutListData, string $authToken): Result
    {
        $args = $checkoutListData;
        self::setBearerAuthToken($args, $authToken);
        return parent::checkoutList($args);
    }
    #endregion

    #region Payment
    /**
     * Get payment by id
     * @link https://docs.maibmerchants.md/checkout/api-reference/endpoints/get-payment-by-id
     */
    public function paymentDetails(string $paymentId, string $authToken): Result
    {
        $args = [
            'paymentId' => $paymentId,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::paymentDetails($args);
    }

    /**
     * Retrieve all payments by filter
     * @link https://docs.maibmerchants.md/checkout/api-reference/endpoints/retrieve-all-payments-by-filter
     */
    public function paymentList(array $paymentListData, string $authToken): Result
    {
        $args = $paymentListData;
        self::setBearerAuthToken($args, $authToken);
        return parent::paymentList($args);
    }

    /**
     * Refund a payment
     * @link https://docs.maibmerchants.md/checkout/api-reference/endpoints/refund-a-payment
     */
    public function paymentRefund(string $paymentId, array $refundData, string $authToken): Result
    {
        $args = $refundData;
        $args['paymentId'] = $paymentId;

        self::setBearerAuthToken($args, $authToken);
        return parent::paymentRefund($args);
    }
    #endregion

    #region Payment Simulation
    /**
     * Payment Simulation (Sandbox)
     * @link https://docs.maibmerchants.md/mia-qr-api/en/payment-simulation-sandbox
     */
    public function miaTestPay(array $testPayData, string $authToken): Result
    {
        $args = $testPayData;

        self::setBearerAuthToken($args, $authToken);
        return parent::miaTestPay($args);
    }
    #endregion

    #region Signature
    /**
     * Callback Payload Signature Key Verification
     * @param string $callbackBody       Callback message (exact raw JSON body as string)
     * @param string $signatureHeader    X-Signature header value
     * @param string $signatureTimestamp X-Signature-Timestamp header value
     * @param string $signatureKey       Merchant’s shared secret key
     * @return bool True if signature is valid, false otherwise
     * @link https://docs.maibmerchants.md/checkout/api-reference/callback-notifications
     * @link https://docs.maibmerchants.md/checkout/api-reference/examples/signature-key-verification
     */
    public static function validateCallbackSignature(string $callbackBody, string $signatureHeader, string $signatureTimestamp, string $signatureKey): bool
    {
        // Extract signature
        $prefix = 'sha256=';
        $callbackSignature = strpos($signatureHeader, $prefix) === 0
            ? substr($signatureHeader, strlen($prefix))
            : '';

        // Validate required data exists
        if (empty($callbackBody) || empty($callbackSignature)) {
            return false;
        }

        // Compute result signature
        $computedSignature = self::computeCallbackSignature($callbackBody, $signatureTimestamp, $signatureKey);

        // Compare the result with the signature
        return hash_equals($computedSignature, $callbackSignature);
    }

    /**
     * Compute Payload Signature
     * @link https://docs.maibmerchants.md/checkout/api-reference/callback-notifications
     * @link https://docs.maibmerchants.md/checkout/api-reference/examples/signature-key-verification
     */
    public static function computeCallbackSignature(string $callbackBody, string $signatureTimestamp, string $signatureKey): string
    {
        // Build message: JSON + "." + timestamp
        $message = "$callbackBody.$signatureTimestamp";

        // Compute HMAC SHA256
        $computedHash = hash_hmac('sha256', $message, $signatureKey, true);

        // Encode to Base64
        $result = base64_encode($computedHash);
        return $result;
    }
    #endregion

    #region Util
    private static function setBearerAuthToken(&$args, $authToken)
    {
        $args['authToken'] = "Bearer $authToken";
    }
    #endregion
}
