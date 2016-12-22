<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Setup\LanguagePairs;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;

    public function __construct(
        Context $context,
        PageFactory $pageFactory
    ) {
        $this->_pageFactory = $pageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $page_object = $this->_pageFactory->create();
        $page_object->getConfig()->getTitle()->prepend(__('Straker Translations'));
        return $page_object;
    }
}
