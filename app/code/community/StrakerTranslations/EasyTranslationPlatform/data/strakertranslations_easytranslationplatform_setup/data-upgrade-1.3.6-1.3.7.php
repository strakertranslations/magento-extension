<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$models = array('page', 'block');

foreach($models as $model){
    $data = Mage::getModel('strakertranslations_easytranslationplatform/job_cms_' . $model)->getCollection();
    foreach($data as $d){
        $needSave = false;

        $origin = $d->getOrigin() ? json_decode($d->getOrigin(), true) : array();

        if ( !$d->getTitle() ) {
            if(array_key_exists('title', $origin)){
                $needSave = true;
                $d->setTitle($origin['title']);
            }
        }

        if ( !$d->getIdentifier() ) {
            if(array_key_exists('identifier', $origin)){
                $needSave = true;
                $d->setIdentifier($origin['identifier']);
            }
        }

        if ($needSave) {
            $d->save();
        }
    }
}