<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Jobs;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Model\JobFactory;
use Straker\EasyTranslationPlatform\Model\StrakerAPI;
use Straker\EasyTranslationPlatform\Logger\Logger;
use Straker\EasyTranslationPlatform\Model;

class RefreshJob extends \Magento\Backend\App\Action
{

    protected $_coreRegistry;
    protected $_resultJsonFactory;
    protected $_configHelper;
    protected $_strakerApi;
    protected $_jobFactory;
    protected $_logger;

    /**
     * RefreshJob constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param ConfigHelper $configHelper
     * @param JsonFactory $resultJsonFactory
     * @param StrakerAPI $strakerAPI
     * @param JobFactory $jobFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ConfigHelper $configHelper,
        JsonFactory $resultJsonFactory,
        StrakerAPI $strakerAPI,
        JobFactory $jobFactory,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_configHelper = $configHelper;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_strakerApi = $strakerAPI;
        $this->_jobFactory = $jobFactory;
        $this->_logger = $logger;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $jobKey = $this->getRequest()->getParam('job_key');
        $jobId = $this->getRequest()->getParam('job_id');
        $result = [ 'Success' => true, 'Action' => ''];
        if(empty($jobKey)){
            $result['Action'] = 'refresh all jobs';
        }else{
            $return = $this->_strakerApi->getTranslation([
                'job_key' => $jobKey
            ]);

            if( isset( $return->job) && count( $return->job) > 0 ){
                $jobData = $return->job[0];
                if( !empty( $jobData ) ){
                    $oldJob = $this->_jobFactory->create()->load( $jobId );
                    if( strcasecmp($jobData->status, $oldJob->getJobStatus() ) !== 0
                        || (strcasecmp($jobData->status, $oldJob->getJobStatus() ) === 0 &&
                            strcasecmp($jobData->status, 'queued') === 0 &&
                            strcasecmp($jobData->quotation, 'ready') === 0))
                    {
                        //TODO: add updateStatus to Job model
                        $oldJob->updateStatus( $jobData );
                        $result['action'] = $jobData->status;
                        $this->messageManager->addSuccessMessage( __('The status has been updated!') );
                    }
                    else
                    {
                        $result['Success'] = false;
                        $result['Action'] = __('The Job has not been update.');
                        $this->_logger->addInfo( $result['Action'],['job_id'=> $jobId] );
                        $this->messageManager->addNoticeMessage( $result['Action'] );
                    }
                }
            }else{
                $result['Success'] = false;
                $result['Action'] = __('There are problems in the Internet Connection');
                $this->_logger->addError( $result['Action'], ['job_id'=>$jobId] );
                $this->messageManager->addWarningMessage( $result['Action'] );
            }
        }
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
