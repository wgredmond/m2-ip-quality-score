<?php
/**
 * Transom Group Inc.
 *
 *
 * @category    Transom
 * @package     Transom_Group
 * @copyright   Copyright (c) Transom Group. All rights reserved. (https://transom-group.com/)
 */

namespace Transom\IPQualityScore\Observer\Events;

use DateTime;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Transom\IPQualityScore\Model\ConfigSettings;


class CreateOrderObserver implements ObserverInterface
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Transom\IPQualityScore\Model\ConfigSettings
     */
    protected $config;

    /**
     * @var DateTime
     */
    protected $eventDate;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;


    /**
     * CreateOrderObserver constructor.
     *
     * @param LoggerInterface $logger
     * @param ConfigSettings $config
     * @param DateTime $eventDate
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(LoggerInterface $logger,
                                ConfigSettings $config,
                                DateTime $eventDate,
                                OrderRepositoryInterface $orderRepository,
                                RemoteAddress $remoteAddress)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->eventDate =  $eventDate;
        $this->orderRepository = $orderRepository;
        $this->remoteAddress = $remoteAddress;
    }


    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // only process order if this service is enable
        if (!$this->config->isApiActive()) {
            return $this;
        }

        // get credentials, call IP quality score
        if ($this->config->isApiAccessKeysInAdmin()) {
            // TODO: call ip quality score api
            $apiKey = $this->config->getApiKey();
        } else {
            // TODO get creds from env
            $this->logger->info('TODO hook up dont manage credentials in admin option.');
            return $this;
        }

        // get payment
        $payment = $observer->getData('payment');

        // get order
        $order = $payment->getOrder();

        //  if order data is empty then doesn't need to process
        if (empty($order)) {
            $this->logger->info('There is an error in CreateOrderObserver');
            return $this;
        }

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
        $billingName = $billingFirstName . " " . $billingLastName;
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

        // populate shipping address variables
        $shippingAddress = $order->getShippingAddress();
        $shippingFirstName = $shippingAddress->getFirstname();
        $shippingLastName = $shippingAddress->getLastName();
        $shippingName = $shippingFirstName . " " . $shippingLastName;
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

        // populate event variables
        $eventTime = $this->eventDate->format('Y-m-d\TH:i:s.') . gettimeofday()['usec'] . 'Z';
        $eventId = $orderId . '-' . $this->eventDate->format('Y-m-d_H-i-s-') . gettimeofday()['usec'];

        // populate session variables
        $ipAddress = $this->remoteAddress->getRemoteAddress();
        //$session            = $this->customerSession->getMyValue();
        $userAgent = $_SERVER ['HTTP_USER_AGENT'];

        try {

        } catch (\Throwable $exception) {
            $this->logger->critical('Exception in Transom CreateOrderObserver -- ' . $exception->getMessage());
            // let order complete
        }
    }
}
