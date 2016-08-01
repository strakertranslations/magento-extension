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

                var_dump($e->getMessage());

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

        $defaultTitle = $job_object->getData('source_language').'_'.$job_object->getData('target_language').'_'.$store.'_'.$job_object->getData('job_id');

        $job_object->setData('title',$defaultTitle);

        $this->_jobRequest['title']       = $job_object->getTitle();
        $this->_jobRequest['sl']          = $job_object->getSourceLanguage();
        $this->_jobRequest['tl']          = $job_object->getTargetLanguage();
        $this->_jobRequest['source_file'] = $job_object->getData('source_file');
        $this->_jobRequest['token']       = $job_object->getId();


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


    /**
     * @param $attribute_id
     * @param $product
     * @param $store_id
     * @return bool
     */
    protected function findMultiOptionAttributes($attribute_id, $product,$store_id)
    {

        $attribute = $this->_attributeRepository->get(\Magento\Catalog\Model\Product::ENTITY,$attribute_id);

        $options = $product->getResource()->getAttributeRawValue($product->getId(), $attribute, $store_id);

        if($options){

            $values['attribute_id'] = $attribute_id;

            $values['label'] = $attribute->getFrontendLabel();

            $options = explode(',',$options);

            foreach ($options as $option_id)
            {
                $values['value'][] = ['option_id'=>$option_id,'value'=>$attribute->getSource()->getOptionText($option_id)];
            }

            return $values;

        }

        return false;

    }

    /**
     * @param $productData
     * @param $job_id
     * @param $jobtype_id
     * @param $source_store_id
     * @param $destination_store_id
     * @return bool
     */
    protected function generateProductXML($productData, $job_id, $jobtype_id, $source_store_id, $target_store_id)
    {

        $this->_xmlHelper->create('_'.$job_id.'_'.time());

        foreach ($productData as $data){

            foreach ($data['attributes'] as $attribute){

                if(is_array($attribute['value']))
                {
                    foreach ($attribute['value'] as $value)
                    {

                        $this->_xmlHelper->appendDataToRoot([
                            'name' => $job_id.'_'.$jobtype_id.'_'.$target_store_id.'_'.$data['product_id'].'_'.$attribute['attribute_id'].'_'.$value['option_id'],
                            'content_context' => 'Product',
                            'content_context_url' => $data['product_url'],
                            'source_store_id'=>$source_store_id,
                            'product_id' => $data['product_id'],
                            'attribute_id'=>$attribute['attribute_id'],
                            'attribute_label'=>$attribute['label'],
                            'option_id'=>$value['option_id'],
                            'value' => $value['value'],
                            'translate'=> isset($attributeList[$attribute['attribute_id']][$value['option_id']]) ? 'false' : 'true'
                        ]);

                        $attributeList[$attribute['attribute_id']][$value['option_id']] = 'inList';
                    }

                }else{

                    $this->_xmlHelper->appendDataToRoot([
                        'name' => $job_id.'_'.$jobtype_id.'_'.$target_store_id.'_'.$data['product_id'].'_'.$attribute['attribute_id'],
                        'content_context' => 'Product',
                        'content_context_url' => $data['product_url'],
                        'source_store_id'=> $source_store_id,
                        'product_id' => $data['product_id'],
                        'attribute_id'=>$attribute['attribute_id'],
                        'attribute_label'=>$attribute['label'],
                        'value' => $attribute['value']
                    ]);

                }


            }

        }

        $this->_xmlHelper->saveXmlFile();

        return true;
    }

    /**
     * @param $jobType
     * @return mixed
     */
    protected function getJobTypeId($jobType){


        $productCollection = $this->_objectManager->create('Straker\EasyTranslationPlatform\Model\ResourceModel\JobType\CollectionFactory');//insert your custom resource model

        $collection = $productCollection->create()
            ->addFieldToFilter('type_name',array('eq'=>$jobType))
            ->getFirstItem();

        return $collection->getData('type_id');
    }

    /**
     * @param $jobStatus
     * @return mixed
     */
    protected function getJobStatusId($jobStatus){

        $productCollection = $this->_objectManager->create('Straker\EasyTranslationPlatform\Model\ResourceModel\JobStatus\CollectionFactory');//insert your custom resource model

        $collection = $productCollection->create()
            ->addFieldToFilter('status_name',array('eq'=>$jobStatus))
            ->getFirstItem();

        return $collection->getData('status_id');
    }

    /**
     * @param $option_values
     * @param $attribute_translation_id
     */
    protected function saveOptionValues($option_values,$attribute_translation_id)
    {

        try{

            foreach ($option_values as $option){

                $model = $this->_objectManager->create('Straker\EasyTranslationPlatform\Model\AttributeOptionTranslation');

                $model->setData(
                    [
                        'attribute_translation_id'=>$attribute_translation_id,
                        'option_id'=>$option['option_id'],
                        'original_value'=>$option['value']
                    ]
                )->save();

            }

        }catch (Exception $e) {


            $this->messageManager->addException($e, __('Something went wrong while saving the job.'));
        }

    }
}
