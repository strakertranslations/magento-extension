<?php

namespace Straker\EasyTranslationPlatform\Helper;

use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\ResourceModel\Category\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Eav\Model\Config;

class CategoryHelper extends AbstractHelper
{
    protected $_translatableFrontendLabel = array(
        'name','description','meta_title','meta_keywords','meta_description'
    );

    protected $_attributeCollectionFactory;
    protected $_entityTypeId;

    public function __construct(
        Context $context,
        Config $eavConfig,
        AttributeCollectionFactory $attributeCollectionFactory
    ) {
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
        $this->_entityTypeId =  $eavConfig->getEntityType(CategoryAttributeInterface::ENTITY_TYPE_CODE)->getEntityTypeId();
        parent::__construct($context);
    }

    public function getAttributes(){
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection $collection */
        $collection = $this->_attributeCollectionFactory->create()
            ->addFieldToFilter( 'attribute_code',   array( 'in' => $this->_translatableFrontendLabel ) );
        return $collection;
    }
}