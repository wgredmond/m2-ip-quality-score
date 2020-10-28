<?php
namespace Transom\IPQualityScore\Model\Config\Settings\Plan;

class Type implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => 'free', 'label' => __('Free')],
            ['value' => 'premium', 'label' => __('Premium')],
            ['value' => 'enterprise_mini', 'label' => __('Enterprise Mini')],
            ['value' => 'enterprise', 'label' => __('Enterprise')],
            ['value' => 'enterprise_plus', 'label' => __('Enterprise Plus')]
        ];
    }

    public function toArray()
    {
        return [
            'free' => __('Free'),
            'premium' => __('Premium'),
            'enterprise_mini' => __('Enterprise Mini'),
            'enterprise' => __('Enterprise'),
            'enterprise_plus' => __('Enterprise Plus')
        ];
    }
}
