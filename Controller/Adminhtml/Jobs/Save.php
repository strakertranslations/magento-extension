<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Jobs;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\TestFramework\ErrorLog\Logger;
use Magento\Eav\Model\AttributeRepository;
use Magento\Store\Model\StoreManagerInterface;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Helper\XmlHelper;
use Straker\EasyTranslationPlatform\Model\JobType;

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
        JobType $jobType,
        \Straker\EasyTranslationPlatform\Model\ResourceModel\Job\CollectionFactory $jobCollectionFactory
    ) {
        $this->_configHelper = $configHelper;
        $this->_jsHelper = $jsHelper;
        $this->_jobCollectionFactory = $jobCollectionFactory;
        $this->_attributeRepository = $attributeRepository;
        $this->_xmlHelper = $xmlHelper;
        $this->_storeManager = $storeManager;
        $this->_jobTypeModel = $jobType;
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
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {

            $productData = $this->getProductData($data['products'],$this->_storeManager->getStore()->getId());

            try {


                $model = $this->_objectManager->create('Straker\EasyTranslationPlatform\Model\Job');

                $model->setData(
                    [
                        'job_type_id'=>$this->getJobTypeId('product'),
                        'job_status_id'=>$this->getJobStatusId('queued'),
                        'source_store_id'=>$this->_configHelper->getStoreInfo($data['destination_store'])['straker/general/source_store'],
                        'target_store_id'=>$data['destination_store'],
                        'source_language'=>$this->_configHelper->getStoreInfo($data['destination_store'])['straker/general/source_language'],
                        'target_language'=>$this->_configHelper->getStoreInfo($data['destination_store'])['straker/general/destination_language']
                    ]
                );

                $model->save();

                $this->generateProductXML($productData, $model->getId(), $model->getData('job_type_id'),$model->getData('source_store_id'),$model->getData('target_store_id'));

                $model->addData(['source_file'=>$this->_xmlHelper->getXMLFileName()]);

                $model->save();

                $this->saveProducts($productData,$model->getId());

                $this->messageManager->addSuccess(__('You saved this job.'));

                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {

                    return $resultRedirect->setPath('*/*/edit', ['job_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');

            } catch (\Magento\Framework\Exception\LocalizedException $e) {

                $this->messageManager->addError($e->getMessage());

            } catch (\RuntimeException $e) {

                $this->messageManager->addError($e->getMessage());

            } catch (\Exception $e) {

                $this->messageManager->addException($e, __('Something went wrong while saving the job.'.$e->getMessage()));
            }

            $this->_getSession()->setFormData($data);

            return $resultRedirect->setPath('*/*/edit', ['job_id' => $this->getRequest()->getParam('job_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param $product_data
     * @param $job_id
     * @return bool
     */
    protected function saveProducts($product_data, $job_id)
    {

        try {

            foreach ($product_data as $data) {

                foreach ($data['attributes'] as $attribute) {

                    $model = $this->_objectManager->create('Straker\EasyTranslationPlatform\Model\AttributeTranslation');

                    $model->setData(
                        [
                            'job_id' => $job_id,
                            'entity_id' => $data['product_id'],
                            'attribute_id' => $attribute['attribute_id'],
                            'original_value' => (is_array($attribute['value']) ? $attribute['label'] : $attribute['value']),
                            'has_option' => is_array($attribute['value']) ? (bool)1 : (bool)0,
                            'is_label' => is_array($attribute['value']) ? (bool)1 : (bool)0
                        ]
                    )->save();

                    if ($model->getData('has_option')) {

                        $this->saveOptionValues($attribute['value'], $model->getId());
                    }

                }
            }

        } catch (Exception $e) {

            $this->messageManager->addException($e, __('Something went wrong while saving the job.'));
        }

    }

    /**
     * @param $product_ids
     * @param $store_id
     * @return array
     */
    protected function getProductData($product_ids,$store_id)
    {
        $product_ids = explode('&',$product_ids);

        $productCollection = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');

        $products = $productCollection->create()
            ->addAttributeToSelect('*')
            ->addIdFilter($product_ids)
            ->load();

        $attributes = array_merge($this->_configHelper->getDefaultAttributes(),$this->_configHelper->getCustomAttributes());

        $productData = [];

        foreach ($products as $product){

            $attributeData = [];

            if($product->getData('type_id') =='configurable'){

                $attributeData = $this->getConfigurableAttributes($product);
            }

            foreach ($attributes as $attribute_id){

                if(in_array($this->_attributeRepository->get(\Magento\Catalog\Model\Product::ENTITY,$attribute_id)->getFrontendInput(),$this->_multiSelectInputTypes)){

                    if($this->findMultiOptionAttributes($attribute_id,$product,$store_id)){

                        array_push($attributeData,$this->findMultiOptionAttributes($attribute_id,$product,$store_id));
                    }

                }else{

                    if($product->getResource()->getAttributeRawValue($product->getId(), $attribute_id,$store_id)){

                       array_push($attributeData,['attribute_id'=>$attribute_id,'label'=>$this->_attributeRepository->get('catalog_product',$attribute_id)->getFrontendLabel(),'value'=>$product->getResource()->getAttributeRawValue($product->getId(), $attribute_id,$store_id)]);
                    }

                }
            }

            usort($attributeData, function($a, $b) {

                return $a['attribute_id'] - $b['attribute_id'];
            });

            $productData[] = ['product_id'=>$product->getId(), 'product_name'=>$product->getName(),'product_url'=>$product->setStoreId($store_id)->getUrlInStore(),'attributes'=>$attributeData];

        }

        return $productData;

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

        $this->appendProductAttributes($productData, $job_id, $jobtype_id, $source_store_id, $target_store_id, $this->_xmlHelper);

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

    protected function getConfigurableAttributes($product)
    {

        //$ids = $product->getTypeInstance()->getUsedProducts($product);

        $attributes = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);

        $configAttributeData = [];

        foreach ($attributes as $attribute)
        {
            $value_data = [];

            foreach ($attribute['values'] as $value){

                $value_data[] = ['option_id'=>$value['value_index'],'value'=>$value['default_label']];
            }

            $configAttributeData[] = [
                'attribute_id'=>$attribute['attribute_id'],
                'label'=>$attribute['label'],
                'value'=>$value_data
            ];

        }

        return $configAttributeData;

    }

    protected function appendProductAttributes($productData, $job_id, $jobtype_id, $source_store_id, $target_store_id, $xmlHelper)
    {

        if($productData)
        {
            try
            {
                foreach ($productData as $data){

                    foreach ($data['attributes'] as $attribute){

                        $job_name = $job_id.'_'.$jobtype_id.'_'.$target_store_id.'_'.$data['product_id'].'_'.$attribute['attribute_id'];

                        $this->appendProductAttributeLabel($data,$attribute,$job_name,$source_store_id,$xmlHelper);

                        if(is_array($attribute['value']))
                        {
                            foreach ($attribute['value'] as $value)
                            {

                                $xmlHelper->appendDataToRoot([
                                    'name' => $job_name.'_'.$value['option_id'],
                                    'content_context' => 'product_attribute_value',
                                    'content_context_url' => $data['product_url'],
                                    'source_store_id'=>$source_store_id,
                                    'product_id' => $data['product_id'],
                                    'attribute_id'=>$attribute['attribute_id'],
                                    'attribute_label'=>$attribute['label'],
                                    'option_id'=>$value['option_id'],
                                    'value' => $value['value'],
                                    'translate'=> (in_array($value['value'], $this->_translatedAttributeOptions) || is_numeric($value['value'])  ) ? 'false' : 'true'
                                ]);

                                array_push($this->_translatedAttributeOptions,$value['value']);
                            }

                        }else{

                            $xmlHelper->appendDataToRoot([
                                'name' => $job_name,
                                'content_context' => 'product_attribute_value',
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

            }catch (\Exception $e){

                $this->messageManager->addException($e, __('Something went wrong while saving the job.'));

            }
        }

        return false;

    }

    protected function appendProductAttributeLabel($productData, $attribute, $jobName, $source_store_id, $xmlHelper)
    {

        if($productData){

            try{

                $xmlHelper->appendDataToRoot([
                    'name' => $jobName,
                    'content_context' => 'product_attribute_label',
                    'content_context_url' => $productData['product_url'],
                    'source_store_id'=> $source_store_id,
                    'product_id' => $productData['product_id'],
                    'attribute_id'=>$attribute['attribute_id'],
                    'attribute_label'=>$attribute['label'],
                    'value'=>$attribute['label'],
                    'translate' => in_array($attribute['label'],$this->_translatedAttributeLabels) ? 'false' : 'true'
                ]);

                array_push($this->_translatedAttributeLabels,$attribute['label']);

                return true;

            }catch (\Exception $e){

                $this->messageManager->addException($e, __('Something went wrong while saving the job.'));

            }
        }

        return false;
    }
}
