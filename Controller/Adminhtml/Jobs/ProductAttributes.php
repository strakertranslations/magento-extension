<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Jobs;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;
use Magento\Framework\Controller\Result\JsonFactory;

class ProductAttributes extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $_resultLayoutFactory;

    /**
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_jsonFactory = $jsonFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $resultLayout = $this->_resultLayoutFactory->create();

        $resultLayout->getLayout()->getBlock('easytranslationplatform.edit.tab.productattributes')->setData('productIds', $this->getRequest()->getPost('products'));

        return $resultLayout;
    }
}
