<?php
namespace Straker\EasyTranslationPlatform\Model;
class JobStatus extends \Magento\Framework\Model\AbstractModel implements JobStatusInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG                 = 'straker_easytranslationplatform_jobstatus';
    const ENTITY                    = 'straker_job_status';
    const JOBSTATUS                 = ['init', 'queued','ready','in progress','completed','published'];
    const JOB_STATUS_INIT           = 1;
    const JOB_STATUS_QUEUED         = 2;
    const JOB_STATUS_READY          = 3;
    const JOB_STATUS_INPROGRESS     = 4;
    const JOB_STATUS_COMPLETED      = 5;
    const JOB_STATUS_PUBLISHED      = 6;

    protected function _construct()
    {
        $this->_init('Straker\EasyTranslationPlatform\Model\ResourceModel\JobStatus');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
