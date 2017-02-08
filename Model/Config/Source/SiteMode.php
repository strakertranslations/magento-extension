<?php
namespace Straker\EasyTranslationPlatform\Model\Config\Source;

class SiteMode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 0, 'label' => __('Sandbox')], ['value' => 1, 'label' => __('Live')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Sandbox'), 1 => __('Live')];
    }
}
