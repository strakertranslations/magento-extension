<?php

namespace Straker\EasyTranslationPlatform\Model\ResourceModel;

//use Straker\EasyTranslationPlatform\Model\JobType;

class AttributeTranslation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_jobFactory;

    const PageAttributes = [
        ['name'=>'title','label'=>'Title'],
        ['name'=>'meta_keywords','label'=>'Meta Keywords'],
        ['name'=>'content_heading','label'=>'Meta Description'],
        ['name'=>'content','label'=>'Content']
    ];

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Straker\EasyTranslationPlatform\Model\JobFactory $jobFactory,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_jobFactory = $jobFactory;
    }

    protected function _construct()
    {
        $this->_init('straker_attribute_translation','attribute_translation_id');
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $job_type = $this->_jobFactory->create()->load($object->getData('job_id'))->getJobTypeId();

        if( $job_type == \Straker\EasyTranslationPlatform\Model\JobType::JOB_TYPE_PAGE )
        {
            $key = array_search( $object->getAttributeId(),array_column(self::PageAttributes, 'name'));
            $object->setAttributeId($key);
        }
        return parent::_beforeSave($object);
    }
}
