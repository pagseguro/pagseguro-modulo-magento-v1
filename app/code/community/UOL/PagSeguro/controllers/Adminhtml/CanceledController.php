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
class UOL_PagSeguro_Adminhtml_CanceledController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var UOL_PagSeguro_Helper_Canceled
     */
    private $canceled;
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
     * Call a helper to request a cancellation of a PagSeguroTransaction and update the order status.
     */
    public function doCanceledAction()
    {
        $this->builder();
        if ($this->getRequest()->getPost('data')) {
            $data = current($this->getRequest()->getPost('data'));
            try {
                $this->canceled->updateOrderStatusMagento(get_class($this->canceled), $data['id'], $data['code']);
            } catch (Exception $pse) {
                print json_encode(array(
                        "status" => false,
                        "err"    => trim($pse->getMessage()),
                    )
                );
                exit();
            }
            $this->doPostAction();
            exit();
        }
        print json_encode(array(
                "status" => false,
                "err"    => true,
            )
        );
        exit();
    }

    private function builder()
    {
        $this->canceled = Mage::helper('pagseguro/canceled');
        $this->log = Mage::helper('pagseguro/log');
        if ($this->getRequest()->getPost('days')) {
            $this->days = $this->getRequest()->getPost('days');
        }
    }

    public function doPostAction()
    {
        $this->builder();
        if ($this->days) {
            $this->log->setSearchTransactionLog(get_class($this->canceled), $this->days);
            try {
                $this->canceled->initialize($this->days);
                if (!$this->canceled->getPaymentsArray()) {
                    print json_encode(array("status" => false));
                    exit();
                }
                print $this->canceled->getTransactionGrid($this->canceled->getPaymentsArray());
            } catch (Exception $e) {
                print json_encode(array(
                        "status" => false,
                        "err"    => trim($e->getMessage()),
                    )
                );
            }
        }
    }

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
