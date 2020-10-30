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
            'fraud_protection/transom_ip_quality_score/api_enabled',
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
            'fraud_protection/transom_ip_quality_score/api_end_point',
            ScopeInterface::SCOPE_WEBSITE
        );
        return $apiKey;
    }


    /**
     * Is IP Quality Score - Manage API access keys in admin?
     *
     * @return bool
     */
    public function isApiAccessKeysInAdmin()
    {
        $enabled = $this->scopeConfig->isSetFlag(
            'fraud_protection/transom_ip_quality_score/api_access_keys_enabled',
            ScopeInterface::SCOPE_WEBSITE
        );
        return $enabled;
    }


    /**
     * IP Quality Score API Key
     *
     * @return string
     */
    public function getApiKey()
    {
        $iamKey = $this->scopeConfig->getValue(
            'fraud_protection/transom_ip_quality_score/api_key',
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
            'fraud_protection/transom_ip_quality_score/plan_type',
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
            'fraud_protection/transom_ip_quality_score/fast',
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
            'fraud_protection/transom_ip_quality_score/strictness',
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
            'fraud_protection/transom_ip_quality_score/allow_public_access_points',
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
            'fraud_protection/transom_ip_quality_score/lighter_penalties',
            ScopeInterface::SCOPE_WEBSITE
        );
        return $lighterPenalties ? 'true' : 'false';
    }
}
