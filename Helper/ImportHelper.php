<?php

namespace Straker\EasyTranslationPlatform\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Xml\Parser;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Framework\App\ResourceConnection;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as OptionCollection;

use Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob\Attribute;
use Straker\EasyTranslationPlatform\Logger\Logger;
use Straker\EasyTranslationPlatform\Helper\XmlHelper;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;

use Straker\EasyTranslationPlatform\Model\AttributeTranslation;
use Straker\EasyTranslationPlatform\Model\JobFactory;
use Straker\EasyTranslationPlatform\Model\AttributeOptionTranslation;
use Straker\EasyTranslationPlatform\Model\AttributeTranslationFactory;
use Straker\EasyTranslationPlatform\Model\AttributeOptionTranslationFactory;
use Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation\CollectionFactory as AttributeTranslationCollection;
use Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeOptionTranslation\CollectionFactory as AttributeOptionTranslationCollection;

class ImportHelper extends \Magento\Framework\App\Helper\AbstractHelper
{

    public $configHelper;

    protected $_logger;
    protected $_xmlParser;
    protected $_xmlHelper;

    protected $_attributeTranslationFactory;
    protected $_attributeOptionTranslationFactory;
    protected $_attributeTranslationCollection;
    protected $_attributeOptionTranslationCollection;
    protected $_jobFactory;
    protected $_attributeRepository;
    protected $_productAction;
    protected $_resourceConnection;
    protected $_attributeCollection;
    protected $_optionCollection;

    protected $_jobModel;
    protected $_parsedFileData = [];
    protected $_translatedOptions;
    protected $_translatedOptionIds;

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
        AttributeCollection $attributeCollection,
        OptionCollection $optionCollection

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
        $this->_attributeRepository = $attributeRepository;
        $this->_productAction = $productAction;
        $this->_resourceConnection = $resourceConnection;
        $this->_attributeCollection = $attributeCollection;
        $this->_optionCollection = $optionCollection;

        parent::__construct($context);
    }

    public function create($job_id)
    {
        $this->_jobModel = $this->_jobFactory->create()->load($job_id);

        return $this;

    }

    public function parseTranslatedFile()
    {
        $filePath = $this->configHelper->getTranslatedXMLFilePath().DIRECTORY_SEPARATOR.$this->_jobModel->getData('translated_file');

        $parsedData = $this->_xmlParser->load($filePath)->xmlToArray();

        $this->_parsedFileData = $parsedData['root']['data'];

        return $this;
    }

    public function saveTranslatedProductData()
    {
        foreach ($this->_parsedFileData as $key => $data){

            if(array_key_exists('attribute_translation_id',$this->_parsedFileData[$key]['_attribute']) && $this->_parsedFileData[$key]['_value']['value'] != $this->_parsedFileData[$key]['_attribute']['attribute_label'] )
            {

                try
                {
                    $data = $this->_attributeTranslationFactory->create()->load($this->_parsedFileData[$key]['_attribute']['attribute_translation_id']);

                    $data->addData(['is_imported'=>1,'translated_value'=>$this->_parsedFileData[$key]['_value']['value']]);

                    $data->save();

                }catch (\Exception $e)
                {
                    $this->_logger->error('error'.__FILE__.' '.__LINE__.' '.$e->getMessage(),array($e));
                }

            }

            if(array_key_exists('option_translation_id',$this->_parsedFileData[$key]['_attribute']) && $this->_parsedFileData[$key]['_value']['value'] != $this->_parsedFileData[$key]['_attribute']['attribute_label']){

                try
                {
                    $data = $this->_attributeOptionTranslationFactory->create()->load($this->_parsedFileData[$key]['_attribute']['option_translation_id']);

                    $data->addData(['is_imported'=>1,'translated_value'=>$this->_parsedFileData[$key]['_value']['value']]);

                    $data->save();

                }catch (\Exception $e)
                {
                    $this->_logger->error('error'.__FILE__.' '.__LINE__.' '.$e->getMessage(),array($e));
                }


            }

        }

        return $this;
    }

    public function publishTranslatedProductData()
    {
        $product_ids = $this->getProductIds($this->_jobModel->getId());

        $this->importTranslatedOptionValues($this->_jobModel->getId());

        foreach ($product_ids as $id)
        {
            $products = $this->_attributeTranslationCollection->create()
                ->addFieldToSelect(['attribute_id','original_value','translated_value'])
                ->addFieldToFilter( 'job_id',   array( 'eq' => $this->_jobModel->getId() ) )
                ->addFieldToFilter( 'entity_id',   array( 'eq' => $id ) )
                ->addFieldToFilter( 'is_label',   array( 'eq' => 0 ) );

            $labels = $this->_attributeTranslationCollection->create()
                ->addFieldToSelect(['attribute_id','original_value','translated_value'])
                ->addFieldToFilter( 'job_id',   array( 'eq' => $this->_jobModel->getId() ) )
                ->addFieldToFilter( 'entity_id',   array( 'eq' => $id ) )
                ->addFieldToFilter( 'is_label',   array( 'eq' => 1 ) )
                ->addFieldToFilter( 'translated_value',   array( 'notnull' => true ) );


            foreach ($labels->toArray()['items'] as $data){
                /** \Magento\Eav\ var $att */
                $att = $this->_attributeRepository->get(\Magento\Catalog\Model\Product::ENTITY,$data['attribute_id']);

                $new_labels = $att->getStoreLabels();

                $new_labels[$this->_jobModel->getTargetStoreId()] = $data['translated_value'];

                $att->setStoreLabels($new_labels)->save();
            }

            $attData = [];

            foreach ($products->toArray()['items'] as $data){

                $attData[$data['attribute_id']] = $data['translated_value'];
            }


            $this->_productAction->updateAttributes(array($id),$attData,$this->_jobModel->getTargetStoreId());
        }

        return $this;
    }

    protected function importTranslatedOptionValues($job_id)
    {

        $translatedOptionData = $this->getOptionValues($job_id);

        if(!empty($translatedOptionData))
        {
            $insertData = [];

            foreach ($translatedOptionData as $data){

                $insertData[] = ['option_id' => $data['option_id'], 'store_id' => $this->_jobModel->getTargetStoreId(), 'value' =>$data['translated_value']];
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
        $existingOptions = $this->_optionCollection->create()
            ->SetStoreFilter($this->_jobModel->getTargetStoreId())
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

            return $translatedOptionValues = [];
        }

    }

}
