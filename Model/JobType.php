<?php
namespace Straker\EasyTranslationPlatform\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Symfony\CS\Fixer\Symfony\SelfAccessorFixer;

class JobType extends AbstractModel implements JobTypeInterface, IdentityInterface
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
