<?php

/**
 * Created by PhpStorm.
 * User: WlliamZhao
 * Date: 30/09/15
 * Time: 4:37 PM
 */
class StrakerTranslations_EasyTranslationPlatform_Model_Resource_Job extends Mage_Core_Model_Resource_Db_Abstract {

  protected function _construct() {
    $this->_init('strakertranslations_easytranslationplatform/job', 'id');
  }

  protected function _getLoadSelect($field, $value, $object) {
    $select = parent::_getLoadSelect($field, $value, $object)
      ->join(array('t' => $this->getTable('strakertranslations_easytranslationplatform/job_type')),
        'straker_job.type_id=t.type_id',
        array('type_name')
      )
      ->join(array('s' => $this->getTable('strakertranslations_easytranslationplatform/job_status')),
        'straker_job.status_id=s.status_id',
        array('status_name')
      );

    return $select;
  }

  /**
   * Prepare data for save
   *
   * @param Mage_Core_Model_Abstract $object
   * @return array
   */
  protected function _prepareDataForSave(Mage_Core_Model_Abstract $object) {
    $currentTime = Varien_Date::now();
    if ((!$object->getId() || $object->isObjectNew()) && !$object->getCreatedAt()) {
      $object->setCreatedAt($currentTime);
    }
    $object->setUpdatedAt($currentTime);
    $data = parent::_prepareDataForSave($object);
    return $data;
  }

}