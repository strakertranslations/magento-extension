<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Job;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $_pageFactory;

    public $resultRedirectFactory;

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
        
        $resultPage = $this->_pageFactory->create();
        $resultPage->setActiveMenu('Straker_EasyTranslationPlatform::post');
        $resultPage->addBreadcrumb(__('Blog Posts'), __('Blog Posts'));
        $resultPage->addBreadcrumb(__('Manage Blog Posts'), __('Manage Blog Posts'));
        $resultPage->getConfig()->getTitle()->prepend(__('Blog Posts'));

        return $resultPage;
    }

}