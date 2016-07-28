<?php
namespace Straker\EasyTranslationPlatform\Model\ResourceModel;
class AttributeOptionTranslation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('straker_attribute_option_translation','attribute_option_translation_id');
    }
}
