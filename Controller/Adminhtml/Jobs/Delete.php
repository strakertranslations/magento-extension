<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Jobs;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;

class Delete extends \Magento\Backend\App\Action
{

    /**
     * {@inheritdoc}
     */
    /*protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Straker_Job::atachment_delete');
    }*/

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('job_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->_objectManager->create('Straker\EasyTranslationPlatform\Model\Job');
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('The job has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['job_id' => $id]);
            }
        }
        $this->messageManager->addError(__('We can\'t find a job to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
