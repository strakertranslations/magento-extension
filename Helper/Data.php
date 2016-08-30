<?php

namespace Straker\EasyTranslationPlatform\Helper;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var UrlInterface
     */
    protected $_backendUrl;

    /**
     * @var StoreManagerInterface $storeManager
     */
    protected $storeManager;

    protected $_magentoDataTables = array(
        'catalog_product_entity_varchar',
        'catalog_product_entity_text',
        'catalog_category_entity_varchar',
        'catalog_category_entity_text',
        'eav_attribute_option_value',
        'catalog_product_super_attribute_label'
    );

    const BACKUP_TABLE_SUFFIX = '_back';


    /**
     * @param Context $context
     * @param UrlInterface $backendUrl
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        UrlInterface $backendUrl,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_backendUrl = $backendUrl;
        $this->storeManager = $storeManager;
    }

    /**
     * get products tab Url in admin
     * @return string
     */
    public function getProductUrl()
    {
        return $this->_backendUrl->getUrl('EasyTranslationPlatform/Jobs/products', ['_current' => true]);
    }

    public function getPagesUrl()
    {
        return $this->_backendUrl->getUrl('EasyTranslationPlatform/Jobs/pages', ['_current' => true]);
    }

    public function getMagentoDataTableArray(){
        return $this->_magentoDataTables;
    }

    public function getBackupTableNames( $tableName ){
        return $tableName.self::BACKUP_TABLE_SUFFIX;
    }

    public function getUrl($path = '/', $parameters=[])
    {
        return $this->_backendUrl->getUrl($path,$parameters);
    }
}
