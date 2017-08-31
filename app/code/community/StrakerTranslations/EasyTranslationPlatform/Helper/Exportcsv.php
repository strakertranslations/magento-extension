<?php

class StrakerTranslations_EasyTranslationPlatform_Helper_Exportcsv extends Mage_Core_Helper_Abstract {

    /**
     * Contains current collection
     * @var string
     */
    protected $_list = null;
     
    public function __construct(  )
    {
        $jobId = Mage::app()->getRequest()->getParam('job_id');
        $job = Mage::getModel('strakertranslations_easytranslationplatform/job')->load($jobId);
        $jobAttributes = Mage::getModel('strakertranslations_easytranslationplatform/product_attributes')->getCollection()->addFieldToFilter('job_id', $jobId);
       
        /** @var StrakerTranslations_EasyTranslationPlatform_Model_Resource_Job_Product_Collection $collection */
        $collection = Mage::getModel('strakertranslations_easytranslationplatform/job_product')->getCollection()->addFieldToFilter('main_table.job_id', $job->getId());
   
        $collection->getSelect()
            ->joinLeft(
                ['product' => $collection->getTable('catalog/product') ],
                'product.entity_id = main_table.product_id',
                ['sku']
            )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('main_table.id')
            ->columns('main_table.product_id')
            ->columns('product.sku');

        foreach ($jobAttributes as $jobAttribute) {

            $attributeCode = Mage::getModel('eav/entity_attribute')->load($jobAttribute->getAttributeId())->getAttributeCode();

            $collection->getSelect()->joinLeft(
                array($attributeCode => $collection->getTable('strakertranslations_easytranslationplatform/product_translate')),
                $attributeCode . '.product_id = main_table.product_id 
                AND ' . $attributeCode . '.attribute_id = ' . $jobAttribute->getAttributeId() . ' 
                AND ' . $attributeCode . '.job_id = ' . $job->getId(),
                array($attributeCode . ' - Source' => 'original', $attributeCode . ' - Target' => 'translate')
            );
        }
        $this->setList( $collection );
    }

    /**
     * Sets current collection
     * @param $query
     */
     public function setList($collection)
     {
         $this->_list = $collection;
     }

     /**
     * Returns indexes of the fetched array as headers for CSV
     * @param array $products
     * @return array
     */
    protected function _getCsvHeaders($products)
    {
        $product = current($products);
        $headers = array_keys($product->getData());
        $refine_headers = array();

        foreach ( $headers as $key => $value ) {
          array_push($refine_headers, ucwords( str_replace("_"," ", $value )));
        }
        return $refine_headers;
    }

    /**
     * Generates CSV file with product's list according to the collection in the $this->_list
     * @return array
     */
     public function generateMlnList()
     {
        if (!is_null($this->_list)) {
            $items = $this->_list->getItems();
            if (count($items) > 0) {
 
                $io = new Varien_Io_File();
                $path = Mage::getBaseDir('var') . DS . 'export' . DS;
                $name = md5(microtime());
                $file = $path . DS . $name . '.csv';
                $io->setAllowCreateFolders(true);
                $io->open(array('path' => $path));
                $io->streamOpen($file, 'w+');
                $io->streamLock(true);

                $io->streamWriteCsv($this->_getCsvHeaders($items));
                foreach ($items as $product) {
                    $io->streamWriteCsv($product->getData());
                }
                return array(
                    'type'  => 'filename',
                    'value' => $file,
                    'rm'    => true
                );
            }
         }
     }
}