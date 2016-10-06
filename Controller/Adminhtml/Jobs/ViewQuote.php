<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Jobs;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Model\JobFactory;

class ViewQuote extends \Magento\Backend\App\Action
{

    protected $_coreRegistry;
    protected $_resultJsonFactory;
    protected $_configHelper;
    protected $_jobFactory;

    /**
     * ViewQuote constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param ConfigHelper $configHelper
     * @param JsonFactory $resultJsonFactory
     * @param JobFactory $jobFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ConfigHelper $configHelper,
        JsonFactory $resultJsonFactory,
        JobFactory $jobFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_configHelper = $configHelper;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_jobFactory = $jobFactory;
    }


    public function execute()
    {
        $jobId = $this->getRequest()->getParam('job_id');
        $jobKey = $this->_jobFactory->create()->load($jobId)->getJobKey();
        $quoteUrl = $this->_configHelper->getPaymentPageUrl().'&job_key='.$jobKey;
        $result = [ 'Success'=> true, 'JobKey' => $quoteUrl ];
        return $this->_resultJsonFactory->create()->setData($result);
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
