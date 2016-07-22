<?php
namespace Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeOptionTranslation;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Straker\EasyTranslationPlatform\Model\AttributeOptionTranslation','Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeOptionTranslation');
    }
}
