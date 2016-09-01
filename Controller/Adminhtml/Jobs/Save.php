<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Jobs;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

use Magento\Backend\Helper\Js;
use Magento\Eav\Model\AttributeRepository;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Xml\Parser;

use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Helper\XmlHelper;
use Straker\EasyTranslationPlatform\Model\JobType;
use Straker\EasyTranslationPlatform\Model\JobRepository;
use Straker\EasyTranslationPlatform\Helper\JobHelper;
use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;
use Straker\EasyTranslationPlatform\Api\Data\SetupInterface;
use Straker\EasyTranslationPlatform\Logger\Logger;
use Straker\EasyTranslationPlatform\Model\ResourceModel\Job\CollectionFactory;

class Save extends Action
{

    /**
     * @var Js
     */
    protected $_jsHelper;

    protected $_setupInterface;

    /**
     * @var \Straker\EasyTranslationPlatform\Helper\ConfigHelper
     */
    protected $_configHelper;

    /**
     * @var CollectionFactory
     */
    protected $_jobCollectionFactory;

    protected $_multiSelectInputTypes = array(
        'select', 'multiselect'
    );

    protected $_storeConfigKeys = array(
        'magento_destination_store','straker_target_language','magento_source_store','straker_source_language'
    );


    protected $_jobRequest;
    protected $_attributeRepository;
    protected $_xmlHelper;
    protected $_xmlParser;
    protected $_storeManager;
    protected $_jobTypeModel;
    protected $jobRepository;
    protected $_api;
    protected $_jobHelper;
    protected $_logger;

    /**
     * Save constructor.
     * @param Context $context
     * @param ConfigHelper $configHelper
     * @param Js $jsHelper
     * @param AttributeRepository $attributeRepository
     * @param StoreManagerInterface $storeManager
     * @param XmlHelper $xmlHelper
     * @param JobRepository $jobRepository
     * @param JobType $jobType
     * @param JobHelper $jobHelper
     * @param StrakerAPIInterface $API
     * @param SetupInterface $setup
     * @param Logger $logger
     * @param CollectionFactory $jobCollectionFactory
     */
    public function __construct(
        Context $context,
        ConfigHelper $configHelper,
        Js $jsHelper,
        AttributeRepository $attributeRepository,
        StoreManagerInterface $storeManager,
        XmlHelper $xmlHelper,
        Parser $xmlParser,
        JobRepository $jobRepository,
        JobType $jobType,
        JobHelper $jobHelper,
        StrakerAPIInterface $API,
        SetupInterface $setup,
        Logger $logger,
        CollectionFactory $jobCollectionFactory
    ) {

        $this->_api = $API;
        $this->_setupInterface = $setup;
        $this->_jobHelper = $jobHelper;
        $this->_logger = $logger;
        $this->_xmlHelper = $xmlHelper;
        $this->_xmlParser = $xmlParser;

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

            if(isset($data['blocks']) && strlen($data['blocks'])>0)
            {
                $jobData[] = $this->_jobHelper->createJob($data)->generateBlockJob();
            }

            if(isset($data['products']) && strlen($data['products'])>0)
            {
                $jobData[] = $this->_jobHelper->createJob($data)->generateProductJob();
            }

            if(strlen($data['categories'])>0)
            {
                $jobData[] = $this->_jobHelper->createJob($data)->generateCategoryJob();
            }

            if(isset($data['pages']) && strlen($data['pages'])>0)
            {
                $jobData[] = $this->_jobHelper->createJob($data)->generatePageJob();

            }

            try {

                $this->_summitJob($jobData);

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

        $sourcefile = $this->mergeJobData($job_object);

        $strakerJobData = current($job_object);

        $defaultTitle = $strakerJobData->getData('sl').'_'.$strakerJobData->getData('tl').'_'.$strakerJobData->getData('source_store_id').'_'.$strakerJobData->getData('job_id');

        $strakerJobData->setData('title',$defaultTitle);

        $this->_jobRequest['title']       = $strakerJobData->getTitle();
        $this->_jobRequest['sl']          = $strakerJobData->getData('sl');
        $this->_jobRequest['tl']          = $strakerJobData->getTl();
        $this->_jobRequest['source_file'] = $sourcefile;
        $this->_jobRequest['token']       = $strakerJobData->getId();

        $response = $this->_api->callTranslate($this->_jobRequest);

        try {

            foreach ($job_object as $job)
            {
                $job->addData(['job_key'=>$response->job_key]);

                $job->setData('sl', $this->_api->getLanguageName( $job->getData('sl')));

                $job->setData('tl', $this->_api->getLanguageName( $job->getData('tl')));

                $job->setData('source_file',$sourcefile);

                $job->save();
            }

            $this->messageManager->addSuccess(__('Your job was submitted successfully.'));


        }catch (\Exception $e){

            $this->_logger->error('error '.__FILE__.' '.__LINE__.''.$response->message,array($response));

            $this->messageManager->addError(__('Something went wrong while submitting your job to Straker Translations.'));
        }

    }

    protected function mergeJobData($job_object)
    {

        $jobMergeData = [];

        $id = '';

        foreach ($job_object as $key => $data)
        {
            $jobMergeData[$key]['id'] =  $data->getData('job_id');
            $jobMergeData[$key]['file_name'] =  $data->getData('source_file');
            $id.=  $data->getData('job_id').'&';
        }

        $this->_xmlHelper->create('_'.rtrim($id,"&").'_'.time());

        foreach ($jobMergeData as $file)
        {
            $fileData = $this->_xmlParser->load($file['file_name'])->xmlToArray();

            foreach ($fileData['root']['data'] as $data)
            {
                $mergeData = array_merge_recursive($data['_value'],$data['_attribute']);

                $this->_xmlHelper->appendDataToRoot($mergeData);
            }
        }

        $this->_xmlHelper->saveXmlFile();

        return $this->_xmlHelper->getXmlFileName();
    }

}
