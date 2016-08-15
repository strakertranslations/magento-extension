<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Test;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as OptionCollection;
use Magento\Framework\Xml\Parser;

use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Api\ProductRepositoryInterface as ProductFactory;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection as configAttributeCollection;

use Magento\Framework\App\ResourceConnection;

use Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob\Attribute;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Helper\XmlHelper;

use Straker\EasyTranslationPlatform\Helper\ImportHelper;

use Straker\EasyTranslationPlatform\Model\JobFactory;

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
    protected $_configurableAttribute;
    protected $_importHelper;
    protected $_configAttributeCollection;

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
        ImportHelper $importHelper,
        configAttributeCollection $configAttributeCollection,
        \Magento\Framework\File\Csv $fileCsv,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list
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
        $this->_importHelper = $importHelper;
        $this->_configAttributeCollection = $configAttributeCollection;
        $this->_productFactory = $productFactory;
        $this->_csv = $fileCsv;
        $this->_directory = $directory_list;

        return parent::__construct($context);
    }

    public function execute()
    {
        var_dump(__FUNCTION__);

        $catalog_file_path = $this->_directory->getPath('var').'/csv/catalog_product_20160731_044409.csv';

        $data = $this->_csv->getData($catalog_file_path);

        var_dump($data[0]);
        var_dump($data[1]);

        $productData =
            [
                ['sku','name','price','categories','product_type','attribute_set_code','tax_class_name','weight','description','short_description'],
                ['p-100','test_product','34.000','Default Category/Lights','simple','Default','None',0,'This is a test product for magento','Test Product']
            ];

        $this->_csv->saveData($this->_directory->getPath('var').'/csv/test.csv',$productData);

        exit;


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
