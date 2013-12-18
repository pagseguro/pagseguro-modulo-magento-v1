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

use Mage_Core_Controller_Front_Action as FrontAction;

class PagSeguro_PagSeguro_PaymentController extends FrontAction
{
    const CANCELADO = 7;
    const MENSAGEM = 'Desculpe, infelizmente, houve um erro durante o checkout. 
        Entre em contato com o administrador da loja, se o problema persistir.';
    /**
     * Get Checkout Session  
     */
    private function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }
    
    /**
     * Get the Order of Checkout
     */
    private function getOrder()
    {
        return Mage::getModel('sales/order')->load($this->getCheckout()->getLastOrderId());
    }
    
    /**
     * Get PagSeguro Model instance
     */
    private function getPagSeguroPaymentModel()
    {
        return Mage::getSingleton('PagSeguro_PagSeguro_Model_PaymentMethod'); //Model
    }
    
    /**
     * Process the payment request and redirect to PagSeguro Gateway
     */
    public function requestAction()
    {
        $Order = $this->getOrder(); //Order Data
        
        $PagSeguroPaymentModel = $this->getPagSeguroPaymentModel();
        
        $fileOSC = scandir(getcwd().'/app/code/local/DeivisonArthur');
            
        $feedback = ($fileOSC == false ? 'checkout/onepage' : 'onepagecheckout');

        if (($Order->getState() == Mage_Sales_Model_Order::STATE_NEW) and
            ($Order->getPayment()->getMethod() == $PagSeguroPaymentModel->getCode()) and
            ($Order->getId())) {
            
            try {

                $PagSeguroPaymentModel->setOrder($Order);
                $this->_redirectUrl($PagSeguroPaymentModel->getRedirectPaymentHtml($Order));

            } catch (Exception $ex) {
                Mage::log($ex->getMessage());
                Mage::getSingleton('core/session')->addError(self::MENSAGEM);
                $this->_redirectUrl(Mage::getUrl() . $feedback);
                $this->_canceledStatus($Order);
            }
        
        } else {
            
            Mage::getSingleton('core/sessio$canceled')->addError(self::MENSAGEM);
            $this->_redirectUrl(Mage::getUrl() . $feedback);
            $this->_canceledStatus($Order);
        }
    }
    
    private function _canceledStatus($Order)
    {
        $Order->cancel();
        $Order->save();
    }
}
