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
    /** @var $conn Varien_Db_Adapter_Interface */
    protected $conn;

    protected function _construct()
    {
        $this->_init('strakertranslations_easytranslationplatform/job');
        /** @var $conn Varien_Db_Adapter_Interface */
        $this->conn = $this->getWriteAdapter();
    }

    protected function addProductAttributes($productAttributeIds)
    {
        foreach ($productAttributeIds as $productAttributeId) {
            $this->_attributes[] =
                Mage::getModel('strakertranslations_easytranslationplatform/product_attributes')
                    ->setJobId($this->getId())
                    ->setAttributeId((int)$productAttributeId)
                    ->save();
        }
        return $this;
    }

    protected function addProductIds($productIds)
    {
        $data = array();
        $jobProductTableName = $this->getResource()->getTable('strakertranslations_easytranslationplatform/job_product');

        foreach ($productIds as $productId) {
            $data[] = [
                'product_id'    => $productId,
                'job_id'        => $this->getId()
            ];
        }

        $this->conn->insertMultiple($jobProductTableName, $data);
//        $model = Mage::getModel('strakertranslations_easytranslationplatform/job_product');
//        foreach ($productIds as $productId) {
//            $model->setProductId($productId);
//            $model->setJobId($this->getId());
//            $model->save();
//            $model->unsetData();
//        }
        return $this;
    }

    protected function addProductTranslateOriginal($productAttributeId, $productCollection)
    {
        $data = array();
        $productTranslateTableName = $this->getResource()->getTable('strakertranslations_easytranslationplatform/product_translate');
        $productAttributeCode = Mage::getModel('eav/entity_attribute')->load($productAttributeId)->getAttributeCode();
        //        $model = Mage::getModel('strakertranslations_easytranslationplatform/product_translate');
        foreach ($productCollection as $product) {
            if($product->getData($productAttributeCode)){
                $data[] = [
                    'job_id'        => $this->getId(),
                    'product_id'    => $product->getId(),
                    'attribute_id'  => $productAttributeId,
                    'original'      => $product->getData($productAttributeCode)
                ];
            }
//            $model->setJobId($this->getId());
//            $model->setProductId($product->getId());
//            $model->setAttributeId($productAttributeId);
//            $model->setOriginal($product->getData($productAttributeCode));
//            $model->save();
//            $model->unsetData();
        }
        if($data){
            $this->conn->insertMultiple($productTranslateTableName, $data);
        }
        return $this;
    }

    protected function addCategoryAttributes($categoryAttributeIds)
    {
        foreach ($categoryAttributeIds as $categoryAttributeId) {
            $this->_attributes[] =
                Mage::getModel('strakertranslations_easytranslationplatform/category_attributes')
                    ->setJobId($this->getId())
                    ->setAttributeId((int)$categoryAttributeId)
                    ->save();
        }
        return $this;
    }


    protected function addCategoryIds($categoryIds)
    {
        $model = Mage::getModel('strakertranslations_easytranslationplatform/job_category');
            //. '`  (`category_id`, `job_id`) VALUES ';
        foreach ($categoryIds as $categoryId) {
            $model->setCategoryId($categoryId);
            $model->setJobId($this->getId());
            $model->save();
            $model->unsetData();
        }
        return $this;
    }

    protected function addCategoryTranslateOriginal($categoryAttributeId, $categoryCollection)
    {
        $model = Mage::getModel('strakertranslations_easytranslationplatform/category_translate');
        $categoryAttributeCode = Mage::getModel('eav/entity_attribute')->load($categoryAttributeId)->getAttributeCode();
        foreach ($categoryCollection as $category) {
            if($category->getData($categoryAttributeCode)){
                $model->setJobId($this->getId());
                $model->setCategoryId($category->getId());
                $model->setAttributeId($categoryAttributeId);
                $model->setOriginal($category->getData($categoryAttributeCode));
                $model->save();
                $model->unsetData();
            }
        }
        return $this;
    }

    protected function addCmsTranslateOriginal($Column, $cmsDataCollection, $type = 'page', $jobCmsIds)
    {

//        $writeConnection = $this->getWriteAdapter();
//
//        $query = 'INSERT INTO `' . Mage::getSingleton('core/resource')->getTableName('strakertranslations_easytranslationplatform/cms_' . $type . '_translate') . '`  (`job_id`, `cms_' . $type . '_id`, `column_name`, `original` , `job_cms_id`) VALUES ';
//        $queryVals = array();
//        foreach ($cmsDataCollection as $cmsData) {
//
//            foreach ($jobCmsIds as $k => $v) {
//                if ($cmsData[$type . '_id'] == $v) {
//                    $jobCmsId = $k;
//                    break;
//                }
//            }
//
//            $queryVals[] = '(' . $this->getId() . ', ' . $cmsData[$type . '_id'] . ',  \'' . addslashes($Column) . '\', \'' . addslashes($cmsData[$Column]) . '\', ' . $jobCmsId . ')';
//        }
//
//        $writeConnection->query($query . implode(',', $queryVals));

        $model = Mage::getModel('strakertranslations_easytranslationplatform/cms_' . $type . '_translate');
        //. '`  (`job_id`, `cms_' . $type . '_id`, `column_name`, `original` , `job_cms_id`) VALUES ';
        $jobCmsId = 0;
        foreach ($cmsDataCollection as $cmsData) {
            foreach ($jobCmsIds as $jobCmsIdArray) {
                if ($cmsData[$type . '_id'] == $jobCmsIdArray[$type.'_id']) {
                    $jobCmsId = $jobCmsIdArray['id'];
                    break;
                }
            }
            if($cmsData[$Column]){
                $model->setJobId($this->getId());
                $model->setData('cms_'.$type.'_id', $cmsData[$type . '_id']);
                $model->setcolumnName($Column);
                $model->setOriginal($cmsData[$Column]);
                $model->setJobCmsId($jobCmsId);
                $model->save();
                $model->unsetData();
            }
        }

        return $this;
    }

    public function array_to_xml($data, &$xml_data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item' . $key; //dealing with <0/>..<n/> issues
                }
                $subnode = $xml_data->addChild($key);
                $this->array_to_xml($value, $subnode);
            } else {
                $xml_data->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    protected function addAttributeTranslateOriginal($attributeData)
    {

//        $writeConnection = $this->getWriteAdapter();
//
//        $query = 'INSERT INTO `' . Mage::getSingleton('core/resource')->getTableName('strakertranslations_easytranslationplatform/straker_attribute_translate') . '`  (`job_id`, `attribute_id`, `original`) VALUES ';
//        $queryVals = array();
//
//        foreach ($attributeData as $attributeId => $translate) {
//
//            $original = array();
//
//            $original['title'] = $translate['label'] ? Mage::getModel('catalog/resource_eav_attribute')->load($attributeId)->getStoreLabel($this->getSourceStore()) : '';
//
//
//            if ($translate['option']) {
//                $attributeOptioinCollection = Mage::getModel('eav/entity_attribute_option')
//                    ->getCollection()
//                    ->setStoreFilter($this->getSourceStore())
//                    ->setAttributeFilter($attributeId);
//
//                foreach ($attributeOptioinCollection as $attributeOptioin) {
//                    $original['option']['id_' . $attributeOptioin->getoptionId()] = $attributeOptioin->getValue();
//                }
//            }
//
//            $xml = new SimpleXMLElement('<attribute/>');
//
//            $this->array_to_xml($original, $xml);
//
//
//            $queryVals[] = '(' . $this->getId() . ',  ' . $attributeId . ', \'' . addslashes($xml->asXML()) . '\')';
//
//
//        }
//
//        $writeConnection->query($query . implode(',', $queryVals));

        $model = Mage::getModel('strakertranslations_easytranslationplatform/attribute_translate');
        // . '`  (`job_id`, `attribute_id`, `original`) VALUES ';

        foreach ($attributeData as $attributeId => $translate) {
            $original = array();
            $original['title'] = $translate['label'] ? Mage::getModel('catalog/resource_eav_attribute')->load($attributeId)->getStoreLabel($this->getSourceStore()) : '';
            if ($translate['option']) {
                $attributeOptioinCollection = Mage::getModel('eav/entity_attribute_option')
                    ->getCollection()
                    ->setStoreFilter($this->getSourceStore())
                    ->setAttributeFilter($attributeId);
                foreach ($attributeOptioinCollection as $attributeOptioin) {
                    $original['option']['id_' . $attributeOptioin->getoptionId()] = $attributeOptioin->getValue();
                }
            }
            $xml = new SimpleXMLElement('<attribute/>');
            $this->array_to_xml($original, $xml);
            //$queryVals[] = '(' . $this->getId() . ',  ' . $attributeId . ', \'' . addslashes($xml->asXML()) . '\')';
            $model->setJobId($this->getId());
            $model->setAttributeId($attributeId);
            $model->setOriginal($xml->asXML());
            $model->save();
            $model->unsetData();
        }

//        $writeConnection->query($query . implode(',', $queryVals));

        return $this;


    }


    protected function addAttributeIds($attributeData)
    {


//        $writeConnection = $this->getWriteAdapter();
//
//        $query = 'INSERT INTO `' . Mage::getSingleton('core/resource')->getTableName('strakertranslations_easytranslationplatform/job_attribute') . '`  (`attribute_id`, `translate_label`, `translate_option`, `job_id` ) VALUES ';
//        $queryVals = array();
//
//        foreach ($attributeData as $attributeId => $translate) {
//
//            $queryVals[] = '(' . (int)$attributeId . ', ' . $translate['label'] . ', ' . $translate['option'] . ', ' . $this->getId() . ')';
//        }
//
//        $writeConnection->query($query . implode(',', $queryVals));


        $model = Mage::getModel('strakertranslations_easytranslationplatform/job_attribute');
        // . '`  (`attribute_id`, `translate_label`, `translate_option`, `job_id` ) VALUES ';

        foreach ($attributeData as $attributeId => $translate) {
            $model->setAttributeId($attributeId);
            $model->setTranslateLabel($translate['label']);
            $model->setTranslateOption($translate['option']);
            $model->setJobId($this->getId());
            $model->save();
            $model->unsetData();
        }

        return $this;

    }


    public function addProducts($productAttributeIds, $productIds)
    {

        if (!$this->getId()) {
            if (!$this->getStoreId()) {
                Mage::throwException('Error: Missing Store Id');
            }
            $this->setSourceStore(Mage::getStoreConfig('straker/general/source', $this->getStoreId()));
            $this->save();
        }

        $this->addProductAttributes($productAttributeIds);

        $countProducts = sizeof($productIds);
        $buffer = 1000;
        $_productIdSet = array();
        for ($i = 0; $i < $countProducts; $i = $i + $buffer) {
            $_productIdSet[] = array_slice($productIds, $i, $buffer);
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

            $productCollection->addFieldToFilter('entity_id', array('in' => $_productIds));

            foreach ($productAttributeIds as $productAttributeId) {
                $this->addProductTranslateOriginal($productAttributeId, $productCollection);
            }
        }
        return $this;
    }

    public function addCategories($categoryAttributeIds, $categoryIds)
    {

        if (!$this->getId()) {
            if (!$this->getStoreId()) {
                Mage::throwException('Error: Missing Store Id');
            }
            $this->setSourceStore(Mage::getStoreConfig('straker/general/source', $this->getStoreId()));
            $this->save();
        }

        $this->addCategoryAttributes($categoryAttributeIds);

        $countCategories = sizeof($categoryIds);
        $buffer = 1000;
        $_categoryIdSet = array();
        for ($i = 0; $i < $countCategories; $i = $i + $buffer) {
            $_categoryIdSet[] = array_slice($categoryIds, $i, $buffer);
        }

        foreach ($_categoryIdSet as $_categoryIds) {
            $this->addCategoryIds($_categoryIds);
        }

        foreach ($_categoryIdSet as $_categoryIds) {

            $categoryCollection = Mage::getModel('catalog/category')->getCollection()->setStore($this->getSourceStore());

            foreach ($categoryAttributeIds as $categoryAttributeId) {

                $categoryAttributeCode = Mage::getModel('eav/entity_attribute')->load($categoryAttributeId)->getAttributeCode();
                $categoryCollection->addAttributeToSelect($categoryAttributeCode);
            }

            $categoryCollection->addFieldToFilter('entity_id', array('in' => $_categoryIds));

            foreach ($categoryAttributeIds as $categoryAttributeId) {
                $this->addCategoryTranslateOriginal($categoryAttributeId, $categoryCollection);
            }
        }
        return $this;
    }

    public function addAttributes($attributeData)
    {

        if (!$this->getId()) {
            if (!$this->getStoreId()) {
                Mage::throwException('Error: Missing Store Id');
            }
            $this->setSourceStore(Mage::getStoreConfig('straker/general/source', $this->getStoreId()));
            $this->save();
        }

        $this->addAttributeIds($attributeData);

        $this->addAttributeTranslateOriginal($attributeData);

        return $this;
    }


    public function addCmsEntities($ids, $Columns = array(), $type = 'page')
    {

        if (!$this->getId()) {
            if (!$this->getStoreId()) {
                Mage::throwException('Error: Missing Store Id');
            }
            $this->setSourceStore(Mage::getStoreConfig('straker/general/source', $this->getStoreId()));
            $this->save();
        }

        foreach ($Columns as $Column) {
            $this->_attributes[] =
                Mage::getModel('strakertranslations_easytranslationplatform/cms_' . $type . '_attributes')
                    ->setJobId($this->getId())
                    ->setColumnName($Column)
                    ->save();
        }

//        $writeConnection = $this->getWriteAdapter();
//
//        $searchQuery = 'SELECT * FROM ' . Mage::getSingleton('core/resource')->getTableName('cms/' . $type)
//            . ' WHERE ' . $type . '_id IN (' . implode(',', $ids) . ')';
//
//        $cmsDataCollection = $writeConnection->fetchAll($searchQuery);

        $cmsDataCollection = Mage::getResourceModel('cms/' . $type.'_collection')
            ->addFieldToFilter($type.'_id', ['in' => $ids])
            ->getData();

//        $query = 'INSERT INTO `'
//            . Mage::getSingleton('core/resource')->getTableName('strakertranslations_easytranslationplatform/job_cms' . $type)
//            . '`  (`' . $type . '_id`,  `job_id` , `origin`) VALUES ';
//
//        $queryVals = array();
//
//        foreach ($cmsDataCollection as $cmsData) {
//            $id = $cmsData[$type . '_id'];
//
//            unset($cmsData[$type . '_id']);
//
//
//            $queryVals[] = "(" . (int)$id . ", " . $this->getId() . ", '" . addslashes(json_encode($cmsData)) . "')";
//        }
//
//        $writeConnection->query($query . implode(',', $queryVals));
        $model = Mage::getModel('strakertranslations_easytranslationplatform/job_cms_' . $type);
        //. '`  (`' . $type . '_id`,  `job_id` , `origin`) VALUES ';

        foreach ($cmsDataCollection as $cmsData) {
            $id = $cmsData[$type . '_id'];
            unset($cmsData[$type . '_id']);
            $model->setData($type.'_id', $id);
            $model->setJobId($this->getId());
            $model->setOrigin(json_encode($cmsData));
            $model->setData('title', $cmsData['title']);
            $model->setData('identifier', $cmsData['identifier']);
            $model->save();
            $model->unsetData();
        }

        $writeConnection = $this->getWriteAdapter();

//        $query = 'SELECT id, ' . $type . '_id FROM ' . Mage::getSingleton('core/resource')->getTableName('strakertranslations_easytranslationplatform/job_cms' . $type)
//            . ' WHERE job_id = ' . $this->getId();
//
//        $jobCmsIds = $writeConnection->fetchPairs($query);

        $jobCmsIds = Mage::getResourceModel('strakertranslations_easytranslationplatform/job_cms_' . $type . '_collection')
            ->addFieldToSelect('id')
            ->addFieldToSelect($type.'_id')
            ->addFieldToFilter('job_id', ['eq' => $this->getId()])
            ->getData();

        foreach ($Columns as $Column) {
            $this->addCmsTranslateOriginal($Column, $cmsDataCollection, $type, $jobCmsIds);
        }

        $this->_createCMSTranslateFile($type);
        return $this;
    }

    protected function _createCMSTranslateFile($type)
    {

        $_xml = new DOMDocument('1.0', 'utf-8');
        $_xml->formatOutput = true;

        $rootElement = $_xml->createElement('root');

        $cmsTranslateCollection = Mage::getModel('strakertranslations_easytranslationplatform/cms_' . $type . '_translate')->getCollection($this->getWriteAdapter());
        $cmsTranslateCollection->addFieldToFilter('job_id', $this->getId());
        foreach ($cmsTranslateCollection as $cmsTranslate) {
            $dataElement = $_xml->createElement('data');
            $dataElement->setAttribute('name', 'cms_' . $type . '_' . $cmsTranslate->getJobCmsId() . '_' . $cmsTranslate->getColumnName());
            $dataElement->setAttribute('content_context', $cmsTranslate->getColumnName());
            $dataElement->setAttribute('content_id', $cmsTranslate->getId());

            $valueElement = $_xml->createElement('value');
            $CDATAValueNode = $_xml->createCDATASection($cmsTranslate->getOriginal());
            $valueElement->appendChild($CDATAValueNode);

            $dataElement->appendChild($valueElement);
            $rootElement->appendChild($dataElement);
        }
        $_xml->appendChild($rootElement);
        file_put_contents(MAGENTO_ROOT . $this->_translateFilePath . 'job' . $this->getId() . '.xml', $_xml->saveXML());
        $this->setSourceFile('job' . $this->getId() . '.xml')->save();

        return $this;


    }

//    protected function _createProductTranslateFile() {
//
//        $_xml = '/</?xml version="1.0" encoding="utf-8"/?/>/<root//>';
//
//        $productTranslateCollection = Mage::getModel('strakertranslations_easytranslationplatform/product_translate')->getCollection($this->getWriteAdapter());
//        $productTranslateCollection->addFieldToFilter('job_id',$this->getId());
//
//        $attributeFrontLabel = array();
//
//        foreach ($productTranslateCollection as $productTranslate ){
//            if (!isset($attributeFrontLabel[$productTranslate->getAttributeId()])){
//                $attributeFrontLabel[$productTranslate->getAttributeId()] =
//                    Mage::getModel('eav/entity_attribute')->load($productTranslate->getAttributeId())->getFrontendLabel();
//            }
//
//            $_xml .= '<data name="' .$this->getTypeId(). '_' . $this->getStoreId().'_'. $productTranslate->getAttributeId().'_'. $productTranslate->getProductId().'" ' ;
//            $_xml .= 'content_context="' . $attributeFrontLabel[$productTranslate->getAttributeId()] . '" ';
//            $_xml .= 'content_context_url="'.Mage::getStoreConfig('web/unsecure/base_link_url',$this->getStoreId()).'catalog/category/view/id/'.$productTranslate->getProductId().'" ';
//            $_xml .= 'content_id="'. $productTranslate->getId() .'">';
//            $_xml .= '<value><![CDATA['.$productTranslate->getOriginal().']]></value></data>';
//        }
//        $_xml .='</root>';
//
//        file_put_contents(MAGENTO_ROOT.$this->_translateFilePath.'job'.$this->getId().'.xml',$_xml);
//        $this->setSourceFile('job'.$this->getId().'.xml')->save() ;
//
//        return $this;
//    }

    protected function _createProductTranslateFile()
    {
        $_xml = new DOMDocument('1.0', 'utf-8');
        $_xml->formatOutput = true;

        $rootElement = $_xml->createElement('root');

        $productTranslateCollection = Mage::getModel('strakertranslations_easytranslationplatform/product_translate')->getCollection($this->getWriteAdapter());
        $productTranslateCollection->addFieldToFilter('job_id', $this->getId());

        $attributeFrontLabel = array();

        foreach ($productTranslateCollection as $productTranslate) {
            $sCData = $productTranslate->getOriginal();
            if( isset( $sCData ) ){
                if (!isset($attributeFrontLabel[$productTranslate->getAttributeId()])) {
                    $attributeFrontLabel[$productTranslate->getAttributeId()] =
                        Mage::getModel('eav/entity_attribute')->load($productTranslate->getAttributeId())->getFrontendLabel();
                }

                $dataElement = $_xml->createElement('data');
                $dataElement->setAttribute('name', $this->getTypeId() . '_' . $this->getStoreId() . '_' . $productTranslate->getAttributeId() . '_' . $productTranslate->getProductId());
                $dataElement->setAttribute('content_context', $attributeFrontLabel[$productTranslate->getAttributeId()]);
                $dataElement->setAttribute('content_context_url',Mage::getStoreConfig('web/unsecure/base_link_url', $this->getStoreId()) . 'catalog/category/view/id/' . $productTranslate->getProductId());
                $dataElement->setAttribute('content_id', $productTranslate->getId());

                $valueElement = $_xml->createElement('value');
                $CDATAValueNode = $_xml->createCDATASection($sCData);
                $valueElement->appendChild($CDATAValueNode);

                $dataElement->appendChild($valueElement);
                $rootElement->appendChild($dataElement);
            }

        }

        $_xml->appendChild($rootElement);
        file_put_contents(Mage::getBaseDir() . $this->_translateFilePath . 'job' . $this->getId() . '.xml', $_xml->saveXML());
        $this->setSourceFile('job' . $this->getId() . '.xml')->save();

        return $this;
    }

    protected function _createCategoryTranslateFile()
    {

        $_xml = new DOMDocument('1.0', 'utf-8');
        $_xml->formatOutput = true;

        $rootElement = $_xml->createElement('root');

        $categoryTranslateCollection = Mage::getModel('strakertranslations_easytranslationplatform/category_translate')->getCollection();
        $categoryTranslateCollection->addFieldToFilter('job_id', $this->getId());

        $attributeFrontLabel = array();

        foreach ($categoryTranslateCollection as $categoryTranslate) {
            if (!isset($attributeFrontLabel[$categoryTranslate->getAttributeId()])) {
                $attributeFrontLabel[$categoryTranslate->getAttributeId()] =
                    Mage::getModel('eav/entity_attribute')->load($categoryTranslate->getAttributeId())->getFrontendLabel();
            }

            $dataElement = $_xml->createElement('data');
            $dataElement->setAttribute('name', $this->getTypeId() . '_' . $this->getStoreId() . '_' . $categoryTranslate->getAttributeId() . '_' . $categoryTranslate->getCategoryId());
            $dataElement->setAttribute('content_context',$attributeFrontLabel[$categoryTranslate->getAttributeId()]);
            $dataElement->setAttribute('content_context_url', Mage::getStoreConfig('web/unsecure/base_link_url', $this->getStoreId()) . 'catalog/category/view/id/' . $categoryTranslate->getCategoryId());
            $dataElement->setAttribute('content_id', $categoryTranslate->getId());

            $valueElement = $_xml->createElement('value');
            $CDATAValueNode = $_xml->createCDATASection($categoryTranslate->getOriginal());
            $valueElement->appendChild($CDATAValueNode);

            $dataElement->appendChild($valueElement);
            $rootElement->appendChild($dataElement);
        }

        $_xml->appendChild($rootElement);
        file_put_contents(Mage::getBaseDir() . $this->_translateFilePath . 'job' . $this->getId() . '.xml',  $_xml->saveXML());
        $this->setSourceFile('job' . $this->getId() . '.xml')->save();

        return $this;
    }

    protected function _createAttributeTranslateFile()
    {

        $_xml = new DOMDocument('1.0', 'utf-8');
        $_xml->formatOutput = true;

        $rootElement = $_xml->createElement('root');

        $attributeTranslateCollection = Mage::getModel('strakertranslations_easytranslationplatform/attribute_translate')->getCollection();
        $attributeTranslateCollection->addFieldToFilter('job_id', $this->getId());

        foreach ($attributeTranslateCollection as $attributeTranslate) {

            $dataInJson = json_encode(simplexml_load_string($attributeTranslate->getOriginal()));
            $data = json_decode($dataInJson, true);

            foreach ($data as $k => $attribute) {
                if ($k == 'title' && $attribute) {
                    $dataElement = $_xml->createElement('data');
                    $dataElement->setAttribute('name', $this->getTypeId() . '_' . $this->getStoreId() . '_' . $attributeTranslate->getAttributeId());
                    $dataElement->setAttribute('content_context', 'product attribute title');
                    $dataElement->setAttribute('content_id', $attributeTranslate->getId());

                    $valueElement = $_xml->createElement('value');
                    $CDATAValueNode = $_xml->createCDATASection($attribute);
                    $valueElement->appendChild($CDATAValueNode);

                    $dataElement->appendChild($valueElement);
                    $rootElement->appendChild($dataElement);
                }

                if ($k == 'option') {
                    foreach ($attribute as $optionId => $optionValue) {
                        $dataElement = $_xml->createElement('data');
                        $dataElement->setAttribute('name', $this->getTypeId() . '_' . $this->getStoreId() . '_' . $attributeTranslate->getAttributeId());
                        $dataElement->setAttribute('content_context', 'product attribute option');
                        $dataElement->setAttribute('option_id', $optionId);
                        $dataElement->setAttribute('content_id', $attributeTranslate->getId());

                        $valueElement = $_xml->createElement('value');
                        $CDATAValueNode = $_xml->createCDATASection($optionValue);
                        $valueElement->appendChild($CDATAValueNode);

                        $dataElement->appendChild($valueElement);
                        $rootElement->appendChild($dataElement);
                    }

                }

            }
        }
        $_xml->appendChild($rootElement);
        file_put_contents(Mage::getBaseDir() . $this->_translateFilePath . 'job' . $this->getId() . '.xml',  $_xml->saveXML());
        $this->setSourceFile('job' . $this->getId() . '.xml')->save();

        return $this;
    }

    protected function _summitJob()
    {

        $request = array();

        /** @var  $helper StrakerTranslations_EasyTranslationPlatform_Helper_Data */
        $helper = Mage::helper('strakertranslations_easytranslationplatform');
        if ($helper->isSandboxMode()) {
            $this->setIsTestJob(true);
        }

        if (!$this->getTitle()) {
            $store = Mage::getModel('core/store')->load($this->getStoreId());
            $defaultTitle = $store->getFrontendName() . '_' . $store->getName() . '_' . Mage::getModel('core/date')->timestamp();
            $this->setTitle($defaultTitle);
        }
        $request['title'] = $this->getTitle();
        $request['sl'] = $this->getSl();
        $request['tl'] = $this->getTl();

        $filePath = MAGENTO_ROOT . $this->_translateFilePath . $this->getSourceFile();

        $request['source_file'] = function_exists('curl_file_create') ? curl_file_create($filePath) : '@' . $filePath;
        $request['callback_uri'] = Mage::getStoreConfig('web/unsecure/base_link_url', $this->getStoreId()) . 'straker/callback';
        $request['token'] = $this->getId();
        /** @var StrakerTranslations_EasyTranslationPlatform_Model_Api $api */
        $api = $this->_getApi();
        $response = $api->callTranslate($request);
        if ($response->job_key) {
            $this->setStatusId(2)
                ->setJobKey($response->job_key)
                ->setTjNumber($response->tj_number)
                ->save();
            $this->setLastStatus(1);
        } else {
            $this->setLastStatus(0);
            $message = $response->magentoMessage ? $response->magentoMessage : 'Unknown Error.';
            $this->setLastMessage($message);
        }
        return $this;

    }

    public function submitProducts($productAttributeIds, $productIds)
    {
        //product
        $this->setTypeId(1);
        $this->addProducts($productAttributeIds, $productIds)
            ->_createProductTranslateFile()
            ->_summitJob();
        return $this;
    }

    public function submitCategories($categoryAttributeIds, $categoryIds)
    {

        //category
        $this->setTypeId(3);
        $this->addCategories($categoryAttributeIds, $categoryIds)
            ->_createCategoryTranslateFile()
            ->_summitJob();
        return $this;
    }

    public function submitAttributes($attributeData)
    {

        //category
        $this->setTypeId(4);
        $this->addAttributes($attributeData)
            ->_createAttributeTranslateFile()
            ->_summitJob();
        return $this;
    }

    public function submitCmsPage($cmsIds, $columns = array('title', 'content'))
    {

        $this->setTypeId(5);
        $this->addCmsEntities($cmsIds, $columns, 'page')
            ->_summitJob();
        return $this;
    }

    public function submitCmsBlock($cmsIds, $columns = array('title', 'content'))
    {

        $this->setTypeId(6);
        $this->addCmsEntities($cmsIds, $columns, 'block')
            ->_summitJob();
        return $this;
    }

    public function updateQuote()
    {

        if ($this->getJobKey()) {
            $request = array();
            $request['job_key'] = $this->getJobKey();
            $api = $this->_getApi();
            $response = $api->getTranslation($request);

            if ($response->job) {
                foreach ($response->job as $job) {
                    if ($job->token == $this->getId()) {
                        $quote = $job->quotation;
                        if ($quote && $quote != $this->getQuote()) {
                            $this->setQuote($quote)->save();
                            return true;
                        }
                    }
                }
            }
        }
        return false;

    }

    public function updateTranslation()
    {
        $updateFlag = false;
        if ($this->getJobKey()) {
            $request = array();
            $request['job_key'] = $this->getJobKey();
            /** @var StrakerTranslations_EasyTranslationPlatform_Model_Api $api */
            $api = $this->_getApi();
            $response = $api->getTranslation($request);
            if ($response->job) {
                foreach ($response->job as $job) {
                    if ($job->token == $this->getId()) {
                        $updateFlag = $this->updateJob($job);
                    }
                }
                if($updateFlag){
                    $this->setLastStatus(1);
                }else{
                    $this->setLastStatus(0);
                }
            }
            else{
                $this->setLastStatus(0);
                $message = $response->magentoMessage?$this->setLastMessage($response->magentoMessage):'Unknown Error.';
                $this->setLastMessage($message);
            }

        }
        return $updateFlag;
    }

    public function bulkUpdateTranslation(){
        /** @var StrakerTranslations_EasyTranslationPlatform_Model_Api $api */
        $api = $this->_getApi();
        $response = $api->getTranslation(array());
        return $response->job ? $response->job : false;
    }

    public function updateJob($job){
        $updateFlag = false;
        $jobStatusId = $this->_getStatusId($job->status);

        if ($jobStatusId && $job->status <> $this->getStatusName()) {
            $this->setStatusId($jobStatusId)->save();
            $updateFlag = true;
        }

        if ($job->tj_number && $job->tj_number <> $this->getTjNumber()) {
            $this->setTjNumber($job->tj_number)->save();
            $updateFlag = true;
        }

        if ($job->workflow && $job->workflow <> $this->getWorkFlow()) {
            $this->setWorkFlow($job->workflow)->save();
            $updateFlag = true;
        }

        if ($job->quotation && $job->quotation <> $this->getQuote()) {
            $this->setQuote($job->quotation)->save();
            $updateFlag = true;
        }
        foreach ($job->translated_file as $file) {
            if ($file->download_url && !$this->getDownloadUrl()) {
                $this->setDownloadUrl($file->download_url)->save();
                $result = $this->_importTranslation();
                $updateFlag = $result === false ? false : true;
            }
        }
        return $updateFlag;
    }

    protected function _getApi(){

        return Mage::getModel('strakertranslations_easytranslationplatform/api',array('store'=>$this->getStoreId()));
    }

    protected function _getStatusId($statusName)
    {

        return Mage::getModel('strakertranslations_easytranslationplatform/job_status')->load($statusName, 'status_name')->getId();
    }

    protected function _importTranslation()
    {
        /** @var StrakerTranslations_EasyTranslationPlatform_Model_Api $api */
        $api = $this->_getApi();
        $xml = $api->getTranslatedFile($this->getDownloadUrl());
        file_put_contents(Mage::getBaseDir() . $this->_translateFilePath . 'translated_job' . $this->getId() . '.xml', $xml, LOCK_EX);
        $data = simplexml_load_string($xml);

        if($data === false){
            return false;
        }else{
            $_translationValueGroup = array();

            foreach ($data->children() as $_translation) {
                $_entityTranslationId = (string)$_translation->attributes()->content_id;
                if ($this->_getType() == 'attribute') {
                    if ($_translation->attributes()->content_context == "product attribute title") {
                        $_translationValueGroup[$_entityTranslationId]['title'] = (string)$_translation->value;
                    }
                    if ($_translation->attributes()->content_context == "product attribute option") {
                        $_translationValueGroup[$_entityTranslationId]['option']['id_' . $_translation->attributes()->option_id] = (string)$_translation->value;
                    }
                } else {
                    $_translationValueGroup[$_entityTranslationId] = (string)$_translation->value;
                }
            }

            foreach ($_translationValueGroup as $content_id => $_translationValue) {
                $value = $_translationValue;
                if (is_array($_translationValue)) {
                    $xml = new SimpleXMLElement('<attribute/>');
                    $this->array_to_xml($_translationValue, $xml);
                    $value = (string)$xml->asXML();
                }

                $_entityTranslation = Mage::getModel('strakertranslations_easytranslationplatform/' . $this->_getType() . '_translate')->load($content_id);
                $_entityTranslation->setTranslate($value);
                $_entityTranslation->save();
                $_entityTranslation->clearInstance();
            }
            return $this->setDownloadedVersion(1)->save();
        }
    }

    protected function _getType()
    {
        return str_replace(' ', '_', strtolower($this->getTypeName()));
    }

    public function updatePayment()
    {

        if ($this->getJobKey()) {
            $request = array();
            $request['job_key'] = $this->getJobKey();
            $api = $this->_getApi();
            $response = $api->getPayment($request);

            if (!empty($response) && $response->status == "Paid") { ///////waiting for payment api
                $this->setPaymentStatus(1)->save();
                return true;
            }
        }
        return false;
    }

    public function applyTranslation($entityIds = array())
    {
        $success = true;
        $jobType = $this->_getType(); //product, category, cms_page ...
        // collection for straker_<TYPE>_translate tables
        $collection = Mage::getModel('strakertranslations_easytranslationplatform/' . $jobType . '_translate')->getCollection()->addFieldToFilter('job_id', $this->getId());

        if ($entityIds) {
            $collection->addFieldToFilter($jobType . '_id', array('in' => $entityIds));
        }

        $updatedIds = array();
        $writeConnection = $this->getWriteAdapter();
        $prefix = Mage::getConfig()->getTablePrefix()->__toString();

        try{
            if (in_array($jobType, array('cms_block', 'cms_page'))) {
                $result = $this->createNewCms($entityIds);
                $cmsTableName = str_replace('_', '', $jobType); //cmsxxx
                $cmsColumnName = str_replace('cms_', '', $jobType); //xxx
                $cmsModelName = 'cms/'.$cmsColumnName;

                if($result['success']){
                    //translated content array
                    $newCmsData = [];
                    foreach($collection as $translation){
                        if(!$translation->getIsImported()){
                            if(empty($newCmsData[$translation->getData($jobType . '_id')])){
                                $newCmsData[$translation->getData($jobType . '_id')] = [
                                    $translation->getColumnName() => $translation->getTranslate(),
                                ];
                            }else{
                                $newCmsData[$translation->getData($jobType . '_id')][$translation->getColumnName()] = $translation->getTranslate();
                            }
                        }
                    }

                    //cms old id and new id pairs
                    $newCmsIds = $result['new_entity_ids'];
                    foreach($newCmsIds as $oldId => $newId){
                        $model = Mage::getModel($cmsModelName)->load($newId);
                        foreach($newCmsData[$oldId] as $k => $v){
                            $model->setData($k, $v);
                        }
                        $model->setStores($this->getStoreId());
                        $model->save();
                        $model->unsetData();
                    }
                    foreach($collection as $translation){
                        $entityId = $translation->getData($jobType . '_id');
                        if(!$translation->getIsImported()){
                            $translation->setIsImported(1)->save();
                        }
                        if( empty($updatedIds[$entityId]) ){
                            $updatedIds[$entityId] = true;
                            $writeConnection->update($prefix . 'straker_job_' . $cmsTableName, array('version' => 1), $cmsColumnName . "_id = {$entityId} and job_id ={$this->getId()}");
                        }
                    }
                }else{
                    $success = false;
                }
            }else{
                foreach ($collection as $translation) {
                    if(!$translation->getVersion()){
                        $return = $translation->setStoreId($this->getStoreId())->importTranslation();
                        if($return){
                            $entityId = call_user_func(array($translation, 'getData'), strtolower(str_replace(' ', '_', $this->getTypeName() . '_id')));
                            if (empty($updatedIds[$entityId])) {
                                $updatedIds[$entityId] = true;
                                $writeConnection->update($prefix . 'straker_job_' . $jobType, array('version' => 1), $jobType . "_id = {$entityId} and job_id ={$this->getId()}");
                            }
                        }else{
                            $success = false;
                        }
                    }
                }
            }
        }catch(Exception $e){
            $success = false;
        }

        return $success;
    }

    protected function createNewCms($entityIds)
    {
        $return = [
            'success'           => true,
            'new_entity_ids'    => []
        ];
        $jobType = $this->_getType();
        $cmsType = str_replace('cms_', '', $jobType);
        $cmsModelName = str_replace('_', '/', $jobType);

        $collection = Mage::getModel('strakertranslations_easytranslationplatform/job_' . $jobType)->getCollection();
        $collection->addFieldToFilter('job_id', $this->getId());

        if ($entityIds) {
            $collection->addFieldToFilter($cmsType . '_id', array('in' => $entityIds));
        }

        foreach ($collection as $jobCms) {
            if (!$jobCms->getNewEntityId()) {
                $cmsModel = Mage::getModel($cmsModelName);
                $cmsData = json_decode($jobCms->getOrigin());
                foreach ($cmsData as $k => $v) {
                    $cmsModel->setData($k, $v);
                }
                //add target store id
//                $cmsModel->setStores(array())->save();
                $cmsModel->setStores([$this->getStoreId()]);
                try{
                    $cmsModel->save();
                    $jobCms->setNewEntityId($cmsModel->getId())->save();
                    $return['new_entity_ids'][$jobCms->getData($cmsType . '_id')] = $cmsModel->getId();
                }catch(Exception $e){
                    $return['success'] = false;
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage() . 'URL Key: ' . $jobCms->getIdentifier());
                }
            }
        }
        return $return;
    }

    public function isPublished()
    {
        if ($this->getStatusId() == 5) {
            return true;
        } elseif ($this->getStatusId() == 4) {
            return $this->updatePublishedStatus()->getStatus() == 5;
        } else {
            return false;
        }
    }

    protected function updatePublishedStatus()
    {
        $collection = Mage::getModel('strakertranslations_easytranslationplatform/job_' . $this->_getType())->getCollection()->addFieldToFilter('job_id', $this->getId());
        $collection->addFieldToFilter('version',
            array(
                array('neq' => '1'),
                array('null' => true)
            )
        );
        if (!$collection->getFirstItem()->getId()) {
            $this->setStatusId(5)->save();
        }
        return $this;
    }

    public function submitSupport(array $data)
    {

        $res = $this->_getApi()->callSupport($data);

        return $res->success;

    }

    public function checkAndCreateFolder()
    {

        $ioAdapter = new Varien_Io_File();
        try {
            $ioAdapter->checkAndCreateFolder(Mage::getBaseDir('var') . DS . 'straker');
        } catch (Exception $e) {
            Mage::throwException($e->getMessage());
        }

        return $this;

    }

    /**
     * @return Varien_Db_Adapter_Interface
     */
    protected function getWriteAdapter()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    public function _getStoreOptionArray(){
        $data = [];
        $stores = Mage::getModel('core/store')->getCollection()->getData();
        foreach($stores as $store ){
            if( $store['code'] !== 'admin'){
                $data[$store['store_id']] = $store['name'];
            }
        }
        return $data;
    }

    public function languageOptionArray( $filter ) {
        $collection = $this->getCollection();
        $collection->distinct(true)->addFieldToSelect($filter);
        $languages = [];
        foreach ($collection->getData() as $lang) {
            $languages[] = $lang[$filter];
        }
        /** @var  $helper StrakerTranslations_EasyTranslationPlatform_Model_Api */
        $helper = Mage::getModel('strakertranslations_easytranslationplatform/api');
        return $helper->_getLanguageName($languages);
    }
}