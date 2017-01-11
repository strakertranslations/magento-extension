<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Jobs;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

use Straker\EasyTranslationPlatform\Block\Adminhtml\Job;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Helper\ImportHelper;
use Straker\EasyTranslationPlatform\Model\JobFactory;
use Straker\EasyTranslationPlatform\Logger\Logger;
use Straker\EasyTranslationPlatform\Model\JobStatus;

use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;

class Reimport extends \Magento\Backend\App\Action
{

    protected $_jobFactory;
    protected $_logger;
    protected $_storeManager;
    protected $_configHelper;
    protected $_importHelper;
    protected $_strakerApi;

    public function __construct(
        Context $context,
        ConfigHelper $configHelper,
        ImportHelper $importHelper,
        JobFactory $jobFactory,
        Logger $logger,
        StoreManagerInterface $storeManager,
        StrakerAPIInterface $strakerAPI
    ) {
        $this->_jobFactory = $jobFactory;
        $this->_configHelper = $configHelper;
        $this->_importHelper = $importHelper;
        $this->_logger = $logger;
        $this->_storeManager = $storeManager;
        $this->_strakerApi = $strakerAPI;
        parent::__construct($context);
    }

    public function execute()
    {
        $jobData = $this->_jobFactory->create()->load($this->getRequest()->getParam('job_id'));
        $originalTranslatedFile = $this->_configHelper->getTranslatedXMLFilePath().'/'.$jobData->getData('translated_file');
        $newTranslatedFile = $this->_configHelper->getTranslatedXMLFilePath().'/old_'.time().'_'.$jobData->getData('translated_file');
        rename($originalTranslatedFile, $newTranslatedFile);
        $file_content = $this->_strakerApi->getTranslatedFile($jobData->getData('download_url'));
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            file_put_contents($this->_configHelper->getTranslatedXMLFilePath().'/'.$jobData->getData('translated_file'), $file_content);
            $this->_importHelper->create($jobData->getData('job_id'))
                ->parseTranslatedFile()
                ->saveData();
            $jobData->setData('job_status_id', JobStatus::JOB_STATUS_COMPLETED)->save();
            $this->messageManager->addSuccess('Translated '.$jobData->getData('job_number').' data has been re-imported for '.$this->_storeManager->getStore($jobData->getData('target_store_id'))->getName().' store');
            $resultRedirect->setPath('*/*/index');
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_logger->error('error'.__FILE__.' '.__LINE__, [$e]);
            $this->_strakerApi->_callStrakerBugLog(__FILE__ . ' ' . __METHOD__ . ' ' . $e->getMessage(), $e->__toString());
            $this->messageManager->addError('Translated data has not been re-imported for '.$this->_storeManager->getStore($jobData->getData('target_store_id'))->getName().' store');
            $resultRedirect->setPath('*/*/index');
            return $resultRedirect;
        }
        return $resultRedirect;
    }
}
