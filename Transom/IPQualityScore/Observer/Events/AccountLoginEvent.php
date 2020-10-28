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

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Transom\IPQualityScore\Helper\Api;
use Transom\IPQualityScore\Model\ConfigSettings;

class AccountLoginEvent implements ObserverInterface
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
     * AccountLoginEvent constructor.
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
        // only process order if this service is enabled
        if (!$this->config->isApiActive()) {
            return $this;
        }

        $this->logger->info('##### In Transom IPQualityScore ##### AccountLoginEvent .');
        $this->api->sendLogin($observer->getEvent()->getCustomer());
    }
}
