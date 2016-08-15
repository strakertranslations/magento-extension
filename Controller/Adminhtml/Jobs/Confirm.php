<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Jobs;

use Magento\Backend\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;

use Straker\EasyTranslationPlatform\Block\Adminhtml\Job;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Helper\ImportHelper;
use Straker\EasyTranslationPlatform\Model\JobFactory;
use Straker\EasyTranslationPlatform\Logger\Logger;

class Confirm extends \Magento\Backend\App\Action
{

    protected $_jobFactory;
    protected $_logger;
    protected $_storeManager;

    public function __construct(
        Context $context,
        ConfigHelper $configHelper,
        ImportHelper $importHelper,
        JobFactory $jobFactory,
        Logger $logger,
        StoreManagerInterface $storeManager

    ) {
        $this->_jobFactory = $jobFactory;
        $this->_configHelper = $configHelper;
        $this->_importHelper = $importHelper;
        $this->_logger = $logger;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $job_id = $this->getRequest()->getParam('job_id');

        $resultRedirect = $this->resultRedirectFactory->create();

        $job = $this->_jobFactory->create()->load($job_id);

        try{

            $this->_importHelper->create($job_id)->parseTranslatedFile()->saveTranslatedProductData()->publishTranslatedProductData();

            $job->addData(['job_status_id'=>6]);

            $job->save();

            $this->messageManager->addSuccess('Translated data has been published for '.$this->_storeManager->getStore($job->getData('target_store_id'))->getName().' store');

            $resultRedirect->setPath('*/*/index');

            return $resultRedirect;


        } catch (\Magento\Framework\Exception\LocalizedException $e) {

            $this->messageManager->addError($e->getMessage());

            $this->_logger->error('error'.__FILE__.' '.__LINE__,array($e));

            $this->messageManager->addError('Translated data has not been published for'.$job->getData('target_store_id').' store');

            $resultRedirect->setPath('*/*/index');

            return $resultRedirect;
        }

    }
}