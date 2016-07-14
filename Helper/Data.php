<?php

namespace Straker\EasyTranslationPlatform\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $storeManager;

    protected $_productTables = array(
        'catalog_product_entity_varchar',
        'catalog_product_entity_text',
        'catalog_category_entity_varchar',
        'catalog_category_entity_text'
    );

    const BACKUP_TABLE_SUFFIX = '_back';


    /**
     * @param \Magento\Framework\App\Helper\Context   $context
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_backendUrl = $backendUrl;
        $this->storeManager = $storeManager;
    }

    /**
     * get products tab Url in admin
     * @return string
     */
    public function getEasyTranslationPlatformUrl()
    {
        return $this->_backendUrl->getUrl('EasyTranslationPlatform/Jobs/products', ['_current' => true]);
    }

    public function getProductTableArray(){
        return $this->_productTables;
    }

    public function getBackupTableNames( $tableName ){
        return $tableName.self::BACKUP_TABLE_SUFFIX;
    }
}
