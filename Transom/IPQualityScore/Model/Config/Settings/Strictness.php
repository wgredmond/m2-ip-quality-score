<?php
namespace Transom\IPQualityScore\Model\Config\Settings;

class Strictness implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('0')],
            ['value' => 1, 'label' => __('1')],
            ['value' => 2, 'label' => __('2')],
            ['value' => 3, 'label' => __('3')]
        ];
    }

    public function toArray()
    {
        return [
            0 => __('0'),
            1 => __('1'),
            2 => __('2'),
            3 => __('3')
        ];
    }
}
