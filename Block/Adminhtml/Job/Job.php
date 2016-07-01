<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job;

use Magento\Backend\Block\Widget\Grid\Container;

class Jobs extends Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_news';
        $this->_blockGroup = 'Tutorial_SimpleNews';
        $this->_headerText = __('Manage News');
        $this->_addButtonLabel = __('Add News');
        parent::_construct();
    }
}