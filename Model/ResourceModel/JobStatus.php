<?php
namespace Straker\EasyTranslationPlatform\Model\ResourceModel;

class JobStatus extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('straker_job_status', 'status_id');
    }
}
