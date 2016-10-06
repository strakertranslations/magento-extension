<?php
namespace Straker\EasyTranslationPlatform\Model\ResourceModel\Job;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'job_id';
    protected $_configHelper;
    protected $_mode;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
        //        , ConfigHelper $configHelper
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
//        $this->_configHelper = $configHelper;
//        $this->_mode = $this->_configHelper->isSandboxMode();
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Straker\EasyTranslationPlatform\Model\Job', 'Straker\EasyTranslationPlatform\Model\ResourceModel\Job');
    }
}
