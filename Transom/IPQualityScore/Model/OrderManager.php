<?php
/**
 * Transom Group Inc.
 *
 *
 * @category    Transom
 * @package     Transom_Group
 * @copyright   Copyright (c) Transom Group. All rights reserved. (https://transom-group.com/)
 */

namespace Transom\IPQualityScore\Model;

use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class OrderManager {

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Transom\IPQualityScore\Model\ConfigSettings
     */
    protected $config;

    public function __construct(LoggerInterface $logger,
                                ConfigSettings $config) {
        $this->logger = $logger;
        $this->config = $config;
    }


    /**
     * @param $order
     * @param $fraudScore
     */
    public function updateOrderStatus($fraudScore, $riskScore, $order) {

        if ($fraudScore > $this->config->getCancelThreshold()) {
            $outcome = 'cancel_order';
        } else if ($fraudScore > $this->config->getReviewThreshold()) {
            $outcome = 'review_order';
        }

        try {
            // update order status
            if ($outcome == 'legit') {
                $order->addStatusToHistory($order->getStatus(), 'Legit order, IPQS fraud score: ' . $fraudScore . '; Transaction risk score: ' . $riskScore, false);
            } else if ($outcome == 'review_order') {
                if ($this->config->isUpdateReview()) {
                    $order->setHoldBeforeState($order->getState());
                    $order->setHoldBeforeStatus($order->getStatus());
                    $order->setState(Order::STATE_HOLDED);
                    $order->setStatus(Order::STATUS_FRAUD);
                    $order->addStatusToHistory(Order::STATUS_FRAUD, 'Setting order status to suspected fraud and state to on hold - for order review.  IPQS fraud score: ' . $fraudScore . '; Transaction risk score: ' . $riskScore, false);
                } else {
                    $order->addStatusToHistory($order->getStatus(), 'Update order status on review is disabled.  This order would have been placed in hold state. IPQS fraud score: ' . $fraudScore . '; Transaction risk score: ' . $riskScore, false);
                }
            } else if ($outcome == 'cancel_order') {
                if ($this->config->isUpdateCancel()) {
                    $order->setState(Order::STATE_CANCELED);
                    $order->setStatus(Order::STATUS_FRAUD);
                    $order->addStatusToHistory(Order::STATUS_FRAUD, 'Setting order status to suspected fraud and state cancel.  IPQS fraud score: ' . $fraudScore . '; Transaction risk score: ' . $riskScore, false);
                } else {
                    $order->addStatusToHistory($order->getStatus(), 'Update order state to cancel for fraudlent orders is disabled.  This order has been identified as a fraudlent order. IPQS fraud score: ' . $fraudScore . '; Transaction risk score: ' . $riskScore, false);
                }
            }

        } catch (\Throwable $exception) {
            $this->logger->critical('Exception in updateOrderStatus -- ' . $exception->getMessage());
            // let order complete
        }
    }
}
