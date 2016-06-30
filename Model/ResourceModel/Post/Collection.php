<?php namespace Straker\EasyTranslationPlatform\Model\ResourceModel\Post;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'post_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Straker\EasyTranslationPlatform\Model\Post', 'Straker\EasyTranslationPlatform\Model\ResourceModel\Post');
    }

}
