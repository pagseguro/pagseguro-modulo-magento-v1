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
class UOL_PagSeguro_Adminhtml_TransactionController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var UOL_PagSeguro_Helper_Transaction
     */
    private $transaction;

    /**
     * @var UOL_PagSeguro_Helper_Log
     */
    private $log;

    /**
     * @var array
     */
    private $paramsFilter;

    /**
     * UOL_PagSeguro_Adminhtml_TransactionController constructor.
     */
    public function _construct()
    {
        $this->log = new UOL_PagSeguro_Helper_Log();
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

    /**
     * Call a helper to request all transactions of a PagSeguro.
     */
    public function doPostAction()
    {
        $this->builder();
        $this->log->setSearchListTransactionLog(get_class($this->transaction), $this->paramsFilter);
        try{
            $this->transaction->initialize($this->paramsFilter);

            if (!$this->transaction->getPagSeguroOrdersArray()) {
                print json_encode(array("status" => false));
                exit();
            }

            print $this->transaction->getTransactionGrid($this->transaction->getPagSeguroOrdersArray());

        }catch (Exception $e) {
            print json_encode(array(
                    "status" => false,
                    "err" => trim($e->getMessage())
                )
            );
        }
    }

    public function builder()
    {
        $this->transaction = Mage::helper('pagseguro/transaction');
        $this->log = Mage::helper('pagseguro/log');

        $this->paramsFilter = array();

        if($this->getRequest()->getPost('date_begin'))
        {
            $this->paramsFilter['startDate'] = $this->getRequest()->getPost('date_begin');
        }

        if($this->getRequest()->getPost('date_end'))
        {
            $this->paramsFilter['endDate'] = $this->getRequest()->getPost('date_end');
        }

        if($this->getRequest()->getPost('id_magento'))
        {
            $this->paramsFilter['idMagento'] = $this->getRequest()->getPost('id_magento');
        }

        if($this->getRequest()->getPost('id_pagseguro'))
        {
            $this->paramsFilter['idPagSeguro'] = $this->getRequest()->getPost('id_pagseguro');
        }

        if($this->getRequest()->getPost('ambiente'))
        {
            $this->paramsFilter['environment'] = $this->getRequest()->getPost('enviroment');
        }

        if($this->getRequest()->getPost('status'))
        {
            $this->paramsFilter['status'] = $this->getRequest()->getPost('status');
        }
    }

    /**
     * Call a helper to request a transaction of a PagSeguro by transaction code
     */
    public function getTransactionAction()
    {
        $this->builder();

        if ($this->getRequest()->getParam('transaction_code')) {
            $transactionCode = str_replace('-', '', $this->getRequest()->getParam('transaction_code'));

            try {
                $this->transaction->getTransactionByCode($transactionCode);

                if(!$this->transaction->getTransactionsArray() && $this->transaction->checkNeedConciliate()) {
                    print json_encode(array("status" => false, "err" => "conciliate"));
                    exit();
                }

                if (!$this->transaction->getTransactionsArray()) {
                    print json_encode(array("status" => false));
                    exit();
                }

                print json_encode($this->transaction->getTransactionsArray());
            } catch (Exception $e) {
                print json_encode(array(
                        "status" => false,
                        "err"    => trim($e->getMessage()),
                    )
                );
                exit();
            }
        }else{
            print json_encode(array("status" => false));
            exit();
        }
    }
}