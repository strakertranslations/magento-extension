<?php
namespace Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation;

use Straker\EasyTranslationPlatform\Model;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Straker\EasyTranslationPlatform\Model\AttributeTranslation',
            'Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation');
    }

    public function massUpdate(array $data)
    {
        $this->getConnection()->update($this->getResource()->getMainTable(), $data, $this->getResource()->getIdFieldName() . ' IN(' . implode(',', $this->getAllIds()) . ')');

        return $this;
    }

    function addCategoryName( $sourceStoreId = 0, $attrId = 0 ){
        $categoryTable = $this->getTable('catalog_category_entity_varchar');

        if( $sourceStoreId == 0 ){
            $this->getSelect()
                ->joinLeft(
                    ['cn'=> $categoryTable],
                    'main_table.entity_id = cn.entity_id AND cn.store_id = 0 AND cn.attribute_id = ' . $attrId,
                    ['name' => 'value']
                );
        }else{
            $this->getSelect()
                ->columns(
                    'if(cn_store.value IS NOT NULL, cn_store.value, cn_default.value) AS name'
                )->joinLeft(
                    ['cn_store'=> $categoryTable],
                    'main_table.entity_id = cn_store.entity_id AND cn_store.store_id = ' .$sourceStoreId . ' AND cn_store.attribute_id = ' . $attrId,
                    []
                )->joinLeft(
                    ['cn_default'=> $categoryTable],
                    'main_table.entity_id = cn_default.entity_id AND cn_default.store_id = 0 AND cn_default.attribute_id = ' . $attrId,
                    []
                );
        }

        return $this;
    }
}
