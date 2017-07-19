<?php

/**
 ************************************************************************
 * Copyright [2015] [PagSeguro Internet Ltda.]
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 ************************************************************************
 */
class UOL_PagSeguro_Adminhtml_AbandonedController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var UOL_PagSeguro_Helper_Abandoned
     */
    private $abandoned;
    /**
     * @var int
     */
    private $days;
    /**
     * @var UOL_PagSeguro_Helper_Log
     */
    private $log;

    /**
     * UOL_PagSeguro_Adminhtml_ConciliationController constructor.
     */
    public function _construct()
    {
        $this->log = new UOL_PagSeguro_Helper_Log();
    }

    /**
     * Call a helper to send an email with PagSeguroTransaction resume link
     */
    public function doAbandonedAction()
    {
        $this->builder();
        if ($this->getRequest()->getPost('data')) {
            foreach ($this->getRequest()->getPost('data') as $data) {
                $this->abandoned->sendAbandonedEmail($data['id'], $data['recovery']);
            }
        }
        $this->doPostAction();
    }

    /**
     * Initializes helpers and instance vars.
     */
    private function builder()
    {
        $this->abandoned = Mage::helper('pagseguro/abandoned');
        //$this->log = Mage::helper('pagseguro/log');
        if ($this->getRequest()->getPost('days')) {
            $this->days = $this->getRequest()->getPost('days');
        }
    }

    /**
     * Get a list of abandoned PagSeguroTransaction from webservice.
     */
    public function doPostAction()
    {
        $this->builder();
        if ($this->days) {
            //$this->log->setSearchTransactionLog(get_class($this->abandoned), $this->days);
            try {
                $this->abandoned->initialize($this->days);
                if (!$this->abandoned->getPaymentsArray()) {
                    print json_encode(array("status" => false));
                    exit();
                }
                print $this->abandoned->getTransactionGrid($this->abandoned->getPaymentsArray());
            } catch (Exception $e) {
                print json_encode(array(
                        "status" => false,
                        "err"    => trim($e->getMessage()),
                    )
                );
            }
        }
    }

    /**
     * Render abandoned layout in administration interface
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
}
