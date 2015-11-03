<?php
/**
 * Created by PhpStorm.
 * User: WlliamZhao
 * Date: 30/09/15
 * Time: 4:37 PM
 */ 
class StrakerTranslations_EasyTranslationPlatform_Model_Job extends Mage_Core_Model_Abstract
{
    protected $_attributes = array();

    protected $_translateFilePath = '/var/straker/';

    protected function _construct()
    {
        $this->_init('strakertranslations_easytranslationplatform/job');
    }

    protected function addProductAttributes($productAttributeIds){

        foreach ($productAttributeIds as $productAttributeId) {
           $this->_attributes[] =
                Mage::getModel('strakertranslations_easytranslationplatform/product_attributes')
                ->setJobId($this->getId())
                ->setAttributeId((int) $productAttributeId)
                ->save();
        }
        return $this;
    }


    protected function addProductIds($productIds){

        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

        $query = 'INSERT INTO `straker_job_product`  (`product_id`, `job_id`) VALUES ';
        $queryVals = array();
        foreach ($productIds as $productId) {
            $queryVals[] = '(' . (int) $productId . ', ' . $this->getId() . ')';
        }

        $writeConnection->query( $query . implode(',', $queryVals));

        return $this;

    }

    protected function addProductTranslateOriginal($productAttributeId, $productCollection){


        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

        $query = 'INSERT INTO `straker_product_translate`  (`job_id`, `product_id`, `attribute_id`, `original`) VALUES ';
        $queryVals = array();
        $productAttributeCode = Mage::getModel('eav/entity_attribute')->load($productAttributeId)->getAttributeCode();
        foreach ($productCollection as $product) {
            $queryVals[] = '(' . $this->getId() . ', ' . $product->getId() . ', ' . $productAttributeId . ', \'' . addslashes($product->getData($productAttributeCode)). '\')';
        }

        $writeConnection->query( $query . implode(',', $queryVals));

        return $this;
    }

    public function addProducts($productAttributeIds,$productIds){

        if (!$this->getId()){
            if (!$this->getStoreId()){
                Mage::throwException('Error: Missing Store Id');
            }
            $this->setSourceStore(Mage::getStoreConfig('straker/general/source',$this->getStoreId()));
            $this->save();
        }

        $this->addProductAttributes($productAttributeIds);

        $countProducts = sizeof($productIds);
        $buffer = 1000;
        $_productIdSet = array();
        for ($i = 0; $i < $countProducts; $i = $i + $buffer){
            $_productIdSet[] = array_slice($productIds,$i,$buffer);
        }

        foreach ($_productIdSet as $_productIds) {
            $this->addProductIds($_productIds);
        }

        foreach ($_productIdSet as $_productIds) {

            $productCollection = Mage::getModel('catalog/product')->getCollection()->setStore($this->getSourceStore());

            foreach ($productAttributeIds as $productAttributeId) {

                $productAttributeCode = Mage::getModel('eav/entity_attribute')->load($productAttributeId)->getAttributeCode();
                $productCollection->addAttributeToSelect($productAttributeCode);
            }

            $productCollection->addFieldToFilter('entity_id', array('in'=> $_productIds));

            foreach ( $productAttributeIds as $productAttributeId){
                    $this->addProductTranslateOriginal($productAttributeId, $productCollection);
            }
        }
        return $this;
    }

    protected function _createProductTranslateFile() {

        $_xml = '<?xml version="1.0" encoding="utf-8"?><root>';

        $productTranslateCollection = Mage::getModel('strakertranslations_easytranslationplatform/product_translate')->getCollection();
        $productTranslateCollection->addFieldToFilter('job_id',$this->getId());

        $attributeFrontLabel = array();

        foreach ($productTranslateCollection as $productTranslate ){
            if (!isset($attributeFrontLabel[$productTranslate->getAttributeId()])){
                $attributeFrontLabel[$productTranslate->getAttributeId()] =
                  Mage::getModel('eav/entity_attribute')->load($productTranslate->getAttributeId())->getFrontendLabel();
            }

            $_xml .= '<data name="' .$this->getTypeId(). '_' . $this->getStoreId().'_'. $productTranslate->getAttributeId().'_'. $productTranslate->getProductId().'" ' ;
            $_xml .= 'content_context="' . $attributeFrontLabel[$productTranslate->getAttributeId()] . '" ';
            $_xml .= 'content_context_url="'.Mage::getStoreConfig('web/unsecure/base_link_url',$this->getStoreId()).'catalog/category/view/id/'.$productTranslate->getProductId().'" ';
            $_xml .= 'content_id="'. $productTranslate->getId() .'">';
            $_xml .= '<value><![CDATA['.$productTranslate->getOriginal().']]></value></data>';
        }
        $_xml .='</root>';

        file_put_contents(MAGENTO_ROOT.$this->_translateFilePath.'job'.$this->getId().'.xml',$_xml);
        $this->setSourceFile('job'.$this->getId().'.xml')->save() ;

        return $this;
    }

    protected function _summitJob(){

        $request = array();

        $request['title'] = $this->getTitle();
        $request['sl']    = $this->getSl();
        $request['tl']    = $this->getTl();

        $filePath = MAGENTO_ROOT.$this->_translateFilePath.$this->getSourceFile();

        $request['source_file']    = function_exists('curl_file_create') ?  curl_file_create($filePath) :'@'.$filePath;
        $request['callback_uri']    = Mage::getStoreConfig('web/unsecure/base_link_url',$this->getStoreId()) . 'straker/callback';
        $request['token']    = $this->getId();

        $api = $this->_getApi();
        $response = $api->callTranslate($request);
        if($response->job_key) {
            $this->setStatusId(2)
                ->setJobKey($response->job_key)
                ->setTjNumber($response->tj_number)
                ->save();
            $this->setLastStatus(1);
        }
        else{
            $this->setLastStatus(0);
            $message = $response->magentoMessage?$response->magentoMessage:'Unknown Error.';
            $this->setLastMessage($message);
        }
        return $this;

    }

    public function submitProducts($productAttributeIds,$productIds){

        //product
        $this->setTypeId(1);
        $this->addProducts($productAttributeIds, $productIds)
        ->_createProductTranslateFile()
        ->_summitJob();
        return $this;
    }

    public function updateQuote(){

        if ($this->getJobKey()){
            $request = array();
            $request['job_key'] = $this->getJobKey();
            $api = $this->_getApi();
            $response = $api->getQuote($request);
            $quote = json_encode($response->quote);

            if($quote && $quote != $this->getQuote()){
                $this->setQuote($quote)->save();
                return true;
            }
        }
        return false;

    }

    public function updateTranslation(){

        if ($this->getJobKey()){
            $request = array();
            $request['job_key'] = $this->getJobKey();
            $api = $this->_getApi();
            $response = $api->getTranslation($request);
            if ($response->job) {
                foreach ($response->job as $job) {

                    if ($job->token == $this->getId()) {
                        $jobStatusId = $this->_getStatusId($job->status);

                        if ($jobStatusId && $job->status <> $this->getStatusName()) {
                            $this->setStatusId($jobStatusId)->save();
                        }

                        if ($job->tj_number && $job->tj_number <> $this->getTjNumber()) {
                            $this->setTjNumber($job->tj_number)->save();
                        }

                        if ($job->workflow && $job->workflow <> $this->getWorkFlow()) {
                            $this->setWorkFlow($job->workflow)->save();
                        }
                    }

                    foreach ($job->translated_file as $file) {

                        if ($file->download_url && !$this->getDownloadUrl()) {
                            $this->setDownloadUrl($file->download_url)->save();

                            $this->_importTranslation();
                        }
                    }
                }
                $this->setLastStatus(1);
            }
            else{
                $this->setLastStatus(0);
                $message = $response->magentoMessage?$this->setLastMessage($response->magentoMessage):'Unknown Error.';
                $this->setLastMessage($message);
            }

        }
        return false;

    }

    protected function _getApi(){

        return Mage::getModel('strakertranslations_easytranslationplatform/api',array('store'=>$this->getStoreId()));
    }

    protected function _getStatusId($statusName) {

        return Mage::getModel('strakertranslations_easytranslationplatform/job_status')->load($statusName,'status_name')->getId();
    }

    protected function _importTranslation() {

        $xml = $this->_getApi()->getTranslatedFile($this->getDownloadUrl());

        file_put_contents(MAGENTO_ROOT.$this->_translateFilePath.'translated_job'.$this->getId().'.xml',$xml, LOCK_EX);

        $data = simplexml_load_string($xml);

        foreach ($data->children() as $_translation) {

            $_productTranslationId = (string) $_translation->attributes()->content_id;
            $_productTranslation = Mage::getModel('strakertranslations_easytranslationplatform/product_translate')->load($_productTranslationId);
            $_productTranslation->setTranslate((string) $_translation->value);
            $_productTranslation->save();
            $_productTranslation->clearInstance();
        }

        return $this->setDownloadedVersion(1)->save();

    }

    public function updatePayment(){

        if ($this->getJobKey()){
            $request = array();
            $request['job_key'] = $this->getJobKey();
            $api = $this->_getApi();
            $response = $api->getPayment($request);

            if(!empty($response) && $response->status == "Paid"){ ///////waiting for payment api
                $this->setPaymentStatus(1)->save();
                return true;
            }
        }
        return false;
    }

    public function applyTranslation( $productIds = array()) {

        $collection = Mage::getModel('strakertranslations_easytranslationplatform/product_translate')->getCollection()->addFieldToFilter('job_id',$this->getId());

        if ($productIds) {
            $collection->addFieldToFilter('product_id', array('in' => $productIds));
        }

        foreach ($collection as $translation) {

            $translation->setStoreId($this->getStoreId())->importTranslation();
        }

        return true;
    }

    public function submitSupport(array $data){

        $res = $this->_getApi()->callSupport($data);

        return $res->success;

    }

    public function checkAndCreateFolder(){

        $ioAdapter = new Varien_Io_File();
        try {
            $ioAdapter->checkAndCreateFolder(Mage::getBaseDir('var').DS.'straker');
        }
        catch (Exception $e) {
            Mage::throwException($e->getMessage());
        }

        return $this;

    }


}