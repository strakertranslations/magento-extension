<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml;

use Magento\Framework\View\Element\Template;

class Test1 extends Template
{
    protected function _prepareLayout()
    {
        $this->setMessage('Hello Again World');
        $this->setName($this->getRequest()->getParam('name'));
    }

    public function getGoodbyeMessage()
    {
        return 'Goodbye World';
    }
}