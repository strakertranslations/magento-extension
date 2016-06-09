<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\SampleOne;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface; // Needed to retrieve config values

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var scopeConfig
     * Needed to retrieve config values
     */
    protected $scopeConfig;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ScopeConfigInterface $scopeConfig // Needed to retrieve config values
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig; // Needed to retrieve config values
    }

    /**
     * Index Action*
     * @return void
     */
    public function execute()
    {
        /** @var \MAgento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
//        $resultPage->setActiveMenu('Straker_EasyTranslationPlatform::sampleTwo');
//        /*$resultPage->addBreadcrumb(__('System'), __('System'));  This also changes page title in tab name
//        $resultPage->addBreadcrumb(__('SampleTwo'), __('SampleTwo'));*/
        $resultPage->getConfig()->getTitle()->prepend(__('Create New Job')); // Changing the page title
//        // You will notice that this block 'Two' is defined in the template file
//        $resultPage->getLayout()->getBlock('Two')->setSampleText('This text is passed'); // Here we use Magento2's Magic getsetters
//        // Retrieving config value and passing it to template
//        $cfg_text = $this->scopeConfig->getValue('adminsample/txt/textsample');
//        $resultPage->getLayout()->getBlock('Two')->setCfgSample($cfg_text);
        return $resultPage;
    }
}