<?php
namespace Straker\EasyTranslationPlatform\Model;
class JobType extends \Magento\Framework\Model\AbstractModel implements JobTypeInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'straker_easytranslationplatform_jobtype';

    const ENTITY = 'straker_job_type';

    const JOBTYPE = ['PRODUCT', 'CATEGORY', 'ATTRIBUTE'];

    protected function _construct()
    {
        $this->_init('Straker\EasyTranslationPlatform\Model\ResourceModel\JobType');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
