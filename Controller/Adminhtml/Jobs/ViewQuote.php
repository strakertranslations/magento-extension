<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Jobs;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;

class ViewQuote extends \Magento\Backend\App\Action
{

    protected $_coreRegistry;
    protected $_resultJsonFactory;
    protected $_configHelper;


    /**
     * ViewQuote constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param ConfigHelper $configHelper
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ConfigHelper $configHelper,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_configHelper = $configHelper;
        $this->_resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $jobKey = $this->getRequest()->getParam('job_key');
        $quoteUrl = $this->_configHelper->getPaymentPageUrl().'&job_key='.$jobKey;
        $result = [ 'Success'=> true, 'JobKey' => $quoteUrl ];
        return $this->_resultJsonFactory->create()->setData( $result );
    }

    /**
     * Is the user allowed to view the attachment grid.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Straker_EasyTranslationPlatform::jobs');
    }
}
