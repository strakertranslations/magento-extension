<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product mass attribute update websites tab
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\Edit\Tab;

use Magento\Store\Model\Group;
use Magento\Store\Model\Website;

class Categories extends \Magento\Catalog\Block\Adminhtml\Category\Tree implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * Tab settings
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('3. &nbsp; Select Categories');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Categories');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
