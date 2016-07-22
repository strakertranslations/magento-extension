<?php
namespace Straker\EasyTranslationPlatform\Model;
class AttributeTranslation extends \Magento\Framework\Model\AbstractModel implements AttributeTranslationInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'straker_easytranslationplatform_attributetranslation';

    const ENTITY = 'straker_attribute_translation';

    protected function _construct()
    {
        $this->_init('Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
