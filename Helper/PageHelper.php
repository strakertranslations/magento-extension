<?php

namespace Straker\EasyTranslationPlatform\Helper;

use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\AttributeRepository;
use Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection as AttributeCollection;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollection;
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

class PageHelper extends AbstractHelper
{

    protected $_productFactory;
    protected $_pageCollectionFactory;
    protected $_attributeTranslationModel;
    protected $_attributeOptionTranslationModel;
    protected $_attributeCollectionFactory;
    protected $_storeManager;

    protected $_entityTypeId;
    protected $_pageData;
    protected $_storeId;

    protected $_pageAttributes = array(
        'name',
        'description',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'content_heading',
        'content'
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
        PageCollection $pageCollectionFactory,
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
        $this->_pageCollectionFactory = $pageCollectionFactory;
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
        return $this->_pageAttributes;
    }


    /**
     * @param $product_ids
     * @param $store_id
     * @return $this
     * Todo: Add store id to filter products by store
     */
    public function getPages(
        $page_ids,
        $source_store_id
    )
    {
        if(strpos($page_ids,'&'))
        {
            $page_ids = explode('&',$page_ids);
        }

        $this->_storeId = $source_store_id;

        $pages = $this->_pageCollectionFactory->create()
            ->addStoreFilter($this->_storeId)
            ->addFieldToFilter( 'page_id',  array( 'in' => $page_ids ));

        $this->_pageData = $pages->toArray()['items'];

        return $this;
    }

    /**
     * @return $this
     */
    public function getSelectedPageAttributes()
    {
        $pageData = [];

        foreach ($this->_pageData as $page_key => $attribute_data)
        {

            $attributeData = [];

            foreach ($attribute_data as $attribute_key => $attribute)
            {
                if(in_array($attribute_key,$this->getAttributes()) && !is_null($attribute))
                {

                    array_push($attributeData,[
                        'attribute_id'=>$attribute_key,
                        'label'=>$attribute_key,
                        'value'=>$attribute
                    ]);
                }

            }

            $pageData[] = [
                'page_id'=>$this->_pageData[$page_key]['page_id'],
                'page_title'=>$this->_pageData[$page_key]['title'],
                'page_url'=>$this->_storeManager->getStore($this->_storeId)->getBaseUrl().$this->_pageData[$page_key]['identifier'].'.html',//check
                'attributes'=>$attributeData
            ];
        }

        $this->_pageData = $pageData;

        return $this;
    }

    /**
     * @param $jobModel
     * @return string
     */
    public function generateCategoryXML($jobModel)
    {
        $this->_xmlHelper->create('_'.$jobModel->getId().'_'.time());

        $this->appendPageAttributes(
            $this->_pageData,
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
    protected function appendPageAttributes(
        $pageData,
        $job_id,
        $jobtype_id,
        $source_store_id,
        $target_store_id,
        $xmlHelper
    )
    {
        if($pageData)
        {

            foreach ($pageData as $data){

                foreach ($data['attributes'] as $attribute) {

                        $job_name = $job_id.'_'.$jobtype_id.'_'.$target_store_id.'_'.$data['page_id'].'_'.$attribute['attribute_id'];

                        $xmlHelper->appendDataToRoot([
                            'name' => $job_name,
                            'content_context' => 'page_attribute_value',
                            'content_context_url' => $data['page_url'],
                            'attribute_translation_id'=>$attribute['value_translation_id'],
                            'source_store_id'=> $source_store_id,
                            'page_id' => $data['page_id'],
                            'attribute_id'=>$attribute['attribute_id'],
                            'attribute_label'=>$attribute['label'],
                            'value' => $attribute['value']
                        ]);

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
    public function savePageData($job_id)
    {

        foreach ($this->_pageData as $pagekey => $data) {

            foreach ($data['attributes'] as $key => $attribute) {

                $attributeTranslationModel = $this->_attributeTranslationFactory->create();

                try{

                    $attributeTranslationModel->setData(
                        [
                            'job_id' => $job_id,
                            'entity_id' => $data['page_id'],
                            'attribute_id' => $attribute['attribute_id'],
                            'original_value' => $attribute['value'],
                            'is_label' => (bool)0
                        ]
                    )->save();

                    $this->_pageData[$pagekey]['attributes'][$key]['value_translation_id'] = $attributeTranslationModel->getId();
                    $this->_pageData[$pagekey]['attributes'][$key]['attribute_id'] = $attributeTranslationModel->getAttributeId();

                }catch (Exception $e){

                    $this->_logger->error('error '.__FILE__.' '.__LINE__.''.$e->getMessage(),array($e));

                }

            }
        }

        return $this;
    }

}