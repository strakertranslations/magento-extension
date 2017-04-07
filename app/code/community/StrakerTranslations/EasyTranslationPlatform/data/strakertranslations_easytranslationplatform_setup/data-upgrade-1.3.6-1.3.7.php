<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$models = ['page', 'block'];

foreach( $models as $model ){
    $data = Mage::getModel('strakertranslations_easytranslationplatform/job_cms_' . $model )->getCollection();
    foreach( $data as $d ){
        $origin = $d->getOrigin() ? json_decode($d->getOrigin(), true) : [];
        if( array_key_exists('title', $origin )){
            $d->setTitle($origin['title']);
        }
        if( array_key_exists('identifier', $origin )){
            $d->setIdentifier($origin['identifier']);
        }
        $d->save();
    }
}