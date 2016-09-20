<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */

    protected function _construct()
    {
        parent::_construct();
        $this->setId('job_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Job Information'));
    }

}
