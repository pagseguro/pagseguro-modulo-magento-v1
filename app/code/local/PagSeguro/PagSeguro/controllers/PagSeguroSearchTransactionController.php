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

class PagSeguro_PagSeguro_PagSeguroSearchTransactionController extends FrontAction
{
    
    private $transactionCode;
    
    private $objCredentials;
    
    private $objTransaction;
    
    public function __construct()
    {
        $this->transactionCode = $_GET['transactionCode'];
        $this->_createCredential();
        $this->_createTransaction();
    }

    /**
     * Create Credential 
     */
    private function _createCredential()
    {
        $this->objPagSeguro =  Mage::getSingleton('PagSeguro_PagSeguro_Model_pagseguro');
        $this->objCredential = $this->objPagSeguro->getCredentialsInformation();
    }
    
    /**
     * Create Transaction
     */
    private function _createTransaction()
    {
        $this->objTransaction = PagSeguroTransactionSearchService::searchByCode(
            $this->objCredentials,
            $this->transactionCode
        );
    }
}
