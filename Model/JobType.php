<?php
namespace Straker\EasyTranslationPlatform\Model;
class JobType extends \Magento\Framework\Model\AbstractModel implements JobTypeInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG             = 'straker_easytranslationplatform_jobtype';
    const ENTITY                = 'straker_job_type';
    const JOBTYPE               = ['product', 'category', 'attribute'];
    const JOB_TYPE_PRODUCT      = 1;
    const JOB_TYPE_CATEGORY     = 2;
    const JOB_TYPE_ATTRIBUTE    = 3;

    protected function _construct()
    {
        $this->_init('Straker\EasyTranslationPlatform\Model\ResourceModel\JobType');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
