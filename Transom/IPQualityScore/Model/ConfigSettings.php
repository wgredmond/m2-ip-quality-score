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


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 *
 * Class for retrieving configuration settings.
 */
class ConfigSettings
{

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }


    /**
     * IP Quality Score API feature enabled
     *
     * @return bool
     */
    public function isApiActive()
    {
        $enabled = $this->scopeConfig->isSetFlag(
            'trust_and_safety/transom_ip_quality_score/api_enabled',
            ScopeInterface::SCOPE_WEBSITE
        );
        return $enabled;
    }


    /**
     * IP Quality Score API End Point
     *
     * @return string
     */
    public function getApiEndPoint()
    {
        $apiKey = $this->scopeConfig->getValue(
            'trust_and_safety/transom_ip_quality_score/api_end_point',
            ScopeInterface::SCOPE_WEBSITE
        );
        return $apiKey;
    }


    /**
     * IP Quality Score API Key
     *
     * @return string
     */
    public function getApiKey()
    {
        $iamKey = $this->scopeConfig->getValue(
            'trust_and_safety/transom_ip_quality_score/api_key',
            ScopeInterface::SCOPE_WEBSITE
        );
        return $iamKey;
    }


    /**
     * IP Quality Score Plan Type
     *
     * @return string
     */
    public function getPlanType()
    {
        $planType = $this->scopeConfig->getValue(
            'trust_and_safety/transom_ip_quality_score/plan_type',
            ScopeInterface::SCOPE_WEBSITE
        );
        return intval($planType);
    }


    /**
     * @return string
     */
    public function getFast()
    {
        $fast = $this->scopeConfig->isSetFlag(
            'trust_and_safety/transom_ip_quality_score/fast',
            ScopeInterface::SCOPE_WEBSITE
        );
        return $fast ? 'true' : 'false';
    }


    /**
     * @return mixed
     */
    public function getStrictness()
    {
        $strictness = $this->scopeConfig->getValue(
            'trust_and_safety/transom_ip_quality_score/strictness',
            ScopeInterface::SCOPE_WEBSITE
        );
        return intval($strictness);
    }


    /**
     * @return string
     */
    public function getAllowPublicAccessPoints()
    {
        $allowPublicAccessPoints = $this->scopeConfig->isSetFlag(
            'trust_and_safety/transom_ip_quality_score/allow_public_access_points',
            ScopeInterface::SCOPE_WEBSITE
        );
        return $allowPublicAccessPoints ? 'true' : 'false';
    }


    /**
     * @return string
     */
    public function getLighterPenalties()
    {
        $lighterPenalties = $this->scopeConfig->isSetFlag(
            'trust_and_safety/transom_ip_quality_score/lighter_penalties',
            ScopeInterface::SCOPE_WEBSITE
        );
        return $lighterPenalties ? 'true' : 'false';
    }


    /**
     * @return int
     */
    public function getCancelThreshold()
    {
        $cancelThreshold = $this->scopeConfig->getValue(
            'trust_and_safety/transom_ip_quality_score/cancel_threshold',
            ScopeInterface::SCOPE_WEBSITE
        );
        return intval($cancelThreshold);
    }


    /**
     * IP Quality Score API feature enabled
     *
     * @return bool
     */
    public function isUpdateCancel()
    {
        $update = $this->scopeConfig->isSetFlag(
            'trust_and_safety/transom_ip_quality_score/cancel_update_order_status',
            ScopeInterface::SCOPE_WEBSITE
        );
        return $update;
    }


    /**
     * @return int
     */
    public function getReviewThreshold()
    {
        $reviewThreshold = $this->scopeConfig->getValue(
            'trust_and_safety/transom_ip_quality_score/review_threshold',
            ScopeInterface::SCOPE_WEBSITE
        );
        return intval($reviewThreshold);
    }


    /**
     * IP Quality Score API feature enabled
     *
     * @return bool
     */
    public function isUpdateReview()
    {
        $update = $this->scopeConfig->isSetFlag(
            'trust_and_safety/transom_ip_quality_score/review_update_order_status',
            ScopeInterface::SCOPE_WEBSITE
        );
        return $update;
    }
}
