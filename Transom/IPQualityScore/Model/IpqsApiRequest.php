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


class IpqsApiRequest extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{

    const CACHE_TAG = 'ipqs_api_request';

    protected $_cacheTag = 'ipqs_api_request';

    protected $_eventPrefix = 'ipqs_api_request';

    protected function _construct()
    {
        $this->_init('Transom\IPQualityScore\Model\ResourceModel\IpqsApiRequest');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }

}
