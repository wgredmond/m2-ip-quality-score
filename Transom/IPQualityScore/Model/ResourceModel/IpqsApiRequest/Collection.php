<?php
namespace Transom\IPQualityScore\Model\ResourceModel\IpqsApiRequest;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'ipqs_api_request_id';
    protected $_eventPrefix = 'transom_ipqualityscore_request_collection';
    protected $_eventObject = 'request_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Transom\IPQualityScore\Model\IpqsApiRequest', 'Transom\IPQualityScore\Model\ResourceModel\IpqsApiRequest');
    }

}
