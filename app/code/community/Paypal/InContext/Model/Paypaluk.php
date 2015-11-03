<?php

class Paypal_InContext_Model_Paypaluk extends Mage_PaypalUk_Model_Api_Nvp{
    
    
    public function callGetPalDetails()
    {
        $response = $this->call('getPalDetails', array());
        $this->_importFromResponse($this->_getPalDetailsResponse, $response);
    }
    
}