<?php

/*
************************************************************************
Copyright [2013] [PagSeguro Internet Ltda.]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
************************************************************************
*/
include_once (getcwd().'/app/code/local/PagSeguro/PagSeguro/Model/PagSeguroLibrary/PagSeguroLibrary.php');

use Mage_Core_Controller_Front_Action as FrontAction;

class PagSeguro_PagSeguro_NotificationController extends FrontAction
{
 
    private $objPagSeguro;

    private $objCredential;

    private $objNotification;

    /**
     * Notification Action
     */
    public function sendAction()
    {
        $this->createObjects();
        $this->createCredential();
            
        $this->objNotification->initialize($this->objCredential, $_POST);
    }
      
    /**
     * Create Objects
     */
    private function createObjects()
    {
        $this->objPagSeguro =  Mage::getSingleton('PagSeguro_PagSeguro_Model_PaymentMethod');
        $this->objNotification = Mage::getSingleton('PagSeguro_PagSeguro_Model_NotificationMethod');
    }
    
    /**
     * Create Credential
     */
    private function createCredential()
    {
        $this->objCredential = $this->objPagSeguro->getCredentialsInformation();
    }
}
