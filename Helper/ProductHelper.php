<?php

namespace Straker\EasyTranslationPlatform\Helper;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Data\Collection;

class ProductHelper extends AbstractHelper
{

    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $_collectionFactory;
    protected $_entityTypeId;

    protected $_translatableBackendType = array (
        'varchar', 'text'
    );

    protected $_translatableFrontendInputType = array(
        'select', 'text','multiline', 'textarea', 'multiselect'
    );

    protected $_translatableFrontendLabel = array(
        'name', 'description', 'meta title', 'meta keywords', 'meta description', 'short description'
    );


    /**
     * ProductHelper constructor.
     * @param Context $context
     * @param ProductFactory $productFactory
     * @param CollectionFactory $collectionFactory
     * @param Config $eavConfig
     */
    public function __construct(
        Context $context,
        ProductFactory $productFactory,
        CollectionFactory $collectionFactory,
        Config $eavConfig
    ) {
        $this->_productFactory = $productFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_entityTypeId =  $eavConfig->getEntityType( ProductAttributeInterface::ENTITY_TYPE_CODE )->getEntityTypeId();

        parent::__construct($context);
    }


    /**
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    public function getDefaultAttributes()
    {
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $collection */
        $collection = $this->getAttributes()
            ->addFieldToFilter( 'frontend_label',  array( 'in' => $this->_translatableFrontendLabel ))
            ->addFieldToFilter( 'is_user_defined', array( 'eq' => 0 ));
        return $collection;
    }

    public function getCustomAttributes()
    {
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $collection */
        $collection = $this->getAttributes()->addFieldToFilter( 'is_user_defined', array( 'eq' => 1 ));
        return $collection;
    }

    public function getAttributes(){
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $collection */
        $collection = $this->_collectionFactory->create()->addVisibleFilter();
        $collection->setEntityTypeFilter( $this->_entityTypeId )
            ->setFrontendInputTypeFilter( array( 'in' => $this->_translatableFrontendInputType ) )
            ->addFieldToFilter( 'backend_type',   array( 'in' => $this->_translatableBackendType ) )
            ->setOrder( 'frontend_label', Collection::SORT_ORDER_ASC  );
        return $collection;
    }
}