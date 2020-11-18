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


class OrderObserver implements ObserverInterface
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
     * CreateAccountObserver constructor.
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
        $localDebug = true;
        if ($localDebug) {
            $this->logger->info(' OrderObserver BEGIN [' . microtime() . ']');
        }
        // only process order if this service is enable
        if (!$this->config->isApiActive()) {
            if ($localDebug) {
                $this->logger->info(' OrderObserver END [' . microtime() . ']');
            }
            return $this;
        }


        // TODO - start debug
        if ($localDebug) {
            $eventName = $observer->getEvent()->getName();
            $this->logger->info(' [' . $eventName . '] data type = ' . getType($observer->getData()));
            $this->logger->info(' [' . $eventName . '] event name = ' . $observer->getEvent()->getName());
            foreach ($observer->getData() as $key => $value) {
                $this->logger->info(' [' . $eventName . '] data[' . $key . '] type = ' . getType($value));
                if (getType($value) === 'object') {
                    $this->logger->info(' [' . $eventName . '] object type = ' . get_class($value));
                }
            }
        }
        // TODO - end debug


        // get payment
        if ( ($eventName === 'sales_order_payment_place_end') OR ($eventName === 'sales_order_payment_place_start') ){
            $payment = $observer->getData('payment');

            // get order
            $order = $payment->getOrder();
        } else {
            $payment = null;
            $order = $observer->getData('order');
        }

        //Warning: get_class() expects parameter 1 to be object,
        // array given in /var/www/vhosts/dev.m2.local.com/app/code/Transom/IPQualityScore/Observer/Events/OrderObserver.php on line 119

        // Recoverable Error: Object of class Magento\Sales\Api\Data\OrderExtension could not be converted to string in /var/www/vhosts/dev.m2.local.com/app/code/Transom/IPQualityScore/Observer/Events/OrderObserver.php on line 99

        // TODO - start debug - order
        if ($localDebug) {
            if ($order) {
                $this->logger->info(' [' . $eventName . '] order class = ' . get_class($order));
                foreach ($order->getData() as $key => $value) {
                    if (is_array($value) || (getType($value) === 'object')) {
                        $this->logger->info(' [' . $eventName . '] order data[' . $key . '] type = ' . getType($value));
                    } else {
                        $this->logger->info(' [' . $eventName . '] order data[' . $key . '] type = ' . getType($value) . ';value is: ' . $value);
                    }
                    if (getType($value) === 'object') {
                        $this->logger->info(' [' . $eventName . '] order object type = ' . get_class($value));
                    }
                }
                $this->logger->info(' [' . $eventName . '] order; id = ' . $order->getId());
                $this->logger->info(' [' . $eventName . '] order; entity id = ' . $order->getEntityId());
                $this->logger->info(' [' . $eventName . '] order; increment id = ' . $order->getIncrementId());
            }
        }
        // TODO - end debug - order

        // TODO - start debug - payment
        if ($localDebug) {
            if ($payment) {
                $this->logger->info(' [' . $eventName . '] payment class = ' . get_class($payment));

                foreach ($payment->getData() as $key => $value) {
                    if (is_array($value) || (getType($value) === 'object')) {
                        $this->logger->info(' [' . $eventName . '] payment data[' . $key . '] type = ' . getType($value));
                    } else {
                        $this->logger->info(' [' . $eventName . '] payment data[' . $key . '] type = ' . getType($value) . ';value is: ' . $value);
                    }
                    if (getType($value) === 'object') {
                        $this->logger->info(' [' . $eventName . '] payment object type = ' . get_class($value));
                    }
                }

                $this->logger->info(' [' . $eventName . '] processPayment; entity id = ' . $payment->getEntityId());
                $this->logger->info(' [' . $eventName . '] processPayment; amount authorized = ' . $payment->getAmountAuthorized());
                $this->logger->info(' [' . $eventName . '] processPayment; method = ' . $payment->getMehtod());
                $this->logger->info(' [' . $eventName . '] processPayment; cc type = ' . $payment->getCcType());
                $this->logger->info(' [' . $eventName . '] processPayment; cc cid status = ' . $payment->getCcCidStatus());
                $this->logger->info(' [' . $eventName . '] processPayment; cc status = ' . $payment->getCcStatus());
                $this->logger->info(' [' . $eventName . '] processPayment; cc trans id = ' . $payment->getCcTransId());
                $this->logger->info(' [' . $eventName . '] processPayment; cc last 4 = ' . $payment->getCcLast4());
                $this->logger->info(' [' . $eventName . '] processPayment; cc exp month = ' . $payment->getCcExpMonth());
                $this->logger->info(' [' . $eventName . '] processPayment; cc exp year = ' . $payment->getCcExpYear());
                $this->logger->info(' [' . $eventName . '] processPayment; parent id = ' . $payment->getParentId());
                $this->logger->info(' [' . $eventName . '] processPayment; transaction id = ' . $payment->getTransactionId());
                $this->logger->info(' [' . $eventName . '] processPayment; can capture = ' . $payment->canCapture());
                if ($payment->getAdditionalInformation()) {
                    //$this->logger->info(' [' . $eventName . '] payment additional information class = ' . get_class($payment->getAdditionalInformation()));
                    foreach ($payment->getAdditionalInformation() as $key => $value) {
                        $this->logger->info(' [' . $eventName . '] additional information [' . $key . '] type = ' . getType($value));
                        if (getType($value) === 'object') {
                            $this->logger->info(' [' . $eventName . '] additional information object type = ' . get_class($value));
                        }
                    }
                }
                if ($payment->getTransactionAdditionalInfo()) {
                    $this->logger->info(' [' . $eventName . '] payment transaction additional information class = ' . get_class($payment->getTransactionAdditionalInfo()));
                    foreach ($payment->getTransactionAdditionalInfo() as $key => $value) {
                        $this->logger->info(' [' . $eventName . '] transaction additional information [' . $key . '] type = ' . getType($value));
                        if (getType($value) === 'object') {
                            $this->logger->info(' [' . $eventName . '] transaction additional information object type = ' . get_class($value));
                        }
                    }
                }
            }
        }
        // TODO - end debug - payment


        //  if order data is empty then doesn't need to process
        if (empty($order)) {
            return $this;
        }

        if ($eventName === 'sales_order_payment_place_end') {
            $this->api->sendTransaction($order, $payment);
        }

        if ($eventName === 'sales_order_save_after') {
            $extAttribs = $order->getExtensionAttributes();
            $this->api->saveOrderScore($order->getId(), $extAttribs->getRiskScore());
        }

        if ($localDebug) {
            $this->logger->info(' OrderObserver END [' . microtime() . ']');
        }
    }

}
