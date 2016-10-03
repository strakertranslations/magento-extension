<?php

namespace Straker\EasyTranslationPlatform\Helper;

use Exception;
use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\AttributeRepository;
use Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection as AttributeCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollection;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Data\Collection;
use Magento\Store\Model\StoreManagerInterface;

use Straker\EasyTranslationPlatform\Model\AttributeOptionTranslation;
use Straker\EasyTranslationPlatform\Model\AttributeTranslationFactory;
use Straker\EasyTranslationPlatform\Model\AttributeOptionTranslationFactory;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Helper\AttributeHelper;
use Straker\EasyTranslationPlatform\Helper\XmlHelper;
use Straker\EasyTranslationPlatform\Logger\Logger;

class CategoryHelper extends AbstractHelper
{

    protected $_productFactory;
    protected $_categoryCollectionFactory;
    protected $_attributeTranslationModel;
    protected $_attributeOptionTranslationModel;
    protected $_attributeCollectionFactory;
    protected $_storeManager;

    protected $_entityTypeId;
    protected $_categoryData;
    protected $_storeId;

    protected $_translatableFrontendLabel = array(
        'name','description','meta_title','meta_keywords','meta_description'
    );

    protected $_translatableBackendType = array (
        'varchar', 'text','int'
    );



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
     */
    public function __construct(
        Context $context,
        AttributeRepository $attributeRepository,
        AttributeCollection $attributeCollectionFactory,
        CategoryCollection $categoryCollectionFactory,
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
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_attributeTranslationFactory = $attributeTranslationFactory;
        $this->_attributeOptionTranslationFactory = $attributeOptionTranslationFactory;
        $this->_attributeRepository = $attributeRepository;
        $this->_configHelper = $configHelper;
        $this->_attributeHelper = $attributeHelper;
        $this->_xmlHelper = $xmlHelper;
        $this->_logger = $logger;
        $this->_entityTypeId =  $eavConfig->getEntityType(\Magento\Catalog\Api\Data\CategoryAttributeInterface::ENTITY_TYPE_CODE)->getEntityTypeId();
        $this->_storeManager = $storeManager;

        parent::__construct($context);
    }

    public function getAttributes()
    {
        $collection = $this->_attributeCollectionFactory->setEntityTypeFilter($this->_entityTypeId)
            ->addFieldToFilter( 'attribute_code',  array( 'in' => $this->_translatableFrontendLabel ));
        return $collection;
    }


    /**
     * @param $product_ids
     * @param $store_id
     * @return $this
     * Todo: Add store id to filter products by store
     */
    public function getCategories(
        $category_ids,
        $source_store_id
    )
    {
        if(strpos($category_ids,',')!== false)
        {
            $category_ids = explode(',',$category_ids);
        }


        $this->_storeManager->setCurrentStore($source_store_id);

        $categories = $this->_categoryCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addIdFilter($category_ids)
            ->load();

        $this->_storeId = $source_store_id;

        $this->_categoryData = $categories;

        return $this;
    }

    /**
     * @return $this
     */
    public function getSelectedCategoryAttributes()
    {
        $categoryData = [];

        foreach ($this->_categoryData as $category)
        {
            $attributeData = [];

            foreach ($this->getAttributes() as $attribute)
            {
                array_push($attributeData,['attribute_id'=>$attribute->getId(),'label'=>$category->getResource()->getAttribute($attribute->getId())->getStoreLabel($this->_storeId),'value'=>$category->getResource()->getAttributeRawValue($category->getId(), $attribute->getId(),$this->_storeId)]);

            }

            $categoryData[] = [
                'category_id'=>$category->getId(),
                'category_name'=>$category->getName(),
                'category_url'=>$this->_storeManager->getStore($this->_storeId)->getBaseUrl().$category->getUrlKey().'.html',//check
                'attributes'=>$attributeData
            ];
        }

        $this->_categoryData = $categoryData;

        return $this;
    }

    /**
     * @param $jobModel
     * @return string
     */
    public function generateCategoryXML($jobModel)
    {
        $this->_xmlHelper->create('_'.$jobModel->getId().'_'.time());

        $this->appendCategoryAttributes(
            $this->_categoryData,
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
     * @param $jobtype_id
     * @param $source_store_id
     * @param $target_store_id
     * @param $xmlHelper
     * @return bool
     */
    protected function appendCategoryAttributes(
        $categoryData,
        $job_id,
        $jobtype_id,
        $source_store_id,
        $target_store_id,
        $xmlHelper
    )
    {

        if($categoryData)
        {

            foreach ($categoryData as $data){

                foreach ($data['attributes'] as $attribute) {

                    if($attribute['value']){

                        $job_name = $job_id.'_'.$jobtype_id.'_'.$target_store_id.'_'.$data['category_id'].'_'.$attribute['attribute_id'];

                        $xmlHelper->appendDataToRoot([
                            'name' => $job_name,
                            'content_context' => 'category_attribute_value',
                            'content_context_url' => $data['category_url'],
                            'attribute_translation_id'=>$attribute['value_translation_id'],
                            'source_store_id'=> $source_store_id,
                            'category_id' => $data['category_id'],
                            'attribute_id'=>$attribute['attribute_id'],
                            'attribute_label'=>$attribute['label'],
                            'value' => $attribute['value']
                        ]);


                    }


                }


            }

            return $this;
        }

        return false;

    }

    /**
     * @param $job_id
     * @return $this
     */
    public function saveCategoryData($job_id)
    {

        foreach ($this->_categoryData as $cat_key => $data)
        {
            foreach ($data['attributes'] as $att_key => $attribute){

                $attributeTranslationModel = $this->_attributeTranslationFactory->create();

                if($attribute['value']){

                    try{

                        $attributeTranslationModel->setData(
                            [
                                'job_id' => $job_id,
                                'entity_id' => $data['category_id'],
                                'attribute_id' => $attribute['attribute_id'],
                                'original_value' => $attribute['value'],
                                'is_label' => (bool)0
                            ]
                        )->save();

                        $this->_categoryData[$cat_key]['attributes'][$att_key]['value_translation_id'] = $attributeTranslationModel->getId();


                    }catch (Exception $e){

                        $this->_logger->error('error '.__FILE__.' '.__LINE__.''.$e->getMessage(),array($e));

                    }

                }

            }
        }

        return $this;
    }

}