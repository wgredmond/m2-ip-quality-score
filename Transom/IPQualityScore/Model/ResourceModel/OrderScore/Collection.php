<?php
namespace Transom\IPQualityScore\Model\ResourceModel\OrderScore;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'ipqs_order_score_id';
    protected $_eventPrefix = 'transom_ipqualityscore_score_collection';
    protected $_eventObject = 'score_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Transom\IPQualityScore\Model\OrderScore', 'Transom\IPQualityScore\Model\ResourceModel\OrderScore');
    }

}
