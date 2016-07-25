<?php
namespace Straker\EasyTranslationPlatform\Model\ResourceModel\JobType;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'type_id';

    protected function _construct()
    {
        $this->_init('Straker\EasyTranslationPlatform\Model\JobType','Straker\EasyTranslationPlatform\Model\ResourceModel\JobType');
    }
}
