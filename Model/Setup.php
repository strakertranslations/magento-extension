<?php

namespace Straker\EasyTranslationPlatform\Model;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\Data\StoreConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Straker\EasyTranslationPlatform\Api\Data\SetupInterface;
use \Straker\EasyTranslationPlatform\Helper\Data;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Store\Model\StoreManagerInterface;

class Setup extends AbstractModel implements SetupInterface
{
    protected $_configModel;
    protected $_storeManager;
    protected $_resourceConnection;
    protected $_dataHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        Config $config,
        StoreManagerInterface $storeManagerInterface,
        ResourceConnection $resourceConnection,
        Data $dataHelper
    ) {
        $this->_configModel = $config;
        $this->_storeManager = $storeManagerInterface;
        $this->_resourceConnection = $resourceConnection;
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $registry);
    }

    public function saveClientData($data)
    {
        $this->_configModel->SaveConfig('straker/general/name', $data['first_name'] . ' ' . $data['last_name'], 'default', 0);

        $this->_configModel->SaveConfig('straker/general/email', $data['email'], 'default', 0);

        $this->_configModel->SaveConfig('straker/general/url', $data['url'], 'default', 0);

        return $this->_configModel;
    }

    public function saveAppKey($appKey)
    {

        $this->_configModel->SaveConfig('straker/general/application_key', $appKey, 'default', 0);

        return $this->_configModel;
    }

    public function saveAccessToken($accessToken)
    {

        $this->_configModel->SaveConfig('straker/general/access_token', $accessToken, 'default', 0);

        return $this->_configModel;
    }

    public function saveStoreSetup($scopeId, $source_store, $source_language, $destination_language)
    {

        $this->_configModel->SaveConfig('straker/general/source_store', $source_store, ScopeInterface::SCOPE_STORES, $scopeId);
        $this->_configModel->SaveConfig('straker/general/source_language', $source_language, ScopeInterface::SCOPE_STORES, $scopeId);
        $this->_configModel->SaveConfig('straker/general/destination_language', $destination_language, ScopeInterface::SCOPE_STORES, $scopeId);

        return $this->_configModel;
    }

    public function saveAttributes($attributes)
    {

        if (!empty($attributes['custom'])) {
            $this->_configModel->SaveConfig('straker_config/attribute/product_custom', $attributes['custom'], 'default', 0);
        }

        if (!empty($attributes['default'])) {
            $this->_configModel->SaveConfig('straker_config/attribute/product_default', $attributes['default'], 'default', 0);
        }

        if (!empty($attributes['category'])) {
            $this->_configModel->SaveConfig('straker_config/attribute/category', $attributes['category'], 'default', 0);
        }

        return $this->_configModel;
    }

    public function clearTranslations($storeId = null)
    {
        $result = ['Success' => false, 'Message' => '', 'Count' => 0];
        $deleteCount = 0;
        $connection = $this->_getConnection();
        try {
            $connection->beginTransaction();
            foreach ($this->_dataHelper->getMagentoDataTableArray() as $rawTableName) {
                $table = $this->_resourceConnection->getTableName($rawTableName);

                if (strcasecmp($rawTableName, 'cms_page') === 0 ||
                    strcasecmp($rawTableName, 'cms_block') === 0 ||
                    strcasecmp($rawTableName, 'catalog_url_rewrite_product_category') === 0) {
                    continue;
                }

                if ($connection->isTableExists($table)) {
                    if (strcasecmp($rawTableName, 'cms_page_store') === 0 || strcasecmp($rawTableName, 'cms_block_store') === 0) {
                        $idField = ( strcasecmp($rawTableName, 'cms_page_store') === 0 ) ? 'page_id' : 'block_id';

                        $select = $connection
                            ->select()
                            ->from($table, [ $idField ])
                            ->where('store_id != ?', Store::DEFAULT_STORE_ID);
                        $return = $select->query()->fetchAll();
                        $ids = array_column($return, $idField);

                        $rawTargetTable = strcasecmp($idField, 'page_id') === 0 ? 'cms_page' : 'cms_block';
                        $targetTable = $this->_resourceConnection->getTableName($rawTargetTable);

                        if ($connection->isTableExists($targetTable)) {
                            $where = [$idField. ' IN(?)' => $ids ];
                            $deleteCount = $connection->delete($targetTable, $where);
                        }
                    }

                    if (is_null($storeId)) {
                        //CLEAR FOR ALL STORES
                        if (strcasecmp($rawTableName, 'url_rewrite') === 0) {
                            $select = $connection->select()->from($table, ['url_rewrite_id'])->where('store_id != ?', 1);
                            $urlRewriteIds = [];
                            $return = $select->query()->fetchAll();
                            if (count($return) > 0) {
                                $urlRewriteIds = array_column($return, 'url_rewrite_id');
                            }
                            $where = [ 'store_id != ?' => 1];
                            $deleteCount += $connection->delete($table, $where);
                            $urlRewriteProductCategoryTable = $this->_resourceConnection->getTableName('catalog_url_rewrite_product_category');
                            if ($connection->isTableExists($urlRewriteProductCategoryTable)  && count($urlRewriteIds) > 0) {
                                $where = ['url_rewrite_id IN(?)'=> $urlRewriteIds ];
                                $deleteCount += $connection->delete($urlRewriteProductCategoryTable, $where);
                            }
                        } else {
                            $where = ['store_id != ?' => Store::DEFAULT_STORE_ID ];
                            $deleteCount += $connection->delete($table, $where);
                        }
                    } else {
                        //CLEAR FOR A SINGLE STORE
                        $where = ['store_id = ?' => $storeId ];
                        $deleteCount += $connection->delete($table, $where);
                    }
                }
            }
            $result['Success'] = true;
            $result['Count'] = $deleteCount;
            $connection->commit();
        } catch (Exception $e) {
            $connection->rollBack();
            $result['Message'] = $e->getMessage();
            throw new Exception($result['Message']);
        }

        return $result;
    }

    public function clearStrakerData()
    {
        $tables = [
            'straker_attribute_option_translation',
            'straker_attribute_translation',
            'straker_job'
        ];

        $result = ['Success' => false, 'Message' => '', 'Count' => 0];
        $deleteCount = 0;
        $connection = $this->_getConnection();

        try {
            $connection->beginTransaction();
            foreach ($tables as $table) {
                $table = $this->_resourceConnection->getTableName($table);
                if ($connection->isTableExists($table)) {
                    $deleteCount = $connection->delete($table);
                    $deleteCount++;
                }
            }

            $this->clearDefaultAttributeSettings();
            $result['Success'] = true;
            $result['Count'] = $deleteCount;
            $connection->commit();
        } catch (Exception $e) {
            $connection->rollBack();
            $result['Message'] = $e->getMessage();
            throw new Exception($result['Message']);
        }

        return $result;
    }

    protected function clearDefaultAttributeSettings()
    {

        $this->_configModel->saveConfig('straker_config/attribute/product_custom', '', 'default', 0);
        $this->_configModel->saveConfig('straker_config/attribute/product_default', '', 'default', 0);
        $this->_configModel->saveConfig('straker_config/attribute/category', '', 'default', 0);

        return  $this->_configModel;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function _getConnection()
    {
        return $this->_resourceConnection->getConnection();
    }

    public function deleteSandboxSetting()
    {

        $this->_configModel->deleteConfig('straker_config/env/sandbox', 'default', 0);

        return  $this->_configModel;
    }
}
