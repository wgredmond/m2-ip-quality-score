<?php

namespace Transom\IPQualityScore\Model\ResourceModel;

class IpqsApiRequest extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    )
    {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('ipqs_api_request', 'ipqs_api_request_id');
    }

}
