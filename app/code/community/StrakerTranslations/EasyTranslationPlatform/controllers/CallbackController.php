<?php
class StrakerTranslations_EasyTranslationPlatform_CallbackController extends Mage_Core_Controller_Front_Action{
    public function indexAction(){
        $data = $this->getRequest()->getPost();
        Mage::log(print_r($data,true), null , 'straker_callback.log' , true);

        $this->getResponse()->setHeader('Content-type', 'application/json');

        if ($data['token'] && $data['event'] ){

            $job = Mage::getModel('strakertranslations_easytranslationplatform/job')->load((int) $data['token']);

            switch ($data['event']){

                case 'quote_ready' :

                    if ($job->getJobKey() && $job->updateQuote()){
                        $this->getResponse()->setBody('{status: success}');
                    }

                    break;

                case 'translation_ready' :

                    if ($job->getJobKey() && $job->updateTranslation()){
                        $this->getResponse()->setBody('{status: success}');
                    }

                    break;

            }

        } else{
            $this->getResponse()->setHeader('HTTP/1.0', '400', true);
            $this->getResponse()->setBody('{status: error, message: parameter missing, token: '.$data['token'].', event: '.$data['event'].'}');
        }







    }
}