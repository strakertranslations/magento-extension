<?php
namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Job;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Straker_EasyTranslationPlatform::job');
        $resultPage->addBreadcrumb(__('EasyTranslationPlatform Jobs'), __('EasyTranslationPlatform Jobs'));
        $resultPage->addBreadcrumb(__('Manage EasyTranslationPlatform Jobs'), __('Manage EasyTranslationPlatform Jobs'));
        $resultPage->getConfig()->getTitle()->prepend(__('EasyTranslationPlatform Jobs'));

        return $resultPage;
    }

    /**
     * Is the user allowed to view the blog job grid.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Straker_EasyTranslationPlatform::job');
    }
}
