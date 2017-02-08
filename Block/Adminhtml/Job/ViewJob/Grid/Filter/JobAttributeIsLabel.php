<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob\Grid\Filter;

use Magento\Backend\Block\Widget\Grid\Column\Filter\Select;

/**
 * Adminhtml newsletter subscribers grid website filter
 */
class JobAttributeIsLabel extends Select
{
    /**
     * @var array
     */
    protected static $_isLabel;

    /**
     * @return void
     */
    protected function _construct()
    {
        self::$_isLabel = [
            null => null,
            '1' => __('Yes'),
            '0' => __('No')
        ];
        parent::_construct();
    }

    /**
     * @return array
     */
    protected function _getOptions()
    {
        $options = [];
        foreach (self::$_isLabel as $k => $v) {
            $options[] = ['value' => $k, 'label' => __($v)];
        }

        return $options;
    }

    /**
     * @return array|null
     */
    public function getCondition()
    {
        return $this->getValue() === null ? null : ['eq' => $this->getValue()];
    }
}
