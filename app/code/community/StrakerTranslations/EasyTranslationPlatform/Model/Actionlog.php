<?php
/**
 * Created by PhpStorm.
 * User: WlliamZhao
 * Date: 30/09/15
 * Time: 4:06 PM
 */ 
class StrakerTranslations_EasyTranslationPlatform_Model_Actionlog extends Mage_Core_Model_Abstract
{
    private $_added = false;

    protected function _construct()
    {
        $this->_init('strakertranslations_easytranslationplatform/actionlog');
    }

    public function addLog($action='', $message='', $extra='' ) {

        if (!$this->_added) { // should be only one log per action

            $_action = $action ? $action : Mage::app()->getRequest()->getControllerName() . ':' . Mage::app()->getRequest()->getActionName();

            $this->setUserId($this->_getAdminUser())
              ->setAction($_action)
              ->setMessage($message)
              ->setExtra($extra)
              ->save();
            $this->_added = true;
        }

    }

    protected function _getAdminUser(){

        return Mage::getSingleton('admin/session')->getUser()->getId();
    }


}