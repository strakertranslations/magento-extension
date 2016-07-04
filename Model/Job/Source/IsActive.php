<?php
namespace Straker\EasyTranslationPlatform\Model\Job\Source;

class IsActive implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Straker\EasyTranslationPlatform\Model\Post
     */
    protected $post;

    /**
     * Constructor
     *
     * @param \Straker\EasyTranslationPlatform\Model\Post $post
     */
    public function __construct(\Straker\EasyTranslationPlatform\Model\Post $post)
    {
        $this->post = $post;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->post->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
