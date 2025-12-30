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

    /**
     * @param ClientInterface      $client
     * @param DescriptionInterface $description
     * @param array                $config
     */
    public function __construct(
        ?ClientInterface $client = null,
        ?DescriptionInterface $description = null,
        array $config = []
    ) {
        $client = $client instanceof ClientInterface ? $client : new Client();
        $description = $description instanceof DescriptionInterface ? $description : new MaibCheckoutDescription($config);
        parent::__construct($client, $description, null, null, null, $config);
    }

    #region Auth
    /**
     * Obtain authentication token
     * @param string $clientId
     * @param string $clientSecret
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/checkout/api-reference/endpoints/authentication/obtain-authentication-token
     * @link https://docs.maibmerchants.md/checkout/getting-started/api-fundamentals#authentication
     */
    public function getToken($clientId, $clientSecret)
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
     * @param array  $checkoutData
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/checkout/api-reference/endpoints/register-a-new-hosted-checkout-session
     */
    public function checkoutRegister($checkoutData, $authToken)
    {
        $args = $checkoutData;
        self::setBearerAuthToken($args, $authToken);
        return parent::checkoutRegister($args);
    }

    /**
     * Cancel a checkout session
     * @param string $checkoutId
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/checkout/api-reference/endpoints/cancel-a-checkout-session
     */
    public function checkoutCancel($checkoutId, $authToken)
    {
        $args = [
            'checkoutId' => $checkoutId,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::checkoutCancel($args);
    }

    /**
     * Get checkout details
     * @param string $checkoutId
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/checkout/api-reference/endpoints/get-checkout-details
     */
    public function checkoutDetails($checkoutId, $authToken)
    {
        $args = [
            'checkoutId' => $checkoutId,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::checkoutDetails($args);
    }

    /**
     * Retrieve all checkouts
     * @param array $checkoutListData
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/checkout/api-reference/endpoints/retrieve-all-checkouts
     */
    public function checkoutList($checkoutListData, $authToken)
    {
        $args = $checkoutListData;
        self::setBearerAuthToken($args, $authToken);
        return parent::checkoutList($args);
    }
    #endregion

    #region Payment
    /**
     * Get payment by id
     * @param string $paymentId
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/checkout/api-reference/endpoints/get-payment-by-id
     */
    public function paymentDetails($paymentId, $authToken)
    {
        $args = [
            'paymentId' => $paymentId,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::paymentDetails($args);
    }

    /**
     * Retrieve all payments by filter
     * @param array $paymentListData
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/checkout/api-reference/endpoints/retrieve-all-payments-by-filter
     */
    public function paymentList($paymentListData, $authToken)
    {
        $args = $paymentListData;
        self::setBearerAuthToken($args, $authToken);
        return parent::paymentList($args);
    }

    /**
     * Refund a payment
     * @param string $paymentId
     * @param array $refundData
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/checkout/api-reference/endpoints/refund-a-payment
     */
    public function paymentRefund($paymentId, $refundData, $authToken)
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
     * @param array $testPayData
     * @param string $authToken
     * @return \GuzzleHttp\Command\Result
     * @link https://docs.maibmerchants.md/mia-qr-api/en/payment-simulation-sandbox
     */
    public function miaTestPay($testPayData, $authToken)
    {
        $args = $testPayData;

        self::setBearerAuthToken($args, $authToken);
        return parent::miaTestPay($args);
    }
    #endregion

    #region Signature
    /**
     * Callback Payload Signature Key Verification
     * @param string $callbackBody
     * @param string $signatureHeader
     * @param string $signatureTimestamp
     * @param string $signatureKey
     * @link https://docs.maibmerchants.md/checkout/api-reference/callback-notifications
     * @link https://docs.maibmerchants.md/checkout/api-reference/examples/signature-key-verification
     */
    public static function validateCallbackSignature($callbackBody, $signatureHeader, $signatureTimestamp, $signatureKey)
    {
        // Extract signature
        $signature = substr($signatureHeader, strlen('sha256='));

        // Compute result signature
        $result = self::computeCallbackSignature($callbackBody, $signatureTimestamp, $signatureKey);

        // Compare the result with the signature
        return hash_equals($result, $signature);
    }

    /**
     * Compute Payload Signature
     * @param string $callbackBody
     * @param string $signatureTimestamp
     * @param string $signatureKey
     * @link https://docs.maibmerchants.md/checkout/api-reference/callback-notifications
     * @link https://docs.maibmerchants.md/checkout/api-reference/examples/signature-key-verification
     */
    public static function computeCallbackSignature($callbackBody, $signatureTimestamp, $signatureKey)
    {
        // Build message: JSON + "." + timestamp
        $message = "$callbackBody.$signatureTimestamp";

        // Compute HMAC SHA256
        $computedHash = hash_hmac('sha256', $message, $signatureKey, true);

        // Encode to Base64
        $result = base64_encode($computedHash);

        // Return with sha256= prefix
        return "sha256=$result";
    }
    #endregion

    #region Util
    /**
     * @param array  $args
     * @param string $authToken
     */
    private static function setBearerAuthToken(&$args, $authToken)
    {
        $args['authToken'] = "Bearer $authToken";
    }
    #endregion
}
