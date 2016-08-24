<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Jobs;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Straker\EasyTranslationPlatform\Helper\JobHelper;
use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;
use Straker\EasyTranslationPlatform\Api\Data\SetupInterface;
use Straker\EasyTranslationPlatform\Logger\Logger;

class Save extends Action
{

    protected $_setupInterface;


    protected $_multiSelectInputTypes = array(
        'select', 'multiselect'
    );

    protected $_storeConfigKeys = array(
        'magento_destination_store','straker_target_language','magento_source_store','straker_source_language'
    );


    protected $_jobRequest;

    /**
     * Save constructor.
     * @param Context $context
     * @param JobHelper $jobHelper
     * @param StrakerAPIInterface $API
     * @param SetupInterface $setup
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        JobHelper $jobHelper,
        StrakerAPIInterface $API,
        SetupInterface $setup,
        Logger $logger
    ) {

        $this->_api = $API;
        $this->_setupInterface = $setup;
        $this->_jobHelper = $jobHelper;
        $this->_logger = $logger;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * @return mixed
     *
     * Todo: Add field to identify job type when submitting new job
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        $resultRedirect = $this->resultRedirectFactory->create();

        $jobData = [];

        if ($data) {


            if(strlen($data['magento_source_store'])>0)
            {
                $this->_saveStoreConfigData($data);
            }

            if(isset($data['products']) && strlen($data['products'])>0)
            {
                $jobData[] = $this->_jobHelper->createJob($data)->generateProductJob()->save();
            }

            if(strlen ($data['categories'])>0)
            {
                $jobData[] = $this->_jobHelper->createJob($data)->generateCategoryJob()->save();
            }


            try {

                foreach ($jobData as $job){

                    $this->_summitJob($job->getJob());
                }

                return $resultRedirect->setPath('*/*/');


            } catch (\Magento\Framework\Exception\LocalizedException $e) {

                $this->messageManager->addError($e->getMessage());

                $this->_logger->error('error'.__FILE__.' '.__LINE__,array($e));


            } catch (\RuntimeException $e) {

                $this->messageManager->addError($e->getMessage());

                $this->_logger->error('error'.__FILE__.' '.__LINE__,array($e));

            } catch (\Exception $e) {

                $this->messageManager->addException($e, __('Something went wrong while saving the job.'.$e->getMessage()));

                $this->_logger->error('error'.__FILE__.' '.__LINE__,array($e));
            }

            return $resultRedirect->setPath('*/*/edit', ['job_id' => $this->getRequest()->getParam('job_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    protected function _saveStoreConfigData($data)
    {
        $count = 0;

        foreach ($this->_storeConfigKeys as $key ) {

            if (isset($data[$key])) {
                $count ++;
            }
        }

        if($count==4)
        {

            try {

                $this->_setupInterface->saveStoreSetup(
                    $data['magento_destination_store'],
                    $data['magento_source_store'],
                    $data['straker_source_language'],
                    $data['straker_target_language']
                );

            } catch (\Magento\Framework\Exception\LocalizedException $e) {

                $this->messageManager->addError($e->getMessage());

                $this->_logger->error('error'.__FILE__.' '.__LINE__,array($e));


            }

        }
    }

    /**
     * @param $job_object
     * @return bool
     */
    protected function _summitJob($job_object){


        $store = $job_object->getData('source_store_id');

        $defaultTitle = $job_object->getData('sl').'_'.$job_object->getData('tl').'_'.$store.'_'.$job_object->getData('job_id');

        $job_object->setData('title',$defaultTitle);

        $this->_jobRequest['title']       = $job_object->getTitle();
        $this->_jobRequest['sl']          = $job_object->getData('sl');
        $this->_jobRequest['tl']          = $job_object->getTl();
        $this->_jobRequest['source_file'] = $job_object->getData('source_file');
        $this->_jobRequest['token']       = $job_object->getId();

        $response = $this->_api->callTranslate($this->_jobRequest);

        try {

            $job_object->addData(['job_key'=>$response->job_key]);

            $job_object->setData('sl', $this->_api->getLanguageName( $job_object->getData('sl')));

            $job_object->setData('tl', $this->_api->getLanguageName( $job_object->getData('tl')));


            $job_object->save();

            $this->messageManager->addSuccess(__('Your job was submitted successfully.'));


        }catch (\Exception $e){

            $this->_logger->error('error '.__FILE__.' '.__LINE__.''.$response->message,array($response));

            $this->messageManager->addError(__('Something went wrong while submitting your job to Straker Translations.'));
        }

    }

}
