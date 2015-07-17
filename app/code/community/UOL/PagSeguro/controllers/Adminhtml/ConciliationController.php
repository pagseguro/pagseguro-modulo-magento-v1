<?php

/**
************************************************************************
Copyright [2015] [PagSeguro Internet Ltda.]

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

class UOL_PagSeguro_Adminhtml_ConciliationController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var int
     */
    private $days;
    
    /**
     * @var UOL_PagSeguro_Helper_Log
     */
    private $log;
    
    /**
     * @var UOL_PagSeguro_Helper_Conciliation
     */
    private $conciliation;

    /**
     * Render conciliation layout in administration interface
     */
    public function indexAction()
    {
        Mage::getSingleton('core/session')->setData(
            'store_id',
            Mage::app()->getRequest()->getParam('store')
        );
        $this->loadLayout();
        $this->_setActiveMenu('pagseguro_menu')->renderLayout();
    }
    
    /**
     * Get a PagSeguroTransaction list from webservice.
     * @return JSON|null of PagSeguroTransaction list
     */
    public function doPostAction()
    {
        $this->builder();
        if ($this->days) {
            $this->log->setSearchTransactionLog(get_class($this->conciliation), $this->days);

            $this->conciliation->initialize($this->days);

            try {
                if (!$this->conciliation->getPaymentsArray()) {
                    print json_encode(false);
                    exit();
                }

                print $this->conciliation->getTransactionGrid($this->conciliation->getPaymentsArray());

            } catch (Exception $e) {
                print $e->getMessage();
            }
        }
    }
    
    /**
     * Call a helper to update the order status.
     */
    public function doConciliationAction()
    {
        $this->builder();

        if ($this->getRequest()->getPost('data')) {
            foreach ($this->getRequest()->getPost('data') as $data) {
                $this->conciliation->updateOrderStatusMagento(
                    get_class($this->conciliation),
                    $data['id'],
                    $data['code'],
                    $data['status']
                );
            }
        }
        
        //Call this function for reload data in DataTables.
        $this->doPostAction();
    }
    
    /**
     * Initializes helpers and instance vars.
     */
    private function builder()
    {
        $this->conciliation = Mage::helper('pagseguro/conciliation');
        $this->log = Mage::helper('pagseguro/log');
        if ($this->getRequest()->getPost('days')) {
            $this->days = $this->getRequest()->getPost('days');
        }
    }
}
