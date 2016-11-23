<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 28/07/16
 * Time: 12:23
 */

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Jobs;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Catalog\Model\ProductFactory;

use Straker\EasyTranslationPlatform\Helper\ConfigHelper;

class ViewJob extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;
    protected $_configHelper;
    protected $_productLoader;

    /**
     * ViewJob constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ConfigHelper $configHelper,
        ProductFactory $productFactory
    ) {

        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_configHelper = $configHelper;
        $this->productFactory = $productFactory;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {

        if ($this->_configHelper->isSandboxMode()) {
            $this->messageManager->addNotice($this->_configHelper->getSandboxMessage());
        }

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Straker_EasyTranslationPlatform::managejobs');
        $resultPage->getConfig()->getTitle()->prepend(__('Straker Translations'));

        if (array_key_exists('target_store_id',$this->_request->getParams())){

            $productUrl = $this->productFactory->create()->setStoreId($this->_request->getParam('target_store_id'))->load($this->_request->getParam('entity_id'))->getProductUrl();

            //Todo:: This only works if there is admin in the product URL - need to change the way the url is retrieved.
            //Todo:: Need to add programatic store switcher.
            $productUrl = str_replace('admin/','',$productUrl);

            $redirect = $this->resultRedirectFactory->create();

            $redirect->setPath($productUrl);

            return $redirect;
        }

        return $resultPage;
    }
}
