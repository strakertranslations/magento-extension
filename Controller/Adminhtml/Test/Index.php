<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Test;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Framework\Xml\Parser;

use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;

use Magento\Framework\App\ResourceConnection;


use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Helper\XmlHelper;

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

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        JsonFactory $jsonFactory,
        Collection $attCollection,
        ConfigHelper $configHelper,
        XmlHelper $xmlHelper,
        Parser $xmlParser,
        AttributeTranslationFactory $attributeTranslationFactory,
        AttributeOptionTranslationFactory $attributeOptionTranslationFactory,
        AttributeTranslationCollection $attributeTranslationCollection,
        AttributeOptionTranslationCollection $attributeOptionTranslationCollection,
        ProductAction $productFactory,
        AttributeRepository $attributeRepository,
        ResourceConnection $connection,
        OptionFactory $optionFactory
    )
    {
        $this->_attributeCollection = $attCollection;
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

        return parent::__construct($context);
    }

    public function execute()
    {

        $filePath = str_replace('job-file','translated-file',$this->_xmlHelper->getXmlFilePath());

        $parsedArray = $this->_xmlParser->load($filePath.'/straker_job_79_1470092827.xml')->xmlToArray();

        $parsedData = $parsedArray['root']['data'];

        foreach ($parsedData as $key => $data){

            if(array_key_exists('attribute_translation_id',$parsedData[$key]['_attribute']) && $parsedData[$key]['_value']['value'] != $parsedData[$key]['_attribute']['attribute_label'] )
            {
                $data = $this->_attributeTranslationFactory->create()->load($parsedData[$key]['_attribute']['attribute_translation_id']);

                $data->addData(['is_imported'=>1,'translated_value'=>$parsedData[$key]['_value']['value']]);

                $data->save();

            }

            if(array_key_exists('option_translation_id',$parsedData[$key]['_attribute']) && $parsedData[$key]['_value']['value'] != $parsedData[$key]['_attribute']['attribute_label']){

                $data = $this->_attributeOptionTranslationFactory->create()->load($parsedData[$key]['_attribute']['option_translation_id']);

                $data->addData(['is_imported'=>1,'translated_value'=>$parsedData[$key]['_value']['value']]);

                $data->save();

            }


        }

        $this->importTranslatedProducts();


    }

    public function importTranslatedProducts()
    {

        $products = $this->_attributeTranslationCollection->create()
            ->addFieldToSelect(['attribute_id','translated_value'])
            ->addFieldToFilter( 'job_id',   array( 'eq' => 79 ) )
            ->addFieldToFilter( 'entity_id',   array( 'eq' => 2045 ) )
            ->addFieldToFilter( 'is_label',   array( 'eq' => 0 ) );

        $labels = $this->_attributeTranslationCollection->create()
        ->addFieldToSelect(['attribute_id','translated_value'])
        ->addFieldToFilter( 'job_id',   array( 'eq' => 79 ) )
        ->addFieldToFilter( 'entity_id',   array( 'eq' => 2045 ) )
        ->addFieldToFilter( 'is_label',   array( 'eq' => 1 ) );

        $options = $this->_attributeTranslationCollection->create()
            ->addFieldToSelect(['attribute_id','translated_value'])
            ->addFieldToFilter( 'is_label',   array( 'eq' => 1 ) )
            ->addFieldToFilter( 'entity_id',   array( 'eq' => 2045 ) )
            ->addFieldToFilter( 'has_option',   array( 'eq' => 1 ) );

        var_dump($options->getData());

        $this->_updateAttributeOptionValues();


        foreach ($labels->toArray()['items'] as $data){

            $att = $this->_attributeRepository->get(\Magento\Catalog\Model\Product::ENTITY,$data['attribute_id']);

            $new_labels = $att->getStoreLabels();

            $new_labels['2'] = $data['translated_value'];

            $att->setStoreLabels($new_labels)->save();
        }


        foreach ($products as $data){

            $attData[$data['attribute_id']] = $data['translated_value'];
        }


        $this->_productFactory->updateAttributes(array('2045'),$attData,2);


    }

    protected function importTranslatedOptionValues()
    {

        $connection = $this->_resourceConnection->getConnection();

        $table = $this->_resourceConnection->getTableName('eav_attribute_option_value');

        $data[] = ['option_id' => '152', 'store_id' => '2', 'value' =>'test122'];
        $data[] = ['option_id' => '153', 'store_id' => '2', 'value' =>'test122'];

        $connection->insertMultiple($table, $data);


    }


}
