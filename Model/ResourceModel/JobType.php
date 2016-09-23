<?php
namespace Straker\EasyTranslationPlatform\Model\ResourceModel;
class JobType extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('straker_job_type','type_id');
    }
}
