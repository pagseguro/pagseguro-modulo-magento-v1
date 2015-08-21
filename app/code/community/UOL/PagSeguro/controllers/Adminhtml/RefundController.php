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

class UOL_PagSeguro_Adminhtml_RefundController extends Mage_Adminhtml_Controller_Action
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
     * @var UOL_PagSeguro_Helper_Refund
     */
    private $refund;

    /**
     * Render canceled layout in administration interface
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
     * Get a list of PagSeguroTransaction from webservice.
     * @return JSON|null of PagSeguroTransaction list
     */
    public function doPostAction()
    {
        $this->builder();
        if ($this->days) {
            $this->log->setSearchTransactionLog(get_class($this->refund), $this->days);

            try {

                $this->refund->initialize($this->days);

                if (!$this->refund->getPaymentsArray()) {
                    print json_encode(array("status" => false));
                    exit();
                }

                print $this->refund->getTransactionGrid($this->refund->getPaymentsArray());

            } catch (Exception $e) {

                print json_encode(array(
                        "status" => false,
                        "err" => trim($e->getMessage())
                    )
                );
            }
        }
    }
    
    /**
     * Call a helper to request a refund of a PagSeguroTransaction and update the order status.
     */
    public function doRefundAction()
    {
        $this->builder();
        if ($this->getRequest()->getPost('data')) {
            foreach ($this->getRequest()->getPost('data') as $data) {
                $this->refund->updateOrderStatusMagento(get_class($this->refund), $data['id'], $data['code']);
            }
            
            $this->doPostAction();
            exit();
        }

        print json_encode(array(
            "status" => false, 
            "err" => true)
        );
        exit();
    }
    
    /**
     * Initializes helpers and instance vars.
     */
    private function builder()
    {
        $this->refund = Mage::helper('pagseguro/refund');
        $this->log = Mage::helper('pagseguro/log');
        if ($this->getRequest()->getPost('days')) {
            $this->days = $this->getRequest()->getPost('days');
        }
    }
}
