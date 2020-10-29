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
use Transom\IPQualityScore\Helper\Api;
use Transom\IPQualityScore\Model\ConfigSettings;


class CreateOrderEvent implements ObserverInterface
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
     * @var \Transom\IPQualityScore\Helper\Api
     */
    protected $api;


    /**
     * CreateAccountEvent constructor.
     * @param LoggerInterface $logger
     * @param ConfigSettings $config
     * @param Api $api
     */
    public function __construct(LoggerInterface $logger,
                                ConfigSettings $config,
                                Api $api)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->api = $api;
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

        $this->logger->info('##### In Transom IPQualityScore ##### CreateOrderEvent');

        // get payment
        $payment = $observer->getData('payment');

        // get order
        $order = $payment->getOrder();

        //  if order data is empty then doesn't need to process
        if (empty($order)) {
            $this->logger->info('There is an error in CreateOrderObserver');
            return $this;
        }

        $this->api->sendTransaction($order);
    }

}
