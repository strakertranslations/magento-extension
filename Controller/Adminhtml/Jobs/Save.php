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

    protected $_configHelper;

    /**
     * @var \Straker\EasyTranslationPlatform\Model\ResourceModel\Job\CollectionFactory
     */
    protected $_jobCollectionFactory;

    protected $_multiSelectInputTypes = array(
        'select', 'multiselect'
    );

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

            /** @var \Straker\EasyTranslationPlatform\Model\Job $model */
            $model = $this->_objectManager->create('Straker\EasyTranslationPlatform\Model\Job');

//            $id = $this->getRequest()->getParam('job_id');
//            if ($id) {
//                $model->load($id);
//            }


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

            $productData = $this->getProductData($data['products'],$this->_storeManager->getStore()->getId());

            $this->generateProductXML($productData, $model->getId().'_'.$model->getData('job_type_id'));

            try {

                $model->save();

                $productData = $this->getProductData($data['products']);

                $this->generateProductXML($productData,$model->getId());

                $this->saveProducts($model, $data);

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

    public function saveProducts($model, $post)
    {
        // Attach the attachments to job
        if (isset($post['products'])) {
            $productIds = $this->_jsHelper->decodeGridSerializedInput($post['products']);
            try {
                $oldProducts = (array) $model->getProducts($model);
                $newProducts = (array) $productIds;

                $this->_resources = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
                $connection = $this->_resources->getConnection();

                $table = $this->_resources->getTableName(\Straker\EasyTranslationPlatform\Model\ResourceModel\Job::TBL_ATT_PRODUCT);
                $insert = array_diff($newProducts, $oldProducts);
                $delete = array_diff($oldProducts, $newProducts);

                if ($delete) {
                    $where = ['job_id = ?' => (int)$model->getId(), 'product_id IN (?)' => $delete];
                    $connection->delete($table, $where);
                }

                if ($insert) {
                    $data = [];
                    foreach ($insert as $product_id) {
                        $data[] = ['job_id' => (int)$model->getId(), 'product_id' => (int)$product_id];
                    }
                    $connection->insertMultiple($table, $data);
                }
            } catch (Exception $e) {

                $this->messageManager->addException($e, __('Something went wrong while saving the job.'));
            }
        }

    }

    /**
     * @param $productIds
     * @return array
     * Todo: Add Store Id & Buffers
     */
    protected function getProductData($productIds,$store_id)
    {
        $productIds = explode('&',$productIds);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');

        $products = $productCollection->create()
            ->addAttributeToSelect('*')
            ->addIdFilter($productIds)
            ->load();

        $attributes = array_merge($this->_configHelper->getDefaultAttributes(),$custom_attributes = $this->_configHelper->getCustomAttributes());

        $productData = [];

        $attributeData = [];

        foreach ($products as $product){

            foreach ($attributes as $attribute_id){

                if(in_array($this->_attributeRepository->get(\Magento\Catalog\Model\Product::ENTITY,$attribute_id)->getFrontendInput(),$this->_multiSelectInputTypes)){

                    if($this->findMultiOptionAttributes($attribute_id,$product,$store_id)){

                        array_push($attributeData,$this->findMultiOptionAttributes($attribute_id,$product,$store_id));
                    }

                }else{

                    if($product->getResource()->getAttributeRawValue($product->getId(), $attribute_id,$store_id)){

                       array_push($attributeData,['attribute_id'=>$attribute_id,'label'=>$this->_attributeRepository->get('catalog_product',$attribute_id)->getFrontendLabel(),'value'=>$product->getResource()->getAttributeRawValue($product->getId(), $attribute_id,0)]);
                    }

                }
            }

            $productData[] = ['product_id'=>$product->getId(), 'product_name'=>$product->getName(),'product_url'=>$product->setStoreId(1)->getUrlInStore(),'attributes'=>$attributeData];

        }

        return $productData;

    }

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

    protected function generateProductXML($productData, $job_id)
    {

        $this->_xmlHelper->create('_'.$job_id.'_'.time());

        foreach ($productData as $data){

            foreach ($data['attributes'] as $attribute){

                if(is_array($attribute['value']))
                {
                    foreach ($attribute['value'] as $value)
                    {
                        $this->_xmlHelper->appendDataToRoot([
                            'name' => $job_id.'_'.$data['product_id'].'_'.$attribute['attribute_id'].'_'.$value['option_id'],
                            'content_context' => 'Product',
                            'content_context_url' => $data['product_url'],
                            'product_id' => $data['product_id'],
                            'attribute_id'=>$attribute['attribute_id'],
                            'attribute_label'=>$attribute['label'],
                            'option_id'=>$value['option_id'],
                            'value' => $value['value']
                        ]);
                    }

                }else{

                    $this->_xmlHelper->appendDataToRoot([
                        'name' => $job_id.'_'.$data['product_id'].'_'.$attribute['attribute_id'],
                        'content_context' => 'Product',
                        'content_context_url' => $data['product_url'],
                        'product_id' => $data['product_id'],
                        'attribute_id'=>$attribute['attribute_id'],
                        'attribute_label'=>$attribute['label'],
                        'value' => $attribute['value']
                    ]);

                }


            }

        }

        $this->_xmlHelper->saveXmlFile();
        var_dump($productData);
        exit;
    }

    protected function getJobTypeId($jobType){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $productCollection = $objectManager->create('Straker\EasyTranslationPlatform\Model\ResourceModel\JobType\CollectionFactory');//insert your custom resource model

        $collection = $productCollection->create()
            ->addFieldToFilter('type_name',array('eq'=>$jobType))
            ->getFirstItem();

        return $collection->getData('type_id');
    }

    protected function getJobStatusId($jobStatus){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $productCollection = $objectManager->create('Straker\EasyTranslationPlatform\Model\ResourceModel\JobStatus\CollectionFactory');//insert your custom resource model

        $collection = $productCollection->create()
            ->addFieldToFilter('status_name',array('eq'=>$jobStatus))
            ->getFirstItem();

        return $collection->getData('status_id');
    }
}
