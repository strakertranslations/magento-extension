<?php
namespace Straker\EasyTranslationPlatform\Model\ResourceModel;
class AttributeTranslation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('straker_attribute_translation','attribute_translation_id');
    }
}
