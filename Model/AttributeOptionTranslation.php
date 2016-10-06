<?php
namespace Straker\EasyTranslationPlatform\Model;

class AttributeOptionTranslation extends \Magento\Framework\Model\AbstractModel implements AttributeOptionTranslationInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'straker_easytranslationplatform_attributeoptiontranslation';

    const ENTITY = 'straker_attribute_option_translation';

    protected function _construct()
    {
        $this->_init('Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeOptionTranslation');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
