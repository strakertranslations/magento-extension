<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Jobs;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;

class NewAction extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    protected $resultForwardFactory;
    protected $_configHelper;
//    public $resultRedirectFactory;


    /**
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        ConfigHelper $configHelper
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_configHelper = $configHelper;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return true;
        // return $this->_authorization->isAllowed('Straker_Job::attachment_save');
    }

    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        if($this->_configHelper->isSandboxMode()){
            $this->messageManager->addNotice($this->_configHelper->getSandboxMessage());
        }
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
