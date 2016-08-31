<?php

namespace Straker\EasyTranslationPlatform\Helper;

use Exception;
use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\AttributeRepository;
use Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection as AttributeCollection;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollection;
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

class BlockHelper extends AbstractHelper
{

    protected $_blockCollectionFactory;
    protected $_attributeTranslationModel;
    protected $_attributeOptionTranslationModel;
    protected $_attributeCollectionFactory;
    protected $_storeManager;
    protected $_attributeTranslationFactory;
    protected $_attributeOptionTranslationFactory;
    protected $_attributeRepository;
    protected $_configHelper;
    protected $_attributeHelper;
    protected $_xmlHelper;

    protected $_entityTypeId;
    protected $_blockData;
    protected $_storeId;

    const blockAttributes = [
        ['name'=>'title','label'=>'Title'],
        ['name'=>'content','label'=>'Content']
    ];


    public function __construct(
        Context $context,
        AttributeRepository $attributeRepository,
        AttributeCollection $attributeCollectionFactory,
        BlockCollection $blockCollectionFactory,
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
        $this->_blockCollectionFactory = $blockCollectionFactory;
        $this->_attributeTranslationFactory = $attributeTranslationFactory;
        $this->_attributeOptionTranslationFactory = $attributeOptionTranslationFactory;
        $this->_attributeRepository = $attributeRepository;
        $this->_configHelper = $configHelper;
        $this->_attributeHelper = $attributeHelper;
        $this->_xmlHelper = $xmlHelper;
        $this->_logger = $logger;
        $this->_entityTypeId =  $eavConfig->getEntityType(CategoryAttributeInterface::ENTITY_TYPE_CODE)->getEntityTypeId();
        $this->_storeManager = $storeManager;

        parent::__construct($context);
    }

    public function getAttributes()
    {
        return array_column( self::blockAttributes, 'name');
    }

    /**
     * @param $page_ids
     * @param $source_store_id
     * @return $this Todo: Add store id to filter products by store
     * Todo: Add store id to filter products by store
     * @internal param $product_ids
     * @internal param $store_id
     */
    public function getBlocks(
        $block_ids,
        $source_store_id
    )
    {
        if(strpos($block_ids,'&'))
        {
            $block_ids = explode('&',$block_ids);
        }

        $this->_storeId = $source_store_id;


        $blocks = $this->_blockCollectionFactory->create()
            ->addStoreFilter($source_store_id)
            ->addFieldToFilter( 'main_table.block_id',  array( 'in' => $block_ids ));

        $this->_blockData = $blocks->toArray()['items'];

        return $this;
    }

    /**
     * @return $this
     */
    public function getSelectedBlockAttributes()
    {
        $blockData = [];

        foreach ($this->_blockData as $block_key => $attribute_data)
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

            $blockData[] = [
                'block_id'=>$this->_blockData[$block_key]['block_id'],
                'page_title'=>$this->_blockData[$block_key]['title'],
                'page_url'=>$this->_storeManager->getStore($this->_storeId)->getBaseUrl().$this->_blockData[$block_key]['identifier'].'.html',//check
                'attributes'=>$attributeData
            ];
        }

        $this->_blockData = $blockData;

        return $this;
    }

    /**
     * @param $jobModel
     * @return string
     */
    public function generateBlockXML($jobModel)
    {
        $this->_xmlHelper->create('_'.$jobModel->getId().'_'.time());

        $this->appendBlockAttributes(
            $this->_blockData,
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
     * @param $pageData
     * @param $job_id
     * @param $jobType_id
     * @param $source_store_id
     * @param $target_store_id
     * @param $xmlHelper
     * @return $this|bool
     */
    protected function appendBlockAttributes(
        $blockData,
        $job_id,
        $jobType_id,
        $source_store_id,
        $target_store_id,
        $xmlHelper
    )
    {
        if($blockData)
        {
            foreach ($blockData as $data){

                foreach ($data['attributes'] as $attribute) {

                        $job_name = $job_id.'_'.$jobType_id.'_'.$target_store_id.'_'.$data['block_id'].'_'.$attribute['attribute_id'];

                        $xmlHelper->appendDataToRoot([
                            'name' => $job_name,
                            'content_context' => 'block_attribute_value',
                            'content_context_url' => $data['page_url'],
                            'attribute_translation_id'=>$attribute['value_translation_id'],
                            'source_store_id'=> $source_store_id,
                            'block_id' => $data['block_id'],
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
    public function saveBlockData($job_id)
    {

        foreach ($this->_blockData as $blockKey => $data) {

            foreach ($data['attributes'] as $key => $attribute) {

                $attributeTranslationModel = $this->_attributeTranslationFactory->create();

                try{

                    $attributeTranslationModel->setData(
                        [
                            'job_id' => $job_id,
                            'entity_id' => $data['block_id'],
                            'attribute_id' => $attribute['attribute_id'],
                            'original_value' => $attribute['value'],
                            'is_label' => (bool)0
                        ]
                    )->save();

                    $this->_blockData[$blockKey]['attributes'][$key]['value_translation_id'] = $attributeTranslationModel->getId();
                    $this->_blockData[$blockKey]['attributes'][$key]['attribute_id'] = $attributeTranslationModel->getAttributeId();

                }catch (Exception $e){

                    $this->_logger->error('error '.__FILE__.' '.__LINE__.''.$e->getMessage(),array($e));

                }

            }
        }

        return $this;
    }

}