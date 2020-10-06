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
     * @param int|null $storeId
     * @return bool
     */
    public function isApiActive($storeId = null)
    {
        $enabled = $this->scopeConfig->isSetFlag(
            'fraud_protection/transom_ip_quality_score/api_enabled',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $enabled;
    }


    /**
     * IP Quality Score API End Point
     *
     * @param int|null $storeId
     * @return string
     */
    public function getApiEndPoint($storeId = null)
    {
        $apiKey = $this->scopeConfig->getValue(
            'fraud_protection/transom_ip_quality_score/api_end_point',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $apiKey;
    }


    /**
     * Is IP Quality Score - Manage API access keys in admin?
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isApiAccessKeysInAdmin($storeId = null)
    {
        $enabled = $this->scopeConfig->isSetFlag(
            'fraud_protection/transom_ip_quality_score/api_access_keys_enabled',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $enabled;
    }


    /**
     * IP Quality Score API Key
     *
     * @param int|null $storeId
     * @return string
     */
    public function getApiKey($storeId = null)
    {
        $iamKey = $this->scopeConfig->getValue(
            'fraud_protection/transom_ip_quality_score/api_key',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $iamKey;
    }
}
