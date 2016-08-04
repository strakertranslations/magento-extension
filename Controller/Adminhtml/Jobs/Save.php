<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Jobs;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Eav\Model\AttributeRepository;
use Magento\Store\Model\StoreManagerInterface;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Helper\XmlHelper;
use Straker\EasyTranslationPlatform\Model\JobType;
use Straker\EasyTranslationPlatform\Model\JobRepository;
use Straker\EasyTranslationPlatform\Helper\JobHelper;
use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;
use Straker\EasyTranslationPlatform\Logger\Logger;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $_jsHelper;

    /**
     * @var \Straker\EasyTranslationPlatform\Helper\ConfigHelper
     */
    protected $_configHelper;

    /**
     * @var \Straker\EasyTranslationPlatform\Model\ResourceModel\Job\CollectionFactory
     */
    protected $_jobCollectionFactory;


    protected $_multiSelectInputTypes = array(
        'select', 'multiselect'
    );

    protected  $_translatedAttributeLabels = [];

    protected  $_translatedAttributeOptions = [];

    protected $_jobRequest;


    /**
     * \Magento\Backend\Helper\Js $jsHelper
     * @param Action\Context $context
     */
    public function __construct(
        Context $context,
        ConfigHelper $configHelper,
        \Magento\Backend\Helper\Js $jsHelper,
        AttributeRepository $attributeRepository,
        StoreManagerInterface $storeManager,
        XmlHelper $xmlHelper,
        JobRepository $jobRepository,
        JobType $jobType,
        JobHelper $jobHelper,
        StrakerAPIInterface $API,
        Logger $logger,
        \Straker\EasyTranslationPlatform\Model\ResourceModel\Job\CollectionFactory $jobCollectionFactory
    ) {
        $this->_configHelper = $configHelper;
        $this->_jsHelper = $jsHelper;
        $this->_jobCollectionFactory = $jobCollectionFactory;
        $this->_attributeRepository = $attributeRepository;
        $this->_xmlHelper = $xmlHelper;
        $this->_storeManager = $storeManager;
        $this->_jobTypeModel = $jobType;
        $this->jobRepository = $jobRepository;
        $this->_api = $API;
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

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {

            $job = $this->_jobHelper->createJob($data)->generateProductJob()->save();

            try {

                $this->_summitJob($job->getJob());

                if ($this->getRequest()->getParam('back')) {

                    return $resultRedirect->setPath('*/*/edit', ['job_id' => $job->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');

            } catch (\Magento\Framework\Exception\LocalizedException $e) {

                $this->messageManager->addError($e->getMessage());

                $this->_logger->error('error'.__FILE__.' '.__LINE__,array($e));


            } catch (\RuntimeException $e) {

                $this->messageManager->addError($e->getMessage());

                $this->_logger->error('error'.__FILE__.' '.__LINE__,array($e));

            } catch (\Exception $e) {

//                var_dump($e->getMessage());

                $this->messageManager->addException($e, __('Something went wrong while saving the job.'.$e->getMessage()));

                $this->_logger->error('error'.__FILE__.' '.__LINE__,array($e));
            }

            return $resultRedirect->setPath('*/*/edit', ['job_id' => $this->getRequest()->getParam('job_id')]);
        }
        return $resultRedirect->setPath('*/*/');
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

//        var_dump($this->_jobRequest);exit();
        $response = $this->_api->callTranslate($this->_jobRequest);

        try {

            $job_object->addData(['job_key'=>$response->job_key]);

            $job_object->save();

            $this->messageManager->addSuccess(__('Your job was submitted successfully.'));


        }catch (\Exception $e){

            $this->_logger->error('error '.__FILE__.' '.__LINE__.''.$response->message,array($response));

            $this->messageManager->addError(__('Something went wrong while submitting your job to Straker Translations.'));
        }

    }

}
