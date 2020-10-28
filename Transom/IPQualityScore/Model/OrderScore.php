<?php


namespace Transom\IPQualityScore\Model;


class OrderScore extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{

    const CACHE_TAG = 'ipqs_order_score';

    protected $_cacheTag = 'ipqs_order_score';

    protected $_eventPrefix = 'ipqs_order_score';

    protected function _construct()
    {
        $this->_init('Transom\IPQualityScore\Model\ResourceModel\OrderScore');
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
