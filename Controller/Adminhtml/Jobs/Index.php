<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Jobs;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Model\JobFactory;
use Straker\EasyTranslationPlatform\Model\StrakerAPI;
use Straker\EasyTranslationPlatform\Logger\Logger;
use Magento\Framework\Registry;

class Index extends Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $_configHelper;
    protected $_strakerApi;
    protected $_jobFactory;
    protected $_logger;
    protected $_coreRegistry;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Logger $logger
     * @param StrakerAPI $strakerAPI
     * @param JobFactory $jobFactory
     * @param ConfigHelper $configHelper
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Logger $logger,
        StrakerAPI $strakerAPI,
        JobFactory $jobFactory,
        ConfigHelper $configHelper,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_strakerApi = $strakerAPI;
        $this->_logger = $logger;
        $this->_jobFactory = $jobFactory;
        $this->_configHelper = $configHelper;
        $this->_coreRegistry = $registry;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        if ($this->_configHelper->isSandboxMode()) {
            $this->messageManager->addNotice($this->_configHelper->getSandboxMessage());
        }
        $this->refreshJobs();
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Straker_EasyTranslationPlatform::managejobs');
        $resultPage->getConfig()->getTitle()->prepend(__('Straker Translations'));
        return $resultPage;
    }

    protected function refreshJobs()
    {
        $updatedJobs = [];
        $localJobIds = [];
        $result = ['status' => true, 'message' => ''];
        //refresh all jobs
        try {
            $apiData = $this->_strakerApi->getTranslation();
            if (isset($apiData->job)) {
                $apiJobs = $apiData->job;
                if (!empty($apiData) && count($apiJobs) > 0) {
                    foreach ($apiJobs as $apiJob) {
                        if ($apiJob->job_key) {
                            $localJobData = $this->_jobFactory->create()->getCollection()->addFieldToFilter('job_key', ['eq' => $apiJob->job_key ])->getItems();

                            if (!empty($localJobData)) {
//                                $localJob = reset( $localJobData );
//                                array_push( $localJobIds, $localJob->getId() );
//                                $isUpdate = $this->_compareJobs( $apiJob, $localJob );
//                                if( $isUpdate['isSuccess'] ){
//                                    array_push( $updatedJobs, $localJob->getId() );
//                                }
                                foreach ($localJobData as $key => $localJob) {
                                    array_push($localJobIds, $localJob->getId());
                                    $isUpdate = $this->_compareJobs($apiJob, $localJob);
                                    if ($isUpdate['isSuccess']) {
                                        $tjNumber = $localJob->getJobNumber();
                                        if (!empty($tjNumber) && !in_array($tjNumber, $updatedJobs)) {
                                            array_push($updatedJobs, $tjNumber);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if (count($updatedJobs) > 0) {
                        $this->_coreRegistry->register('job_updated', true );
                        $this->messageManager->addSuccessMessage(__(implode(', ', $updatedJobs)  .' has been updated.'));
                    } elseif (count($localJobIds) <= 0) {
                        $result['status'] = false;
                        $result['message'] = __('You have not created any job.');
                        $this->_logger->addInfo($result['message']);
                        $this->messageManager->addNoticeMessage($result['message']);
                    } else {
                        $result['status'] = false;
                        $result['message'] = __('All jobs are up to date.');
                        $this->_logger->addInfo($result['message']);
                        //$this->messageManager->addSuccessMessage( $result['message'] );
                    }
                } else {
                    $result['status'] = false;
                    $result['message'] =  __('No job has been found or failed to connect server.');
                    $this->messageManager->addErrorMessage($result['message']);
                    $this->_logger->addError($result['message']);
                }
            } else {
                $dataArray = (array)$apiData;
                $result['status'] = false;
                $result['message'] =  __( 'Server: ' . $dataArray['message'] );
//                $this->messageManager->addErrorMessage( $result['message'] );
                $this->_logger->addError($result['message']);
            }
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] =  __('Failed to connect server.');
            $this->messageManager->addErrorMessage($result['message']);
            $this->_logger->addError($result['message'], [ 'exception' => $e->getMessage() ]);
        }
    }

    protected function resolveApiStatus($apiJob)
    {
        $status = 0;
        if (!empty($apiJob) && !empty($apiJob->status)) {
            switch (strtolower($apiJob->status)) {
                case 'queued':
                    $status =  strcasecmp($apiJob->quotation, 'ready') == 0  ? 3 : 2;
                    break;
                case 'in_progress':
                    $status = 4;
                    break;
                case 'completed':
                    $status = 5;
                    break;
                default:
                    $status = 0;
                    break;
            }
        }

        return $status;
    }


    /**
     * @param $apiJob
     * @param $localJob
     * @return array
     */
    protected function _compareJobs($apiJob, $localJob)
    {

        if ($localJob->getJobStatusId() < $this->resolveApiStatus($apiJob)) {
            return $localJob->updateStatus($apiJob);
        }
        return ['isSuccess' => false, 'Message'=> __('The status is up to date') ];
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
