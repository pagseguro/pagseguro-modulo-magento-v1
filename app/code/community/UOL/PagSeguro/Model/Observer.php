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

class UOL_PagSeguro_Model_Observer
{

    protected $lib_path;

    /**
     * UOL_PagSeguro_Model_Observer constructor.
     * @param string $lib_path
     */
    public function __construct($lib_path)
    {
        $this->lib_path = Mage::getBaseDir('lib'). '/PagseguroPhpSdk/vendor/autoload.php';
    }

    public function addAutoloader()
    {
        include_once($this->lib_path);
        return $this;
    }

    /**
     * Query the existing transaction codes with the id of the request and assembles an array with these codes.
     * @param object $observer - It is an object of Event of observe.
     */
    public function salesOrderGridCollectionLoadBefore($observer)
    {
        $collection = $observer->getOrderGridCollection();
        $select = $collection->getSelect();
        $tableCollection = Mage::getSingleton('core/resource')->getTableName('pagseguro_orders');
        $select->joinLeft(
            array('payment' => $tableCollection),
            'payment.order_id = main_table.entity_id',
            array('payment_code'=>'transaction_code',
            'payment_environment' => 'environment')
        );
    }

    /**
    * Performs a function that checks if the credentials are correct.
    */
    public function adminSystemConfigPaymentSave()
    {
        if (!Mage::getStoreConfig("payment/pagseguro/init")) {
            Mage::getConfig()->saveConfig('payment/pagseguro/init', 1);
        }

        if (Mage::getStoreConfig("payment/pagseguro/email") && Mage::getStoreConfig("payment/pagseguro/token")) {
            try {
                Mage::helper('pagseguro')->checkCredentials();
            } catch (Exception $exc) {
                Mage::getSingleton('core/session')->addError(
                    'PagSeguro: Credenciais (EMAIL ou TOKEN) inválidas para o AMBIENTE selecionado.'
                        . 'Não será possível utilizar nenhum tipo de checkout enquanto não '
                        . 'forem salvas credenciais válidas.'
                );
            }
        } else {
            throw new Exception("Certifique-se de que o e-mail e token foram preenchidos.");
        }

        $this->configStatusPagSeguro();

    }

    public function configStatusPagSeguro()
    {
        $statusPagSeguro = array('pending', 'aguardando_pagamento_ps', 'cancelada_ps', 'em_analise_ps', 'paga_ps',
                                'devolvida_ps', 'em_disputa_ps', 'disponivel_ps', 'em_contestacao_ps', 'chargeback_debitado_ps');
        $statusPagSeguro = "'" . implode("','", $statusPagSeguro) . "'";

        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        $table = $resource->getTableName('sales_order_status_state');

        $query = "SELECT * FROM $table WHERE status IN ($statusPagSeguro)";
        $result = $readConnection->fetchAll($query);

        foreach ($result as $status){
            $sql = "UPDATE ".$table." SET state = '".Mage::getStoreConfig('payment/pagseguro_status_notification/'. $status['status'])."' 
                    WHERE status = '".$status['status'] ."'";

            $writeConnection->query($sql);
        }
    }
}
