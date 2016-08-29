<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Test;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as OptionCollection;
use Magento\Framework\Xml\Parser;

use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection as configAttributeCollection;

use Magento\Framework\App\ResourceConnection;

use Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob\Attribute;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Helper\XmlHelper;
use Straker\EasyTranslationPlatform\Helper\CategoryHelper;

use Straker\EasyTranslationPlatform\Helper\ImportHelper;

use Straker\EasyTranslationPlatform\Model\JobFactory;
use Straker\EasyTranslationPlatform\Model\ResourceModel\Job\CollectionFactory;
use Straker\EasyTranslationPlatform\Model\StrakerAPI;

use Straker\EasyTranslationPlatform\Model\AttributeOptionTranslation;
use Straker\EasyTranslationPlatform\Model\AttributeOptionTranslationFactory;
use Straker\EasyTranslationPlatform\Model\AttributeTranslationFactory;

use Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation;
use Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation\CollectionFactory as AttributeTranslationCollection;
use Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeOptionTranslation\CollectionFactory as AttributeOptionTranslationCollection;

class Index extends \Magento\Backend\App\Action
{
    protected $_attributeCollection;
    protected $_jsonFactory;
    protected $_resultPageFactory;
    protected $_configHelper;
    protected $_xmlHelper;
    protected $_attributeTranslationFactory;
    protected $_attributeOptionTranslationFactory;
    protected $_attributeTranslationCollection;
    protected $_attributeTranslationOptionCollection;
    protected $_productFactory;
    protected $_attributeRepository;
    protected $_resourceConnection;
    protected $_attributeOptionFactory;
    protected $_jobFactory;
    protected $_translatedOptions;
    protected $_translatedOptionIds;
    /** @var  \Magento\Catalog\Model\Product $_product */
    protected $_product;
    protected $_registry;
    protected $_api;
    protected $_configurableAttribute;
    protected $_importHelper;
    protected $_configAttributeCollection;
    protected $_categoryFactory;
    protected $_categoryCollection;
    protected $_categoryHelper;
    protected $_testRequest =
        [
            'job_id' => 12,
            'job_key' => 1,
            'job_type_id' => 1,
            'target_store_id'=>2
        ];

    protected $_testFilePath = '/straker_job_12_1470260999.xml';

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        JsonFactory $jsonFactory,
        AttributeCollection $attCollection,
        OptionCollection $attOptCollection,
        ConfigHelper $configHelper,
        XmlHelper $xmlHelper,
        Parser $xmlParser,
        AttributeTranslationFactory $attributeTranslationFactory,
        AttributeOptionTranslationFactory $attributeOptionTranslationFactory,
        AttributeTranslationCollection $attributeTranslationCollection,
        AttributeOptionTranslationCollection $attributeOptionTranslationCollection,
        ProductFactory $productFactory,
        AttributeRepository $attributeRepository,
        ResourceConnection $connection,
        OptionFactory $optionFactory,
        JobFactory $jobFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        StrakerAPI $api,
        ImportHelper $importHelper,
        configAttributeCollection $configAttributeCollection,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        CategoryHelper $categoryHelper,
        \Magento\Framework\Registry $registry
    )
    {
        $this->_attributeCollection = $attCollection;
        $this->_attributeOptionCollection = $attOptCollection;
        $this->_resultPageFactory = $pageFactory;
        $this->_jsonFactory = $jsonFactory;
        $this->_configHelper = $configHelper;
        $this->_xmlHelper = $xmlHelper;
        $this->_xmlParser = $xmlParser;
        $this->_attributeTranslationFactory = $attributeTranslationFactory;
        $this->_attributeOptionTranslationFactory = $attributeOptionTranslationFactory;
        $this->_attributeTranslationCollection = $attributeTranslationCollection;
        $this->_attributeOptionTranslationCollection = $attributeOptionTranslationCollection;
        $this->_productFactory = $productFactory;
        $this->_attributeRepository = $attributeRepository;
        $this->_resourceConnection = $connection;
        $this->_attributeOptionFactory = $optionFactory;
        $this->_jobFactory = $jobFactory;
        $this->_api = $api;
        $this->_importHelper = $importHelper;
        $this->_configAttributeCollection = $configAttributeCollection;
        $this->_categoryFactory = $categoryFactory;
        $this->_categoryHelper = $categoryHelper;
        $this->_registry = $registry;

//        foreach ($this->_categoryHelper->getAttributes()->getData() as $item){
//            var_dump($item['attribute_code']);
//        }
//exit;
//        $attr = [
//            'name','description','meta_title','meta_keywords','meta_description'
//        ];
//        $collection = $this->_categoryCollection = $categoryCollectionFactory->create();
////            ->addFieldToFilter('entity_id', ['eq'=>20]);
//        foreach ($attr as $item){
//            $collection->addFieldToSelect($item);
//        }
//        foreach ($this->_categoryCollection->getItems() as $category){
//            foreach ( $attr as $item ){
////                var_dump($category->getData($item));
//            }
//            var_dump($category->getData('name'));
//        }
//        var_dump($this->_categoryCollection->getData());

//        $category = $this->_categoryFactory->create()->load(20);
//        var_dump($category->getData('name'));
//        foreach ( $attr as $item ){
//            var_dump($category->getData($item));
//        }

//        $p = $productFactory->create()->load(2002);
//        foreach($p->getTypeInstance()->getChildrenIds($p->getId()) as $item){
//            var_dump($item);
//        }

//exit;
        return parent::__construct($context);
    }

//    function multiArrayValueSearch($haystack, $needle, &$result, &$aryPath=NULL, $currentKey='') {
//        if (is_array($haystack)) {
//            $count = count($haystack);
//            $iterator = 0;
//            foreach($haystack as $location => $straw) {
//                $iterator++;
//                $next = ($iterator == $count)?false:true;
//                if (is_array($straw)) $aryPath[$location] = $location;
//                multiArrayValueSearch($straw,$needle,$result,$aryPath,$location);
//                if (!$next) {
//                    unset($aryPath[$currentKey]);
//                }
//            }
//        } else {
//            $straw = $haystack;
//            if ($straw == $needle) {
//                if (!isset($aryPath)) {
//                    $strPath = "\$result[$currentKey] = \$needle;";
//                } else {
//                    $strPath = "\$result['".join("']['",$aryPath)."'][$currentKey] = \$needle;";
//                }
//                eval($strPath);
//            }
//        }
//    }

    public function execute()
    {
        $p = $this->_productFactory->create()->load(2002);
        $this->_registry->register('current_product', $p);
        $resultPage = $this->_resultPageFactory->create();
        //$resultPage->setActiveMenu('Straker_EasyTranslationPlatform::managejobs');
        $resultPage->getConfig()->getTitle()->prepend(__('Straker Translations'));
        return $resultPage;
//
//        var_dump($this->getUrl('straker/setup_languagepairs/index',['target_store_id'=>1]));
//
//        exit;
//
//        $productData = $this->_productFactory->getById(2045);
//
//        $config = $this->_configAttributeCollection->addFieldToFilter( 'attribute_id',   array( 'eq' => 90 ) );
//
//        var_dump($config->getData());
//
//        exit;
//
//
//        $att = $this->_attributeRepository->get(\Magento\Catalog\Model\Product::ENTITY,'color');
//
//        $att->setData('label','颜色');
//
//        var_dump($att->getStoreLabels());
//
//        $this->_importHelper->saveConfigLabel($att,2);
//
//        exit;


    }

    //add target store id
    public function importTranslatedProducts($job_id)
    {

        $product_ids = $this->getProductIds($job_id);

        $this->importTranslatedOptionValues($job_id);

        foreach ($product_ids as $id)
        {
            $products = $this->_attributeTranslationCollection->create()
                ->addFieldToSelect(['attribute_id','original_value','translated_value'])
                ->addFieldToFilter( 'job_id',   array( 'eq' => $job_id ) )
                ->addFieldToFilter( 'entity_id',   array( 'eq' => $id ) )
                ->addFieldToFilter( 'is_label',   array( 'eq' => 0 ) );

            $labels = $this->_attributeTranslationCollection->create()
                ->addFieldToSelect(['attribute_id','original_value','translated_value'])
                ->addFieldToFilter( 'job_id',   array( 'eq' => $job_id ) )
                ->addFieldToFilter( 'entity_id',   array( 'eq' => $id ) )
                ->addFieldToFilter( 'is_label',   array( 'eq' => 1 ) )
                ->addFieldToFilter( 'translated_value',   array( 'notnull' => true ) );


            foreach ($labels->toArray()['items'] as $data){

                $att = $this->_attributeRepository->get(\Magento\Catalog\Model\Product::ENTITY,$data['attribute_id']);

                $new_labels = $att->getStoreLabels();

                $new_labels[$this->_testRequest['target_store_id']] = $data['translated_value'];

                $att->setStoreLabels($new_labels)->save();
            }

            $attData = [];

            foreach ($products->toArray()['items'] as $data){

                $attData[$data['attribute_id']] = $data['translated_value'];
            }


            $this->_productFactory->updateAttributes(array($id),$attData,$this->_testRequest['target_store_id']);
        }


    }

    protected function importTranslatedOptionValues($job_id)
    {

        $translatedOptionData = $this->getOptionValues($job_id);

        if(!empty($translatedOptionData))
        {
            $insertData = [];

            foreach ($translatedOptionData as $data){

                $insertData[] = ['option_id' => $data['option_id'], 'store_id' => '2', 'value' =>$data['translated_value']];
            }

            $connection = $this->_resourceConnection->getConnection();

            $table = $this->_resourceConnection->getTableName('eav_attribute_option_value');

            $connection->insertMultiple($table, $insertData);

        }

    }

    protected function getProductIds($job_id)
    {
        $product_ids = $this->_attributeTranslationCollection->create()
            ->addFieldToSelect(array('entity_id'))
            ->addFieldToFilter( 'job_id',   array( 'eq' => $job_id ) );

        $product_ids->getSelect()->group('entity_id');

        $products = $product_ids->toArray();

        $productIdArray = [];

        array_walk_recursive($products['items'], function($value,$key) use (&$productIdArray) { if($key == 'entity_id'){$productIdArray[] = $value;}});

        return $productIdArray;
    }

    /**
     * This function compares two arrays. An array of existing option attribute values for the target store
     * and the translated option attribute values. This is to avoid duplicate attribute values
     * for a target language store in Magento.
     *
     * @param $job_id
     * @return array - ids of translated options to be inserted in to the options table.
     *
     */
    protected function getOptionValues($job_id)
    {
        //Straker Translation Translation Ids
        $translatedOptionKeys = [];

        //Magento Option Id's
        $translatedOptionIds = [];

        //Find Attributes with translated Options
        $translatedAttributes = $this->_attributeTranslationCollection->create()
            ->addFieldToSelect(['attribute_id','original_value','translated_value'])
            ->addFieldToFilter( 'job_id',   array( 'eq' => $job_id ) )
            ->addFieldToFilter( 'has_option',   array( 'eq' => 1 ))
            ->toArray();

        //Walk over array Array to get a single array of Straker's attribute_translation id (primary key)
        array_walk_recursive($translatedAttributes['items'], function($value,$key) use (&$translatedOptionKeys) {

            if($key == 'attribute_translation_id'){

                $translatedOptionKeys[] = $value;

            }
        });

        //Find the Foreign Key (attribute_translation_id) in the Straker attribute option translation table
        $translatedOptions = $this->_attributeOptionTranslationCollection->create()
            ->addFieldToSelect(['option_id','original_value','translated_value'])
            ->addFieldToFilter('attribute_translation_id', array('in'=>$translatedOptionKeys))
            ->toArray();


        //Sort Array by Option Id Asc
        usort($translatedOptions['items'], function($a, $b) {

            return $a['option_id'] - $b['option_id'];

        });

        //Walk over array Array to get a single array of Straker's attribute_translation ids (primary key)
        array_walk_recursive($translatedOptions['items'], function($value,$key) use (&$translatedOptionIds) {

            if($key == 'option_id'){

                $translatedOptionIds[] = $value;

            }

        });

        //Filter out options which have already been translated for a store - checking Magento's options table (eav_attribute_option).
        $existingOptions = $this->_attributeOptionCollection->create()
            ->SetStoreFilter($this->_testRequest['target_store_id'])
            ->SetIdFilter($translatedOptionIds)
            ->toOptionArray();

        //Sort Array by Option Id (option value) Asc
        usort($existingOptions, function($a, $b) {

            return $a['value'] - $b['value'];

        });


        //Check Length of both Arrays
        if(count($existingOptions) == count($translatedOptions['items']))
        {
            $translatedOptions = array_slice($translatedOptions['items'],0);

            foreach ($existingOptions as $key => $value)
            {
                if($existingOptions[$key]['label'] !== $translatedOptions[$key]['translated_value'])
                {
                    $this->_translatedOptions[] = ['t_value'=> $translatedOptions[$key]['translated_value'],'o_value'=>$existingOptions[$key]['label']];

                    $this->_translatedOptionIds[] = $translatedOptions[$key]['option_id'];
                };
            }

            $translatedOptionValues = $this->_attributeOptionTranslationCollection->create()
                ->addFieldToSelect(['option_id','original_value','translated_value'])
                ->addFieldToFilter('option_id', array('in'=>$this->_translatedOptionIds))->toArray()['items'];

            return $translatedOptionValues;

        }else{

            return [];
        }


    }


}
