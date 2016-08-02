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
        AttributeRepository $attributeRepository
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

        $this->updateAttributes();

        exit;

        var_dump($parsedData);

        exit;

        return $parsedArray['xmlNodeName'];


    }

    public function updateAttributes()
    {

        $attData = [];

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

        //$options = $this->_attributeOptionTranslationCollection->create()
           // ->addFieldToFilter( 'attribute_translation_id',   array( 'eq' => 1074 ) );

        //var_dump($products->getData());
        //var_dump($labels->getData());
        //var_dump($options->getData());

        foreach ($options as $data)
        {
            $optionValues = $this->_attributeOptionTranslationCollection->create()
             ->addFieldToFilter( 'attribute_translation_id',   array( 'eq' => $data->getId() ) )->toArray()['items'];

            $att = $this->_attributeRepository->get(\Magento\Catalog\Model\Product::ENTITY,$data['attribute_id']);

            foreach ($optionValues as $value ){

                $transValue[0] = $value['original_value'];
                $transValue[1] = $value['original_value'];
                $transValue[2] = $value['translated_value'];

                $att->setData('option',array('value'=>array(
                    $value['option_id']=>$transValue)));

                $att->save();
            }

        }

        var_dump($optionValues);

        exit;



        foreach ($labels->toArray()['items'] as $data){

            $att = $this->_attributeRepository->get(\Magento\Catalog\Model\Product::ENTITY,$data['attribute_id']);

            $new_labels = $att->getStoreLabels();

            $new_labels['2'] = $data['translated_value'];

            $att->setStoreLabels($new_labels)->save();
        }


        foreach ($products as $data){

            $attData[$data['attribute_id']] = $data['translated_value'];
        }

//        var_dump($attData);
//
//        var_dump($products->getData());
//
//        exit;

        //store 2

        //$productFactory = $this->_productFactory->create();

        $this->_productFactory->updateAttributes(array('2045'),$attData,2);

        exit;

    }


}
