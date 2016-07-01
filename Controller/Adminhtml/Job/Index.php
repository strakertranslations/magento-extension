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

        $resultRedirect = $this->resultRedirectFactory->create();

//        if(stripos($this->_redirect->getRefererUrl(), 'Languagepairs')===false)
//        {
//            $resultRedirect->setPath('/Setup_Storelanguage/index/');
//
//            return $resultRedirect;
//        }

        $resultPage = $this->_pageFactory->create();
        $resultPage->setActiveMenu('Straker_EasyTranslationPlatform::post');
        $resultPage->addBreadcrumb(__('Blog Posts'), __('Blog Posts'));
        $resultPage->addBreadcrumb(__('Manage Blog Posts'), __('Manage Blog Posts'));
        $resultPage->getConfig()->getTitle()->prepend(__('Blog Posts'));

        return $resultPage;
    }

}