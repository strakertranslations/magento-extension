<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob\Widget;

/**
 * Button widget
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Breadcrumbs extends \Magento\Backend\Block\Widget
{
    /**
     * Define block template
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setTemplate('Straker_EasyTranslationPlatform::widget/breadcrumbs.phtml');
        parent::_construct();
    }
}
