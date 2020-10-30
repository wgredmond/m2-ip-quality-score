<?php
namespace Transom\IPQualityScore\Model\Config\Settings\Plan;

class Type implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Free')],
            ['value' => 1, 'label' => __('Premium')],
            ['value' => 2, 'label' => __('Enterprise Mini')],
            ['value' => 3, 'label' => __('Enterprise')],
            ['value' => 4, 'label' => __('Enterprise Plus')]
        ];
    }

    public function toArray()
    {
        return [
            0 => __('Free'),
            1 => __('Premium'),
            2 => __('Enterprise Mini'),
            3 => __('Enterprise'),
            4 => __('Enterprise Plus')
        ];
    }
}
