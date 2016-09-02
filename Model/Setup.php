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
use Straker\EasyTranslationPlatform\Model\Error;
use \Straker\EasyTranslationPlatform\Helper\Data;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Store\Model\StoreManagerInterface;


class Setup extends AbstractModel implements SetupInterface
{
    protected $_configModel;
    protected $_storeManager;
    protected $_errorManager;
    protected $_resourceConnection;
    protected $_dataHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        Config $config,
        StoreManagerInterface $storeManagerInterface,
        ResourceConnection $resourceConnection,
        Data $dataHelper,
        Error $error
    ) {
        $this->_configModel = $config;
        $this->_storeManager = $storeManagerInterface;
        $this->_resourceConnection = $resourceConnection;
        $this->_dataHelper = $dataHelper;
        $this->_errorManager = $error;
        parent::__construct($context, $registry);
    }

    public function saveClientData($data)
    {

        try {

            $this->_configModel->SaveConfig('straker/general/name', $data['first_name'] . ' ' . $data['last_name'], 'default', 0);

            $this->_configModel->SaveConfig('straker/general/email', $data['email'], 'default', 0);

            $this->_configModel->SaveConfig('straker/general/url', $data['url'], 'default', 0);

            $this->_errorManager->_error = false;

            return $this->_errorManager;

        } catch (Exception $e) {

            $this->_logger->error('error' . __FILE__ . ' ' . __LINE__, array($e));

            $this->_errorManager->_errorMessage = 'There was an error saving your details';

            $this->_errorManager->_error = true;

            return $this->_errorManager;

        }
    }

    public function saveAppKey($appKey)
    {

        try {

            $this->_configModel->SaveConfig('straker/general/application_key', $appKey, 'default', 0);

            $this->_errorManager->_error = false;

            return $this->_errorManager;

        } catch (Exception $e) {

            $this->_logger->error('error' . __FILE__ . ' ' . __LINE__, array($e));

            $this->_errorManager->_errorMessage = 'There was an error saving your application key';

            $this->_errorManager->_error = true;

            return $this->_errorManager;

        }
    }

    public function saveAccessToken($accessToken)
    {

        try {

            $this->_configModel->SaveConfig('straker/general/access_token', $accessToken, 'default', 0);

            $this->_errorManager->_error = false;

            return $this->_errorManager;

        } catch (Exception $e) {

            $this->_logger->error('error' . __FILE__ . ' ' . __LINE__, array($e));

            $this->_errorManager->_errorMessage = 'There was an error saving your access token';

            $this->_errorManager->_error = true;

            return $this->_errorManager;

        }
    }

    public function saveStoreSetup($scopeId, $source_store, $source_language, $destination_language)
    {

        try {

            $this->_configModel->saveConfig('straker/general/source_store', $source_store, ScopeInterface::SCOPE_STORES, $scopeId);
            $this->_configModel->SaveConfig('straker/general/source_language', $source_language, ScopeInterface::SCOPE_STORES, $scopeId);
            $this->_configModel->SaveConfig('straker/general/destination_language', $destination_language, ScopeInterface::SCOPE_STORES, $scopeId);

            $this->_errorManager->_error = false;

            return $this->_errorManager;

        } catch (Exception $e) {

            $this->_logger->error('error' . __FILE__ . ' ' . __LINE__, array($e));

            $this->_errorManager->_errorMessage = 'There was an error saving Language Pairs';

            $this->_errorManager->_error = true;

            return $this->_errorManager;
        }
    }

    public function saveProductAttributes($attributes)
    {

        try {

            if (!empty($attributes['custom'])) {

                $this->_configModel->SaveConfig('straker_attribute/settings/custom', $attributes['custom'], 'default', 0);
            }

            if (!empty($attributes['default'])) {

                $this->_configModel->SaveConfig('straker_attribute/settings/default', $attributes['default'], 'default', 0);
            }

            $this->_errorManager->_error = false;

            return $this->_errorManager;

        } catch (Exception $e) {

            $this->_logger->error('error' . __FILE__ . ' ' . __LINE__, array($e));

            $this->_errorManager->_errorMessage = 'There was an error saving Product Attributes';

            $this->_errorManager->_error = true;

            return $this->_errorManager;
        }
    }

    public function clearTranslations($storeId = null)
    {
        $result = ['Success' => false, 'Message' => '', 'Count' => 0];
        $deleteCount = 0;
        $connection = $this->_getConnection();

        try {
            foreach ($this->_dataHelper->getMagentoDataTableArray() as $rawTableName) {
                $table = $connection->getTableName($rawTableName);

                if( strcasecmp($rawTableName, 'cms_page') === 0 || strcasecmp($rawTableName, 'cms_block') === 0 ){
                    continue;
                }

                if ($connection->isTableExists($table)) {

                    if( strcasecmp($rawTableName, 'cms_page_store') === 0 || strcasecmp( $rawTableName, 'cms_block_store') === 0 ){
                        $idField = ( strcasecmp($rawTableName, 'cms_page_store') === 0 ) ? 'page_id' : 'block_id';

                        $sql = $this->_resourceConnection->getConnection()->select()->from( $table , [ $idField ] )->where('store_id != ?', Store::DEFAULT_STORE_ID);
                        $return = $this->_resourceConnection->getConnection()->query($sql);
                        $ids = array_column( $return->fetchAll(),$idField );

                        $rawTargetTable = strcasecmp($idField, 'page_id') === 0 ? 'cms_page' : 'cms_block';
                        $targetTable = $connection->getTableName($rawTargetTable);

                        if( $connection->isTableExists( $targetTable )){
                            $where = [$idField. ' IN(?)' => $ids ];
                            $deleteCount = $connection->delete($targetTable, $where);
                            $where = ['entity_id IN(?)' => $ids, 'store_id = ?' => ( empty($storeId) ? 1: $storeId)];
                            $deleteCount += $connection->delete($connection->getTableName('url_rewrite'), $where);
                        }
                    }

                    if (empty($storeId)) {
                        //CLEAR FOR ALL STORES
                        $where = ['store_id != ?' => Store::DEFAULT_STORE_ID ];
                        $deleteCount += $connection->delete($table, $where );
                    } else {
                        //CLEAR FOR A SINGLE STORE
                        $where = ['store_id = ?' => $storeId ];
                        $deleteCount += $connection->delete($table, $where);
                    }
                }
            }
            $result['Success'] = true;
            $result['Count'] = $deleteCount;
        } catch (Exception $e) {
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
            foreach ($tables as $table) {
                $table = $connection->getTableName($table);
                if ($connection->isTableExists($table)) {
                    $deleteCount = $connection->delete($table);
                    $deleteCount++;
                }
            }

            $this->clearDefaultAttributeSettings();
            $result['Success'] = true;
            $result['Count'] = $deleteCount;
        } catch (Exception $e) {
            $result['Message'] = $e->getMessage();
            throw new Exception($result['Message']);
        }

        return $result;


    }

    protected function clearDefaultAttributeSettings(){
        $this->_configModel->saveConfig('straker_attribute/settings/default', '', 'default', 0);
        $this->_configModel->saveConfig('straker_attribute/settings/custom',  '', 'default', 0);
        $this->_configModel->saveConfig('straker_attribute/settings/category','', 'default', 0);
    }

    protected function _getConnection()
    {
        return $this->_resourceConnection->getConnection();
    }
}