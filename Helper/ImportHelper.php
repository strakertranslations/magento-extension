<?php

namespace Straker\EasyTranslationPlatform\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Xml\Parser;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

use Magento\UrlRewrite\Model\UrlFinderInterface;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as OptionCollection;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Cms\Model\PageFactory as PageFactory;
use Magento\Cms\Model\BlockFactory as BlockFactory;

use Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob\Attribute;
use Straker\EasyTranslationPlatform\Logger\Logger;
use Straker\EasyTranslationPlatform\Helper\XmlHelper;

use Straker\EasyTranslationPlatform\Model\AttributeTranslation;
use Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation as AttributeTranslationResourceModel;
use Straker\EasyTranslationPlatform\Model\JobFactory;
use Straker\EasyTranslationPlatform\Model\AttributeOptionTranslation;
use Straker\EasyTranslationPlatform\Model\AttributeTranslationFactory;
use Straker\EasyTranslationPlatform\Model\AttributeOptionTranslationFactory;
use Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation\CollectionFactory as AttributeTranslationCollection;
use Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeOptionTranslation\CollectionFactory as AttributeOptionTranslationCollection;

class ImportHelper extends AbstractHelper
{
    /** @var $this->configHelper \Straker\EasyTranslationPlatform\Helper\ConfigHelper */
    public $configHelper;

    protected $_logger;
    protected $_xmlParser;
    protected $_xmlHelper;
    protected $_attributeTranslationFactory;
    protected $_attributeOptionTranslationFactory;
    protected $_attributeTranslationCollection;
    protected $_attributeOptionTranslationCollection;
    protected $_categoryCollection;
    protected $_jobFactory;
    protected $_attributeRepository;
    protected $_productAction;
    protected $_resourceConnection;
    protected $_attributeCollection;
    protected $_optionCollection;
    protected $_storeManager;
    protected $_pageFactory;
    protected $_blockFactory;
    protected $_urlFinder;
    protected $_jobModel;
    protected $_parsedFileData = [];
    protected $_translatedLabels = [];
    protected $_attributeTranslationIds;
    protected $_saveOptionIds = [];

    protected $_productData;
    protected $_categoryData;

    protected $_selectQuery = 'select option_id from %1$s where option_id = %2$s and store_id = %3$s';
    protected $_updateQuery = 'update %1$s set value = "%2$s" where option_id = %3$s and store_id = %4$s';
    protected $_labelTable = 'catalog_product_super_attribute_label';
    protected $_categoryFactory;
    protected $_pageData;
    protected $_blockData;

    public function __construct(

        Context $context,
        Logger $logger,
        Parser $xmlParser,
        XmlHelper $xmlHelper,
        ConfigHelper $configHelper,
        JobFactory $jobFactory,
        AttributeTranslationFactory $attributeTranslationFactory,
        AttributeOptionTranslationFactory $attributeOptionTranslationFactory,
        AttributeTranslationCollection $attributeTranslationCollection,
        AttributeOptionTranslationCollection $attributeOptionTranslationCollection,
        AttributeRepositoryInterface $attributeRepository,
        ProductAction $productAction,
        ResourceConnection $resourceConnection,
        CategoryCollectionFactory $categoryCollectionFactory,
        CategoryFactory $categoryFactory,
        AttributeCollection $attributeCollection,
        OptionCollection $optionCollection,
        PageFactory $pageFactory,
        BlockFactory $blockFactory,
        StoreManagerInterface $storeManager,
        UrlFinderInterface $urlFinder
    ) {
        $this->_logger = $logger;
        $this->_xmlParser = $xmlParser;
        $this->_xmlHelper = $xmlHelper;
        $this->configHelper = $configHelper;
        $this->_jobFactory = $jobFactory;
        $this->_attributeTranslationFactory = $attributeTranslationFactory;
        $this->_attributeOptionTranslationFactory = $attributeOptionTranslationFactory;
        $this->_attributeTranslationCollection = $attributeTranslationCollection;
        $this->_attributeOptionTranslationCollection = $attributeOptionTranslationCollection;
        $this->_categoryCollection = $categoryCollectionFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_attributeRepository = $attributeRepository;
        $this->_productAction = $productAction;
        $this->_resourceConnection = $resourceConnection;
        $this->_attributeCollection = $attributeCollection;
        $this->_optionCollection = $optionCollection;
        $this->_pageFactory = $pageFactory;
        $this->_blockFactory = $blockFactory;
        $this->_storeManager = $storeManager;
        $this->_urlFinder = $urlFinder;

        parent::__construct($context);
    }

    public function create($job_id)
    {
        $this->_jobModel = $this->_jobFactory->create()->load($job_id);

        return $this;

    }

    public function parseTranslatedFile()
    {
        $filePath = $this->configHelper->getTranslatedXMLFilePath() . DIRECTORY_SEPARATOR . $this->_jobModel->getData('translated_file');

        $parsedData = $this->_xmlParser->load($filePath)->xmlToArray();

        $this->_parsedFileData = $parsedData['root']['data'];

        $this->_categoryData = array_filter($this->_parsedFileData, function ($v) {

            return preg_match('/category/', $v['_attribute']['content_context']);

        });

        $this->_productData = array_filter($this->_parsedFileData, function ($v) {

            return preg_match('/product/', $v['_attribute']['content_context']);

        });

        $this->_pageData = array_filter($this->_parsedFileData, function ($v) {

            return preg_match('/page/', $v['_attribute']['content_context']);

        });

        $this->_blockData = array_filter($this->_parsedFileData, function ($v) {

            return preg_match('/block/', $v['_attribute']['content_context']);

        });

        return $this;
    }

    public function saveData()
    {
        if (!empty($this->_productData)) {
            $this->saveTranslatedProductData();
        }
        if (!empty($this->_categoryData)) {
            $this->saveTranslatedCategoryData();
        }

        if (!empty($this->_pageData)) {
            $this->saveTranslatedPageData();
        }

        if (!empty($this->_blockData)) {
            $this->saveTranslatedBlockData();
        }

        return $this;
    }

    public function publishTranslatedData()
    {
        if ($this->_jobModel->getJobType() == 'product') {
            $this->publishTranslatedProductData();
        }

        if ($this->_jobModel->getJobType() == 'category') {
            $this->publishTranslatedCategoryData();
        }

        if ($this->_jobModel->getJobType() == 'page') {
            $this->publishTranslatedPageData();
        }

        if ($this->_jobModel->getJobType() == 'block') {
            $this->publishTranslatedBlockData();
        }

        return $this;
    }


    public function saveTranslatedProductData()
    {
        $this->getOptionIds($this->_jobModel->getId());

        foreach ($this->_productData as $data) {

            if (array_key_exists('attribute_translation_id', $data['_attribute'])) {

                try {
                    $att_trans_model = $this->_attributeTranslationFactory->create()->load($data['_attribute']['attribute_translation_id']);

                    $att_trans_model->addData(['is_imported' => 1, 'translated_value' => $data['_value']['value']]);

                    $att_trans_model->save();

                    strpos($data['_attribute']['content_context'], 'label') ? $this->saveLabel($data['_attribute']['attribute_id'], $data['_value']['value']) : false;

                } catch (\Exception $e) {
                    $this->_logger->error('error' . __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage(), array($e));
                }

            }

            if (array_key_exists('option_translation_id', $data['_attribute'])) {

                try {
                    $att_opt_model = $this->_attributeOptionTranslationFactory->create()->load($data['_attribute']['option_translation_id']);

                    $att_opt_model->addData(['is_imported' => 1, 'translated_value' => $data['_value']['value']]);

                    $att_opt_model->save();

                    if (!in_array($att_opt_model->getData('option_id'), $this->_saveOptionIds)) {
                        $translatedOptions = $this->_attributeOptionTranslationCollection->create()
                            ->addFieldToSelect(['option_id', 'translated_value'])
                            ->addFieldToFilter('attribute_translation_id', array('in' => $this->_attributeTranslationIds))
                            ->addFieldToFilter('option_id', array('eq' => $att_opt_model->getData('option_id')));

                        $translatedOptions->massUpdate(array('translated_value' => $att_opt_model->getData('translated_value')));

                        $this->_saveOptionIds[] = $att_opt_model->getData('option_id');

                    }

                } catch (\Exception $e) {
                    $this->_logger->error('error' . __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage(), array($e));
                }

            }

        }

        return $this;
    }


    public function publishTranslatedProductData()
    {
        $product_ids = $this->getProductIds($this->_jobModel->getId());

        $this->importTranslatedOptionValues($this->_jobModel->getId());

        $this->importTranslatedAttributeLabels($this->_jobModel->getId());

        foreach ($product_ids as $id) {
            $products = $this->_attributeTranslationCollection->create()
                ->addFieldToSelect(['attribute_id', 'original_value', 'translated_value'])
                ->addFieldToFilter('job_id', array('eq' => $this->_jobModel->getId()))
                ->addFieldToFilter('entity_id', array('eq' => $id))
                ->addFieldToFilter('is_label', array('eq' => 0));

            $attData = [];

            foreach ($products->toArray()['items'] as $data) {

                $attData[$data['attribute_id']] = $data['translated_value'];
            }


            $this->_productAction->updateAttributes(array($id), $attData, $this->_jobModel->getTargetStoreId());
        }

        return $this;
    }

    public function saveLabel($label_id, $value)
    {

        $labels = $this->_attributeTranslationCollection->create()
            ->addFieldToFilter('job_id', array('eq' => $this->_jobModel->getId()))
            ->addFieldToFilter('is_label', array('eq' => 1))
            ->addFieldtoFilter('attribute_id', array('eq' => $label_id))
            ->addFieldToFilter('translated_value', array('null' => true));

        try {
            $labels->massUpdate(array('translated_value' => $value));

        } catch (\Exception $e) {
            $this->_logger->error('error' . __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage(), array($e));
        }


    }

    protected function importTranslatedAttributeLabels($job_id)
    {

        $labels = $this->_attributeTranslationCollection->create()
            ->addFieldToSelect(['attribute_id', 'original_value', 'translated_value'])
            ->addFieldToFilter('job_id', array('eq' => $job_id))
            ->addFieldToFilter('is_label', array('eq' => 1))
            ->addFieldToFilter('translated_value', array('notnull' => true));

        $labels->getSelect()->group('attribute_id');

        foreach ($labels->toArray()['items'] as $data) {

            $att = $this->_attributeRepository->get(\Magento\Catalog\Model\Product::ENTITY, $data['attribute_id']);

            $new_labels = $att->getStoreLabels();

            $new_labels[$this->_jobModel->getTargetStoreId()] = $data['translated_value'];

            $att->setStoreLabels($new_labels)->save();
        }
    }

    protected function importTranslatedOptionValues($job_id)
    {

        $this->getOptionIds($job_id);

        $translatedOptions = $this->_attributeOptionTranslationCollection->create()
            ->addFieldToSelect(['option_id', 'original_value', 'translated_value'])
            ->addFieldToFilter('attribute_translation_id', array('in' => $this->_attributeTranslationIds));

        $translatedOptions->getSelect()->group('option_id');

        $translatedOptionData = $translatedOptions->toArray()['items'];

        $connection = $this->_resourceConnection->getConnection();

        $table = $this->_resourceConnection->getTableName('eav_attribute_option_value');

        if (!empty($translatedOptionData)) {

            foreach ($translatedOptionData as $data) {
                $select_query = sprintf($this->_selectQuery, $table, $data['option_id'], $this->_jobModel->getTargetStoreId());

                if ($connection->fetchOne($select_query)) {
                    $update_query = sprintf($this->_updateQuery, $table, $data['translated_value'], $data['option_id'], $this->_jobModel->getTargetStoreId());

                    $connection->query($update_query);

                } else {

                    $connection->insertArray($table, ['option_id', 'store_id', 'eav_attribute_option_value.value'],
                        [[$data['option_id'], $this->_jobModel->getTargetStoreId(), $data['translated_value']]]);
                };
            }

        }

    }

    protected function getProductIds($job_id)
    {
        $product_ids = $this->_attributeTranslationCollection->create()
            ->addFieldToSelect(array('entity_id'))
            ->addFieldToFilter('job_id', array('eq' => $job_id));

        $product_ids->getSelect()->group('entity_id');

        $products = $product_ids->toArray();

        $productIdArray = [];

        array_walk_recursive($products['items'], function ($value, $key) use (&$productIdArray) {
            if ($key == 'entity_id') {
                $productIdArray[] = $value;
            }
        });

        return $productIdArray;
    }

    /**
     * @param $job_id
     * @return $this
     */
    protected function getOptionIds($job_id)
    {

        //Straker Translation Translation Ids
        $translatedOptionKeys = [];

        //Find Attributes with translated Options
        $translatedAttributes = $this->_attributeTranslationCollection->create()
            ->addFieldToSelect(['attribute_id', 'original_value', 'translated_value'])
            ->addFieldToFilter('job_id', array('eq' => $job_id))
            ->addFieldToFilter('has_option', array('eq' => 1))
            ->toArray()['items'];


        //Walk over array Array to get a single array of Straker's attribute_translation id (primary key)
        array_walk_recursive($translatedAttributes, function ($value, $key) use (&$translatedOptionKeys) {

            if ($key == 'attribute_translation_id') {

                $translatedOptionKeys[] = $value;

            }
        });

        $this->_attributeTranslationIds = $translatedOptionKeys;

        return $this;

    }

    public function saveConfigLabel($attribute, $store_id)
    {
        $connection = $this->_resourceConnection->getConnection();

        $connection->insertOnDuplicate(
            $this->_labelTable,
            [
                'product_super_attribute_id' => (int)$attribute->getId(),
                'use_default' => (int)0,
                'store_id' => $store_id,
                'value' => $attribute->getLabel(),
            ],
            ['value', 'use_default']
        );

        return $this;
    }

    public function saveTranslatedCategoryData()
    {

        foreach ($this->_categoryData as $data) {

            $att_trans_model = $this->_attributeTranslationFactory->create()->load($data['_attribute']['attribute_translation_id']);
            $att_trans_model->addData(['is_imported' => 1, 'translated_value' => $data['_value']['value']]);
            $att_trans_model->save();

        }

        return $this;
    }

    public function publishTranslatedCategoryData()
    {

        $translatedCategories = $this->_attributeTranslationCollection->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('job_id', array('eq' => $this->_jobModel->getId()))->toArray();


        foreach ($translatedCategories['items'] as $data) {

            $attribute_code = $this->_attributeRepository->get(\Magento\Catalog\Model\Category::ENTITY, $data['attribute_id'])->setStoreId($this->_jobModel->getTargetStoreId())->getAttributeCode();
            $category = $this->_categoryFactory->create()->load($data['entity_id'])->setStoreId($this->_jobModel->getTargetStoreId());
            $category->setData($attribute_code, $data['translated_value'])->getResource()->saveAttribute($category, $attribute_code);
        }

        return $this;
    }

    public function saveTranslatedPageData()
    {
        foreach ($this->_pageData as $data) {
            $att_trans_model = $this->_attributeTranslationFactory->create()->load($data['_attribute']['attribute_translation_id']);
            $att_trans_model->addData(['is_imported' => 1, 'translated_value' => $data['_value']['value']]);
            $att_trans_model->save();

        }
        return $this;
    }

    public function publishTranslatedPageData()
    {

        $translatedPageAttributes = $this->_attributeTranslationCollection->create()
            ->addFieldToSelect(['attribute_id', 'translated_value', 'entity_id'])
            ->addFieldToFilter('job_id', array('eq' => $this->_jobModel->getId()));

        $attData = $translatedPageAttributes->toArray()['items'];

        $pageData = [];

        foreach ($attData as $key => $data) {
            $pageData[$data['entity_id']][] = $data;
        }

        foreach ($pageData as $page => $attributes) {
            $original_page = $this->_pageFactory->create()->load($page);

            $urlKey = $this->_urlFinder->findOneByData([
                'request_path' => $original_page->getData('identifier'),
                'store_id' => $this->_jobModel->getTargetStoreId()
            ]);

            $saveData = [
                'title' => $original_page->getData('title'),
                'identifier' => $original_page->getData('identifier'),
                'content' => $original_page->getData('content'),
                'content_heading' => $original_page->getData('content_heading'),
                'page_layout' => $original_page->getData('page_layout'),
                'is_active' => 1,
                'sort_order' => 0,
                'stores' => array($this->_jobModel->getTargetStoreId())
            ];


            foreach ($attributes as $key => $value) {
                $saveData[PageHelper::PageAttributes[$value['attribute_id']]['name']] = $value['translated_value'];
            }

            if (!empty($urlKey)) {
                $page = $this->_pageFactory->create()->load($urlKey->getEntityId())
                    ->setTitle($saveData['title'])
                    ->setContent($saveData['content'])
                    ->setContentHeading($saveData['content_heading'])
                    ->setUpdateTime(time());
            } else {
                $page = $this->_pageFactory->create()->setData($saveData);
            }
            $page->save();

        }

        return $this;

    }

    public function saveTranslatedBlockData()
    {
        foreach ($this->_blockData as $data) {
            $att_trans_model = $this->_attributeTranslationFactory->create()->load($data['_attribute']['attribute_translation_id']);
            $att_trans_model->addData(['is_imported' => 1, 'translated_value' => $data['_value']['value']]);
            $att_trans_model->save();

        }
        return $this;
    }

    //key key in url table
    public function publishTranslatedBlockData()
    {

        $translatedBlockAttributes = $this->_attributeTranslationCollection->create()
            ->addFieldToSelect(['attribute_id', 'translated_value', 'entity_id'])
            ->addFieldToFilter('job_id', array('eq' => $this->_jobModel->getId()));

        $attData = $translatedBlockAttributes->toArray()['items'];

        $blockData = [];

        foreach ($attData as $key => $data) {
            $blockData[$data['entity_id']][] = $data;
        }

        foreach ($blockData as $block => $attributes) {
            $original_block = $this->_blockFactory->create()->load($block);

            $saveData = [
                'title' => $original_block->getData('title'),
                'content' => $original_block->getData('content'),
                'identifier' => $original_block->getData('identifier'),
                'is_active' => 1,
                'stores' => array($this->_jobModel->getTargetStoreId())
            ];

            $blocks = $this->_blockFactory->create()->getResourceCollection()
                ->addFieldToFilter('identifier', ['eq' => $original_block->getIdentifier()]);

            foreach ($attributes as $key => $value) {
                $saveData[BlockHelper::blockAttributes[$value['attribute_id']]['name']] = $value['translated_value'];
            }

            if (count($blocks->getData()) > 1) {
                foreach ($blocks->getItems() as $block){
                    if( in_array($this->_jobModel->getTargetStoreId(), $block->getStores())){
                        $block->setTitle($saveData['title'])
                            ->setContent($saveData['content'])
                            ->setUpdateTime(time())
                            ->save();
                        break;
                    }
                }
            } else {
                $block = $this->_blockFactory->create();
                $block->setData($saveData)->save();
            }
        }
        return $this;

    }

}
