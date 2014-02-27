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
use Mage_Payment_Helper_Data as HelperData;

class PagSeguro_PagSeguro_Helper_Data extends HelperData
{
        
    private $arraySt;

    private $objStatus;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->_createArraySt();

    }
        
    /**
     * Create Array Status PagSeguro
     */
    private function _createArraySt()
    {
        $this->arraySt = array(
            0 => array("status" => "iniciado_ps", "label" => "Iniciado"),
            1 => array("status" => "aguardando_pagamento_ps", "label" => "Aguardando Pagamento"),
            2 => array("status" => "em_analise_ps", "label" => "Em análise"),
            3 => array("status" => "paga_ps", "label" => "Paga"),
            4 => array("status" => "disponivel_ps", "label" => "Disponível"),
            5 => array("status" => "em_disputa_ps", "label" => "Em Disputa"),
            6 => array("status" => "devolvida_ps", "label" => "Devolvida"),
            7 => array("status" => "cancelada_ps", "label" => "Cancelada")
        );
    }

    /**
     * Return payment status by key PagSeguro 
     * @param type $value
     * @return type
     */
    public function returnOrderStByStPagSeguro($value)
    {
        return (array_key_exists($value, $this->arraySt) ? $this->arraySt[$value] : false);
    }

    /**
    * get array status
    * @return type
    */
    public function getArraySt()
    {
        return $this->arraySt;
    }

    /**
     * Save Status PagSeguro 
     */
    public function saveAllStatusPagSeguro()
    {
        foreach ($this->arraySt as $key => $value) {
            if (!$this->_existsStatus($value['status'])) {
                $this->objStatus->setStatus($value['status'])
                       ->setLabel($value['label']);
                $this->objStatus->save();
            }
        }
    }
    
    /**
     * Save Status PagSeguro
     * @param array $value
     */
    public function saveStatusPagSeguro(array $value)
    {
        if (!$this->_existsStatus($value['status'])) {
            $this->objStatus->setStatus($value['status'])
                 ->setLabel($value['label']);
            $this->objStatus->save();
        }
    }
    
    /**
     * Exists Status
     * @param type $status
     * @return type
     */
    public function _existsStatus($status)
    {
        $this->objStatus = Mage::getModel('sales/order_status')->load($status);

        return ($this->objStatus->getStatus()) ? true : false;
    }
}
