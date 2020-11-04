<?php
/**
 * Transom Group Inc.
 *
 *
 * @category    Transom
 * @package     Transom_Group
 * @copyright   Copyright (c) Transom Group. All rights reserved. (https://transom-group.com/)
 */

namespace Transom\IPQualityScore\Helper;

use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Psr\Log\LoggerInterface;
use Transom\IPQualityScore\Model\ConfigSettings;
use Transom\IPQualityScore\Model\IpqsApiRequestFactory;
use Transom\IPQualityScore\Model\OrderManager;
use Transom\IPQualityScore\Model\OrderScoreFactory;
use Transom\IPQualityScore\Model\ResourceModel\IpqsApiRequest;
use Transom\IPQualityScore\Model\ResourceModel\OrderScore;



class Api extends \Magento\Framework\App\Helper\AbstractHelper {


    // see https://www.ipqualityscore.com/documentation/proxy-detection/overview for ipqs api overview
    const IPQS_PARAM_STRICTNESS = 'strictness';
    const IPQS_PARAM_ALLOW_PUBLIC_ACCESS_PIONTS = 'allow_public_access_points';
    const IPQS_PARAM_LIGHTER_PENALTIES = 'lighter_penalties';
    const IPQS_PARAM_FAST = 'fast';
    const IPQS_PARAM_USER_AGENT = 'user_agent';
    const IPQS_PARAM_USER_LANGUAGE = 'user_language';

    // see https://www.ipqualityscore.com/documentation/proxy-detection/transaction-scoring for list of transaction parameters
    const IPQS_PARAM_BILLING_FIRST_NAME = 'billing_first_name';
    const IPQS_PARAM_BILLING_LAST_NAME = 'billing_last_name';
    const IPQS_PARAM_BILLING_COUNTRY = 'billing_country';
    const IPQS_PARAM_BILLING_ADDRESS_1 = 'billing_address_1';
    const IPQS_PARAM_BILLING_ADDRESS_2 = 'billing_address_2';
    const IPQS_PARAM_BILLING_CITY = 'billing_city';
    const IPQS_PARAM_BILLING_STATE = 'billing_state';
    const IPQS_PARAM_BILLING_ZIPCODE = 'billing_zipcode';
    const IPQS_PARAM_BILLING_PHONE = 'billing_phone';
    const IPQS_PARAM_BILLING_EMAIL = 'billing_email';
    const IPQS_PARAM_SHIPPING_FIRST_NAME = 'shiping_first_name';
    const IPQS_PARAM_SHIPPING_LAST_NAME = 'shiping_last_name';
    const IPQS_PARAM_SHIPPING_COUNTRY = 'shiping_country';
    const IPQS_PARAM_SHIPPING_ADDRESS_1 = 'shiping_address_1';
    const IPQS_PARAM_SHIPPING_ADDRESS_2 = 'shiping_address_2';
    const IPQS_PARAM_SHIPPING_CITY = 'shiping_city';
    const IPQS_PARAM_SHIPPING_STATE = 'shiping_state';
    const IPQS_PARAM_SHIPPING_ZIPCODE = 'shiping_zipcode';
    const IPQS_PARAM_SHIPPING_PHONE = 'shiping_phone';
    const IPQS_PARAM_SHIPPING_EMAIL = 'shiping_email';
    const IPQS_PARAM_CC_EXP_MONTH = "credit_card_expiration_month";
    const IPQS_PARAM_CC_EXP_YEAR = "credit_card_expiration_year";
    const IPQS_PARAM_ORDER_AMOUNT = 'order_amount';
    const IPQS_PARAM_SUCCESS = 'success';
    const IPQS_PARAM_FRAUD_SCORE = 'fraud_score';
    const IPQS_PARAM_RISK_SCORE = 'risk_score';
    const IPQS_PARAM_TRANSACTION_DETAILS = 'transaction_details';
    const IPQS_PARAM_BOT_STATUS = 'bot_status';

    const IPQS_REQUEST_LOGIN = 'login';
    const IPQS_REQUEST_TRANSACTION = 'transaction';


    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Transom\IPQualityScore\Model\ConfigSettings
     */
    protected $config;

    /**
     * @var \Transom\IPQualityScore\Model\IpqsApiRequestFactory
     */
    protected $ipqsApiRequestFactory;

    /**
     * @var \Transom\IPQualityScore\Model\ResourceModel\IpqsApiRequest
     */
    protected $ipqsApiRequestResource;

    /**
     * @var \Transom\IPQualityScore\Model\OrderScoreFactory
     */
    protected $orderScoreFactory;

    /**
     * @var \Transom\IPQualityScore\Model\ResourceModel\OrderScore
     */
    protected $orderScoreResource;

    /**
     * @var \Transom\IPQualityScore\Model\OrderManager
     */
    protected $orderManager;

    /**
     * Api constructor.
     * @param LoggerInterface $logger
     * @param ConfigSettings $config
     * @param IpqsApiRequestFactory $ipqsApiRequestFactory
     * @param IpqsApiRequest $ipqsApiRequestResource
     * @param OrderScoreFactory $orderScoreFactory
     * @param OrderScore $orderScoreResource
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(LoggerInterface $logger,
                                ConfigSettings $config,
                                IpqsApiRequestFactory $ipqsApiRequestFactory,
                                IpqsApiRequest $ipqsApiRequestResource,
                                OrderScoreFactory $orderScoreFactory,
                                OrderScore $orderScoreResource,
                                OrderManager $orderManager,
                                RemoteAddress $remoteAddress)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->ipqsApiRequestFactory = $ipqsApiRequestFactory;
        $this->ipqsApiRequestResource = $ipqsApiRequestResource;
        $this->orderScoreFactory = $orderScoreFactory;
        $this->orderScoreResource = $orderScoreResource;
        $this->orderManager = $orderManager;
        $this->remoteAddress = $remoteAddress;
    }


    /**
     * send login event data to ipqs
     * @param $customer
     * @return $this
     */
    public function sendLogin($customer) {

        // get basic parameters
        $ipAddress = $this->remoteAddress->getRemoteAddress();
        $userAgent = $_SERVER ['HTTP_USER_AGENT'];
        $userLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

        $parameters = array(
            self::IPQS_PARAM_USER_AGENT => $userAgent,
            self::IPQS_PARAM_USER_LANGUAGE => $userLanguage,
            self::IPQS_PARAM_STRICTNESS => $this->config->getStrictness(),
            self::IPQS_PARAM_ALLOW_PUBLIC_ACCESS_PIONTS => $this->config->getAllowPublicAccessPoints(),
            self::IPQS_PARAM_LIGHTER_PENALTIES => $this->config->getLighterPenalties(),
            self::IPQS_PARAM_FAST => $this->config->getFast()
        );

        $result = $this->sendRequest($parameters, $ipAddress);

        // Check to see if our query was successful.
        if(isset($result[self::IPQS_PARAM_SUCCESS]) && $result[self::IPQS_PARAM_SUCCESS] === true) {
            $this->saveRequest($parameters, $ipAddress, $result, self::IPQS_REQUEST_LOGIN, $customer->getId(), $customer->getEmail());
        }
    }


    /**
     * send transaction event data to ipqs
     * @param $order
     * @return $this
     */
    public function sendTransaction(\Magento\Sales\Model\Order\Interceptor $order, \Magento\Sales\Model\Order\Payment\Interceptor $payment) {

        // get basic parameters
        $ipAddress = $this->remoteAddress->getRemoteAddress();
        $userAgent = $_SERVER ['HTTP_USER_AGENT'];
        $userLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

        $parameters = array(
            self::IPQS_PARAM_USER_AGENT => $userAgent,
            self::IPQS_PARAM_USER_LANGUAGE => $userLanguage,
            self::IPQS_PARAM_STRICTNESS => $this->config->getStrictness(),
            self::IPQS_PARAM_ALLOW_PUBLIC_ACCESS_PIONTS => $this->config->getAllowPublicAccessPoints(),
            self::IPQS_PARAM_LIGHTER_PENALTIES => $this->config->getLighterPenalties(),
            self::IPQS_PARAM_FAST => $this->config->getFast()
        );

        // populate order and payment variables
        $orderId = $order->getIncrementId();
        $orderAmount = $order->getGrandTotal();

        // populate cc variables
        $ccExpMonth = $payment->getCcExpMonth();
        if ($ccExpMonth) {
            $parameters[self::IPQS_PARAM_CC_EXP_MONTH] = $ccExpMonth;
        }
        $ccExpYear = $payment->getCcExpYear();
        if ($ccExpYear) {
            $parameters[self::IPQS_PARAM_CC_EXP_YEAR] = $ccExpYear;
        }

        // populate customer variables
        $customerId = $order->getCustomerId();
        $customerEmail = $order->getCustomerEmail();

        // add order transaction parameters
        $parameters[self::IPQS_PARAM_ORDER_AMOUNT] = $orderAmount;

        // populate billing address variables
        $billingAddress = $order->getBillingAddress();
        if ($billingAddress) {
            $billingFirstName = $billingAddress->getFirstName();
            $billingLastName = $billingAddress->getLastName();
            $billingTelephone = $billingAddress->getTelephone();
            $billingStreet = $billingAddress->getStreet();
            $billingAddress1 = $billingStreet[0];
            $billingAddress2 = "";
            if (isset($billingStreet[1])) {
                $billingAddress2 = $billingStreet[1];
            }
            $billingCity = $billingAddress->getCity();
            $billingRegion = $billingAddress->getRegion();
            $billingCountry = $billingAddress->getCountryId();
            $billingZipCode = $billingAddress->getPostcode();

            // set billing params
            $parameters[self::IPQS_PARAM_BILLING_FIRST_NAME] = $billingFirstName;
            $parameters[self::IPQS_PARAM_BILLING_LAST_NAME] = $billingLastName;
            $parameters[self::IPQS_PARAM_BILLING_COUNTRY] = $billingCountry;
            $parameters[self::IPQS_PARAM_BILLING_ADDRESS_1] = $billingAddress1;
            $parameters[self::IPQS_PARAM_BILLING_ADDRESS_2] = $billingAddress2;
            $parameters[self::IPQS_PARAM_BILLING_CITY] = $billingCity;
            $parameters[self::IPQS_PARAM_BILLING_STATE] = $billingRegion;
            $parameters[self::IPQS_PARAM_BILLING_ZIPCODE] = $billingZipCode;
            $parameters[self::IPQS_PARAM_BILLING_PHONE] = $billingTelephone;
            $parameters[self::IPQS_PARAM_BILLING_EMAIL] = $customerEmail;
        }

        // populate shipping address variables
        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress) {
            $shippingFirstName = $shippingAddress->getFirstname();
            $shippingLastName = $shippingAddress->getLastName();
            $shippingTelephone = $shippingAddress->getTelephone();
            $shippingStreet = $shippingAddress->getStreet();
            $shippingAddress1 = $shippingStreet[0];
            $shippingAddress2 = "";
            if (isset($shippingStreet[1])) {
                $shippingAddress2 = $shippingStreet[1];
            }
            $shippingCity = $shippingAddress->getCity();
            $shippingRegion = $shippingAddress->getRegion();
            $shippingCountry = $shippingAddress->getCountryId();
            $shippingZipCode = $shippingAddress->getPostcode();

            // set shipping params
            $parameters[self::IPQS_PARAM_SHIPPING_FIRST_NAME] = $shippingFirstName;
            $parameters[self::IPQS_PARAM_SHIPPING_LAST_NAME] = $shippingLastName;
            $parameters[self::IPQS_PARAM_SHIPPING_COUNTRY] = $shippingCountry;
            $parameters[self::IPQS_PARAM_SHIPPING_ADDRESS_1] = $shippingAddress1;
            $parameters[self::IPQS_PARAM_SHIPPING_ADDRESS_2] = $shippingAddress2;
            $parameters[self::IPQS_PARAM_SHIPPING_CITY] = $shippingCity;
            $parameters[self::IPQS_PARAM_SHIPPING_STATE] = $shippingRegion;
            $parameters[self::IPQS_PARAM_SHIPPING_ZIPCODE] = $shippingZipCode;
            $parameters[self::IPQS_PARAM_SHIPPING_PHONE] = $shippingTelephone;
            $parameters[self::IPQS_PARAM_SHIPPING_EMAIL] = $customerEmail;
        }

        $result = $this->sendRequest($parameters, $ipAddress);

        // Check to see if the request was successful.
        if(isset($result[self::IPQS_PARAM_SUCCESS]) && $result[self::IPQS_PARAM_SUCCESS] === true) {
            $this->saveRequest($parameters, $ipAddress, $result, self::IPQS_REQUEST_TRANSACTION, $customerId, $customerEmail);
            $this->saveOrderScore($result, $orderId);
            $this->orderManager->updateOrderStatus($result[self::IPQS_PARAM_FRAUD_SCORE], $result[self::IPQS_PARAM_TRANSACTION_DETAILS][self::IPQS_PARAM_RISK_SCORE], $order);
        }
    }


    /**
     * send request to ip quality score service
     * @param $parameters
     * @param $ipAddress
     * @return $this|mixed
     */
    protected function sendRequest($parameters, $ipAddress) {

        // only process order if this service is enabled
        if (!$this->config->isApiActive()) {
            return $this;
        }

        // get credentials, call IP quality score
        $apiKey = $this->config->getApiKey();
        $endpointUrl = $this->config->getApiEndPoint();
        $formattedParameters = http_build_query($parameters);
        $url = sprintf(
            $endpointUrl . 'json/ip/%s/%s?%s',
            $apiKey,
            $ipAddress,
            $formattedParameters
        );

        // Fetch The Result
        $timeout = 5;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);

        $json = curl_exec($curl);
        curl_close($curl);

        // Decode the result into an array.
        return json_decode($json, true);
    }


    /**
     * @param $parameters
     * @param $ipAddress
     * @param $result
     * @param $type
     * @param $customerId
     * @param $customerEmail
     * @param null $order
     */
    protected function saveRequest($parameters, $ipAddress, $result, $type, $customerId, $customerEmail) {

        $fraudScore = $result[self::IPQS_PARAM_FRAUD_SCORE];
        $botStatus = $result[self::IPQS_PARAM_BOT_STATUS];

        $request = array(
            'sent' => date('Y-m-d H:i:s'),
            'type' => $type,
            'customer_id' => $customerId,
            'email' =>  $customerEmail,
            'ip_address' => $ipAddress
        );
        $request = array_merge($request, $parameters);
        $data = array_merge($result, $request);

        try {
            $ipqsInterface = $this->ipqsApiRequestFactory->create();
            $ipqsInterface->setData('type', $type);
            $ipqsInterface->setData('fraud_score', $fraudScore);
            $ipqsInterface->setData('bot_status', $botStatus);
            $ipqsInterface->setData('data', json_encode($data));
            $this->ipqsApiRequestResource->save($ipqsInterface);
        } catch (\Exception $e) {
            $this->logger->info('Exception saving IPQS request: ' . $e->getMessage());
        }
    }


    /**
     * @param $result
     * @param $orderId
     */
    protected function saveOrderScore($result, $orderId) {
        $fraudScore = $result[self::IPQS_PARAM_FRAUD_SCORE];
        $riskScore = $result[self::IPQS_PARAM_TRANSACTION_DETAILS][self::IPQS_PARAM_RISK_SCORE];
        $botStatus = $result[self::IPQS_PARAM_BOT_STATUS];

        try {
            $orderScoreInterface = $this->orderScoreFactory->create();
            $orderScoreInterface->setData('order_id', $orderId);
            $orderScoreInterface->setData('fraud_score', $fraudScore);
            $orderScoreInterface->setData('risk_score', $riskScore);
            $orderScoreInterface->setData('bot_status', $botStatus);
            $this->orderScoreResource->save($orderScoreInterface);
        } catch (\Exception $e) {
            $this->logger->info('Exception saving IPQS score: ' . $e->getMessage());
        }
    }
}
