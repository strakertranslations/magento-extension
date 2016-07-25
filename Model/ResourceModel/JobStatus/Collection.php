<?php
namespace Straker\EasyTranslationPlatform\Model\ResourceModel\JobStatus;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected function _construct()
    {
        $this->_init('Straker\EasyTranslationPlatform\Model\JobStatus','Straker\EasyTranslationPlatform\Model\ResourceModel\JobStatus');
    }
}
