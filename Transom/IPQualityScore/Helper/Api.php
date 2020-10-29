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
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Transom\IPQualityScore\Model\ConfigSettings;
use Transom\IPQualityScore\Model\IpqsApiRequestFactory;
use Transom\IPQualityScore\Model\OrderScoreFactory;
use Transom\IPQualityScore\Model\ResourceModel\IpqsApiRequest;
use Transom\IPQualityScore\Model\ResourceModel\OrderScore;


class Api extends \Magento\Framework\App\Helper\AbstractHelper {

    const IPQS_PARAM_STRICTNESS = 'strictness';
    const IPQS_PARAM_ALLOW_PUBLIC_ACCESS_PIONTS = 'allow_public_access_points';
    const IPQS_PARAM_LIGHTER_PENALTIES = 'lighter_penalties';
    const IPQS_PARAM_FAST = 'fast';
    const IPQS_PARAM_USER_AGENT = 'user_agent';
    const IPQS_PARAM_USER_LANGUAGE = 'user_language';
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
    const IPQS_PARAM_CC_EXP_MONTH = 'credit_card_expiration_month';
    const IPQS_PARAM_CC_EXP_YEAR = 'credit_card_expiration_year';
    const IPQS_PARAM_ORDER_AMOUNT = 'order_amount';
    const IPQS_PARAM_SUCCESS = 'success';
    const IPQS_PARAM_FRAUD_SCORE = 'fraud_score';
    const IPQS_PARAM_RISK_SCORE = 'risk_score';
    const IPQS_PARAM_TRANSACTION_DETAILS = 'transaction_details';

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
     * Api constructor.
     *
     * @param LoggerInterface $logger
     * @param ConfigSettings $config
     */
    public function __construct(LoggerInterface $logger,
                                ConfigSettings $config,
                                IpqsApiRequestFactory $ipqsApiRequestFactory,
                                IpqsApiRequest $ipqsApiRequestResource,
                                OrderScoreFactory $orderScoreFactory,
                                OrderScore $orderScoreResource,
                                RemoteAddress $remoteAddress)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->ipqsApiRequestFactory = $ipqsApiRequestFactory;
        $this->ipqsApiRequestResource = $ipqsApiRequestResource;
        $this->orderScoreFactory = $orderScoreFactory;
        $this->orderScoreResource = $orderScoreResource;
        $this->remoteAddress = $remoteAddress;
    }


    /**
     * send login event data to ipqs
     * @param $customer
     * @return $this
     */
    public function sendLogin($customer) {
        $this->logger->info('##### In Transom IPQualityScore ##### sendLogin()');

        $ipAddress = $this->remoteAddress->getRemoteAddress();
        $userAgent = $_SERVER ['HTTP_USER_AGENT'];
        $userLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

        // Set the strictness for this query. (0 (least strict) - 3 (most strict))
        $strictness = 0;

        // You may want to allow public access points like coffee shops, schools, corporations, etc...
        $allowPublicAccessPoints = 'true';

        // Reduce scoring penalties for mixed quality IP addresses shared by good and bad users.
        $lighterPenalties = 'false';

        $parameters = array(
            self::IPQS_PARAM_USER_AGENT => $userAgent,
            self::IPQS_PARAM_USER_LANGUAGE => $userLanguage,
            self::IPQS_PARAM_STRICTNESS => $strictness,
            self::IPQS_PARAM_ALLOW_PUBLIC_ACCESS_PIONTS => $allowPublicAccessPoints,
            self::IPQS_PARAM_LIGHTER_PENALTIES => $lighterPenalties,
            self::IPQS_PARAM_FAST => 'true'
        );

        $result = $this->sendRequest($parameters, $ipAddress);

        // Check to see if our query was successful.
        if(isset($result[self::IPQS_PARAM_SUCCESS]) && $result[self::IPQS_PARAM_SUCCESS] === true) {
            $this->saveRequest($parameters, $ipAddress, $result, 'login', $customer->getId(), $customer->getEmail());
        }
    }


    /**
     * send transaction event data to ipqs
     * @param $order
     * @return $this
     */
    public function sendTransaction($order) {
        $this->logger->info('##### In Transom IPQualityScore ##### sendTransaction()');

        $ipAddress = $this->remoteAddress->getRemoteAddress();
        $userAgent = $_SERVER ['HTTP_USER_AGENT'];
        $userLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

        // Set the strictness for this query. (0 (least strict) - 3 (most strict))
        $strictness = 0;

        // You may want to allow public access points like coffee shops, schools, corporations, etc...
        $allowPublicAccessPoints = 'true';

        // Reduce scoring penalties for mixed quality IP addresses shared by good and bad users.
        $lighterPenalties = 'false';

        // populate order and payment variables
        $orderId = $order->getIncrementId();
        $orderAmount = $order->getGrandTotal();
        $orderCurrency = $order->getOrderCurrencyCode();

        // populate customer variables
        $customerId = $order->getCustomerId();
        $customerEmail = $order->getCustomerEmail();

        // populate billing address variables
        $billingAddress = $order->getBillingAddress();
        $billingFirstName = $billingAddress->getFirstname();
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
        $this->logger->info('##### ##### billing name = ' . $billingFirstName . ' ' . $billingLastName);

        // populate shipping address variables
        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress) {
            $shippingFirstName = $shippingAddress->getFirstname();
            $this->logger->info('##### ##### shipping first name = ' . $shippingFirstName);
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
        }
        $this->logger->info('##### ##### shipping name = ' . $shippingFirstName . ' ' . $shippingLastName);

        // TODO - populate event variables
        //$eventTime = $this->eventDate->format('Y-m-d\TH:i:s.') . gettimeofday()['usec'] . 'Z';
        //$eventId = $orderId . '-' . $this->eventDate->format('Y-m-d_H-i-s-') . gettimeofday()['usec'];

        $parameters = array(
            self::IPQS_PARAM_USER_AGENT => $userAgent,
            self::IPQS_PARAM_USER_LANGUAGE => $userLanguage,
            self::IPQS_PARAM_STRICTNESS => $strictness,
            self::IPQS_PARAM_ALLOW_PUBLIC_ACCESS_PIONTS => $allowPublicAccessPoints,
            self::IPQS_PARAM_LIGHTER_PENALTIES => $lighterPenalties,
            self::IPQS_PARAM_FAST => 'true',

            // set billing params
            self::IPQS_PARAM_BILLING_FIRST_NAME => $billingFirstName,
            self::IPQS_PARAM_BILLING_LAST_NAME => $billingLastName,
            self::IPQS_PARAM_BILLING_COUNTRY => $billingCountry,
            self::IPQS_PARAM_BILLING_ADDRESS_1 => $billingAddress1,
            self::IPQS_PARAM_BILLING_ADDRESS_2 => $billingAddress2,
            self::IPQS_PARAM_BILLING_CITY => $billingCity,
            self::IPQS_PARAM_BILLING_STATE => $billingRegion,
            self::IPQS_PARAM_BILLING_ZIPCODE => $billingZipCode,
            self::IPQS_PARAM_BILLING_PHONE => $billingTelephone,
            self::IPQS_PARAM_BILLING_EMAIL => $customerEmail,

            // set shipping params
            self::IPQS_PARAM_SHIPPING_FIRST_NAME => $shippingFirstName,
            self::IPQS_PARAM_SHIPPING_LAST_NAME => $shippingLastName,
            self::IPQS_PARAM_SHIPPING_COUNTRY => $shippingCountry,
            self::IPQS_PARAM_SHIPPING_ADDRESS_1 => $shippingAddress1,
            self::IPQS_PARAM_SHIPPING_ADDRESS_2 => $shippingAddress2,
            self::IPQS_PARAM_SHIPPING_CITY => $shippingCity,
            self::IPQS_PARAM_SHIPPING_STATE => $shippingRegion,
            self::IPQS_PARAM_SHIPPING_ZIPCODE => $shippingZipCode,
            self::IPQS_PARAM_SHIPPING_PHONE => $shippingTelephone,
            self::IPQS_PARAM_SHIPPING_EMAIL => $customerEmail,

            // add order transaction parameters
            self::IPQS_PARAM_ORDER_AMOUNT => $orderAmount
        );

        $result = $this->sendRequest($parameters, $ipAddress);

        // Check to see if the request was successful.
        if(isset($result[self::IPQS_PARAM_SUCCESS]) && $result[self::IPQS_PARAM_SUCCESS] === true) {
            $this->saveRequest($parameters, $ipAddress, $result, 'transaction', $customerId, $customerEmail);
            $this->saveOrderScore($result, $orderId);
            $this->updateOrderStatus($result, $order);
        }
    }


    /**
     * send request to ip quality score service
     * @param $parameters
     * @param $ipAddress
     * @return $this|mixed
     */
    protected function sendRequest($parameters, $ipAddress) {
        $this->logger->info('##### In Transom IPQualityScore ##### sendRequest()');

        // only process order if this service is enabled
        if (!$this->config->isApiActive()) {
            return $this;
        }

        // get credentials, call IP quality score
        if ($this->config->isApiAccessKeysInAdmin()) {
            // TODO: call ip quality score api
            $apiKey = $this->config->getApiKey();
            $endpointUrl = $this->config->getApiEndPoint();
        } else {
            // TODO get creds from env
            $this->logger->info('TODO hook up dont manage credentials in admin option.');
            return $this;
        }

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

        $request = array(
            'type' => $type,
            'customer_id' => $customerId,
            'email' =>  $customerEmail,
            'ip_address' => $ipAddress
        );
        $request = array_merge($request, $parameters);

        try {
            $ipqsInterface = $this->ipqsApiRequestFactory->create();
            $ipqsInterface->setData('type', $type);
            $ipqsInterface->setData('fraud_score', $fraudScore);
            $ipqsInterface->setData('request', json_encode($request));
            $ipqsInterface->setData('response', json_encode($result));
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

        try {
            $orderScoreInterface = $this->orderScoreFactory->create();
            $orderScoreInterface->setData('fraud_score', $fraudScore);
            $orderScoreInterface->setData('risk_score', $riskScore);
            $orderScoreInterface->setData('order_id', $orderId);
            $this->orderScoreResource->save($orderScoreInterface);
        } catch (\Exception $e) {
            $this->logger->info('Exception saving IPQS score: ' . $e->getMessage());
        }
    }

    /**
     * @param $order
     * @param $fraudScore
     */
    protected function updateOrderStatus($result, $order) {
        $riskScore = $result[self::IPQS_PARAM_TRANSACTION_DETAILS][self::IPQS_PARAM_RISK_SCORE];
        $fraudScore = $result[self::IPQS_PARAM_FRAUD_SCORE];

        if ($riskScore > 85) {
            $outcome = 'cancel_order';
        } else if ($riskScore > 65) {
            $outcome = 'review_order';
        }

        try {
            // update order status
            if ($outcome == 'legit') {
                $this->logger->info('##### ##### legit order');
                $order->addStatusToHistory($order->getStatus(), 'Legit order, IPQS fraud score: ' . $fraudScore . '; risk score: ' . $riskScore, false);
            } else if ($outcome == 'review_order') {
                $this->logger->info('##### ##### block but do not cancel this order');
                $order->setHoldBeforeState($order->getState());
                $order->setHoldBeforeStatus($order->getStatus());
                $order->setState(Order::STATE_HOLDED);
                $order->setStatus(Order::STATUS_FRAUD);
                $order->addStatusToHistory(Order::STATUS_FRAUD, 'Setting order status to suspected fraud and state to on hold - for order review.  IPQS fraud score: ' . $fraudScore . '; risk score: ' . $riskScore, false);
                //$this->orderRepository->save($order);
            } else if ($outcome == 'cancel_order') {
                $this->logger->info('##### ##### cancel order');
                $order->setState(Order::STATE_CANCELED);
                $order->setStatus(Order::STATUS_FRAUD);
                $order->addStatusToHistory(Order::STATUS_FRAUD, 'Setting order status to suspected fraud and state cancel.  IPQS fraud score: ' . $fraudScore . '; risk score: ' . $riskScore, false);
                //$this->orderRepository->save($order);
            }

        } catch (\Throwable $exception) {
            $this->logger->critical('Exception in updateOrderStatus -- ' . $exception->getMessage());
            // let order complete
        }
    }
}
