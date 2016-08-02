<?php
namespace Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Straker\EasyTranslationPlatform\Model\AttributeTranslation','Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation');
    }
}
