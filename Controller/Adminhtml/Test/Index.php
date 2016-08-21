<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Test;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $_storeManager;
    protected $_pageFactory;

    public function __construct(
        Context $context,
        PageFactory $pageFactory
    )
    {
        $this->_pageFactory = $pageFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $page_object = $this->_pageFactory->create();

        return $page_object;

    }

}