<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\AttributeFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\Entity\StoreFactory;
use Magento\Framework\DataObject;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Straker\EasyTranslationPlatform\Api\JobRepositoryInterface;
use Straker\EasyTranslationPlatform\Model\JobFactory;
use Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation\CollectionFactory as AttributeTranslationCollection;

class JobAttributeLabel extends AbstractRenderer
{

    protected $_attributeRepository;
    protected $_jobFactory;
    protected $_productFactory;
    protected $_storeFactory;
    protected $_attributeTranslationCollectionFactory;
    /** @var \Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation\Collection AttributeTranslationCollection  */
    protected $_jobAttributeCollection;

    public function __construct(
        Context $context,
        JobFactory $jobFactory,
        ProductFactory $productFactory,
        StoreFactory $storeFactory,
        AttributeRepositoryInterface $eavAttributeRepository,
        AttributeTranslationCollection $attributeTranslationCollection
    )
    {
        $this->_attributeRepository = $eavAttributeRepository;
        $this->_jobFactory = $jobFactory;
        $this->_productFactory= $productFactory;
        $this->_storeFactory = $storeFactory;
        $this->_attributeTranslationCollectionFactory = $attributeTranslationCollection;
        parent::__construct( $context );
    }


    function render(DataObject $row)
    {

//        var_dump( $row->getData() );
        $isLabel = $row->getData('is_label');
        $jobId = $row->getData('job_id');
        $strakerJob = $this->_jobFactory->create()->load( $jobId );
        $this->_jobAttributeCollection = $this->_attributeTranslationCollectionFactory->create()
            ->addFieldToFilter('job_id', ['eq' => $jobId] )
            ->addfieldtofilter('is_label', ['eq' => true ]);


//        var_dump( $this->_jobAttributeCollection->getSelect()->__toString());
//        var_dump( $this->_jobAttributeCollection->getData() );exit();
//        /** @var \Magento\Catalog\Model\Product $product */
//        $product = $this->_productFactory->create()->load( $row->getData('entity_id'));

//        /** \Magento\Eav\Model\Attribute $attribute */
//        $attribute = $this->_attributeRepository->get(
//            ProductAttributeInterface::ENTITY_TYPE_CODE,
//            $row->getData('attribute_id')
//        );

//        /** @var  \Magento\Eav\Model\Entity\Store $sourceStore */
//        $sourceStore = $this->_storeFactory->create()->load($strakerJob->getSourceStoreId());

        $attrLabel = '';


        if( strcasecmp($isLabel, 'yes') === 0 ){
            $attrLabel = $row->getData('original_value');
        }else{
            $attrLabel = $this->_getFieldLabel( $row->getData('attribute_id'));
            $attrLabel = empty( $attrLabel ) ? $this->_getFrontendLabel() : $attrLabel;
        }


        $row->setData('label', $attrLabel );
//        var_dump( $row->getData() ); exit();
        return parent::render($row);
    }

    protected function _getFieldLabel( $attributeId ){
        $data = $this->_jobAttributeCollection->addFieldToFilter( 'attribute_id', [ 'eq' => $attributeId ])->getData();
        return count($data) > 0 ? $data['original_value'] : null;
    }

    protected function _getFrontendLabel(){
        //TODO: Get Frontend Label
        return 'Frontend Label';
    }
}
