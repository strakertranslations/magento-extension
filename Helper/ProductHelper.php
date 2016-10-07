<?php

namespace Straker\EasyTranslationPlatform\Helper;

use Exception;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\AttributeRepository;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Data\Collection;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Store\Model\StoreManagerInterface;

use Straker\EasyTranslationPlatform\Model\AttributeOptionTranslation;
use Straker\EasyTranslationPlatform\Model\AttributeTranslationFactory;
use Straker\EasyTranslationPlatform\Model\AttributeOptionTranslationFactory;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Helper\AttributeHelper;
use Straker\EasyTranslationPlatform\Helper\XmlHelper;
use Straker\EasyTranslationPlatform\Logger\Logger;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

class ProductHelper extends AbstractHelper
{

    protected $_productFactory;
    protected $_collectionFactory;
    protected $_attributeTranslationModel;
    protected $_attributeOptionTranslationModel;
    protected $_storeManager;

    protected $_entityTypeId;
    protected $_productData;
    protected $_storeId;

    protected $_translatedAttributeOptions = [];

    protected $_translatableBackendType = array (
        'varchar', 'text','int'
    );

    protected $_translatableFrontendInputType = array(
        'select', 'text','multiline', 'textarea', 'multiselect'
    );

    protected $_translatableFrontendLabel = array(
        'name', 'description', 'meta title', 'meta keywords', 'meta description', 'short description', 'color','size'
    );

    protected $_multiSelectInputTypes = array(
        'select', 'multiselect'
    );
    protected $_attributeCollectionFactory;
    protected $_productCollectionFactory;
    protected $_attributeTranslationFactory;
    protected $_attributeOptionTranslationFactory;
    protected $_attributeRepository;
    protected $_configHelper;
    protected $_attributeHelper;
    protected $_xmlHelper;


    /**
     * ProductHelper constructor.
     * @param Context $context
     * @param ProductFactory $productFactory
     * @param AttributeRepository $attributeRepository
     * @param AttributeCollection $attributeCollectionFactory
     * @param ProductCollection $productCollectionFactory
     * @param AttributeTranslationFactory $attributeTranslationFactory
     * @param AttributeOptionTranslationFactory $attributeOptionTranslationFactory
     * @param Config $eavConfig
     * @param \Straker\EasyTranslationPlatform\Helper\ConfigHelper $configHelper
     * @param \Straker\EasyTranslationPlatform\Helper\AttributeHelper $attributeHelper
     * @param \Straker\EasyTranslationPlatform\Helper\XmlHelper $xmlHelper
     * @param Logger $logger
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        ProductFactory $productFactory,
        AttributeRepository $attributeRepository,
        AttributeCollection $attributeCollectionFactory,
        ProductCollection $productCollectionFactory,
        AttributeTranslationFactory $attributeTranslationFactory,
        AttributeOptionTranslationFactory $attributeOptionTranslationFactory,
        Config $eavConfig,
        ConfigHelper $configHelper,
        AttributeHelper $attributeHelper,
        XmlHelper $xmlHelper,
        Logger $logger,
        StoreManagerInterface $storeManager
    ) {
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_attributeTranslationFactory = $attributeTranslationFactory;
        $this->_attributeOptionTranslationFactory = $attributeOptionTranslationFactory;
        $this->_attributeRepository = $attributeRepository;
        $this->_configHelper = $configHelper;
        $this->_attributeHelper = $attributeHelper;
        $this->_xmlHelper = $xmlHelper;
        $this->_logger = $logger;
        $this->_entityTypeId =  $eavConfig->getEntityType(ProductAttributeInterface::ENTITY_TYPE_CODE)->getEntityTypeId();
        $this->_storeManager = $storeManager;

        parent::__construct($context);
    }


    /**
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    public function getDefaultAttributes()
    {
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $collection */
        $collection = $this->getAttributes()
            ->addFieldToFilter( 'frontend_label',  array( 'in' => $this->_translatableFrontendLabel ));
        return $collection;
    }

    public function getCustomAttributes()
    {
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $collection */
        $collection = $this->getAttributes()
                        ->addFieldToFilter( 'is_user_defined', array( 'eq' => 1 ))
                        ->addFieldToFilter('frontend_label',array('nin'=>$this->_translatableFrontendLabel));
        return $collection;
    }

    public function getAttributes(){
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $collection */
        $collection = $this->_attributeCollectionFactory->create();
        $collection->setEntityTypeFilter( $this->_entityTypeId )
            ->setFrontendInputTypeFilter( array( 'in' => $this->_translatableFrontendInputType ) )
            ->addFieldToFilter( 'backend_type',   array( 'in' => $this->_translatableBackendType ) )
            ->setOrder( 'attribute_id', 'asc' );
        return $collection;
    }

    /**
     * @param $product_ids
     * @param $target_store_id
     * @param bool $includeChildren
     * @return $this Todo: Add store id to filter products by store
     * Todo: Add store id to filter products by store
     * @internal param $store_id
     */
    public function getProducts(
        $product_ids,
        $source_store_id,
        $includeChildren = true
    )
    {
        if(strpos($product_ids,'&')!== false)
        {
            $product_ids = explode('&',$product_ids);
        }

        $this->_storeManager->setCurrentStore($source_store_id);

        if( $includeChildren ){
            $childrenIds = $this->_getChildrenProducts( $product_ids );
            if(is_array($product_ids)){
                $product_ids = array_merge( $product_ids, $childrenIds );
            }else{
                $product_ids = array_merge( [$product_ids], $childrenIds );
            }
        }

        $products = $this->_productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addIdFilter($product_ids)
            ->load();

        $this->_storeId = $source_store_id;

        $this->_productData = $products;

        return $this;
    }

    /**
     * @return $this
     */
    public function getSelectedProductAttributes()
    {
        $attributes = array_merge($this->_configHelper->getDefaultAttributes(),$this->_configHelper->getCustomAttributes());

        $productAttributeData = [];

        foreach ($this->_productData as $product){

            $attributeData = [];

            if($product->getData('type_id') =='configurable'){

                $attributeData = $this->_attributeHelper->getConfigurableAttributes($product);
            }

            foreach ($attributes as $attribute_id){

                if(in_array($this->_attributeRepository->get(\Magento\Catalog\Model\Product::ENTITY,$attribute_id)->getFrontendInput(),$this->_multiSelectInputTypes)){

                    if($this->_attributeHelper->findMultiOptionAttributes($attribute_id,$product,$this->_storeId)){

                        array_push($attributeData,$this->_attributeHelper->findMultiOptionAttributes($attribute_id,$product,$this->_storeId));
                    }

                }else{

                    if($product->getResource()->getAttributeRawValue($product->getId(),$attribute_id,$this->_storeId)){

                        array_push($attributeData,['attribute_id'=>$attribute_id,'label'=>$product->getResource()->getAttribute($attribute_id)->getStoreLabel($this->_storeId),'value'=>$product->getResource()->getAttributeRawValue($product->getId(), $attribute_id,$this->_storeId)]);
                    }

                }
            }

            //Sort Attribute Data by Id Asc
            usort($attributeData, function($a, $b) {

                return $a['attribute_id'] - $b['attribute_id'];
            });

            $productAttributeData[] = [
                'product_id'=>$product->getId(),
                'product_name'=>$product->getName(),
                'product_url'=>$product->setStoreId($this->_storeId)->getUrlInStore(),
                'attributes'=>$attributeData
            ];

        }

        $this->_productData = $productAttributeData;

        return $this;
    }

    /**
     * @param $jobModel
     * @return string
     */
    public function generateProductXML($jobModel)
    {

        $this->_xmlHelper->create('_'.$jobModel->getId().'_'.time());

        $this->appendProductAttributes(
            $this->_productData,
            $jobModel->getId(),
            $jobModel->getData('job_type_id'),
            $jobModel->getData('source_store_id'),
            $jobModel->getData('target_store_id'),
            $this->_xmlHelper
        );

        $this->_xmlHelper->saveXmlFile();

        return $this->_xmlHelper->getXmlFileName();
    }

    /**
     * @param $productData
     * @param $job_id
     * @param $jobType_id
     * @param $source_store_id
     * @param $target_store_id
     * @param $xmlHelper
     * @return bool
     */
    protected function appendProductAttributes(
        $productData,
        $job_id,
        $jobType_id,
        $source_store_id,
        $target_store_id,
        $xmlHelper
    )
    {

        if($productData)
        {
            try
            {
                foreach ($productData as $data){

                    foreach ($data['attributes'] as $attribute){

                        $job_name = $job_id.'_'.$jobType_id.'_'.$target_store_id.'_'.$data['product_id'].'_'.$attribute['attribute_id'];

                        $this->_attributeHelper->appendAttributeLabel($data,$attribute,$job_name,$source_store_id,$xmlHelper);

                        if(is_array($attribute['value']))
                        {
                            foreach ($attribute['value'] as $value)
                            {

                                $xmlHelper->appendDataToRoot([
                                    'name' => $job_name.'_'.$value['option_id'],
                                    'content_context' => 'product_attribute_value',
                                    'content_context_url' => $data['product_url'],
                                    'option_translation_id'=>$value['translation_id'],
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
                                'attribute_translation_id'=>$attribute['value_translation_id'],
                                'source_store_id'=> $source_store_id,
                                'product_id' => $data['product_id'],
                                'attribute_id'=>$attribute['attribute_id'],
                                'attribute_label'=>$attribute['label'],
                                'value' => $attribute['value']
                            ]);

                        }

                    }

                }

            }catch (Exception $e){

                $this->_logger->error('error '.__FILE__.' '.__LINE__.''.$e->getMessage(),array($e));

            }
        }

        return false;

    }

    /**
     * @param $job_id
     * @return $this
     */
    public function saveProductData($job_id)
    {
        try {

            foreach ($this->_productData as $product_key => $data) {

                if( count( $data['attributes'] )){
                    foreach ($data['attributes'] as $attribute_key => $attribute) {

                        $attributeTranslationModel = $this->_attributeTranslationFactory->create();

                        if (is_array($attribute['value'])) {

                            $attributeTranslationModel->setData(
                                [
                                    'job_id' => $job_id,
                                    'entity_id' => $data['product_id'],
                                    'attribute_id' => $attribute['attribute_id'],
                                    'original_value' => $attribute['label'],
                                    'has_option' => (bool)1,
                                    'is_label' => (bool)1
                                ]
                            )->save();

                            $this->_productData[$product_key]['attributes'][$attribute_key]['label_translation_id'] = $attributeTranslationModel->getId();

                            $this->saveOptionValues($attribute['value'], $attributeTranslationModel->getId(),$product_key,$attribute_key);

                        }else{

                            $attributeTranslationModel->setData(
                                [
                                    'job_id' => $job_id,
                                    'entity_id' => $data['product_id'],
                                    'attribute_id' => $attribute['attribute_id'],
                                    'original_value' => $attribute['label'],
                                    'is_label' => (bool)1
                                ]
                            )->save();

                            $this->_productData[$product_key]['attributes'][$attribute_key]['label_translation_id'] = $attributeTranslationModel->getId();

                            $attributeTranslationModel->setData(
                                [
                                    'job_id' => $job_id,
                                    'entity_id' => $data['product_id'],
                                    'attribute_id' => $attribute['attribute_id'],
                                    'original_value' => $attribute['value'],
                                    'is_label' => (bool)0
                                ]
                            )->save();

                            $this->_productData[$product_key]['attributes'][$attribute_key]['value_translation_id'] = $attributeTranslationModel->getId();

                        }
                    }
                }else{
                    $this->_logger->error('error '.__FILE__.' '.__LINE__.''. __(' no product attributes being selected'));
                }
            }
            return $this;

        } catch (Exception $e) {
            $this->_logger->error('error '.__FILE__.' '.__LINE__.''.$e->getMessage(),array($e));
        }

    }

    /**
     * @param $option_values
     * @param $attribute_translation_id
     * @param $product_key
     * @param $attribute_key
     */
    protected function saveOptionValues(
        $option_values,
        $attribute_translation_id,
        $product_key,
        $attribute_key
    )
    {

        try{

            foreach ($option_values as $option_key => $option){

                $attributeTranslationOptionModel = $this->_attributeOptionTranslationFactory->create();

                $attributeTranslationOptionModel->setData(
                    [
                        'attribute_translation_id'=>$attribute_translation_id,
                        'option_id'=>$option['option_id'],
                        'original_value'=>$option['value']
                    ]
                )->save();

                $this->_productData[$product_key]['attributes'][$attribute_key]['value'][$option_key]['translation_id'] = $attributeTranslationOptionModel->getId();

            }

        }catch (Exception $e) {

            $this->_logger->error('error'.__FILE__.' '.__LINE__.''.$e->getMessage(),array($e));
        }

    }

    private function _getChildrenProducts( $parentIds = [] ){
        $children = [];
        $types = [
            Type::TYPE_BUNDLE,
            Grouped::TYPE_CODE,
            Configurable::TYPE_CODE
        ];

        if( !is_array( $parentIds )){
            $parentIds = [ $parentIds ];
        }

        if( count($parentIds) > 0 ){
            $parentProducts = $this->_productCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addIdFilter($parentIds)
                ->load();
            /** @var \Magento\Catalog\Model\Product $product */
            foreach ( $parentProducts->getItems() as $product){
                $productTypeInstance = $product->getTypeInstance();
                $productTypeId = $product->getTypeId();
                if( in_array($productTypeId, $types )){
                    $childrenArray = $productTypeInstance->getChildrenIds($product->getId());
                    foreach ( $childrenArray as $childrenItem ){
                        foreach ( $childrenItem as $k => $v ){
                            if( !in_array( $v, $children) )
                                array_push($children, $v);
                        }
                    }
                }
            }

        }
        return $children;
    }
}