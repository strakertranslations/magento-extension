<?php
namespace Straker\EasyTranslationPlatform\Model;
class JobStatus extends \Magento\Framework\Model\AbstractModel implements JobStatusInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'straker_easytranslationplatform_jobstatus';
    
    const ENTITY = 'straker_job_status';
    
    const JOBSTATUS = ['queued','ready','in progress','completed','published'];

    protected function _construct()
    {
        $this->_init('Straker\EasyTranslationPlatform\Model\ResourceModel\JobStatus');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
